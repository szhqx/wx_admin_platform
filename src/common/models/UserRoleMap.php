<?php

namespace common\models;

use Yii;

use yii\db\ActiveRecord;

use common\models\AuthorityRole;


/**
 * 用户角色映射model
 */
class UserRoleMap extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_MODIFY = 'modify';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            [['user_id', 'role_id'], 'required'],

            ['role_id', 'integer'],

            ['user_id', 'integer'],

            ['parent_id', 'integer']

        ];
    }

    /**
     * Finds child list by parent_id.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findChildsByParentId($uid, $columns)
    {
        $query = static::find();

        if($columns) {
            $query->select($columns);
        }

        $query->where(['parent_id' => $uid]);

        return $query->all();
    }

    /**
     * Find child list by uid list.
     *
     * @param array uid list
     *
     * @return static|null
     */
    public static function findChildsByUidList($uid_list)
    {
        $query = static::find();

        $query->where(['in', 'user_id', $uid_list]);

        return $query->all();
    }

    public function deleteParentRecord($parent_id) {
        return static::deleteAll("`parent_id` = :parent_id or `user_id` = :parent_id",
                                 [':parent_id'=>$parent_id, ':user_id'=>$parent_id]);
    }

}
