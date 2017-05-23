<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * OfficialAccount model.
 *
 * @property int $id
 * @property string $weixin_id
 * @property string $weixin_name
 * @property string $weixin_password
 * @property string $official_id
 * @property string $official_origin_id
 * @property string $app_id
 * @property string $app_secret
 * @property string $encoding_aes_key
 * @property string $token
 * @property int $is_verified
 * @property string $admin_weixin_id
 * @property string $admin_email
 * @property string $operation_subject
 * @property string $operation_certificate_no
 * @property string $operator_name
 * @property int $editor_id
 * @property int $author_id
 * @property int $annual_verification_time
 * @property int $is_annual_validity
 * @property string $attention_link
 * @property int $status
 * @property int $group_id
 * @property int $company_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $fans_num
 */
class OfficialAccount extends ActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

    public function rules()
    {
        return [
            [['app_id'],'unique','message'=>'此公众号已存在，请勿重新添加'],
            [['weixin_id'],'unique','message'=>'微信id号重复']
        ];
    }
    public static function findById($oId, $checkStatus=true) {

        $query = static::find()->where(['id'=>$oId]);

        if($checkStatus) {
            $query->andWhere(['status'=>$checkStatus]);
        }

        return $query->one();
    }

    public function delete() {

        $this->status = 0;
        $this->updated_at = time();

        return $this->save(false);
    }

    public static function getList($params, $page, $num)
    {
        $query = static::find();

        $query->where(['status'=>1]);
        // check if super admin
//        $current_user = Yii::$app->user->identity;
//        $is_current_user_admin = Yii::$app->exAuthManager->is_admin($current_user);
//        if(!$is_current_user_admin){
//            $query->andWhere(['editor_id'=>$current_user->getId()]);
//        }

        foreach($params as $key=>$value) {
            if($key=='weixin_name') {
                $query->andWhere(['like', 'weixin_name', $value]);
            } elseif ($key == 'attention_range_start' ) {
                $query->andWhere(['>=', 'fans_num', $value]);
            } elseif ($key == 'attention_range_end') {
                $query->andWhere(['<=', 'fans_num', $value]);
            } elseif ($key == 'editor_id_list') {
                $query->andWhere(['in', 'editor_id', $value]);
            }
            else {
                $query->andWhere([$key=>$value]);
            }
        }

        $total = $query->count();

        $query->orderBy(["is_connect" => SORT_ASC]);

        if($page) {
            $start = max(($page-1)*$num, 0);
            $query->limit($num)->offset($start);
        }

        $raw_account_list = $query->asArray()->all();

        $official_account_list = [];
        $u_id_list = [];
        $official_group_id_list = [];

        foreach($raw_account_list as $raw_account) {
            $u_id_list[] = $raw_account['editor_id'];
            $u_id_list[] = $raw_account['auditor_id'];
            $official_group_id_list[] = $raw_account['group_id'];
        }
        array_unique($u_id_list);
        array_unique($official_group_id_list);

        $params = [
            "company_id"=>Yii::$app->user->identity->company_id,
            "blocked_at"=>NULL,
            "ids"=>$u_id_list
        ];
        $user_info_list = User::getUsers($params);

        $official_group_list = OfficialGroup::getGroups(["ids"=>$official_group_id_list]);

        foreach($raw_account_list as $raw_account) {

            $group_info =["id"=>0, "name"=>"未分组"];
            if(isset($official_group_list[$raw_account['group_id']])) {
                $raw_group_info = $official_group_list[$raw_account['group_id']];
                $group_info = [
                    "id" => $raw_group_info['id'],
                    "name" => $raw_group_info['name']
                ];
            }

            $official_account_list[] = [

                "id"=>$raw_account['id'],
                "weixin_id"=>$raw_account['weixin_id'],
                "weixin_name"=>$raw_account['weixin_name'],
                "weixin_password"=>$raw_account['weixin_password'],
                // "official_id"=>$raw_account['official_id'],
                "official_origin_id"=>$raw_account['official_origin_id'],
                "app_id"=>$raw_account['app_id'],
                "app_secret"=>$raw_account['app_secret'],
                "encoding_aes_key"=>$raw_account['encoding_aes_key'],
                "admin_weixin_id"=>$raw_account['admin_weixin_id'],
                "admin_email"=>$raw_account['admin_email'],
                "operation_subject"=>$raw_account['operation_subject'],
                "is_verified"=>$raw_account['is_verified'],
                // "operation_certificate_no"=>$raw_account['operation_certificate_no'],
                // "operator_name"=>$raw_account['operator_name'],

                // "editor_id"=>$raw_account['editor_id'],
                // "auditor_id"=>$raw_account['auditor_id'],

                "editor_info"=>[
                    "id"=>$raw_account['editor_id'],
                    "nickname"=>isset($user_info_list[$raw_account['editor_id']]) ? $user_info_list[$raw_account['editor_id']]['nickname'] : ""
                ],

                "auditor_info"=>[
                    "id"=>$raw_account['auditor_id'],
                    "nickname"=>isset($user_info_list[$raw_account['auditor_id']]) ? $user_info_list[$raw_account['auditor_id']]['nickname'] : ""
                    // "nickname"=>$user_info_list[$raw_account['auditor_id']]['nickname']
                ],
                "is_annual_validity"=>((int)$raw_account['annual_verification_time'] - time() < 0)? "已年审" : "已过期",
                "annual_verification_time"=>$raw_account['annual_verification_time'],
                // "is_annual_validity"=>$raw_account['is_annual_validity'],
                "attention_link"=>$raw_account['attention_link'],

                "group_info"=>$group_info,
                "is_connect"=>$raw_account['is_connect'],

                "fans_num"=>$raw_account['fans_num']
            ];
        }
        unset($raw_account_list);
        unset($u_id_list);
        unset($official_group_list);
//        var_dump($total);exit;
        $final_data = [
            "official_account_list" => $official_account_list,
            "page_num" => $num ? ceil($total/$num) : 1
        ];

        return $final_data;
     }

    public static function getTotalCount($params)
    {

        $query = static::find();
        $query->where(['status'=>1]);

        foreach($params as $key=>$value) {
            if($key=='weixin_name') {
                $query->andWhere(['like', 'weixin_name', $value]);
            } elseif ($key == 'attention_range_start') {
                $query->andWhere(['>=', 'fans_num', $value]);
            } elseif ($key == 'attention_range_end') {
                $query->andWhere(['<=', 'fans_num', $value]);
            } else {
                $query->andWhere([$key=>$value]);
            }
        }

        $total = $query->count();

        return $total;
    }
    public static function getToken($id){
        return static::findOne($id)->token;
    }

    public static function getOutPortData(){

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $keyword = Yii::$app->request->get('keyword', null);
        $group_id = Yii::$app->request->get('group_id', null);
        $editor_id = Yii::$app->request->get('editor_id', null);
        $auditor_id = Yii::$app->request->get('auditor_id', null);

        $attention_range_start = Yii::$app->request->get('attention_range_start', null);
        $attention_range_end = Yii::$app->request->get('attention_range_end', null);

        $params = [
            "company_id"=>Yii::$app->user->identity->company_id,
        ];

        if(!is_null($keyword)) {
            $params['weixin_name'] = $keyword;
        }

        if(!is_null($group_id)) {
            $params['group_id'] = $group_id;
        }

        if(!is_null($editor_id)) {
            $params['editor_id'] = $editor_id;
        }

        if(!is_null($auditor_id)) {
            $params['auditor_id'] = $auditor_id;
        }

        if(!is_null($attention_range_start)) {
            $params['attention_range_start'] = $attention_range_start;
        }

        if(!is_null($attention_range_end)) {
            $params['attention_range_end'] = $attention_range_end;
        }

        $raw_account_list = OfficialAccount::getList($params, $page, $num);
//        var_dump($raw_account_list);exit;
        $official_account_list = [];
        foreach($raw_account_list['official_account_list'] as $raw_account) {

            $official_account_list[] = [

                "id"=>$raw_account['id'],
                "weixin_id"=>$raw_account['weixin_id'],
                "weixin_name"=>$raw_account['weixin_name'],
                "weixin_password"=>$raw_account['weixin_password'],
                // "official_id"=>$raw_account['official_id'],
                "official_origin_id"=>$raw_account['official_origin_id'],
                "app_id"=>$raw_account['app_id'],
                "app_secret"=>$raw_account['app_secret'],
                "encoding_aes_key"=>$raw_account['encoding_aes_key'],
                "admin_weixin_id"=>$raw_account['admin_weixin_id'],
                "admin_email"=>$raw_account['admin_email'],
                "operation_subject"=>$raw_account['operation_subject'],
                "is_verified"=>$raw_account['is_verified']==1?"已认证":"未认证",
                // "operation_certificate_no"=>$raw_account['operation_certificate_no'],
                // "operator_name"=>$raw_account['operator_name'],

                // "editor_id"=>$raw_account['editor_id'],
                // "auditor_id"=>$raw_account['auditor_id'],

                "editor_name"=>isset($raw_account['editor_info']) ? $raw_account['editor_info']['nickname'] : "",

                "auditor_name"=>isset($raw_account['auditor_info']) ? $raw_account['auditor_info']['nickname'] : "",

                "annual_verification_time"=>date("Y-m-d",$raw_account['annual_verification_time']),
                "is_annual_validity"=>$raw_account['is_annual_validity'],
                "attention_link"=>$raw_account['attention_link'],

                // TODO 增加公众号信息
                 "group_name"=> $raw_account['group_info']['name'],
//                "group_info"=>["id"=>0, "name"=>"未知"],

                "fans_num"=>$raw_account['fans_num']
            ];
        }

        unset($raw_account_list);
        $final_data = [
            "official_account_list" => $official_account_list,
        ];

        return $final_data;



    }

    public  function getOfficial_group(){
        return $this->hasOne(OfficialGroup::className(), ['id' => 'group_id']);

    }

    public static function getListByIdList($idList, $checkStatus=true)
    {
        $query = static::find();

        if($checkStatus) {
            $query->where(['status'=>self::STATUS_ACTIVE]);
        }

        $query->andWhere(['in', 'id', $idList]);

        return $query->all();
     }


    public static function getCountByGroup($group_id)
    {
        $count = static::find()->where(['status'=>self::STATUS_ACTIVE,'company_id'=>Yii::$app->user->identity->company_id,'group_id'=>$group_id])->count();


        if($count){
            return $count;
        }
        return 0;
    }

    private function _getAnnualValidityStatus($time){
        if($time > time()){

        }
    }
}