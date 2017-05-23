<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class StatisticNews extends ActiveRecord
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

    public static function getlist($params){
        $model = new StatisticNews();
//        $from = strtotime(date("Y-m-d",strtotime("-".$day." day")));
//        $to = strtotime(date("Y-m-d",strtotime("-1 day")));
        $query = $model->find()
            ->select([
                'ref_date',
                'sum(int_page_read_user) as int_page_read_user',
                'sum(int_page_read_count) as int_page_read_count',
                'sum(share_user) as share_user',
                'sum(share_count) as share_count',
                'sum(add_to_fav_user) as add_to_fav_user',
                'sum(add_to_fav_count) as add_to_fav_count'
            ])
            ->where(['between','ref_date',$params['from'],$params['to']])
            ->andWhere(['official_account_id'=>$params['official_account_id']])
            ->groupBy('ref_date')
            ->orderBy('ref_date desc');
        $total = $query->count();




        $start = max(($params['page']-1)*$params['num'], 0);
        $data_list = $query->offset($start)->limit($params['num'])->asArray()->all();

        $data_list_2 = $model->find()
            ->select(['ref_date','int_page_read_user as int_page_read_user_0','int_page_read_count as int_page_read_count_0'])
            ->where(['between','ref_date',$params['from'],$params['to']])
            ->where(['user_source'=>0])
            ->andWhere(['official_account_id'=>$params['official_account_id']])
            ->orderBy('ref_date desc')
            ->offset($start)
            ->limit($params['num'])
            ->asArray()->all();

        $data_list_3 = $model->find()
            ->select(['ref_date','int_page_read_user as int_page_read_user_2','int_page_read_count as int_page_read_count_2'])
            ->where(['between','ref_date',$params['from'],$params['to']])
            ->where(['user_source'=>2])
            ->andWhere(['official_account_id'=>$params['official_account_id']])
            ->orderBy('ref_date desc')
            ->offset($start)
            ->limit($params['num'])
            ->asArray()->all();
        $data_new = [];
        $list = [];
        foreach($data_list_2 as $k=>$v){
            foreach($data_list_3 as $kk=>$vv){
                if($vv['ref_date'] == $v['ref_date']){
                    $data_new[] = [
                        "ref_date" => $v['ref_date'],
                        "int_page_read_user_0" => $v['int_page_read_user_0'],
                        "int_page_read_count_0" => $v['int_page_read_count_0'],
                        "int_page_read_user_2" =>$vv['int_page_read_user_2'],
                        "int_page_read_count_2" =>$vv['int_page_read_count_2']
                    ];
                }
            }
        }

        foreach($data_new as $k=>$v){
            foreach($data_list as $kk=>$vv){
                if($vv['ref_date'] == $v['ref_date']){
                    $list[] = [
                        "ref_date" => date("m-d",$v['ref_date']),
                        "int_page_read_user" => $vv['int_page_read_user'],
                        "int_page_read_count" => $vv['int_page_read_count'],
                        "int_page_read_user_0" => $v['int_page_read_user_0'],
                        "int_page_read_count_0" => $v['int_page_read_count_0'],
                        "int_page_read_user_2" => $v['int_page_read_user_2'],
                        "int_page_read_count_2" => $v['int_page_read_count_2'],
                        "share_user" => $vv['share_user'],
                        "share_count" => $vv['share_count'],
                        "add_to_fav_user" => $vv['add_to_fav_user'],
                        "add_to_fav_count" => $vv['add_to_fav_count']
                    ];
                }
            }
        }
        unset($data_list);
        unset($data_list_2);
        unset($data_list_3);
        unset($data_new);

        $final_data = [
            "list" => $list,
            "page_num" => ceil($total/$params['num'])
        ];

        return $final_data;

    }

}
