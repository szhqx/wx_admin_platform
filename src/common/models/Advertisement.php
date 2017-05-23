<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class Advertisement extends ActiveRecord
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

    public static function findByOrderId($order_id) {
        return static::find()->where(['order_id' => $order_id])->one();
    }

    // TODO 分布式的时候需要调整这里的算法，解决冲突
    public static function genOrderId($ran) {

        list($usec, $sec) = explode(" ", microtime());

        $t = substr(md5(explode(".", $usec)[1] . $sec), 0, 10) . substr(md5($ran), 0, 6);

        return $t;
    }

    /**
     * Finds company by company id .
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function getList($params, $page, $num)
    {

        $query =  static::find()->where(['company_id'=>Yii::$app->user->identity->company_id]);

        if(isset($params['status'])) {
            $query->andWhere(['status'=>$params['status']]);
        }
        if(isset($params['receipt_date'])) {
            $query->andWhere(['between','receipt_date',$params['receipt_date'],$params['receipt_date']+86400]);
        }
        if(isset($params['customer'])) {
            $customer_id_list = Customer::GetCustomerIdListByName($params['customer']);
            $query->andWhere(['in','customer_id',$customer_id_list]);

        }
        if(isset($params['user_id'])) {
            $query->andWhere(['user_id'=>$params['user_id']]);
        }
        if(isset($params['user_id_list'])) {
            $query->andWhere(['in','user_id',$params['user_id_list']]);
        }
        $total = $query->count();

        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);

        $query->orderBy(["created_at" => SORT_DESC]);

        $raw_list = $query->asArray()->all();
        $data = [];
        foreach($raw_list as $list){

            $order_list = AdvertisementOfficial::find()->where(['ad_id'=>$list['id']])->asArray()->all();
            $customer_info = Customer::find()
                ->where(['company_id'=>Yii::$app->user->identity->company_id,'id'=>$list['customer_id']])
                ->asArray()->one();
            $order_info = [];
            foreach($order_list as $k=>$v){
                $official_info = OfficialAccount::findById($v['official_account_id']);
//                var_dump($offcial_info);exit;
                $type_info = AdvertisementType::getTypeInfo($v['type_id']);
                $order_info[]=[
                    'id_son' => $v['id'],
                    'send_date' => $v['send_date'],
                    'ad_position' => $v['ad_position'],
                    'retain_day' => $v['retain_day'],
                    'type_info' => $type_info,
                    'type_id' => $type_info['son']['id'],
                    'million_fans_price' => $v['million_fans_price'],
                    'official_account' => $official_info->weixin_name,
                    'official_account_id' => $v['official_account_id'],
                    'fans_num' => $official_info->fans_num,
                    'amount' => $v['amount'],
                    'status' => $v['status']
                ];
            }
            $data[]=[
//                 'order_id' => $list['id'],
                'order_id' => $list['order_id'],
                'username' => User::getUserNameById($list['user_id']),
                'user_id' => $list['user_id'],
                'receipt_date' => $list['receipt_date'],
                'customer_id' => $list['customer_id'],
                'customer' => $customer_info['customer'],
                'qq' => $customer_info['qq'],
                'order_amount' => $list['order_amount'],
                'deposit' => $list['deposit'],
                'status' => $list['status'],
                'order_info' => $order_info
            ];
        }

        $final_data = [
            "list" => $data,
            "page_num" => ceil($total/$num)
        ];

        return $final_data;
    }

}
