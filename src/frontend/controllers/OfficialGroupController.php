<?php

namespace frontend\controllers;

use common\models\OfficialAccount;
use Yii;

use common\models\OfficialGroup;

class OfficialGroupController extends BaseController
{

    /**
     * 创建公众号群组
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('official-group/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $model = new OfficialGroup();
        $model->name = $post_content['name'];
        $model->desc = $post_content['name'];
        $model->status = 1;
        $model->company_id  = Yii::$app->user->identity->company_id;
        $model->created_at = time();
        if ($model->save()) {
            $this->manageLog(0,'添加公众号分组');
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }
        Yii::error(sprintf('Fail to create user cos reason:(%s)', json_encode($model->errors)));
        return json_encode(["code"=>10101, "msg"=>$model->getErrors()]);
    }

    /**
     * 修改公众号群组
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('official-group/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();

        $model = OfficialGroup::findOne($post_content['id']);

        if(!$model){
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $model->name = $post_content['name'];
        $model->desc = $post_content['name'];
        $model->updated_at = time();

        if($model->save()){
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to modify OfficialGroup cos reason:(%s)', json_encode($model->getErrors())));

        return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
    }

    /**
     * 查看公众号群组信息
     *
     * @return string
     */
    public function actionInfo()
    {
        // 暂无需求
    }

    /**
     * 查看公众号群组列表信息(无分页)
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('official-group/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $model = OfficialGroup::find();

        // $debug_group = [
        //     [
        //         "id"=>1,
        //         "name"=>"test"
        //     ],
        //     [
        //         "id"=>2,
        //         "name"=>"asdjkh"
        //     ]
        // ];
        $group_list = $model->select(['id','name'])->where(['status'=>1,'company_id'=>Yii::$app->user->identity->company_id])->asArray()->all();
        $list = [];
        $list[] = [
            "id"=>0,
            "name"=>"未分组",
            "count"=>OfficialAccount::getCountByGroup(0)
        ];
        foreach($group_list as $k=>$v){
            $list[] = [
                "id"=>$v['id'],
                "name"=>$v['name'],
                "count"=>OfficialAccount::getCountByGroup($v['id'])
            ];
        }

        $final_data = [
            "group_info_list" => $list,
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0],"data"=>$final_data]);
    }

    public function actionMove()
    {
        if(!Yii::$app->exAuthManager->can('official-group/move')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_ids = Yii::$app->request->post('official_account_ids');
        $group_id = Yii::$app->request->post('group_id');

        Yii::$app->db->createCommand()->update('official_account', ['group_id' => $group_id], ['in','id',$official_account_ids])->execute();

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('official-group/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $group_id = Yii::$app->request->post('group_id');

        $model = OfficialGroup::findById($group_id);

        $is_delete = $model->delete();

        if($is_delete){
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        return json_encode(["code"=>1, "msg"=>$this->status_code_msg[-1]]);
    }
}
