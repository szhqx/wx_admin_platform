<?php

namespace frontend\controllers;

use common\models\Fans;
use EasyWeChat\Broadcast\Broadcast;
use Yii;
use yii\web\Controller;
use yii\web\UrlManager;
use yii\helpers\Url;

use common\models\Mass;
use common\models\Material;
use common\models\MaterialForm;
use common\models\OfficialAccount;
use common\helpers\FileUtil;
use common\helpers\Utils;
use common\helpers\OssUtils;
use common\models\User;
use common\models\ArticleImageMap;

use EasyWeChat\Core\Exceptions\HttpException;

use PHPHtmlParser\Dom;


class MaterialController extends BaseController
{
    public $wechat;
    public $err_code = 0;
    public $extra_msg_list = [];

    /**
     * 创建素材.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('material/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 增加数据权限校验

        $post_content = Yii::$app->request->post();

        $if_has_right = $this->_checkIfOfficialAccountRight($post_content['official_account_id']);
        if(!$if_has_right) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $this->wechat = $this->getWechat($post_content['official_account_id']);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        try{

            switch($post_content['type']) {

            // case Material::MATERIAL_TYPE_ARTICLE:
            //     return $this->_uploadArticle($post_content);
            //     break;

            case Material::MATERIAL_TYPE_ARTICLE_MULTI:
                return $this->_uploadArticleMulti($post_content);
                break;

            case Material::MATERIAL_TYPE_TEMPLATE:
                return $this->_uploadArticleMulti($post_content);
                break;

                // 这个接口废掉，请不要使用
            case Material::MATERIAL_TYPE_IMAGE:
                return $this->_uploadImage($post_content);
                break;

            case Material::MATERIAL_TYPE_VOICE:
                return $this->_uploadVoice($post_content);
                break;

            case Material::MATERIAL_TYPE_VIDEO:
                return $this->_uploadVideo($post_content);
                break;

            case Material::MATERIAL_TYPE_COVER_IMAGE:
                return $this->_uploadThumb($post_content);
                break;

            case Material::MATERIAL_TYPE_ARTICLE_IMAGE:
                return $this->_uploadArticleImage($post_content);
                break;

            default:
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
                break;
            }

        } catch(HttpException $e) {
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }  catch (\Exception $e) {
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
    }

    /**
     *  删除素材（软删）
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('material/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 增加数据权限校验

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try{

            $post_content = Yii::$app->request->post();
            $material_id = $post_content['id'];

            // get material info
            $material_info = Material::findById($material_id);
            if(!$material_info) {
                $transaction->rollback();
                Yii::error(sprintf('Fail to delete material cos bad material id(%s) params.', $material_id));
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }

            // $official_account_info = OfficialAccount::findById($material_info['official_account_id']);
            // if(!$official_account_info) {
            //     $transaction->rollback();
            //     Yii::error(sprintf('Fail to delete material cos bad material id(%s) params.', $material_id));
            //     return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            // }

            // // check if user has the right to delete specific resources, may be not
            // if(Yii::$app->user->identity->company_id != $official_account_info['company_id']) {
            //     $transaction->rollback();
            //     Yii::error(sprintf('Fail to delete material cos bad material id(%s) params.', $material_id));
            //     return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            // }

            $if_has_right = $this->_checkIfOfficialAccountRight($material_info['official_account_id']);
            if(!$if_has_right) {
                $transaction->rollback();
                Yii::error(sprintf('Fail to delete material cos bad material id(%s) params.', $material_id));
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }

            $this->wechat = $this->getWechat($material_info['official_account_id']);

            $is_deleted = false;

            switch($material_info->type) {
            // case Material::MATERIAL_TYPE_ARTICLE:
            //     $this->_deleteArticle($material_info);
            //     break;

            case Material::MATERIAL_TYPE_ARTICLE_MULTI:
                $is_deleted = $this->_deleteArticleMulti($material_info);
                break;
            case Material::MATERIAL_TYPE_TEMPLATE:
                $is_deleted = $this->_deleteArticleMulti($material_info);
                break;

            case Material::MATERIAL_TYPE_IMAGE:
                $is_deleted = $this->_deleteImage($material_info);
                break;

            case Material::MATERIAL_TYPE_VOICE:
                $is_deleted = $this->_deleteVoice($material_info);
                break;

            case Material::MATERIAL_TYPE_VIDEO:
                $is_deleted = $this->_deleteVideo($material_info);
                break;

            case Material::MATERIAL_TYPE_COVER_IMAGE:
                $is_deleted = $this->_deleteThumb($material_info);
                break;

            case Material::MATERIAL_TYPE_ARTICLE_IMAGE:
                $is_deleted = $this->_deleteArticleImage($material_info);
                break;

            default:
                break;
            }

            if($is_deleted) {
                $transaction->commit();
            } else {
                $transaction->rollback();
            }

        } catch (\Exception $e)
        {
            $transaction->rollback();
            Yii::error(sprintf('Fail to delete material cos reason:(%s)', $e));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        return json_encode(["code"=>$this->err_code, "msg"=>$this->status_code_msg[$this->err_code]]);
    }

    /**
     *  编辑素材
     *
     * @return string
     */
    public function actionModify()
    {

        if(!Yii::$app->exAuthManager->can('material/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 增加数据权限校验
        $post_content = Yii::$app->request->post();

        $model = new MaterialForm();
        $model->scenario = MaterialForm::SCENARIO_MODIFY_ARTICLE;
        $article_content = ["MaterialForm"=>$post_content];

        if(!($model->load($article_content) && $model->validate())) {
            Yii::error(sprintf('Fail to modify material cos reason:(%s)', json_encode($model->errors)));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $official_account_id = $post_content['official_account_id'];
        $this->wechat = $this->getWechat($official_account_id);

        try{

            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            $parent_article = Material::findById($model->material_id);

            // filter redirect img link and construct wechat img

            foreach($model->article_list as &$article) {
                $article['content'] = Utils::clear_wechat_redirect_url($article['content']);
                $article['weixin_cover_url'] = Utils::restore_wechat_cover_url($article['cover_url']);
                $article['cover_url'] = '';
            }

            if($parent_article->is_synchronized) {

                if(!$model->is_synchronized) {
                    $transaction->rollback();
                    return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
                }

                // rebuildAllArticle and sync to wechat
                $material_id = $this->_reSyncArticleMaterialToWechat($model->article_list, $parent_article);

            } else {

                if($model->is_synchronized) {
                    // rebuild all articles and sync them to wechat
                    $material_id = $this->_syncArticleMaterialToWechat($model->article_list, $parent_article);
                } else {
                    // update local
                    $update_time = time();
                    $material_id = Material::updateLocalArticleMaterial($parent_article, $model->article_list, $update_time);
                }
            }

            if(!$material_id) {

                foreach($model->article_list as &$article) {
                    unset($article['content']);
                }

                $transaction->rollback();
                Yii::error(sprintf('Fail to modify material(%s) bad params:(%s)', $parent_article['id'], json_encode($model->article_list)));

                if($this->extra_msg_list) {

                    $data = [
                        "extra_msg_list"=>$this->extra_msg_list
                    ];

                    return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$data]);
                }

                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }

            $transaction->commit();

            $final_data = [
                "material_id"=>$material_id,
                "extra_msg_list"=>$this->extra_msg_list
            ];

            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

        } catch(\Exception $e) {
            $transaction->rollback();
            $err_msg = sprintf("Fail to modify material cos reason:%s", $e);
            Yii::error($err_msg);
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
    }

    /**
     * 获取素材列表
     *
     * @return string
     */
    public function actionInfoList()
    {
        // if(!Yii::$app->exAuthManager->can('material/info-list')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // TODO 增加数据权限校验

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $is_completed = Yii::$app->request->get('is_completed', null);
        $title = Yii::$app->request->get('title', null);
        $is_synchronized = Yii::$app->request->get('is_synchronized', 0);
        $type = Yii::$app->request->get('type', null);
        $official_account_id = Yii::$app->request->get('official_account_id', null);

        if(is_null($type)) {
            return json_encode(["code"=>10101, "msg"=>"类型不能为空"]);
        }

        $params['type'] = $type;

        if(is_null($is_synchronized)) {
            return json_encode(["code"=>10101, "msg"=>"素材列表类型不能为空"]);
        }
        $params['is_synchronized'] = $is_synchronized;

        if(is_null($official_account_id)) {
            return json_encode(["code"=>10101, "msg"=>"请选择公众号"]);
        }
        $params['official_account_id'] = $official_account_id;

        if(!is_null($is_completed)) {
            $params['is_completed'] = $is_completed;
        }

        if(!is_null($title)) {
            $params['title'] = $title;
            $final_data = Material::_getFinalListByTitle($params, $page, $num);
//            var_dump($final_data);exit;
        }else{
            $params['parent_id'] = 0;

            $raw_material_list = Material::getList($params, $page, $num);

            $total = Material::getTotalCount($params);

            $material_list = [];

            // construct 单图文、多图文、图片素材信息
            foreach($raw_material_list as $raw_material) {

                if($raw_material['type'] == Material::MATERIAL_TYPE_IMAGE) {

                    $raw_material['weixin_source_url'] = Utils::prepare_image_weixin_source_url($raw_material['weixin_source_url']);
                    $material_list[] = Material::constructImageInfo($raw_material);

                } else if (in_array($raw_material['type'], [Material::MATERIAL_TYPE_ARTICLE_MULTI,Material::MATERIAL_TYPE_TEMPLATE])) {

                    $material_list[] = $this->_constructMultiArticleInfo($raw_material);

                } else {
                    // TODO support more type
                }
            }

            $final_data = [
                "material_list" => $material_list,
                "page_num" => ceil($total/$num)
            ];
        }

        // \Yii::$app->cache->gc();

        $cached_key = md5(sprintf("%s:%s:%s:%s", $type, $official_account_id, $page, $num));
        $is_load = \Yii::$app->cache->get($cached_key);
        \Yii::$app->cache->set($cached_key, 1, \Yii::$app->params['CACHE_WECHAT_EXPIRED_TIME']); // 2分钟过期一次

        if(!$is_load) {

            $priority = time();
            $delay = 0;
            $ttr = 3 * 60;
            $mixedData = [
                "official_account_id"=>$official_account_id,
                "page"=>$page,
                "num"=>$num
            ];

            if($type == Material::MATERIAL_TYPE_IMAGE) {
                $tube = Yii::$app->params['QUEUE_MATERIAL_IMAGE'];
            } else if($type == Material::MATERIAL_TYPE_ARTICLE_MULTI) {

                if($is_synchronized) {
                    $tube = Yii::$app->params['QUEUE_MATERIAL_ARTICLE'];
                } else {
                    $tube = null;
                }
            }

            if(isset($tube)) {
                \Yii::$app->beanstalk
                    ->putInTube($tube, $mixedData , $priority, $delay, $ttr);
            }
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 获取素材详情
     *
     * @return string
     */
    public function actionInfo()
    {
        // if(!Yii::$app->exAuthManager->can('material/info')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // TODO 增加数据权限校验

        $material_id = Yii::$app->request->get('id', null);

        // get material info
        $material_info = Material::findById($material_id);
        if(!$material_info) {
            Yii::error(sprintf('Fail to find material cos bad material id(%s) params.', $material_id));
            return json_encode(["code"=>10101, "msg"=>"素材不存在，或已被删除"]);
        }

        // 检查素材是否隶属于该公司下的某个公众号
        $if_has_right = $this->_checkIfOfficialAccountRight($material_info['official_account_id']);
        if(!$if_has_right) {
            return json_encode(["code"=>10101, "msg"=>"素材不属于此公众号，或者公众号已删除"]);
        }

        $this->wechat = $this->getWechat($material_info['official_account_id']);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $final_material_info = [];


        try {
            if(in_array($material_info['type'], [Material::MATERIAL_TYPE_ARTICLE_MULTI,Material::MATERIAL_TYPE_TEMPLATE])) {
                if(!$material_info['is_synchronized']) {
                    $final_material_info = $this->_constructMultiArticleInfo($material_info, true);
                } else {
                    // 从wechat拉单篇文章信息
                    $wechat_material_info = $this->wechat->material->get($material_info['media_id']);
                    $final_material_info = $this->_constructWechatMultiArticleInfo($material_info, $wechat_material_info, true);
                }
            } else if ($material_info['type'] == Material::MATERIAL_TYPE_IMAGE) {

                $final_material_info = Material::constructImageInfo($material_info);
                $final_material_info['weixin_source_url'] = Utils::prepare_image_weixin_source_url($final_material_info['weixin_source_url']);

            } // TODO 其他类型暂时不支持

        } catch(\Exception $e) {
            $err_msg = sprintf("素材不存在，或已被删除:(%s)", $material_id);
            Yii::error(sprintf('%s:(%s)', $err_msg, $e->getMessage()));
            return json_encode(["code"=>10101, "msg"=>"素材不存在，或已被删除"]);
        }

        // 获取广告信息
        $order_info = Material::_getAdInfo($material_id);
        $final_data = [
            "material_info" => $final_material_info,
            "order_info" => $order_info
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 获取图文素材的单个图文信息
     *
     * @return string
     */
    public function actionSingleInfo()
    {
        // if(!Yii::$app->exAuthManager->can('material/single-info')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // if(!Yii::$app->exAuthManager->can('material/info')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // TODO 增加数据权限校验

        $material_id = Yii::$app->request->get('id', null);

        // get material info
        $material_info = Material::findById($material_id);
        if(!$material_info) {
            Yii::error(sprintf('Fail to find material cos bad material id(%s) params.', $material_id));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

       // 检查素材是否隶属于该公司下的某个公众号
        $if_has_right = $this->_checkIfOfficialAccountRight($material_info['official_account_id']);
        if(!$if_has_right) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        if(in_array($material_info['type'],[Material::MATERIAL_TYPE_ARTICLE_MULTI,Material::MATERIAL_TYPE_TEMPLATE])) {
            $final_material_info = $this->_constructSingleArticleInfo($material_info, true);
        } else {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $final_data = [
            "material_info" => $final_material_info
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 发送到手机预览
     *
     * @return string
     */
    public function actionPreview(){

       if(!Yii::$app->exAuthManager->can('material/preview')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        // TODO 增加数据权限校验

        $id = Yii::$app->request->post('id');
        $wechat_name = Yii::$app->request->post('weixin_name');

        $type = Yii::$app->request->post('type');
        $official_account_id = Yii::$app->request->post('official_account_id');

        if(!$official_account_id){

            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $this->wechat = $this->getWechat($official_account_id);

        if(!$this->wechat) {

            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        if($type == Material::MATERIAL_TYPE_IMAGE) {
            $wechat_material_type = Broadcast::MSG_TYPE_IMAGE;
        } else if($type == Material::MATERIAL_TYPE_ARTICLE_MULTI) {
            $wechat_material_type = Broadcast::MSG_TYPE_NEWS;
        } else {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        try{
            $fans_model = Fans::findByWechatName($wechat_name,$official_account_id);
            if(!$fans_model){
                $material_model = Material::findById($id);
                $media_id = $material_model->media_id;
                $broadcast = $this->wechat->broadcast;
                $res = $broadcast->previewByName($wechat_material_type , $media_id, $wechat_name);
                if($res->errcode == 0){
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                return json_encode(["code"=>-1, "msg"=>'未找到此微信号，或者未关注']);
            }else{
                $openId = $fans_model->open_id;
                $material_model = Material::findById($id);
                $media_id = $material_model->media_id;
                $broadcast = $this->wechat->broadcast;
                $res = $broadcast->preview($wechat_material_type,$media_id, $openId);
                if($res->errcode == 0){
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
            }

        }catch (\Exception $e){
            Yii::error(sprintf('Fail to send preview cos reason:(%s)', $e->getMessage()));
            return json_encode(["code"=>-1, "msg"=>"未找到此微信号，或者未关注"]);
        }

    }

    /*
     * 上传图文素材的图片
     */
    public function actionUploadArticleImage() {

        if(!Yii::$app->exAuthManager->can('material/upload-article-image')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 增加数据权限校验

        $post_content = Yii::$app->request->post();

        $if_has_right = $this->_checkIfOfficialAccountRight($post_content['official_account_id']);
        if(!$if_has_right) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $this->wechat = $this->getWechat($post_content['official_account_id']);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $image_list = $post_content['image_list'];

        // $image_map = $this->_sync_article_image($image_list);
        $image_map = Material::sync_article_image($image_list, $this->wechat);

        $data = [
            "image_map"=>$image_map
        ];

        return json_encode(["code"=>0, "msg"=>'ok', 'data'=>$data]);
    }

    public function actionSyncSingle() {

        if(!Yii::$app->exAuthManager->can('material/upload-article-image')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 增加数据权限校验

        $material_id = Yii::$app->request->get('material_id', NULL);

        if(!$material_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $material_info = Material::findById($material_id);
        if(!$material_info) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $this->wechat = $this->getWechat($material_info['official_account_id']);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

//        $this->wechat = $this->getWechat($material_info['official_account_id']);

        try{

            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            $media_id = $this->_syncLocalArticleToWechat($material_info, $this->wechat);
            if($media_id == 0){
                return json_encode(["code"=>-1, "msg"=>"请先编辑，更换图片后在同步到微信"]);
            }

            if(!$media_id) {
                throw new \Exception(sprintf('fail to sync local article(%s) to wechat', $material_info->id));
            }

            $transaction->commit();

        } catch(\Exception $e) {
            $transaction->rollback();
            $err_msg = sprintf("Fail to sync material cos reason:%s", $e);
            Yii::error($err_msg);
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        $data = [
            "media_id"=>$media_id
        ];

        return json_encode(["code"=>0, "msg"=>'ok', 'data'=>$data]);
    }

    // ------- 各种helper funcs --------

    /*
     * 上传图片（永久）
     */
    private function _uploadImage($raw_image_content)
    {
        $model = new MaterialForm();
        $model->scenario = MaterialForm::SCENARIO_CREATE_IMAGE;
        $material_content = ["MaterialForm"=>$raw_image_content];
        $now = time();

        if(!($model->load($material_content) && $model->validate())) {
            Yii::error(sprintf('Fail to create material cos reason:(%s)', json_encode($model->errors)));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $uploadInfo = OssUtils::downLoadSourceFromAliyun($raw_image_content);

        // TODO log object action here
        $content = $this->wechat->material->uploadImage($uploadInfo['path']);

        // supplement data
        $model->user_id = Yii::$app->user->identity->id;
        $model->status = Material::STATUS_ACTIVE;
        $model->created_from = Material::CREATED_FROM_SERVER;
        $model->media_id = $content['media_id'];
        // TODO 考虑是否需要阿里云的图片备份，拿掉之后，然后在图片素材列表那块，直接用微信端的url，做一层转发就可以了
        $model->source_url = OssUtils::constructAliSourceUrl($raw_image_content['image_key']);
        $model->weixin_source_url = $content['url'];
        $model->created_at = $now;

        $image = $model->storeImage();

        if(!$image) {
            Yii::error(sprintf('Fail to create image material cos reason:(%s)', json_encode($model->errors)));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $final_data = [
            "material_info" => [
                "id" => $image->id,
                "media_id" => $model->media_id,
                "source_url" => $model->source_url
            ]
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /*
     * 上传声音（永久）
     */
    private function _uploadVoice()
    {

    }

    /*
     * 上传视频（永久）
     */
    private function _uploadVideo()
    {

    }

    /*
     * 上传缩略图（永久）
     */
    private function _uploadThumb()
    {

    }

    /*
     * 上传永久多图文消息（永久）
     */
    private function _uploadArticleMulti($raw_article_list)
    {
        try{

            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            // validate out come params
            $model = new MaterialForm();
            $model->scenario = MaterialForm::SCENARIO_CREATE_ARTICLE_MULTI;

            // add extra info
            $model->user_id = Yii::$app->user->identity->id;
            $model->status = Material::STATUS_ACTIVE;
            $model->created_from = MATERIAL::CREATED_FROM_SERVER;
            $model->parent_id = 0;

            $material_content = ["MaterialForm"=>$raw_article_list];
            if(!($model->load($material_content) && $model->validate())) {
                Yii::error(sprintf('Fail to create multi aritcle material cos reason:(%s)', json_encode($model->errors)));
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }

            // store to local
            $local_article_list = $model->storeMultiArticle();
            if(!$local_article_list) {
                Yii::error(sprintf('Fail to create multi article material cos reason:(%s)', json_encode($model->errors)));
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }

            // shift out parent article
            $parent_article = array_shift($local_article_list);

            $media_id = NULL;

            if($model->is_synchronized) {

                $wechat_img_map = Material::makeWechatImageMap($local_article_list);

                // construct article list
                $article_list = [];
                foreach($local_article_list as $local_article) {
                    $remote_article = Material::constructRemoteArticle($local_article, $wechat_img_map);
                    $article_list[] = $remote_article;
                }
                $content = $this->wechat->material->uploadArticle($article_list);

                $media_id = $content['media_id'];

                // update article material info
                $parent_article->media_id = $media_id;
                $parent_article->updated_at = time();
                $parent_article->is_synchronized = 1;
                $parent_article->update(false);
            }

            $transaction->commit();

            $final_data = [
                "material_info" => [
                    "id"=>$parent_article['id'],
                    "media_id"=>$media_id
                ]
            ];
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

        }catch(\Exception $e) {
            $transaction->rollback();
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
    }

    /*
     * 修改永久图文消息（永久）
     */
    private function _updateArticle()
    {
        // TODO
    }

    /*
     * 上传文章内容图片（永久）
     */
    private function _uploadArticleImage()
    {
        // TODO
    }

    private function _deleteArticleMulti($material_info) {

        // check if material in mass status
        $is_in_used = Mass::checkMaterialInUsed($material_info['id']);
        if($is_in_used) {
            $this->err_code = 20101;
            return false;
        }

        $material_info->deleteAllChild(["parent_id"=>$material_info->id]);

        $is_synchronized = $material_info->is_synchronized;

        $material_info->delete();

        if($is_synchronized) {
            $this->wechat->material->delete($material_info->media_id);
        }

        return true;
    }

    private function _deleteImage($material_info) {

        // $material_info->status = Material::STATUS_DEACTIVE;
        // $material_info->updated_at = time();
        // $material_info->save(false);

        $material_info->delete();

        $this->wechat->material->delete($material_info->media_id);

        $this->err_code = 0;

        return true;
    }

    private function _deleteThumb($material_info) {
        // TODO
    }

    private function _deleteVideo($material_info) {
        // TODO
    }

    private function _deleteArticleImage($material_info) {
        // TODO
    }

    private function _constructArticleInfo($raw_material, $detail) {

        // prepare cover url
        if($raw_material['weixin_cover_url']) {
            $cover_url = Utils::prepare_cover_url($raw_material['weixin_cover_url']);
        } else {
            $cover_url = $raw_material['cover_url'];
        }

        $final_material = [
            "id"=>$raw_material['id'],
            "title"=>$raw_material['title'],
            "description"=>$raw_material['description'],
            "cover_media_id"=>$raw_material['cover_media_id'],

            "cover_url"=>$cover_url,

            "show_cover_pic"=>$raw_material['show_cover_pic'],
            "author"=>$raw_material['author'],
            "order"=>$raw_material['order'],
            "source_url"=>$raw_material['source_url'],
            "ad_source_url"=>$raw_material['ad_source_url'],
            "type"=>$raw_material['type'],
            "is_completed"=>$raw_material['is_completed'],
            "is_synchronized"=>$raw_material['is_synchronized']
        ];

        if($detail) {
            $final_material['content'] = $this->_prepare_content($raw_material['content']);
        }

        return $final_material;
    }

    private function _constructMultiArticleInfo($material_info, $detail=false) {

        $result = [];
        $final_material_info = [];

        $childList = Material::getChildArticle($material_info['id']);

        foreach($childList as $child_material_info) {
            $final_material_info[$child_material_info['order']] = $this->_constructArticleInfo($child_material_info, $detail);
        }

        ksort($final_material_info, SORT_NUMERIC);
        $order_info = Material::_getAdInfo($material_info['id']);

        $result = [
            "id"=>$material_info['id'],
            "media_id"=>$material_info['media_id'],
            "is_completed"=>$material_info['is_completed'],
            "is_synchronized"=>$material_info['is_synchronized'],
            "create_time"=>$material_info['created_at'],
            "order_info" => $order_info,
            "item_list"=>array_values($final_material_info)
        ];

        return $result;
    }

    private function _construct_source_url($media_id, $company_id, $official_account_id, $img_url=NULL) {

        $wechat = $this->wechat;

        return Material::constructSourceUrl($media_id, $company_id, $official_account_id, $wechat, $img_url);
    }

    private function _constructSingleArticleInfo($material_info, $detail=TRUE) {

        $material_info = $this->_constructArticleInfo($material_info, $detail);

        return $material_info;
    }

    private function _checkIfOfficialAccountRight($official_account_id, $user_id=NULL) {

            $official_account_info = OfficialAccount::findById($official_account_id);
            if(!$official_account_info) {
                return false;
            }

            // check if user has the right to delete specific resources, may be not
            if(!$user_id) {

                if(Yii::$app->user->identity->company_id != $official_account_info['company_id']) {
                    return false;
                }

                return true;
            }
            else {

                $user_info = User::findById($user_id, false);

                if(!$user_info) {
                    return false;
                }

                if($user_info['company_id'] != $official_account_info['company_id']) {
                    return false;
                }

                return true;
            }
    }

    private function _prepare_content($content) {

        // 调整wechat img 的link
        if(!$content) {
            return '';
        }

        $pattern = \Yii::$app->params['WECHAT_IMG_DOMAIN_PATTERN_WITH_POS'];
        $fix_url = \Yii::$app->params['CUSTOM_IMG_DOMAIN'];
        $content = preg_replace($pattern, sprintf('%s?q=\1\2', $fix_url), $content);

        $dom = new Dom;

        $dom->load($content);
        $img_list = $dom->find('img');

        foreach($img_list as $img) {

            $data_img_src = $img->getAttribute('data-src');
            $raw_img_src = $img->getAttribute('src');

            if($raw_img_src) {
                $img_src = $raw_img_src;
            } else {
                $img_src = $data_img_src;
            }

            $img->setAttribute('src', $img_src);

            // preg_match($pattern, $img_src, $matches);

            // // TODO 调整这里的代码
            // if($matches) {
            //     $img->setAttribute('src', $fix_url . '?q=' . $img_src);
            // } else {
            //     $img->setAttribute('src', $img_src);
            // }

            // simply delet the data-src attribute
            $img->removeAttribute('data-src');
        }

        return (string)$dom;
        // return $content;
    }

    // TODO 抽取出公用的部分
    private function _constructWechatMultiArticleInfo($material_info, $wechat_material_info, $detail=false) {

        $result = [];
        $final_material_info = [];
        $material_info_list = $wechat_material_info['news_item'];
        foreach($material_info_list as $index=>$_material_info) {
            $final_material_info[$index] = $this->_constructWechatArticleInfo($_material_info, $detail, $index);
        }

        $result = [
            "id"=>$material_info['id'],
            "media_id"=>$material_info['media_id'],
            "is_completed"=>$material_info['is_completed'],
            "is_synchronized"=>$material_info['is_synchronized'],
            "create_time"=>$material_info['created_at'],
            "item_list"=>array_values($final_material_info)
        ];

        return $result;
    }

    private function _constructWechatArticleInfo($raw_material, $detail, $index) {

        // prepare cover url
        $cover_url = Utils::prepare_cover_url($raw_material['thumb_url']);

        $final_material = [
//            "id"=>$raw_material['id'],
            "id"=>0,
            "title"=>$raw_material['title'],
            "description"=>$raw_material['digest'],
            "cover_media_id"=>$raw_material['thumb_media_id'],
            "cover_url"=>$cover_url,
            "show_cover_pic"=>$raw_material['show_cover_pic'],
            "author"=>$raw_material['author'],
            "order"=>$index,
            // "source_url"=>$raw_material['source_url'],
            // "ad_source_url"=>$raw_material['ad_source_url'],
            "ad_source_url"=>$raw_material['content_source_url'],
            "type"=>Material::MATERIAL_TYPE_ARTICLE_MULTI,
            "is_synchronized"=>1
        ];

        if($detail) {
            $final_material['content'] = $this->_prepare_content($raw_material['content']);
        }

        return $final_material;
    }

    private function _syncArticleMaterialToWechat($article_list, $parent_article) {

        // TODO 调整修改算法（修改算法三）

//        // 全量删除本地的数据
//        $updated_num = Material::deleteAllChild(["parent_id"=>$parent_article->id]);
//        Yii::info(sprintf('delete parent article(%s)\'s %s child articles.', $parent_article->id, $updated_num));
//
//        // filter redirect img link
//        foreach($article_list as &$article) {
//            $article['content'] = Utils::clear_wechat_redirect_url($article['content']);
//        }
//
//        // batch insert the child articles
//        $is_inserted = Material::batchInsertChildArticle($parent_article, $article_list);
//        if(!$is_inserted) {
//            Yii::error(sprintf('Fail to batch insert child articles for material(%s).', $parent_article->id));
//            return false;
//        }

        Material::_updateArticleLocal($parent_article,$article_list);

        $wechat_img_map = Material::makeWechatImageMap($article_list);
        $remote_article_list = [];
        foreach($article_list as $local_article) {
            $remote_article = Material::constructRemoteArticle($local_article, $wechat_img_map);
            $remote_article_list[] = $remote_article;
        }

        $content = $this->wechat->material->uploadArticle($remote_article_list);
        $media_id = $content['media_id'];

        // update article material info
        $parent_article->media_id = $media_id;
        $parent_article->updated_at = time();
        $parent_article->is_synchronized = 1;
        $parent_article->update(false);

        return $parent_article['id'];
    }

    private function _reSyncArticleMaterialToWechat($article_list, $parent_article) {

        // update the is_synchronized status of parent article


        // TODO 调整修改算法（修改二）

//        // 全量删除本地的数据
//        $updated_num = Material::deleteAllChild(["parent_id"=>$parent_article->id]);
//        Yii::info(sprintf('delete parent article(%s)\'s %s child articles.', $parent_article->id, $updated_num));



//        // batch insert the child articles
//        $is_inserted = Material::batchInsertChildArticle($parent_article, $article_list);
//        if(!$is_inserted) {
//            Yii::error(sprintf('Fail to batch insert child articles for material(%s).', $parent_article->id));
//            return false;
//        }
        Material::_updateArticleLocal($parent_article,$article_list);

        $wechat_img_map = Material::makeWechatImageMap($article_list);
        foreach($article_list as &$article) {
            $article['content'] = Material::replaceArticleImageContent($article['content'], $wechat_img_map);
        }

        $is_updated = Material::batchUpdateArticleMaterial($article_list, $this->wechat, $parent_article->media_id, $this->extra_msg_list);
        if(!$is_updated) {
            Yii::error(sprintf('Fail to update remote article material(%s).', $parent_article->id));
            return false;
        }

        $parent_article->is_synchronized = 1;
        $parent_article->updated_at = time();
        $parent_article->save(false);

        return $parent_article['id'];
    }

    private function _syncLocalArticleToWechat($parent_article, $wechat) {

        // get child articles
        $local_article_list = Material::getChildArticle($parent_article->id);

        $wechat_img_map = Material::makeWechatImageMap($local_article_list);

        // construct article list
        $article_list = [];
        foreach($local_article_list as $local_article) {
            if($local_article['cover_media_id'] == '1'){
                return 0;
            }
            $remote_article = Material::constructRemoteArticle($local_article, $wechat_img_map);
            $article_list[] = $remote_article;
        }
        $content = $this->wechat->material->uploadArticle($article_list);

        $media_id = $content['media_id'];

        // TODO update all local child artcile is_synchronized to 1
        $parent_article->media_id = $media_id;
        $parent_article->updated_at = time();
        $parent_article->is_synchronized = 1;
        $parent_article->update(false);

        return $parent_article->id;
    }

}