<?php

namespace frontend\controllers;

use Yii;

// use phpDocumentor\Reflection\DocBlock\Tags\Var_;

use common\models\Fans;
use common\models\FansGroup;
use common\models\FansTag;
use common\models\FansTagMap;

use EasyWeChat\User\User;
use EasyWeChat\Core\Exceptions\HttpException;

class FansController extends BaseController
{
    public $wechat;

    /**
     * 获取粉丝列表. (从数据库中拉去)
     *
     * @return string
     */
    public function actionGetList()
    {
        if(!Yii::$app->exAuthManager->can('fans/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        // TODO 支持更多过滤参数
        $official_account_id = Yii::$app->request->get("official_account_id");
        if(!$official_account_id){
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $group_id = Yii::$app->request->get('group_id', null);
        $nickname = Yii::$app->request->get("nickname", null);
        $tag_id = Yii::$app->request->get("tag_id",null);
        $params = [
            "official_account_id" => $official_account_id,
            "page" => $page,
            "num" => $num,
        ];
        if(!is_null($nickname)) {
            $params['nickname'] = $nickname;
        }
        if(!is_null($tag_id)) {
            $params['tag_id'] = $tag_id;
        }
        if(!is_null($group_id)) {
            $params['group_id'] = $group_id;
        }
        $final_data = Fans::getList($params, $page, $num);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }

    /**
     * 同步粉丝、标签、分组.（手动操作） (第一版先做增量同步，不做减量同步)
     *
     * @return string
     */
    public function actionSyncOpenid()
    {
        // if(!Yii::$app->exAuthManager->can('fans/sync-openid')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

         if(!Yii::$app->exAuthManager->can('fans/sync')) {
             return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
         }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $next_openid = Fans::getNextOpenid($official_account_id);
        $next_openid = Yii::$app->request->get('next_openid',$next_openid);

        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $res = $this->wechat->user->lists($next_openid)->toArray();


        if(is_null($res)){
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
        $this->manageLog($official_account_id,'同步粉丝');
        if($res['count'] == 0){
            return json_encode([
                "code"=>0,
                "msg"=>'sync openid is successful,keep going to sync fans info',
                "data"=>[
                    "status"=>1,
                    "url"=>'r=fans/sync-fans-tag'
                ]
            ]);
        }

        $this->_saveOpenid($res['data']['openid'],$official_account_id);

        if($res['total'] >10000){
            return json_encode([
                "code"=>0,
                "msg"=>'sync openid is unfinished,keep going to sync openid',
                "data" =>[
                    "status"=>1,
                    "url"=>'r=fans/sync-openid&next_openid='.$res['next_openid']
                ]
            ]);
        }
        return json_encode([
            "code"=>0,
            "msg"=>'sync openid is successful,keep going to sync fans info',
            "data" =>[
                "status"=>1,
                "url"=>'r=fans/sync-fans-tag'
            ]
        ]);
    }

    private function _saveOpenid($openids,$official_account_id){

        $rows = [];
        foreach ($openids as $k=>$v){
            $rows[] = [$official_account_id,0,$v];
        }

        Yii::$app->db->createCommand()
            ->batchInsert(Fans::tableName(), ['account_id','group_id','open_id'], $rows)
            ->execute();

        return true;
    }

    /**
     * 同步粉丝信息 (第一版先做增量同步，不做减量同步)
     *
     *
     */
    public function actionSyncFansInfo(){
        set_time_limit(0);
        if(!Yii::$app->exAuthManager->can('fans/sync')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $model = new Fans();
        $total = $model->find()->where(['account_id'=>$official_account_id])->count();
        $synced = $model->find()->where(['account_id'=>$official_account_id,'is_syc'=>1])->count();
        $openid_arr = $model->find()
            ->select(['open_id'])
            ->where(['is_syc'=>0,'account_id'=>$official_account_id])
            ->limit(50)
            ->orderBy('id')
            ->asArray()
            ->all();
        if(count($openid_arr) == 0){
            //粉丝信息同步完毕
            return json_encode([
                "code"=>0,
                "msg"=>'sync fans info successful...',
                "data"=>[
                    "status" => 0,
                    "total" => $total,
                    "synced" =>$synced

//                    "url" => 'r=fans/sync-fans-group'
                ]
            ]);
        }
        $new_openid_arr=[];
        foreach ($openid_arr as $k=>$v){
            $new_openid_arr[] = $v['open_id'];
        }
        $default_head_img = Yii::$app->params['DEFAULT_HEAD_IMG'];
        //var_dump($new_openid_arr);exit;

        try{
            $list = $this->wechat->user->batchGet($new_openid_arr)->toArray();
            try{
                $db = Yii::$app->db->createCommand();
                foreach ($list['user_info_list'] as $k=>$v){
                    if($v['subscribe'] == 1){
                        $db->update(
                            Fans::tableName(),
                            [   'nickname'=>$v['nickname'],
                                'city'=>$v['city'],
                                'province'=>$v['province'],
                                'remark'=>$v['remark'],
                                'country'=>$v['country'],
                                'avator'=>empty($v['headimgurl'])?$default_head_img:$v['headimgurl'],
                                'group_id'=>$v['groupid'],
                                'sex'=>$v['sex'],
                                'language'=>$v['language'],
                                'subscribed_at'=>$v['subscribe_time'],
                                'tagid_list'=>serialize($v['tagid_list']),
                                'is_syc'=>1,
                                'created_at'=>time()
                            ],
                            [
                                'open_id'=>$v['openid'],
                                'is_syc'=>0,
                                'account_id'=>$official_account_id,
                            ])->execute();
                    }else{
                        $db->update(
                            Fans::tableName(),
                            [
                                'is_syc'=>1,
                                'is_subscribe'=>0,
                                'created_at'=>time()
                            ],
                            [
                                'open_id'=>$v['openid'],
                                'is_syc'=>0,
                                'account_id'=>$official_account_id,
                            ])->execute();
                    }


                }
            }catch (\Exception $e){
                Yii::error(sprintf('Fail to sync fans info cos reason:(%s)', json_encode($e->getMessage())));
//                return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
                return json_encode([
                    "code"=>0,
                    "msg"=>'sync fans info...',
                    "data"=>[
                        "status" => 1,
                        "total" => $total,
                        "synced" =>$synced,
                        "url" => 'r=fans/sync-fans-info'
                    ]
                ]);
            }

        }catch (\Exception $e){
            Yii::error(sprintf('Fail to sync fans info cos reason:(%s)', json_encode($e->getMessage())));
//            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
            return json_encode([
                "code"=>0,
                "msg"=>'sync fans info...',
                "data"=>[
                    "status" => 1,
                    "total" => $total,
                    "synced" =>$synced,
                    "url" => 'r=fans/sync-fans-info'
                ]
            ]);
        }
        return json_encode([
            "code"=>0,
            "msg"=>'sync fans info...',
            "data"=>[
                "status" => 1,
                "total" => $total,
                "synced" =>$synced,
                "url" => 'r=fans/sync-fans-info'
            ]
        ]);
    }

    /**
     *
     *
     * 同步粉丝分组 (第一版先做增量同步，不做减量同步)
     */
    public function actionSyncFansGroup(){

        // if(!Yii::$app->exAuthManager->can('fans/sync-fans-group')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        if(!Yii::$app->exAuthManager->can('fans/sync')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $model = new FansGroup();
        $wechat_group_id_arr = $model->find()->select(['wechat_group_id'])->where(['account_id'=>$official_account_id])->asArray()->all();
        $new_wechat_id_arr = [];
        foreach($wechat_group_id_arr as $k=>$v){
            $new_wechat_id_arr[] = $v['wechat_group_id'];
        }
        $lists = $this->wechat->user_group->lists();
        $rows = [];
        foreach($lists->groups as $k=>$v){
            if(in_array($v['id'],$new_wechat_id_arr)){continue;}
            $rows[] = [$v['name'],time(),$v['id'],$v['name'],$v['count'],$official_account_id];
        }
        Yii::$app->db->createCommand()
            ->batchInsert(FansGroup::tableName(),
                ['name', 'created_at','wechat_group_id','wechat_group_name','wechat_group_count','account_id'], $rows)
            ->execute();
        return json_encode([
            "code"=>0,
            "msg"=>'sync fans group successful...',
            "data"=>[
                "status" => 1,
                "url" => 'r=fans/sync-fans-info'
            ]
        ]);
    }

    /*
     *
     *
     *
     * */
    public function actionSyncFansTag(){

        // if(!Yii::$app->exAuthManager->can('fans/sync-fans-tag')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        if(!Yii::$app->exAuthManager->can('fans/sync')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model = new FansTag();
        $wechat_tag_id_arr = $model->find()->select(['wechat_tag_id'])->where(['official_account_id'=>$official_account_id])->asArray()->all();
        $new_wechat_id_arr = [];
        foreach($wechat_tag_id_arr as $k=>$v){
            $new_wechat_id_arr[] = $v['wechat_tag_id'];
        }

        $lists = $this->wechat->user_tag->lists();
        $rows = [];
        foreach($lists->tags as $k=>$v){
            if(in_array($v['id'],$new_wechat_id_arr)){continue;}
            $rows[] = [$v['name'],$official_account_id,time(),$v['id'],$v['name'],$v['count']];
        }

        Yii::$app->db->createCommand()
            ->batchInsert(FansTag::tableName(),
                ['title', 'official_account_id','created_at','wechat_tag_id','wechat_tag_name','wechat_tag_count'], $rows)
            ->execute();

        return json_encode([
            "code"=>0,
            "msg"=>'sync fans tag successful...',
            "data"=>[
                "status" => 1,
                "url" => 'r=fans/sync-fans-tag-map'
            ]
        ]);

    }

    /*
    *
    *
    *
    * */
    public function actionSyncFansTagMap(){

        // if(!Yii::$app->exAuthManager->can('fans/sync-fans-tag-map')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        if(!Yii::$app->exAuthManager->can('fans/sync')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $fans_tag_list = FansTag::find()->where(['official_account_id'=>$official_account_id,'is_sync'=>0])->asArray()->all();
        if(!$fans_tag_list){
            return json_encode([
                "code"=>0,
                "msg"=>'sync fans tag map is successful',
                "data"=>[
                    "status" => 1,
                    "url" => 'r=fans/sync-fans-group'
                ]
            ]);
        }

        $db= Yii::$app->db->createCommand();
        $value = [];
        foreach($fans_tag_list as $k=>$v){

            $fans_openid_model = $this->wechat->user_tag->usersOfTag($v['wechat_tag_id']);
            if($fans_openid_model->count == 0){
                $db->update('fans_tag',['is_sync'=>1],['id'=>$v['id']])->execute();
                continue;
            }
            $fans_openid_list = $fans_openid_model->data['openid'];
            foreach($fans_openid_list as $va){
                $value[] = [Fans::getIdByOpenId($va,$official_account_id),$v['id'],time(),1];
            }
            $db->update('fans_tag',['is_sync'=>1],['id'=>$v['id']])->execute();
        }
        $db->batchInsert('fans_tag_map',['uid','tag_id','created_at','is_sync'],$value)->execute();
        return json_encode([
            "code"=>0,
            "msg"=>'sync fans tag map is successful',
            "data"=>[
                "status" => 1,
                "url" => 'r=fans/sync-fans-group'
            ]
        ]);

    }

    /**
     * 添加标签.
     *
     * @return string
     */
    public function actionCreateTag()
    {
        if(!Yii::$app->exAuthManager->can('fans/create-tag')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $tag_name = Yii::$app->request->post('tag_name');
        try{
            //微信端存储
            $res = $this->wechat->user_tag->create($tag_name);

            if($res->tag['id']){
                //服务器端存储
                $model = new FansTag();
                $model->title = $tag_name;
                $model->official_account_id=$official_account_id;
                $model->created_at = time();
                $model->wechat_tag_id = $res->tag['id'];
                $model->wechat_tag_name= $res->tag['name'];
                if($model->save()){
                    $this->manageLog($official_account_id,"添加标签--".$tag_name);
                    return json_encode(["code"=>0, "msg"=>'ok',"data"=>['id'=>$model->id,'title'=>$tag_name]]);
                }
                Yii::error(sprintf('Fail to save fans tag cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to save fans tag cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }

    }

    /**
     * 修改粉丝备注名称.
     *
     * @return string
     */
    public function actionMark()
    {
        if(!Yii::$app->exAuthManager->can('fans/mark')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $fans_id = Yii::$app->request->post('fans_id');
        $remark_name = Yii::$app->request->post('remark_name');
        $model = Fans::findById($fans_id);
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->user->remark($model->open_id,$remark_name);
            if($res){
                $model->remark = $remark_name;
                if($model->save()){
                    $this->manageLog($official_account_id,"修改粉丝备注--".$remark_name);
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to mark fans remark name cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to mark fans remark name cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }



    /**
     * 修改标签.
     *
     * @return string
     */
    public function actionUpdateTag()
    {
        if(!Yii::$app->exAuthManager->can('fans/update-tag')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $tag_id = Yii::$app->request->post('tag_id');
        $tag_name = Yii::$app->request->post('tag_name');
        $model = FansTag::findById($tag_id);
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->user_tag->update($model->wechat_tag_id,$tag_name);
            if($res){
                $model->title = $tag_name;
                $model->wechat_tag_name = $tag_name;
                $model->updated_at = time();
                if($model->save()){
                    $this->manageLog($official_account_id,"修改标签--".$tag_name);
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to update fans tag name cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to update fans tag name cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }


    /**
     * 删除标签.
     *
     * @return string
     */
    public function actionDeleteTag()
    {
        if(!Yii::$app->exAuthManager->can('fans/delete-tag')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $tag_id = Yii::$app->request->post('tag_id');
        $model = FansTag::findById($tag_id);
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->user_tag->delete($model->wechat_tag_id);
            if($res){
                if($model->delete()){
                    $this->manageLog($official_account_id,"删除标签--".$model->wechat_tag_name);
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to delete fans tag name cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to delete fans tag name cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }


    /**
     * 标签列表.
     *
     * @return string
     */
    public function actionGetTagList()
    {
        if(!Yii::$app->exAuthManager->can('fans/get-tag-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $official_account_id = Yii::$app->request->get('official_account_id');
        $model = new FansTag();
        $list = $model->find()->where(['official_account_id'=>$official_account_id])->asArray()->all();
        return json_encode(["code"=>0, "msg"=>'ok',"data"=>["fans_tag_list"=>$list]]);
    }


    /**
     * 给粉丝打标签.
     *
     * @return string
     */
    public function actionTagging()
    {
        if(!Yii::$app->exAuthManager->can('fans/tagging')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        //var_dump(Yii::$app->request->post());exit;
        $official_account_id = Yii::$app->request->post('official_account_id');
        $tag_id_list = Yii::$app->request->post('tag_id');//此处为数组 [1,2,3,4,5]
        $fans_ids = Yii::$app->request->post('fans_ids'); //此处为数组 [1,2,3,4,5]
        $this->wechat = $this->getWechat($official_account_id);
//        var_dump($_POST);exit;
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model = new Fans();
        $openids_arr = $model->find()->select(['open_id'])->where(['id'=>$fans_ids])->asArray()->all();
        $openid_list = [];
        foreach($openids_arr as $k=>$v){
            $openid_list[] = $v['open_id'];
        }
//        var_dump($openids);exit;
        $len = count($tag_id_list);
        $transation =  Yii::$app->db->beginTransaction();
        for($i=0;$i<$len;$i++){
            $tag_model = FansTag::findById($tag_id_list[$i]);
            try{
                $this->wechat->user_tag->batchUntagUsers($openid_list,$tag_model->wechat_tag_id);
                $res = $this->wechat->user_tag->batchTagUsers($openid_list,$tag_model->wechat_tag_id);
                if($res->errcode == 0){
                    $db = Yii::$app->db->createCommand();

                    $value = [];
                    foreach($fans_ids as $v){
                        $db->delete('fans_tag_map',['uid'=>$v,'tag_id'=>$tag_model->tag_id])->execute();
                    }
                    foreach($fans_ids as $v){
                        $value[] = [$v,$tag_model->id,time(),1];
                    }
                    $db->batchInsert('fans_tag_map',['uid','tag_id','created_at','is_sync'],$value)->execute();

                }
            }catch (\Exception $e){
                var_dump($e->getMessage());
                Yii::error(sprintf('Fail to tagging fans cos reason:(%s)', json_encode($e->getMessage())));
                $transation->rollBack();
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }
        $transation->commit();
        $this->manageLog($official_account_id,"给粉丝打标签");
        return json_encode(["code"=>0, "msg"=>'ok']);

    }



    /**
     * 卸载粉丝标签.
     *
     * @return string
     */
    public function actionUnTagging()
    {
        if(!Yii::$app->exAuthManager->can('fans/un-tagging')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        //var_dump(Yii::$app->request->post());exit;
        $official_account_id = Yii::$app->request->post('official_account_id');
        $tag_id = Yii::$app->request->post('tag_id');
        $fans_ids = Yii::$app->request->post('fans_ids'); //此处为数组 [1,2,3,4,5]
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model = new Fans();
        $openids_arr = $model->find()->select(['open_id'])->where(['id'=>$fans_ids])->asArray()->all();
        $openids = [];
        foreach($openids_arr as $k=>$v){
            $openids[] = $v['open_id'];
        }
        $tag_model = FansTag::findById($tag_id);

        try{
            $this->wechat->user_tag->batchUntagUsers($openids,$tag_model->wechat_tag_id);
            $res = $this->wechat->user_tag->batchTagUsers($openids,$tag_model->wechat_tag_id);
//        print_r($res);exit;
            if($res->errcode == 0){
                $db = Yii::$app->db->createCommand();
//                foreach ($openids as $k=>$v){
//                    $fans_tag_id_info = $model->find()->select(['tagid_list'])->where(['open_id'=>$v])->asArray()->one();
//                    $tagid_list = unserialize($fans_tag_id_info['tagid_list']);
////                    var_dump($tag_model->wechat_tag_id);exit;
//                    $tagid_list[] = $tag_model->wechat_tag_id;
//                    $tagid_list = array_unique($tagid_list);
//                    $db->update(
//                        Fans::tableName(),
//                        [
//                            'tagid_list'=>serialize($tagid_list),
//                            'updated_at'=>time()
//                        ],
//                        [
//                            'open_id'=>$v,
//                            'account_id'=>$official_account_id,
//                        ])->execute();
//                }

                foreach($fans_ids as $va){
                    $db->delete('fans_tag_map',['uid'=>$va,'tag_id'=>$tag_model->tag_id])->execute();
                }
                $this->manageLog($official_account_id,"卸载粉丝标签");
                return json_encode(["code"=>0, "msg"=>'ok']);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to tagging fans cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }

    }


    /*
     * 粉丝分组管理，包含黑名单，黑名单默认为1；
     * @return string
     */
    public function actionMoveFansToGroup()
    {
        if(!Yii::$app->exAuthManager->can('fans/move-fans-to-group')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $group_id = Yii::$app->request->post('group_id');
        $fans_ids = Yii::$app->request->post('fans_ids'); //此处为数组 [1,2,3,4,5]
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model = new Fans();
        $openids_arr = $model->find()->select(['open_id'])->where(['id'=>$fans_ids])->asArray()->all();
        $openids = [];
        foreach($openids_arr as $k=>$v){
            $openids[] = $v['open_id'];
        }
        $group_model = FansGroup::findById($group_id);

        try{
            $res = $this->wechat->user_group->moveUsers($openids,$group_model->wechat_group_id);
//        print_r($res->errcode);exit;
            if($res->errcode == 0){
                $db = Yii::$app->db->createCommand();
                foreach ($openids as $k=>$v){
                    $db->update(
                        Fans::tableName(),
                        [
                            'group_id'=>1,
                            'updated_at'=>time()
                        ],
                        [
                            'open_id'=>$v,
                            'account_id'=>$official_account_id,
                        ])->execute();
                }
                $this->manageLog($official_account_id,"将粉丝添加黑名单");
                return json_encode(["code"=>0, "msg"=>'ok']);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to add fans to block cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }

    /**
     * 添加分组.
     *
     * @return string
     */
    public function actionCreateGroup()
    {
        if(!Yii::$app->exAuthManager->can('fans/create-group')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $group_name = Yii::$app->request->post('group_name');
        try{
            //微信端存储
            $res = $this->wechat->user_group->create($group_name);

            if($res->group['id']){
                //服务器端存储
                $model = new FansGroup();
                $model->name = $group_name;
                $model->account_id=$official_account_id;
                $model->created_at = time();
                $model->wechat_group_id = $res->group['id'];
                $model->wechat_group_name= $res->group['name'];
                if($model->save()){
                    $this->manageLog($official_account_id,"添加分组");
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to save fans group cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to save fans group cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }

    }


    /**
     * 修改分组.
     *
     * @return string
     */
    public function actionUpdategroup()
    {
        if(!Yii::$app->exAuthManager->can('fans/update-group')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $group_id = Yii::$app->request->post('group_id');
        $group_name = Yii::$app->request->post('group_name');
        $model = FansGroup::findById($group_id);
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->user_group->update($model->wechat_group_id,$group_name);
            if($res){
                $model->name = $group_name;
                $model->wechat_group_name = $group_name;
                $model->updated_at = time();
                if($model->save()){
                    $this->manageLog($official_account_id,"修改分组");
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to update fans group name cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to update fans group name cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }


    /**
     * 删除分组.
     *
     * @return string
     */
    public function actionDeletegroup()
    {
        if(!Yii::$app->exAuthManager->can('fans/delete-group')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $group_id = Yii::$app->request->post('group_id');
        $model = FansGroup::findById($group_id);
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->user_group->delete($model->wechat_group_id);
            if($res){
                if($model->delete()){
                    $this->manageLog($official_account_id,"删除分组");
                    return json_encode(["code"=>0, "msg"=>'ok']);
                }
                Yii::error(sprintf('Fail to delete fans group name cos reason:(%s)', json_encode($model->getErrors())));
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to delete fans group name cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }


    /**
     * 分组列表.
     *
     * @return string
     */
    public function actionGetgroupList()
    {
        if(!Yii::$app->exAuthManager->can('fans/get-group-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $official_account_id = Yii::$app->request->get('official_account_id');
        $model = new FansGroup();
        $list = $model->find()->where(['account_id'=>$official_account_id])->asArray()->all();
        return json_encode(["code"=>0, "msg"=>'ok',"data"=>["fans_group_list"=>$list]]);
    }











}
