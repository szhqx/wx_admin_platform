<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class MaterialCate extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'materials_category';
    }
    public function rules()
    {
        return [
            [['title'], 'string'],
            [['title'], 'required'],
            [['title'], 'unique'],
            [['created_at'], 'integer'],
        ];
    }

}
