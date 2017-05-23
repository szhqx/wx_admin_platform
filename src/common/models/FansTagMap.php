<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class FansTagMap extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid','tag_id','created_at','updated_at'], 'integer'],
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }
    public static function getUserIdListByTagId($tag_id){
        $res = static::find()->select(['uid'])->where(['tag_id'=>$tag_id])->asArray()->all();
        $fans_id_list = [];
        foreach($res as $k=>$v){
            $fans_id_list[] = $v['uid'];
        }
        return $fans_id_list;
    }
    public static function getTagById($u_id){
        $res = static::find()->where(['uid'=>$u_id,'is_sync'=>1])->asArray()->all();
        $tag_id_list = [];
        foreach($res as $k=>$v){
            $tag_id_list[] = $v['tag_id'];
        }
        $tag_name_list = FansTag::getTagNameByTagIdList($tag_id_list);
        
        return $tag_name_list;
    }
}
