<?php

namespace frontend\controllers;

use common\helpers\WechatHelper;
use common\models\Fans;
use common\models\Menus;
use common\models\MenusNews;
use common\models\Messages;
use common\models\OfficialAccount;
use common\models\Reply;
use common\models\ProxyDomain;

use common\models\ReplyNews;
use common\models\StatisticMenu;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Material;

use EasyWeChat\Message\News;
use EasyWeChat\Message\Raw;
use EasyWeChat\Message\Text;
use Yii;
use yii\web\Controller;

use EasyWeChat\Foundation\Application as Server;


class WechatController extends Controller
{
    public $wechat;
    public $official_account_id;
    public $enableCsrfValidation = false;

    public function actionIndex(){

        $echoStr = Yii::$app->request->get("echostr",null);

        if ($this->checkSignature()) {

            // TODO 这块是做什么的？
            if(!is_null($echoStr)){
                //修改公众号的状态is_connect = 1  生成菜单
                Yii::info(sprintf("update official account is_content =1"),__METHOD__);
                $sql = sprintf("update official_account set is_connect = %d where id = %d",1,Yii::$app->request->get('id'));
                Yii::$app->db->createCommand($sql)->query();

                $this ->_send_menu(Yii::$app->request->get('id'));
                echo $echoStr;exit;
            }

            $this->official_account_id = Yii::$app->request->get('id');

            $this->_requestMsg();

            exit;
        }
    }

    public function _requestMsg()
    {

        $this->wechat = $this->getServer();
        $this->wechat->server->setMessageHandler(function ($message) {

            Yii::info(sprintf("MsgType(%s).Event(%s).user(%s).Key(%s).Msg(%s)",$message->MsgType,$message->Event,$message->FromUserName,$message->EventKey,$message->Content),__METHOD__);
            switch ($message->MsgType) {
            case 'event':
                if ($message->Event == "subscribe") {
                    return $this->_getReplyMsg(Reply::AUTO_REPLY);
                } elseif ($message->Event == "unsubscribe") {

                } elseif ($message->Event == "CLICK") {
                    if(isset($message->EventKey)){
                        Yii::info(sprintf("save menus Key(%s)",$message->EventKey),__METHOD__);
                        $this->StatisticMenu($message->EventKey,$message->FromUserName);
                    }
                    $menu_info = $this->_getMenuSendInfo($message->EventKey);
                    return $this->_returnMenuMsg($menu_info);

                } elseif ($message->Event == "VIEW") {
                    if(isset($message->EventKey)){
                        Yii::info(sprintf("save menus Key(%s)",$message->EventKey),__METHOD__);
                        $this->StatisticMenu($message->EventKey,$message->FromUserName);
                    }
                }
                break;
            case 'text':
                return $this->_getReplyMsg(Reply::KEYWORD_REPLY, $message->Content);
                break;
            case 'image':
                return $this->_saveMsg($message->FromUserName,$message->MsgType,$message->MsgId,null,$message->PicUrl,$message->MediaId);
                break;
            case 'voice':
                return $this->_saveMsg($message->FromUserName,$message->MsgType,$message->MsgId,null,null,$message->MediaId,$message->Format,$message->Recognition);
                # 语音消息...
                break;
            case 'video':
                return $this->_saveMsg($message->FromUserName,$message->MsgType,$message->MsgId,null,null,$message->MediaId,null,null,$message->ThumbMediaId);
                # 视频消息...
                break;
            case 'location':
                # 坐标消息...
                break;
            case 'link':
                # 链接消息...
                break;
                // ... 其它消息
            default:
                break;
            }
            // ...
        });

        $this->wechat->server->serve()->send();
    }

    public function _getMenuSendInfo($key){
        $menu_info = Menus::find()->where(['official_account_id'=>$this->official_account_id,'key'=>$key])->asArray()->one();
        if(!$menu_info){
            return false;
        }
        //test
        Yii::info($key."-media_id=>".$menu_info['media_id']."-msg_type".$menu_info['msg_type']);
        return $menu_info;

    }

    public function _returnMenuMsg($menu_info){
        try{
            if($menu_info['msg_type'] == 'text'){
                return new Text(['content'=>$menu_info['value']]);
            }elseif($menu_info['msg_type'] == 'news'){
                $news_info = MenusNews::find()->where(['account_id'=>$menu_info['official_account_id'],'media_id'=>$menu_info['media_id']])->asArray()->all();
                $news = [];
                foreach ($news_info as $new_info){
                    $news[] = new News([
                        'title'       => $new_info['title'],
                        'description' => $new_info['digest'],
                        'url'         => $new_info['content_url'],
                        'image'       => $new_info['cover_url'],
                    ]);
                }
                return $news;
            }elseif($menu_info['msg_type'] == 'img'){
                return new Image(['media_id'=>$menu_info['media_id']]);

            }elseif($menu_info['msg_type'] == 'video'){

            }elseif($menu_info['msg_type'] == 'voice'){

            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to send message cos reason:(%s)', $e->getMessage()));
            return new Text(['content'=>"未识别内容"]);
        }
    }

    public function _saveMsg($openid,$type,$msg_id,$content=null,$picurl=null,$media_id=null,$format=null,$recognition=null,$thumb_mediaId=null){
        $model = new Messages();
        $model->fans_id = Fans::findByOpenid($openid)['id'];
        $model->official_account_id = $this->official_account_id;
        $model->msg_id = $msg_id;
        $model->content = $content;
        $model->msg_type = $type;
        $model->imgurl = $picurl;
        $model->media_id = $media_id;
        $model->voice_format = $format;
        $model->recognition = $recognition;
        $model->thumb_media_id = $thumb_mediaId;
        $model->created_at = time();
        try{
            if($model->save()){
                return true;
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to save message form fans cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>sprintf('Fail to save message form fans cos reason:(%s)', json_encode($e->getMessage()))]);
        }

    }

    public function _saveFansOpenId($openid){
        $model = new Fans();
        try{
            if($model->findByOpenid($openid)){
                return true;
            }else{
                $model->open_id = $openid;
                $model->account_id = $this->official_account_id;
                $model->is_syc = 0;
                $model->save();
                return true;
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to save fans openid cos reason:(%s)', json_encode($e->getMessage())));
            return false;
        }

    }

    //关注自动回复 //未识别消息自动回复 //关键字自动回复
    public function _getReplyMsg($reply_type, $message=null) {

        $model = new Reply();

        if($reply_type == Reply::AUTO_REPLY) { //关注自动回复

            $reply_info = $model->find()->where(['account_id'=>$this->official_account_id,'type_reply'=>Reply::AUTO_REPLY])->one();

            if($reply_info) {

                if($reply_info['type_msg'] == Reply::NEWS){

                    Yii::info(Reply::NEWS);

                    $news_info = ReplyNews::find()->where(['account_id'=>$this->official_account_id,'media_id'=>$reply_info['wx_media_id']])->asArray()->all();
                    $news = [];
                    foreach ($news_info as $new_info){
                        $news[] = new News([
                            'title'       => $new_info['title'],
                            'description' => $new_info['digest'],
                            'url'         => ProxyDomain::prepareProxyUrl($new_info['content_url']),
                            'image'       => $new_info['cover_url'],
                        ]);
                    }
                    return $news;

                }elseif($reply_info['type_msg'] == Reply::IMAGE){
                    Yii::info(Reply::IMAGE);
                    return new Image(['media_id' => $reply_info['wx_media_id']]);
                }elseif($reply_info['type_msg'] == Reply::VOICE){
//                    return new Material('voice',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::VIDEO){
//                    return new Material('mpvideo',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::TEXT){
                    return new Text(['content'=>$reply_info['content']]);
                }
            }else{
                return "欢迎关注！";
            }
        }

        if($reply_type == Reply::KEYWORD_REPLY){
            $reply_info = $model->find()->where(['account_id'=>$this->official_account_id,'type_reply'=>Reply::KEYWORD_REPLY])->all();
            if($reply_info){ //判断是否有关键字自动回复
                foreach($reply_info as $k=>$v){
                    if(in_array($message,explode(' ',$v['keyword']))){
                        if($v['type_msg'] == Reply::NEWS){
                            $news_info = ReplyNews::find()->where(['account_id'=>$this->official_account_id,'media_id'=>$v['wx_media_id']])->asArray()->all();
                            $news = [];
                            foreach ($news_info as $new_info){
                                $news[] = new News([
                                    'title'       => $new_info['title'],
                                    'description' => $new_info['digest'],
                                    'url'         => ProxyDomain::prepareProxyUrl($new_info['content_url']),
                                    'image'       => $new_info['cover_url'],
                                ]);
                            }
                            return $news;
                        }elseif($v['type_msg'] == Reply::IMAGE){
                            return new Image(['media_id' => $v['wx_media_id']]);
                        }elseif($v['type_msg'] == Reply::VOICE){
//                            return new Material('voice',$v['wx_media_id']);
                        }elseif($v['type_msg'] == Reply::VIDEO){
//                            return new Material('mpvideo',$v['wx_media_id']);
                        }elseif($v['type_msg'] == Reply::TEXT){
                            Yii::info(Reply::TEXT);
                            return new Text(['content' => $v['content']]);
                        }else{

                        }
                    }
                }
            }

            $reply_info = $model->find()->where(['account_id'=>$this->official_account_id,'type_reply'=>Reply::MSG_REPLY])->one();
            if($reply_info){ //判断是否有未识别自动回复
                if($reply_info['type_msg'] == Reply::NEWS){
                    $news_info = ReplyNews::find()->where(['account_id'=>$this->official_account_id,'media_id'=>$reply_info['wx_media_id']])->asArray()->all();
                    $news = [];
                    foreach ($news_info as $new_info){
                        $news[] = new News([
                            'title'       => $new_info['title'],
                            'description' => $new_info['digest'],
                            'url'         => $new_info['content_url'],
                            'image'       => $new_info['cover_url'],
                        ]);
                    }
                    return $news;
                }elseif($reply_info['type_msg'] == Reply::IMAGE){
                    return new Image(['media_id' => $reply_info['wx_media_id']]);
                }elseif($reply_info['type_msg'] == Reply::VOICE){
//                    return new Material('voice',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::VIDEO){
//                    return new Material('mpvideo',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::TEXT){
                    return new Text(['content' => $reply_info['content']]);
                }
            }else{
                return "我不知道您说了啥？你敢再说一遍吗？";
            }

        }else{
            $reply_info = $model->find()->where(['account_id'=>$this->official_account_id,'type_reply'=>Reply::MSG_REPLY])->one();
            if($reply_info){ //判断是否有未识别自动回复
                if($reply_info['type_msg'] == Reply::NEWS){
                    $news_info = ReplyNews::find()->where(['account_id'=>$this->official_account_id,'media_id'=>$reply_info['wx_media_id']])->asArray()->all();
                    $news = [];
                    foreach ($news_info as $new_info){
                        $news[] = new News([
                            'title'       => $new_info['title'],
                            'description' => $new_info['digest'],
                            'url'         => ProxyDomain::prepareProxyUrl($new_info['content_url']),
                            'image'       => $new_info['cover_url'],
                        ]);
                    }
                }elseif($reply_info['type_msg'] == Reply::IMAGE){
                    return new Image(['media_id' => $reply_info['wx_media_id']]);
                }elseif($reply_info['type_msg'] == Reply::VOICE){
//                    return new Material('voice',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::VIDEO){
//                    return new Material('mpvideo',$reply_info['wx_media_id']);
                }elseif($reply_info['type_msg'] == Reply::TEXT){
                    return new Text(['content' => $reply_info['content']]);
                }
            }else{
                return "我不知道您说了啥？你敢再说一遍吗？";
            }
        }
    }

    public function _getNews($title,$description,$url,$image){
        return new News([
            'title'=>$title,
            'description'=>$description,
            'url'=>$url,
            'image'=>$image,
        ]);

    }

    public function getArticle($type,$media_id){
        return new Material('mpnews', $media_id);

//        $model = new \common\models\Material();
//        $article = $model->find()->where(['official_account_id'=>$this->official_account_id,'media_id'=>$media_id,'type'=>$type])->asArray()->one();
//        Yii::info(var_dump($article));
//        if(is_null($article['id'])){
//            $wx = new Server();
//            $material = $wx->material->get($media_id);
//            foreach($material as $k=>$v){
//                $news = new Article([
//                    'title'   => $v['title'],
//                    'author'  => $v['author'],
//                    'content' => $v['content'],
//                    'thumb_media_id' => $v['thumb_media_id'],
//                    'digest' => $v['digest'],
//                    'source_url' => $v['url'],
//                    'show_cover' => $v['show_cover_pic'],
//                ]);
//            }
//
//        }
//        return new Article([
//            'title'   => $article['title'],
//            'author'  => $article['author'],
//            'content' => $article['content'],
//            'thumb_media_id' => $article['cover_media_id'],
//            'digest' => $article['description'],
//            'source_url' => $article['weixin_source_url'],
//            'show_cover' => $article['show_cover_pic'],
//        ]);

    }


    public function getServer() {

        $official_account = OfficialAccount::findById($this->official_account_id);
        if(!$official_account) {
            return NULL;
        }

        // TODO 添加default的options，具体参考：https://easywechat.org/zh-cn/docs/configuration.html

        $options = [
            "app_id"=>$official_account['app_id'],
            "secret"=>$official_account['app_secret'],
            "token"=>$official_account['token'],
            "aes_key"=>$official_account['encoding_aes_key'],
        ];
        $wechat = new Server($options);
        return $wechat;
    }

    private function checkSignature() {
        $token_info = OfficialAccount::findById(Yii::$app->request->get("id"));
        $signature = Yii::$app->request->get("signature");
        $timestamp = Yii::$app->request->get("timestamp");
        $nonce = Yii::$app->request->get("nonce");
        $token = $token_info->token;
        $tmpArr = array($token, $timestamp, $nonce);//C('token')
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            echo $token;
            return false;
        }
    }

    private function StatisticMenu($key,$user){

        Yii::$app->db->createCommand()
            ->insert('statistic_menu_log',
                [
                    'official_account_id'=>$this->official_account_id,
                    'click_user'=>$user,
                    'key'=>$key,
                    'created_at'=>time(),
                ])->execute();

//        Yii::info(sprintf("MsgType(%s).Event(%s).user(%s).Key(%s).Msg(%s)",$message->MsgType,$message->Event,$message->FromUserName,$message->EventKey,$message->Content),__METHOD__);

    }

    public function _send_menu($official_account_id){

        $wechat = WechatHelper::getWechat($official_account_id);
        if(!$wechat) {
            Yii::error(sprintf('Fail to get wechat '));
            return false;
        }
        $tree2 = $this->_getlist($official_account_id);

        if(count($tree2) == 0){
            return true;
        }
//        return \GuzzleHttp\json_encode($tree2);exit;
        try{
            $wechat->menu->add($tree2);
            Yii::info("success send menus \n");

        }catch (\Exception $e){
            Yii::error(sprintf('Fail to send menu to wechat cos reason:(%s) file(%s) line(%d)', $e->getMessage(),$e->getFile(),$e->getLine()));

        }
    }

    public function _getlist($official_account_id){
        $tree['button']= array();
        $data = $this->_get_data($official_account_id);

        if(count($data) == 0){
            return [];
        }
        foreach ($data as $k => $d) {
            if ($d ['parent_id'] != 0)
                continue;
            $tree ['button'] [$d ['id_s']] = $this->_deal_data($d);
            unset ($data [$k]);
        }
        foreach ($data as $k => $d) {
            $tree ['button'] [$d ['parent_id']] ['sub_button'] [] = $this->_deal_data($d);
            unset ($data [$k]);
        }
        $tree2 ['button'] = [];

        foreach ($tree ['button'] as $k => $d) {
            $tree2 ['button'] [] = $d;
        }
        return $tree2 ['button'];
    }

    private function _get_data($official_account_id) {

        $model = new Menus();
        $list = $model->find()->where(['official_account_id'=>$official_account_id])->asArray()->all();
        if(count($list) == 0){
            return [];
        }

        // 取一级菜单
        $one_arr = [];
        $data = [];
        foreach ( $list as $k => $vo ) {
            if ($vo ['parent_id'] != 0)
                continue;

            $one_arr [$vo ['id_s']] = $vo;
            unset ( $list [$k] );
        }

        foreach ( $one_arr as $p ) {
            $data [] = $p;

            $two_arr = [];
            foreach ( $list as $key => $l ) {
                if ($l ['parent_id'] != $p ['id_s'])
                    continue;

                //$l ['title'] = '├──' . $l ['title'];
                $two_arr [] = $l;
                unset ( $list [$key] );
            }

            $data = array_merge ( $data, $two_arr );
        }

        return $data;
    }

    private function _deal_data($d) {
        $res ['name'] = str_replace ( '├──', '', $d ['name'] );

        if ($d ['type'] == 'view') {
            $res ['type'] = 'view';
            $res ['url'] =  $d ['url'];
        } elseif ($d ['msg_type'] == 'news') {
            $res ['type'] = 'media_id';
            $res ['media_id'] = trim ( $d ['media_id'] );
        }elseif ($d ['msg_type'] == 'img') {
            $res ['type'] = 'media_id';
            $res ['media_id'] = trim ( $d ['media_id'] );
        } elseif ($d ['type'] == 'media_id' || $d ['type'] == 'view_limited' || $d ['type'] == 'news' || $d ['type'] == 'img' || $d ['type'] == 'video' || $d ['type'] == 'voice') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['media_id'] = trim ( $d ['media_id'] );
        } elseif ($d ['type'] == 'text') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['value'] = trim ( $d ['value'] );
        } elseif ($d ['type'] != 'none') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['key'] = trim ( $d ['key'] );
        }

        return $res;
    }

}