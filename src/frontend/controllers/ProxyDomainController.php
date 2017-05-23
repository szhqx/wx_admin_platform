<?php

namespace frontend\controllers;

use Yii;

use common\models\MaterialCate;
use common\models\ProxyDomain;


class ProxyDomainController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [

                'class' => 'yii\filters\AccessControl',

                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['redirect'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],

                'denyCallback' => function($rule, $action) {
                    BaseController::denyCallback($rule, $action);
                }

            ]
        ];
    }

    public function actionRedirect() {

        $params = [];
        $id = (int)Yii::$app->request->get('id', 0);
        $url = Yii::$app->request->get('url', '');

        if(!$id and !$url) {
            $url = "http://baidu.com";
            Yii::$app->response->redirect($url, 302);
        }

        // TODO checksum
        $proxy_domain = ProxyDomain::findById($id);
        if(!$proxy_domain) {
            // may get deleted
        }

        Yii::$app->response->redirect(urlencode(base64_decode(urldecode($url))), 302);
    }

    public function actionCreate() {

        if(!Yii::$app->exAuthManager->can('proxy-domain/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $domain_list = Yii::$app->request->post()['url_list'];

        // TODO check domain pattern

        foreach($domain_list as $domain) {

            // check if exist
            $record = ProxyDomain::findByDomain($domain);
            if ($record)
            {
                continue;
            }

            $proxy_domain = new ProxyDomain();
            $proxy_domain->domain = $domain;
            $is_saved = $proxy_domain->save(false);

            if(!$is_saved) {
                Yii::warning(sprintf('Fail to to save proxy domain(%s).', $domain));
                continue;
            }

            Yii::info(sprintf('success to to save proxy domain(%s).', $domain));
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    public function actionDelete() {

        if(!Yii::$app->exAuthManager->can('proxy-domain/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $id_list = Yii::$app->request->post()['id_list'];

        ProxyDomain::deleteByIdlist($id_list);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    public function actionInfoList() {

        if(!Yii::$app->exAuthManager->can('proxy-domain/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);

        $params = [];

        $raw_domain_list = ProxyDomain::getDomainList($params, $page, $num);

        $total = ProxyDomain::getTotal($params);

        $domain_list = [];

        foreach($raw_domain_list as $raw_domain) {
            $domain_list[] = [
                "id"=>$raw_domain['id'],
                "domain"=>$raw_domain['domain'],
                "req_nums"=>$raw_domain['req_nums']
            ];
        }

        $final_data = [
            "url_list" => $domain_list,
            "total" => ceil($total/$num)
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }
}