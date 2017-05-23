<?php
namespace console\controllers;

use common\models\Menus;
use common\models\OfficialAccount;
use common\models\Reply;
use common\models\ReplyNews;
use common\models\User;
use yii\helpers\Console;
use Yii;

use udokmeci\yii2beanstalk\BeanstalkController;

use common\helpers\Utils;
use common\models\Material;
use common\helpers\WechatHelper;

use common\models\StatisticNew;
use common\models\StatisticNews;
use common\models\StatisticUser;


class WorkerController extends BeanstalkController
{
    // Those are the default values you can override
    const DELAY_PRIORITY = "1000"; // Default priority

    const DELAY_TIME = 5; // Default delay time

    // Used for Decaying. When DELAY_MAX reached job is deleted or delayed with
    const DELAY_MAX = 3;

    // public $wechat;
    public function init() {
        ini_set('memory_limit', '512M');
        return parent::init();
    }

    public function listenTubes() {
        return [
            "sync_image_list",
            "once_sync_image_list",
            "sync_statistic_data",
            "sync_menus_data",
            "sync_reply_data",
            "others",
        ];
    }

    public function actionOnce_sync_image_list($job) {

        try {

            // ini_set('memory_limit', '512M');

            $sentData = $job->getData();

            // trust data
            $official_account_id = $sentData->official_account_id;

            $db = Yii::$app->db;
            // TODO
            $raw_sql = sprintf("select * from official_account where id = %d and status = %d for update;",
                $official_account_id,OfficialAccount::STATUS_ACTIVE);
            $official_account = $db->createCommand($raw_sql)->queryOne();

            if (!$official_account) {
                $error_msg = sprintf("Fail to get the official account with id (%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));
                Yii::warning($error_msg, __METHOD__);
            }

            try {

                $user_id = User::getUserIdByCompanyId($official_account['company_id']);

                $wechat = WechatHelper::getWechat($official_account['id']);

                //获取素材总数 voice_count  video_count  image_count  news_count
                try{
                    $total_count = $wechat->material->stats();
                }catch (\Exception $e){
                    $error_msg = sprintf("official_account(%d)：--wechat error (%s) \n", $official_account['id'],$e->getMessage());
                    Yii::info($error_msg,__METHOD__);
                    return self::DELETE;
                }

                //同步图片素材到本地
                if($total_count['image_count'] > 0){

                    $count = 10;

                    $transaction = $db->beginTransaction();
                    $total_save = $total_count['image_count'] > 20 ? 20 : $total_count['image_count'];
                    for($offset=0;$offset<$total_save;$offset =$offset+$count ){
                        $this->_syc_image_from_wechat($wechat,$offset, $count, $official_account['id'], $user_id,$official_account['company_id']);
                    }

                    $transaction->commit();
                    $error_msg = sprintf("official_account(%d):--sync image successful.\n", $official_account['id']);
                    Yii::info($error_msg,__METHOD__);
                }

                return self::DELETE;

            } catch (\Exception $e) {
                $transaction->rollBack();
                $error_msg = sprintf("Fail to get the official account list with id list(%s) at %s.\n", $official_account['id'], date("Y-m-d H:i:s"));
                Yii::error($error_msg, __METHOD__);
                return self::BURY;
            }
        }catch (\Exception $e) {
            $err_msg = sprintf("Fail to sync material to wechat cos reason:%s", $e->getMessage());
            Yii::error($err_msg, __METHOD__);
            return self::BURY;
        }

    }

    // TODO 批量同步公众号的前20条图文素材

    public function actionSync_image_list($job) {

        try {

            // ini_set('memory_limit', '512M');
            $sentData = $job->getData();

            Yii::info("excuse sync image list at official_account ".$sentData->official_account_id."\n", __METHOD__);

            // trust data
            $official_account_id = $sentData->official_account_id;
            // $page = $sentData['page'];
            // $num = $sentData['num'];
            // $num = 20; // 固定拉取20条
            $num = $sentData->num;
            $type = 'image';
            // $page = $this->_calcPageNum($sentData->page);
            $page = $sentData->page;
            $offset = max($page-1, 0) * $num;

            $official_account = OfficialAccount::findById($official_account_id);

            $user_id = User::getUserIdByCompanyId($official_account['company_id']);

            // construct wechat
            $wechat = WechatHelper::getWechat($official_account_id);

            $lists = $wechat->material->lists($type, $offset, $num);
           // var_dump($lists);exit;

            $image_list = $lists['item'];

            // no need to continue
            if(empty($image_list)) {
                $msg = sprintf("No need to sync material to wechat cos empty news list with official_account_id(%s)/offset(%s)/page(%s)/type(%s)",
                    $official_account_id,
                    $offset,
                    $page,
                    $type
                );
                Yii::info($msg, __METHOD__);
                return self::DELETE; //Deletes the job from beanstalkd
            }

            // TODO 如何同步删除事件，需要设计更好的同步算法，或者像微小宝那种，通过模拟微信请求的方式，来解决
            // 这种方法太过独特，暂时没精力投入.=)

            $media_id_list = array_column($image_list, "media_id");
            $local_image_material_list = Material::find()->andWhere(
                ["in", "media_id", $media_id_list]
            )->andWhere(["status"=>Material::STATUS_ACTIVE])->andWhere(
                ["official_account_id"=>$official_account_id]
            )->all();

            // 更新最新的素材 或者 插入新素材
            $has = [];
            foreach($local_image_material_list as $news_material) {
                $has[$news_material['media_id']] = $news_material;
            }

            // 本地同步更新算法
            try{

                foreach($image_list as $image) {

                    try {

                        $media_id = $image['media_id'];

                        if(isset($has[$media_id])) { //本地有素材，

                            Yii::warning(sprintf("material(%s) has aleady downloaded to local.\n",
                                $media_id),
                                __METHOD__);

                            continue;
                        }

                        // 插入新素材也有可能会出现竞态，数据库唯一性导致的抛错也会出现
                        $db = Yii::$app->db;

                        // $transaction = $db->beginTransaction();
                        // Yii::info("save-->image_url=".$cover_url['image_url']."\n", __METHOD__);

                        // TODO 塞到后台
                        $material = new Material();

                        $material->type = Material::MATERIAL_TYPE_IMAGE;
                        $material->media_id = $image['media_id'];
                        $material->official_account_id = $official_account_id;
                        $material->status = Material::STATUS_ACTIVE;

                        $material->user_id = $user_id;
                        $material->created_from = Material::CREATED_FROM_WECHAT;
                        $material->weixin_source_url = $image['url'];

                        // $material->source_url = $cover_url['image_url'];
                        $material->source_url = '';
                        $material->created_at = $image['update_time'];

                        $is_saved = $material->save(false);

                        if(!$is_saved) {
                            // $transaction->rollBack();
                            $err_msg = sprintf('Fail to store image material cos reason:%s', $material->errors);
                            Yii::error($err_msg, __METHOD__);
                            continue;
                        }

                        Yii::info(sprintf("success to update image on official_account(%d) at date(%s)",
                                          $official_account_id, date("Y-m-d H:i:s")), __METHOD__);

                        // $transaction->commit();

                    } catch(\Exception $e) {
                        $err_msg = sprintf("Fail to sync material cos reaons(%s) at (%s) and (%s). \n",
                            $e->getMessage(),
                            $e->getLine(),
                            $e->getFile());
                        Yii::error($err_msg, __METHOD__);
                        // $transaction->rollBack();
                        continue;
                    }
                }

                unset($image_list);

                return self::DELETE;

            } catch (\Exception $e) {
                Yii::error(sprintf("Fail to store wechat article cos reason(%s)",
                    $e->getMessage()), __METHOD__);
                return self::BURY;
            }

        } catch (\Exception $e) {
            $err_msg = sprintf("Fail to sync material to wechat cos reason:%s", $e->getMessage());
            Yii::error($err_msg, __METHOD__);
            return self::BURY;
        }

    }

    public function actionSync_statistic_data($job) {
        try {

            $sentData = $job->getData();

            // trust data
            $official_account_id = $sentData->official_account_id;

            Yii::info("excuse sync statistic data at official_account ".$sentData->official_account_id."\n", __METHOD__);

            $this->_checkData($official_account_id,'news');

            $this->_checkData($official_account_id,'fans');

            $this->_checkArticleData($official_account_id);

            return self::DELETE;

        } catch (\Exception $e) {
            $err_msg = sprintf("Fail to sync wechat material to local cos reason:%s", $e->getMessage());
            Yii::error($err_msg, __METHOD__);
            return self::BURY;
        }
    }

    public function actionSync_menus_data($job) {

        try {

            $sentData = $job->getData();

            // trust data
            $official_account_id = $sentData->official_account_id;

            Yii::info("excuse sync menus data at official_account ".$sentData->official_account_id."\n", __METHOD__);

            $menu_list = Menus::find()->where(['official_account_id'=>$official_account_id])->asArray()->all();
            if(!$menu_list){

                $wechat = WechatHelper::getWechat($official_account_id);
                if(!$wechat) {
                    Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
                }
                $current_list = $wechat->menu->current();

                $this->_saveCurrentMenu($current_list->selfmenu_info['button'],$official_account_id);
            }

            return self::DELETE;

        } catch (\Exception $e) {
            $err_msg = sprintf("Fail to sync wechat material to local cos reason:%s", $e->getMessage());
            Yii::error($err_msg, __METHOD__);
            return self::BURY;
        }
    }

    public function actionSync_reply_data($job) {
        try {

            $sentData = $job->getData();

            // trust data
            $official_account_id = $sentData->official_account_id;

            Yii::info("excuse sync reply data at official_account ".$sentData->official_account_id."\n", __METHOD__);

            $wx_data = $this->getWxReplyData($official_account_id);

            $this->saveData($wx_data);

            return self::DELETE;

        } catch (\Exception $e) {
            $err_msg = sprintf("Fail to sync reply data to local cos reason:%s", $e->getMessage());
            Yii::error($err_msg, __METHOD__);
            return self::BURY;
        }

    }

    public function actionOthers($job) {
        $sentData = $job->getData();
        Yii::info("test \n", __METHOD__);
        $msg = sprintf("test send official_account_id:%d \n", $sentData->official_account_id);
        Yii::error($msg, __METHOD__);
        return self::DELETE;
    }

    // ------- inner helper funcs ------

    // TODO 拿到业务逻辑层去
    private function _calcPageNum($page) {

        // 这个取整算法，跟前端的获取条数有关
        // 目前算法是写死的，即前端拉取的条数永远是8条，这样就能保证后端每次去拉取20条（最多）的时候，
        // 都能比前端拉取的拿到更多，达到素材全部同步下来的效果

        return max(ceil($page/2) -1, 0);
    }

    private function _updateLocalNewsMaterial($remote_item, $local_parent_material) {

        try {

            // 插入新素材也有可能会出现竞态，数据库唯一性导致的抛错也会出现
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            // TODO select parent material for update, use transation here
            $raw_sql = sprintf("select * from material where media_id = '%s' and " .
                               "official_account_id = %d limit 1 for update;", $local_parent_material->media_id, $local_parent_material->official_account_id);

            $db->createCommand($raw_sql)->queryOne(); // just for lock

            $fake_local_article_list = Material::wechatItemToLocalArticleList($remote_item);

            $is_updated = Material::updateLocalArticleMaterial($local_parent_material, $fake_local_article_list, $remote_item['update_time']);

            if(!$is_updated) {
                Yii::error(sprintf('Fail to update local articles for material(%s).', $local_parent_material->id));
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();

        } catch (\Exception $e) {
            Yii::error(sprintf('Fail to update local articles cos reason(%s).', $e->getMessage()));
            $transaction->rollBack();
            return false;
        }

        return true;
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

                if($image['url']) {

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

                    $material->source_url = '';
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

            $transaction->commit();

        } catch(\Exception $e) {
            Yii::error(sprintf("Fail to sync material image cos reason cos (%s) at line(%d) and date(%s).\n", $e->getMessage(), $e->getLine() ,date("Y-m-d H:i:s")), __METHOD__);
            $transaction->rollBack();
        }
    }

    // ------- 统计分析模块的方法 ------

    /**
     * 检测本地是否存在统计数据，如果存在，跳过，如果不存在，向微信端拉去数据到本地.
     *
     * @return array
     */
    private function _checkData($official_account_id,$type){
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
        if($type == 'fans'){
            $model = new StatisticUser();
            $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
            if(!$res){
                for($i=0;$i<6;$i++) {
                    $from = date("Y-m-d", strtotime("-" . (7 * $i + 7) . " day"));
                    $to = date("Y-m-d", strtotime("-" . (7 * $i + 1) . " day"));
                    $FansData = $this->_getData($official_account_id, 'fans', $from, $to);
                    $this->_saveData($FansData, null, 'fans');
                }
            }else{
                $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
                if(!$res){
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'fans',$from, $to);
//                    var_dump($yesterdayData);exit;
                    $this->_saveData($yesterdayData,null,'fans');
                }
            }
        }
        else{
            $model = new StatisticNews();
            $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
            if(!$res){
                for($i=0;$i<15;$i++){
                    $from = date("Y-m-d",strtotime("-".(3*$i+3)." day"));
                    $to = date("Y-m-d",strtotime("-".(3*$i+1)." day"));
                    $NewsData = $this->_getData($official_account_id,'news',$from,$to);
                    $this->_saveData(null,$NewsData,'news');
                }
            }else{
                $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
                if(!$res){
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'news',$from,$to);
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
        $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
        if(!$res){
            for($i=1;$i<=15;$i++) {
                $articleData = $this->_getArticleData($official_account_id,$i);

                $this->_saveArticleData($articleData);
            }
        }else{
            $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
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


    /*微信菜单保存*/
    public function _saveCurrentMenu($button_list,$official_account_id){

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
    private function getWxReplyData($official_account_id){
        $news_data = [];

        $wechat = WechatHelper::getWechat($official_account_id);

        if(!$wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
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

        if(isset($data_list['keyword_autoreply_info']['list']) && count($data_list['keyword_autoreply_info']['list'])){
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