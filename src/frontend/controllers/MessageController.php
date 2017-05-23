<?php

namespace frontend\controllers;

use common\models\Fans;
use common\models\Messages;
use common\models\Reply;
use Yii;

class MessageController extends BaseController
{
    public $wechat;

    /**
     * 获取消息列表
     *
     * @return array
     */
    public function actionGetList()
    {
        if(!Yii::$app->exAuthManager->can('message/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 支持更多过滤参数
        $official_account_id = Yii::$app->request->get("official_account_id");
        if(!$official_account_id){
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $params = [
            "official_account_id" => $official_account_id,
            "page" => $page,
            "num" => $num,
        ];

        $final_data = Messages::getList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 回复消息
     *
     * @return string
     */
    public function actionResponse(){

        if(!Yii::$app->exAuthManager->can('message/response')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post("official_account_id");
        $message = Yii::$app->request->post("message");
        if(!$message){
            return json_encode(["code"=>10101, "msg"=>"请填写要发送的信息"]);
        }
        if(!$official_account_id){
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $id = Yii::$app->request->post("id");
        $model = Messages::findById($id);
        if(!$model){
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $openid = Fans::getOpenidById($model->fans_id);
        try{
            $this->wechat = $this->getWechat($official_account_id);
            if(!$this->wechat) {
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }
            $res = $this->wechat->broadcast->previewText($message,$openid);
//            var_dump($res);exit;
            if($res){
                $model -> is_reply = 1;
                $model -> save();
                $this->manageLog($official_account_id,'消息回复--'.$message);
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to response fans cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }
    }

    /**
     * 收藏
     *
     * @return string
     */
    public function actionCollect(){

        if(!Yii::$app->exAuthManager->can('message/collect')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id = Yii::$app->request->get('id');
        if(!$id){
            return json_encode(["code"=>10101, "msg"=>"messing id"]);
        }

        $model = Messages::findById($id);
        $model ->is_collection = 1;
        try{
            if($model->save()){
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to collect messages cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }

        return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
    }

    /**
     * 保存为素材
     *
     * @return string
     */
    public function actionSaveToMaterial(){
    }
}
