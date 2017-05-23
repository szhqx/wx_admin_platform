<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Signup form.
 */
class TellerOrder extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        
        return [
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }


}
