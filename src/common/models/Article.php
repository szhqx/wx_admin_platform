<?php

namespace common\models;

use Yii;

use yii\db\ActiveRecord;

use common\models\FansTag;
use common\models\User;
use common\models\Material;
use common\models\Mass;

/**
 * 文章ActiveRecord
 */
class Article extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    public static function findById($id, $checkStatus=TRUE) {

        $query = Article::find();

        $query->andWhere(["id"=>$id]);

        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        return $query->one();
    }

    public static function findByMsgDataId($msg_data_id, $checkStatus=TRUE) {

        $query = Article::find();

        $query->andWhere(["msg_data_id"=>$msg_data_id]);

        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        return $query->one();
    }


    public static function findByMassOrderKey($mass_id, $order, $checkStatus=TRUE) {

        $query = Article::find();

        $query->andWhere(["mass_id"=>$mass_id]);

        $query->andWhere(["order"=>$order]);

        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        return $query->one();
    }

    public static function storeImgMsg($mass, $material_info) {

        $article = new Article();

        $now = time();

        $article->source_url = $material_info['source_url'];
        $article->weixin_source_url = $material_info['weixin_source_url'];
        $article->ad_source_url = $material_info['ad_source_url'];
        $article->user_id = $mass['user_id'];
        $article->author = $material_info['author'];

        $article->official_account_id = $material_info['official_account_id'];
        $article->material_id = $material_info['id'];
        $article->mass_id = $mass['id'];

        if($mass['msg_id']) {
            $article->msg_id = $mass['msg_id'];
        }
        $article->created_at = $now;
        $article->published_at = $now;

        return $article->save();
    }

    public static function storeMultiArticleMsg($mass, $material_info) {

        # construct parent article
        $parent_article = self::_constructArticleInfo($mass, $material_info);
        if(!$parent_article) {
            $err_msg = sprintf("Fail to construct article info with mass params(%s) and material_info params(%s).",
                               json_encode($mass), json_encode($material_info));
            Yii::error($err_msg);
            return false;
        }

        if($material_info['is_multi']) {

            $child_article_list = Material::getChildArticle($material_info['id']);

            if(!$child_article_list) {
                $err_msg = sprintf("Fail to find child article list with mass params(%s) and material_info params(%s).",
                                   json_encode($mass), json_encode($material_info));
                Yii::error($err_msg);
                return false;
            }

            $insert_value_list = [];
            foreach($child_article_list as $child_article) {
                $insert_value_list[] = self::_constructArticleInfoArray($mass, $child_article, $parent_article->id);
            }

            $insert_params = self::_get_insert_params();

            $insert_rows = Yii::$app->db->createCommand()->batchInsert('article', $insert_params, $insert_value_list)->execute();

            if(!$insert_rows) {
                $err_msg = sprintf("Fail to insert article list with mass params(%s) and material_info params(%s).",
                                   json_encode($mass), json_encode($material_info));
                Yii::error($err_msg);
                return false;
            }

            Yii::info("Success to store multi article msg.");
        }

        return true;
    }

    public static function constructMassArtileList($page, $num, $params) {

        $editor_id_list = [];
        $mass_list = [];
        $mass_id_list = [];
        $user_tag_id_list = [];
        $raw_mass_list = Mass::getList($page, $num, $params);

        foreach($raw_mass_list as $raw_mass) {
            $mass_list[$raw_mass['id']] = [
                "id"=>$raw_mass['id'],
                "article_list"=>[],
                "user_tag_id"=>$raw_mass['user_tag_id']
            ];

            $mass_id_list[] = $raw_mass['id'];
            $user_tag_id_list[] = $raw_mass['user_tag_id'];
        }

        $raw_article_info_list = Article::find()->where(["in", "mass_id", $mass_id_list])->all();
        $article_info_list = [];

        unset($mass_id_list);
        $editor_info_list = [];
        $editor_id_list = [];

        foreach($raw_article_info_list as $raw_article_info) {
            $editor_id_list[] = $raw_article_info['user_id'];
        }
        array_unique($editor_id_list);
        $editor_info_list = User::getUsersByIdList($editor_id_list, false);

        array_unique($user_tag_id_list);
        $receiver_info_list = FansTag::findByIdList($user_tag_id_list);

        foreach($raw_article_info_list as $raw_article_info) {

            $raw_mass = $mass_list[$raw_article_info['mass_id']];

            $editor_info = self::_constructEditorInfo($raw_article_info['user_id'], $editor_info_list);
            $receiver_info = self::_constructReceiverInfo($raw_mass['user_tag_id'], $receiver_info_list);

            if($raw_article_info['parent_id']) {
                $article_info_list[$raw_article_info['parent_id']][$raw_article_info['order']] = self::_constructMassArticle($raw_article_info, $raw_mass, $editor_info, $receiver_info);
            } else {
                $article_info_list[$raw_article_info['id']][$raw_article_info['order']] = self::_constructMassArticle($raw_article_info, $raw_mass, $editor_info, $receiver_info);
            }
        }

        // sort it
        foreach($article_info_list as &$article_info) {
            ksort($article_info, SORT_NUMERIC);
            $mass_list[$article_info[0]['mass_id']]['article_list'] = array_values($article_info);
        }

        return $mass_list;
    }

    public static function disableAll($official_account_id) {

        return static::updateAll(['status'=>self::STATUS_DELETED, 'updated_at'=>time()],
                                 sprintf('official_account_id = %d', $official_account_id));
    }

    // ------------ private function
    private static function _constructMassArticle($raw_article_info, $raw_mass, $editor_info, $receiver_info) {

        return [
            "id"=>$raw_article_info['id'],
            "cover_url"=>$raw_article_info['cover_url'],
            "title"=>$raw_article_info['title'],
            "read_num"=>$raw_article_info['int_page_read_count'],
            "type"=>$raw_article_info['type'],
            "fav_num"=>$raw_article_info['add_to_fav_count'],
            "editor_info"=>$editor_info,
            // "receiver"=>self::_get_receiver_info($raw_mass['user_tag_id']),
            "receiver"=>$receiver_info,
            "source_url"=>$raw_article_info['source_url'],
            "ad_source_url"=>$raw_article_info['ad_source_url'],
            "published_at"=>$raw_article_info['published_at'],
            "mass_id"=>$raw_mass['id'],
            "order"=>$raw_article_info['order']
        ];

    }

    private static function _constructArticleInfo($mass, $material_info, $parent_id=null) {

        $now = time();

        $article = new Article();

        $article->parent_id = $parent_id ? $parent_id : 0;
        $article->mass_id = $mass['id'];
        $article->official_account_id = $mass['official_account_id'];
        $article->is_multi = $material_info['is_multi'];
        $article->title = $material_info['title'];
        $article->type = $material_info['type'];
        $article->description = $material_info['description'];
        $article->content = $material_info['content'];
        $article->cover_url = $material_info['cover_url'];
        $article->source_url = $material_info['source_url'];
        $article->weixin_source_url = $material_info['weixin_source_url'];
        $article->ad_source_url = $material_info['ad_source_url'];
        $article->user_id = $mass['user_id'];
        $article->parent_id = $material_info['parent_id'];
        $article->author = $material_info['author'];

        # 冗余部分字段
        $article->material_id = $material_info['id'];
        $article->official_account_id = $material_info['official_account_id'];

        $article->is_legal = $material_info['is_legal'];
        $article->show_cover_pic = $material_info['show_cover_pic'];
        $article->order = $material_info['order'];

        $article->msg_id = $mass->msg_id;
        $article->msg_data_id = $mass['msg_data_id'] . '_' . $material_info['order'];

        $article->status = $material_info['status'];
        $article->published_at = $now;
        $article->created_at = $now;

        $is_saved = $article->save();

        if(!$is_saved) {
            return;
        }

        return $article;
    }

    private static function _constructArticleInfoArray($mass, $material_info, $parent_id=NULL) {

        $now = time();
        $infoArray = [];

        if(is_null($parent_id)) $parent_id = 0;

        $infoArray = [
            "parent_id" => $parent_id,
            "is_multi" => $material_info['is_multi'],
            "title" => $material_info['title'],
            "type" => $material_info['type'],
            "description" => $material_info['description'],
            "content" => $material_info['content'],
            "cover_url" => $material_info['cover_url'],
            "source_url" => self::_constructArticleContentSourceUrl($mass['id'], $material_info['order']),
            "weixin_source_url" => $material_info['weixin_source_url'],
            "ad_source_url" => $material_info['ad_source_url'],
            "user_id" => $mass['user_id'],
            "author" => $material_info['author'],

            "official_account_id" => $material_info['official_account_id'],
            "material_id" => $material_info['id'],

            "show_cover_pic" => $material_info['show_cover_pic'],
            "is_legal" => $material_info['is_legal'],

            "order" => $material_info['order'],

            "mass_id" => $mass['id'],
            "msg_data_id" => $mass['msg_data_id'] . '_' . $material_info['order'],

            "status"  => self::STATUS_ACTIVE,
            "published_at" => $now,
            "created_at" => $now
        ];

        return $infoArray;
    }


    // TIPS 这里的顺序很关键，需要跟_get_insert_value_list一致
    private static function _get_insert_params() {

        $insert_params = [
            "parent_id",
            "is_multi",
            "title",
            "type",
            "description",
            "content",
            "cover_url",
            "source_url",
            "weixin_source_url",
            "ad_source_url",
            "user_id",
            "author",

            "official_account_id",
            "material_id",

            "show_cover_pic",
            "is_legal",

            "order",

            "mass_id",
            "msg_data_id",

            "status",
            "published_at",
            "created_at"
        ];

        return $insert_params;
    }

    private static function _get_receiver_info($user_tag_id) {

        if($user_tag_id) {


        }

        return "全部用户";
    }

    /*
     * 构造阅读原文链接
     */
    private static function _constructArticleContentSourceUrl($mass_id, $material_order)
    {
        // $urlManager = Yii::$app->urlManager;

        // $host_info = $urlManager->getHostInfo();

        // $final_url = $urlManager->createAbsoluteUrl(['article/detail', "id"=>$material_id . '_' . $material_order]);

        $HOST_INFO = Yii::$app->params['HOST_INFO'];

        $scheme = $HOST_INFO['SCHEME'];
        $domain_info = $HOST_INFO['FRONTEND_DOMAIN_INFO'];

        $final_url = sprintf($scheme . '://' . $domain_info . '/' . 'article.html?' . 'id=%s', $mass_id . '_' . $material_order);

        return $final_url;
    }

    private static function _constructEditorInfo($user_id, $editor_info_list) {

        $editor_info = [
            "id"=>$user_id,
            "nickname"=>"未知"
        ];

        if(isset($editor_info_list[$user_id])) {

            $editor_info = [
                "id"=>$user_id,
                "nickname"=>$editor_info_list[$user_id]['nickname']
            ];
        }

        return $editor_info;
    }

    private static function _constructReceiverInfo($user_tag_id, $receiver_info_list) {

        $receiver_info = "未知";

        if(isset($receiver_info_list[$user_tag_id])) {
            $receiver_info = $receiver_info_list[$user_tag_id]['title'];
        }

        return $receiver_info;
    }
}