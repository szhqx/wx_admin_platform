<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;

use EasyWeChat\Foundation\Application;

const APP_ID = 'wx89efd9398453e27e';
const APP_SECRET = 'cb11c889864974bac30b6ff8b2158ba1';
const SNSAPI_INFO = [
            1=> 'snsapi_userbase',
            2=> 'snsapi_userinfo',
            3=> 'snsapi_login',
      ];
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:get, put, post, delete, options');
header('Access-Control-Allow-Headers:authorization, origin, content-type, accept');
class ShareController extends Controller
{
    /**
     * 分享接口.
     *
     * @return array
     *
     */
    

    public function actionIndex()
    {

        $app_id = Yii::$app->request->get('app_id','wx3639f3e999cde993');
        $app_secret = Yii::$app->request->get('app_secret','5e0d10f0f9b8c5960f354fa3e48f5dec');
        $url = Yii::$app->request->get('url','http://march3qian.duapp.com/');

        $options = [
            "app_id"=>$app_id,
            "secret"=>$app_secret,
        ];

        $wechat = new Application($options);
        $wechat->js->setUrl($url);

        return ($wechat->js->config(['onMenuShareTimeline','onMenuShareAppMessage'],true));

    }

    /**
     * 网页授权接口.
     *
     * @return array
     *
     */

    public function is_weixin(){

        if ( strpos($_SERVER['HTTP_USER_AGENT'],

                'MicroMessenger') !== false ) {

            return true;
        }
        return false;

    }


    public function actionGetUserInfo(){
        //第一步：用户同意授权，获取code
        $appid = "wx9c044f98156b8e20";
        $redirect_url = urlencode("http://admin.cheesebeer.net/index.php?r=share/get-user");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_userinfo&state=".time()."#wechat_redirect";
        header("location:".$url);
    }
    public function actionGetUser(){
        $code = $_GET['code'];
        $appid = "wx9c044f98156b8e20";
        $app_secret = "3fd73bb4cd76af92528e3c393435e2ab";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$app_secret."&code=".$code."&grant_type=authorization_code";
        $res = json_decode($this->_http_curl_get($url));

        if(isset($res->access_token)){
            $get_user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$res->access_token."&openid=".$res->openid."&lang=zh_CN";
            $user_info = json_decode($this->_http_curl_get($get_user_url));
            $target_url = "http://movietip1.duapp.com/index.html?headimgurl=".$user_info->headimgurl."&nickname=".$user_info->nickname;
            header("location:".$target_url);
        }
    }

    public function _http_curl_get($url){
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        return $output;
    }

    public function _http_curl_post($url,$post_data){
//        $url = "http://localhost/web_services.php";
//        $post_data = array ("username" => "bob","key" => "12345");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }

}


