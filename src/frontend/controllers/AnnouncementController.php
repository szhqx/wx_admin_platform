<?php

namespace frontend\controllers;

use Yii;

use common\models\Announcement;

class AnnouncementController extends BaseController
{
    /**
     * 增加公告.
     *
     * @return string
     */
    public function actionCreate()
    {
        if(!Yii::$app->exAuthManager->can('announcement/create')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }


        $post_content = Yii::$app->request->post();

        $announcement = new Announcement();

        $announcement_content = ["Announcement"=>$post_content];

        if($announcement->load($announcement_content) && $announcement->create()) {
            $this->manageLog(0,'添加公告');
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }

        Yii::error(sprintf('Fail to create user cos reason:(%s)', json_encode($announcement->errors)));
        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
    }

    /**
     *  删除公告（暴力删除）
     *
     * @return string
     */
    public function actionDelete()
    {
        if(!Yii::$app->exAuthManager->can('announcement/delete')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $post_content = Yii::$app->request->post();

        $announcement = new Announcement();

        $is_deleted = $announcement->deleteByIds($post_content['id']);
        if(!$is_deleted) {
            $this->manageLog(0,'删除公告');
            Yii::error(sprintf('Fail to delete user cos reason:(%s)', json_encode($announcement->errors)));
            return json_encode(["code"=>-1, "msg"=>$this->status_code_msg[-1]]);
        }

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
    }

    /**
     *  查看最新公告(根据时间排序)
     *
     * @return string
     */
    public function actionMostNewAnnouncement()
    {
        if(!Yii::$app->exAuthManager->can('announcement/most-new-announcement')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $announcement = Announcement::findMostNew();

        if(!$announcement) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }

        $final_data = ["announcement_info" => [
            "id" => $announcement->id,
            "content" => $announcement->content,
            "created_at" => $announcement->created_at,
            "user_id" => $announcement->user_id,
        ]];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     *  查看查看公告列表 (不做分页)
     *
     * @return string
     */
    public function actionAnnouncementList()
    {
        if(!Yii::$app->exAuthManager->can('announcement/announcement-list')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $db =  new \yii\db\Query();
        $res = $db->select(['u.nickname','a.title','a.content','a.created_at','a.user_id'])->from('announcement as a')->leftJoin('user u','a.user_id=u.id')->all();

        $final_data = ["announcement-list" => $res];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);
    }

    /**
     *  修改公告
     *
     * @return string
     */
    public function actionUpdate()
    {
        if(!Yii::$app->exAuthManager->can('announcement/update')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }
        $post_content = Yii::$app->request->post();

        $announcement = Announcement::findById($post_content['id']);
        $announcement ->updated_at = time();
        $announcement ->content = $post_content['content'];

        if($announcement->save()){
            $this->manageLog(0,'修改公告');
            return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0]]);
        }
        return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
    }

}
