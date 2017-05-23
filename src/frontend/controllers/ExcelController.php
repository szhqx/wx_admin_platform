<?php

namespace frontend\controllers;

use common\helpers\WechatHelper;
use common\models\Customer;
use common\models\OfficialAccount;
use common\models\OfficialGroup;
use common\models\User;
use EasyWeChat\Foundation\Application as WeChat;

use Yii;


class ExcelController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionExport()
    {

        if(!Yii::$app->exAuthManager->can('excel/export')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_data = $this->_getOfficialData();
//        var_dump($official_data);exit;

        $list = [];
        foreach($official_data['official_account_list'] as $k=>$v){
            $list[] = [
                "weixin_id" => $v['weixin_id'],
                "weixin_name" => $v['weixin_name'],
                "weixin_password" => $v['weixin_password'],
                "official_origin_id" => $v['official_origin_id'],
                "app_id" => $v['app_id'],
                "app_secret" => $v['app_secret'],
                "editor_info" => $v['editor_info']['nickname'],
                "auditor_info" => $v['auditor_info']['nickname'],
                "admin_weixin_id" => $v['admin_weixin_id'],
                "operation_subject" => $v['operation_subject'],
                "annual_verification_time" => date("Y-m-d",$v['annual_verification_time']),
                "group_info" => $v['group_info']['name'],
                "fans_num" => $v['fans_num'],
            ];
        }

        $format = [
            "公众号" => 'weixin_id',
            "公众号名称" => 'weixin_name',
            "公众号密码" => 'weixin_password',
            "原始id" => 'official_origin_id',
            "AppID" => 'app_id',
            "AppSecret" => 'app_secret',
            "编辑人员" => 'editor_info',
            "审核人员" => 'auditor_info',
            "管理员微信号" => 'admin_weixin_id',
            "运营主体" => 'operation_subject',
            "年审有效期" => 'annual_verification_time',
            "类别" => 'group_info',
            "粉丝数" => 'fans_num',
        ];
        $title = "公众号列表";
        $this->Export($format,$list,$title);

    }

    public function actionImport() {

        if(!Yii::$app->exAuthManager->can('excel/import')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $file = $_FILES['file'];

        $file_type = $file['type'];
        try{
            //        if($file_type == 'application/octet-stream') {
            $excelFile = $file['tmp_name'];//获取文件名
            $excelReader = \PHPExcel_IOFactory::createReader('Excel5');
            $phpexcel = $excelReader->load($excelFile)->getSheet(0);//载入文件并获取第一个sheet

            $total_line = $phpexcel->getHighestRow();

            $data = [];
            for ($row = 4; $row <= $total_line; $row++) {
                for ($column = 'B'; $column <= 'M'; $column++) {
//                    if(trim($phpexcel->getCell($column . $row)->getValue()) == null){
//                        continue;
//                    }
                    $data[$row][] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$phpexcel->getCell($column . $row)->getValue());
                }
            }

            $msg = [];
            $newdata = [];
            $appid_list = [];

            foreach ($data as $k1 => $v1) {

                trim($v1['4']);
                trim($v1['5']);
                Yii::info($v1[1]);
                if ($v1[0] == '') {
//                    $msg[] = $v1[1]."  微信号不能为空";
                    Yii::error($v1[1]."  微信号不能为空");
                    continue;
                }

                if($this->check($v1[4])){
                    $msg[] = $v1[1]."  之前有添加过";
                    Yii::info($v1[1]."  之前有添加过");
                    continue;
                }

                $fans_num = $this->_getFansNum($v1[4],$v1[5]);

                if(is_string($fans_num)){
                    Yii::info($v1[1]."  ".$fans_num);
                    $msg[] = $v1[1]."  ".$fans_num;
                    continue;
                }
                if(in_array($v1[4],$appid_list)){
                    Yii::info($v1[1]."  是重复数据");
                    $msg[] = $v1[1]."  是重复数据";
                    continue;
                }
                foreach ($v1 as $k2 => $v2) {
                    if ($k2 == 11) {
                        if(empty($v2)){
                            $newdata[$k1][$k2] = 0;
                        }else{
                            $newdata[$k1][$k2] = strtotime($v2);
                        }
                    } elseif ($k2 == 8) {
                        $newdata[$k1][$k2] = User::getIdByNickName($v2);
                    } elseif ($k2 == 9) {
                        $newdata[$k1][$k2] = User::getIdByNickName($v2);
                    }  elseif ($k2 == 6) {
                        $newdata[$k1][$k2] = $this->getGroupId($v2);
                    } else {
                        $newdata[$k1][$k2] = $v2;
                    }
                }
                $newdata[$k1][] = time();
                $newdata[$k1][] = Yii::$app->user->identity->company_id;
                $newdata[$k1][] = $this->createRandomStr(10);
                $newdata[$k1][] = $this->createRandomStr(43);
                $newdata[$k1][] = 1;
                $newdata[$k1][] = $fans_num;

                $appid_list[] = $v1[4];

            }

            unset($data);

//            Yii::info(\GuzzleHttp\json_encode($msg));
            if(empty($newdata)){
                return json_encode(["code"=>0, "msg"=>"插入数据为空或者已存在","data_msg"=>$msg]);
            }

            Yii::$app->db->createCommand()
                ->batchInsert(OfficialAccount::tableName(),
                    [
                        'weixin_id',   //B
                        'weixin_name',   //C
                        'weixin_password',   //D
                        'official_origin_id',   //E
                        'app_id',   //F
                        'app_secret',   //G
                        'group_id',
                        'admin_weixin_id',   //H
                        'editor_id',   //K
                        'auditor_id',   //L
                        'attention_link',   //M
                        'annual_verification_time',   //N
                        'created_at',
                        'company_id',
                        'token',
                        'encoding_aes_key',
                        'status',
                        'fans_num',
                    ], $newdata)
                ->execute();

            $official_appid_info = OfficialAccount::find()->where(['in','app_id',$appid_list])->select(['id'])->asArray()->all();
            $official_id_list = array_column($official_appid_info,'id');

            foreach($official_id_list as $account_id){
                $mixedData = [
                    "official_account_id"=>$account_id,
                    "page"=>1,
                    "num"=>20
                ];
                $delay = 0;
                $priority = time();
                $tube_news = Yii::$app->params['QUEUE_MATERIAL_ARTICLE'];
                $tube_image = Yii::$app->params['QUEUE_ONCE_MATERIAL_IMAGE'];
                $tube_statistic = Yii::$app->params['QUEUE_SYNC_STATISTIC'];
                $tube_menu = Yii::$app->params['QUEUE_SYNC_MENU'];
                $tube_reply = Yii::$app->params['QUEUE_SYNC_REPLY'];

                Yii::$app->beanstalk->putInTube($tube_news, $mixedData , $priority, $delay);
                Yii::$app->beanstalk->putInTube($tube_image, $mixedData , $priority, $delay);
                Yii::$app->beanstalk->putInTube($tube_statistic, $mixedData , $priority, $delay);
                Yii::$app->beanstalk->putInTube($tube_menu, $mixedData , $priority, $delay);
                Yii::$app->beanstalk->putInTube($tube_reply, $mixedData , $priority, $delay);
            }

            $this->manageLog(0,'导入公众号列表');
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data_msg"=>$msg]);
        }catch (\Exception $e){
            Yii::error(\GuzzleHttp\json_encode($e->getMessage()));
            return json_encode(["code"=>-1, "msg"=>"导入失败"]);

        }
    }

    public function actionDownload()
    {
        header("Content-type:text/html;charset=utf-8");
        $file_name="example.xls";
        $file_name=iconv("utf-8","gb2312",$file_name);
        $file_sub_path=Yii::$app->BasePath.'/web/assets/import_tem.xls';

        if(!file_exists($file_sub_path))
        {
            echo "没有该文件文件";
            return ;
        }

        $fp=fopen($file_sub_path,"r");
        $file_size=filesize($file_sub_path);

        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }

    /*********** 导入客户数据 ***********/
    public function actionImportCustomer(){
        $file = $_FILES['file'];
        $company_id = Yii::$app->user->identity->company_id;
        try {
            $excelFile = $file['tmp_name'];//获取文件名
            $excelReader = \PHPExcel_IOFactory::createReader('Excel5');
            $phpexcel = $excelReader->load($excelFile)->getSheet(0);//载入文件并获取第一个sheet
            $total_line = $phpexcel->getHighestRow();
            $data = [];
            for ($row = 3; $row <= $total_line; $row++) {
                for ($column = 'B'; $column <= 'H'; $column++) {
                    $data[$row][] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $phpexcel->getCell($column . $row)->getValue());
                }
            }
            $newdata = [];
            $msg = [];
            $customer = [];
            foreach ($data as $k1 => $v1) {

                if ($v1[0] == '') {
                    continue;
                }
                if ($v1[1] == '') {
                    $msg[] = $v1[1]."  qq不能为空";
                    continue;
                }
                if (in_array($v1[0],$customer)){
                    $msg[] = "客户昵称 ".$v1[0]."  重复数据";
                    continue;
                }
                if ($this->_checkCustomerExist($v1[0],$company_id)){
                    $msg[] = "客户昵称 ".$v1[0]."  重复数据";
                    continue;
                }

                foreach ($v1 as $k2 => $v2) {
                    $newdata[$k1][$k2] = ($v2 == '') ? null : $v2 ;
                }
                $newdata[$k1][] = time();
                $newdata[$k1][] = $company_id;
                $customer[] = $v1[0];
            }

            if(empty($newdata)){
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data_msg"=>$msg]);
            }
            Yii::$app->db->createCommand()
                ->batchInsert(Customer::tableName(),
                    [
                        'customer',   //B
                        'qq',   //E
                        'realname',   //C
                        'wechat_id',
                        'tel',   //D
                        'company',   //F
                        'mark',   //G
                        'created_at',
                        'company_id'
                    ], $newdata)
                ->execute();

            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data_msg"=>$msg]);

        }catch (\Exception $e){
            Yii::error(\GuzzleHttp\json_encode($e->getMessage()));
            return json_encode(["code"=>-1, "msg"=>"导入失败"]);
        }
    }

    public function actionDownloadCustomer(){
        header("Content-type:text/html;charset=utf-8");
        $file_name="customer.xls";
        $file_name=iconv("utf-8","gb2312",$file_name);
        $file_sub_path=Yii::$app->BasePath.'/web/assets/customer.xls';

        if(!file_exists($file_sub_path))
        {
            echo "没有该文件文件";
            return ;
        }

        $fp=fopen($file_sub_path,"r");
        $file_size=filesize($file_sub_path);

        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }

    private function getGroupId($group_name){
        $res = OfficialGroup::find()->where(['company_id'=>Yii::$app->user->identity->company_id,'name'=>$group_name])->one();
        if($res){
            return $res->id;
        }
        return 0;
    }

    public function check($app_id){
        $model = OfficialAccount::find();
        $res = $model->where(['app_id'=>$app_id])->one();
        if($res){
            if($res->status == 0){
                $res->status = 1;
                $res->company_id = Yii::$app->user->identity->company_id;
                $res->updated_at = time();
                $res->save();
                return true;
            }
            return true;
        }

        return false;

    }

    private function _getFansNum($app_id,$app_secret){
        $options = [
            "app_id"=>$app_id,
            "secret"=>$app_secret
        ];
        try{
            $_date = date('Y-m-d');
            $start_date = $end_date = date('Y-m-d', strtotime($_date .' -1 day'));
            $wechat = new Wechat($options);
            $userSummary = $wechat->stats->userCumulate($start_date, $end_date);
            $pre_day_summary = $userSummary['list']['0'];
            return (int)$pre_day_summary['cumulate_user'];
        }catch (\Exception $e){
            Yii::error(sprintf('wechat error:(%s)', $e->getMessage()));
            if(strpos($e->getMessage(),'invalid appid hint')){
                return "appid错误";
            }elseif (strpos($e->getMessage(),'api unauthorized hint')){
                return "公众号未认证";
            }
            return "app_id 或者 app_secret错误";
        }
    }

    /*
     * 获取要导出的公众号数据
     * */
    private function _getOfficialData(){

        $page = (int)Yii::$app->request->post('page', 1);
        $num = (int)Yii::$app->request->post('num', 400); //这里先写死，后期优化 todo
        $keyword = Yii::$app->request->post('keyword', null);
        $group_id = Yii::$app->request->post('group_id', null);
        $editor_id = Yii::$app->request->post('editor_id', null);
        $attention_range_start = Yii::$app->request->post('fans_num_range_start', 0);
        $attention_range_end = Yii::$app->request->post('fans_num_range_end', 900000000);
        // $auditor_id = Yii::$app->request->post('auditor_id', null);

        // prepare params
        $params = [
            "company_id"=>Yii::$app->user->identity->company_id,
            "status"=>1
        ];

        // 数据权限校验
        $current_user = Yii::$app->user;
        $editor_id_list = Yii::$app->dataAuthManager->getEditorChildUidList($current_user);

        if($editor_id_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($editor_id_list !== true) {

            if($editor_id_list === []) {
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>["official_account_list"=>[], "page_num"=>10]]);
            }

            if($editor_id and !in_array($editor_id, $editor_id_list)) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }

            if(!is_null($editor_id)) {
                $params['editor_id'] = $editor_id;
            } else {
                $params['editor_id_list'] = $editor_id_list;
            }

        } else  {

            if(!is_null($editor_id)) {
                $params['editor_id'] = $editor_id;
            }

        }

        if(!is_null($keyword)) {
            $params['weixin_name'] = $keyword;
        }

        if(!is_null($group_id)) {
            $params['group_id'] = $group_id;
        }

        // if(!is_null($auditor_id)) {
        //     $params['auditor_id'] = $auditor_id;
        // }

        if(!is_null($attention_range_start)) {
            $params['attention_range_start'] = $attention_range_start;
        }

        if(!is_null($attention_range_end)) {
            $params['attention_range_end'] = $attention_range_end;
        }

        $final_data = OfficialAccount::getList($params, $page, $num);

        return $final_data;
    }

    private function _checkCustomerExist($customer_name,$company_id){
        $customer = New Customer();
        $res  = $customer->find()->where(['company_id'=>$company_id,'customer'=>$customer_name])->one();
        if($res){
            return true;
        }
        return false;
    }


}