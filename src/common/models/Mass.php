<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use common\models\Article;

/**
 * 群发记录
 */
class Mass extends ActiveRecord
{
    const STATUS_DELETED = 0;

    const STATUS_NORMAL = 1;

    const STATUS_SENDING = 2;

    const STATUS_ABNORMAL = 3;

    const STATUS_COMPLETED = 4;

    public static function findById($id, $checkStatus=true){

        $query = static::find();

        $query->andWhere(['id' => $id]);

        if($checkStatus) {
            $query->andWhere(['not in', 'status', [self::STATUS_DELETED]]);
        }

        return $query->one();
    }

    public function finishBrocast($send_info) {

        $now = time();

        $this->updated_at = $now;
        if(!$this->pub_at) {
            $this->pub_at = $now;
        }
        $this->status = self::STATUS_COMPLETED;

        $this->msg_id = $send_info['msg_id'];

        return $this->save(false);
    }

    public function canModify() {
        return in_array($this->status, [self::STATUS_NORMAL]);
    }

    public function hasSend() {
        return in_array($this->status, [self::STATUS_SENDING, self::STATUS_COMPLETED]);
    }

    public function deleteRecord() {
        $this->status = self::STATUS_DELETED;
        $this->updated_at = time();
        return $this->save(false);
    }

    public function deleteSendInfo() {

        $this->status = self::STATUS_DELETED;
        $this->updated_at = time();
        $is_updated = $this->save(false);

        if(!$is_updated) {
            return false;
        }

        $is_updated = Yii::$app->db->createCommand('update article set status = :status, updated_at = :now WHERE mass_id=:mass_id')
                                   ->bindValue(':mass_id', $this->id)
                                   ->bindValue(':status', Article::STATUS_DELETED)
                                   ->bindValue(':now', time())
                                   ->execute();

        return $is_updated;
    }

    public static function getTotalCount($params)
    {

        $query = static::find();

        foreach($params as $key=>$value) {
            if($key=='pub_at_begin') {
                $query->andWhere(['>=', 'pub_at', $value]);
            } else if($key == 'pub_at_end') {
                $query->andWhere(['<=', 'pub_at', $value]);
            } else {
                $query->andWhere([$key=>$value]);
            }
        }

        $total = $query->count();

        return $total;
    }

    public static function getList($page, $num, $params) {

        $query = static::find();

        foreach($params as $key=>$value) {
            if($key=='pub_at_begin') {
                $query->andWhere(['>=', 'pub_at', $value]);
            } else if($key == 'pub_at_end') {
                $query->andWhere(['<=', 'pub_at', $value]);
            } else {
                $query->andWhere([$key=>$value]);
            }
        }

        // if($checkStatus) {
        //     $query->andWhere(['status'=>self::STATUS_COMPLETED]);
        // }

        $query->orderBy(["pub_at" => SORT_ASC]);

        $start = max(($page-1)*$num, 0);

        $query->limit($num)->offset($start);

        return $query->all();
    }

    public static function broadcast($material_info, $wechat_tag_id, $wechat) {

        // $broadcast = $this->wechat->broadcast;
        $broadcast = $wechat->broadcast;
        $send_info = null;

        if($material_info->type == Material::MATERIAL_TYPE_ARTICLE_MULTI) {

            // $send_info = $broadcast->sendNews($material_info->media_id, 100);

            if($wechat_tag_id) {
                $send_info = $broadcast->sendNews($material_info->media_id, $wechat_tag_id);
            } else {
                $send_info = $broadcast->sendNews($material_info->media_id);
            }

        } else if ( $material_info->type == Material::MATERIAL_TYPE_IMAGE) {

            // $send_info = $broadcast->sendImage($material_info->media_id, 100);

            if($wechat_tag_id) {
                $send_info = $broadcast->sendImage($material_info->media_id, $wechat_tag_id);
            } else {
                $send_info = $broadcast->sendImage($material_info->media_id);
            }

        }
        // TODO support other type

        return $send_info;
    }

    public static function checkMaterialInUsed($material_id) {

        $query = static::find();

        $query->andWhere(['material_id' => $material_id]);
        $query->andWhere(['in', 'status', [self::STATUS_NORMAL]]);

        return $query->one();
    }
}