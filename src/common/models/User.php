<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use common\models\AuthorityRole;

/**
 * User model.
 *
 * @property int $id
 * @property string $username
 * @property string $nickname
 * @property string $weixin_id
 * @property string $company_id
 * @property string $role_id
 * @property string $auth_key
 * @property string $phone
 * @property string $email
 * @property string $password_hash
 * @property string $password_reset_token
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['phone', 'unique'],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'blocked_at' => null]);
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => '正常',
            self::STATUS_DELETED => '禁用'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])
                             ->andWhere(['blocked_at' => null])
                             ->one();
    }

    /**
     * Finds user by phone.
     *
     * @param string $phone
     *
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        return static::find()->where(['phone' => $phone])
                             ->andWhere(['blocked_at' => null])
                             ->one();
    }

    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email])
                             ->andWhere(['blocked_at' => null])
                             ->one();
    }

    public static function findByUsernameOrEmail($login)
    {
        return static::find()->where(['or', 'username = "' . $login . '"', 'email = "' . $login . '"'])
                             ->andWhere(['blocked_at' => null])
                             ->one();
    }

    public static function findById($uid, $checkBlock=true) {
        $query = static::find();
        $query->where(['id' => $uid]);
       if($checkBlock) {
           $query->andWhere(['blocked_at' => null]);
       }

        return $query->one();
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'blocked_at' => null
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function modifyPassword($newPassword)
    {
        // 修改密码
        $this->setPassword($newPassword);

        return $this->save(false);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        if (!$this->save() ) {
            return false;
        }

        return true;
    }

    public function delete()
    {
        if($this->company_id != Yii::$app->user->identity->company_id) {
            return false;
        }

        $this->updated_at = $this->blocked_at = time();

        return $this->save(false);
    }

    public static function getUsers($params, $page=null, $num=null, $checkDelete=true)
    {
        $query = static::find();

        if($checkDelete) {
            $query->where(['status'=>1]);
        }

        foreach($params as $key=>$value) {
            if($key == 'ids') {
                $query->andWhere(['in', 'id', $value]);
                continue;
            } else if($key == 'role_id_list') {
                $query->andWhere(['in', 'role_id', $value]);
                continue;
            } else if($key == 'username_like') {
                $query->andWhere(['like', 'nickname', $value]);
                continue;
            }

            $query->andWhere([$key=>$value]);
        }

        $query->orderBy(["id" => SORT_DESC]);

        if(!is_null($page) and !is_null($num)) {
            $start = max(($page-1)*$num, 0);
            $query->limit($num)->offset($start);
        }

        $raw_user_info_list = $query->all();

        $user_info_list = [];

        foreach($raw_user_info_list as $raw_user) {
            $user_info_list[$raw_user['id']] = $raw_user;
        }

        unset($raw_user_info_list);

        return $user_info_list;
     }

    public static function getIdByNickName($name){
        $res = static::find()->select(['id'])->where(['nickname'=>$name])->one();
        if($res){
            return $res['id'];
        }

        return 0;
    }

    public static function getUsersByIdList($id_list, $checkStatus=true) {

        $query = static::find();

        $query->andWhere(['in', 'id', $id_list]);

        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        $raw_user_info_list = $query->all();

        $user_info_list = [];

        foreach($raw_user_info_list as $raw_user) {
            $user_info_list[$raw_user['id']] = $raw_user;
        }
        unset($raw_user_info_list);
        return $user_info_list;
    }

    public static function getUserIdByCompanyId($company_id){
        // $role_info = AuthorityRole::find()->where(['is_super_admin'=>1,'company_id'=>$company_id])->asArray()->one();
        $role_info = AuthorityRole::find()->where(['role_type'=>AuthorityRole::ROLE_TYPE_ADMIN, 'company_id'=>$company_id])->asArray()->one();
        $res = static::find()->where(['company_id'=>$company_id,'role_id'=>$role_info['id']])->asArray()->one();
        if($res){
            return $res['id'];
        }
        return 0;
    }

    public static function getUserByCompanyId($company_id){
        // $role_info = AuthorityRole::find()->where(['is_super_admin'=>1,'company_id'=>$company_id])->asArray()->one();
        $role_info = AuthorityRole::find()->where(['role_type'=>AuthorityRole::ROLE_TYPE_ADMIN, 'company_id'=>$company_id])->asArray()->one();
        $res = static::find()->where(['company_id'=>$company_id,'role_id'=>$role_info['id']])->asArray()->one();
        if($res){
            return $res;
        }
        return '';
    }

    public static function getUserNameById($user_id){
        $user = static::find()->select(['nickname'])->where(['id'=>$user_id])->asArray()->one();
        if($user){
            return $user['nickname'];
        }
        return '';
    }

    /**
     * Finds child list by role_id and company_id.
     *
     * @param int $role_id
     *
     * @return static|null
     */
    public static function findChildsByRoleCompany($role_id, $company_id, $checkStatus=true)
    {
        $query = static::find();

        $query->select(['id', 'nickname']);

        $query->where(['role_id'=>$role_id]);

        $query->andWhere(['company_id'=>$company_id]);

        // 已删除的用户不要筛选出来
        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        return $query->all();
    }

    public static function getTotal($params, $checkDelete=true)
    {

        $query = static::find();

        if($checkDelete) {
            $query->where(['status'=>1]);
        }

        foreach($params as $key=>$value) {
            if($key == 'ids') {
                $query->andWhere(['in', 'id', $value]);
                continue;
            } else if($key == 'role_id_list') {
                $query->andWhere(['in', 'role_id', $value]);
                continue;
            } else if($key == 'username_like') {
                $query->andWhere(['like', 'nickname', $value]);
                continue;
            }

            $query->andWhere([$key=>$value]);
        }

        return $query->count();
    }
}
