<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use common\helpers\AuthManager;


/**
 * 权限model
 */
class AuthorityPermission extends ActiveRecord
{
    // public $name;
    // public $value;
    // public $description;
    // public $status;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_MODIFY = 'modify';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'value', 'description', 'status'], 'required'],

            ['name', 'string', "min"=>4, "max"=>'255'],

            ['value', 'int'],

            ['description', 'string'],

            ['status', 'required'],
            ['status', 'integer'],
            ['status', 'in', 'range'=> [0,1]],
        ];
    }

    protected function validatePermissionList($attribute, $params) {

        foreach($attribute as $roleId){
            if(!is_int($roleId)) {
                $this->addError($attribute, 'The permissions id should be int.');
                break;
            }
        }
    }

    public static function getPermissionsByIds($permissionIdList) {
        return static::find()->where(['in', 'id', $permissionIdList])
                             ->all();
    }

    public static function getPermissionByName($permissionName) {
        return static::find()->where(['name'=>$permissionName])
                             ->one();
    }

    public static function getAllPermissions() {
        $final_data = [];

        $permission_list = static::find()->where(['status'=>1])
                                             ->all();

        foreach($permission_list as $permission) {
            $final_data[$permission['id']] = $permission;
        }

        return $final_data;
    }

    public function create() {

    }

    public function delete() {

    }

    public function modify() {

    }
}