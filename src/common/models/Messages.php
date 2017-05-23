<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * Signup form.
 */
class Messages extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * ALTER TABLE messages ADD COLUMN `media_id` VARCHAR(20)  DEFAULT 0 COMMENT 'mediaid';
    ALTER TABLE messages ADD COLUMN `voice_format` VARCHAR(200)  DEFAULT 0 COMMENT '音频类型';
    ALTER TABLE messages ADD COLUMN `recognition` VARCHAR(100)  DEFAULT 0 COMMENT '自动识别音频';
    ALTER TABLE messages ADD COLUMN `thumb_media_id` VARCHAR(200)  DEFAULT 0 COMMENT '视频消息缩略图的媒体id';
     * ALTER TABLE messages ADD COLUMN `msg_type` VARCHAR(20)  DEFAULT 0 COMMENT '消息类型';
     */
    public function rules()
    {
        return [
            [[
                'official_account_id',
                'fans_id',
                'msg_id',
                'is_reply',
                'created_at',
                'updated_at'
            ],
                'integer'],
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }


    public static function getList($params, $page, $num)
    {
        $query = static::find();

        $query->where(['official_account_id'=>$params['official_account_id']]);

        $query->orderBy(["id" => SORT_DESC]);
        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);
//        var_dump($query->asArray()->all());exit;
        $raw_account_list = $query->asArray()->all();
        $list = [];
        foreach($raw_account_list as $data){
            $list['id'] = $data['id'];
            $list['official_account_id'] = $data['official_account_id'];
            $list['is_reply'] = $data['is_reply']==0?"未回复":"已回复";

            if($data['msg_type'] == "text"){
                $list['content'] = $data['content'];
                $list['msg_type'] = $data['msg_type'];
                //1->article, 2->image, 3->voice, 4->video, 5->text
            }elseif($data['msg_type'] == "video"){
                $list['content'] = '';
                $list['msg_type'] = $data['msg_type'];
            }elseif($data['msg_type'] == "voice"){
                $list['content'] = '';
                $list['msg_type'] = $data['msg_type'];
            }elseif($data['msg_type'] == "image"){
                $list['content'] = $data['imgurl'];
                $list['msg_type'] = $data['msg_type'];
            }
            $list['is_collection'] = $data['is_collection'];
            $list['created_at'] = $data['created_at'];
            $list['created_at'] = $data['created_at'];
        }
        unset($data);
        unset($raw_account_list);
        $total = $query->count();
        $final_data = [
            "message_list" => $list,
            "page_num" => ceil($total/$num)
        ];
//        var_dump($list);exit;
        return $final_data;
    }

    public static function getTotalCount($params)
    {

        $query = static::find();

        $query->where(['official_account_id'=>$params['official_account_id']]);
//        $query->andWhere(['not in', 'group_id',[1]]); //不显示黑名单用户

//        foreach($params as $key=>$value) {
//            if($key=='nickname') {
//                $query->andWhere(['like', 'nickname', $value]);
//            }elseif($key == 'tag_id'){
//                $id_list = FansTagMap::getUserIdListByTagId($value);
////                var_dump($id_list);exit;
//                $query->andWhere(['id'=>$id_list]);
//            }elseif($key == 'group_id'){
//                $query->andWhere(['group_id'=>$value]);
//            }
//        }
        $total = $query->count();
        return $total;
    }

}
