<?php

namespace common\models;

use common\helpers\WechatHelper;
use Yii;
use yii\base\Model;
use EasyWeChat\Foundation\Application as WeChat;
use common\models\OfficialAccount;


/**
 * OfficialAccount form.
 */
class OfficialAccountForm extends Model
{
    public $id;
    public $weixin_id;
    public $weixin_name;
    public $weixin_password;
    // public $official_id;
    public $official_origin_id;
    public $app_id;
    public $app_secret;
    public $encoding_aes_key;
    // public $token;
    public $is_verified;
    public $admin_weixin_id;
    public $admin_email;
    public $operation_subject;
    // public $operation_certificate_no;
    // public $operator_name;
    public $editor_id;
    // public $auditor_id;
    public $annual_verification_time;
    // public $is_annual_validity;
    public $attention_link;
    public $status;
    public $group_id;
    public $company_id;
    public $fans_num;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_MODIFY = 'modify';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

//             [['weixin_id', 'weixin_name', 'weixin_password', 'official_id', 'official_origin_id', 'app_id', 'app_secret',

            // [['weixin_id', 'weixin_name', 'weixin_password', 'official_origin_id', 'app_id', 'app_secret',
            //    'is_verified', 'admin_weixin_id', 'admin_email', 'operation_subject',
            //   'editor_id', 'auditor_id', 'annual_verification_time', 'attention_link', 'group_id', 'company_id'],
            //  'required', 'on'=>static::SCENARIO_CREATE],
            [['weixin_id', 'weixin_name', 'official_origin_id', 'app_id', 'app_secret', 'company_id'],
             'required', 'on'=>static::SCENARIO_CREATE],

            // [['weixin_id', 'weixin_name', 'weixin_password', 'official_id', 'official_origin_id', 'app_id', 'app_secret',
            [['weixin_id', 'weixin_name', 'weixin_password', 'official_origin_id', 'app_id', 'app_secret',
               'admin_weixin_id', 'admin_email', 'operation_subject',
              'attention_link'], 'filter', 'filter' => 'trim'],

            // [['weixin_id', 'weixin_name', 'weixin_password', 'official_id', 'official_origin_id', 'app_id', 'app_secret',
            [['weixin_id', 'weixin_name', 'weixin_password', 'official_origin_id', 'app_id', 'app_secret',
               'admin_weixin_id', 'admin_email', 'operation_subject', 'attention_link'], 'string'],

            [['is_verified', 'editor_id', 'annual_verification_time', 'status', 'group_id', 'company_id'], 'integer'],

            ["status", 'default', 'value'=>1, 'on'=>static::SCENARIO_CREATE],
            ["editor_id", 'default', 'value'=>0, 'on'=>static::SCENARIO_CREATE],
//            ["auditor_id", 'default', 'value'=>0, 'on'=>static::SCENARIO_CREATE],
            ["group_id", 'default', 'value'=>0, 'on'=>static::SCENARIO_CREATE],
            ["attention_link", 'default', 'value'=>'', 'on'=>static::SCENARIO_CREATE],

            ['id', 'required', 'on'=>static::SCENARIO_MODIFY],
            ['id', 'integer', 'on'=>static::SCENARIO_MODIFY],

        ];
    }

    /**
     * create official account
     *
     * @return official account|null the saved model or null if saving fails
     */
    public function create()
    {
        if ($this->validate()) {

            try{
                $officialAccount = new OfficialAccount();
//                if(isset($this->is_verified)){
//                    if($this->is_verified == 0){
//                        return ["code"=>-1, "msg"=>[["app_iderr"=>"您的公众号未认证"]]];
//                    }
//                }
                $res = $officialAccount->find()->where(['app_id'=>$this->app_id,'status'=>0])->one();

                if($res){
                    $fans = $this->getFansNumByAppId($this->app_id,$this->app_secret);
                    if(is_string($fans)){
                        return ["code"=>-1, "msg"=>$fans];
                    }
                    $res->fans_num = $fans;
                    $res->company_id = Yii::$app->user->identity->company_id;
                    $res->status = 1;
                    $res->updated_at = time();
                    $res->save();
                    return ["code"=>0, "id"=>$res->id];
                }

                $officialAccount->weixin_id = $this->weixin_id;

                if(isset($this->weixin_name))
                    $officialAccount->weixin_name = $this->weixin_name;

                if(isset($this->weixin_password))
                    $officialAccount->weixin_password = $this->weixin_password;

                // $officialAccount->official_id = $this->official_id;
                if(isset($this->official_origin_id))
                    $officialAccount->official_origin_id = $this->official_origin_id;

                $officialAccount->app_id = $this->app_id;
                $officialAccount->app_secret = $this->app_secret;

                $fans = $this->getFansNumByAppId($this->app_id,$this->app_secret);
                if(is_string($fans)){
                    return ["code"=>-1, "msg"=>$fans];
                }

                $officialAccount->fans_num = $fans;

                $officialAccount->encoding_aes_key = $this->createRandomStr(43);
                $officialAccount->token = $this->createRandomStr(10);

                if(isset($this->is_verified))
                    $officialAccount->is_verified = $this->is_verified;

                if(isset($this->admin_weixin_id))
                    $officialAccount->admin_weixin_id = $this->admin_weixin_id;

                if(isset($this->admin_email))
                    $officialAccount->admin_email = $this->admin_email;

                if(isset($this->operation_subject))
                    $officialAccount->operation_subject = $this->operation_subject;

                // $officialAccount->operation_certificate_no = $this->operation_certificate_no;
                // $officialAccount->operator_name = $this->operator_name;

                if(isset($this->editor_id))
                    $officialAccount->editor_id = $this->editor_id;

                if(isset($this->auditor_id))
                    $officialAccount->auditor_id = $this->auditor_id;

                if($this->annual_verification_time)
                    $officialAccount->annual_verification_time = $this->annual_verification_time;

                // $officialAccount->is_annual_validity = $this->is_annual_validity;

                if($this->attention_link)
                    $officialAccount->attention_link = $this->attention_link;

                if(isset($this->status))
                    $officialAccount->status = $this->status;

                if(isset($this->group_id))
                    $officialAccount->group_id = $this->group_id;

                $officialAccount->company_id = $this->company_id;
                if ($officialAccount->save()) {
                    return ["code"=>0, "id"=>$officialAccount->id];
                }else{
                    $errorMsg = $officialAccount->getErrors();
                    Yii::error(sprintf('Fail to create official account cos reason:(%s)', json_encode($officialAccount->getErrors())));
                    return ["code"=>-1, "msg"=>$errorMsg];
                }


            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to create official account cos reason:(%s) at (%s)', $e->getMessage(),$e->getLine()));
                return ["code"=>-1, "msg"=>$e->getMessage()];
            }
        }
        return ["code"=>-1, "msg"=>$this->getErrors()];
    }

    function createRandomStr($length){
        $str = array_merge(range(0,9),range('a','z'),range('A','Z'));
        shuffle($str);
        $str = implode('',array_slice($str,0,$length));
        return $str;
    }

    /*
     * modify official account
     */
    public function modify()
    {
        if ($this->validate()) {

            try{
                $official_account = OfficialAccount::findById($this->id, false);
                if(!$official_account) {
                    $this->addError("id", sprintf("The official account id(%d) does not exist.", $this->id));
                    return;
                }

                if(!is_null($this->weixin_id)) {
                    $official_account->weixin_id = $this->weixin_id;
                }

                if(!is_null($this->weixin_name)) {
                    $official_account->weixin_name = $this->weixin_name;
                }

                if(!is_null($this->weixin_password)) {
                    $official_account->weixin_password = $this->weixin_password;
                }

                // if(!is_null($this->official_id)) {
                //     $official_account->official_id = $this->official_id;
                // }

                if(!is_null($this->official_origin_id)) {
                    $official_account->official_origin_id = $this->official_origin_id;
                }

                if(!is_null($this->app_id)) {
                    $official_account->app_id = $this->app_id;
                }

                if(!is_null($this->app_secret)) {
                    $official_account->app_secret = $this->app_secret;
                }

                if(!is_null($this->encoding_aes_key)) {
                    $official_account->encoding_aes_key = $this->encoding_aes_key;
                }

                // if(!is_null($this->token)) {
                //     $official_account->token = $this->token;
                // }

                if(!is_null($this->is_verified)) {
                    $official_account->is_verified = $this->is_verified;
                }

                if(!is_null($this->admin_weixin_id)) {
                    $official_account->admin_weixin_id = $this->admin_weixin_id;
                }

                if(!is_null($this->admin_email)) {
                    $official_account->admin_email = $this->admin_email;
                }

                if(!is_null($this->operation_subject)) {
                    $official_account->operation_subject = $this->operation_subject;
                }

                // if(!is_null($this->operation_certificate_no)) {
                //     $official_account->operation_certificate_no = $this->operation_certificate_no;
                // }

                // if(!is_null($this->operator_name)) {
                //     $official_account->operator_name = $this->operator_name;
                // }

                if(!is_null($this->editor_id)) {
                    $official_account->editor_id = $this->editor_id;
                }

//                if(!is_null($this->auditor_id)) {
//                    $official_account->auditor_id = $this->auditor_id;
//                }

                if(!is_null($this->annual_verification_time)) {
                    $official_account->annual_verification_time = $this->annual_verification_time;
                }

                // if(!is_null($this->is_annual_validity)) {
                //     $official_account->is_annual_validity = $this->is_annual_validity;
                // }

                if(!is_null($this->attention_link)) {
                    $official_account->attention_link = $this->attention_link;
                }

                if(!is_null($this->status)) {
                    $official_account->status = $this->status;
                }

                if(!is_null($this->group_id)) {
                    $official_account->group_id = $this->group_id;
                }

                if(!is_null($this->company_id)) {
                    $official_account->company_id = $this->company_id;
                }

                if ($official_account->save(false)) {
                    return $official_account;
                }

            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to modify official account cos reason:(%s)', $e));
            }
        }

        return;
    }


    public static function getFansNumFromWechat($official_account_id){
        $wechat = WechatHelper::getWechat($official_account_id);
        $_date = date('Y-m-d');
        $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));
        $userSummary = $wechat->stats->userCumulate($start_date, $end_date);
        $pre_day_summary = $pre_day_summary = $userSummary['list'];
        if(!empty($pre_day_summary)){
            return $pre_day_summary['0']['cumulate_user'];
        }else{
            return 0;
        }

    }

    public static function getFansNumByAppId($app_id,$app_secret){
        $options = [
            "app_id"=>$app_id,
            "secret"=>$app_secret,

            /**
             * Guzzle 全局设置
             *
             * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
             */
            'guzzle' => [
                'timeout' => 30.0, // 超时时间（秒）
                //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
            ],
        ];
        try{
            $_date = date('Y-m-d');
            $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));
            $wechat = new Wechat($options);;
            $userSummary = $wechat->stats->userCumulate($start_date, $end_date);
            $pre_day_summary = $userSummary['list']['0'];
            return (int)$pre_day_summary['cumulate_user'];
        }catch (\Exception $e){
            Yii::error(sprintf('wechat error:(%s)', $e->getMessage()));
//            var_dump($e->getMessage());
            if(strpos($e->getMessage(),'invalid appid hint')){
                return "appid错误";
            }elseif (strpos($e->getMessage(),'api unauthorized hint')){
                return "公众号未授权";
            }elseif (strpos($e->getMessage(),'invalid appsecret')){
                return "appsecret错误";
            }
        }

    }
}
