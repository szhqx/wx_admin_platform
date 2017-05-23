<?php

namespace common\helpers;

use Yii;
use yii\base\Component;

use common\models\AuthorityPermission;
use common\models\AuthorityRole;
use common\models\UserRoleMap;


/**
 * 借鉴yii2的rbac理念，专门给前端业务做的一个简易版权限管理系统
 **/
class ExAuthManager extends Component
{

    // 获取角色信息
    public static function getRoleById($roleId) {
        return AuthorityRole::findById($roleId);
    }

    // 访问权限检测，快捷方法
    public static function can($permissionName) {

        $currentUser = Yii::$app->user;

        if($currentUser->isGuest){
            return false;
        }

        $role = AuthorityRole::findById($currentUser->identity->role_id);
        if(!$role) {
            return false;
        }

        // 系统的超级管理员，或公司的管理员，直接通过
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN])) {
            return true;
        }

        $permission = AuthorityPermission::getPermissionByName($permissionName);
        $permission_id_list = json_decode($role->permission_id_list);

        return in_array($permission['id'], $permission_id_list);
    }

    public function is_super_admin($user) {

        $role = AuthorityRole::findById($user->role_id);
        if(!$role) {
            return false;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_SUPER_ADMIN) {
            return true;
        }

        return;
    }

    public function is_admin($user) {

        $role = AuthorityRole::findById($user->role_id);
        if(!$role) {
            return false;
        }

        // 超级管理员和管理员都认定为管理员权限
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN])) {
            return true;
        }

        return;
    }

    public function getChildUidList($user) {

        $uid_list = [];

        $user_role_list = UserRoleMap::findChildsByParentId($user->id);

        if(!$user_role_list) {
            return [];
        }

        foreach($user_role_list as $user_role_info) {
            $uid_list[] = $user_role_info['user_id'];
        }

        return $uid_list;
    }

    // 增加支持根据根据公众号拉取权限列表
    public static function getRolesByOfficialId($officialId, $page=0) {
    }

    // 添加角色
    public static function addRole($roleName, $description, $company_id, $status, $permission_id_list, $role_type, $role_level) {
    }

    // 移除角色
    public static function removeRole($roleId){
    }

    // 更新角色
    public static function updateRole($roleId, $name, $description, $company_id, $status, $permission_id_list, $role_type, $role_level) {
    }
}