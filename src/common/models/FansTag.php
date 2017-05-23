<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class FansTag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string'],
            [['official_account_id','created_at','updated_at'], 'integer'],
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }

    public static function findByIdList($idList) {
        $query = static::find();

        $query->andWhere(['in', 'id', $idList]);

        $raw_fans_tag_list = $query->all();

        $fans_tag_list = [];

        foreach($raw_fans_tag_list as $fans_tag) {
            $fans_tag_list[$fans_tag['id']] = $fans_tag;
        }

        return $fans_tag_list;
    }

    public static function getTagNameByIds($ids,$account_id){
        $id_list = unserialize($ids);
        $res = static::find()->where(['official_account_id'=>$account_id])->andWhere(['wechat_tag_id'=>$id_list])->all();
        $data = null;
        if($res){
            foreach($res as $k=>$v){
                $data .= $v['wechat_tag_name'].' ';
            }
        }
        unset($res);
        return $data;

    }

    public static function getTagNameByTagIdList($tag_id_list){
        $res = static::find()->where(['id'=>$tag_id_list])->all();
        $tag_name_list = null;
        foreach($res as $k=>$v){
            $tag_name_list  .= $v['title'].' ';
        }
        return $tag_name_list;
    }
}
