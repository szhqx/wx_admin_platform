<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Signup form.
 */
class Customer extends ActiveRecord
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

    public static function getList($params, $page, $num)
    {

        $query =  static::find()->where(['company_id'=>Yii::$app->user->identity->company_id])->orderBy('created_at desc');

        if(isset($params['created_at'])) {
            $query->andWhere(['between','created_at',$params['created_at'],$params['created_at']+86400]);
        }
        if(isset($params['customer'])) {
            $query->andWhere(['like','customer',$params['customer']]);
        }
        $total = $query->count();

        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);

        $raw_list = $query->asArray()->all();
        $data = [];
        foreach($raw_list as $list){
//            var_dump(unserialize($list['type_ids']));
            $ad_type_info = AdvertisementType::getTypeInfoByIds(unserialize($list['type_ids']));
            $data[]=[
                'id' => $list['id'],
                'created_at' => $list['created_at'],
                'customer' => $list['customer'],
                'realname' => $list['realname'],
                'qq' => $list['qq'],
                'tel' => $list['tel'],
                'company' => $list['company'],
                'wechat_id' => $list['wechat_id'],
                'mark' => $list['mark'],
                'ad_type_info' => $ad_type_info,
            ];
        }
        $final_data = [
            "list" => $data,
            "page_num" => ceil($total/$num)
        ];
        return $final_data;
    }

    public static function GetCustomerIdListByName($customer_name){
        $res = static::find()->select(['id'])->where(['like','customer',$customer_name])->asArray()->all();
        if(isset($res['0']['id'])){
            $ids = array_column($res,'id');
            return $ids;
        }
        return false;
    }


}
