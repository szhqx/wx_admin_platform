<?php

namespace frontend\controllers;

use Yii;

use yii\web\Controller;


class SiteController extends BaseController
{
    // 不用系统默认的，采用自己的
    // public function actions()
    // {
    //     return [
    //         'error' => [
    //             'class' => 'yii\web\ErrorAction',
    //         ],
    //     ];
    // }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $err_msg = sprintf("Fail to process action cos reason(%s).", $exception);
            Yii::error($err_msg);
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return json_encode([
            'msg' => $this->status_code_msg[-1],
            'code' => -1,
        ]);
    }

}