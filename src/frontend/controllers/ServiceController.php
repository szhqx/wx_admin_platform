<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

use common\helpers\OssUtils;

use common\models\Company;
use common\models\MaterialForm;
use common\models\Material;
use common\models\OfficialAccount;
use common\models\Image;
use common\helpers\Utils;

use Sts\Core\Profile\DefaultProfile;
use Sts\Core\DefaultAcsClient;
use Sts\AssumeRoleRequest;

class ServiceController extends BaseController
{
    // public function behaviors()
    // {
    //     return [

    //         'access' => [

    //             'class' => 'yii\filters\AccessControl',

    //             'rules' => [
    //                 [
    //                     'allow' => true,
    //                     'actions'=>['ueditor'],
    //                     'roles' => ['?'],
    //                 ],
    //             ],

    //             'denyCallback' => function($rule, $action) {
    //                 self::denyCallback($rule, $action);
    //             }
    //       ]

    //     ];
    // }

    public function actionUeditor() {

        // if(!Yii::$app->exAuthManager->can('service/ueditor')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // 兼容分发ueditor的请求
        $action = Yii::$app->request->get('action', null);

        if(!$action) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        switch($action) {
        case 'config':
            return $this->_config();
            break;
        case 'listimage':
            return $this->_listImage();
        case 'uploadimage':
            return $this->_uploadImage();
        case 'catchimage':
            return $this->_catchImage();
        default:
            break;
        }

        $final_data = [];

        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101], "data"=>$final_data]);
    }

    public function actionGetWechatImg() {

        $redirect_url = Yii::$app->request->get('q', '');

        if(!$redirect_url) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        echo $content;
    }

    private function _config() {

        $CONFIG = Yii::$app->params['UEDITOR_OSS_INFO'];

        $callback = Yii::$app->request->get('callback', null);

        $final_data = $CONFIG;

        if($callback) {

            if (preg_match("/^[\w_]+$/", $callback)) {
                return htmlspecialchars($callback) . '(' . json_encode($final_data) . ')';
            } else {
                return json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }

        }

        return json_encode($final_data);
    }

    private function _listImage() {

        $request = Yii::$app->request;

        $start = $request->get('start', 0);
        $size = $request->get('size', 20);
        $callback = Yii::$app->request->get('callback', null);

        $official_account_id = Yii::$app->request->cookies->getValue('official_account_id');
        if(!$official_account_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $params = [
            "parent_id"=>0,
            "official_account_id"=>$official_account_id,
            "type"=>Material::MATERIAL_TYPE_IMAGE
        ];
        $list = $this->_constructUeditorImageList($params, $start, $size);
        $total = Material::getTotalCount($params);

        $final_data = [
            "state"=>"SUCCESS",
            "list"=>$list,
            "start"=>$start,
            "total"=>$total
        ];

        if($callback) {
            if (preg_match("/^[\w_]+$/", $callback)) {
                return htmlspecialchars($callback) . '(' . json_encode($final_data) . ')';
            } else {
                return json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        }

        $page = $this->_calPageNum($start);

        $type = 2;
        $cached_key = md5(sprintf("%s:%s:%s:%s", $type, $official_account_id, $page, $size));
        $is_load = \Yii::$app->cache->get($cached_key);
        \Yii::$app->cache->set($cached_key, 1, \Yii::$app->params['CACHE_WECHAT_EXPIRED_TIME']); // 2分钟过期一次

        if(!$is_load) {

            $priority = time();
            $delay = 0;
            $mixedData = [
                "official_account_id"=>$official_account_id,
                // "page"=>$this->_calPageNum($start),
                "page"=>$page,
                "num"=>20 // 固定拉取20条
            ];

            $tube = Yii::$app->params['QUEUE_MATERIAL_IMAGE'];

            if($tube) {
                \Yii::$app->beanstalk
                    ->putInTube($tube, $mixedData , $priority, $delay);
            }
        }

        return json_encode($final_data);
    }

    private function _uploadImage() {

        $request = Yii::$app->request;
        $company_id = Yii::$app->user->identity->company_id;

        $callback = Yii::$app->request->get('callback', null);
        $type = Yii::$app->request->get('type', null);
        $official_account_id = Yii::$app->request->cookies->getValue('official_account_id');
        // $official_account_id = 2;
        if(!$official_account_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model = new Image();
        $final_data = [];

        // construct wechat
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            // return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            return json_encode([
                "state"=>"ERROR_UNKNOWN"
            ]);
        }

        // TODO check if image type
        $imgInfo = $_FILES['upfile'];
        $fileType = $imgInfo['type'];
        $fileSize = $imgInfo['size'];
        $fileName = null;
        $originalFileName = $imgInfo['name'];
        $url = null;

        // upload to weixin
        if($type == 2) {

            // use cover
            try{
                // upload to aliyun
                // $_object_key = OssUtils::getObjectKey();
                // $object_key = $fileName = OssUtils::getUploadkey($_object_key, $company_id, $official_account_id);
                $fileContent = file_get_contents($imgInfo['tmp_name']);
                // OssUtils::uploadSourceToAliyun($object_key, $fileContent);
                // $image_url = OssUtils::constructAliSourceUrl($object_key);

                $ext = explode('/', $fileType)[1];
                $with_ext_name = $imgInfo['tmp_name'] . '.' . $ext;
                rename($imgInfo['tmp_name'], $with_ext_name);
                $result = $this->wechat->material->uploadImage($with_ext_name);
                $media_id = $result->media_id;
                $weixin_source_url = $result->url;

            } catch(\Exception $e) {
                $err_msg = sprintf("Fail to upload image material cos reason:%s", $e);
                Yii::error($err_msg);
                return json_encode([
                    "state"=>"ERROR_UNKNOWN"
                ]);
            }
        } else {
            // use article image
            try{
                $ext = explode('/', $fileType)[1];
                $with_ext_name = $imgInfo['tmp_name'] . '.' . $ext;
                rename($imgInfo['tmp_name'], $with_ext_name);
                $result = $this->wechat->material->uploadArticleImage($with_ext_name);
                $media_id = '';
                $weixin_source_url = $result->url;
            } catch(\Exception $e) {
                $err_msg = sprintf("Fail to upload image material cos reason:%s", $e);
                Yii::error($err_msg);
                return json_encode([
                    "state"=>"ERROR_UNKNOWN"
                ]);
            }
        }

        if($type == 2) {
            $model = new MaterialForm();
            $model->official_account_id = $official_account_id;
            $model->user_id = Yii::$app->user->identity->id;
            // $model->user_id = 1;
            $model->status = Material::STATUS_ACTIVE;
            $model->created_from = Material::CREATED_FROM_SERVER;
            $model->media_id = $result->media_id;
            // $model->source_url = $image_url;
            $model->source_url = '';
            $model->weixin_source_url = $weixin_source_url;
            $model->type = Material::MATERIAL_TYPE_IMAGE;
            $model->created_at = time();
            if(!$model->storeImage()) {
                return json_encode([
                    "state"=>"ERROR_UNKNOWN"
                ]);
            }
        }

        $final_data = [
            "state"=>"SUCCESS",
            // "url"=>$image_url,
            "url"=>Utils::prepare_image_weixin_source_url($weixin_source_url),
            "title"=>$fileName,
            "original"=>$originalFileName,
            "type"=>$fileType,
            "size"=>$fileSize,
            "media_id"=>$media_id
        ];

        if($callback) {
            if (preg_match("/^[\w_]+$/", $callback)) {
                return htmlspecialchars($callback) . '(' . json_encode($final_data) . ')';
            } else {
                return json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        }

        return json_encode($final_data);
    }

    public function actionGetUploadInfo() {

        // if(!Yii::$app->exAuthManager->can('service/get-upload-info')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        $upload_dir = OssUtils::cal_upload_dir(Yii::$app->user->identity->company_id);
        $token_info = $this->_getTokenInfo();

        $upload_info = [
            "token_info"=>[
                "access_key_id"=>$token_info['AccessKeyId'],
                "access_key_secret"=>$token_info['AccessKeySecret'],
                "security_token"=>$token_info['SecurityToken'],
                "expiration"=>$token_info['Expiration']
            ],
            "dir"=>$upload_dir
        ];

        $final_data = ["upload_info"=>$upload_info];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    public function actionMarkOfficial() {

        // if(!Yii::$app->exAuthManager->can('service/mark-official')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // $upload_dir = OssUtils::cal_upload_dir(Yii::$app->user->identity->company_id);
        // $token_info = $this->_getTokenInfo();

        // $upload_info = [
        //     "token_info"=>[
        //         "access_key_id"=>$token_info['AccessKeyId'],
        //         "access_key_secret"=>$token_info['AccessKeySecret'],
        //         "security_token"=>$token_info['SecurityToken'],
        //         "expiration"=>$token_info['Expiration']
        //     ],
        //     "dir"=>$upload_dir
        // ];

        // $final_data = ["upload_info"=>$upload_info];

        // return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

        $request = Yii::$app->request;

        $official_account_id = $request->post('official_account_id', null);
        if(!$official_account_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // check if the user has the responsibility to mark such a official account
        if(Yii::$app->user->identity->company_id != $official_account->company_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // mark the official account id in headers
        // get the cookie collection (yii\web\CookieCollection) from the "response" component
        $cookies = Yii::$app->response->cookies;

        // add a new cookie to the response to be sent
        $cookies->add(new \yii\web\Cookie([
            'name' => 'official_account_id',
            'value' => $official_account_id,
        ]));

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    public function actionConfig() {

        if(!Yii::$app->exAuthManager->can('service/config')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $CONFIG = Yii::$app->params['UEDITOR_OSS_INFO'];

        //加入OSS配置
        $oss_config = OssUtils::constructConfig(Yii::$app->user->identity->company_id);
        $oss_uconf = [
            'imageUpload2Oss'=>true,
            'imageUpload2OssActionUrl' => $oss_config['host'],
            'imageUpload2OssFormData' => [
                'key' => $oss_config['dir'].'${random}', //文件命名规则, ${filename}以原文件名  ${random}随机文件名
                'policy' => $oss_config['policy'],
                'OSSAccessKeyId' => $oss_config['accessid'],
                'success_action_status' => '200',
                //'callback' => $oss_config['callback'],
                'Signature' => $oss_config['signature']
            ],
            //重写OSS接受文件字段名
            'imageFieldName' => 'file'
        ];

        $result = array_merge($CONFIG, $oss_uconf);
        $result = json_encode($result);

        return $result;
    }

    private function _catchImage() {

        $request = Yii::$app->request;
        $company_id = Yii::$app->user->identity->company_id;

        $_upload_image_list = Yii::$app->request->post('source', []);
        $upload_image_list = [];
        foreach($upload_image_list as $upload_src) {
            $upload_image_list[] = trim($upload_src);
        }

        $official_account_id = Yii::$app->request->cookies->getValue('official_account_id');
        if(!$official_account_id) {
            return json_encode([
                "state"=>"ERROR_UNKNOWN"
            ]);
        }

        // construct wechat
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode([
                "state"=>"ERROR_UNKNOWN"
            ]);
        }

        // TODO check if has right
        // $if_has_right = $this->_checkIfOfficialAccountRight($post_content['official_account_id']);
        // if(!$if_has_right) {
        //     return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        // }

        $sync_result = Material::sync_article_image($upload_image_list, $this->wechat);

        Yii::info(sprintf("Result of sync article image:%s", json_encode($sync_result)));

        // return json_encode($final_data);
        return ''; // TODO 搞清楚这里的返回值
    }

    // ------------ intern helper funcs ---------------
    private function _getAliyunPolicy() {

$policy=<<<POLICY
{
"Statement": [
    {
    "Action": [
        "oss:Get*",
        "oss:List*"
    ],
    "Effect": "Allow",
    "Resource": "*"
    }
],
"Version": "1"
}
POLICY;

        return $policy;
    }

    private function _getTokenInfo() {

        $ALIYUN_INFO = Yii::$app->params['ALIYUN_INFO'];
        $access_key_id = $ALIYUN_INFO['KEY'];
        $access_key_secret = $ALIYUN_INFO['SECRET'];
        $end_point = $ALIYUN_INFO['END_POINT'];
        $role_arn = $ALIYUN_INFO['ROLE_ARN'];
        $token_expire_time = $ALIYUN_INFO['TOKEN_EXPIRE_TIME'];
        $policy = $this->_getAliyunPolicy();

        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $access_key_id, $access_key_secret);
        $client = new DefaultAcsClient($iClientProfile);

        $request = new AssumeRoleRequest();
        $request->setRoleSessionName("client_name");
        $request->setRoleArn($role_arn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($token_expire_time);
        $response = $client->doAction($request);

        $rows = array();
        $body = $response->getBody();
        $content = json_decode($body);
        $rows['status'] = $response->getStatus();

        // var_dump($content);exit;

        if ($response->getStatus() == 200)
        {
            $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
            $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
            $rows['Expiration'] = $content->Credentials->Expiration;
            $rows['SecurityToken'] = $content->Credentials->SecurityToken;
        }
        else
        {
            $rows['AccessKeyId'] = "";
            $rows['AccessKeySecret'] = "";
            $rows['Expiration'] = "";
            $rows['SecurityToken'] = "";
        }

        return $rows;
    }

    private function _constructUeditorImageList($params, $start, $size) {

        $official_account_id = $params['official_account_id'];

        $page = max(ceil($start/$size), 1);

        $raw_material_list = Material::getList($params, $page, $size);

        $material_list = [];

        foreach($raw_material_list as $raw_material) {

            $material_info = Material::constructUeditorImageInfo($raw_material);
            $material_info['weixin_url'] = Utils::prepare_image_weixin_source_url($material_info['weixin_url']);

            $material_list[] = $material_info;
        }

        return $material_list;
    }

    private function _calPageNum($start) {

        $page = max(ceil($start/20) - 1, 0);

        return $page;
    }
}