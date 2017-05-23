<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class OfficialGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','desc'], 'string'],
            [['created_at','status','updated_at'], 'integer'],
        ];
    }

    public function create(){
        if ($this->validate()) {
            $model = new OfficialGroup();
            $model->name = $this->name;
            $model->desc = $this->name;
            $model->status = 1;
           // $model->desc = $this->desc;
            $model->created_at = time();
            if ($model->save()) {
                return $model;
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

    public static function getGroupidByName($name){
        $res = static::find()->where(['name'=>$name])->asArray()->one();
        if($res){
            return $res['id'];
        }
        return 0;
    }

    public static function getGroupNameById($id){
        $res = static::find()->where(['id'=>$id])->asArray()->one();
        if($res){
            return $res['name'];
        }
        return null;
    }

    public static function getGroups($params) {
        $query = static::find();

        foreach($params as $key=>$val) {
            if($key == 'ids') {
                $query->andWhere(['in', 'id', $val]);
            } else {
                $query->andWhere([$key=>$val]);
            }
        }

        $raw_group_list = $query->all();
        $group_list = [];

        foreach($raw_group_list as $raw_group) {
            $group_list[$raw_group['id']] = $raw_group;
        }

        return $group_list;
    }

    public static function getGroupNameByOfficialId($official_account_id){
        $official_account = OfficialAccount::find()->where(['id'=>$official_account_id])->asArray()->one();
        if(!$official_account){
            return ['id'=>0,'name'=>'未分组'];
        }
        $res  = static::find()->where(['id'=>$official_account['group_id']])->asArray()->one();
        if($res){
            return ['id'=>$res['id'],'name'=>$res['name']];
        }
        return ['id'=>0,'name'=>'未分组'];
    }


}


