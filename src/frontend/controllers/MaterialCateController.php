<?php

namespace frontend\controllers;

use Yii;

use common\models\MaterialCate;

class MaterialCateController extends BaseController
{
    /**
     * 创建素材分类.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('material-cate/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $post_content = Yii::$app->request->post();
        if(!isset($post_content['title'])){
            return json_encode(["code"=>-1, "msg"=>'缺少参数title']);
        }
        $model = new MaterialCate();
        $model->title = $post_content['title'];
        $model->display_order = isset($post_content['display_order'])?$post_content['display_order']:1;
        $model->created_at = time();
        if($model->save()){
            $this->manageLog(0,'创建素材分类--'.$post_content['title']);
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }else{
            Yii::error(sprintf('Fail to create material-cate cos reason:(%s)', json_encode($model->errors)));
            return json_encode(["code"=>-1, "msg"=>$model->errors]);
        }
    }

    /**
     *  删除
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('material-cate/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $id = Yii::$app->request->get('id');
        if(!$id){
            return json_encode(["code"=>-1, "msg"=>'却少参数id']);
        }
        $model = MaterialCate::findOne($id);

        if(!$model){
            return json_encode(["code"=>-1, "msg"=>'未找到此id='.$id.'的对象']);
        }

        if($model->delete()){
            $this->manageLog(0,'删除素材分类--'.$model->title);
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }
    }

    /**
     *  修改
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('material-cate/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $id = Yii::$app->request->post('id');
        if(!$id){
            return json_encode(["code"=>-1, "msg"=>'miss id']);
        }
        $title  = Yii::$app->request->post('title');
        if(!$title){
            return json_encode(["code"=>-1, "msg"=>'miss title']);
        }
        $model = MaterialCate::findOne($id);
        if(!$model){
            return json_encode(["code"=>-1, "msg"=>'material-cate not find']);
        }
        $model->title = $title;
        if($model->save()){
            $this->manageLog(0,'修改素材分类--'.$title);
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }
        return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);

    }


    /**
     * 获取素材列表(无分页)
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('material-cate/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $model = new MaterialCate();
        $list = $model->find()->select(['title'])->asArray()->all();

        $final_data = [
             "info-list" => $list,
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

}
