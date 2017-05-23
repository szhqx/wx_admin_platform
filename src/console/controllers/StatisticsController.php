<?php

namespace console\controllers;

use common\helpers\Utils;
use common\models\Fans;
use common\models\FansGroup;
use common\models\FansTag;
use common\models\Material;
use common\models\User;
use EasyWeChat\Foundation\Application;
use Yii;

use common\models\Mass;
use common\models\Article;
use common\helpers\Cron;
use common\models\OfficialAccount;

use common\helpers\WechatHelper;

/**
 * Statistics controller
 */
class StatisticsController extends BaseController {

    /*
     * 更新公众号粉丝数（每日增减）
     */
    public function actionUpdateFansStatisticsDailyIncr() {
        // TODO
    }

    public function actionTest(){
        $options = [
            "app_id"=>"wx50b0d5f09ded62c5",
            "secret"=>"34912296db29d2ccdea01b745401c0f3"
        ];

        $wechat = new Application($options);
        $_date = date('Y-m-d');
        $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));
        $userSummary = $wechat->stats->userCumulate($start_date, $end_date)->toArray();


//        $openids = $wechat->user->lists()->toArray();
        var_dump($userSummary['list']['0']['cumulate_user']);

    }

    /*
     * 更新公众号粉丝数（总数）+ 每日零点跑
     */
    public function actionUpdateFansStatisticsTotal() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        if(Cron::lock($unique_key) !== FALSE) {

            //$redis = Yii::$app->redis;
            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 1; // 获取微信的统计数据经常超时，这里直接每次都一条
            $now = time();
            $date = date("Y-m-d H:i:s");

            $_date = date('Y-m-d');
            $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));

            for (;;) {

                try{

                    $transaction = $db->beginTransaction();

                    $raw_sql = sprintf("select id from official_account where status = %d order by id desc limit %d,%d;", OfficialAccount::STATUS_ACTIVE, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollback();
                        Yii::info(sprintf("Success to update all official account info with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }
                    $id_list_str = implode(",", $row_id_list);

                    # 锁住这些记录
                    $raw_sql = sprintf("select * from official_account where id in (%s) and status = %d for update;", $id_list_str, OfficialAccount::STATUS_ACTIVE);
                    $official_account_list = $db->createCommand($raw_sql)->queryAll();
                    if(!$official_account_list) {
                        $transaction->rollback();
                        Yii::warning(sprintf("Fail to get the official account list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }

                    foreach($official_account_list as $official_account) {

                        try {

                            $transaction = $db->beginTransaction();

                            $wechat = WechatHelper::getWechat($official_account['id']);

                            $userSummary = $wechat->stats->userCumulate($start_date, $end_date)->toArray();

                            if(empty($userSummary['list'])){
                                Yii::warning("userSummary is empty", __METHOD__);
                                continue;
                            }

                            Yii::warning(sprintf("user Summary(%s).\n", json_encode($userSummary)), __METHOD__);

                            $pre_day_summary = $userSummary['list']['0'];

                            $raw_sql = sprintf("update official_account set fans_num = %d, updated_at = %d where id = %d;", $pre_day_summary['cumulate_user'], $now, $official_account['id']);
                            $db->createCommand($raw_sql)->execute();

                            $transaction->commit();

                        } catch (\Exception $e) {

                            $transaction->rollback();
                            Yii::error(sprintf("Fail to update the statistics of official account(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,$date), __METHOD__);
                            continue;
                        }

                    }

                    $transaction->commit();

                }catch(\Exception $e) {

                    Yii::error(sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    $transaction->rollback();
                }

            }

            Cron::unlock($unique_key);
        }

    }

    /*
     * 更新文章统计数据（总数）
     * 每日零点跑，统计昨天的一天的阅读数据
     */
    public function actionUpdateArticleStatisticsDailyIncr() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        if(Cron::lock($unique_key) !== FALSE) {

            //$redis = Yii::$app->redis;
            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;
            $now = time();
            $date = date("Y-m-d H:i:s");

            $_date = date('Y-m-d');
            $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));
            // $start_date = $end_date = date('Y-m-d', strtotime($_date . ' +0 day')); // 貌似获取不了当天的数据

            Yii::error($start_date, __METHOD__);
            Yii::error($end_date, __METHOD__);

            for (;;) {

                try {

                    $transaction = $db->beginTransaction();

                    $raw_sql = sprintf("select id from official_account where status = %d order by id desc limit %d,%d;", OfficialAccount::STATUS_ACTIVE, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollback();
                        Yii::info(sprintf("Success to update all articles statistics of official accounts with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }
                    $id_list_str = implode(",", $row_id_list);

                    # 锁住这些记录
                    $raw_sql = sprintf("select * from official_account where id in (%s) and status = %d for update;", $id_list_str, OfficialAccount::STATUS_ACTIVE);
                    $official_account_list = $db->createCommand($raw_sql)->queryAll();
                    if(!$official_account_list) {
                        $transaction->rollback();
                        Yii::warning(sprintf("Fail to get the official account list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }

                    foreach($official_account_list as $official_account) {

                        $msg_data_id = '';

                        try {

                            $transaction = $db->beginTransaction();

                            $wechat = WechatHelper::getWechat($official_account['id']);

                            $articleSummary = $wechat->stats->articleSummary($start_date, $end_date);

                            if(empty($articleSummary['list'])){
                                Yii::warning("article is empty", __METHOD__);
                                continue;
                            }

//                            Yii::warning(json_encode($articleSummary), __METHOD__);

                            foreach($articleSummary['list'] as $summary) {

                                $int_page_read_count = $summary['int_page_read_count'];
                                $add_to_fav_count = $summary['add_to_fav_count'];
                                $msg_data_id = $summary['msgid'];

                                try {

                                    $transaction = $db->beginTransaction();

                                    $article = Article::findByMsgDataId($msg_data_id);

                                    if(!$article) {
                                        Yii::warning(sprintf("article(%s) not in system mass ",$msg_data_id), __METHOD__);
                                        continue;
                                    }

                                    $raw_sql = sprintf("update article set int_page_read_count = int_page_read_count + %d, add_to_fav_count = add_to_fav_count + %d, updated_at = %d where msg_data_id = '%s' and status = %d;", $int_page_read_count, $add_to_fav_count, $now, $msg_data_id, Article::STATUS_DELETED);

                                    $db->createCommand($raw_sql)->execute();

                                    $transaction->commit();

                                } catch(\Exception $e) {

                                    $transaction->rollback();

                                    Yii::error(sprintf("Fail to update article statistics for article of official_account_id(%d) with msg_data_id(%d) cos %s at date(%s).\n", $msg_data_id, $official_account['id'], $e->getMessage(), $date), __METHOD__);

                                    continue;
                                }
                            }

                            $transaction->commit();

                        } catch (\Exception $e) {
                            $transaction->rollback();
                            Yii::error(sprintf("Fail to update article statistics for official account(%d) with msg_data_id(%d) cos %s at date(%s).\n",  $official_account['id'], $msg_data_id, $e->getMessage(), $date), __METHOD__);
                            continue;
                        }
                    }

                    $transaction->commit();

                } catch(\Exception $e) {

                    Yii::error(sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    $transaction->rollback();
                }

            }
            Cron::unlock($unique_key);
        }
    }


}
