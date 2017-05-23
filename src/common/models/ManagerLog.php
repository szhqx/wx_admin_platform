<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class ManagerLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['description', 'string'],
            [['user_id','official_account_id','created_at'], 'integer'],
        ];
    }

    public $weixin_name;
    public $nickname;

    public function create(){
        if ($this->validate()) {
            $ManagerLog = new ManagerLog();
            $ManagerLog->description = $this->description;
            $ManagerLog->official_account_id = $this->official_account_id;
            $ManagerLog->user_id = Yii::$app->user->getId();
            $ManagerLog->created_at = time();
            if ($ManagerLog->save()) {
                return $ManagerLog;
            }
        }
        return;
    }

    public static function getList($params=null, $page, $num)
    {
        $query = static::find();
        $query->joinWith('user');
        $query->joinWith('official_account');

        if(is_null($params)){
            $query->orderBy(["manager_log.id" => SORT_DESC]);
            $query->select(['user.nickname','official_account.weixin_name','manager_log.id','manager_log.description','manager_log.created_at','manager_log.ip',]);
        }else{
            $query->select(['user.nickname','official_account.weixin_name','manager_log.id','manager_log.description','manager_log.created_at','manager_log.ip',]);
            foreach($params as $key=>$value) {
                if($key=='weixin_name') {
                    $query->andFilterWhere(['like', 'official_account.weixin_name', $value]);
                } elseif ($key == 'nickname') {
                    $query->andFilterWhere(['like', 'user.nickname', $value]);
                }  else {

                }
            }
        }
        $total = $query->count();

        $query->orderBy(["manager_log.id" => SORT_DESC]);

        $start = max(($page-1)*$num, 0);

        $query->limit($num)->offset($start);
        $raw_log_list = $query->all();
        $manager_log_list =[];
        foreach($raw_log_list as $raw_log){
            $manager_log_list[] = [
                "id" => $raw_log['id'],
//                "weixin_name"=> $raw_log['weixin_name'],
                "nickname" => $raw_log['nickname']                 ,
                "description" => $raw_log['description'],
                "created_at" => $raw_log['created_at'],
                "ip" => $raw_log['ip']
            ];
        }

        $final_data = [
            "manager_log_list" => $manager_log_list,
            "page_num" => ceil($total/$num)
        ];

        return $final_data;


    }

    public static function getTotalCount($params=null,$page,$num)
    {

        $query = static::find();
        $query->joinWith('user');
        $query->joinWith('official_account');

        if(is_null($params)){
            $query->orderBy(["manager_log.id" => SORT_DESC]);

            $start = max(($page-1)*$num, 0);

            $query->limit($num)->offset($start);

            $total = $query->count();

            return $total;
        }else{
            foreach($params as $key=>$value) {
                if($key=='weixin_name') {
                    $query->andFilterWhere(['like', 'official_account.weixin_name', $value]);
                } elseif ($key == 'nickname') {
                    $query->andFilterWhere(['like', 'user.nickname', $value]);
                }  else {

                }
            }

            $query->orderBy(["manager_log.id" => SORT_DESC]);

            $start = max(($page-1)*$num, 0);

            $query->limit($num)->offset($start);

            $total = $query->count();

            return $total;
        }


    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getOfficial_account(){
        return $this->hasOne(OfficialAccount::className(), ['id' => 'official_account_id']);
    }

}
