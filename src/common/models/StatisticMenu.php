<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 *
 */
class StatisticMenu extends ActiveRecord
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
