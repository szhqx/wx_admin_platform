<?php

namespace frontend\controllers;

use Yii;

use common\models\Article;
use common\models\Mass;
use common\helpers\ArticleTrait;

class ArticleController extends BaseController
{
    use ArticleTrait;

    public function behaviors()
    {
        return [
            'access' => [

                'class' => 'yii\filters\AccessControl',

                'rules' => [
                    [
                        'actions' => ['detail'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
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

    /**
     * 获取文章详情页.
     *
     * @return string
     */
    public function actionDetail()
    {
        $request = Yii::$app->request;

        $id_str = $request->get('id', NULL);
        if(!$id_str) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $_ = explode("_", $id_str);
        $mass_id = $_[0];
        $order = $_[1];

        $article = Article::findByMassOrderKey($mass_id, $order);
        if(!$article) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $data = [
            "article_info"=>[
                "id"=>$article['id'],
                "title"=>$article['title'],
                "content"=>$article['content'],
                "author"=>$article['author'],
                "published_at"=>$article['published_at']
            ]
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$data]);
    }

}
