<?php

namespace common\models;

use Yii;
use yii\base\Model;

use common\helpers\Utils;
use common\models\Material;

/**
 * Material Form.
 */
class MaterialForm extends Model
{
    // db 字段
    public $parent_id;
    public $is_multi;
    public $title;
    public $type;
    public $description;
    public $content;
    public $cover_media_id;
    public $cover_url;
    public $source_url;
    public $weixin_source_url;
    public $ad_source_url;
    public $user_id;
    public $author;
    public $is_completed;
    public $is_synchronized;
    public $official_account_id;
    public $media_id;
    // public $original_id;
    public $created_from;
    // public $content_url;
    public $is_legal;
    public $show_cover_pic;
    public $order;

    public $status;
    public $published_at;
    public $created_at;
    public $updated_at;

    // post 字段
    public $mime_type;
    public $image_key;
    public $video_key;
    public $voice_key;
    public $cover_image_key;
    public $article_list;
    public $material_id;
    public $is_extra_modify;

    const SCENARIO_CREATE_ARTICLE = 'create/article';
    const SCENARIO_CREATE_ARTICLE_MULTI = 'create/multi_article';
    const SCENARIO_CREATE_IMAGE = 'create/image';
    const SCENARIO_CREATE_VIDEO = 'create/video';
    const SCENARIO_CREATE_VOICE = 'create/voice';
    const SCENARIO_CREATE_COVER_IMAGE = 'create/cover_image';
    const SCENARIO_CREATE_ARTICLE_IMAGE = 'create/article_image';

    const SCENARIO_MODIFY_ARTICLE = 'modify/article';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [["official_account_id"], "required"],

            [["type", "is_completed"], "required", "on"=>[self::SCENARIO_CREATE_ARTICLE_MULTI, self::SCENARIO_CREATE_IMAGE]],

            ["user_id", "integer"],

            ["is_completed", "integer"],

            ["is_synchronized", "integer"],
            ['is_synchronized', 'required', "on"=>[self::SCENARIO_MODIFY_ARTICLE, self::SCENARIO_CREATE_ARTICLE_MULTI]],

            ["media_id", "string"],

            ["official_account_id", "integer"],

            ["type", "integer"],

            ["status", "integer"],

            ["mime_type", "string"],
            ["mime_type", "required", "on"=>[self::SCENARIO_CREATE_IMAGE, self::SCENARIO_CREATE_VIDEO, self::SCENARIO_CREATE_VOICE, self::SCENARIO_CREATE_ARTICLE_IMAGE]],

            ["source_url", "string"],
            // ["source_url", "required", "on"=>[self::SCENARIO_CREATE_IMAGE, self::SCENARIO_CREATE_VIDEO, self::SCENARIO_CREATE_VOICE, self::SCENARIO_CREATE_ARTICLE_IMAGE]],

            ["weixin_source_url", "string"],
            // ["weixin_source_url", "required", "on"=>[self::SCENARIO_CREATE_IMAGE, self::SCENARIO_CREATE_VIDEO, self::SCENARIO_CREATE_VOICE, self::SCENARIO_CREATE_ARTICLE_IMAGE]],

            ["image_key", "string"],
            ["image_key", "required", "on"=>[self::SCENARIO_CREATE_IMAGE, self::SCENARIO_CREATE_ARTICLE_IMAGE]],

            ["video_key", "string"],
            ["video_key", "required", "on"=>self::SCENARIO_CREATE_VIDEO],

            ["voice_key", "string"],
            ["voice_key", "required", "on"=>self::SCENARIO_CREATE_VOICE],

            ["cover_image_key", "string"],
            ["cover_image_key", "required", "on"=>self::SCENARIO_CREATE_COVER_IMAGE],

            ["parent_id", "integer"],
            ["parent_id", "required", "on"=>self::SCENARIO_CREATE_ARTICLE_MULTI],

            ["created_from", "integer"],

            [["title", "description", "content"], "string"],

            [["show_cover_pic", "is_legal", "order"], "integer"],

            // [["title", "description", "content", "cover_media_id", "show_cover_pic"], "required", "on"=>[self::SCENARIO_CREATE_ARTICLE]],
            [["title", "content", "show_cover_pic"], "required", "on"=>[self::SCENARIO_CREATE_ARTICLE]],

            ['article_list', "checkArticleList"],
            ['article_list', 'required', "on"=>[self::SCENARIO_CREATE_ARTICLE_MULTI, self::SCENARIO_MODIFY_ARTICLE]],

            ['is_extra_modify', "integer"],
            ['is_extra_modify', 'required', "on"=>[self::SCENARIO_MODIFY_ARTICLE]],

            ['material_id', "integer"],
            ['material_id', 'required', "on"=>[self::SCENARIO_MODIFY_ARTICLE]],

            ["cover_url", "string"],

            ["ad_source_url", "url"],
        ];
    }

    /**
     * create material.
     *
     * @return material|null the saved model or null if saving fails
     */
    public function create()
    {
        if ($this->validate()) {

            try{
                return true;
            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to create material cos reason(%s).', $e));
            }
        }

        return;
    }

    public function delete($material_id)
    {
        return;
    }

    /*
     * 调整素材信息
     */
    public function modify()
    {
        if ($this->validate()) {

            try{
                //  TODO
            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to modify material cos reason:(%s)', $e));
            }
        }

        return;
    }

    public function storeImage() {

        if($this->validate()) {

            try{

                $material = new Material();

                $material->media_id = $this->media_id;
                $material->source_url = $this->source_url;
                $material->weixin_source_url = $this->weixin_source_url;
                $material->official_account_id = $this->official_account_id;
                $material->type = $this->type;

                $material->user_id = $this->user_id;
                $material->created_from = $this->created_from;
                $material->status = $this->status;
                $material->created_at = time();

                $material->save(false);

                return $material;

            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to store image cos reason:(%s)', $e));
            }

            return;
        }
    }

    public function storeArticle() {

        if($this->validate()) {

            try {

                $material = new Material();

                $material->official_account_id = $this->official_account_id;
                $material->type = $this->type;

                $material->title = $this->title;
                $material->cover_media_id = $this->cover_media_id;
                $material->cover_url = $this->cover_url;
                $material->show_cover_pic = $this->show_cover_pic;
                $material->author = $this->author;
                $material->content = $this->content;

                if($this->description) {
                    $material->description = $this->description;
                }

                $material->user_id = $this->user_id;
                $material->created_from = $this->created_from;
                $material->status = $this->status;
                $material->created_at = time();

                $material->save(false);

                return $material;

            } catch (\Exception $e)
            {
                Yii::error(sprintf('Fail to store image cos reason:(%s)', $e));
            }

            return;
        }
    }

    public function storeMultiArticle() {

        if($this->validate()) {

            $material_list = [];

            // 排序article_list
            $pre_order_list = [];
            foreach($this->article_list as &$article) {
                $article['content'] = Utils::clear_wechat_redirect_url($article['content']);
                $article['weixin_cover_url'] = Utils::restore_wechat_cover_url($article['cover_url']);
                $pre_order_list[$article['order']] = $article;
            }

            ksort($pre_order_list, SORT_NUMERIC);

            $parent_article = $this->_constructFakeParentArticle();
            $material_list[] = $parent_article;

            foreach($pre_order_list as $article) {
                $article['parent_id'] = $parent_article->id;
                $article['is_multi'] = 1;
                $article['is_synchronized'] = $this->is_synchronized;
                $material_list[] = $this->_fillArticleMaterial($article);
            }

            return $material_list;
        }

        return;
    }

    public function checkArticleList($attribute, $params) {

        // var_dump($params);exit;

        foreach($this->article_list as $article) {

            // check title
            if((is_null($article['title']) or empty($article['title'])) and !$article['title']) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article title can not be blank.');
            }

            // check cover media id
            if((is_null($article['cover_media_id'])) and !$article['cover_media_id']) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article cover media id can not be blank.');
            }

            // check cover url
            if(!isset($article['cover_url']) or !$article['cover_url']) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article cover url can not be blank.');
            }

            // TODO 针对单个素材的情况，考虑description的空白校验
            // // check description
            // if((is_null($article['description']) or empty($article['description'])) and !$article['description']) {
            //     // no real check at the moment to be sure that the error is triggered
            //     $this->addError($attribute, 'one of the article description can not be blank.');
            // }

            // check show pic
            if(!is_int($article['show_cover_pic'])) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article show_cover_pic can not be blank and should be int type.');
            }

            // check content
            if(is_null($article['content']) or empty($article['content']) or !$article['content']) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article content can not be blank.');
            }

            // check order
            if(!is_int($article['order'])) {
                // no real check at the moment to be sure that the error is triggered
                $this->addError($attribute, 'one of the article order can not be blank and should be int type.');
            }

        }
    }

    private function _fillArticleMaterial($article) {

        $material = new Material();

        $material->official_account_id = $this->official_account_id;
        $material->type = $this->type;

        $material->title = $article['title'];
        $material->cover_media_id = $article['cover_media_id'];
        // $material->cover_url = $article['cover_url'];
        // $material->weixin_cover_url = ''; # 创建素材，暂不考虑微信端的封面图
        $material->cover_url = '';
        $material->weixin_cover_url = $article['weixin_cover_url']; # 创建素材，暂不考虑微信端的封面图
        $material->show_cover_pic = $article['show_cover_pic'];
        $material->author = $article['author'];
        $material->content = $article['content'];
        $material->order = $article['order'];

        if($article['description']) {
            $material->description = $article['description'];
        }
        $material->is_multi = $article['is_multi'];

        if(array_key_exists('parent_id', $article)) {
            $material->parent_id = $article['parent_id'];
        }else {
            $material->parent_id = $this->parent_id;
        }

        if(array_key_exists('ad_source_url', $article)) {
            $material->ad_source_url = $article['ad_source_url'];
        }

        $material->user_id = $this->user_id;
        $material->created_from = $this->created_from;
        $material->status = $this->status;
        $material->created_at = time();

        $material->is_completed = $this->is_completed;

        $material->is_synchronized = $article['is_synchronized'];

        $material->save(false);

        # update source url
        // $material->source_url = $this->_constructContentSourceUrl($material->id);
        // $material->save(false);

        return $material;
    }

    private function _constructFakeParentArticle() {

        $material = new Material();

        $material->official_account_id = $this->official_account_id;
        $material->type = $this->type;
        $material->title = '';
        $material->cover_media_id = '';
        $material->cover_url = '';
        $material->show_cover_pic = 0;
        $material->author = '';
        $material->content = '';
        $material->order = -1;
        $material->description = '';
        $material->is_multi = 1;
        $material->parent_id = 0;
        $material->ad_source_url = '';
        $material->user_id = $this->user_id;
        $material->created_from = $this->created_from;
        $material->status = $this->status;
        $material->created_at = time();
        $material->is_completed = $this->is_completed;
        $material->is_synchronized = $this->is_synchronized;

        $material->save(false);

        return $material;
    }

}
