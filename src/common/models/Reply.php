<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class Reply extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    //1->article, 2->image, 3->voice, 4->video, 5->text 6->news
    const NEWS =1;
    const IMAGE =2;
    const VOICE =3;
    const VIDEO =4;
    const TEXT =5;

    //0被添加自动回复 1消息自动回复 2关键字自动回复
    const AUTO_REPLY =0;
    const MSG_REPLY =1;
    const KEYWORD_REPLY =2;

    public function rules()
    {
        return [

        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }



}
