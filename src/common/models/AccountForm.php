<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\UserRoleMap;


/**
 * Account Form.
 */
class AccountForm extends Model
{
    public $phone;
    public $nickname;
    public $password;
    public $company_id;
    public $role_id;
    public $role_uid_list;
    public $status;
    public $user_id;
    // public $weixin_id;

    const SCENARIO_MODIFY = 'modify';
    const SCENARIO_CREATE = 'create';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            ['phone', 'required', 'on'=>static::SCENARIO_CREATE],
            ['phone', 'filter', 'filter' => 'trim'],
            ['phone', 'string', 'min'=> 11, 'max'=>'15'],

            ['nickname', 'required', 'on'=>static::SCENARIO_CREATE],
            ['nickname', 'filter', 'filter' => 'trim'],
            ['nickname', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required', 'on'=>static::SCENARIO_CREATE],
            ['password', 'string', 'min' => 6],

            ['company_id', 'required', 'on'=>static::SCENARIO_CREATE],
            ['company_id', 'integer'],

            ['status', 'required', 'on'=>static::SCENARIO_CREATE],
            ['status', 'integer'],
            ['status', 'in', 'range'=> [0, 1]],

            ['role_id', 'required', 'on'=>static::SCENARIO_CREATE],
            ['role_id', 'integer'],

            // ['weixin_id', 'required', 'on'=>static::SCENARIO_CREATE],
            // ['weixin_id', 'filter', 'filter' => 'trim'],
            // ['weixin_id', 'string'],

            ['user_id', 'required', 'on'=>static::SCENARIO_MODIFY],
            ['user_id', 'integer', 'on'=>static::SCENARIO_MODIFY],

            ['role_uid_list', 'validateRoleUidList']
        ];
    }

    public function validateRoleUidList($attribute, $params) {
        # TODO 考虑在这里做公司用户的校验
        foreach($this->role_uid_list as $uid){
            if(!is_int($uid)) {
                $this->addError($attribute, 'The uid should be int.');
                break;
            }
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {

            $blocked_at = null;
            if($this->status === 0) {
                $blocked_at = time();
            }

            $transaction = Yii::$app->db->beginTransaction();

            try{

                $user = new User();
                $user->nickname = $this->nickname;
                $user->phone = $this->phone;
                $user->company_id = $this->company_id;
                $user->blocked_at = $blocked_at;
                $user->role_id = $this->role_id;
                $user->created_at = time();
                $user->status = $this->status;

                // FIXME check if uid belong to company
                $role_uid_list = $this->role_uid_list;

                // 根据随机盐生成密码
                $user->setPassword($this->password);
                $user->generateAuthKey();

                if ($user->save(false)) {

                    if($role_uid_list) {
                        $inserted = self::batchInsertRoleMapInfo($user->id, $role_uid_list);
                    } else {
                        $inserted = true;
                    }

                    if($inserted) {
                        $transaction->commit();
                        return $user;
                    }

                    Yii::error(sprintf('Fail to create user with user role map with params(%s)', json_encode($role_uid_list)));
                    $transaction->rollBack();
                    return;
                }

                Yii::error(sprintf('Fail to create user cos reason(%s)', 'fail to save user role map'));
                $transaction->rollBack();
                return;

            } catch (\yii\db\IntegrityException $e)
            {
                Yii::error(sprintf('Fail to create user cos dup attributes:(%s)', $e));
                $transaction->rollBack();
            }
        }

        return;
    }

    /*
     * 调整用户的信息
     */
    public function modify()
    {
        if ($this->validate()) {

            $transaction = Yii::$app->db->beginTransaction();

            try{

                $user = User::findById($this->user_id, false);
                if(!$user or $user->company_id != Yii::$app->user->identity->company_id) {
                    $this->addError("id", "The user id does not exist or the company id does not equal.");
                    return;
                }

                $blocked_at = null;
                if($this->status === 0) {
                    $blocked_at = time();
                }

                if($this->nickname) {
                    $user->nickname = $this->nickname;
                }

                // not allowed to change user phone info
                // if($this->phone) {
                //     $user->phone = $this->phone;
                // }

                if($this->company_id) {
                    $user->company_id = $this->company_id;
                }

                $user->blocked_at = $blocked_at;

                // check if role id is valid
                if($this->role_id) {
                    $user->role_id = $this->role_id;
                }

                // if($this->weixin_id) {
                //     $user->weixin_id = $this->weixin_id;
                // }

                // $user->status = $this->status;

                if($this->password) {
                    $user->setPassword($this->password);
                }

                $user->generateAuthKey();

                if ($user->save(false)) {

                    # TODO 校验uid的company id是否跟当前用户的company id一致，防止权限穿透公司的结构了

                    $updated = self::batchUpdateRoleMapInfo($user->id, $this->role_uid_list);
                    if($updated) {
                        $transaction->commit();
                        return $user;
                    }

                    Yii::error(sprintf('Fail to create user with user role map with params(%s)', json_encode($this->role_uid_list)));
                    $transaction->rollBack();
                    return;
                }

                $transaction->rollBack();
                return;

            } catch (\yii\db\IntegrityException $e)
            {
                $transaction->rollBack();
                Yii::error(sprintf('Fail to modify user cos dup attributes:(%s)', $e));
            }
        }

        Yii::error(sprintf('Fail to validate update content for user reason:(%s)', json_encode($this->errors)));
        return;
    }

    public static function disableUser($user) {

        $transaction = Yii::$app->db->beginTransaction();

        // 同步删除这个用户的资源关系 user_role_map
        $now = time();
        $user->blocked_at = $user->updated_at = $now;
        $is_disabled = $user->save();
        if($is_disabled) {
            UserRoleMap::deleteParentRecord($user->id);
            $transaction->commit();
            Yii::info(sprintf('Success to disable user(%s).', $user->id));
            return true;
        }

        $transaction->rollBack();
        Yii::error(sprintf('Fail to delete user cos reason:(%s)', json_encode($user->errors)));
        return false;
    }

    public static function batchInsertRoleMapInfo($parent_id, $role_uid_list)
    {
        $created = time();
        $rows[] = [
            $parent_id,
            0,
            $created
        ];

        foreach($role_uid_list as $uid) {
            $rows[] = [
                $uid,
                $parent_id,
                $created
            ];
        }

        $insert_column_list = [
            'user_id',
            'parent_id',
            'created_at'
        ];

        $inserted = Yii::$app->db->createCommand()
                                 ->batchInsert(UserRoleMap::tableName(), $insert_column_list, $rows)
                                 ->execute();

        return $inserted;
    }

    public static function batchUpdateRoleMapInfo($parent_id, $role_uid_list)
    {
        // delete all role map info for parent_id, 返回的条数可能为0，就不检查了
        UserRoleMap::deleteParentRecord($parent_id);

        if(!$role_uid_list) {
            return true;
        }

        $created = time();
        $rows[] = [
            $parent_id,
            0,
            $created
        ];

        foreach($role_uid_list as $uid) {
            $rows[] = [
                $uid,
                $parent_id,
                $created
            ];
        }

        $insert_column_list = [
            'user_id',
            'parent_id',
            'created_at'
        ];

        $inserted = Yii::$app->db->createCommand()
                                 ->batchInsert(UserRoleMap::tableName(), $insert_column_list, $rows)
                                 ->execute();

        return $inserted;
    }

}
