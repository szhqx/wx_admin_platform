<?php

namespace console\controllers;

use Yii;

use common\models\Mass;
use common\models\Article;
use common\models\Material;
use common\models\OfficialAccount;
use common\helpers\Cron;
use common\helpers\WechatHelper;

/**
 * Fix sync controller
 */
class FixSynController extends BaseController {

    /*
     * fix图文素材的cover封面同步失败的资料
     */
    public function actionFixArticleMulti() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        $MAX_FIX_TIMES = $params['MAX_FIX_TIMES'];

        if(Cron::lock($unique_key) !== FALSE) {

            //$redis = Yii::$app->redis;
            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;
            $now = time();
            $date = date("Y-m-d H:i:s");

            for (;;) {

                // $transaction = $db->beginTransaction();

                try{

                    $raw_sql = sprintf("select id, official_account_id, cover_media_id from material where type = %d and status = %d and cover_url = '' and fail_times <= %d order by id desc limit %d,%d;", Material::MATERIAL_TYPE_ARTICLE_MULTI, Material::STATUS_ACTIVE,$MAX_FIX_TIMES, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    // /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        // $transaction->rollback();
                        Yii::info(sprintf("Fail to update all material info with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }
                    $id_list_str = implode(",", $row_id_list);

                    # 锁住这些记录
                    $raw_sql = sprintf("select id, official_account_id, cover_media_id, weixin_cover_url from material where id in (%s) and status = %d;", $id_list_str, Material::STATUS_ACTIVE);
                    $material_list = $db->createCommand($raw_sql)->queryAll();
                    if(!$material_list) {
                        $transaction->rollback();
                        Yii::warning(sprintf("Fail to get the material list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }

                    $company_list = $this->_get_company_list($material_list);

                    foreach($material_list as $material) {

                        try {

                            // $transaction = $db->beginTransaction();

                            $wechat = WechatHelper::getWechat($material['official_account_id']);

                            $company_id = $company_list[$material['official_account_id']];

                            $source_info = Material::constructSourceUrl($material['cover_media_id'], $company_id, $material['official_account_id'], $wechat, $material['weixin_cover_url']);
                            if(!$source_info and $source_info['image_url']) {
                                Yii::warning(sprintf("Fail to get the cover url of the material(%d) with media id(%s).\n", $material['id'], $material['cover_media_id']), __METHOD__);
                            }
                            $source_url = $source_info['image_url'];

                            $raw_sql = sprintf("update material set cover_url = '%s', updated_at = %d, fail_times = fail_times + 1 where id = %d;", $source_url, $now, $material['id']);
                            $db->createCommand($raw_sql)->execute();

                            // $transaction->commit();

                        } catch (\Exception $e) {

                            // $transaction->rollback();
                            Yii::error(sprintf("Fail to fix the multi article material of account(%d) cos (%s) at line(%d) and date(%s).\n", $material['official_account_id'], $e->getMessage(), $e->getLine() ,$date), __METHOD__);
                            continue;
                        }
                    }

                //     $transaction->commit();

                }catch(\Exception $e) {

                    Yii::error(sprintf("Fail to fix the materials of multi article type cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    // $transaction->rollback();
                }

            }

            Cron::unlock($unique_key);
        }
    }

    /*
     * fix图片素材的下载失败的情况
     */
    public function actionFixImgMaterial() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        $MAX_FIX_TIMES = $params['MAX_FIX_TIMES'];

        if(Cron::lock($unique_key) !== FALSE) {

            //$redis = Yii::$app->redis;
            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;
            $now = time();
            $date = date("Y-m-d H:i:s");

            for (;;) {

                $transaction = $db->beginTransaction();

                try{

                    $raw_sql = sprintf("select id, official_account_id, weixin_source_url from material where type = %d and status = %d and source_url = '' and fail_times <= %d order by id desc limit %d,%d;", Material::MATERIAL_TYPE_IMAGE, Material::STATUS_ACTIVE, $MAX_FIX_TIMES, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    // /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollback();
                        Yii::info(sprintf("Fail to update all img material info with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }
                    $id_list_str = implode(",", $row_id_list);

                    $raw_sql = sprintf("select id, media_id, official_account_id, weixin_source_url from material where id in (%s) and status = %d;", $id_list_str, Material::STATUS_ACTIVE);
                    $material_list = $db->createCommand($raw_sql)->queryAll();
                    if(!$material_list) {
                        $transaction->rollback();
                        Yii::warning(sprintf("Fail to get the material list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }

                    $company_list = $this->_get_company_list($material_list);

                    foreach($material_list as $material) {

                        try {

                            $transaction = $db->beginTransaction();

                            $wechat = WechatHelper::getWechat($material['official_account_id']);

                            $company_id = $company_list[$material['official_account_id']];

                            $source_url = Material::downloadWeixinSourceImg($material['media_id'], $material['weixin_source_url'], $company_id, $material['official_account_id'], $wechat);
                            if(!$source_url) {
                                Yii::warning(sprintf("Fail to get the weixin source url of the material(%d) with weixin_source_url(%s).\n", $material['id'], $material['weixin_source_url']), __mETHOD__);
                            }

                            $raw_sql = sprintf("update material set source_url = '%s', updated_at = %d, fail_times = fail_times + 1 where id = %d;", $source_url, $now, $material['id']);
                            $db->createCommand($raw_sql)->execute();

                            $transaction->commit();

                        } catch (\Exception $e) {

                            $transaction->rollback();
                            Yii::error(sprintf("Fail to fix materials of img type for official_account(%d) cos (%s) at line(%d) and date(%s).\n", $material['official_account_id'], $e->getMessage(), $e->getLine() ,$date), __METHOD__);
                            continue;
                        }
                    }

                    $transaction->commit();

                }catch(\Exception $e) {

                    Yii::error(sprintf("Fail to fix materials for img type cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    $transaction->rollback();
                }

            }

            Cron::unlock($unique_key);
        }
    }

    private function _get_company_list($material_list) {

        $company_list = [];
        $official_account_id_list = [];

        foreach($material_list as $material) {
            $official_account_id_list[] = $material['official_account_id'];
        }
        array_unique($official_account_id_list);

        $raw_official_account_list = OfficialAccount::getListByIdList($official_account_id_list, false);
        foreach($raw_official_account_list as $raw_official_account) {
            $company_list[$raw_official_account['id']] = $raw_official_account['company_id'];
        }

        return $company_list;
    }
}
