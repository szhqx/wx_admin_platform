<?php

namespace frontend\controllers;

use common\models\AuthorityRole;
use common\models\UserRoleMap;
use common\models\Company;
use common\models\User;
use Yii;


class CompanyController extends BaseController
{
    /**
     * 创建公司.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('company/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $userid = $user_id = Yii::$app->user->getId();
        $res = $this->_checkUser($userid);
        if($res){
            return json_encode(["code"=>-1, "msg"=>'没有权限']);
        }

        $time = time();
        $post_content = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();

        $role_info = AuthorityRole::findByTypeLevel(AuthorityRole::ROLE_TYPE_ADMIN,
                                                    AuthorityRole::ROLE_LEVEL
        );

        try{

            $company = new Company();
            $company -> name = $post_content['name'];
            $company -> description = $post_content['contact'];
            $company -> login_time = (int)$post_content['login_time'];
            $company -> status = (int)$post_content['status'];
            $company -> created_at = $time;

            if($company->save()) {

                // 先创建管理员
                $user = new User();
                $user->nickname = $post_content['nickname'];
                $user->phone = $post_content['phone'];
                $user->company_id = $company->id;
                $user->role_id =$role->id;
                $user->created_at = $time;
                $user->status = 1;
                $user->setPassword($post_content['password']);
                $user->generateAuthKey();

                $user->role_type = AuthorityRole::ROLE_TYPE_ADMIN;
                $user->role_id = $role_info['id'];

                if ($user->save()) {

                    // $map = new UserRoleMap();
                    // $map  -> role_id = $role_info['id'];
                    // $map  -> parent_id = 0;
                    // $map  -> user_id = 1;
                    // $map  -> created_at = $time;

                    // if($map->save(false)) {
                        $transaction->commit();
                        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
                    // }

                    // $transaction->rollBack();
                    // return json_encode(["code"=>-1, "msg"=>$user->getErrors()]);
                }

                $transaction->rollBack();
                return json_encode(["code"=>-1, "msg"=>$user->getErrors()]);
            }

            $transaction->rollBack();
            return json_encode(["code"=>-1, "msg"=>$role->getErrors()]);

        } catch (\Exception $e){
            $transaction->rollBack();
            Yii::error(sprintf('Fail to create Company cos reason:(%s)', json_encode($e->getMessage())));
            return json_encode(["code"=>10101, "msg"=>$e->getMessage()]);
        }
    }

    public function _checkUser($user_id){
        $user_info = User::findById($user_id, false);
        if($user_info->company_id == 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  删除公司（软删）
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('company/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();
        $id = $post_content['id'];

        $model = Company::findById($id);
        if(!$model) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $model->status = 0;
        $model->updated_at = time();
        $is_deleted = $model->save();
        if(!$is_deleted) {
            Yii::error(sprintf('Fail to delete company cos reason:(%s)', json_encode($model->errors)));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     *  修改公司信息
     *
     * @return string
     */
    public function actionModify()
    {
        if(!Yii::$app->exAuthManager->can('company/modify')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();

        $model = Company::findById($post_content['id']);
        $model->name = $post_content['name'];
        $model->description = $post_content['contact'];
        $model->updated_at = time();

        if($model->save()) {
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to modify company cos reason:(%s)', json_encode($model->errors)));

        return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
    }

    /**
     *  查看公司信息
     *
     * @return string
     */
    public function actionInfo()
    {

         if(!Yii::$app->exAuthManager->can('company/info')) {
             return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
         }

        $post_content = Yii::$app->request->post();

        $id = $post_content['id'];

        $model = Company::findById($id);

        if(!$model) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        // $role_info = Yii::$app->exAuthManager->getRoleById($user->role_id);

        $final_data = ["company_info" => [
            "id" => $model->id,
            "name" => $model->name,
            "description" => $model->description,
            "created_at" => $model->created_at,
        ]];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     * 查看公司信息列表
     *
     * @return string
     */
    public function actionInfoList()
    {
        if(!Yii::$app->exAuthManager->can('company/info-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $params = [];
        $page = (int)Yii::$app->request->get('page', 1);
        $num = (int)Yii::$app->request->get('num', 20);
        $name = Yii::$app->request->get('name', null);
        if(!is_null($name)) {
            $params['name'] = $name;
        }
        $final_data = Company::getList($params, $page, $num);
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }
}
