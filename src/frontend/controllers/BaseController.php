<?php

namespace frontend\controllers;

use common\models\ManagerLog;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use common\models\OfficialAccount;
use common\models\LoginForm;
use common\models\ContactForm;
use common\helpers\WechatHelper;

use EasyWeChat\Foundation\Application as WeChat;

use OSS\OssClient;

class BaseController extends Controller
{
    public $enableCsrfValidation = false;
    public $wechat;
    public $oss_client;
    public $status_code_msg;
    public $oss_info;

    public function init() {
        parent::init();

        $this->oss_info = Yii::$app->params["ALIYUN_INFO"];
        $this->status_code_msg = Yii::$app->params['STATUS_CODE_MSG'];
        $this->oss_client = new OssClient($this->oss_info['KEY'], $this->oss_info['SECRET'], $this->oss_info['END_POINT']);
    }

    public function behaviors()
    {
        return [
            'access' => [

            'class' => 'yii\filters\AccessControl',

            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],

            'denyCallback' => function($rule, $action) {
                self::denyCallback($rule, $action);
            }
          ]
        ];
    }

    // 检查用户权限
    public function can($permission) {
        return;
    }

    public function beforeAction($action)
    {
        // your custom code here, if you want the code to run before action filters,
        // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
        if (!parent::beforeAction($action)) {
            return false;
        }

        // other custom code here
        if(Yii::$app->request->method === 'OPTIONS') {
            return false;
        }

        return true; // or false to not run the action
    }

    public static function denyCallback($rule, $action) {
        echo json_encode(["code"=>20007, "msg"=>"请重新登录"]);
        return;
    }

    public function getWechat($official_account_id) {
        return WechatHelper::getWechat($official_account_id);
    }

    // 过滤掉emoji表情
    function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    // 记录日志
    public function manageLog($official_account_id,$description){
        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;
        $model = new ManagerLog();
        $model -> user_id = $user_id;
        $model -> official_account_id = $official_account_id;
        $model -> company_id = $company_id;
        $model -> description = $description;
        $model -> ip = \EasyWeChat\Payment\get_client_ip();
        $model -> created_at = time();
        try{
            $model->save();
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to save manager log cos reason:(%s)', $e->getMessage()));
        }
    }


    public function createRandomStr($length){
        $str = array_merge(range(0,9),range('a','z'),range('A','Z'));
        shuffle($str);
        $str = implode('',array_slice($str,0,$length));
        return $str;
    }

    public function Export($format,$data_list,$title)
    {

        $col_title="";
        $data="";

        $flag = false;
//var_dump($data_list);exit;
        foreach($data_list as $row) {

            if(!$flag) {
                $flag = true;

//                foreach($row as $key=>$value) {
//                    $col_title .= '<Cell ss:StyleID="2"><Data ss:Type="String">'.$key.'</Data></Cell>';
//                }

                foreach($format as $key=>$value) {
                    $col_title .= '<Cell ss:StyleID="2"><Data ss:Type="String">'.$key.'</Data></Cell>';
                }

                $col_title = '<Row>'.$col_title.'</Row>';
            }


            $line = '';
            foreach($row as $value) {
                if (!isset($value)) {
                    $value_data = '<Cell ss:StyleID="1"><Data ss:Type="String"></Data></Cell>';
                } else {
                    if(gettype($value) == 'string'){
                        $value = str_replace('"', '', $value);
                        $value_data = '<Cell ss:StyleID="1"><Data ss:Type="String">' . $value . '</Data></Cell>';
                    }elseif(gettype($value) == 'integer'){
                        $value_data = '<Cell ss:StyleID="1"><Data ss:Type="Number">' . $value . '</Data></Cell>';
                    }else{
                        $value_data = '<Cell ss:StyleID="1"><Data ss:Type="Number">' . $value . '</Data></Cell>';
                    }

                }
                $line .= $value_data;
            }
            $data .= trim("<Row>".$line."</Row>")."\n";
        }

        $data = str_replace("\r","",$data);

        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Disposition: attachment; filename=export.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        $xls_header = '<?xml version="1.0" encoding="utf-8"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
    <Author></Author>
    <LastAuthor></LastAuthor>
    <Company></Company>
    </DocumentProperties>
    <Styles>
    <Style ss:ID="1">
    <Alignment ss:Horizontal="Left"/>
    </Style>
    <Style ss:ID="2">
    <Alignment ss:Horizontal="Left"/>
    <Font ss:Bold="1"/>
    </Style>

    </Styles>
    <Worksheet ss:Name="Export">
    <Table>';

        $xls_footer = '</Table>
    <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
	<Selected/>
	<FreezePanes/>
	<FrozenNoSplit/>
	<SplitHorizontal>1</SplitHorizontal>
	<TopRowBottomPane>1</TopRowBottomPane>
	</WorksheetOptions>
	</Worksheet>
	</Workbook>';

        print $xls_header.$col_title.$data.$xls_footer;
        //print $xls_header.$xls_footer;
        exit;
    }

}
