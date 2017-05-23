<?php

namespace common\models;

use Yii;

use yii\db\ActiveRecord;


/**
 * 角色model
 */
class AuthorityRole extends ActiveRecord
{
    // public $name;
    // public $value;
    // public $description;
    // public $company_id;
    // public $status;
    // public $permission_id_list;
    // public $role_id;
    // public $is_super_admin;

    const ROLE_TYPE_SUPER_ADMIN = 1;
    const ROLE_TYPE_ADMIN = 2;
    const ROLE_TYPE_EDITOR = 3;
    const ROLE_TYPE_FINANCE = 4;
    const ROLE_TYPE_BUSINESS = 5;

    const ROLE_LEVEL_ONE = 1;
    const ROLE_LEVEL_TWO = 2;
    const ROLE_LEVEL_THREE = 3;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_MODIFY = 'modify';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            [['name', 'description', 'company_id', 'status', 'permission_id_list'], 'required'],

            ['name', 'string', "min"=>2, "max"=>10],

            ['description', 'string'],

           ['permission_id_list', 'validatePermissionList'],

            ['status', 'required'],
            ['status', 'integer'],
            ['status', 'in', 'range'=> [0,1]],

            ['company_id', 'integer'],

            // ['role_id', 'integer', 'on'=>static::SCENARIO_MODIFY],
            // [['role_id'], 'required', 'on'=>static::SCENARIO_MODIFY]
        ];
    }

    public function validatePermissionList($attribute, $params) {

        foreach($this->$attribute as $key=>$role_id){

            if(!is_int($role_id)) {
                $this->addError($attribute, 'The permissions id should be int.');

                // try {
                // } catch (\Exception $e){
                //     $this->addError($attribute, 'The permissions id should be int.');
                //     return;
                // }
            }
        }
    }

    /**
     * Finds role by roleId.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findById($id, $checkStatus=true)
    {
        $query = static::find();

        $query->where(['id' => $id]);

        if($checkStatus) {
            $query->andWhere(['status' => 1]);
        }

        return $query->one();
    }

    /**
     * Finds role by type and level.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findByTypeLevel($type, $level) {

        $query = static::find();

        $query->where(['role_type'=>$type]);

        $query->andWhere(['role_levle'=>$level]);

        return $query->one();
    }

    /**
     * Finds role list by type.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function getRoleListByType($type) {

        $query = static::find();

        $query->where(['role_type'=>$type]);

        return $query->all();
    }

    /**
     * get role level info.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function getRoleLevelInfo()
    {
        $role_lelve_info = [];

        $query = static::find();

        $query->where(['in', 'role_type', [AuthorityRole::ROLE_TYPE_EDITOR,
                                                                 AuthorityRole::ROLE_TYPE_FINANCE,
                                                                 AuthorityRole::ROLE_TYPE_BUSINESS
        ]]);

        $raw_role_info_list = $query->all();

        $role_info_list = [];
        foreach($raw_role_info_list as $raw_role_info) {
            $role_info_list[$raw_role_info->role_type][] = [
                "id"=>$raw_role_info->id,
                "name"=>$raw_role_info->name,
                "role_level"=>$raw_role_info->role_level
            ];
        }

        $role_lelve_info = [
            [
                "name"=>"编辑",
                "role_type"=>AuthorityRole::ROLE_TYPE_EDITOR,
                "subordinate_list"=>$role_info_list[AuthorityRole::ROLE_TYPE_EDITOR]
            ],
//            [
//                "name"=> "财务",
//                "role_type"=>AuthorityRole::ROLE_TYPE_FINANCE,
//                "subordinate_list"=>$role_info_list[AuthorityRole::ROLE_TYPE_FINANCE]
//            ],
            [
                "name"=> "商务",
                "role_type"=>AuthorityRole::ROLE_TYPE_BUSINESS,
                "subordinate_list"=>$role_info_list[AuthorityRole::ROLE_TYPE_BUSINESS]
            ]
        ];

        return $role_lelve_info;
    }

    // TODO
    public function create() {

        if ($this->validate()) {

            $role = new AuthorityRole();

            $role->name = $this->nickname;
            $role->description = $this->description;
            $role->company_id = $this->company_id;
            $role->status = $this->status;
            $role->permission_id_list = json_encode($this->permission_id_list);

            if ($role->save()) {
                return $role;
            }
        }

        return;
    }

    // TODO
    public function delete() {

        $this->updated_at = time();
        $this->status = 0;

        return $this->save(false);
    }

    //TODO
    public function modify() {

        if ($this->validate()) {

            $role = static::findById($this->role_id);

            $role->name = $this->nickname;
            $role->description = $this->description;
            $role->company_id = $this->company_id;
            $role->status = $this->status;
            $role->permission_id_list = json_encode($this->permission_id_list);

            if ($role->save()) {
                return $role;
            }
        }

        return;
    }

    // TODO
    public static function getRoleList($params, $page, $num, $checkStatus=true)
    {
        $query = static::find();

        if($checkStatus) {
            $query->where(['status'=>1]);
        }

        foreach($params as $key=>$value) {
            $query->andWhere([$key=>$value]);
        }

        $query->orderBy(["id" => SORT_DESC]);

        $start = max(($page-1)*$num, 0);

        $query->limit($num)->offset($start);

        return $query->all();
     }

    // TODO
    public static function getTotalCount($params) {

    }

    // TODO
    public static function getByIdList($role_id_list, $checkStatus=true) {

        $query = static::find();

        if($checkStatus) {
            $query->where(['status'=>1]);
        }

        $query->andWhere(['in', 'id', $role_id_list]);

        return $query->all();
    }
}
