<?php

namespace frontend\controllers;
use common\models\Advertisement;
use common\models\AdvertisementOfficial;
use common\models\AdvertisementType;
use common\models\Customer;
use common\models\Material;
use common\models\MaterialForm;
use common\models\OfficialAccount;
use common\models\Teller;
use common\models\User;
use Yii;


class AdvertiseController extends BaseController
{

    /*********** 订单模块 ***********/

    /*
     * 广告订单列表
     * */
    public function actionGetList(){

        if(!Yii::$app->exAuthManager->can('advertise/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $status = Yii::$app->request->get('status', 0);
        $user_id = Yii::$app->request->get('user_id',null);
        $receipt_date = Yii::$app->request->get('receipt_date',null);
        $customer = Yii::$app->request->get('customer', null);

        // 数据权限校验
        $current_user = Yii::$app->user->identity;
        $user_id_list = Yii::$app->dataAuthManager->getAdvertiseChildUidList($current_user);
//        var_dump($user_id_list);

        if($user_id_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($user_id_list !== true) {

            if($user_id_list === []) {
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>["list"=>[], "page_num"=>10]]);
            }

            if($user_id and !in_array($user_id, $user_id_list)) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }

            if(!is_null($user_id)) {
                $params['user_id'] = $user_id;
            } else {
                $params['user_id_list'] = $user_id_list;
            }

        } else  {

            if(!is_null($user_id)) {
                $params['user_id'] = $user_id;
            }

        }

        if(!is_null($status)) {
            $params['status'] = $status;
        }if($receipt_date) {
            $params['receipt_date'] = date("Y-m-d",$receipt_date);
        }if(!is_null($customer)) {
            $params['customer'] = $customer;
        }
        $final_data = Advertisement::getList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /*
     * 添加广告订单
     * */
    public function actionAddOrder(){

        if(!Yii::$app->exAuthManager->can('advertise/add-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // 数据权限校验
        $current_user = Yii::$app->user->identity;
        if(!Yii::$app->dataAuthManager->canAddAdvertisement($current_user)){
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        $receipt_date = Yii::$app->request->post('receipt_date',time());
        $customer_id = Yii::$app->request->post('customer_id',null);
        $order_amount = (int)Yii::$app->request->post('order_amount',0);
        $deposit = (int)Yii::$app->request->post('deposit',0);
        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;
        $now = time();

        $order_id = Advertisement::genOrderId($user_id);

        $model = new Advertisement();
        $model -> user_id = $user_id;
        $model -> receipt_date = $receipt_date;
        $model -> customer_id = $customer_id;
        $model -> order_amount = $order_amount;
        $model -> deposit = $deposit;
        $model -> company_id = $company_id;
        $model -> order_id = $order_id; // TODO 有一定的几率失败，细到milseconds，看需要再调整算法
        $model -> created_at = $now;
        if(!$model ->save()){
            $transaction->rollBack();
            Yii::error(sprintf("failed to save advertisement cos (%s) at (%s)",$model->getErrors(),date("Y-m-d H:i:s")));
            return json_encode(["code"=>-1, "msg"=>$model->getErrors()]);
        }

        $id = $model->id;

        $order_info = Yii::$app->request->post('order_info',[]);

        //判断广告位置和日期是否被其他订单占用
        $order_total_amount = 0;
        $type_ids = [];
        foreach($order_info as $k=>$v){
            if(!$this->_checkPositionAndDateIsOK($v['ad_position'],$v['send_date'],$v['official_account_id'])){
                $transaction->rollBack();
                return json_encode(["code"=>-1, "msg"=>"公众号：".$v['official_account_id']." 位置：".$v['ad_position']." 发送日期：".date("Y-m-d",$v['send_date'])." 被占用"]);
            }
            if($v['send_date'] < strtotime(date("Y-m-d",$now))){
                $transaction->rollBack();
                return json_encode(["code"=>-1, "msg"=>"发送日期不能小于今天"]);
            }
            $order_total_amount +=  $v['amount'];
            $type_ids[] = $v['type_id'];
        }

        $this->_saveCustomerAdType($customer_id,$type_ids);
        if($order_total_amount !== $order_amount || $order_total_amount==0 || $order_amount==0){
            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>"订单总价和每个公众号单价不相等"]);
        }
        $data = [];
        foreach($order_info as $k=>$v){
            $data[] = [$id,$v['ad_position'],$v['retain_day'],$v['type_id'],
                $v['official_account_id'],$v['send_date'],$now,$v['amount'],Yii::$app->user->identity->company_id,
                $v['million_fans_price']
            ];
        }

//        var_dump($data);exit;
        Yii::$app->db->createCommand()
            ->batchInsert(AdvertisementOfficial::tableName(), [
                'ad_id','ad_position','retain_day',
                'type_id','official_account_id','send_date',
                'created_at','amount','company_id','million_fans_price'
            ], $data)
            ->execute();

        $ad_off_info = AdvertisementOfficial::find()
            ->select(['official_account_id','ad_id','id','send_date'])
            ->where(['ad_id'=>$id])->asArray()->all();
//            var_dump($ad_off_info);exit;
        $data = [];
        foreach ($ad_off_info as $k=>$v){
            $data[] = [
                "official_account_id"=>$v['official_account_id'],
                "id" =>$v['id'],
                "ad_id" =>$v['ad_id'],
                "send_date" =>date("Y-m-d",$v['send_date'])
            ];
        }

        $res = $this->_addMaterial($data);

        if($res == 0){
            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>"公众号错误"]);

        }
        $this->manageLog(0,'添加广告订单');
        $transaction->commit();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*
     * 修改广告订单
     * */
    public function actionModifyOrder(){

        if(!Yii::$app->exAuthManager->can('advertise/modify-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $transaction = Yii::$app->db->beginTransaction();

        $order_id = Yii::$app->request->post('order_id',null);
        $receipt_date = Yii::$app->request->post('receipt_date',time());
        $customer_id = Yii::$app->request->post('customer_id',null);
        $order_amount = (int)Yii::$app->request->post('order_amount',0);
        $deposit = (int)Yii::$app->request->post('deposit',0);
        $model = Advertisement::findByOrderId($order_id);
        if(!$model){
            return json_encode(["code"=>-1, "msg"=>"order_id错误"]);
        }
        $ad_id = $model -> id;
        // 数据权限校验
        $current_user = Yii::$app->user->identity;
        if(!Yii::$app->dataAuthManager->canModifyAdvertisement($model, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $now = time();
        $date = date("Y-m-d H:i:s");
//        var_dump($post_content);exit;
        $is_modify = $this->_checkIsDelete($order_id);
        if($is_modify){
            try{
                $model -> user_id = Yii::$app->user->getId();
                $model -> receipt_date = $receipt_date;
                $model -> customer_id = $customer_id;
                $model -> order_amount = $order_amount;
                $model -> deposit = $deposit;
                $model -> updated_at = $now;
                if(!$model ->update()){
                    $transaction->rollBack();
                    Yii::error(sprintf("failed to save advertisement cos (%s) at (%s)",$model->getErrors(),$date));
                    return json_encode(["code"=>-1, "msg"=>$model->getErrors()]);
                }
                $data = [];
                $order_info = Yii::$app->request->post('order_info',[]);

                //判断广告位置和日期是否被其他订单占用
                $order_total_amount = 0;
                $type_ids = [];
                foreach($order_info as $k=>$v){
                    if((int)$v['id_son'] < 0){
                        continue;
                    }
//                    if(!$this->_checkPositionAndDateIsOK($v['ad_position'],$v['send_date'],$v['official_account_id'])){
//                        return json_encode(["code"=>-1, "msg"=>"公众号：".$v['official_account_id']." 位置：".$v['ad_position']." 发送日期：".date("Y-m-d",$v['send_date'])." 被占用"]);
//                    }
                    if($v['send_date'] < strtotime(date("Y-m-d",$now))){
                        $transaction->rollBack();
                        return json_encode(["code"=>-1, "msg"=>"发送日期不能小于今天"]);
                    }
                    $order_total_amount +=  $v['amount'];
                    $type_ids[] = $v['type_id'];

                }

                $this->_saveCustomerAdType($customer_id,$type_ids);

                if((($order_total_amount !== $order_amount) && ($order_total_amount>0) && ($order_amount>0))){
                    $transaction->rollBack();
                    return json_encode(["code"=>-1, "msg"=>"订单总价和每个公众号单价不相等"]);
                }
                $ids = [];

                foreach($order_info as $k=>$v){
                    $advertise_model = new AdvertisementOfficial();
                    if((int)$v['id_son'] < 0){
                        $advertise_model::findById(abs($v['id_son']))->delete();
                        continue;
                    }
                    $advertise_official = $advertise_model::findById($v['id_son']);
                    if($advertise_official){

                        $advertise_official -> official_account_id = $v['official_account_id'];
                        $advertise_official -> ad_position = $v['ad_position'];
                        $advertise_official -> retain_day = $v['retain_day'];
                        $advertise_official -> send_date = $v['send_date'];
                        $advertise_official -> type_id = $v['type_id'];
                        $advertise_official -> million_fans_price = $v['million_fans_price'];
                        $advertise_official -> amount = $v['amount'];
                        $advertise_official -> updated_at = $now;
                        $advertise_official -> update();
                    }else{
                        $advertise_model -> ad_id = $ad_id;
                        $advertise_model -> official_account_id = $v['official_account_id'];
                        $advertise_model -> ad_position = $v['ad_position'];
                        $advertise_model -> retain_day = $v['retain_day'];
                        $advertise_model -> send_date = $v['send_date'];
                        $advertise_model -> type_id = $v['type_id'];
                        $advertise_model -> million_fans_price = $v['million_fans_price'];
                        $advertise_model -> amount = $v['amount'];
                        $advertise_model -> created_at = $now;
                        $advertise_model -> save();
                        $ids [] = $advertise_model->id;
                    }
                }
                if(count($data)){
                    $ad_off_info = AdvertisementOfficial::find()
                        ->select(['official_account_id','ad_id','id','send_date'])
                        ->where(['in','id',$ids])->asArray()->all();
                    $data_list = [];
                    foreach ($ad_off_info as $k=>$v){
                        $data_list[] = [
                            "official_account_id"=>$v['4'],
                            "id" =>$v['id'],
                            "ad_id" =>$v['ad_id'],
                            "send_date" =>date("Y-m-d",$v['send_date'])
                        ];
                    }
                    $res = $this->_addMaterial($data_list);
                    if($res == 0){
                        $transaction->rollBack();
                        return json_encode(["code"=>-1, "msg"=>"公众号错误"]);
                    }
                }
                $this->manageLog(0,'修改广告订单');
                $transaction->commit();
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }catch (\Exception $e){
                $transaction->rollBack();
                Yii::error(sprintf("fail to modify advertisement cos to (%s)",$e->getMessage()),__METHOD__);
                return json_encode(["code"=>-1, "msg"=>"修改失败"]);
            }
        }
        $transaction->rollBack();
        return json_encode(["code"=>-1, "msg"=>"此订单无法修改"]);

    }

    /*
     * 删除广告订单
     * */
    public function actionDeleteOrder(){

        if(!Yii::$app->exAuthManager->can('advertise/delete-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id = Yii::$app->request->get('id');
        $current_user = Yii::$app->user->identity;
        $advertisement = Advertisement::findByOrderId($id);
        if(!$advertisement){
            return json_encode(["code"=>-1, "msg"=>"无效id"]);
        }

        if(!Yii::$app->dataAuthManager->canModifyAdvertisement($advertisement, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $is_delete = $this->_checkIsDelete($advertisement->id);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        if($is_delete){
            try{
                $material_id_list = array_column(AdvertisementOfficial::find()->where(['ad_id'=>$advertisement->id])->asArray()->all(),'material_id');
                $db->createCommand()->delete(AdvertisementOfficial::tableName(),['ad_id'=>$advertisement->id])->execute();
                $advertisement->delete();
                foreach ($material_id_list as $material_id){
                    $db->createCommand()->delete(Material::tableName(),['id'=>$material_id])->execute();
                    $db->createCommand()->delete(Material::tableName(),['parent_id'=>$material_id])->execute();
                }
                $transaction ->commit();

                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }catch (\Exception $e){
                $transaction->rollBack();
                Yii::error(sprintf("fail to delete advertisement cos to (%s)",$e->getMessage()),__METHOD__);
                return json_encode(["code"=>-1, "msg"=>"删除失败"]);
            }
        }
        return json_encode(["code"=>-1, "msg"=>"此订单无法删除"]);

    }

    /*********** 广告类型模块 ***********/

    /*
     * 广告类型列表
     * */
    public function actionTypeList(){

        if(!Yii::$app->exAuthManager->can('advertise/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        //这里暂时不做分页，因为数据量很小
        $list = AdvertisementType::getList();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$list]);
    }

    /*
     * 添加广告类型
     * */
    public function actionAddType(){
        if(!Yii::$app->exAuthManager->can('advertise/add-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $name = Yii::$app->request->post('name',null);
        $p_id = Yii::$app->request->post('p_id',null);
        if(is_null($name)){
            return json_encode(["code"=>-1, "msg"=>"请填写名称"]);
        }
        $company_id = Yii::$app->user->identity->company_id;
        $model = new AdvertisementType();
        $model -> parent_id = $p_id;
        $model -> name = $name;
        $model -> company_id = $company_id;
        $model -> created_at = time();
        $is_exist_name = $model->find()->where(['company_id'=>$company_id,'name'=>$name])->one();
        if($is_exist_name){
            return json_encode(["code"=>-1, "msg"=>"名字已经存在"]);
        }
        if(!$model -> save()){
            return json_encode(["code"=>-1, "msg"=>"添加失败"]);
        }
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*
     * 修改广告类型
     * */
    public function actionModifyType(){
        if(!Yii::$app->exAuthManager->can('advertise/modify-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $name = Yii::$app->request->post('name',null);
        $id = Yii::$app->request->post('id',null);
        if(is_null($name) || is_null($id)){
            return json_encode(["code"=>-1, "msg"=>"请填写名称和id"]);
        }
        $model = AdvertisementType::findById($id);
        $model -> name = $name;
        $model -> updated_at = time();

        $is_exist_name = AdvertisementType::find()
            ->where(['company_id'=>Yii::$app->user->identity->company_id,'name'=>$name])->one();
        if($is_exist_name){
            return json_encode(["code"=>-1, "msg"=>"名字已经存在"]);
        }

        if(!$model -> update()){
            return json_encode(["code"=>-1, "msg"=>"修改失败"]);
        }
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*
     * 删除广告类型
     * */
    public function actionDeleteType(){

        if(!Yii::$app->exAuthManager->can('advertise/delete-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id = Yii::$app->request->get('id',null);
        if(is_null($id)){
            return json_encode(["code"=>-1, "msg"=>"请填写id"]);
        }
        $model = AdvertisementType::findById($id);
        if($model->parent_id == 0){
            $son = AdvertisementType::find()
                ->where(['company_id'=>Yii::$app->user->identity->company_id,'parent_id'=>$model->id])
                ->asArray()->all();
            if(isset($son['0']['id'])){
                $ids = array_column($son,'id');
            }
            $ids[] = $id;
            Yii::$app->db->createCommand()->delete(AdvertisementType::tableName(),['in','id',$ids])->execute();
        }else{
            if(!$model -> delete()){
                return json_encode(["code"=>-1, "msg"=>"删除失败"]);
            }
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*********** 客户模块 ***********/

    /*
     * 客户列表
     * */
    public function actionCustomerList(){

        if(!Yii::$app->exAuthManager->can('advertise/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $created_at = Yii::$app->request->get('created_at',null);
        $customer = Yii::$app->request->get('customer', null);

        if($created_at) {
            $params['created_at'] = strtotime(date("Y-m-d",$created_at));
        }if(!is_null($customer)) {
            $params['customer'] = $customer;
        }
        $final_data = Customer::getList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /*
     * 添加客户
     * */
    public function actionCustomerAdd(){

        if(!Yii::$app->exAuthManager->can('advertise/add-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $customer = Yii::$app->request->post('customer',null);
        $realname = Yii::$app->request->post('realname',null);
        $tel = Yii::$app->request->post('tel',null);
        $qq = Yii::$app->request->post('qq',null);
        $company = Yii::$app->request->post('company',null);
        $mark = Yii::$app->request->post('mark',null);
        $wechat_id = Yii::$app->request->post('wechat_id',null);
        $company_id = Yii::$app->user->identity->company_id;

        if($customer == "null" || $qq == "null"){
            return json_encode(["code"=>-1, "msg"=>"id或者客户姓名必填"]);
        }

        if(is_null($qq) || is_null($customer)){
            return json_encode(["code"=>-1, "msg"=>"id或者客户姓名必填"]);
        }
        $model = new Customer();

        if($realname !== "null" && !is_null($realname)){
            $model->realname = $realname;
        }
        if($tel !== "null" && !is_null($tel)){
            $model->tel = $tel;
        }
        if($company !== "null" && !is_null($company)){
            $model->tel = $tel;
        }
        if($mark !== "null" && !is_null($mark)){
            $model->tel = $tel;
        }
        if($wechat_id !== "null" && !is_null($wechat_id)){
            $model->tel = $tel;
        }
        $model->customer = $customer;
        $model->qq = $qq;
        $model->company_id = $company_id;
        $model->created_at = time();

        $is_exist_name = $model->find()->where(['company_id'=>$company_id,'customer'=>$customer])->one();
        if($is_exist_name){
            return json_encode(["code"=>-1, "msg"=>"名字已经存在"]);
        }
        if($model->save()){
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        return json_encode(["code"=>-1, "msg"=>\GuzzleHttp\json_encode($model->getErrors())]);
    }

    /*
     * 修改客户
     * */
    public function actionCustomerModify(){

        if(!Yii::$app->exAuthManager->can('advertise/modify-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        try{

            $id = Yii::$app->request->post('id',null);
            $customer = Yii::$app->request->post('customer',null);
            $realname = Yii::$app->request->post('realname',null);
            $tel = Yii::$app->request->post('tel', null);
            $qq = Yii::$app->request->post('qq', null);
            $company = Yii::$app->request->post('company',null);
            $mark = Yii::$app->request->post('mark',null);
            $wechat_id = Yii::$app->request->post('wechat_id',null);
            $company_id = Yii::$app->user->identity->company_id;

            if(is_null($id) || is_null($customer) || $id == "null" && $customer == "null"){
                return json_encode(["code"=>-1, "msg"=>"id或者客户姓名必填"]);
            }

            $model = Customer::findById($id);
            if(!$model){
                return json_encode(["code"=>-1, "msg"=>"id错误"]);
            }

            $model->customer = $customer;
            $model->qq = $qq;
            $model->company_id = $company_id;


            $option_param_list = [
                "tel"=>$tel,
                "realname"=>$realname,
                "company"=>$company,
                "mark"=>$mark,
                "wechat_id"=>$wechat_id
            ];

            // hack for empty value for fe support
            foreach($option_param_list as $key=>$option) {

                if(!is_null($option)) {

                    if($option == 'null') {
//                        $model->$key = null;
                        continue;
                    }

                    $model->$key = $option;
                    continue;
                }
            }

            $model->updated_at = time();

//        $is_exist_name = Customer::find()->where(['company_id'=>$company_id,'customer'=>$customer])->one();
//        if($is_exist_name){
//            return json_encode(["code"=>-1, "msg"=>"名字已经存在"]);
//        }

            if($model->update()){
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }
            Yii::error(sprintf("failed to modify cos to (%s)",json_encode($model->getErrors())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }catch (\Exception $e){
            Yii::error(sprintf("failed to modify cos to (%s)",json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
    }

    /*
     * 删除客户
     * */
    public function actionCustomerDelete(){

        if(!Yii::$app->exAuthManager->can('advertise/delete-order')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id = Yii::$app->request->get('id',null);
        if(is_null($id)){
            return json_encode(["code"=>-1, "msg"=>"id不能为空"]);
        }
        $model = Customer::findById($id);
        if($model -> delete()){
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

    }

    /*********** 出纳流水模块 ***********/

    /*
     * 出纳列表
     * */
    public function actionTellerList(){

        if(!Yii::$app->exAuthManager->can('advertise/teller-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $customer = Yii::$app->request->get('customer', null);

        $date = Yii::$app->request->get('receipt_date', null);

        if(!is_null($date)){
            $from = strtotime(date("Y-m-d",$date));
            $to = $from+(60*60*24);
            $params['from'] = $from;
            $params['to'] = $to;
        }

//        $from = Yii::$app->request->get("start_time",$from);
//        $to = Yii::$app->request->get("end_time",$to);

        if(!is_null($customer)) {
            $params['customer'] = $customer;
        }
        $final_data = Teller::getList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }

    /*
     * 添加出纳流水
     * */
    public function actionAddTeller(){

        if(!Yii::$app->exAuthManager->can('advertise/add-teller')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $user_id = Yii::$app->user->getId();
        $now = time();
        $data = [];
        foreach($post_content['order_info'] as $k=>$v){
            $is_order_id = Advertisement::findById($v['order_id']);
            if(!$is_order_id){
                return json_encode(["code"=>-1, "msg"=>"没有此订单号 ".$v['order_id']]);
            }
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $model = new Teller();
            $model ->user_id = $user_id;
            $model ->receipt_date = $post_content['receipt_date'];
            $model ->customer = $post_content['customer'];
            $model ->order_comment = $post_content['order_comment'];
            $model ->receipt_bank_name = $post_content['receipt_bank_name'];
            $model ->receipt_bank_num = $post_content['receipt_bank_num'];
            $model ->pay_bank_name = $post_content['pay_bank_name'];
            $model ->pay_bank_num = $post_content['pay_bank_num'];
            $model ->amount_total = $post_content['amount_total'];
            $model ->company_id = Yii::$app->user->identity->company_id;
            $model ->created_at = $now;
            if($model ->save()){
                $id = $model->id;
                foreach($post_content['order_info'] as $k=>$v){
                    $data[] = [$id, $v['order_id'], $v['amount'],$now];
                }
                $db->createCommand()
                    ->batchInsert('teller_order', [
                        'teller_id','order_id','amount','created_at'
                    ], $data)
                    ->execute();
                $this->manageLog(0,'添加出纳流水');
                $transaction->commit();
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }
    }

    /*
     * 修改出纳流水
     * */
    public function actionModifyTeller(){

        if(!Yii::$app->exAuthManager->can('advertise/modify-teller')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $user_id = Yii::$app->user->getId();
        $now = time();
        $data = [];
        foreach($post_content['order_info'] as $k=>$v){
            $is_order_id = Advertisement::findById($v['order_id']);
            if(!$is_order_id){
                return json_encode(["code"=>-1, "msg"=>"没有此订单号 ".$v['order_id']]);
            }
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $model = Teller::findById($post_content['id']);
            $model ->user_id = $user_id;
            $model ->receipt_date = $post_content['receipt_date'];
            $model ->customer = $post_content['customer'];
            $model ->order_comment = $post_content['order_comment'];
            $model ->receipt_bank_name = $post_content['receipt_bank_name'];
            $model ->receipt_bank_num = $post_content['receipt_bank_num'];
            $model ->pay_bank_name = $post_content['pay_bank_name'];
            $model ->pay_bank_num = $post_content['pay_bank_num'];
            $model ->amount_total = $post_content['amount_total'];
            $model ->company_id = Yii::$app->user->identity->company_id;
            $model ->updated_at = $now;
            if($model ->update()){
                $id = $model->id;
                foreach($post_content['order_info'] as $k=>$v){
                    $data[] = [$id, $v['order_id'], $v['amount'],$now];
                }
                $db->createCommand()->delete('teller_order', ['teller_id'=>$id])->execute();
                $db->createCommand()
                    ->batchInsert('teller_order', [
                        'teller_id','order_id','amount','created_at'
                    ], $data)
                    ->execute();
                $this->manageLog(0,'修改出纳流水');
                $transaction->commit();
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }
    }

    /*
     * 删除出纳流水
     * */
    public function actionDeleteTeller(){

        if(!Yii::$app->exAuthManager->can('advertise/add-teller')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id = Yii::$app->request->get('id');
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $model = Teller::findById($id);
            if($model){
                if($model ->delete()){
                    $db->createCommand()->delete('teller_order', ['teller_id'=>$id])->execute();
                    $this->manageLog(0,'删除出纳流水');
                    $transaction->commit();
                    return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
                }
            }
            return json_encode(["code"=>-1, "msg"=>"请输入正确的Id"]);
        }catch (\Exception $e){
            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }

    }

    /*********** 图表统计收入模块 ***********/

    /*
     * 广告收入列表
     * */
    public function actionAdIncome(){

        if(!Yii::$app->exAuthManager->can('advertise/ad-income')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $user_id = Yii::$app->request->get('user_id', null);
        $day = Yii::$app->request->get('day', 1);
//        var_dump(strtotime(date("Y-m-d",strtotime("-1 day"))));exit;
        $date_s = strtotime(date("Y-m-d",strtotime("-".$day." day")));
        $date_n = strtotime(date("Y-m-d",time()));

        if(!is_null($user_id)) {
            $params['user_id'] = $user_id;
        }
        $params['date_s'] = $date_s;
        $params['date_n'] = $date_n;
        $final_data = Teller::getIncomeList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }
    /*
     * 广告收入图表
     * */
    public function actionIncomeChart(){
        if(!Yii::$app->exAuthManager->can('advertise/ad-income')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $params = [];
        $day = Yii::$app->request->get('day', 1);
        $date_s = strtotime(date("Y-m-d",strtotime("-".$day." day")));
        $date_n = strtotime(date("Y-m-d",time()));

        $params['date_s'] = $date_s;
        $params['date_n'] = $date_n;
        $final_data = Teller::getIncomeChartList($params);
        $total_data = Teller::getTotalData();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data,"total"=>$total_data]);

    }
    /*
     * 分类收入汇总图表
     * */
    public function actionCateIncomeChart(){
        if(!Yii::$app->exAuthManager->can('advertise/ad-income')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $params = [];
        $day = Yii::$app->request->get('day', 1);
        $date_s = strtotime(date("Y-m-d",strtotime("-".$day." day")));
        $date_n = strtotime(date("Y-m-d",time()));

        $params['date_s'] = $date_s;
        $params['date_n'] = $date_n;
        $final_data = Teller::getCateIncomeChartList($params);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }
    /*
     * 公众号收入汇总图表
     * */
    public function actionOfficialIncomeChart(){
        if(!Yii::$app->exAuthManager->can('advertise/ad-income')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $params = [];
        $official_account_id = Yii::$app->request->get('official_account_id', 60);
        $day = Yii::$app->request->get('day', 1);
        $date_s = strtotime(date("Y-m-d",strtotime("-".$day." day")));
        $date_n = strtotime(date("Y-m-d",time()));

        $params['date_s'] = $date_s;
        $params['date_n'] = $date_n;
        $params['official_account_id'] = $official_account_id;
        $final_data = Teller::getOfficialIncomeChartList($params);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }

    /*********** 导出数据 ***********/

    /*
     * 导出广告订单数据
     * */
    public function actionExportAdvertiseData(){
        $advertise_data = $this->_getAdvertiseData();
        $list = [];
        foreach($advertise_data['list'] as $k=>$v){
            foreach ($v['order_info'] as $order){
                $list[] = [
                    "order_id" => $v['order_id'],
                    "username" => $v['username'],
                    "receipt_date" => date("Y-m-d",$v['receipt_date']),
                    "customer" => $v['customer'],
//                    "order_amount" => $v['order_amount'],
//                    "deposit" => $v['deposit'],
                    "send_date" => date("Y-m-d",$order['send_date']),
                    "ad_position" => $order['ad_position'],
                    "retain_day" => $order['retain_day'] ."天",
                    "type_info" => $order['type_info']['parent']['name'] ."/" .$order['type_info']['son']['name'],
                    "million_fans_price" => (int)$order['million_fans_price'],
                    "official_account" => $order['official_account'],
                    "fans_num" => (int)$order['fans_num'],
                    "amount" => (int)$order['amount'],
                ];
            }
        }
        $format = [
            "订单号" => 'order_id',
            "接单人" => 'username',
            "接单时间" => 'receipt_date',
            "客户" => 'customer',
//            "订单金额" => 'order_amount',
//            "定金" => 'deposit',
            "发送时间" => 'send_date',
            "广告位" => 'ad_position',
            "保留天数" => 'retain_day',
            "广告类型" => 'type_info',
            "万粉单价" => 'million_fans_price',
            "公众号" => 'official_account',
            "粉丝数" => 'fans_num',
            "价格" => 'amount',
        ];
        $title = "广告订单列表";

        $this->Export($format,$list,$title);
    }


    public function actionExportCustomerData(){
        $customer_data = $this->_getCustomerData();
//        var_dump($customer_data);exit;
        $list = [];
        foreach($customer_data['list'] as $k=>$v){
            $list[] = [
                "created_at" => date("Y-m-d",$v['created_at']),
                "customer" => $v['customer'],
                "realname" => $v['realname'],
                "qq" => $v['qq'],
                "wechat_id" => $v['wechat_id'],
                "tel" => $v['tel'],
                "company" => $v['company'],
                "mark" => $v['mark'],
            ];

        }
        $format = [
            "添加时间" => 'created_at',
            "客户昵称" => 'customer',
            "姓名" => 'realname',
            "qq" => 'qq',
            "微信号" => 'wechat_id',
            "电话" => 'tel',
            "公司" => 'company',
            "备注" => 'mark',
        ];
        $title = "客户列表";

        $this->Export($format,$list,$title);
    }


    private function _getAdvertiseData(){
        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 400);
        $status = Yii::$app->request->get('status', 0);
        $user_id = Yii::$app->request->get('user_id',null);
        $receipt_date = Yii::$app->request->get('receipt_date',null);
        $customer = Yii::$app->request->get('customer', null);

        // 数据权限校验
        $current_user = Yii::$app->user->identity;
        $user_id_list = Yii::$app->dataAuthManager->getAdvertiseChildUidList($current_user);
//        var_dump($user_id_list);

        if($user_id_list === false) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($user_id_list !== true) {

            if($user_id_list === []) {
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>["list"=>[], "page_num"=>10]]);
            }

            if($user_id and !in_array($user_id, $user_id_list)) {
                return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
            }

            if(!is_null($user_id)) {
                $params['user_id'] = $user_id;
            } else {
                $params['user_id_list'] = $user_id_list;
            }

        } else  {

            if(!is_null($user_id)) {
                $params['user_id'] = $user_id;
            }

        }

        if(!is_null($status)) {
            $params['status'] = $status;
        }if(!is_null($receipt_date)) {
            $params['receipt_date'] = date("Y-m-d",$receipt_date);
        }if(!is_null($customer)) {
            $params['customer'] = $customer;
        }
        $final_data = Advertisement::getList($params, $page, $num);
        return $final_data;
    }

    private function _getCustomerData() {

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 400);
        $created_at = Yii::$app->request->get('created_at',null);
        $customer = Yii::$app->request->get('customer', null);

        if(!is_null($created_at)) {
            $params['created_at'] = date("Y-m-d",$created_at);
        }if(!is_null($customer)) {
            $params['customer'] = $customer;
        }
        $final_data = Customer::getList($params, $page, $num);
        return $final_data;
    }


    private function _checkIsDelete($id){
        $res = AdvertisementOfficial::find()->where(['ad_id'=>$id])->asArray()->all();
        foreach ($res as $v){
            if($v['status'] !== '0'){
                return false;
            }
        }
        return true;
    }

    private function _addMaterial($ad_info){
//        exit;

        $official = [];
        $send_date = [];
//        $material_id_arr = [];
        foreach($ad_info as $k=>$v){
            if(in_array($v['send_date'],$send_date)){ //这里可能出现时同一天的情况

                if(!in_array($v['official_account_id'],$official)){

                    $material_id = $this->_createAdMaterial($v['official_account_id']);
//                    var_dump($material_id);exit;
                    if($material_id == 0){
                        return 0;
                    }
                    Yii::$app->db->createCommand()
                        ->update(AdvertisementOfficial::tableName(), [
                            'material_id'=>$material_id
                        ], ['id'=>$v['id']])->execute();
                    $official[] = $v['official_account_id'];
                    $send_date[] = $v['send_date'];

                }else{

                    $material_id_info = AdvertisementOfficial::find()
                        ->where(['official_account_id'=>$v['official_account_id'],'ad_id'=>$v['ad_id']])
                        ->andWhere(['and', 'material_id!=0'])
                        ->asArray()
                        ->one();
                    $material_id = $material_id_info['material_id'];
                    Yii::$app->db->createCommand()
                        ->update(AdvertisementOfficial::tableName(), [
                            'material_id'=>$material_id
                        ], ['id'=>$v['id']])->execute();


                }
            }else{

                $material_id = $this->_createAdMaterial($v['official_account_id']);
//                var_dump($material_id);exit;
                if($material_id == 0){
                    return 0;
                }
                Yii::$app->db->createCommand()
                    ->update(AdvertisementOfficial::tableName(), [
                        'material_id'=>$material_id
                    ], ['id'=>$v['id']])->execute();


                $official[] = $v['official_account_id'];
                $send_date[] = $v['send_date'];
            }


        }
        return true;
    }
    /*
     * 添加广告对应素材
     * */
    private function _createAdMaterial($official_account_id)
    {

        $post_content = [
            "official_account_id"=>$official_account_id,
            "article_list"=>[
                [
                    "id"=>"",
                    "title"=>"广告",
                    "description"=>"广告",
                    "content"=>"<p>广告</p>",
                    "cover_media_id"=>"",
                    "author"=>"广告",
                    "ad_source_url"=>"",
                    "cover_url"=>"http://wx-admin-platform-new.oss-cn-shenzhen.aliyuncs.com/4e002fb293564ee16036d47b93d26160/58a3febfa341a2.38980714.jpeg",
                    "show_cover_pic"=>0,
                    "is_completed"=>1,
                    "showSourceLink"=>"",
                    "order"=>0,
                    "type"=>1
                ]
            ],
            "is_completed"=>0,
            "is_synchronized"=>0,
            "type"=>1
        ];


        $if_has_right = $this->_checkIfOfficialAccountRight($post_content['official_account_id']);
        if(!$if_has_right) {
            Yii::error(sprintf('公众号不是这个公司的'));
            return 0;
        }

        $this->wechat = $this->getWechat($post_content['official_account_id']);

        if(!$this->wechat) {
            Yii::error(sprintf('微信错误'));
            return 0;
        }

        try{

            switch($post_content['type']) {

                // case Material::MATERIAL_TYPE_ARTICLE:
                //     return $this->_uploadArticle($post_content);
                //     break;

                case Material::MATERIAL_TYPE_ARTICLE_MULTI:
                    return $this->_uploadArticleMulti($post_content);
                    break;

                case Material::MATERIAL_TYPE_IMAGE:
                    return $this->_uploadImage($post_content);
                    break;

                case Material::MATERIAL_TYPE_VOICE:
                    return $this->_uploadVoice($post_content);
                    break;

                case Material::MATERIAL_TYPE_VIDEO:
                    return $this->_uploadVideo($post_content);
                    break;

                case Material::MATERIAL_TYPE_COVER_IMAGE:
                    return $this->_uploadThumb($post_content);
                    break;

                case Material::MATERIAL_TYPE_ARTICLE_IMAGE:
                    return $this->_uploadArticleImage($post_content);
                    break;

                default:
                    return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
                    break;
            }

        } catch(HttpException $e) {
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return 0;
        }  catch (\Exception $e) {
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return 0;
        }
    }

    private function _uploadArticleMulti($raw_article_list)
    {
        try{

            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            // validate out come params
            $model = new MaterialForm();
            $model->scenario = MaterialForm::SCENARIO_CREATE_ARTICLE_MULTI;

            // add extra info
            $model->user_id = Yii::$app->user->identity->id;
            $model->status = Material::STATUS_ACTIVE;
            $model->created_from = Material::CREATED_FROM_SERVER;
            $model->parent_id = 0;

            $material_content = ["MaterialForm"=>$raw_article_list];
            if(!($model->load($material_content) && $model->validate())) {
                Yii::error(sprintf('Fail to create multi aritcle material cos reason:(%s)', json_encode($model->errors)));
                return 0;
            }

            // store to local
            $local_article_list = $model->storeMultiArticle();
            if(!$local_article_list) {
                Yii::error(sprintf('Fail to create multi article material cos reason:(%s)', json_encode($model->errors)));
                return 0;
            }

            // shift out parent article
            $parent_article = array_shift($local_article_list);

            $media_id = NULL;

            if($model->is_synchronized) {

                $wechat_img_map = Material::makeWechatImageMap($local_article_list);

                // construct article list
                $article_list = [];
                foreach($local_article_list as $local_article) {
                    $remote_article = Material::constructRemoteArticle($local_article, $wechat_img_map);
                    $article_list[] = $remote_article;
                }
                $content = $this->wechat->material->uploadArticle($article_list);

                $media_id = $content['media_id'];

                // update article material info
                $parent_article->media_id = $media_id;
                $parent_article->updated_at = time();
                $parent_article->is_synchronized = 1;
                $parent_article->update(false);
            }

            $transaction->commit();


            return $parent_article['id'];


        }catch(\Exception $e) {
            $transaction->rollback();
            $err_msg = sprintf("Fail to create material cos reason:%s", $e);
            Yii::error($err_msg);
            return 0;
        }
    }


    private function _checkIfOfficialAccountRight($official_account_id, $user_id=NULL) {

        $official_account_info = OfficialAccount::findById($official_account_id);
        if(!$official_account_info) {
            return false;
        }

        // check if user has the right to delete specific resources, may be not
        if(!$user_id) {

            if(Yii::$app->user->identity->company_id != $official_account_info['company_id']) {
                return false;
            }

            return true;
        }
        else {

            $user_info = User::findById($user_id);

            if(!$user_info) {
                return false;
            }

            if($user_info['company_id'] != $official_account_info['company_id']) {
                return false;
            }

            return true;
        }
    }


    private function _checkPositionAndDateIsOK($ad_position,$send_date,$official_account_id){
        $date_s = strtotime(date("Y-m-d",$send_date));
        $date_n = $date_s+86400;
        $res = AdvertisementOfficial::find()->where(['ad_position'=>$ad_position,'official_account_id'=>$official_account_id])->andWhere(['between', 'send_date', $date_s, $date_n])->one();
        if($res){
            return false;
        }
        return true;
    }


    private function _saveCustomerAdType($customer_id,$type_ids){
        $customer = Customer::findById($customer_id);
        if(!$customer){
            return ;
        }
        $origin_type_ids = unserialize($customer->type_ids);
        if($origin_type_ids){
            $type_ids = array_unique(array_merge($origin_type_ids,$type_ids));
            foreach ($type_ids as $k=>$v){
                if($v == -1){
                    unset($type_ids[$k]);
                }
            }
            $type_ids = serialize($type_ids);
            $customer ->type_ids = $type_ids;
            $customer ->update();
            return ;
        }else{
            $type_ids = serialize($type_ids);
            $customer ->type_ids = $type_ids;
            $customer ->update();
            return ;
        }

    }







}
