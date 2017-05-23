<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class Teller extends ActiveRecord
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

    /**
     * Finds company by company id .
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function getList($params, $page, $num)
    {
        $query =  static::find();
        $query ->where(['company_id'=>Yii::$app->user->identity->company_id]);

        if(isset($params['from']) && isset($params['to'])){
            $query->andWhere(['between', 'receipt_date', $params['from'], $params['to']]);
        }

        if(isset($params['customer'])){
            $query->andWhere(['like', 'customer', $params['customer']]);
        }

        $total  =$query->count();
        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);

        $teller_list = $query->asArray()->all();
        $data_list = [];
        foreach($teller_list as $list){
            $data_list[] = [
                "username" => User::getUserNameById($list['user_id']),
                "receipt_date" =>$list['receipt_date'],
                "order_comment" =>$list['order_comment'],
                "customer" =>$list['customer'],
                "receipt_bank_name" =>$list['receipt_bank_name'],
                "receipt_bank_num" =>$list['receipt_bank_num'],
                "pay_bank_name" =>$list['pay_bank_name'],
                "pay_bank_num" =>$list['pay_bank_num'],
                "amount" => $list['amount']
            ];
        }
        $final_data = [
            "list" => $data_list,
            "page_num" => ceil($total/$num)
        ];
        return $final_data;
    }
    public static function getIncomeList($params, $page, $num)
    {
        $query =  static::find();
        $query ->where(['company_id'=>Yii::$app->user->identity->company_id]);
        $query ->andWhere(['between', 'receipt_date', $params['date_s'], $params['date_n']]);
        foreach($params as $key=>$value) {
            if($key=='date_n' || $key=='date_s') {
                continue;
            }else{
                $query->andWhere([$key=>$value]);
            }
        }
        $total = (new \yii\db\Query())
            ->from('teller')
            ->count();

        $total_income = $query->sum('amount');

        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);

        $teller_list = $query->asArray()->all();

//        var_dump($teller_list);exit;
        $list = [];


        foreach($teller_list as $teller){
            $order_info = Advertisement::findById($teller['order_id']);
            $list[] = [
                "order_id" => $teller['order_id'],
                "receipt_date" => $order_info['receipt_date'],
                "username" => User::getUserNameById($order_info['user_id']),
                "customer" => $teller['customer'],
                "amount" => $order_info['order_amount'],
                "deposit" => $order_info['deposit'],
                "income_date"=>$teller['receipt_date'],
                "income" => $teller['amount'],
            ];
        }

        $final_data = [
            "list" => $list,
            "total_income" => $total_income,
            "page_num" => ceil($total/$num)
        ];
        return $final_data;
    }
    public static function getIncomeChartList($params)
    {
        $query =  static::find();

        $query->andWhere(['between', 'receipt_date', $params['date_s'], $params['date_n']]);

        $teller_list = $query->orderBy('receipt_date')->asArray()->all();
//        var_dump($teller_list);exit;
        $data = [];
        foreach($teller_list as $k=>$v){
            $data[] = [
                "receipt_date" => date("Y-m-d",$v['receipt_date']),
                "income" =>$v['amount']
            ];
        }
        $data_list = [];
        foreach ($data as $k=>$v){
            $data_list[$v['receipt_date']] = 0;
            $income = 0;
            foreach ($data as $in){
                if($in['receipt_date'] == $v['receipt_date']){
                    $income += $in['income'];
                }
            }
            $data_list[$v['receipt_date']] = $income;
        }
        $time = [];
        $datas = [];
        $new_data = [];
        foreach($data_list as $k => $v){
            $time[] = $k;
            $datas[] = $v;
            $new_data = [
               "name" => "收入汇总",
               "data" => $datas
            ];
        }
        $list = [
            "time" => $time,
            "data" => [$new_data]
        ];
        unset($data_list);
        unset($data);
        unset($teller_list);

        return $list;
    }
    public static function getCateIncomeChartList($params)
    {

        $lists = AdvertisementOfficial::find()->select(['official_account_id','sum(amount) as income'])
                ->where(['company_id'=>Yii::$app->user->identity->company_id])
                ->andWhere(['between', 'send_date', $params['date_s'], $params['date_n']])
                ->groupBy(['official_account_id'])
                ->asArray()->all();
//        var_dump($lists);exit;
        $data = [];
        foreach($lists as $raw){
            $group_info = OfficialGroup::getGroupNameByOfficialId($raw['official_account_id']);
            $data[] = [
                "income"=>$raw['income'],
                "group_name" => $group_info['name'],
                "group_id" => $group_info['id'],
            ];
        }
//        var_dump($data);exit;
        $data_list = [];
        foreach ($data as $k=>$v){
            $data_list[$v['group_name']] = 0;
            $income = 0;
            foreach ($data as $in){
                if($in['group_name'] == $v['group_name']){
                    $income += $in['income'];
                }
            }
            $data_list[$v['group_name']] = $income;
        }
        $group_name = [];
        $datas = [];
        $newdata = [];
        foreach($data_list as $k => $v){
            $group_name[] = $k;
            $datas[] = $v;
            $newdata = [
                "name" => "分类收入汇总",
                "data" => $datas
            ];
        }

        $list = [
            "group_name" => $group_name,
            "data" => [$newdata]
        ];

        return $list;
    }
    public static function getOfficialIncomeChartList($params)
    {

        $lists = AdvertisementOfficial::find()->select(['official_account_id','amount as income','send_date'])
                ->where(['company_id'=>Yii::$app->user->identity->company_id])
//                ->andWhere(['between', 'send_date', $params['date_s'], $params['date_n']])
                ->andWhere(['official_account_id'=>$params['official_account_id']])
//                ->groupBy(['official_account_id'])
                ->asArray()->all();
        $data = [];
//        var_dump($lists);exit;
        foreach($lists as $raw){
            $data[] = [
                "income"=>$raw['income'],
                "send_date" => date("Y-m-d",$raw['send_date']),
            ];
        }

        $data_list = [];
        foreach ($data as $k=>$v){
            $data_list[$v['send_date']] = 0;
            $income = 0;
            foreach ($data as $in){
                if($in['send_date'] == $v['send_date']){
                    $income += $in['income'];
                }
            }
            $data_list[$v['send_date']] = $income;
        }

//        var_dump($data_list);exit;
        $time = [];
        $datas = [];
        $newdata = [];
        foreach($data_list as $k => $v){
            $time[] = $k;
            $datas[] = $v;
            $newdata = [
                "name" =>  "公众号收入汇总",
                "data" => $datas
            ];
            
        }

        return $list = [
            "time" => $time,
            "data" => [$newdata]
        ];
    }

    public static function getTotalData(){
        $date_s = strtotime(date("Y-m-d",strtotime("-2 day")));
        $date_n = strtotime(date("Y-m-d",strtotime("-1 day")));
        $date_m_s = strtotime(date("Y-m-d",strtotime("-30 day")));
        $date_m_n = strtotime(date("Y-m-d",strtotime("30 day")));
//        var_dump(static::find()->where(['company_id'=>Yii::$app->user->identity->company_id])->all());exit;
        $yesterday = static::find()->where(['company_id'=>Yii::$app->user->identity->company_id])
//                    ->andWhere(['between', 'receipt_date', $date_s, $date_n])
                    ->asArray()
                    ->sum('amount');

        $month = static::find()->where(['company_id'=>Yii::$app->user->identity->company_id])
//            ->andWhere(['between', 'receipt_date', $date_m_s, $date_m_n])
            ->sum('amount');
        
        return (['yesterday_amount'=>$yesterday,'month_amount'=>$month]);
    }


}
