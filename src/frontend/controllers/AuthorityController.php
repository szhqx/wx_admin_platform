<?php

namespace frontend\controllers;

use Yii;

use common\models\User;
use common\models\UserRoleMap;
use common\models\AuthorityRole;
use common\models\AuthorityPermission;
use common\models\AuthorityAssignment;

class AuthorityController extends BaseController
{
    /**
     * 拉取多级权限列表
     *
     * @return string
     */
    public function actionGetRoleLevelList()
    {
        if(!Yii::$app->exAuthManager->can('authority/get-role-level-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $raw_role_list = AuthorityRole::getRoleLevelInfo();

        $final_data = [
            "role_list"=>$raw_role_list
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 拉取下属列表
     *
     * @return string
     */
    public function actionGetSubordinateList()
    {
        if(!Yii::$app->exAuthManager->can('authority/get-subordinate-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $current_user = Yii::$app->user->identity;
        $role_id = (int)Yii::$app->request->get('role_id', 0);
        $company_id = Yii::$app->user->identity->company_id;

        // TODO check if modify user id valid
        $modify_user_id = Yii::$app->request->get('user_id', null);

        if(!$role_id) {
            return json_encode(["code"=>10101, "msg"=>"角色类型不正确"]);
        }

        if(is_null($modify_user_id) or $modify_user_id == 0) {
            return json_encode(["code"=>10101, "msg"=>"用户id不正确"]);
        }

        $raw_user_list = User::findChildsByRoleCompany($role_id, $company_id);

        $user_list = [];
        foreach($raw_user_list as $raw_user) {
            $user_list[$raw_user->id] = $raw_user;
        }

        // Yii::error(json_encode($user_list)); # TODO
        $raw_uid_list = [];
        $exist_user_list = [];
        foreach($user_list as $user) {
            $raw_uid_list[] = $user->id;
        }

        if($raw_uid_list) {
            $exist_user_list = UserRoleMap::findChildsByUidList($raw_uid_list);
        }

        // Yii::error(json_encode($exist_user_list)); # TODO

        $exit_uid_list = [];
        foreach($exist_user_list as $user){

            if($user['parent_id'] == $modify_user_id) {
                continue;
            }

            $exit_uid_list[] = $user['user_id'];
        }

        $ok_uid_list = array_diff($raw_uid_list, $exit_uid_list);

        // Yii::error(json_encode($ok_uid_list)); # TODO

        $final_user_list = [];
        foreach($ok_uid_list as $uid) {
            $user_info = $user_list[$uid];
            $final_user_list[] = [
                "id"=>$user_info->id,
                "name"=>$user_info->nickname,
            ];
        }

        $final_data = [
            "user_list"=>$final_user_list
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 拉取人员菜单
     *
     * @return string
     */
    public function actionGetMemberList()
    {
        // TODO 写入scripts脚本里面，统一权限的管理
        // if(!Yii::$app->exAuthManager->can('authority/get-member-list')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // TODO 优化这里的查询，没必要全量查找

        // prepare params
        $current_user = Yii::$app->user->identity;
        $role_id = (int)Yii::$app->request->get('role_id', 0);
        $company_id = Yii::$app->user->identity->company_id;

        if(!$role_id) {
            return json_encode(["code"=>10101, "msg"=>"角色类型不正确"]);
        }

        $raw_user_list = User::findChildsByRoleCompany($role_id, $company_id);

        $user_list = [];
        foreach($raw_user_list as $raw_user) {
            $user_list[$raw_user->id] = $raw_user;
        }

        // Yii::error(json_encode($user_list)); # TODO
        $raw_uid_list = [];
        $exist_user_list = [];
        foreach($user_list as $user) {
            $raw_uid_list[] = $user->id;
        }

        $exit_uid_list = Yii::$app->dataAuthManager->getSubIdList(Yii::$app->user, $role_id);
        if($exit_uid_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($exit_uid_list === true) {
            $ok_uid_list = $raw_uid_list;
        } else {
            $ok_uid_list = array_intersect($raw_uid_list, $exit_uid_list);
        }

        // Yii::error(json_encode($ok_uid_list)); # TODO

        $final_user_list = [];
        foreach($ok_uid_list as $uid) {
            $user_info = $user_list[$uid];
            $final_user_list[] = [
                "id"=>$user_info->id,
                "name"=>$user_info->nickname,
            ];
        }

        $final_data = [
            "user_list"=>$final_user_list
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

}
