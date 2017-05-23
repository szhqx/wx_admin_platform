<?php

namespace frontend\controllers;

use Yii;

use common\models\ManagerLog;

class ManagerLogController extends BaseController
{
    /**
     *  查看日志列表
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('manager-log/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $weixin_name = Yii::$app->request->get('weixin_name',null);
        $nickname = Yii::$app->request->get('nickname',null);

        if(!is_null($weixin_name)){
            $params['weixin_name'] = $weixin_name;
        }
        if(!is_null($nickname)){
            $params['nickname'] = $nickname;
        }
        if(!isset($params)){
            $params = null;
        }

        $final_data = ManagerLog::getList($params,$page,$num);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }
}
