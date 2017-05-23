<?php

namespace console\controllers;
use common\models\StatisticNews;
use common\models\StatisticUser;
use EasyWeChat\Foundation\Application;
use Yii;
use common\helpers\Cron;
use common\models\OfficialAccount;
use common\helpers\WechatHelper;

/**
 * 统计模块
 */
class AnalysisController extends BaseController {

    /*
     * 更新公众号用户分析数据（总数）+ 每日1点跑昨天的数据
     */
    public function actionUpdateFansData() {

        $unique_key = md5(__METHOD__);
//        $params = Yii::$app->params;

        if(Cron::lock($unique_key) !== FALSE) {

            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            $date = date("Y-m-d H:i:s");
            $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
            $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));

            for (;;) {

                try{

                    $transaction = $db->beginTransaction();

                    $raw_sql = sprintf("select id from official_account where status = %d order by id desc limit %d,%d;", OfficialAccount::STATUS_ACTIVE, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollBack();
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
                        $transaction->rollBack();
                        Yii::warning(sprintf("Fail to get the official account list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }



                    foreach($official_account_list as $official_account) {

                        try {

                            $transaction = $db->beginTransaction();

                            $raw_sql_user = sprintf("select * from statistic_user where ref_date = %d and official_account_id = %d;", $yesterday_7, $official_account['id']);
                            $res = $db->createCommand($raw_sql_user)->queryOne();
                            if($res){
                                $raw_sql_user = sprintf("select * from statistic_user where ref_date = %d and official_account_id = %d;", $yesterday, $official_account['id']);
                                $res = $db->createCommand($raw_sql_user)->queryOne();
                                if($res){
                                    break;
                                }
                                //更新昨天的数据
                                $yesterdayData = $this->_getYesterdayData($official_account['id'],'fans');
                                $this->_saveData($yesterdayData,null,'fans');

                            }else{
                                //更新全部数据
                                //获取一个月内的数据
                                for($i=0;$i<5;$i++) {
                                    $from = date("Y-m-d", strtotime("-" . (7 * $i + 7) . " day"));
                                    $to = date("Y-m-d", strtotime("-" . (7 * $i + 1) . " day"));
                                    $FansData = $this->_getData($official_account['id'], 'fans', $from, $to);
//                                    var_dump($FansData);
                                    $this->_saveData($FansData, null, 'fans');
                                }
                            }
                            echo "success official_account ".$official_account['id']."\n";
                            $transaction->commit();

                        } catch (\Exception $e) {

                            $transaction->rollBack();
                            $err_msg = sprintf("Fail to update the statistics of official account(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,$date);
                            echo $err_msg;
                            Yii::error($err_msg, __METHOD__);
                            continue;
                        }

                    }

                    $transaction->commit();

                }catch(\Exception $e) {
                    $err_msg = sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date);
                    echo $err_msg;
                    Yii::error(sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                }

            }

            Cron::unlock($unique_key);
        }

    }
    /*
     * 更新公众号图文分析数据（总数）+ 每日1点跑昨天的数据
     */
    public function actionUpdateNewsData() {

        $unique_key = md5(__METHOD__);

        if(Cron::lock($unique_key) !== FALSE) {

            $db = Yii::$app->db;

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            $date = date("Y-m-d H:i:s");
            $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
            $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));

            for (;;) {

                try{

                    $transaction = $db->beginTransaction();

                    $raw_sql = sprintf("select id from official_account where status = %d order by id desc limit %d,%d;", OfficialAccount::STATUS_ACTIVE, $start, $num);
                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if(count($rows) == 0) {
                        $transaction->rollBack();
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
                        $transaction->rollBack();
                        Yii::warning(sprintf("Fail to get the official account list with id list(%s) at %s.\n", $id_list_str, $date), __METHOD__);
                        continue;
                    }



                    foreach($official_account_list as $official_account) {

                        try {

                            $transaction = $db->beginTransaction();

                            $raw_sql_user = sprintf("select * from statistic_news where ref_date = %d and official_account_id = %d;", $yesterday_7, $official_account['id']);
                            $res = $db->createCommand($raw_sql_user)->queryOne();
                            if($res){
                                $raw_sql_user = sprintf("select * from statistic_news where ref_date = %d and official_account_id = %d;", $yesterday, $official_account['id']);
                                $res = $db->createCommand($raw_sql_user)->queryOne();
                                if($res){
                                    break;
                                }
                                //更新昨天的数据
                                $yesterdayData = $this->_getYesterdayData($official_account['id'],'news');
                                $this->_saveData(null,$yesterdayData,'news');

                            }else{
                                //更新全部数据
                                //获取三天内的数据
                                for($i=0;$i<10;$i++){
                                    $from = date("Y-m-d",strtotime("-".(3*$i+3)." day"));
                                    $to = date("Y-m-d",strtotime("-".(3*$i+1)." day"));
                                    $NewsData = $this->_getData($official_account['id'],'news',$from,$to);
                                    $this->_saveData(null,$NewsData,'news');
                                }

                            }
                            echo "success official_account ".$official_account['id']."\n";
                            $transaction->commit();

                        } catch (\Exception $e) {

                            $transaction->rollBack();
                            $err_msg = sprintf("Fail to update the statistics of official account(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,$date);
                            echo $err_msg;
                            Yii::error($err_msg, __METHOD__);
                            continue;
                        }

                    }

                    $transaction->commit();

                }catch(\Exception $e) {
                    $err_msg = sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date);
                    echo $err_msg;
                    Yii::error(sprintf("Fail to update the statistics of official account cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,$date), __METHOD__);

                }

            }

            Cron::unlock($unique_key);
        }

    }

    private function _getData($official_account_id,$type,$from,$to){
        $wechat = WechatHelper::getWechat($official_account_id);
        if(!$wechat) {
            Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
        }
        $stats = $wechat->stats;

        if($type == 'fans'){
            $userSummary = $stats->userSummary($from, $to)->toArray();
            $userCumulate = $stats->userCumulate($from, $to)->toArray();
            $data = [];
            for($i=1;$i<=7;$i++){
                $ref_date = strtotime($userCumulate['list'][$i-1]['ref_date']);
                $user_source = $userCumulate['list'][$i-1]['user_source'];
                $cumulate_user = $userCumulate['list'][$i-1]['cumulate_user'];
                $new_user = 0;
                $cancel_user = 0;
                for($j=1;$j<=7;$j++){
                    if(isset($userSummary['list'][$j - 1]['ref_date'])) {
                        if ($userCumulate['list'][$i - 1]['ref_date'] == $userSummary['list'][$j - 1]['ref_date']) {
                            $new_user = $userSummary['list'][$j - 1]['new_user'];
                            $cancel_user = $userSummary['list'][$j - 1]['cancel_user'];
                        }
                    }
                }

                $data[] = [$official_account_id,$ref_date,$user_source,$new_user,$cancel_user,$cumulate_user,time()];
            }

            return $data;
        }else{
            $data = [];
            $userReadSummary = $stats->userReadSummary($from, $to)->toArray();
            foreach ($userReadSummary['list'] as $list){
                $data[] = [
                    $official_account_id,
                    strtotime($list['ref_date']),
                    $list['user_source'],
                    $list['int_page_read_user'],
                    $list['int_page_read_count'],
                    $list['ori_page_read_user'],
                    $list['ori_page_read_count'],
                    $list['share_user'],
                    $list['share_count'],
                    $list['add_to_fav_user'],
                    $list['add_to_fav_count'],
                    time(),
                ];
            }
            
            return $data;
        }

    }

    private function _saveData($FansData=null,$newsData=null,$type){
        if($type == 'fans'){
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticUser::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'user_source',
                    'new_user',
                    'cancel_user',
                    'cumulate_user',
                    'created_at'], $FansData)
                ->execute();
        }else{
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticNews::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'user_source',
                    'int_page_read_user',
                    'int_page_read_count',
                    'ori_page_read_user',
                    'ori_page_read_count',
                    'share_user',
                    'share_count',
                    'add_to_fav_user',
                    'add_to_fav_count',
                    'created_at'], $newsData)
                ->execute();
        }

    }

    private function _getYesterdayData($official_account_id,$type){
        $wechat = WechatHelper::getWechat($official_account_id);
        if(!$wechat) {
            Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
        }
        $stats = $wechat->stats;
        
        if($type == 'fans'){
            $userSummary = $stats->userSummary(date("Y-m-d",strtotime("-1 day")), date("Y-m-d",strtotime("-1 day")))->toArray();
            $userCumulate = $stats->userCumulate(date("Y-m-d",strtotime("-1 day")), date("Y-m-d",strtotime("-1 day")))->toArray();
            $new_user = 0;
            $cancel_user = 0;
            if(isset($userSummary['list']['0']['ref_date'])){
                $new_user = $userSummary['list']['0']['new_user'];
                $cancel_user = $userSummary['list']['0']['cancel_user'];
            }
            $data = [$official_account_id,strtotime($userCumulate['list']['0']['ref_date']),$userCumulate['list']['0']['user_source'],$new_user,$cancel_user,$cumulate_user = $userCumulate['list']['0']['cumulate_user'],time()];

            return $data;
        }else{
            $data = [];
            $userReadSummary = $stats->userReadSummary(date("Y-m-d",strtotime("-1 day")), date("Y-m-d",strtotime("-1 day")))->toArray();
            foreach ($userReadSummary['list'] as $list){
                $data[] = [
                    $list['official_account_id'],
                    strtotime($list['ref_date']),
                    $list['user_source'],
                    $list['int_page_read_user'],
                    $list['int_page_read_count'],
                    $list['ori_page_read_user'],
                    $list['ori_page_read_count'],
                    $list['share_user'],
                    $list['share_count'],
                    $list['add_to_fav_user'],
                    $list['add_to_fav_count'],
                    time(),
                ];
            }

            return $data;
        }
        
    }





}
