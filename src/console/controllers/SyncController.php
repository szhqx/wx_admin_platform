<?php

namespace console\controllers;

use common\helpers\Utils;
use common\models\Fans;
use common\models\FansGroup;
use common\models\FansTag;
use common\models\Material;
use common\models\Menus;
use common\models\Reply;
use common\models\ReplyNews;
use common\models\StatisticMenu;
use common\models\StatisticMenuLog;
use common\models\User;
use EasyWeChat\Foundation\Application;
use Yii;

use common\models\Mass;
use common\models\Article;
use common\helpers\Cron;
use common\models\OfficialAccount;

use common\helpers\WechatHelper;


use common\models\StatisticNew;
use common\models\StatisticNews;
use common\models\StatisticUser;

/**
 * Statistics controller
 */
class SyncController extends BaseController
{


    public function actionTest(){
        $model = new StatisticNews();
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $res = $model->find()->select(['id'])->where(['official_account_id'=>130])->andWhere(['between','ref_date',$yesterday,$yesterday+86400])->one();
        var_dump($res);

    }


    //同步微信端菜单和自定义自动回复的数据到本地，前提清空menus,menus_news,reply,reply_news,statistic_menu,statistic_menu_log
    public function actionSync(){

        //清空数据表
        $this ->_truncateTable();

        $unique_key = md5(__METHOD__);

        if (Cron::lock($unique_key) !== FALSE) {



            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            for (; ;) {
                $db = Yii::$app->db;
//                $transaction = $db->beginTransaction();
                try {

                    $raw_sql = sprintf("select id from official_account where status = %d order by id limit %d,%d;", OfficialAccount::STATUS_ACTIVE,$start, $num);

                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if (count($rows) == 0) {
                        $error_msg = sprintf("Success to sync menu and reply data with probably max total(%d) at %s.\n", $start - $num, date("Y-m-d H:i:s"));
                        Yii::info($error_msg, __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach ($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }

                    foreach($row_id_list as $official_account_id){

                        # 锁住这些记录
                        $raw_sql = sprintf("select * from official_account where id = %d for update;", $official_account_id);
                        $official_account = $db->createCommand($raw_sql)->queryOne();
                        if (!$official_account) {
                            $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));

                            Yii::warning($error_msg, __METHOD__);
                            continue;
                        }

                        try {

                            echo sprintf("start to sync data official_account (%d).\n",$official_account_id);
                            $wechat = WechatHelper::getWechat($official_account_id);
                            $menu_list = Menus::find()->where(['official_account_id'=>$official_account_id])->asArray()->all();
                            if(count($menu_list) == 0){

                                if(!$wechat) {
                                    echo sprintf("Fail to get WeChat by official_account_id() at date(%d).\n",$official_account_id);
                                    Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
                                    continue;
                                }
                                $current_list = $wechat->menu->current();

                                $this->_saveCurrentMenu($current_list->selfmenu_info['button'],$official_account_id);
                            }


                            $wx_data = $this->getWxReplyData($official_account_id,$wechat);

                            $this->saveData($wx_data);

                            $this->_checkData($official_account_id,'news');

                            $this->_checkData($official_account_id,'fans');

                            $this->_checkArticleData($official_account_id);

                            Yii::info(sprintf("success to sync data official_account (%d).\n",$official_account_id), __METHOD__);
                            echo sprintf("success to sync data official_account (%d).\n",$official_account_id);


                        } catch (\Exception $e) {
//                            $transaction->rollBack();
                            $error_msg = sprintf("Fail to sync menu and reply data cos reason(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                            Yii::error($error_msg, __METHOD__);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
//                    $transaction->rollBack();
                    $error_msg = sprintf("Fail to sync menu and reply data cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                    Yii::error($error_msg, __METHOD__);
                }
            }
        }
    }

    //同步每天的用户分析和图文分析数据
    public function actionSyncStatisticData(){
        $unique_key = md5(__METHOD__);

        if (Cron::lock($unique_key) !== FALSE) {



            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            for (; ;) {
                $db = Yii::$app->db;
//                $transaction = $db->beginTransaction();
                try {

                    $raw_sql = sprintf("select id from official_account where status = %d order by id limit %d,%d;", OfficialAccount::STATUS_ACTIVE,$start, $num);

                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if (count($rows) == 0) {
                        $error_msg = sprintf("Success to statistic yesterday data with probably max total(%d) at %s.\n", $start - $num, date("Y-m-d H:i:s"));
                        Yii::info($error_msg, __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach ($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }

                    foreach($row_id_list as $official_account_id){

                        # 锁住这些记录
                        $raw_sql = sprintf("select * from official_account where id = %d for update;", $official_account_id);
                        $official_account = $db->createCommand($raw_sql)->queryOne();
                        if (!$official_account) {
                            $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));

                            Yii::warning($error_msg, __METHOD__);
                            continue;
                        }

                        try {

                            Yii::info(sprintf("sync statistic yesterday data of official_account(%d)",$official_account_id),__METHOD__);

                            $this->_checkData($official_account_id,'news');

                            $this->_checkData($official_account_id,'fans');

                            $this->_checkArticleData($official_account_id);


                        } catch (\Exception $e) {
//                            $transaction->rollBack();
                            $error_msg = sprintf("Fail to statistic yesterday data cos reason(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                            Yii::error($error_msg, __METHOD__);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
//                    $transaction->rollBack();
                    $error_msg = sprintf("Fail to statistic yesterday data cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                    Yii::error($error_msg, __METHOD__);
                }
            }
        }
    }

    //同步每天的每天同步菜单统计数据
    public function actionSyncStatisticMenuData(){
        $unique_key = md5(__METHOD__);

        if (Cron::lock($unique_key) !== FALSE) {

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            for (; ;) {
                $db = Yii::$app->db;
//                $transaction = $db->beginTransaction();
                try {

                    $raw_sql = sprintf("select id from official_account where status = %d order by id limit %d,%d;", OfficialAccount::STATUS_ACTIVE,$start, $num);

                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if (count($rows) == 0) {
                        $error_msg = sprintf("Success to statistic menu data with probably max total(%d) at %s.\n", $start - $num, date("Y-m-d H:i:s"));
                        Yii::info($error_msg, __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach ($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }

                    foreach($row_id_list as $official_account_id){

                        # 锁住这些记录
                        $raw_sql = sprintf("select * from official_account where id = %d for update;", $official_account_id);
                        $official_account = $db->createCommand($raw_sql)->queryOne();
                        if (!$official_account) {
                            $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));
                            Yii::warning($error_msg, __METHOD__);
                            continue;
                        }

                        try {

                            Yii::info(sprintf("sync statistic menu data of official_account(%d)",$official_account_id),__METHOD__);

                            $this->_checkMenuData($official_account_id);

                        } catch (\Exception $e) {
//                            $transaction->rollBack();
                            $error_msg = sprintf("Fail to statistic menu data cos reason(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));
                            Yii::error($error_msg, __METHOD__);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
//                    $transaction->rollBack();
                    $error_msg = sprintf("Fail to statistic menu data cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));
                    Yii::error($error_msg, __METHOD__);
                }
            }
        }
    }

    //可能出现没有同步数据的现象，执行未同步的用户分析数据，
    public function actionSyncStatisticDataToDay(){
        $unique_key = md5(__METHOD__);

        if (Cron::lock($unique_key) !== FALSE) {

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            for (; ;) {
                $db = Yii::$app->db;

                try {

                    $raw_sql = sprintf("select id from official_account where status = %d order by id limit %d,%d;", OfficialAccount::STATUS_ACTIVE,$start, $num);

                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if (count($rows) == 0) {
                        $error_msg = sprintf("Success to check statistic yesterday data with probably max total(%d) at %s.\n", $start - $num, date("Y-m-d H:i:s"));
                        Yii::info($error_msg, __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach ($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }

                    foreach($row_id_list as $official_account_id){

                        # 锁住这些记录
                        $raw_sql = sprintf("select * from official_account where id = %d for update;", $official_account_id);
                        $official_account = $db->createCommand($raw_sql)->queryOne();
                        if (!$official_account) {
                            $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));

                            Yii::warning($error_msg, __METHOD__);
                            continue;
                        }

                        try {

                            Yii::info(sprintf("sync statistic yesterday data of official_account(%d)",$official_account_id),__METHOD__);

                            $from = date("Y-m-d", strtotime("-2 day"));
                            $to = date("Y-m-d", strtotime("-2 day"));

                            $yesterdayData = $this->_getData($official_account_id,'fans',$from, $to);
                            if(empty($yesterdayData)){
                                Yii::info(sprintf("yesterday fans data is empty for (%s)",$official_account_id),__METHOD__);
                                return false;
                            }
                            $this->_saveData($yesterdayData,null,'fans');


                        } catch (\Exception $e) {
//                            $transaction->rollBack();
                            $error_msg = sprintf("Fail to statistic yesterday data cos reason(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                            Yii::error($error_msg, __METHOD__);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
//                    $transaction->rollBack();
                    $error_msg = sprintf("Fail to statistic yesterday data cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                    Yii::error($error_msg, __METHOD__);
                }
            }
        }
    }

    //检查是否有存在昨天的数据，
    public function actionCheckStatisticData(){
        $unique_key = md5(__METHOD__);

        if (Cron::lock($unique_key) !== FALSE) {

            // 获取所有待触发的记录
            $start = 0;
            $num = 5;

            for (; ;) {
                $db = Yii::$app->db;

                try {

                    $raw_sql = sprintf("select id from official_account where status = %d order by id limit %d,%d;", OfficialAccount::STATUS_ACTIVE,$start, $num);

                    $rows = $db->createCommand($raw_sql)->queryAll();

                    /* execute query */
                    $start += $num;
                    if (count($rows) == 0) {
                        $error_msg = sprintf("Success to check statistic yesterday data with probably max total(%d) at %s.\n", $start - $num, date("Y-m-d H:i:s"));
                        Yii::info($error_msg, __METHOD__);
                        break;
                    }

                    $row_id_list = [];
                    foreach ($rows as $row) {
                        $row_id_list[] = $row['id'];
                    }

                    foreach($row_id_list as $official_account_id){

                        # 锁住这些记录
                        $raw_sql = sprintf("select * from official_account where id = %d for update;", $official_account_id);
                        $official_account = $db->createCommand($raw_sql)->queryOne();
                        if (!$official_account) {
                            $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));

                            Yii::warning($error_msg, __METHOD__);
                            continue;
                        }

                        try {

                            $yesterday = date("Y-m-d", strtotime("-1 day"));
                            $raw_sql_fans = sprintf("select * from statistic_user where official_account_id = %d and ref_date = %s",$official_account_id,$yesterday);
                            $result_statistic_fans = $db->createCommand($raw_sql_fans)->queryOne();
                            if(!$result_statistic_fans){
                                Yii::info(sprintf("check statistic data for twice official_account(%d)",$official_account_id),__METHOD__);

                                $yesterdayData = $this->_getData($official_account_id,'fans',$yesterday, $yesterday);
                                if(empty($yesterdayData)){
                                    Yii::info(sprintf("yesterday fans data is empty for (%s)",$official_account_id),__METHOD__);
                                    return false;
                                }
                                $this->_saveData($yesterdayData,null,'fans');
                            }


                        } catch (\Exception $e) {

                            $error_msg = sprintf("Fail to statistic yesterday data cos reason(%d) cos (%s) at line(%d) and date(%s).\n", $official_account['id'], $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                            Yii::error($error_msg, __METHOD__);
                            continue;
                        }
                    }
                } catch (\Exception $e) {
//                    $transaction->rollBack();
                    $error_msg = sprintf("Fail to statistic yesterday data cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));

                    Yii::error($error_msg, __METHOD__);
                }
            }
        }
    }


    //保存粉丝信
    private function _saveFansInfo($wechat,$openid_list,$official_account_id){
        $db = Yii::$app->db;
        $default_head_img = Yii::$app->params['DEFAULT_HEAD_IMG'];
        try{

            $list = $wechat->user->batchGet($openid_list)->toArray();

            try{
//
                foreach ($list['user_info_list'] as $k=>$v){
                    if($v['subscribe'] == 1){
                        $db->createCommand()->update(
                            Fans::tableName(),
                            [   'nickname'=>$v['nickname'],
                                'city'=>$v['city'],
                                'province'=>$v['province'],
                                'remark'=>$v['remark'],
                                'country'=>$v['country'],
                                'avator'=>empty($v['headimgurl'])?$default_head_img:$v['headimgurl'],
                                'group_id'=>$v['groupid'],
                                'sex'=>$v['sex'],
                                'language'=>$v['language'],
                                'subscribed_at'=>$v['subscribe_time'],
                                'tagid_list'=>serialize($v['tagid_list']),
                                'is_syc'=>1,
                                'created_at'=>time()
                            ],
                            [
                                'open_id'=>$v['openid'],
                                'is_syc'=>0,
                                'account_id'=>$official_account_id,
                            ])->execute();
                    }else{
                        $db->createCommand()->update(
                            Fans::tableName(),
                            [
                                'is_syc'=>1,
                                'is_subscribe'=>0,
                                'created_at'=>time()
                            ],
                            [
                                'open_id'=>$v['openid'],
                                'is_syc'=>0,
                                'account_id'=>$official_account_id,
                            ])->execute();
                    }
                }


            }catch (\Exception $e){
                $error_msg = sprintf('Fail to sync fans info cos at(%s) reason:(%s)',$e->getLine(),$e->getMessage());
                echo $error_msg;
                Yii::error($error_msg,__METHOD__);
                return false;
            }
        }catch (\Exception $e){
            $error_msg = sprintf('Fail to sync fans info cos at(%s) reason:(%s)',$e->getLine(),$e->getMessage());
            echo $error_msg;
            Yii::error($error_msg,__METHOD__);
            return false;
        }
    }

    //保存openid
    private function _saveOpenid($openids,$official_account_id){
        $rows = [];
        foreach ($openids as $k=>$v){
            $rows[] = [$official_account_id,0,$v];
        }
        $res = Yii::$app->db->createCommand()
                            ->batchInsert('fans', ['account_id','group_id','open_id'], $rows)
                            ->execute();
        return $res;
    }


    private function _syc_image_from_wechat($wechat,$offset, $count, $official_account_id, $user_id,$company_id) {

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {

            $type = "image";

            $lists = $wechat->material->lists($type, $offset, $count);

            $image_list = $lists['item'];

            if(empty($image_list)) {
                $transaction->rollBack();
                return null;
            }

            $media_id_list = Utils::getSubByKey($image_list, "media_id");
            $local_image_material_list = Material::find()->where(
                ["in", "media_id", $media_id_list]
            )->andWhere(['status'=>Material::STATUS_ACTIVE])->all();

            $has = [];
            foreach($local_image_material_list as $image_material) {
                $has[$image_material['media_id']] = $image_material;
            }

            foreach($image_list as $image) {

                $media_id = $image['media_id'];

                if(isset($has[$media_id])) {
                    // TODO 考虑更新时间不一致的情况，有可能是线上更新了，本地没更新，需要更新本地的资源
                    continue;
                }

                // 拉封面到本地
                // 上传到阿里云
                // 上传到微信(unsupported)
                $cover_url= $this->_construct_source_url($image['media_id'], $company_id, $official_account_id,$wechat,$image['url'],$user_id); // FIX 部分ext可能有问题，要跟下

//                var_dump($cover_url); continue;

                if(!isset($cover_url['image_url'])){
                    echo $image['url']."\n";
                }

                if($image['url'] && isset($cover_url['image_url'])) {
                    echo "save-->image_url=".$cover_url['image_url']."\n";

                    // TODO 塞到后台
                    $transaction = $db->beginTransaction();

                    $material = new Material();

                    $material->type = Material::MATERIAL_TYPE_IMAGE;
                    $material->media_id = $image['media_id'];
                    $material->official_account_id = $official_account_id;
                    $material->status = Material::STATUS_ACTIVE;

                    $material->user_id = $user_id;
                    $material->created_from = Material::CREATED_FROM_WECHAT;
                    $material->weixin_source_url = $image['url'];

                    $material->source_url = $cover_url['image_url'];
                    $material->created_at = $image['update_time'];

                    $is_saved = $material->save(false);

                    if(!$is_saved) {
                        $transaction->rollBack();
                        $err_msg = sprintf('Fail to store image material cos reason:%s', $material->errors);
                        Yii::error($err_msg);
                        continue;
                    }

                    $transaction->commit();
                }
            }

            echo "save image successful official_account(".$official_account_id.")\n";
            $transaction->commit();

        } catch(\Exception $e) {
            // TODO
            echo sprintf("Fail to sync material image cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s"));
            Yii::error(sprintf("Fail to sync material image cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s")), __METHOD__);
            $transaction->rollBack();
        }
    }

    private function _syc_article_from_wechat($wechat,$offset, $count, $official_account_id,$user_id, $company_id) {

        $type = "news";

//        $wechat = WechatHelper::getWechat($official_account_id);

        $lists = $wechat->material->lists($type, $offset, $count);

        $news_list = $lists['item'];

        if(empty($news_list)) {
            return null;
        }

        $media_id_list = Utils::getSubByKey($news_list, "media_id");
        $local_news_material_list = Material::find()->andWhere(
            ["in", "media_id", $media_id_list]
        )->andWhere(["status"=>Material::STATUS_ACTIVE])->all();

        $has = [];
        foreach($local_news_material_list as $news_material) {
            $has[$news_material['media_id']] = $news_material;
        }

        try{

            foreach($news_list as $news) {
                $media_id = $news['media_id'];
                echo sprintf("the num of material components is %d \n", count($news['content']['news_item']));
                if(isset($has[$media_id])) {
                    echo sprintf("material(%s) has aleady downloaded to local. \n", $media_id);
                    Yii::warning(sprintf("material(%s) has aleady downloaded to local. \n", $media_id));
                    // TODO 考虑更新时间不一致的情况，有可能是线上更新了，本地没更新，需要更新本地的资源
                    continue;
                }

                $article = array_shift($news['content']['news_item']);
//                var_dump($article);exit;
//                var_dump($article);exit;
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();

                try {

                    // construct parent article
                    echo sprintf("Going to get img with media id: %s. \n", $article['thumb_media_id']);
                    Yii::info(sprintf("Going to get img with media id: %s. \n", $article['thumb_media_id']));

                    $cover_url = $this->_construct_source_url($article['thumb_media_id'], $company_id, $official_account_id,$wechat,$article['thumb_url'],$user_id);
//                    var_dump($cover_url);exit;

                    $order = 0;

                    $created_at = $news['update_time'];

                    if (Yii::$app->db->isActive) {
                        $parent_material = Material::storeWechatArticle($article, $official_account_id, $user_id,
                            $media_id, null, $cover_url['image_url'], $order, $created_at);
//                        var_dump($parent_material);exit;
                        if(!$parent_material) {
                            $transaction->rollBack();
                            echo sprintf("Fail to store wechat article with info(%s). \n", $parent_material->getErrors());
                            Yii::error(sprintf("Fail to store wechat article with info(%s).", $parent_material->getErrors()));
                            continue;
                        }
                    } else {
                        continue;
                    }

                    $is_all_updated = true;

                    foreach($news['content']['news_item'] as $article) {
//                        var_dump($article);exit;
                        $article['parent_id'] = $parent_material->id;
                        Yii::info(sprintf("Going to get img with media id: %s.", $article['thumb_media_id']));
                        $cover_url = $this->_construct_source_url($article['thumb_media_id'], $company_id, $official_account_id,$wechat,$article['thumb_url'],$user_id);
                        $order = $order + 1;
                        if(Yii::$app->db->isActive){
                            $material = Material::storeWechatArticle($article, $official_account_id, $user_id, null,
                                $parent_material->id, $cover_url['image_url'], $order, $created_at);
                            if(!$material) {
                                $is_all_updated = false;
                                $transaction->rollBack();
                                echo sprintf("Fail to store wechat article with info(%s) \n", $material->getErrors());
                                Yii::error(sprintf("Fail to store wechat article with info(%s) \n", $material->getErrors()));
                                break;
                            }
                        }
                    }

                    if($is_all_updated) {
                        if($transaction->isActive){
                            $transaction->commit();
                        }

                    } else {
                        $transaction->rollBack();
                    }

                } catch(\Exception $e) {
                    $err_msg = sprintf("Fail to sync material cos reaons(%s) at (%s) and (%s). \n", $e->getMessage(),$e->getLine(),$e->getFile());
//                    echo $err_msg;
                    Yii::error($err_msg);
//                    $transaction->rollBack();
                    continue;
                }
            }

        } catch (\Exception $e) {
            echo sprintf("Fail to store wechat article cos reason(%s) \n", $e->getMessage());
            Yii::error(sprintf("Fail to store wechat article cos reason(%s)", $e->getMessage()));
            return true;
        }
        return true;
    }

    // TODO 考虑更多的兼容性
    private function _construct_source_url($media_id, $company_id, $official_account_id,$wechat,$weixin_img_url,$user_id) {
        return Material::constructSourceUrl($media_id, $company_id, $official_account_id, $wechat,$weixin_img_url,$user_id);
    }



    // ------- 统计分析模块的方法 ------

    /**
     * 检测本地是否存在统计数据，如果存在，跳过，如果不存在，向微信端拉去数据到本地.
     *
     * @return array
     */
    //1489420800
    private function _checkData($official_account_id,$type){
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
        if($type == 'fans'){
            $model = new StatisticUser();
            $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday_7,$yesterday_7+86400])->one();
            if(!$res){
                Yii::info(sprintf("success to save all fans statistic data of(%s)",$official_account_id),__METHOD__);
                for($i=0;$i<6;$i++) {
                    $from = date("Y-m-d", strtotime("-" . (7 * $i + 7) . " day"));
                    $to = date("Y-m-d", strtotime("-" . (7 * $i + 1) . " day"));
                    $FansData = $this->_getData($official_account_id, 'fans', $from, $to);
                    $this->_saveData($FansData, null, 'fans');
                }
            }else{
                $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday,$yesterday+86400])->one();
                Yii::info("yesterday".\GuzzleHttp\json_encode($res),__METHOD__);
                if(!$res){
                    Yii::info(sprintf("success to save yesterday fans statistic data of(%s) \n",$official_account_id),__METHOD__);
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'fans',$from, $to);
                    if(empty($yesterdayData)){
                        Yii::info(sprintf("yesterday fans data is empty for (%s)",$official_account_id),__METHOD__);
                        return false;
                    }
                    $this->_saveData($yesterdayData,null,'fans');
                }
            }
        }
        else{
            $model = new StatisticNews();
            $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday_7,$yesterday_7+86400])->one();

            if(!$res){
                Yii::info("all".\GuzzleHttp\json_encode($res),__METHOD__);
                for($i=0;$i<15;$i++){
                    Yii::info(sprintf("success to save yesterday news statistic data of(%s) \n",$official_account_id),__METHOD__);
                    $from = date("Y-m-d",strtotime("-".(3*$i+3)." day"));
                    $to = date("Y-m-d",strtotime("-".(3*$i+1)." day"));
                    $NewsData = $this->_getData($official_account_id,'news',$from,$to);
                    $this->_saveData(null,$NewsData,'news');
                }
            }else{
                $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday,$yesterday+86400])->one();
                Yii::info("yesterday".\GuzzleHttp\json_encode($res),__METHOD__);
                if(!$res){
                    Yii::info(sprintf("success to save yesterday news statistic data of(%s) \n",$official_account_id),__METHOD__);
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'news',$from,$to);
                    if(empty($yesterdayData)){
                        Yii::info(sprintf("yesterday news data is empty for (%s)",$official_account_id),__METHOD__);
                        return false;
                    }
                    $this->_saveData(null,$yesterdayData,'news');
                }
            }
        }
        return true;

    }

    private function _checkArticleData($official_account_id){
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));

        $model = new StatisticNew();
        $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday_7,$yesterday_7+86400])->one();

        if(!$res){
            for($i=1;$i<=15;$i++) {
                $articleData = $this->_getArticleData($official_account_id,$i);

                $this->_saveArticleData($articleData);
            }
        }else{
            $res = $model->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday,$yesterday+86400])->one();
            if(!$res){
                $FansData = $this->_getArticleData($official_account_id,1);
                $this->_saveArticleData($FansData);
            }
        }

    }

    /**
     * 获取微信端统计数据
     *
     * @return array
     */
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

            foreach($userCumulate['list'] as $cumulate){
                $new_user = 0;
                $cancel_user = 0;
                foreach($userSummary['list'] as $summary){
                    if($cumulate['ref_date'] == $summary['ref_date']){
                        $new_user += $summary['new_user'];
                        $cancel_user += $summary['cancel_user'];
                        $ref_date = $summary['ref_date'];
                    }
                }
                $data[] = [
                    $official_account_id,
                    strtotime($cumulate['ref_date']),
                    $cumulate['user_source'],
                    $new_user,
                    $cancel_user,
                    $cumulate['cumulate_user'],
                    time()
                ];

            }
//var_dump($data);exit;
            return $data;
        }else{
            $data = [];
            $userReadSummary = $stats->userReadSummary($from, $to)->toArray();
            $userSource = [0,1,2,4,5];
            $ref_date = [];
            $news = [];
            $datass = [];
            foreach($userReadSummary['list'] as $k=>$v){
                $ref_date[$v['ref_date']][] = $v['user_source'];
            }

            foreach($ref_date as $k=>$v){
                $dif = array_diff($userSource,$v);
                if(count($dif) > 0 ){
                    foreach($dif as $kk =>$vv){
                        $news[$k][] = $vv;
                    }
                }
            }
            foreach($news as $k =>$v){
                foreach($v as $kk =>$vv){
                    $datass[] = [
                        $official_account_id,
                        strtotime($k),
                        $vv,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        time()
                    ];
                }

            }
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
            $insert_data = array_merge($data,$datass);
            return $insert_data;
        }

    }

    /**
     * 保存数据
     *
     * @return true
     */
    private function _saveData($FansData=null,$newsData=null,$type){
//        var_dump($FansData);exit;
        Yii::info(sprintf("start to save data"),__METHOD__);
        $db = Yii::$app->db->createCommand();
        if($type == 'fans'){
            $db->batchInsert(StatisticUser::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'user_source',
                    'new_user',
                    'cancel_user',
                    'cumulate_user',
                    'created_at'], $FansData)
                ->execute();
        }else{
            $db->batchInsert(StatisticNews::tableName(), [
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
        $sql = $db->getSql();
        Yii::info(sprintf("the sql (%s)",$sql),__METHOD__);

    }

    private function _getArticleData($official_account_id,$day){

        $wechat = WechatHelper::getWechat($official_account_id);
        if(!$wechat)  {
            Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
        }
        $stats = $wechat->stats;
        $from = date("Y-m-d",strtotime("-".$day." day"));

//        $articleSummary = $stats->articleSummary($from,$to);
        $articleTotal = $stats->articleTotal($from,$from)->toArray();
        if(count($articleTotal['list'])>0){
            $data = [];
            foreach ($articleTotal['list'] as $list){
                foreach($list['details'] as $details){
                    $data[] = [
                        $official_account_id,
                        strtotime($list['ref_date']),
                        strtotime($details['stat_date']),
                        $list['msgid'],
                        $list['title'],
                        $list['user_source'],
                        $details['target_user'],
                        $details['int_page_read_user'],
                        $details['int_page_read_count'],
                        $details['ori_page_read_user'],
                        $details['ori_page_read_count'],
                        $details['share_user'],
                        $details['share_count'],
                        $details['add_to_fav_user'],
                        $details['add_to_fav_count'],
                        $details['int_page_from_session_read_user'],
                        $details['int_page_from_session_read_count'],
                        $details['int_page_from_hist_msg_read_user'],
                        $details['int_page_from_hist_msg_read_count'],
                        $details['int_page_from_feed_read_user'],
                        $details['int_page_from_feed_read_count'],
                        $details['int_page_from_friends_read_user'],
                        $details['int_page_from_friends_read_count'],
                        $details['int_page_from_other_read_user'],
                        $details['int_page_from_other_read_count'],
                        $details['feed_share_from_session_user'],
                        $details['feed_share_from_session_cnt'],
                        $details['feed_share_from_feed_user'],
                        $details['feed_share_from_feed_cnt'],
                        $details['feed_share_from_other_user'],
                        $details['feed_share_from_other_cnt'],
                        time(),
                    ];

                }
            }
            return $data;
        }
        return [];


    }

    private function _saveArticleData($data){
        if(count($data) > 0){
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticNew::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'stat_date',
                    'msgid',
                    'title',
                    'user_source',
                    'target_user',
                    'int_page_read_user',
                    'int_page_read_count',
                    'ori_page_read_user',
                    'ori_page_read_count',
                    'share_user',
                    'share_count',
                    'add_to_fav_user',
                    'add_to_fav_count',
                    'int_page_from_session_read_user',
                    'int_page_from_session_read_count',
                    'int_page_from_hist_msg_read_user',
                    'int_page_from_hist_msg_read_count',
                    'int_page_from_feed_read_user',
                    'int_page_from_feed_read_count',
                    'int_page_from_friends_read_user',
                    'int_page_from_friends_read_count',
                    'int_page_from_other_read_user',
                    'int_page_from_other_read_count',
                    'feed_share_from_session_user',
                    'feed_share_from_session_cnt',
                    'feed_share_from_feed_user',
                    'feed_share_from_feed_cnt',
                    'feed_share_from_other_user',
                    'feed_share_from_other_cnt',
                    'created_at'], $data)
                ->execute();
        }

    }

    private function _checkMenuData($official_account_id){

        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
        $statistciMenu = new StatisticMenu();

        $res = $statistciMenu->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday_7,$yesterday_7+86400])->one();
        $data = [];
        if($res){
            $res = $statistciMenu->find()->select(['id'])->where(['official_account_id'=>$official_account_id])->andWhere(['between','ref_date',$yesterday,$yesterday+86400])->one();
            if(!$res){
                $db = Yii::$app->db;
                $raw_sql = sprintf("SELECT COUNT(1) AS `number`, `key`,`click_user`  FROM `statistic_menu_log` WHERE ".
                    "`official_account_id`= %d and `created_at` BETWEEN %d and %d GROUP BY `key`;", $official_account_id,$yesterday,$yesterday+86400);
                $click_count = $db->createCommand($raw_sql)->queryAll();

                if(!isset($click_count['0']['number'])){

                }else{
                    $raw_sql = sprintf("SELECT `key`,`click_user`  FROM `statistic_menu_log` WHERE ".
                        "`official_account_id`= %d and `created_at` BETWEEN %d and %d GROUP BY `key`,`click_user`;", $official_account_id,$yesterday,$yesterday+86400);
                    $click = $db->createCommand($raw_sql)->queryAll();
                    $exist_name = [];
                    foreach($click_count as $count){
                        $user=0;
                        foreach ($click as $k=>$v){
                            if($v['key'] == $count['key']){
                                $user++;
                            }
                        }

                        $menu_info = Menus::find()
                            ->where(['official_account_id'=>$official_account_id,'key'=>$count['key']])->asArray()->one();
                        if(!$menu_info){
                            $menu_info = Menus::find()
                                ->where(['official_account_id'=>$official_account_id,'url'=>$count['key']])->asArray()->one();
                            if(!$menu_info){
                                continue;
                            }
                        }

                        $data [] = [
                            $official_account_id,$user,(int)$count['number'],$yesterday,$this->_preMenuName($menu_info['name'],$official_account_id),time()
                        ];
                        $exist_name [] = $menu_info['name'];

                    }
                }
            }
        }

        if($data) {
            Yii::$app->db->createCommand()
                         ->batchInsert(StatisticMenu::tableName(), [
                             'official_account_id',
                             'click_user',
                             'click_count',
                             'ref_date',
                             'menus_name',
                             'created_at',
                         ], $data)
                         ->execute();
        }

        return true;
    }

    private function _truncateTable(){
        $db = Yii::$app->db;
        $tables = ['menus','menus_news','reply','reply_news','statistic_menu','statistic_menu_log','statistic_user','statistic_news','statistic_new'];
        foreach ($tables as $table){
            $raw_sql = sprintf("TRUNCATE TABLE %s;", $table);
            $db->createCommand($raw_sql)->query();
        }
    }

    private function _preMenuName($name,$official_account_id){
        $model = new Menus();
        $res  = $model->find()->where(['official_account_id'=>$official_account_id,'name'=>$name])->andWhere(['<>','parent_id',0])->one();
        if($res){
            $res_return = $model->find()->where(['official_account_id'=>$official_account_id,'id_s'=>$res->parent_id])->one();
            return $res_return->name."-".$res->name;
        }else{
            return $name;
        }

    }



    /*微信菜单保存*/
    public function _saveCurrentMenu($button_list,$official_account_id){
//        var_dump($button_list);exit;
        $rows = [];
        $news_data = [];
        foreach($button_list as $k=>$v){
            $url = empty(isset($v['url'])?$v['url']:null)?null:$v['url'];
            $key = empty(isset($v['key'])?$v['key']:null)?null:$v['key'];
            $media_id = empty(isset($v['media_id'])?$v['media_id']:null)?null:$v['media_id'];
            $type = isset($v['type'])?$v['type']:'none';
            switch ($type){
                case 'click':
                    $media_id = $v['media_id'];
                    $msg_type = $type;
                    $type = 'click';
                    break;
                case 'none':

                    break;
                case 'text':
                    $value = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;
                case 'view':
                    $url = $v['url'];
                    $msg_type = $type;
                    break;
                case 'img':
                    $media_id = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;
                case 'news' || 'video' || 'voice':
                    $media_id = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;

            }
            $rows[] = [$official_account_id,0,$k+1,$v['name'],$type,$key,$url,$k,time(),$media_id,isset($value)?$value:'',isset($msg_type)?$msg_type:0];

            if(isset($v['type']) && ($v['type'] == 'news')){

                foreach($v['news_info']['list'] as $news_info){
                    $news_data[] = [
                        $official_account_id,$media_id,'news',$news_info['title'],
                        $news_info['author'],$news_info['digest'],$news_info['show_cover'],
                        $news_info['cover_url'],$news_info['content_url'],$news_info['source_url'],
                    ];
                }
            }

            if(!empty($v['sub_button']['list'])){
                foreach($v['sub_button']['list'] as $k1=>$v1){
                    $url1 = empty(isset($v1['url'])?$v1['url']:null)?null:$v1['url'];
                    $key1 = empty(isset($v1['key'])?$v1['key']:null)?null:$v1['key'];
                    $media_id_sub = empty(isset($v1['media_id'])?$v1['media_id']:null)?null:$v1['media_id'];
                    $type_sub = isset($v1['type'])?$v1['type']:'none';
                    switch ($type_sub){
                        case 'none':

                            break;
                        case 'text':
                            $value_sub = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;
                        case 'view':
                            $url1 = $v1['url'];
                            $msg_type = $type_sub;
                            break;
                        case 'img':
                            $media_id_sub = $v1['value'];
                            $key1 = $this->createRandomStr(3).time();
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            break;
                        case 'news' || 'video' || 'voice':
                            $media_id_sub = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;

                    }
                    $rows[] = [$official_account_id,$k+1,0,$v1['name'],$type_sub,$key1,$url1,$k,time(),$media_id_sub,isset($value_sub)?$value_sub:'',isset($msg_type_sub)?$msg_type_sub:0];
                    if(isset($v1['type']) && ($v1['type'] == 'news')){
                        foreach($v1['news_info']['list'] as $news_info){
                            $news_data[] = [
                                $official_account_id,$media_id_sub,'news',$news_info['title'],
                                $news_info['author'],$news_info['digest'],$news_info['show_cover'],
                                $news_info['cover_url'],$news_info['content_url'],$news_info['source_url'],time()
                            ];
                        }
                    }
                }
            }
        }

        $db = Yii::$app->db->createCommand();
//        var_dump($news_data);exit;
        $db ->delete(Menus::tableName(),['official_account_id'=>$official_account_id])->execute();
        $db ->batchInsert(Menus::tableName(),
            [
                'official_account_id',
                'parent_id',
                'id_s',
                'name',
                'type',
                'key',
                'url',
                'sort',
                'created_at',
                'media_id',
                'value',
                'msg_type'
            ], $rows)->execute();

        $db ->delete('menus_news',['account_id'=>$official_account_id])->execute();
        $db ->batchInsert('menus_news',
            [
                'account_id',
                'media_id',
                'type',
                'title',
                'author',
                'digest',
                'show_cover',
                'cover_url',
                'content_url',
                'source_url',
                'created_at'
            ], $news_data)->execute();
    }

    public function createRandomStr($length){
        $str = array_merge(range(0,9),range('a','z'),range('A','Z'));
        shuffle($str);
        $str = implode('',array_slice($str,0,$length));
        return $str;
    }



    /*获取微信端自动回复数据*/
    /*
     * 获取微信端自动回复内容
     * */
    private function getWxReplyData($official_account_id,$wechat){
        $news_data = [];

        $data_list = $wechat->reply->current()->toArray();
//        var_dump($data_list);exit;

        $reply_auto_add_type = null;
        $reply_auto_add_media_id = null;
        $reply_auto_add_content = null;
        if(isset($data_list['add_friend_autoreply_info']['type']) &&  $data_list['add_friend_autoreply_info']['type']== 'text'){
            $reply_auto_add_type = 5;
            $reply_auto_add_media_id = null;
            $reply_auto_add_content = $data_list['add_friend_autoreply_info']['content'];
        }elseif (isset($data_list['add_friend_autoreply_info']['type']) && $data_list['add_friend_autoreply_info']['type']== 'news'){
            $reply_auto_add_type = 1;
            $reply_auto_add_media_id = $data_list['add_friend_autoreply_info']['content'];

            foreach ($data_list['add_friend_autoreply_info']['news_info']['list'] as $new_info){
                $news_data[] = [
                    $official_account_id,$reply_auto_add_media_id,'news',$new_info['title'],
                    $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                    $new_info['content_url'],$new_info['source_url'],time()
                ];
            }
        }elseif ($data_list['add_friend_autoreply_info']['type'] == 'img'){
            $reply_auto_add_type = 2;
            $reply_auto_add_media_id = $data_list['add_friend_autoreply_info']['content'];
        }
        $data[] = [$official_account_id,0,$reply_auto_add_type,$reply_auto_add_media_id,$reply_auto_add_content,time(),null,null];


        $reply_default_type_msg = null;
        $reply_default_msg_media_id = null;
        $reply_default_msg_content = null;
        if(isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'text'){
            $reply_default_type_msg = 5;
            $reply_default_msg_content = $data_list['message_default_autoreply_info']['content'];
        }elseif (isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'news'){
            $reply_default_type_msg = 1;
            $reply_default_msg_media_id = $data_list['message_default_autoreply_info']['content'];

            foreach ($data_list['message_default_autoreply_info']['news_info']['list'] as $new_info){
                $news_data[] = [
                    $official_account_id,$reply_default_msg_media_id,'news',$new_info['title'],
                    $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                    $new_info['content_url'],$new_info['source_url'],time()
                ];
            }

        }elseif (isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'img'){
            $reply_default_type_msg = 2;
            $reply_default_msg_media_id = $data_list['message_default_autoreply_info']['content'];
        }
        $data[] = [$official_account_id,1,$reply_default_type_msg,$reply_default_msg_media_id,$reply_default_msg_content,time(),null,null];

        if(isset($data_list['keyword_autoreply_info']['list']) && count($data_list['keyword_autoreply_info']['list']) !==0 ){
            foreach($data_list['keyword_autoreply_info']['list'] as $k=>$v){
                $keyword_autoreply_type = null ;
                $keyword_autoreply_media_id = null;
                $keyword_autoreply_content = null;
                if($v['reply_list_info']['0']['type'] == 'text'){
                    $keyword_autoreply_type = 5;
                    $keyword_autoreply_content = $v['reply_list_info']['0']['content'];
                }elseif ($v['reply_list_info']['0']['type'] == 'news'){
                    $keyword_autoreply_type = 1;
                    $keyword_autoreply_media_id = $v['reply_list_info']['0']['content'];
                    if(isset($v['reply_list_info']['0']['news_info']['list'])){
                        foreach ($v['reply_list_info']['0']['news_info']['list'] as $new_info){
                            $news_data[] = [
                                $official_account_id,$keyword_autoreply_media_id,'news',$new_info['title'],
                                $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                                $new_info['content_url'],$new_info['source_url'],time()
                            ];
                        }
                    }

                }elseif ($v['reply_list_info']['0']['type'] == 'img'){
                    $keyword_autoreply_type = 2;
                    $keyword_autoreply_media_id = $v['reply_list_info']['0']['content'];
                }
                $keyword = '';
                foreach($v['keyword_list_info'] as $kk => $vv){
                    $keyword .= $vv['content']." ";
                }
                $keyword = rtrim($keyword);
                $rule_name = $v['rule_name'];
                $data[] = [$official_account_id,2,$keyword_autoreply_type,$keyword_autoreply_media_id,$keyword_autoreply_content,time(),$keyword,$rule_name];
            }
        }
        return ["data"=>$data,"new_data"=>$news_data];

    }

    /*
     * 保存信息到数据库中
     * */
    private function saveData($data){
        Yii::$app->db->createCommand()
            ->batchInsert(Reply::tableName(), ['account_id','type_reply','type_msg','wx_media_id','content','created_at','keyword','rule'], $data['data'])
            ->execute();

        Yii::$app->db->createCommand()
            ->batchInsert(ReplyNews::tableName(), ['account_id','media_id','type','title','author','digest','show_cover','cover_url','content_url','source_url','created_at'], $data['new_data'])
            ->execute();
        return true;
    }

}