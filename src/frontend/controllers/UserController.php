<?php

namespace frontend\controllers;

use Yii;

use common\models\LoginForm;

class UserController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [

                'class' => 'yii\filters\AccessControl',

                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'signup'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],

                'denyCallback' => function($rule, $action) {
                    BaseController::denyCallback($rule, $action);
                }

            ]
        ];
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {

            $user = Yii::$app->user->identity;

            $role_info = $this->_construct_role_info($user->role_id);

            $final_data = ["user_info" => [
                "id" => $user->id,
                "phone" => $user->phone,
                "nickname" => $user->nickname,
                // "weixin_id" => $user->weixin_id,
                "role_info" => $role_info,
                "status" => $user->status
            ],"is_login"=>true];
            $this->manageLog(0,'用户登录');
            return json_encode(['code'=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
        }else{
            if(Yii::$app->request->isGet){
                return json_encode(["code"=>0, "msg"=>"请登录","data"=>["is_login"=>false]]);
            }
            $post_content = Yii::$app->request->post();

            $model = new LoginForm();

            $login_content = ["LoginForm" => $post_content];

            if ($model->load($login_content) && $model->login()) {

                $user = Yii::$app->user->identity;

                $role_info = $this->_construct_role_info($user->role_id);

                $final_data = ["user_info" => [
                    "id" => $user->id,
                    "phone" => $user->phone,
                    "nickname" => $user->nickname,
                    // "weixin_id" => $user->weixin_id,
                    "role_info" => $role_info,
                    "status" => $user->status
                ]];
                $this->manageLog(0,'用户登录');
                return json_encode(['code'=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
            }

            return json_encode(["code"=>20001, "msg"=>$this->status_code_msg[20001]]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        $this->manageLog(0,'用户退出');
        Yii::$app->user->logout();
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*
     * 修改密码
     */
    public function actionModifyPassword()
    {
        $post_content = Yii::$app->request->post();
        $new_password = $post_content['new_password'];
        $old_password = $post_content['old_password'];

        if(!Yii::$app->user->identity->validatePassword($old_password)) {
            return json_encode(['code'=>20008, 'msg'=>$this->status_code_msg[20008]]);
        }

        if(!$new_password or strlen($new_password) < 6) {
            return json_encode(['code'=>10101, 'msg'=>$this->status_code_msg[10101]]);
        }

        $is_modified = Yii::$app->user->identity->modifyPassword($new_password);

        if(!$is_modified) {
            Yii::error(sprintf("Fail to modify user password cos db error cos reason(%s).", "temply known")); # TODO
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        $this->manageLog(0,'修改用户密码');

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /*
     * 修改自己的个人资料
     */
    public function actionModifyPersonalInfo()
    {
        $post_content = Yii::$app->request->post();

        $nickname = $post_content['nickname'];
        $weixin_id = $post_content['weixin_id'];

        if($weixin_id == 'null') {
            $weixin_id = NULL;
        }

        if(!$nickname) {
            return json_encode(['code'=>10101, 'msg'=>$this->status_code_msg[10101]]);
        }

        $current_user = Yii::$app->user->identity;

        $current_user->nickname = $nickname;
        $current_user->weixin_id = $weixin_id;
        $is_modified = $current_user->save();

        if(!$is_modified) {
            Yii::error(sprintf("Fail to modify user personal info cos db error cos reason(%s).", "temply known")); # TODO
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    // -------- private helper funcs
    private function _construct_role_info($role_id) {

        $role_info = Yii::$app->exAuthManager->getRoleById($role_id);

        return [
            "id"=>$role_info['id'],
            "name"=>$role_info['name'],
            // "is_super_admin"=>$role_info['is_super_admin'],
            // "permission_list"=>json_decode($role_info['permission_id_list']),
            "role_type"=>$role_info['role_type'],
            "role_level"=>$role_info['role_level']
        ];
    }
}
