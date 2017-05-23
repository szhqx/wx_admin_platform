<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Signup form.
 */
class AdvertisementType extends ActiveRecord
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

    public static function getList(){
        $query = static::find()->where(['company_id'=>Yii::$app->user->identity->company_id]);
        $list = $query ->asArray()->all();
        $data = [];
        foreach ($list as $p){
            if($p['parent_id'] == 0){
                $son = [];
                foreach ($list as $s){
                    if($p['id'] == $s['parent_id']){
                        $son [] = [
                            "s_id" => $s['id'],
                            "s_name" => $s['name'],
                            "created_at" => $s['created_at']
                        ];
                    }
                }
                $data [] = [
                    "p_id" => $p['id'],
                    "p_name" => $p['name'],
                    "count" => count($son),
                    "son_list" => $son
                ];
            }
        }
        return $data;

    }

    public static function getTypeInfo($id){
        $son = static::find()->where(['id' => $id])->one();
        if($son){
            $parent = static::find()->where(['id' => $son->parent_id])->one();
            if($parent){
                return [
                    'son' => [
                        'id' => $son ->id,
                        'name' => $son->name
                    ],
                    'parent' => [
                        'id' => $parent->id,
                        'name' => $parent->name
                    ]
                ];
            }
        }
        return [
            'son' => [
                'id' => -1,
                'name' => '未知'
            ],
            'parent' => [
                'id' => -1,
                'name' => '未知'
            ]
        ];
    }

    public static function getTypeInfoByIds($id_arr){
        $list = [];
        if($id_arr){

            foreach ($id_arr as $id){
                $list[] = static::getTypeInfo($id);
            }
            $data = [];
            foreach ($list as $k=>$v){
                $data [] = [
                    "name" => $v['parent']['name']."/".$v['son']['name']
                ];
            }
            unset($list);
            return $data;
        }else{
            $data [] = [
                "name" => "未知/未知"
            ];
            return $data;
        }

    }


}
