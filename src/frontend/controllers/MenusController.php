<?php
namespace frontend\controllers;

use Yii;

use common\helpers\Utils;
use common\models\Material;
use common\models\MenusNews;
use common\models\Menus;


class MenusController extends BaseController
{
    public $wechat;

    /*
     * 获取微信菜单列表
     */
    public function actionGetList(){

        if(!Yii::$app->exAuthManager->can('menus/get-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');

        // 查看本地是否存在数据，如果为空，调用微信端数据存入本地，如果不为空，直接get list
        $menu_list = Menus::find()->where(['official_account_id'=>$official_account_id])->asArray()->all();

        if(!$menu_list){
            $this->wechat = $this->getWechat($official_account_id);
            if(!$this->wechat){
                return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
            }
            $current_list = $this->wechat->menu->current();
            $this->_saveCurrentMenu($current_list->selfmenu_info['button'],$official_account_id);
        }

        $list = $this->_getListOnView($official_account_id);

        return json_encode(["code"=>0,"msg"=>$this->status_code_msg[0],"data"=>["menu_list"=>$list]]);
    }

    /*
     *
     * 添加微信菜单（先添加到本地，再推送到服务器）
     */
    public function actionCreate(){

        if(!Yii::$app->exAuthManager->can('menus/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $data = Yii::$app->request->post();
        $official_account_id = $data['official_account_id'];
        $button_list = $data['button'];

        $res = $this->_saveMenu($button_list, $official_account_id);
        if(!$res){
            return json_encode(["code"=>-1,"msg"=>"添加失败"]);
        }

        if($this->_SendMenu($official_account_id)){
            return json_encode(["code"=>0,"msg"=>$this->status_code_msg[0]]);
        }

        $this->manageLog($official_account_id,'创建菜单');
        return json_encode(["code"=>-1,"msg"=>"添加菜单失败"]);
    }


    public function _saveCurrentMenu($button_list,$official_account_id){
//        var_dump($button_list);exit;
        $rows = [];
        $news_data = [];
        foreach($button_list as $k=>$v){
            $url = empty(isset($v['url'])?$v['url']:null)?null:$v['url'];
            $key = empty(isset($v['key'])?$v['key']:null)?null:$v['key'];
            $media_id = empty(isset($v['media_id'])?$v['media_id']:null)?null:$v['media_id'];
            $type = isset($v['type'])?$v['type']:'none';
            switch ($type){
                case 'click':
                    $media_id = $v['media_id'];
                    $msg_type = $type;
                    $type = 'click';
                    break;
                case 'none':

                    break;
                case 'text':
                    $value = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;
                case 'view':
                    $url = $v['url'];
                    $msg_type = $type;
                    break;
                case 'img':
                    $media_id = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;
                case 'news' || 'video' || 'voice':
                    $media_id = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;

            }
            $rows[] = [$official_account_id,0,$k+1,$v['name'],$type,$key,$url,$k,time(),$media_id,isset($value)?$value:'',isset($msg_type)?$msg_type:0];

            if(isset($v['type']) && ($v['type'] == 'news')){

                foreach($v['news_info']['list'] as $news_info){
                    $news_data[] = [
                        $official_account_id,$media_id,'news',$news_info['title'],
                        $news_info['author'],$news_info['digest'],$news_info['show_cover'],
                        $news_info['cover_url'],$news_info['content_url'],$news_info['source_url'],
                    ];
                }
            }

            if(!empty($v['sub_button']['list'])){
                foreach($v['sub_button']['list'] as $k1=>$v1){
                    $url1 = empty(isset($v1['url'])?$v1['url']:null)?null:$v1['url'];
                    $key1 = empty(isset($v1['key'])?$v1['key']:null)?null:$v1['key'];
                    $media_id_sub = empty(isset($v1['media_id'])?$v1['media_id']:null)?null:$v1['media_id'];
                    $type_sub = isset($v1['type'])?$v1['type']:'none';
                    switch ($type_sub){
                        case 'none':

                            break;
                        case 'text':
                            $value_sub = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;
                        case 'view':
                            $url1 = $v1['url'];
                            $msg_type = $type_sub;
                            break;
                        case 'img':
                            $media_id_sub = $v1['value'];
                            $key1 = $this->createRandomStr(3).time();
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            break;
                        case 'news' || 'video' || 'voice':
                            $media_id_sub = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;

                    }
                    $rows[] = [$official_account_id,$k+1,0,$v1['name'],$type_sub,$key1,$url1,$k,time(),$media_id_sub,isset($value_sub)?$value_sub:'',isset($msg_type_sub)?$msg_type_sub:0];
                    if(isset($v1['type']) && ($v1['type'] == 'news')){
                        foreach($v1['news_info']['list'] as $news_info){
                            $news_data[] = [
                                $official_account_id,$media_id_sub,'news',$news_info['title'],
                                $news_info['author'],$news_info['digest'],$news_info['show_cover'],
                                $news_info['cover_url'],$news_info['content_url'],$news_info['source_url'],time()
                            ];
                        }
                    }
                }
            }
        }

        $db = Yii::$app->db->createCommand();
//        var_dump($news_data);exit;
        $db ->delete(Menus::tableName(),['official_account_id'=>$official_account_id])->execute();
        $db ->batchInsert(Menus::tableName(),
        [
            'official_account_id',
            'parent_id',
            'id_s',
            'name',
            'type',
            'key',
            'url',
            'sort',
            'created_at',
            'media_id',
            'value',
            'msg_type'
        ], $rows)->execute();

        $db ->delete('menus_news',['account_id'=>$official_account_id])->execute();
        $db ->batchInsert('menus_news',
            [
                'account_id',
                'media_id',
                'type',
                'title',
                'author',
                'digest',
                'show_cover',
                'cover_url',
                'content_url',
                'source_url',
                'created_at'
            ], $news_data)->execute();
    }
    public function _saveMenu($button_list,$official_account_id){
//        var_dump($button_list);
        $rows = [];
        $news_data = [];
        foreach($button_list as $k=>$v){
            $url = empty(isset($v['url'])?$v['url']:null)?null:$v['url'];
            $key = empty(isset($v['key'])?$v['key']:null)?null:$v['key'];
            $value = empty(isset($v['value'])?$v['value']:null)?null:$v['value'];
            $type = isset($v['type'])?$v['type']:'none';
            $media_id = '';
            switch ($type){
                case 'none':
                    break;
                case 'text':
                    $value = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;
                case 'view':
                    $url = $v['value'];
                    $msg_type = $type;
                    break;
                case 'img' || 'news' || 'video' || 'voice':
                    $media_id = $v['value'];
                    $msg_type = $type;
                    $type = 'click';
                    $key = $this->createRandomStr(3).time();
                    break;

            }
            $rows[] = [$official_account_id,0,$k+1,$v['name'],$type,$key,$url,$k,time(),$media_id,isset($value)?$value:'',isset($msg_type)?$msg_type:0];

            if(isset($v['type']) && ($v['type'] == 'news')){

                $is_medai_id = MenusNews::find()->where(['account_id'=>$official_account_id,'media_id'=>$media_id])->one();
                if($is_medai_id){continue;}
                $news_info_parent = Material::find()->select(['id'])->where(['official_account_id'=>$official_account_id,'media_id'=>$media_id])
                    ->asArray()->one();
                if(!$news_info_parent){
                    Yii::error(sprintf("no parent media_id(%s)"),__METHOD__);
                    return false;
                }
                $news_info = Material::find()->select(['id','official_account_id','title','author','description','cover_url','weixin_source_url'])
                    ->where(['parent_id'=>$news_info_parent['id']])->asArray()->all();

                foreach($news_info as $new_info){
                    $news_data[] = [
                        $official_account_id,$media_id,'news',$new_info['title'],
                        $new_info['author'],$new_info['description'],0,
                        $new_info['cover_url'],$new_info['weixin_source_url'],$new_info['weixin_source_url'],time()
                    ];
                }
            }

            if(!empty($v['sub_button'])){
                foreach($v['sub_button'] as $k1=>$v1){
                    $url1 = empty(isset($v1['url'])?$v1['url']:null)?null:$v1['url'];
                    $key1 = empty(isset($v1['key'])?$v1['key']:null)?null:$v1['key'];
                    $value_s = empty(isset($v1['value'])?$v1['value']:null)?null:$v1['value'];
                    $type_sub = isset($v1['type'])?$v1['type']:'none';
                    switch ($type_sub){
                        case 'none':

                            break;
                        case 'text':
                            $value_sub = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;
                        case 'view':
                            $url1 = $v1['value'];
                            $msg_type = $type_sub;
                            break;
                        case 'img' || 'news' || 'video' || 'voice':
                            $value_s = $v1['value'];
                            $msg_type_sub = $type_sub;
                            $type_sub = 'click';
                            $key1 = $this->createRandomStr(3).time();
                            break;

                    }
                    $rows[] = [$official_account_id,$k+1,0,$v1['name'],$type_sub,$key1,$url1,$k,time(),$value_s,isset($value_sub)?$value_sub:'',isset($msg_type_sub)?$msg_type_sub:0];
                    if(isset($v1['type']) && ($v1['type'] == 'news')){

                        $is_medai_id = MenusNews::find()->where(['account_id'=>$official_account_id,'media_id'=>$value_s])->one();
                        if($is_medai_id){continue;}
                        $news_info_parent = Material::find()->select(['id'])->where(['official_account_id'=>$official_account_id,'media_id'=>$value_s])
                            ->asArray()->one();
                        if(!$news_info_parent){
                            Yii::error(sprintf("no parent media_id(%s)"),__METHOD__);
                            return false;
                        }
                        $news_info = Material::find()->select(['id','official_account_id','title','author','description','cover_url','weixin_source_url'])
                            ->where(['parent_id'=>$news_info_parent['id']])->asArray()->all();

                        foreach($news_info as $new_info){
                            $news_data[] = [
                                $official_account_id,$value_s,'news',$new_info['title'],
                                $new_info['author'],$new_info['description'],0,
                                $new_info['cover_url'],$new_info['weixin_source_url'],$new_info['weixin_source_url'],time()
                            ];
                        }
                    }
                }
            }
        }
        $db = Yii::$app->db;
        $transaction = $db ->beginTransaction();
        try{
            //        var_dump($news_data);exit
            $db ->createCommand()->delete(Menus::tableName(),['official_account_id'=>$official_account_id])->execute();
            $db ->createCommand()->batchInsert(Menus::tableName(),
                [
                    'official_account_id',
                    'parent_id',
                    'id_s',
                    'name',
                    'type',
                    'key',
                    'url',
                    'sort',
                    'created_at',
                    'media_id',
                    'value',
                    'msg_type'
                ], $rows)->execute();

//        $db ->delete('menus_news',['account_id'=>$official_account_id])->execute();
            $db ->createCommand()->batchInsert('menus_news',
                [
                    'account_id',
                    'media_id',
                    'type',
                    'title',
                    'author',
                    'digest',
                    'show_cover',
                    'cover_url',
                    'content_url',
                    'source_url',
                    'created_at'
                ], $news_data)->execute();
            $transaction ->commit();
            return true;
        }catch (\Exception $e){
            Yii::error(sprintf("failed to save menus data cos(%s)",$e->getMessage()),__METHOD__);
            $transaction ->rollBack();
            return false;
        }

    }

    public function _savesMenu($button_list,$official_account_id){
        $rows = [];
        $news_data = [];
        foreach($button_list as $k=>$v){
            $name = $v['name'];
            $value = $v['value'];
            $sub_button = $v['sub_button'];
            $type = isset($v['type'])?$v['type']:'none';
            switch ($type){
                case 'view':
                    $key = '';
                    $url = $value;
                    $media_id = '';
                    $type_p = $type;
                    $value = '';
                    $msg_type_p = '';
                    break;
                case 'none':
                    $key = '';
                    $url = $value;
                    $media_id = '';
                    $type_p = $type;
                    $value = '';
                    $msg_type_p = '';
                    break;
                    break;
                case 'news':
                    break;
                case 'img':
                    break;

            }


            if(isset($type) && ($type == 'news')){
                $is_medai_id = MenusNews::find()->where(['official_account_id'=>$official_account_id,'media_id'=>$media_id])->one();
                if($is_medai_id){continue;}
                $news_info_parent = Material::find()->select(['id'])->where(['official_account_id'=>$official_account_id,'media_id'=>$media_id])
                    ->asArray()->one();
                $news_info = Material::find()->select(['id','official_account_id','title','author','description','cover_url','weixin_source_url'])
                    ->where(['parent_id'=>$news_info_parent['id']])->asArray()->all();
                foreach ($news_info as $new_info)
                    $news_data[] = [
                        $official_account_id,$media_id,'news',$new_info['title'],
                        $new_info['author'],$new_info['description'],0,
                        $new_info['cover_url'],$new_info['weixin_source_url'],$new_info['source_url'],time()
                    ];
            }

            $rows[] = [$official_account_id,$k+1,0,$name,$type_p,$key,$url,$k,time(),$media_id,$value,$msg_type_p];


        }

        $rows = [];
        $news_data = [];
        foreach($button_list as $k=>$v){
            $url = empty(isset($v['url'])?$v['url']:null)?null:$v['url'];
            $key = empty(isset($v['key'])?$v['key']:null)?null:$v['key'];
            $media_id = empty(isset($v['media_id'])?$v['media_id']:null)?null:$v['media_id'];
            if(isset($v['type']) && ($v['type'] == 'news')){
                $is_medai_id = MenusNews::find()->where(['official_account_ud'=>$official_account_id,'media_id'=>$media_id])->one();
                if($is_medai_id){continue;}
                $news_info_parent = Material::find()->select(['id'])->where(['official_account_ud'=>$official_account_id,'media_id'=>$media_id])
                    ->asArray()->one();
                $news_info = Material::find()->select(['id','official_account_id','title','author','description','cover_url','weixin_source_url'])
                    ->where(['parent_id'=>$news_info_parent['id']])->asArray()->all();
                foreach ($news_info as $new_info)
                    $news_data[] = [
                        $official_account_id,$media_id,'news',$new_info['title'],
                        $new_info['author'],$new_info['description'],0,
                        $new_info['cover_url'],$new_info['weixin_source_url'],$new_info['source_url'],time()
                    ];
            }

            $rows[] = [$official_account_id,0,$k+1,$v['name'],$v['type'],$key,$url,$k,time(),$media_id];
            if(!empty($v['sub_button'])){
                foreach($v['sub_button'] as $k1=>$v1){
                    $url1 = empty(isset($v1['url'])?$v1['url']:null)?null:$v1['url'];
                    $key1 = empty(isset($v1['key'])?$v1['key']:null)?null:$v1['key'];
                    $media_id_s = empty(isset($v1['media_id'])?$v1['media_id']:null)?null:$v1['media_id'];

                    if(isset($v1['type']) && ($v1['type'] == 'news')){
                        $is_medai_id = MenusNews::find()->where(['official_account_ud'=>$official_account_id,'media_id'=>$media_id_s])->one();
                        if($is_medai_id){continue;}
                        $news_info_parent = Material::find()->select(['id'])->where(['official_account_ud'=>$official_account_id,'media_id'=>$media_id_s])
                            ->asArray()->one();
                        $news_info = Material::find()->select(['id','official_account_id','title','author','description','cover_url','weixin_source_url'])
                            ->where(['parent_id'=>$news_info_parent['id']])->asArray()->all();
                        foreach ($news_info as $new_info)
                            $news_data[] = [
                                $official_account_id,$media_id_s,'news',$new_info['title'],
                                $new_info['author'],$new_info['description'],0,
                                $new_info['cover_url'],$new_info['weixin_source_url'],$new_info['source_url'],time()
                            ];
                    }

                    $rows[] = [$official_account_id,$k+1,0,$v1['name'],$v1['type'],$key1,$url1,$k,time(),$media_id];
                }
            }
        }
        $db = Yii::$app->db->createCommand();
        $db ->delete(Menus::tableName(),['official_account_id'=>$official_account_id])->execute();
        $db ->batchInsert(Menus::tableName(), [
            'official_account_id',
            'parent_id',
            'id_s',
            'name',
            'type',
            'key',
            'url',
            'sort',
            'created_at',
            'media_id',
            'value',
            'msg_type'
        ], $rows)
            ->execute();

        $db ->batchInsert('menus_news',
            [
                'account_id',
                'media_id',
                'type',
                'title',
                'author',
                'digest',
                'show_cover',
                'cover_url',
                'content_url',
                'source_url',
                'created_at'
            ], $news_data)->execute();
    }

    // TODO 删掉不用的代码
    //同步微信当前菜单到本地
    public function actionSyncCurrent(){
        if(!Yii::$app->exAuthManager->can('menus/sync-current')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat){
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
        $list = $this->wechat->menu->current();

        $data = $list->selfmenu_info;

        $button_list = $data['button'];
        $rows = [];
        foreach($button_list as $k=>$v){
            $url = empty(isset($v['url'])?$v['url']:null)?null:$v['url'];
            $key = empty(isset($v['key'])?$v['key']:null)?null:$v['key'];
            $media_id = empty(isset($v['media_id'])?$v['media_id']:null)?null:$v['media_id'];

            $rows[] = [$official_account_id,0,$k+1,$v['name'],$v['type'],$key,$url,$k,time(),$media_id];
            if(!empty($v['sub_button'])){
                foreach($v['sub_button'] as $k1=>$v1){
                    $url1 = empty(isset($v1['url'])?$v1['url']:null)?null:$v1['url'];
                    $key1 = empty(isset($v1['key'])?$v1['key']:null)?null:$v1['key'];
                    $media_id = empty(isset($v1['media_id'])?$v1['media_id']:null)?null:$v1['media_id'];
                    $rows[] = [$official_account_id,$k+1,0,$v1['name'],$v1['type'],$key1,$url1,$k,time(),$media_id];
                }
            }
        }
        $db = Yii::$app->db->createCommand();
        $db ->delete(Menus::tableName(),['official_account_id'=>$official_account_id])->execute();
        $db ->batchInsert(Menus::tableName(), [
            'official_account_id',
            'parent_id',
            'id_s',
            'name',
            'type',
            'key',
            'url',
            'sort',
            'created_at',
            'media_id',
        ], $rows)
            ->execute();

        return json_encode(["code"=>0,"msg"=>$this->status_code_msg[0]]);
    }

    // TODO 删掉不用的代码
    public function actionSendMenu(){

        if(!Yii::$app->exAuthManager->can('menus/send-menu')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $tree2 = $this->_getlist($official_account_id);
//        return \GuzzleHttp\json_encode($tree2);exit;
        try{
            $res = $this->wechat->menu->add($tree2);
            if($res->errcode == 0){
                return json_encode(["code"=>0,"msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to send menu to wechat cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$e->getMessage()]);
        }
    }
    public function _SendMenu($official_account_id){

        // if(!Yii::$app->exAuthManager->can('menus/send-menu')) {
        //     return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        // }

        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return false;
        }
        $tree2 = $this->_getlist($official_account_id);
//        return \GuzzleHttp\json_encode($tree2);exit;
        try{
            $res = $this->wechat->menu->add($tree2);
            if($res->errcode == 0){
                return true;
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to send menu to wechat cos reason:(%s)', json_encode($e->getMessage())));
            return false;
        }
    }

    public function _getlist($official_account_id){
        $tree['button']= array();
        $data = $this->_get_data($official_account_id);
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

    public function _getListOnView($official_account_id){
        $tree['button']= array();
        $data = $this->_get_data($official_account_id);
        foreach ($data as $k => $d) {
            if ($d ['parent_id'] != 0)
                continue;
            $tree ['button'] [$d ['id_s']] = $this->_deal_data_view($d);
            unset ($data [$k]);
        }
        foreach ($data as $k => $d) {
            $tree ['button'] [$d ['parent_id']] ['sub_button'] [] = $this->_deal_data_view($d);
            unset ($data [$k]);
        }
        $tree2 ['button'] = [];

        foreach ($tree ['button'] as $k => $d) {
            $tree2 ['button'] [] = $d;
        }
        return $tree2 ['button'];
    }

    /*
    * 重置微信端菜单
    *
    * */
    /*
    public function actionDeleteMenuServer(){

        if(!Yii::$app->exAuthManager->can('menus/delete-menu-server')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->post('official_account_id');
        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        try{
            $res = $this->wechat->menu->destroy();
            if($res->errcode == 0){
                return json_encode(["code"=>0,"msg"=>$this->status_code_msg[0]]);
            }
        }catch (\Exception $e){
            Yii::error(sprintf('Fail to delete menu from wechat cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[10101]]);
        }
    }
*/

    // ------ below private funcs

    private function _get_data($official_account_id) {

        $model = new Menus();
        $list = $model->find()->where(['official_account_id'=>$official_account_id])->asArray()->all();

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

    /*
     *
     */
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

    private function _deal_data_view($d) {
        $res ['name'] = str_replace ( '├──', '', $d ['name'] );

//        var_dump($d);
        if ($d ['type'] == 'view') {
            $res ['type'] = 'view';
            $res ['value'] =  $d ['url'];
        } elseif ($d ['type'] == 'media_id' || $d ['type'] == 'view_limited' || $d ['type'] == 'news' || $d ['type'] == 'img' || $d ['type'] == 'video' || $d ['type'] == 'voice') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['media_id'] = trim ( $d ['media_id'] );
        } elseif ($d ['type'] == 'text') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['value'] = trim ( $d ['value'] );
        }elseif ($d ['type'] == 'click' && $d['msg_type'] == 'text') {
            $res ['type'] = 'text';
            $res ['value'] = trim ( $d ['value'] );
        }elseif ($d['msg_type'] == 'news') {
            $new_data = [];
            $news_info = MenusNews::find()->where(['account_id'=>$d['official_account_id'],'media_id'=>$d ['media_id']])->asArray()->all();
            foreach($news_info as $new_info){
                $new_data[] = [
                        'title' => $new_info['title'],
                        'digest' => $new_info['digest'],
                        'cover_url' => Utils::prepare_cover_url($new_info['cover_url']),
                    ];
            }
            $res ['type'] = 'news';
            $res ['value'] = trim ( $d ['media_id'] );
            $res ['news_info'] = $new_data;
        }elseif ($d['msg_type'] == 'img') {
            $res ['type'] = trim ( $d ['msg_type'] );
            $res ['value'] = trim ( $d ['media_id'] );
        }elseif ($d ['type'] != 'none') {
            $res ['type'] = trim ( $d ['type'] );
            $res ['key'] = trim ( $d ['key'] );
        }

        return $res;
    }
}
