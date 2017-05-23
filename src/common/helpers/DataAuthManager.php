<?php

namespace common\helpers;

use Yii;
use yii\base\Component;

use common\models\AuthorityPermission;
use common\models\AuthorityRole;
use common\models\UserRoleMap;
use common\models\OfficialAccount;


/**
 * 借鉴yii2的rbac理念，专门给前端业务做的一个简易版权限管理系统
 **/
class DataAuthManager extends Component
{

    // 公众号数据权限检测
    public static function canModifyOfficialAccount($official_account, $current_user_)
    {
        if($current_user_->isGuest){
            return false;
        }

        $current_user = $current_user_->identity;

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_SUPER_ADMIN) {
            return true;
        }

        // 公司管理员/公司财务人员/公司商务人员，并且company_id一致
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN,
                                         AuthorityRole::ROLE_TYPE_FINANCE,
                                         AuthorityRole::ROLE_TYPE_BUSINESS])
           and $official_account->company_id == $current_user->company_id
        )
        {
            return true;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_EDITOR) {

            if($role['role_level'] == 1) {
                return true;
            } else if($role['role_level'] == 2) {
                $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);
                $sub_uid_list = [];
                foreach($raw_sub_uid_list as $raw_sub_uid) {
                    $sub_uid_list[] = $raw_sub_uid['user_id'];
                }

                $sub_uid_list[] = $current_user->id;

                if(in_array($sub_uid_list, $official_account['editor_id'])) {
                    return true;
                }
            } else if($official_account['editor_id'] == $current_user->id){
                return true;
            }

        }

        return false;
    }

    // 获取公众号的编辑人员id列表
    public static function getEditorChildUidList($current_user_) {

        if($current_user_->isGuest){
            return false;
        }

        $current_user = $current_user_->identity;

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_SUPER_ADMIN) {
            return true;
        }

        // 公司管理员/公司财务人员/公司商务人员
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN,
                                         AuthorityRole::ROLE_TYPE_FINANCE,
                                         AuthorityRole::ROLE_TYPE_BUSINESS])
        )
        {
            return true;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_EDITOR) {

            if($role['role_level'] == 1) {
                return true;
            } else if($role['role_level'] == 2) {

                $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);

                $sub_uid_list = [];

                foreach($raw_sub_uid_list as $raw_sub_uid) {
                    $sub_uid_list[] = $raw_sub_uid['user_id'];
                }

                $sub_uid_list[] = $current_user->id;

                return $sub_uid_list;

            } else {
                return [$current_user->id];
            }
        }

        return false;
    }

    public static function getSubIdList($current_user_, $role_id) {

        if($current_user_->isGuest){
            return false;
        }

        $current_user = $current_user_->identity;

        $role = AuthorityRole::findById($role_id);
        $current_user_role = AuthorityRole::findById($current_user->role_id);

        if(in_array($current_user_role->role_type, [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN])) {
            return true;
        }

        // 只拉取编辑人员、财务人员、商务人员
        if(!in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_EDITOR,
                                         AuthorityRole::ROLE_TYPE_FINANCE,
                                         AuthorityRole::ROLE_TYPE_BUSINESS])
        )
        {
            return false;
        }

        if($role['role_level'] != AuthorityRole::ROLE_LEVEL_THREE) {
            return false;
        }

        $sub_uid_list = [];
        $raw_sub_uid_list = [];
        if($current_user_role->role_type == $role->role_type) {
            if($current_user_role->role_level == 1){
                return true;
            }
            $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);
            foreach($raw_sub_uid_list as $raw_sub_uid) {
                $sub_uid_list[] = $raw_sub_uid['user_id'];
            }

            if($current_user_role['role_type'] == $role->role_type) {
                $sub_uid_list[] = $current_user->id;
            }

            return $sub_uid_list;
        }

        return true;
    }



    // 广告订单数据权限检测
    public static function canModifyAdvertisement($advertisement, $current_user)
    {
//        if($current_user->isGuest){
//            return false;
//        }

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }

        // 公司管理员/超级管理员
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN]))
        {
            return true;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_BUSINESS) {

            if($role['role_level'] == 1) {
                return true;
            } else if($role['role_level'] == 2) {
                $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);
                $sub_uid_list = [];
                $sub_uid_list [] = $current_user->id;
                foreach($raw_sub_uid_list as $raw_sub_uid) {
                    $sub_uid_list[] = $raw_sub_uid['user_id'];
                }

                if(in_array($sub_uid_list, $advertisement->user_id)) {
                    return true;
                }
            } else if($advertisement->user_id == $current_user->id){
                return true;
            }

        }

        return false;
    }


    // 获取广告订单的编辑人员id列表
    public static function getAdvertiseChildUidList($current_user) {

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }

        // 公司管理员/公司商务人员
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN]))
        {
            return true;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_BUSINESS) {

            if($role['role_level'] == 1) {
                return true;
            } else if($role['role_level'] == 2) {

                $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);

                $sub_uid_list = [];
                $sub_uid_list [] = $current_user->id;
                foreach($raw_sub_uid_list as $raw_sub_uid) {
                    $sub_uid_list[] = $raw_sub_uid['user_id'];
                }

                return $sub_uid_list;

            } else {
                return [$current_user->id];
            }
        }

        return false;
    }


    public static function canAddAdvertisement($current_user){
//        if($current_user->isGuest){
//            return false;
//        }

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }

        // 公司管理员/超级管理员

        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN, AuthorityRole::ROLE_TYPE_SUPER_ADMIN,AuthorityRole::ROLE_TYPE_BUSINESS]))
        {
            return true;
        }
        return false;
    }

    // 群发数据权限检测
    public static function canModifyMass($material_info=null, $current_user_=null, $official_account=null)
    {

        if($current_user_->isGuest){
            return false;
        }

        $current_user = $current_user_->identity;

        if(!$material_info and !$official_account) {
            return false;
        }

        $role = AuthorityRole::findById($current_user->role_id);
        if(!$role) {
            return false;
        }
        if($role['role_type'] == AuthorityRole::ROLE_TYPE_SUPER_ADMIN) {
            return true;
        }

        if(!$official_account) {
            $official_account = OfficialAccount::findById($material_info['official_account_id']);
            if(!$official_account) {
                Yii::error(sprintf('Fail to find official account info cos bad official account id(%s) params.', $material_info['official_account_id']));
                return false;
            }
        }

        // 公司管理员/公司财务人员/公司商务人员，并且company_id一致
        if(in_array($role['role_type'], [AuthorityRole::ROLE_TYPE_ADMIN,
                                         AuthorityRole::ROLE_TYPE_FINANCE,
                                         AuthorityRole::ROLE_TYPE_BUSINESS])
           and $official_account->company_id == $current_user->company_id
        )
        {
            return true;
        }

        if($role['role_type'] == AuthorityRole::ROLE_TYPE_EDITOR) {

            if($role['role_level'] == 1) {
                return true;
            } else if($role['role_level'] == 2) {
                $raw_sub_uid_list = UserRoleMap::findChildsByParentId($current_user->id,null);
                $sub_uid_list = [];

                foreach($raw_sub_uid_list as $raw_sub_uid) {
                    $sub_uid_list[] = $raw_sub_uid['user_id'];
                }

                $sub_uid_list[] = $current_user->id;

                if(in_array($official_account['editor_id'],$sub_uid_list)) {
                    return true;
                }

            } else if($official_account['editor_id'] == $current_user->id){
                return true;
            }

        }

        return false;
    }

}