<?php

namespace frontend\controllers;

use common\helpers\Utils;
use common\models\AdvertisementOfficial;
use Yii;

use common\models\Mass;
use common\models\FansTag;
use common\models\MassForm;
use common\models\Material;
use common\models\OfficialAccount;
use common\models\Article;
use common\helpers\ArticleTrait;

class MassController extends BaseController
{
    use ArticleTrait;

    /**
     * 获取群发排期列表.
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('mass/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $request = Yii::$app->request;
        $current_user = Yii::$app->user;

        // prepare params
        $page = $request->get('page', 1);
        $num = $request->get('num', 20);
        $official_account_id = $request->get('official_account_id', null);
        $user_id = $request->get('user_id', null);
        $type = $request->get('type', null);
        $pub_at_begin = $request->get('pub_at_begin', null);
        $pub_at_end = $request->get('pub_at_end', null);

        if(!$official_account_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            Yii::error(sprintf('Fail to find official account info cos bad official account id(%s) params.', $official_account_id));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // check params
        $params = [];
        if(!is_null($official_account_id)) $params['official_account_id'] = $official_account_id;
        if(!is_null($user_id)) $params['user_id'] = $user_id;
        if(!is_null($type)) $params['type'] = $type;
        if(!is_null($pub_at_begin)) $params['pub_at_begin'] = $pub_at_begin;
        if(!is_null($pub_at_end)) $params['pub_at_end'] = $pub_at_end;

        //
        $params['status'] = Mass::STATUS_NORMAL;

        // 数据权限校验
        if(!Yii::$app->dataAuthManager->canModifyMass(null, $current_user, $official_account)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $mass_list = [];
        $material_id_list = [];
        $raw_mass_list = Mass::getList($page, $num, $params);

        foreach($raw_mass_list as $raw_mass) {
            // TODO 去掉这里的k/v的形式，改成数组扔回给前端
            $mass_list[$raw_mass['material_id']] = [
                "id"=>$raw_mass['id'],
                "pub_at"=>$raw_mass['pub_at'],
                "updated_at"=>$raw_mass['updated_at'],
                "material_id"=>$raw_mass['material_id']
            ];
            $material_id_list[] = $raw_mass['material_id'];
        }

        $raw_material_info_list = Material::findByIdList($material_id_list);

        $raw_material_info_child_list = Material::getChildArticleByParentIdList($material_id_list);

        foreach($raw_material_info_list as $raw_material_info) {

            $parent_material_info = $this->_construct_material_info($raw_material_info);
//            $mass_list[$raw_material_info['id']]['material_list'][] = $parent_material_info;

            $final_child_list = [];

            $_ = $raw_material_info_child_list;
            $raw_child_list = isset($_[$raw_material_info['id']]) ? $_[$raw_material_info['id']] : NULL;

            if(!$raw_child_list) {
                continue;
            }

            foreach($raw_child_list as $raw_child) {
                $final_child_list[] = $this->_construct_material_info($raw_child);
            }

            $mass_list[$raw_material_info['id']]['material_list'] = $final_child_list;
            $mass_list[$raw_material_info['id']]['type'] = $parent_material_info['type'];
        }

        $total = Mass::getTotalCount($params);

        $final_data = [
            "mass_list" => $mass_list,
            "page_num" => ceil($total/$num)
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 获取已群发列表.
     *
     * @return string
     */
    public function actionGetSendList()
    {
        if(!Yii::$app->exAuthManager->can('mass/get-send-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $request = Yii::$app->request;
        $current_user = Yii::$app->user;

        $page = $request->get('page', 1);
        $num = $request->get('num', 20);
        $official_account_id = $request->get('official_account_id', null);
        $user_id = $request->get('user_id', null);
        $type = $request->get('type', null);
        $pub_at_begin = $request->get('pub_at_begin', null);
        $pub_at_end = $request->get('pub_at_end', null);

        if(!$official_account_id) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            Yii::error(sprintf('Fail to find official account info cos bad official account id(%s) params.', $official_account_id));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $params = [];
        if(!is_null($official_account_id)) $params['official_account_id'] = $official_account_id;
        if(!is_null($user_id)) $params['user_id'] = $user_id;
        if(!is_null($type)) $params['type'] = $type;
        if(!is_null($pub_at_begin)) $params['pub_at_begin'] = $pub_at_begin;
        if(!is_null($pub_at_end)) $params['pub_at_end'] = $pub_at_end;

        $params['status'] = Mass::STATUS_COMPLETED;

        // 数据权限校验
        if(!Yii::$app->dataAuthManager->canModifyMass(null, $current_user, $official_account)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $mass_list = Article::constructMassArtileList($page, $num, $params);
        $total = Mass::getTotalCount($params);

        foreach($mass_list as &$raw_mass) {
            unset($raw_mass['user_tag_id']);
        }

        $final_data = [
            "mass_list" => array_values($mass_list),
            "page_num" => ceil($total/$num)
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 添加群发.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('mass/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $request = Yii::$app->request;
        $post_content = $request->post();
        $current_user = Yii::$app->user;

        // create a mass record
        $mass_form = new MassForm();
        $mass_form->scenario = MassForm::SCENARIO_CREATE_MASS;
        $mass_content = ['MassForm'=>$post_content];
        $db = Yii::$app->db;

        if(!($mass_form->load($mass_content) && $mass_form->validate())) {
            Yii::error(sprintf('Fail to create mass cos reason:(%s)', json_encode($mass_form->errors)));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $material_info = Material::findById($post_content['material_id']);
        if(!$material_info) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // 群发的数据权限校验
        if(!Yii::$app->dataAuthManager->canModifyMass($material_info, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $this->wechat = $this->getWechat($material_info['official_account_id']);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $transaction = $db->beginTransaction();

        $mass_form->type = $material_info['type'];
        $mass_form->media_id = $material_info['media_id'];
        $mass_form->official_account_id = $material_info['official_account_id'];
        $mass_form->user_id = Yii::$app->user->identity->id;

        $mass = $mass_form->create();
        if(!$mass) {
            Yii::error(sprintf('Fail to create mass cos reason:(%s)', json_encode($mass_form->errors)));
            $transaction->rollback();
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        if(!isset($mass_form['pub_at'])) {
            $is_send = $this->_fireMsg($mass, $material_info);
            if(!$is_send) {
                $transaction->rollback();
                return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
            }
        }

        $transaction->commit();

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     * 调整群发.
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('mass/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $request = Yii::$app->request;

        $now = time();
        $mass_id = (int)$request->post('id');
        $material_id = $request->post('material_id', null);
        $pub_at = $request->post('pub_at', null);
        $user_tag_id = $request->post('user_tag_id', null);
        $db = Yii::$app->db;
        $current_user = Yii::$app->user;

        if(!$mass_id) {
            Yii::warning('wrong params.');
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $transaction = $db->beginTransaction();

        // for update
        $raw_sql = sprintf("select * from mass where id = %d for update;", $mass_id);
        $db->createCommand($raw_sql)->queryOne();

        $mass = Mass::findById($mass_id);
        if(!$mass) {
            $transaction->rollback();
            Yii::warning(sprintf('Fail to find mass with id(%s)', $mass_id));
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // 群发的数据权限校验
        $material_info = Material::findById($mass['material_id']);
        if(!$material_info) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        if(!Yii::$app->dataAuthManager->canModifyMass($material_info, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        # 修改mass
        if(!$mass->canModify()) {
            $transaction->rollback();
            Yii::warning(sprintf('Fail to modify mass(%s) cos sended.', $mass_id));
            return json_encode(["code"=>20100, "msg"=>$this->status_code_msg[20100]]);
        }

        if(!is_null($pub_at)) {
            $mass->pub_at = $pub_at;
        }

        if(!is_null($user_tag_id)) {
            $mass->user_tag_id = $user_tag_id;
        }

        if(!is_null($material_id)) {
            $mass->material_id = $material_id;
        }
        $mass->updated_at = $now;

        $is_updated = $mass->save();

        if(!$is_updated) {
            $transaction->rollback();
            Yii::error(sprintf('Fail to update mass(%s) with params(%s).', $mass_id, json_encode($request->post())));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        // if($mass->pub_at == 0) {
        //     $is_send = $this->_fireMsg($mass, $material_info);
        //     if(!$is_send) {
        //         $transaction->rollback();
        //         return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        //     }
        // }

        $transaction->commit();

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     * 删除群发.
     *
     * @return string
     */
    public function actionDelete()
    {

        if(!Yii::$app->exAuthManager->can('mass/delete') or !Yii::$app->exAuthManager->can('mass/delete-send')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $mass_id = Yii::$app->request->post('mass_id', null);
        $db = Yii::$app->db;
        $current_user = Yii::$app->user;

        if(!$mass_id) {
            Yii::warning('wrong params.');
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $transaction = $db->beginTransaction();

        // for update
        $raw_sql = sprintf("select * from mass where id = %d for update;", $mass_id);
        $db->createCommand($raw_sql)->queryOne();

        $mass = Mass::findById($mass_id);
        if(!$mass) {
            $transaction->rollback();
            Yii::warning('wrong params.');
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // 群发的数据权限校验
        $material_info = Material::findById($mass['material_id']);
        if(!$material_info) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        if(!Yii::$app->dataAuthManager->canModifyMass($material_info, $current_user)) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        if($mass->hasSend()) {
            $is_done = $mass->deleteSendInfo();
        } else {
            $is_done = $mass->deleteRecord();
        }

        if(!$is_done) {
            $transaction->rollback();
            Yii::error('Fail to delete send cos reason("").');
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $transaction->commit();

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    // -------------------------------- private helper funcs ------------------------------------

    // 私有方法
    private function _broadcast($material_info, $wechat_tag_id, $wechat) {
        return Mass::broadcast($material_info, $wechat_tag_id, $wechat);
    }

    private function _store_send_msg($mass, $material_info) {

        switch($material_info['type']) {

        case Material::MATERIAL_TYPE_ARTICLE_MULTI:
            return $this->_store_multi_article_msg($mass, $material_info);
        case Material::MATERIAL_TYPE_IMAGE:
            return $this->_store_image_msg($mass, $material_info);
        default:
            return false;
        }

        return false;
    }

    private function _store_multi_article_msg($mass, $material_info) {
        return Article::storeMultiArticleMsg($mass, $material_info);
    }

    private function _store_image_msg($mass, $material_info)  {
        return Article::storeImgMsg($mass, $material_info);
    }

    private function _fireMsg($mass, $material_info) {

        try{
            $wechat_tag_id = null;
            if($mass->user_tag_id) {
                $wechat_tag_info = FansTag::findById($mass->user_tag_id);
                if(!$wechat_tag_info) {
                    throw new \Exception(sprintf("Fail to find fans tag(%s)", $mass->user_tag_id));
                }
                $wechat_tag_id = $wechat_tag_info['wechat_tag_id'];
            }
            $send_info = $this->_broadcast($material_info, $wechat_tag_id, $this->wechat);
            if(!$send_info) {
                throw new \Exception("");
            }
            $order_info = Material::_getAdInfo($material_info['id']);
            if(count($order_info)){
                $ids = array_column($order_info,'id');
                AdvertisementOfficial::updateAll(['status'=>1,'updated_at'=>time()],['in','id',$ids]);
            }

            $mass->msg_id = $send_info['msg_id'];

            if($material_info['type'] == Material::MATERIAL_TYPE_ARTICLE_MULTI) {
                $mass->msg_data_id = $send_info['msg_data_id'];
            }

            // create article list
            $is_stored = $this->_store_send_msg($mass, $material_info);
            if(!$is_stored) {
                throw new \Exception(sprintf("Fail to store send msg(%s)", ''));
            }

        } catch(\Exceptions $e) {
            Yii::error(sprintf('Fail to create mass cos fail to send broadcast:(%s)', $e));
            return false;
        }

        $is_updated = $mass->finishBrocast($send_info);

        return $is_updated;
    }

    private function _construct_material_info($raw_material_info) {

        $cover_url = $raw_material_info['type'] == Material::MATERIAL_TYPE_ARTICLE_MULTI? Utils::prepare_cover_url($raw_material_info['weixin_cover_url']) : Utils::prepare_cover_url($raw_material_info['source_url']);

        $parent_material_info  = [
            "id" => $raw_material_info['id'],
            "title" => $raw_material_info['title'],
            "cover_url" => $cover_url,
            "type" => $raw_material_info['type'],
            "show_cover_pic" => $raw_material_info['show_cover_pic'],
            "order" => $raw_material_info['order']
        ];

        return $parent_material_info;
    }

}
