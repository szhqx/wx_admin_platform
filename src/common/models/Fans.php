<?php

namespace common\models;

use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

use EasyWeChat\Core\Exceptions\HttpException;
/**
 * Signup form.
 */
class Fans extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
//    public $mark_name;
    public function rules()
    {
        
        return [
            [['open_id'], 'string'],
            [['account_id','group_id','unionid','liveness','subscribed_at','subscribed_at'], 'integer'],
        ];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }

    public static function findByOpenid($openid){
        return static::find()->where(['open_id' => $openid])->one();
    }

    public static function getOpenidById($id){
        $res = static::find()->where(['id' => $id])->asArray()->one();
        return $res['open_id'];
    }


    public static function getList($params, $page, $num)
    {
        $query = static::find();

        $query->where(['account_id'=>$params['official_account_id'],'status'=>1]);
        $query->andWhere(['not in', 'group_id',[1]]); //不显示黑名单用户

        foreach($params as $key=>$value) {
            if($key=='nickname') {
                $query->andWhere(['like', 'nickname', $value]);
            }elseif($key == 'tag_id'){
                $id_list = FansTagMap::getUserIdListByTagId($value);
//                var_dump($id_list);exit;
                $query->andWhere(['id'=>$id_list]);
            }elseif($key == 'group_id'){
                $query->andWhere(['group_id'=>$value]);
            }
        }
//        $query->orderBy(["id" => SORT_DESC]);
        $total = $query->count();
        $start = max(($page-1)*$num, 0);
        $query->limit($num)->offset($start);
//        var_dump($query->asArray()->all());exit;
        $raw_account_list = $query->asArray()->all();
        $list = [];
        foreach($raw_account_list as $k=>$v){
            $list[$k]['id'] = $v['id'];
            $list[$k]['open_id'] = $v['open_id'];
            $list[$k]['nickname'] = $v['nickname'];
            $list[$k]['avator'] = $v['avator'];
            $list[$k]['remark'] = $v['remark'];
            $list[$k]['signature'] = $v['signature'];
            $list[$k]['tag_info'] = is_null(FansTagMap::getTagById($v['id']))?'无标签':FansTagMap::getTagById($v['id']);
//            $li[$k]st['tag_info'] = unserialize($v['tagid_list']) == FansTag::getTagNameByIds($v['tagid_list']);
            $list[$k]['sex'] = $v['sex'];
            $list[$k]['language'] = $v['language'];
            $list[$k]['province'] = $v['province'];
            $list[$k]['country'] = $v['country'];
            $list[$k]['city'] = $v['city'];
            $list[$k]['created_at'] = $v['created_at'];
            $list[$k]['is_subscribe'] = $v['is_subscribe'];
        }
        unset($raw_account_list);
        $final_data = [
            "fans_list" => $list,
            "page_num" => ceil($total/$num)
        ];
        
        return $final_data;
    }

    public static function getTotalCount($params)
    {

        $query = static::find();

        $query->where(['account_id'=>$params['official_account_id'],'status'=>1]);
        $query->andWhere(['not in', 'group_id',[1]]); //不显示黑名单用户

        foreach($params as $key=>$value) {
            if($key=='nickname') {
                $query->andWhere(['like', 'nickname', $value]);
            }elseif($key == 'tag_id'){
                $id_list = FansTagMap::getUserIdListByTagId($value);
//                var_dump($id_list);exit;
                $query->andWhere(['id'=>$id_list]);
            }elseif($key == 'group_id'){
                $query->andWhere(['group_id'=>$value]);
            }
        }
        $total = $query->count();
        return $total;
    }
    
    public static function getNextOpenid($official_account_id){
        $res = static::find()->select(['open_id','id'])->where(['account_id'=>$official_account_id])->orderBy('id desc')->one();
        if($res){
            return $res['open_id'];
        }
        return null;

    }

    public static function getNickNameById($id){
        $res = static::find()->select(['nickname','id'])->where(['id'=>$id])->one();
        return $res['nickname'];
    }

    public static function findByWechatName($wechat_name,$official_account_id){
        $res = static::find()->select(['nickname','id','open_id'])->where(['nickname'=>$wechat_name,'account_id'=>$official_account_id])->one();
        if(!$res){
            return false;
        }
        return $res;
    }

    public static function getIdByOpenId($openid,$official_account_id){
        $res = static::find()->select(['id'])->where(['open_id'=>$openid,'account_id'=>$official_account_id])->one();
        if(!$res){
            return 0;
        }
        return $res['id'];
    }



}
