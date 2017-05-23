<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;


/**
 * Signup form.
 */
class MenusNews extends ActiveRecord
{
    /**1->click/
     * 2->view/
     * 3->scancode_push/
     * 4->scancode_waitmsg/
     * 5->pic_sysphoto/
     * 6->pic_photo_or_album/
     * 7->pic_weixin/
     * 8->location_select
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
