<?php

namespace frontend\controllers;

use Yii;

use common\helpers\Utils;
use common\models\Fans;
use common\models\Material;
use common\models\Reply;
use common\models\ReplyNews;

class ReplyController extends BaseController
{
    public $wechat;

    /**
     * 添加自动回复.  ALTER TABLE reply ADD COLUMN rule VARCHAR(255) DEFAULT '' COMMENT '规则名称' AFTER keyword ;
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('reply/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 支持更多过滤参数
        $official_account_id = Yii::$app->request->post("official_account_id");
        if(!$official_account_id){
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $type_reply = (int)Yii::$app->request->post('type_reply');
        $type_msg = (int)Yii::$app->request->post('type_msg');

        $id = Yii::$app->request->post("id",null);
        $wx_media_id =  Yii::$app->request->post("wx_media_id",null);
        $content = Yii::$app->request->post("content",null);
        $keyword = Yii::$app->request->post("keyword",null);
        $rule = Yii::$app->request->post("rule",null);

        $model = new Reply();
        $res = $model->find()->where(['type_reply'=>$type_reply, 'account_id'=>$official_account_id])->one();

        $transaction = Yii::$app->db->beginTransaction();

        if($type_msg == Reply::NEWS) {
            $ressss = $this->_saveMediaDataByMaterial($official_account_id, $wx_media_id);
            if($ressss['code'] == -1){
                return json_encode(["code"=>-1, "msg"=>$ressss['msg']]);
            }
        }

        if($res){

            // 关键字自动回复
            if($type_reply == Reply::KEYWORD_REPLY) {

                if(is_null($id)) {

                    $model->account_id = $official_account_id;
                    $model->type_reply = $type_reply;
                    $model->type_msg = $type_msg;
                    $model->wx_media_id = $wx_media_id;
                    $model->content = $content;
                    $model->keyword = $keyword;
                    $model->rule = $rule;
                    $model->created_at = time();

                    if($model->save()){
                        $this->manageLog($official_account_id, '创建自动回复');
                        $transaction->commit();
                        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
                    }
                    else{
                        $transaction->rollBack();
                        return json_encode(["code"=>-1, "msg"=>$model->getErrors()]);
                    }

                }else{

                    $res->account_id = $official_account_id;
                    $res->type_reply = $type_reply;
                    $res->type_msg = $type_msg;
                    $res->wx_media_id = $wx_media_id;
                    $res->content = $content;
                    $res->keyword = $keyword;
                    $res->rule = $rule;
                    $res->updated_at = time();

                    if($res->update()){
                        $this->manageLog($official_account_id,'修改自动回复');
                        $transaction->commit();
                        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
                    }
                    else{
                        $transaction->rollBack();
                        return json_encode(["code"=>-2, "msg"=>$res->getErrors()]);
                    }
                }
            //  其他类型回复
            } else {

                $res->account_id = $official_account_id;
                $res->type_reply = $type_reply;
                $res->type_msg = $type_msg;
                $res->wx_media_id = $wx_media_id;
                $res->content = $content;
                $res->keyword = $keyword;
                $res->rule = $rule;
                $res->updated_at = time();

                if($res->update()){
                    $this->manageLog($official_account_id,'修改自动回复');
                    $transaction->commit();
                    return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
                }
                else{
                    $transaction->rollBack();
                    return json_encode(["code"=>-2, "msg"=>$res->getErrors()]);
                }
            }

        } else {

            $model->account_id = $official_account_id;
            $model->type_reply = $type_reply;
            $model->type_msg = $type_msg;
            $model->wx_media_id = $wx_media_id;
            $model->content = $content;
            $model->keyword = $keyword;
            $model->rule = $rule;
            $model->created_at = time();

            if($model->save()){
                $this->manageLog($official_account_id,'修改自动回复');
                $transaction->commit();
                return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
            }else{
                $transaction->rollBack();
                return json_encode(["code"=>-1, "msg"=>$model->getErrors()]);
            }
        }

    }

    public function actionGetList(){

        if(!Yii::$app->exAuthManager->can('reply/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');

        $model = new Reply();
        $res = $model->find()->where(['account_id'=>$official_account_id])->one();
        if(!$res){
            $wx_data = $this->getWxReplyData($official_account_id);
//            var_dump($wx_data);exit;
            // FIXME 为什么这里要插入数据？
            $this->saveData($wx_data);
        }
        $auto_reply = $model->find()->where(['account_id'=>$official_account_id,'type_reply'=>Reply::AUTO_REPLY])->asArray()->one();
        $final_auto_reply  = $this->_getFinalData($auto_reply,$official_account_id);

        $msg_reply = $model->find()->where(['account_id'=>$official_account_id,'type_reply'=>Reply::MSG_REPLY])->asArray()->one();
        $final_msg_reply = $this->_getFinalData($msg_reply,$official_account_id);

        $keyword_reply = $model->find()->where(['account_id'=>$official_account_id,'type_reply'=>Reply::KEYWORD_REPLY])->asArray()->all();
        $final_keyword_reply = [];
        foreach($keyword_reply as $k=>$v){
            $final_keyword_reply[] = $this->_getFinalData($v,$official_account_id);
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data"=>["auto_reply"=>$final_auto_reply,"msg_reply"=>$final_msg_reply,"keyword_reply"=>$final_keyword_reply]]);

    }

    public function actionDelete(){

        if(!Yii::$app->exAuthManager->can('reply/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $ids = Yii::$app->request->post('id');
        try{
            foreach($ids as $id){
                $res = Reply::deleteAll(['id'=>$id]);
                if(!$res){
                    return json_encode(["code"=>-1, "msg"=>"没有此id为".$id."的数据"]);
                }
            }
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to delete reply message cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }
    }

    // TODO 删掉不用的代码
    public function actionInfo(){

        // if(!Yii::$app->exAuthManager->can('reply/info')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        // if(!Yii::$app->exAuthManager->can('reply/get')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        $id = Yii::$app->request->get('id');
        $model = new Reply();
        $list = $model->find()->where(['id'=>$id])->asArray()->one();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data"=>$list]);
    }

    /*
     * 保存信息到数据库中
     *
     */
    private function saveData($data){

        Yii::$app->db->createCommand()
            ->batchInsert(Reply::tableName(), ['account_id','type_reply','type_msg','wx_media_id','content','created_at','keyword','rule'], $data['data'])
            ->execute();

        Yii::$app->db->createCommand()
            ->batchInsert(ReplyNews::tableName(), ['account_id','media_id','type','title','author','digest','show_cover','cover_url','content_url','source_url','created_at'], $data['new_data'])
            ->execute();

        return true;
    }

    /*
     * 获取微信端自动回复内容
     * */
    private function getWxReplyData($official_account_id){

        $news_data = [];
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $data_list = $this->wechat->reply->current()->toArray();

        $reply_auto_add_type = null;
        $reply_auto_add_media_id = null;
        $reply_auto_add_content = null;

        if(isset($data_list['add_friend_autoreply_info']['type']) &&  $data_list['add_friend_autoreply_info']['type']== 'text'){

            $reply_auto_add_type = 5;
            $reply_auto_add_media_id = null;
            $reply_auto_add_content = $data_list['add_friend_autoreply_info']['content'];

        }elseif (isset($data_list['add_friend_autoreply_info']['type']) && $data_list['add_friend_autoreply_info']['type']== 'news'){

            $reply_auto_add_type = 1;
            $reply_auto_add_media_id = $data_list['add_friend_autoreply_info']['content'];

            foreach ($data_list['add_friend_autoreply_info']['news_info']['list'] as $new_info){
                $news_data[] = [
                    $official_account_id, $reply_auto_add_media_id,'news',$new_info['title'],
                    $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                    $new_info['content_url'],$new_info['source_url'],time()
                ];
            }

        }elseif (isset($data_list['add_friend_autoreply_info']['type']) && $data_list['add_friend_autoreply_info']['type']== 'img'){
            $reply_auto_add_type = 2;
            $reply_auto_add_media_id = $data_list['add_friend_autoreply_info']['content'];
        }
        if($reply_auto_add_type) {
            $data[] = [$official_account_id, 0, $reply_auto_add_type,$reply_auto_add_media_id,$reply_auto_add_content,time(),null,null];
        }

        $reply_default_type_msg = null;
        $reply_default_msg_media_id = null;
        $reply_default_msg_content = null;
        if(isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'text'){
            $reply_default_type_msg = 5;
            $reply_default_msg_content = $data_list['message_default_autoreply_info']['content'];
        }elseif (isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'news'){
            $reply_default_type_msg = 1;
            $reply_default_msg_media_id = $data_list['message_default_autoreply_info']['content'];

            foreach ($data_list['message_default_autoreply_info']['news_info']['list'] as $new_info){
                $news_data[] = [
                    $official_account_id,$reply_default_msg_media_id,'news',$new_info['title'],
                    $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                    $new_info['content_url'],$new_info['source_url'],time()
                ];
            }
        }elseif (isset($data_list['message_default_autoreply_info']['type']) && $data_list['message_default_autoreply_info']['type'] == 'img'){
            $reply_default_type_msg = 2;
            $reply_default_msg_media_id = $data_list['message_default_autoreply_info']['content'];
        }
        if($reply_default_type_msg) {
            $data[] = [$official_account_id,1,$reply_default_type_msg,$reply_default_msg_media_id,$reply_default_msg_content,time(),null,null];
        }

        if(isset($data_list['keyword_autoreply_info']['list']) && count($data_list['keyword_autoreply_info']['list'])){
            foreach($data_list['keyword_autoreply_info']['list'] as $k=>$v){
                $keyword_autoreply_type = null ;
                $keyword_autoreply_media_id = null;
                $keyword_autoreply_content = null;
                if($v['reply_list_info']['0']['type'] == 'text'){
                    $keyword_autoreply_type = 5;
                    $keyword_autoreply_content = $v['reply_list_info']['0']['content'];
                }elseif ($v['reply_list_info']['0']['type'] == 'news'){
                    $keyword_autoreply_type = 1;
                    $keyword_autoreply_media_id = $v['reply_list_info']['0']['content'];
                    if(isset($v['reply_list_info']['0']['news_info']['list'])){
                        foreach ($v['reply_list_info']['0']['news_info']['list'] as $new_info){
                            $news_data[] = [
                                $official_account_id,$keyword_autoreply_media_id,'news',$new_info['title'],
                                $new_info['author'],$new_info['digest'],$new_info['show_cover'],$new_info['cover_url'],
                                $new_info['content_url'],$new_info['source_url'],time()
                            ];
                        }
                    }

                }elseif ($v['reply_list_info']['0']['type'] == 'img'){
                    $keyword_autoreply_type = 2;
                    $keyword_autoreply_media_id = $v['reply_list_info']['0']['content'];
                }
                $keyword = '';
                foreach($v['keyword_list_info'] as $kk => $vv){
                    $keyword .= $vv['content']." ";
                }
                $keyword = rtrim($keyword);
                $rule_name = $v['rule_name'];
                $data[] = [$official_account_id,2,$keyword_autoreply_type,$keyword_autoreply_media_id,$keyword_autoreply_content,time(),$keyword,$rule_name];
            }
        }

        return ["data"=>$data,"new_data"=>$news_data];
    }

    /*
     * 更具media_id获取图文消息
     * */
    private function _getMediaData($media_id,$official_account_id){
        $news_info = ReplyNews::find()->where(['account_id'=>$official_account_id,'media_id'=>$media_id])->asArray()->all();
        if($news_info){
            $data = [];
            foreach ($news_info as $k=>$v){
                $data [] = [
                    'title' => $v['title'],
                    'author' => $v['author'],
                    'description' => $v['digest'],
                    'cover_url' => Utils::prepare_cover_url($v['cover_url']),
                    'content_url' => Utils::prepare_cover_url($v['content_url'])
                ];
            }
            return $data;
        }
        return false;
    }

    private function _saveMediaDataByMaterial($official_account_id, $media_id){

        // TODO 支持媒体素材内容的更新
        $is_media_id = ReplyNews::find()->where(['account_id'=>$official_account_id, 'media_id'=>$media_id])->one();

        if($is_media_id){
            return (["code"=>0, "msg"=>"ok"]);
        }

        try{
            $this->wechat = $this->getWechat($official_account_id);

            if(!$this->wechat) {
                return (["code"=>-1, "msg"=>"无效公众号ID"]);
            }

            $material = $this->wechat->material->get($media_id);

            $news_info = [];
            foreach ($material['news_item'] as $item){
                $news_info[] = [
                    "media_id" => $media_id,
                    "title" => $item['title'],
                    "author" => $item['author'],
                    "description" => $item['digest'],
                    "cover_url" => $item['thumb_url'],
                    "source_url" => $item['url'],
                ];
            }

            foreach ($news_info as $k=>$v){
                $data [] = [
                    $official_account_id,$media_id,'news', $v['title'], $v['author'],
                    $v['description'], 0, $v['cover_url'], $v['source_url'],'',time()
                ];
            }

            Yii::$app->db->createCommand()
                ->batchInsert(ReplyNews::tableName(), ['account_id','media_id','type','title','author','digest','show_cover',
                    'cover_url','content_url','source_url','created_at'], $data)
                ->execute();
            return (["code"=>0, "msg"=>"ok"]);

        }catch (\Exception $e){
            Yii::error(sprintf('wechat error:(%s)', $e->getMessage()));
            if(strpos($e->getMessage(),'appid hint')){
                return (["code"=>-1, "msg"=>"无效公众号ID"]);
            }elseif (strpos($e->getMessage(),'api unauthorized hint')){
                return (["code"=>-1, "msg"=>"无效公众号ID"]);
            }elseif (strpos($e->getMessage(),'appsecret')){
                return (["code"=>-1, "msg"=>"无效公众号appsecret"]);
            }elseif (strpos($e->getMessage(),'media_id')){
                return (["code"=>-1, "msg"=>"无效公众号media_id"]);
            }
        }
    }

    private function _getFinalData($reply_data,$official_account_id){
        $final_data = [];
        $news = [];
        if($reply_data['type_msg'] == 1){  //图文素材
            $news_data = $this->_getMediaData($reply_data['wx_media_id'],$official_account_id);
            if($news_data){
                foreach($news_data as $new){
                    $news[] = [
                        'title' => $new['title'],
                        'author' => $new['author'],
                        'description' => $new['description'],
                        'cover_url' => $new['cover_url'],
                        'content_url' => $new['cover_url'],
                    ];
                }
                $final_data = [
                    'id'=>$reply_data['id'],
                    'keyword'=>$reply_data['keyword'],
                    'rule'=>$reply_data['rule'],
                    'type_msg' => $reply_data['type_msg'],
                    'media_id' => $reply_data['wx_media_id'],
                    'news_info' => $news

                ];
            }else{
                $final_data = [
                    'id'=>$reply_data['id'],
                    'keyword'=>$reply_data['keyword'],
                    'rule'=>$reply_data['rule'],
                    'type_msg' => $reply_data['type_msg'],
                    'media_id' => $reply_data['wx_media_id'],
                ];
            }

        }elseif($reply_data['type_msg'] == 2){  //图片素材
            $img_data = Material::find()->select(['weixin_source_url'])->where(['media_id'=>$reply_data['wx_media_id'],'official_account_id'=>$official_account_id])->asArray()->one();
            if($img_data){
                $final_data = [
                    'id'=>$reply_data['id'],
                    'type_msg' => $reply_data['type_msg'],
                    'keyword'=>$reply_data['keyword'],
                    'rule'=>$reply_data['rule'],
                    'media_id' => $reply_data['wx_media_id'],
                    'img_url' => Utils::prepare_cover_url($img_data['weixin_source_url']),
                ];
            }else{
                $final_data = [
                    'id'=>$reply_data['id'],
                    'type_msg' => $reply_data['type_msg'],
                    'keyword'=>$reply_data['keyword'],
                    'rule'=>$reply_data['rule'],
                    'media_id' => $reply_data['wx_media_id'],
                ];
            }

        }elseif($reply_data['type_msg'] == 5){  //文本素材
            $final_data = [
                'id'=>$reply_data['id'],
                'type_msg' => $reply_data['type_msg'],
                'keyword'=>$reply_data['keyword'],
                'rule'=>$reply_data['rule'],
                'content' => $reply_data['content'],
            ];
        }
        return $final_data;
    }

}
