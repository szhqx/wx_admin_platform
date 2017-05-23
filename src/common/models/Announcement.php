<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class Announcement extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['content','required'],
            ['content', 'string'],
            ['user_id', 'integer'],
        ];
    }

    public function create(){
        if ($this->validate()) {

            $announcement = new Announcement();

            $announcement->content = $this->content;
            $announcement->user_id = Yii::$app->user->getId();
            $announcement->created_at = time();
            if ($announcement->save()) {
                return $announcement;
            }
        }
        return;
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }

    public static function findMostNew(){
        return static::find()->orderBy('created_at desc')->one();
    }

    public function deleteByIds($ids){
        $id = implode(',',$ids);
        return $this->deleteAll("id in ($id)");
    }


}
