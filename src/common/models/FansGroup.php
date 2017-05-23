<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class FansGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','wechat_group_name'], 'string'],
            [['wechat_group_id','wechat_group_count','created_at','updated_at'], 'integer'],
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }

    /**
     * 获取黑名单model
     * */
    public static function getBlock($account_id){
        return static::find()->where(['account_id'=>$account_id,'wechat_group_id'=>1])->one();
    }
}
