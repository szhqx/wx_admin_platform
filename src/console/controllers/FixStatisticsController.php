<?php

namespace console\controllers;

use Yii;

use common\models\Mass;
use common\models\Article;
use common\helpers\Cron;
use common\models\OfficialAccount;

use common\helpers\WechatHelper;

/**
 * Fix Statistics controller
 */
class FixStatisticsController extends BaseController {

    /*
     * 全量更新文章阅读数，最多能算到从发出到最近7天以内的。
     * 如果文章的发布时间超过当前时间，就拉取不到总量数据了，只能通过其他hack的第三方接口或手动修改
     */
    public function actionUpdateArticleStatisticsTotal() {

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

            for (;;) {

                try{

                    $transaction = $db->beginTransaction();

                    // $raw_sql = sprintf("select id from official_account where status = %d order by id desc limit %d,%d;", OfficialAccount::STATUS_ACTIVE, $start, $num);
                    // $rows = $db->createCommand($raw_sql)->queryAll();

                    // /* execute query */
                    // $start += $num;
                    // if(count($rows) == 0) {
                    //     $transaction->rollback();
                    //     Yii::info(sprintf("Success to update all official account info with probably max total(%d) at %s.\n", $start - $num, $date), __METHOD__);
                    //     break;
                    // }

                    // $row_id_list = [];
                    // foreach($rows as $row) {
                    //     $row_id_list[] = $row['id'];
                    // }
                    // $id_list_str = implode(",", $row_id_list);

                    // # 锁住这些记录
                    // $raw_sql = sprintf("select * from official_account where id in (%s) and status = %d for update;", $id_list_str, OfficialAccount::STATUS_ACTIVE);
                    // $official_account_list = $db->createCommand($raw_sql)->queryAll();
                    // if(!$official_account_list) {
                    //     $transaction->rollback();
                    //     Yii::warning(sprintf("Fail to get the official account list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                    //     continue;
                    // }

                    // foreach($official_account_list as $official_account) {

                    //     try {

                    //         $transaction = $db->beginTransaction();

                    //         $wechat = WechatHelper::getWechat($official_account['id']);

                    //         $userSummary = $wechat->stats->userCumulate($start_date, $end_date);

                    //         $pre_day_summary = $userSummary['list'][0];

                    //         $raw_sql = sprintf("update official_account set fans_num = %d, updated_at = %d where id = %d;", $pre_day_summary['cumulate_user'], $now, $official_account['id']);
                    //         $db->createCommand($raw_sql)->execute();

                    //         $transaction->commit();

                    //     } catch (\Exception $e) {

                    //         $transaction->rollback();
                    //         Yii::error(sprintf("Fail to update the statistics of official account(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,$date), __METHOD__);
                    //         continue;
                    //     }

                    // }

                    $transaction->commit();

                }catch(\Exception $e) {

                    Yii::error(sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                    $transaction->rollback();
                }

            }

            Cron::unlock($unique_key);
        }
    }
}
