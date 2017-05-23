<?php

namespace frontend\controllers;

use EasyWeChat\Foundation\Application;
use Yii;
use yii\web\Controller;

use common\models\User;
use common\models\Article;
use common\models\OfficialAccountForm;
use common\models\OfficialAccount;
use common\models\OfficialGroup;

class OfficialAccountController extends BaseController
{
    /**
     * 创建公众号.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('official-account/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = array_merge(Yii::$app->request->post(), ["company_id"=>Yii::$app->user->identity->company_id]);

        $model = new OfficialAccountForm();
        $model->scenario = OfficialAccountForm::SCENARIO_CREATE;
        $official_account_content = ["OfficialAccountForm"=>$post_content];

        if($model->load($official_account_content) ) {

            $res = $model->create();
            if($res['code'] == 0) {
                $data = [
                    "id" => $res['id']
                ];
                $this->manageLog(0,'创建公众号--'.$post_content['weixin_id']);

                try{
                    $mixedData = [
                        "official_account_id"=>$res['id'],
                        "page"=>1,
                        "num"=>20
                    ];
                    $delay = 0;
                    $priority = time();
                    $tube_news = Yii::$app->params['QUEUE_MATERIAL_ARTICLE'];
                    $tube_image = Yii::$app->params['QUEUE_ONCE_MATERIAL_IMAGE'];
                    $tube_statistic = Yii::$app->params['QUEUE_SYNC_STATISTIC'];
                    $tube_menu = Yii::$app->params['QUEUE_SYNC_MENU'];
                    $tube_reply = Yii::$app->params['QUEUE_SYNC_REPLY'];

                    Yii::$app->beanstalk->putInTube($tube_news, $mixedData , $priority, $delay);
                    Yii::$app->beanstalk->putInTube($tube_image, $mixedData , $priority, $delay);
                    Yii::$app->beanstalk->putInTube($tube_statistic, $mixedData , $priority, $delay);
                    Yii::$app->beanstalk->putInTube($tube_menu, $mixedData , $priority, $delay);
                    Yii::$app->beanstalk->putInTube($tube_reply, $mixedData , $priority, $delay);

                    return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$data]);
                }catch (\Exception $e){
                    Yii::error(sprintf('Fail to create official account cos reason:(%s)', json_encode($e->getMessage())));
                    return json_encode(["code"=>-1, "msg"=>'添加队列失败']);
                }

            }
            else{
                Yii::error(sprintf('Fail to create official account cos reason:(%s)', json_encode($res)));
//                var_dump($res);exit;

                if(is_string($res['msg'])){
                    return json_encode(["code"=>-1, "msg"=>$res['msg']]);
                }else{
                    $error_msg='';
                    foreach ($res['msg'] as $k=>$v){
                        $error_msg .= $v['0']." ";
                    }
                    return json_encode(["code"=>-1, "msg"=>$error_msg]);
                }


            }
        }
    }

    /**
     * 禁用公众号.
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('official-account/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $current_user = Yii::$app->user;

        $post_content = Yii::$app->request->post();
        $official_account_id = $post_content['id'];

        $transaction = Yii::$app->db->beginTransaction();

        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            return json_encode(["code"=>10101, "msg"=>'未找到此Id数据']);
        }

        // 数据权限校验
        if(!Yii::$app->dataAuthManager->canModifyOfficialAccount($official_account, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $is_deleted = $official_account->delete();

        if(!$is_deleted) {
            $transaction->rollBack();
            Yii::error(sprintf('Fail to delete official account(%d)cos reason:(%s)', $official_account_id));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        Article::disableAll($official_account_id);

        $transaction->commit();
        $this->manageLog($official_account_id, '删除公众号');
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     * 修改公众号.
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('official-account/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();

        $current_user = Yii::$app->user;
        $official_account_id = $post_content['id'];
        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            return json_encode(["code"=>10101, "msg"=>'未找到此Id数据']);
        }

        // 数据权限校验
        if(!Yii::$app->dataAuthManager->canModifyOfficialAccount($official_account, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $model = new OfficialAccountForm();
        $model->scenario = OfficialAccountForm::SCENARIO_MODIFY;
        $official_account_content = ["OfficialAccountForm"=>$post_content];

        if($model->load($official_account_content) && $model->modify()) {
            $this->manageLog($post_content['id'],'修改公众号');
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to modify official account cos reason:(%s)', json_encode($model->errors)));
        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
    }

    /**
     * 获取公众号列表.
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('official-account/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $page = (int)Yii::$app->request->post('page', NULL);
        $num = (int)Yii::$app->request->post('num', NULL);
        $keyword = Yii::$app->request->post('keyword', null);
        $group_id = Yii::$app->request->post('group_id', null);
        $editor_id = Yii::$app->request->post('editor_id', null);
        $attention_range_start = Yii::$app->request->post('fans_num_range_start', 0);
        $attention_range_end = Yii::$app->request->post('fans_num_range_end', 900000000);
        // $auditor_id = Yii::$app->request->post('auditor_id', null);

        // prepare params
        $params = [
            "company_id"=>Yii::$app->user->identity->company_id,
            "status"=>1
        ];

        // 数据权限校验
        $current_user = Yii::$app->user;
        $editor_id_list = Yii::$app->dataAuthManager->getEditorChildUidList($current_user);

        if($editor_id_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($editor_id_list !== true) {

            if($editor_id_list === []) {
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>["official_account_list"=>[], "page_num"=>10]]);
            }

            if($editor_id and !in_array($editor_id, $editor_id_list)) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }

            if(!is_null($editor_id)) {
                $params['editor_id'] = $editor_id;
            } else {
                $params['editor_id_list'] = $editor_id_list;
            }

        } else  {

            if(!is_null($editor_id)) {
                $params['editor_id'] = $editor_id;
            }

        }

        if(!is_null($keyword)) {
            $params['weixin_name'] = $keyword;
        }

        if(!is_null($group_id)) {
            $params['group_id'] = $group_id;
        }

        // if(!is_null($auditor_id)) {
        //     $params['auditor_id'] = $auditor_id;
        // }

        if(!is_null($attention_range_start)) {
            $params['attention_range_start'] = $attention_range_start;
        }

        if(!is_null($attention_range_end)) {
            $params['attention_range_end'] = $attention_range_end;
        }

        $final_data = OfficialAccount::getList($params, $page, $num);
//        $total = OfficialAccount::getTotalCount($params);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 获取公众号信息.
     *
     * @return string
     */
    public function actionInfo()
    {
        if(!Yii::$app->exAuthManager->can('official-account/info')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();

        // check if official account exist
        $id = $post_content['id'];
        $official_account = OfficialAccount::findById($id);
        if(!$official_account) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // check if the data permissions of user
        $editor_id = $official_account['editor_id'];
        $current_user = Yii::$app->user;
        $editor_id_list = Yii::$app->dataAuthManager->getEditorChildUidList($current_user);
        if($editor_id_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        if($editor_id_list !== true) {
            if(!in_array($editor_id, $editor_id_list)) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }
        }

        $u_id_list = [$official_account['editor_id']];
        $params = [
            "company_id"=>Yii::$app->user->identity->company_id,
            "blocked_at"=>NULL,
            "ids"=>$u_id_list
        ];
        $user_info_list = User::getUsers($params);

        $group_info = $this->_contructOfficialGroupInfo($official_account['group_id']);

        $final_data = ["official_account_info" => [
            "id"=>$official_account['id'],
            "weixin_id"=>$official_account['weixin_id'],
            "weixin_name"=>$official_account['weixin_name'],
            "weixin_password"=>$official_account['weixin_password'],
            // "official_id"=>$official_account['official_id'],
            "official_origin_id"=>$official_account['official_origin_id'],
            "app_id"=>$official_account['app_id'],
            "app_secret"=>$official_account['app_secret'],
            "encoding_aes_key"=>$official_account['encoding_aes_key'],
            "token"=>$official_account['token'],
            "admin_weixin_id"=>$official_account['admin_weixin_id'],
            "admin_email"=>$official_account['admin_email'],
            "operation_subject"=>$official_account['operation_subject'],
            "is_verified"=>$official_account['is_verified'],
            // "operation_certificate_no"=>$official_account['operation_certificate_no'],
            // "operator_name"=>$official_account['operator_name'],

            // "editor_id"=>$official_account['editor_id'],
            // "auditor_id"=>$official_account['auditor_id'],

            "editor_info"=>[
                "id"=>$official_account['editor_id'],
                // "nickname"=>$user_info_list[$official_account['editor_id']]['nickname']
                "nickname"=>isset($user_info_list[$official_account['editor_id']]) ? $user_info_list[$official_account['editor_id']]['nickname'] : ""
            ],

            // "auditor_info"=>[
            //     "id"=>$official_account['auditor_id'],
            //     // "nickname"=>$user_info_list[$official_account['auditor_id']]['nickname']
            //     "nickname"=>isset($user_info_list[$official_account['auditor_id']]) ? $user_info_list[$official_account['auditor_id']]['nickname'] : ""
            // ],

            "annual_verification_time"=>$official_account['annual_verification_time'],
            // "is_annual_validity"=>$official_account['is_annual_validity'],
            "attention_link"=>$official_account['attention_link'],

            "group_info"=>$group_info,

            "created_at" => $official_account['created_at'],

            "fans_num"=>$official_account['fans_num'],
            "status"=>$official_account['status']
        ]];
        unset($official_account);
        unset($u_id_list);
        unset($group_info);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 公众号连接信息.
     *
     * @return string
     */
    public function actionConnectInfo(){

        // if(!Yii::$app->exAuthManager->can('official-account/show-connect')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        if(!Yii::$app->exAuthManager->can('official-account/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_acount_id = Yii::$app->request->get('official_account_id');
        $model = new OfficialAccount();
        $res = $model->find()->select(['id','token','encoding_aes_key'])->where(['id'=>$official_acount_id])->asArray()->one();
        $data['url'] = Yii::$app->request->hostInfo.'/index.php?r=wechat/index&id='.$official_acount_id;
        $data['token'] = $res['token'];
        $data['encoding_aes_key'] = $res['encoding_aes_key'];
        unset($res);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>['info'=>$data]]);
    }

    /**
     * 公众号连接信息.
     *
     * @return string
     */
    public function actionConnect(){

        // if(!Yii::$app->exAuthManager->can('official-account/connect')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

//        if(!Yii::$app->exAuthManager->can('official-account/modify')) {
//            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
//        }
//
//        $official_acount_id = Yii::$app->request->get('official_account_id');
//        $model = OfficialAccount::findById($official_acount_id);
//        $model->is_connect = 1;
//        $model->save();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     * 批量导入公众号信息.
     *
     * @return string
     */
    public function actionBatchImport()
    {
        if(!Yii::$app->exAuthManager->can('official-account/batch-import')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO
    }

    /**
     * 批量导出公众号信息.
     *
     * @return string
     */
    public function actionBatchDump()
    {
        if(!Yii::$app->exAuthManager->can('official-account/batch-dump')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO
    }

     /**
     * 自动回复
     *
     * @return string
     */
    public function actionAutoResponse()
    {
        if(!Yii::$app->exAuthManager->can('official-account/auto-response')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO
    }

    private function _contructOfficialGroupInfo($group_id) {

        $official_group = OfficialGroup::findById($group_id);

        if($official_group) {
            $group_info = [
                "id"=>$official_group['id'],
                "name"=>$official_group['name']
            ];
        } else {
             $group_info = [
                "id"=>0,
                "name"=>"未知"
            ];
        }

        return $group_info;
    }
}