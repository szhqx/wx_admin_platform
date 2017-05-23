<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class StatisticUser extends ActiveRecord
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
        $query =  static::find()
            ->where(['between','ref_date',$params['from'],$params['to']])
            ->andWhere(['official_account_id'=>$params['official_account_id']])
            ->orderBy('ref_date desc');
        $start = max(($params['page']-1)*$params['num'], 0);
        $data_list = $query->offset($start)->limit($params['num'])->asArray()->all();
        return $data_list;
    }

}
