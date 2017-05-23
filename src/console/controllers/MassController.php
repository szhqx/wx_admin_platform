<?php

namespace console\controllers;

use common\models\AdvertisementOfficial;
use Yii;

use common\models\Mass;
use common\models\Material;
use common\models\Article;
use common\models\FansTag;
use common\helpers\Cron;

use common\helpers\WechatHelper;

/**
 * Mass controller
 */
class MassController extends BaseController {

    /*
     * - 找出所有ready状态的记录
     * - 找出所有相关的material列表
     * - for循环记录
     *  - 发送给微信
     *  - 本地备份文章
     *  - 调整mass状态
     *  - done
     */
    public function actionBatchSendMsg() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        if(Cron::lock($unique_key) !== FALSE) {

            //$redis = Yii::$app->redis;
            $db = Yii::$app->db;
            $date = date("Y-m-d H:i:s");

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;
            $today_begin = strtotime(date("Y-m-d 00:00:00"));
            $now = time();

            for (;;) {

                try{

                    $transaction = $db->beginTransaction();

                    # 找出所有ready状态的记录
                    $raw_sql = sprintf("select id from mass where status = %d and fail_times <= %d and pub_at <= %d and pub_at >= %d order by id desc limit %d,%d;", Mass::STATUS_NORMAL, $params['MAX_MASS_FAIL_TIMES'], $now, $today_begin, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollback();
                        Yii::info(sprintf("Success to fire all mass info with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }
                    $id_list_str = implode(",", $row_id_list);

                    # 锁住这些记录
                    $raw_sql = sprintf("select * from mass where id in (%s) and status = %d and pub_at <= %d and pub_at >= %d for update;", $id_list_str, Mass::STATUS_NORMAL, $now, $today_begin);
                    $mass_list = $db->createCommand($raw_sql)->queryAll();
                    if(!$mass_list) {
                        $transaction->rollback();
                        Yii::warning(sprintf("Fail to get the mass records from ready to completed at %s.\n", $date), __METHOD__);
                        continue;
                    }

                    // Yii::warning(json_encode(_list), __METHOD__);

                    $material_id_list = [];
                    for($i=0;$i<count($mass_list);$i++) {
                        $material_id_list[] = $mass_list[$i]['material_id'];
                    }

                    Yii::warning(json_encode($material_id_list), __METHOD__);

                    # 找出所有相关的material列表
                    $raw_material_list = Material::findByIdList($material_id_list);
                    if(!$raw_material_list) {
                        Yii::warning(sprintf("查找素材列表为空，查找id为(%s).\n", json_encode($material_id_list)), __METHOD__);
                        continue;
                    }

                    $material_list = [];
                    foreach($raw_material_list as $material_info) {
                        $material_list[$material_info['id']] = $material_info;
                    }

                    foreach($mass_list as $raw_mass) {

                        $transaction = $db->beginTransaction();

                        # TODO 优化这一次查询
                        $mass = Mass::findById($raw_mass['id']);

                        $material_info = $material_list[$mass['material_id']];

                        # 发送给微信
                        $is_send = $this->_fireMsg($mass, $material_info);
                        if(!$is_send) {

                            $transaction->rollback();
                            Yii::error(sprintf("Fail to send mass info with params: mass(%s).\n", json_encode($mass)), __METHOD__);

                            // mark down fail times
                            $mass->fail_times += 1;
                            if($mass->fail_times >= $params['MAX_MASS_FAIL_TIMES']) {
                                $mass->status = Mass::STATUS_ABNORMAL;
                            }
                            $mass->save();

                            continue;
                        }

                        $transaction->commit();
                    }

                    $transaction->commit();

                }catch(\Exception $e) {

                    Yii::error(sprintf("获取mass记录出错.cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    $transaction->rollback();
                }

            }

            Cron::unlock($unique_key);
        }
    }


    // private helpers
    private function _fireMsg($mass, $material_info) {

        try {

            $wechat_tag_id = null;
            if($mass['user_tag_id']) {
                $wechat_tag_info = FansTag::findById($mass['user_tag_id']);
                if(!$wechat_tag_info) {
                    throw new Exception(sprintf("Fail to find fans tag(%s)", $mass->user_tag_id));
                }
                $wechat_tag_id = $wechat_tag_info['wechat_tag_id'];
            }

            $wechat = WechatHelper::getWechat($material_info['official_account_id']);
            $send_info = Mass::broadcast($material_info, $wechat_tag_id, $wechat);
            if(!$send_info) {
                throw new Exception("");
            }
            $order_info = Material::_getAdInfo($material_info['id']);
            if(count($order_info)){
                $ids = array_column($order_info,'id');
                AdvertisementOfficial::updateAll(['status'=>1,'updated_at'=>time()],['in','id',$ids]);
            }
            $mass->msg_id = $send_info['msg_id'];

            if($material_info['type'] == Material::MATERIAL_TYPE_ARTICLE_MULTI) {
                $mass->msg_data_id = $send_info['msg_data_id'];
            }

            // create article list
            $is_stored = $this->_storeSendMsg($mass, $material_info);
            if(!$is_stored) {
                throw new Exception(sprintf("Fail to store send msg(%s)", ''));
            }

        } catch(\Exception $e) {
            Yii::error(sprintf('Fail to create mass cos fail to send broadcast:(%s)', $e), __METHOD__);
            return false;
        }

        $is_updated = $mass->finishBrocast($send_info);
        if(!$is_updated) {
            Yii::error(sprintf('Fail to update mass cos(%s)', ''), __METHOD__);
            return false;
        }

        return true;
    }

    private function _storeSendMsg($mass, $material_info) {

        switch($material_info['type']) {

        case Material::MATERIAL_TYPE_ARTICLE_MULTI:
            $this->_updateAdvertiseStatus($material_info['id']);
            return Article::storeMultiArticleMsg($mass, $material_info);
        case Material::MATERIAL_TYPE_IMAGE:
            return Article::storeImgMsg($mass, $material_info);
        default:
            return false;
        }

        return false;
    }

    private function _updateAdvertiseStatus($material_id){
        $advertise_official = AdvertisementOfficial::find()->where(["material_id"=>$material_id])->one();
        if($advertise_official){
            $advertise_official ->status = 1;
            $advertise_official ->save();
            Yii::info(sprintf("success to update advertise of material id(%d) status to 1",$material_id));
        }else{
            Yii::info(sprintf("the material(%d) is not advertise",$material_id));
        }
    }


}

