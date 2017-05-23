<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;

use common\models\AccountForm;
use common\models\User;
use common\models\AuthorityRole;
use common\models\UserRoleMap;

class AccountController extends BaseController
{
    /**
     * 创建用户.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('account/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = array_merge(Yii::$app->request->post(),
                                    ["company_id"=>Yii::$app->user->identity->company_id]);

        $exist_user = User::findByPhone($post_content['phone']);
        if($exist_user) {
            return json_encode(["code"=>20006, "msg"=>$this->status_code_msg[20006]]);
        }

        $model = new AccountForm();
        $model->scenario = AccountForm::SCENARIO_CREATE;
        $account_content = ["AccountForm"=>$post_content];

        if($model->load($account_content) && $model->signup()) {
            $this->manageLog(0,'创建用户--'.$post_content['phone']);
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to create user cos reason:(%s)', json_encode($model->errors)));
        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
    }

    /**
     *  删除用户（软删）
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('account/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $user_id = $post_content['user_id'];

        $user = User::findById($user_id, false);
        if(!$user) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $current_user = Yii::$app->user->identity;

        // 不允许跨公司改其他人的账号信息
        if($current_user->company_id != $user->company_id) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // check if admin
        // admin can not be deleted
        if(Yii::$app->exAuthManager->is_admin($user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $is_disabled = AccountForm::disableUser($user);

        if(!$is_disabled) {
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        $this->manageLog(0, '删除用户--'.$user->phone);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     *  修改用户信息
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('account/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $current_user = Yii::$app->user->identity;
        $user = User::findById($post_content['user_id'], false);

        // 不允许跨公司改其他人的账号信息
        if($current_user->company_id != $user->company_id) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // check if admin
        // 只有管理员可以修改自己
        $is_admin = Yii::$app->exAuthManager->is_admin($user);
        $is_current_user_admin = Yii::$app->exAuthManager->is_admin($current_user);
        if($is_admin) {
            if(!$is_current_user_admin) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }
        }

        // the role/status/role_uid_list of admin type can not be changed
        if($is_admin) {
            unset($post_content['role_id']);
            unset($post_content['status']);
            unset($post_content['role_uid_list']);
        }

        if(isset($post_content['role_id'])) {

            // admin role or super admin role type is not allowed
            $role_info = AuthorityRole::findById($post_content['role_id']);

            if(!$role_info or in_array($role_info['role_type'], [AuthorityRole::ROLE_TYPE_SUPER_ADMIN, AuthorityRole::ROLE_TYPE_ADMIN])) {
                Yii::error(sprintf('Opps! Someone try to hack in with user_id(%s).', $user->id));
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }
        }

        $model = new AccountForm();
        $model->scenario = AccountForm::SCENARIO_MODIFY;
        $account_content = ["AccountForm"=>$post_content];

        if($model->load($account_content) && $model->modify()) {
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to modify user cos reason:(%s)', json_encode($model->errors)));
        $this->manageLog(0,'修改用户');

        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
    }

    /**
     *  查看用户信息
     *
     * @return string
     */
    public function actionInfo()
    {
        if(!Yii::$app->exAuthManager->can('account/info')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $user_id = $post_content['user_id'];
        $user = User::findById($user_id, false);
        $current_user = Yii::$app->user->identity;

        if(!$user) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // 不允许跨公司查看其它用户的账号信息
        if($current_user->company_id != $user->company_id) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $role_info = $this->_construct_role_info($user->role_id, $user->id);

        $final_data = ["user_info" => [
            "id" => $user->id,
            "phone" => $user->phone,
            "nickname" => $user->nickname,
            // "weixin_id" => $user->weixin_id,
            "role_info" => $role_info,
            "status" => $user->blocked_at ? 1 : 0
        ]];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 查看用户信息列表
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('account/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [
            "company_id"=>Yii::$app->user->identity->company_id
        ];

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);

        // prepare role id list
        $role_type = Yii::$app->request->get('role_type', null);
        if(!is_null($role_type)) {
            $role_type = (int)$role_type;
            $raw_role_id_list = AuthorityRole::getRoleListByType($role_type);
            $role_id_list = [];
            foreach($raw_role_id_list as $raw_role_info) {
                $role_id_list[] = $raw_role_info['id'];
            }
            if($role_id_list) {
                $params['role_id_list'] = $role_id_list;
            }
        }

        $raw_role_uid_list = Yii::$app->request->get('role_uid_list', []);
        $role_uid_list = null;
        foreach($raw_role_uid_list as $uid) {
            $role_uid_list[] = (int)$uid;
        }
        if(!is_null($role_uid_list)) {
            $params['ids'] = $role_uid_list;
        }

        // prepare username param if exits
        $username = Yii::$app->request->get('username', '');
        if($username) {
            $params['username_like'] = $username;
        }

        $raw_user_list = User::getUsers($params, $page, $num);

        $total = User::getTotal($params);

        $user_list = [];
        $role_id_list =[];
        foreach($raw_user_list as $raw_user) {
            $role_id_list[] = $raw_user['role_id'];
        }
        $role_id_list = array_unique($role_id_list);

        $raw_role_info_list = AuthorityRole::getByIdList($role_id_list);
        $role_info_list = $this->_construct_role_info_list($raw_role_info_list);

        foreach($raw_user_list as $raw_user) {

            $user_list[] = [
                "id"=>$raw_user['id'],
                "phone"=>$raw_user['phone'],
                "nickname"=>$raw_user['nickname'],
                // "weixin_id"=>$raw_user['weixin_id'],
                "role_info"=>$role_info_list[$raw_user['role_id']],
                "status"=> $raw_user['blocked_at'] ? 0 : 1
            ];

        }

        $final_data = [
            "user_list" => $user_list,
            "total" => ceil($total/$num)
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    // ------------ private helper funcs
    private function _construct_role_info($role_id, $user_id) {

        $raw_role_info = Yii::$app->exAuthManager->getRoleById($role_id);

        $role_uid_list = [];
        $raw_role_uid_list = UserRoleMap::findChildsByParentId($user_id, ['user_id']);
        foreach($raw_role_uid_list as $_) {
            $role_uid_list[] = $_['user_id'];
        }

        return [
            "id"=>$raw_role_info['id'],
            "name"=>$raw_role_info['name'],

            "role_type"=>$raw_role_info['role_type'],
            "role_level"=>$raw_role_info['role_level'],

            // "is_super_admin"=>$role_info['is_super_admin'],
            // "permission_list"=>json_decode($role_info['permission_id_list'])
            "role_uid_list"=>$role_uid_list
        ];
    }

    private function _construct_role_info_list($raw_role_info_list) {

        $role_info_list = [];

        foreach($raw_role_info_list as $raw_role_info) {

            $role_info_list[$raw_role_info['id']] = [

                "id"=>$raw_role_info['id'],
                "name"=>$raw_role_info['name'],

                "role_type"=>$raw_role_info['role_type'],
                "role_level"=>$raw_role_info['role_level']

                // "is_super_admin"=>$raw_role_info['is_super_admin'],
                // "permission_list"=>json_decode($raw_role_info['permission_id_list'])
            ];
        }

        return $role_info_list;
    }
}
