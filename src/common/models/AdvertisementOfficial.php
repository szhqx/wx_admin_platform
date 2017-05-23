<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class AdvertisementOfficial extends ActiveRecord
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
