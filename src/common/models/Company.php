<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Material Form.
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name','unique']
        ];
    }

    /**
     * Finds company by company id .
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findById($id)
    {
        return static::find()->where(['id' => $id])
                             ->andWhere(['status' => 1])
                             ->one();
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

        foreach($params as $key=>$value) {
            if($key=='name') {
                $query->andWhere(['like', 'company.name', $value]);
            }
        }
        $query->orderBy(["company.id" => SORT_DESC]);
        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);

        $raw_list = $query->asArray()->all();
        $data = [];
        foreach($raw_list as $list){
            $userinfo = User::getUserByCompanyId($list['id']);
            $data[]=[
                'id' => $list['id'],
                'name' => $list['name'],
                'contact' => $list['description'],
                'login_time' => $list['login_time'],
                'status' => $list['status'],
                'nickname' => isset($userinfo['nickname'])?$userinfo['nickname']:'未知',
                'phone' => isset($userinfo['phone'])?$userinfo['phone']:'未知',
            ];
        }
        
        $total = $query->count();
        $final_data = [
            "company_list" => $data,
            "page_num" => ceil($total/$num)
        ];
        return $final_data;
    }
    

}