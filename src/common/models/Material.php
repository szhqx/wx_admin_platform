<?php

namespace common\models;

use common\helpers\WechatHelper;
use Yii;
use yii\db\ActiveRecord;

use common\helpers\Utils;
use common\helpers\OssUtils;

use common\models\ArticleImageMap;

use EasyWeChat\Core\Exceptions\HttpException;

use GuzzleHttp\Exception\ConnectException;

/**
 * Material model.
 *
 * TODO 补充
 * @property int $id
 * @property string $attention_link
 * @property int $official_account_id
 * @property int $created_at
 * @property int $updated_at
 */
class Material extends ActiveRecord
{
    // const MATERIAL_TYPE_ARTICLE = 1;
    const MATERIAL_TYPE_ARTICLE_MULTI = 1;
    const MATERIAL_TYPE_IMAGE = 2;
    const MATERIAL_TYPE_VOICE = 3;
    const MATERIAL_TYPE_VIDEO = 4;
    const MATERIAL_TYPE_COVER_IMAGE = 5;
    const MATERIAL_TYPE_ARTICLE_IMAGE = 6;
    const MATERIAL_TYPE_TEMPLATE = 7;

    const CREATED_FROM_WECHAT = 0;
    const CREATED_FROM_SERVER = 1;

    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    public static function findById($oId, $checkStatus=true) {

        $query = static::find()->where(['id'=>$oId]);

        if($checkStatus) {
            $query->andWhere(['status'=>$checkStatus]);
        }

        return $query->one();
    }

    public static function findByIdList($oidList, $checkStatus=true) {

        $query = static::find()->where(['in', 'id', $oidList]);

        if($checkStatus) {
            $query->andWhere(['status'=>$checkStatus]);
        }

        return $query->all();
    }

    // // soft delete
    // public function delete() {

    //     $this->status = 0;
    //     $this->updated_at = time();

    //     return $this->save(false);
    // }

    public static function getList($params, $page, $num, $checkStatus=TRUE)
    {
        $query = static::find();

        if($checkStatus) {
            $query->andWhere(['status'=>self::STATUS_ACTIVE]);
        }

        foreach($params as $key=>$value) {
            if($key == 'title'){
                $query->andWhere(['and','like',$value]);
            }else{
                $query->andWhere([$key=>$value]);
            }

        }

        $query->orderBy(["created_at" => SORT_DESC]);

        $start = max(($page-1)*$num, 0);

        $query->limit($num)->offset($start);

        return $query->all();
     }

    public static function getTotalCount($params, $checkStatus=TRUE)
    {

        $query = static::find();

        if($checkStatus) {
            $query->where(['status'=>self::STATUS_ACTIVE]);
        }

        foreach($params as $key=>$value) {
            $query->andWhere([$key=>$value]);
        }

        $total = $query->count();

        return (int)$total;
    }

    public static function getChildArticle($parent_id) {

        $query = static::find();

        $query->where(['status'=>1]);

        $query->where(['parent_id'=>$parent_id]);

        $query->orderBy(["order" => SORT_ASC]);

        return $query->all();
    }

    public static function disableAllChild($parent_id) {

        return static::updateAll(['status'=>self::STATUS_DEACTIVE, 'updated_at'=>time()],
                                 sprintf('parent_id = %d', $parent_id));
    }

    public static function storeWechatArticle($remoteArtile, $official_account_id, $user_id, $media_id=NULL,
                                              $parent_id=NULL, $cover_url="", $order=NULL, $created_at=NULL) {

        $material = new Material();

        if(!is_null($media_id))
            $material->media_id = $media_id;

        if(!is_null($parent_id))
            $material->parent_id = $parent_id;

        $material->official_account_id = $official_account_id;
        $material->type = self::MATERIAL_TYPE_ARTICLE_MULTI;

        $material->title = $remoteArtile['title'];
        $material->cover_media_id = $remoteArtile['thumb_media_id'];
        $material->cover_url = $cover_url;
        $material->weixin_cover_url = $remoteArtile['thumb_url'];
        $material->show_cover_pic = $remoteArtile['show_cover_pic'];
        $material->author = $remoteArtile['author'];
        $material->content = $remoteArtile['content'];
        $material->description = $remoteArtile['digest'];
        $material->ad_source_url = $remoteArtile['content_source_url'];
        $material->weixin_source_url = $remoteArtile['url'];

        $material->user_id = $user_id;
        $material->created_from = self::CREATED_FROM_WECHAT;
        $material->status = self::STATUS_ACTIVE;

        if(!$created_at) {
            $created_at = time();
        }
        $material->updated_at = $material->created_at = $created_at;

        $material->order = $order;

        $is_saved = $material->save(false);

        if(!$is_saved) {
            return;
        }

        return $material;
    }

    public static function getChildArticleByParentIdList($material_id_list, $checkStatus=true) {

        $query = static::find()->where(['in', 'parent_id', $material_id_list]);

        if($checkStatus) {
            $query->andWhere(["status"=>self::STATUS_ACTIVE]);
        }

        $material_info_list = [];

        $raw_material_list =  $query->all();

        foreach($raw_material_list as $raw_material) {
            $material_info_list[$raw_material['parent_id']][$raw_material['order']] = $raw_material;
        }

        foreach($material_info_list as $material_info) {
            ksort($material_info, SORT_NUMERIC);
        }

        return $material_info_list;
    }

    public static function constructImageInfo($material_info) {

        $final_material_info = [
            "id"=>$material_info['id'],
            "media_id"=>$material_info['media_id'],
            "source_url"=>$material_info['source_url'],
            "weixin_source_url"=>$material_info['weixin_source_url'],
            "is_completed"=>$material_info['is_completed'],
            "type"=>$material_info['type'],
            "create_time"=>$material_info['created_at']
        ];

        return $final_material_info;
    }

    public static function constructUeditorImageInfo($material_info) {

        $final_material_info = [
            "url"=>$material_info['source_url'],
            "mtime"=>$material_info['created_at'],
            "media_id"=>$material_info['media_id'],
            "weixin_url"=>$material_info['weixin_source_url']
        ];

        return $final_material_info;
    }

    // TODO 待删除
    public static function constructSourceUrl($media_id, $company_id, $official_account_id, $wechat=null, $weixin_img_url=NULL, $user_id=NULL) {

        try {

            if(!$media_id) return '';

            if(!$user_id)
                $user_id = Yii::$app->user->identity->id;

            // 检查本地是否有对应的素材记录
            $material = self::find()->where(['media_id'=>$media_id])->andWhere(['type'=>Material::MATERIAL_TYPE_ARTICLE_IMAGE])->one();
            if($material and $material['source_url']) {
                return $material['source_url'];
            }

            $object_key = OssUtils::getObjectKey();
            $upload_key = OssUtils::getUploadkey($object_key, $company_id, $official_account_id);

            $local_img_path = '';

            // prefer the img url way
            if(!$weixin_img_url) {

                $stream = $wechat->material->get($media_id);

                $stream->rewind(); // Seek to the beginning
                $content = $stream->getContents(); // returns all the contents

                // check content type
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $ext = explode('/', $finfo->buffer($content))[1];
                $upload_key = $upload_key . '.' . $ext;
                // save to local
//                $tempname = tempnam('D:/phpStudy/WWW/wx_admin_platform/src/runtime/log', '');
                $tempname = tempnam(Yii::$app->params['TEMPNAME'], '');
                $local_img_path = $tempname . '.' . $ext;
                $is_save_local = file_put_contents($local_img_path, $content, LOCK_EX);
//                var_dump($is_save_local);exit;
                if(!$is_save_local) {
//                    var_dump($tempname);
                    throw new \Exception(sprintf("Fail to save local img with params:official_account_id(%s)/local_img_path(%s)/img_url(%s)",
                                                 $official_account_id, $local_img_path, $weixin_img_url));
                }

                OssUtils::uploadSourceToAliyun($upload_key, $content);

                $image_url = OssUtils::constructAliSourceUrl($upload_key);

            } else {
                $is_save_local = true;
                $image_info = self::_get_source_url_by_weixin_source_url($official_account_id, $company_id, $weixin_img_url, $is_save_local);
                $local_img_path = $image_info['local_img_path'];
                $image_url = $image_info['image_url'];
            }

            // upload to wechat
            $wechat_content = $wechat->material->uploadImage($local_img_path);

            // sync to wechat
            $is_saved = self::_save_cover_image_material($image_url, $official_account_id, $company_id, $wechat_content,$user_id);

            if(!$is_saved) {
                throw new \Exception(sprintf("Fail to save cover img material with params:official_account_id(%s)/company_id(%s)/img_url(%s)/wechat_content(%s)", $official_account_id, $company_id, $image_url, json_encode($wechat_content)));
            }

            return [
                "image_url"=>$image_url,
                "media_id"=>$wechat_content['media_id']
            ];

        } catch(\Exception $e) {

            $err_msg = sprintf('Fail to get image from wechat with media_id(%s)/official_account_id(%d)/compay_id(%d) weixin_img_url(%s) cos reason:%s', $media_id, $official_account_id, $company_id, $weixin_img_url,$e->getMessage());
            Yii::error($err_msg);
        }

        return [];
    }

    public static function downloadWeixinSourceImg($media_id, $weixin_source_url, $company_id, $official_account_id, $wechat=null) {

        try {

            if(!$media_id) return '';

            // 检查本地是否有对应的素材记录
            $material = self::find()->where(['media_id'=>$media_id])->andFilterWhere(['!=', 'source_url', ''])->one();
            if($material and $material['source_url']) {
                return $material['source_url'];
            }

            if(!$weixin_source_url) {

                $object_key = OssUtils::getObjectKey();
                $upload_key = OssUtils::getUploadkey($object_key, $company_id, $official_account_id);

                $stream = $wechat->material->get($media_id);

                $stream->rewind(); // Seek to the beginning
                $content = $stream->getContents(); // returns all the contents

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $ext = explode('/', $finfo->buffer($content))[1];

                // check content type
                // $object_key = $object_key . '.' . $ext;
                $upload_key = $upload_key . '.' . $ext;

                OssUtils::uploadSourceToAliyun($upload_key, $content);

                $image_url = OssUtils::constructAliSourceUrl($upload_key);

                return $image_url;

            } else {

                $img_info = self::_get_source_url_by_weixin_source_url($official_account_id, $company_id, $weixin_source_url);

                return $img_info['image_url'];

            }

        } catch (HttpException $e) {

            // media id 不存在 或者其他wechat sdk自己的异常
            $err_msg = sprintf('Try a new way to get material cos failing to get image from wechat with media_id(%s)/official_account_id(%d)/compay_id(%d) cos reason:%s', $media_id, $official_account_id, $company_id, $e);
            Yii::warning($err_msg);

            try {
                $img_info = self::_get_source_url_by_weixin_source_url($official_account_id, $company_id, $weixin_source_url);
                return $img_info['image_url'];
            } catch (\Exception $e) {
                $err_msg = sprintf('Fail to download image from wechat with weixin_source_url(%s) cos reason:%s', $weixin_source_url, $e);
                Yii::error($err_msg);
            }

        } catch (ConnectException $e) {

            // http 超时了
            $err_msg = sprintf('Connection timeout. Try a new way to get material cos failing to get image from wechat with media_id(%s)/official_account_id(%d)/compay_id(%d) cos reason:%s', $media_id, $official_account_id, $company_id, $e);
            Yii::warning($err_msg);

            try {
                $img_info = self::_get_source_url_by_weixin_source_url($official_account_id, $company_id, $weixin_source_url);
                return $img_info['image_url'];
            } catch (\Exception $e) {
                $err_msg = sprintf('Fail to download image from wechat with weixin_source_url(%s) cos reason:%s', $weixin_source_url, $e);
                Yii::error($err_msg);
            }

        }
        catch(\Exception $e) {

            $err_msg = sprintf('Fail to get image from wechat with media_id(%s)/official_account_id(%d)/compay_id(%d) cos reason:%s', $media_id, $official_account_id, $company_id, $e);
            Yii::error($err_msg);
        }

        return '';
    }

    public static function sync_article_image($image_list, $wechat) {

        $image_map = [];

        // check if has downloaded
        $map_result = ArticleImageMap::getImageMap($image_list);

        $upload_image_list = array_diff($image_list, $map_result['has_downloaded']);

        // upload those that have not downloaded
        $upload_result = self::_upload_article_image_to_wechat($upload_image_list, $wechat);

        $image_map = array_merge($upload_result, $map_result['image_map']);

        return $image_map;
    }

    public static function batchInsertChildArticle($parent_article, $article_list) {

        // warning: 调用前，请确保文章内容、cover_url这些，没有遗留调整过的痕迹

        $is_multi = 1;
        $rows = [];
        $official_account_id = $parent_article['official_account_id'];
        $type = $parent_article['type'];
        $user_id = $parent_article['user_id'];
        $created_from = $parent_article['created_from'];
        $status = $parent_article['status'];
        $is_completed = $parent_article['is_completed'];
        $is_synchronized = $parent_article['is_synchronized'];

        foreach($article_list as $article) {

            $rows[] = [
                $official_account_id,
                $type,
                $article['title'],
                $article['cover_media_id'],
                $article['cover_url'],
                // $weixin_cover_url,
                $article['weixin_cover_url'],
                $article['show_cover_pic'],
                $article['author'],
                $article['content'],
                $article['order'],
                $article['description'],
                $is_multi,
                $parent_article['id'],
                $article['ad_source_url'],
                // $article['user_id'],
                $user_id,
                // $article['created_from'],
                $created_from,
                // $article['status'],
                // $article['is_completed'],
                // $article['is_synchronized']
                $status,
                $is_completed,
                $is_synchronized
            ];
        }

        $insert_column_list = [
            'official_account_id',
            'type',
            'title',
            'cover_media_id',
            'cover_url',
            'weixin_cover_url',
            'show_cover_pic',
            'author',
            'content',
            'order',
            'description',
            'is_multi',
            'parent_id',
            'ad_source_url',
            'user_id',
            'created_from',
            'status',
            'is_completed',
            'is_synchronized'
        ];

        $inserted = Yii::$app->db->createCommand()
                                 ->batchInsert(Material::tableName(), $insert_column_list, $rows)
                                 ->execute();

        return $inserted;
    }

    public static function batchUpdateArticleMaterial($remote_article_list, $wechat, $media_id, &$extra_msg_list){
        if(is_null($extra_msg_list)) {
            $extra_msg_list = [];
        }
        if(!$media_id){
            Yii::error(sprintf("media_id is null"),__METHOD__);
            return false;
        }
        try{
            foreach ($remote_article_list as $article_list){
                $result = $wechat->material->updateArticle($media_id,[
                    'title'  =>   $article_list['title'],
                    'thumb_media_id'  =>   $article_list['cover_media_id'],
                    'author'  =>   $article_list['author'],
                    'digest'  =>   $article_list['description'],
                    'show_cover_pic'  =>   $article_list['show_cover_pic'],
                    'content'  =>   $article_list['content'],
                    'content_source_url'  =>   $article_list['ad_source_url'] ? $article_list['ad_source_url'] : "",
                ],$article_list['order']);

                Yii::info(sprintf("result title(%s) result(%s) media_id(%s)",$article_list['title'],json_encode($result->toArray()),$media_id),__METHOD__);

            }
        }catch (\Exception $e){
            Yii::error(sprintf("failed to update material cos reason (%s)",$e->getMessage()),__METHOD__);
            return false;
        }
        return true;


    }

    public static function batchUpdateArticleMaterial2($remote_article_list, $wechat, $media_id, &$extra_msg_list) {

        if(is_null($extra_msg_list)) {
            $extra_msg_list = [];
        }

        $fail_times = 0;
        $upload_article = [];

        $acces_token = $wechat->access_token->getToken();
        $UPDATE_URL = sprintf("https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=%s", $acces_token);

        $task_id_list = [];
        foreach($remote_article_list as $index=>$article) {
            $task_id_list[$index] = $article;
        }

        // upload image to wechat as a article material
        try {

            $curl_arr = array();

            $master = curl_multi_init();

            foreach($task_id_list as $id=>$article)
            {
                Yii::info($id);
                Yii::info($article['title']);
                Yii::info($article['cover_media_id']);

                // TODO 统一迁移到一个构造函数里
                $post = [
                    "media_id"=>$media_id,
                    "index"=>$article['order'],
                    "articles"=> [
                        "title"=>$article['title'],
                        "thumb_media_id"=>$article['cover_media_id'],
                        "author"=>$article['author'],
                        "digest"=>$article['description'],
                        "show_cover_pic"=>$article['show_cover_pic'],
                        "content"=>$article['content'],
                        "content_source_url"=> $article['ad_source_url'] ? $article['ad_source_url'] : ""
                    ]
                ];

                $ch = $curl_arr[$id] = curl_init($UPDATE_URL);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true); // enable posting
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_UNESCAPED_UNICODE));
                curl_multi_add_handle($master, $ch);
            }

            do {
                curl_multi_exec($master, $running);
            } while($running > 0);

            foreach($task_id_list as $id=>$article)
            {
                $response = curl_multi_getcontent($curl_arr[$id]);

                // close the connection, release resources used
                $ch = $curl_arr[$id];
                if (curl_errno($ch)) {

                    $extra_msg_list[] = ["order"=>$article['order'], "msg"=>"保存失败"];

                    Yii::error("Error: " . curl_error($ch));
                    continue;
                }

                curl_multi_remove_handle($master, $ch);

                try {
                    $result = json_decode($response);
                    if($result->errcode != 0) {
                        $fail_times += 1;
                        throw new \Exception('');
                    }

                    $debug_msg = sprintf("success to sync article(%s) to wechat with response(%s).\n", $article['title'], $response);
                    Yii::info($debug_msg);

                } catch (\Exception $e) {

                    $reason = "未知";

                    if($result and $result->errcode == 40007) {
                        $reason = "封面有误，请重新上传";
                    }

                    $extra_msg_list[] = ["order"=>$article['order'], "msg"=>sprintf("保存失败，原因：%s", $reason)];

                    $err_msg = sprintf("Fail to sync article(%s) to wechat with response(%s).\n", $article['title'], $response);
                    Yii::error($err_msg);
                    continue;
                }
            }

            curl_multi_close($master);

        } catch(\Exception $e) {
            $err_msg = sprintf("Fail to sync the list of article to wechat cos(%s).\n", $e->getMessage());
            Yii::error($err_msg);
            return false;
        }

        if($fail_times == count($remote_article_list)) {
            return false;
        }

        $should_count = count($remote_article_list);
        Yii::info(sprintf('success to update remote article with should conut(%s) and final count(%s)', $should_count, $should_count - $fail_times ));

        return true;
    }

    public static function constructRemoteArticle($raw_article, $wechat_img_map) {

        // construct article
        $article = [
            "title"=>$raw_article['title'],
            "thumb_media_id"=>$raw_article['cover_media_id'],
            "author"=>$raw_article['author'],
            "digest"=>$raw_article['description'],
            "show_cover_pic"=>$raw_article['show_cover_pic'],
            "content"=>self::replaceArticleImageContent($raw_article['content'], $wechat_img_map),
            "content_source_url"=> $raw_article['ad_source_url'] ? $raw_article['ad_source_url'] : ""
        ];

        return $article;
    }

    /*
     * 过滤文章素材的图片地址，调整成我们需要的
     */
    public static function replaceArticleImageContent($raw_content, $wechat_img_map) {

        $img_src_list = [];
        $img_wechat_link_list = [];
        foreach($wechat_img_map as $source_url=>$wechat_source_url) {
            $img_src_list[] = $source_url;
            $img_wechat_link_list[] = $wechat_source_url;
        }

        $final_content = str_replace($img_src_list, $img_wechat_link_list, $raw_content);

        return $final_content;
    }

    public static function makeWechatImageMap($local_article_list) {

        // batch construct the images of all remote article content
        $non_wechat_img_link_list = [];

        foreach($local_article_list as $article) {
            $non_wechat_img_link_list[] = self::_grap_all_non_wechat_img_link($article['content']);
        }

        $non_wechat_img_link_list = array_reduce($non_wechat_img_link_list, function ($a, $b) {
            return array_merge($a, (array) $b);
        }, []);

        $non_wechat_img_link_list = array_unique($non_wechat_img_link_list);

        $wechat_img_map = self::_map_non_wechat_img($non_wechat_img_link_list);

        return $wechat_img_map;
    }

    public static function constructFakeParentArticle(
        $official_account_id, $created_at, $media_id=NULL,
        $type=self::MATERIAL_TYPE_ARTICLE_MULTI,
        $user_id=0, $created_from=self::CREATED_FROM_WECHAT,
        $is_completed=1, $is_synchronized=1, $status=1
    ) {

        $material = new Material();

        $material->official_account_id = $official_account_id;
        $material->type = $type;
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
        $material->user_id = $user_id;
        $material->created_from = $created_from;
        $material->status = $status;
        $material->created_at = $created_at;
        $material->is_completed = $is_completed;
        $material->is_synchronized = $is_synchronized;

        if($media_id) {
            $material->media_id = $media_id;
        }

        $material->save(false);

        return $material;
    }

    public static function wechatItemToLocalArticleList($remote_item) {

        $news_item = $remote_item['content']['news_item'];

        $fake_local_article_list = [];
        $order = 0;

        foreach($news_item as $remote_article) {

            $fake_local_article_list[] = [
                "title"=>$remote_article['title'],
                "description"=>$remote_article['digest'],
                "content"=>$remote_article['content'],
                "cover_media_id"=>$remote_article['thumb_media_id'],
                "cover_url"=>'',
                "weixin_cover_url"=>$remote_article['thumb_url'],
                "show_cover_pic"=>$remote_article['show_cover_pic'],
                "author"=>$remote_article['author'],
                "order"=>$order,
                "ad_source_url"=>$remote_article['content_source_url']
            ];

            $order += 1;
        }

        return $fake_local_article_list;
    }

    public static function updateLocalArticleMaterial($parent_article, $article_list, $update_time) {

        // find out all the child article
        self::_updateArticleLocal($parent_article,$article_list);

//        // 全量删除本地的数据
//        $updated_num = self::deleteAllChild(["parent_id"=>$parent_article->id]);
//        Yii::info(sprintf('delete parent article(%s)\'s %s child articles.', $parent_article->id, $updated_num));
//
//        // batch insert the child articles
//        $is_inserted = self::batchInsertChildArticle($parent_article, $article_list);
//
//        if(!$is_inserted) {
//            Yii::error(sprintf('Fail to batch insert child articles for material(%s).', $parent_article->id));
//            return false;
//        }

        $parent_article->updated_at = $update_time;
        $parent_article->save(false);

        return $parent_article->id;
    }

    public static function deleteAllChild($params) {
        return self::deleteAll($params);
    }

    public static function updateChildArticle($update_article){

        $model = new Material();

        $is_updated = true;

        foreach ($update_article as $article){
            $article_model = $model->findById($article['id']);
            $article_model -> title = $article['title'];
//            $article_model -> type = $article['type'];
            $article_model -> cover_media_id = $article['cover_media_id'];
            $article_model -> cover_url = $article['cover_url'];
//            $article_model -> weixin_cover_url = $article['weixin_cover_url'];
            $article_model -> show_cover_pic = $article['show_cover_pic'];
            $article_model -> author = $article['author'];
            $article_model -> content = $article['content'];
            $article_model -> order = $article['order'];
            $article_model -> description = $article['description'];
            $article_model -> ad_source_url = $article['ad_source_url'];
            $article_model -> is_completed = $article['is_completed'];
            $article_model -> is_synchronized = $article['is_synchronized'];

            $is_saved = $article_model ->save();

            if(!$is_saved) {
                $is_updated = false;
            }
        }

        return $is_updated;
    }

    // ----------- private functions --------------
    // TODO 如果后续需要，提供curl的并发版本
    private static function _get_source_url_by_weixin_source_url($official_account_id, $company_id, $weixin_source_url, $is_save_local=false) {

        $local_img_path = '';
        $object_key = OssUtils::getObjectKey();
        $upload_key = OssUtils::getUploadkey($object_key, $company_id, $official_account_id);

        $ch = curl_init($weixin_source_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds

        $content = curl_exec($ch);

        curl_close($ch);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $ext = explode('/', $finfo->buffer($content))[1];

        if($is_save_local) {
            // save to local
            $tempname = tempnam('/mnt/tmp', '');
            $local_img_path = $tempname . '.' . $ext;
            $is_save_local = file_put_contents($local_img_path, $content, LOCK_EX);
            if(!$is_save_local) {
                throw new \Exception(sprintf("Fail to save local img with params:official_account_id(%s)/company_id(%s)/weixin_source_urk(%s)", $official_account_id, $company_id, $weixin_source_url));
            }
        }

        $upload_key = $upload_key . '.' . $ext;
        OssUtils::uploadSourceToAliyun($upload_key, $content);
        $image_url = OssUtils::constructAliSourceUrl($upload_key);

        $img_info = [
            "image_url"=>$image_url,
            "local_img_path"=>$local_img_path
        ];

        return $img_info;
    }

    private static function _save_cover_image_material($image_url, $official_account_id, $company_id, $wechat_content, $user_id) {

        $model = new MaterialForm();

        $now = time();

        $model->official_account_id = $official_account_id;
        // $model->user_id = Yii::$app->user->identity->id;
        $model->user_id = $user_id;
        $model->status = Material::STATUS_ACTIVE;
        $model->created_from = Material::CREATED_FROM_WECHAT;
        $model->media_id = $wechat_content['media_id'];
        $model->source_url = $image_url;
        $model->weixin_source_url = $wechat_content['url'];
        $model->type = Material::MATERIAL_TYPE_IMAGE;
        $model->created_at = $now;

        $image = $model->storeImage();

        if(!$image) {
            Yii::error(sprintf('Fail to create image material cos reason:(%s)', json_encode($model->errors)));
            return false;
        }

        return true;
    }

    private function _upload_article_image_to_wechat($upload_image_list, $wechat) {

        $upload_image_map = [];

        foreach($upload_image_list as $url) {
            $upload_image_map[$url] = "";
        }

        $acces_token = $wechat->access_token->getToken();
        $UPLOAD_URL = sprintf("https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=%s", $acces_token);

        // download image to local
        $download_image_map = self::_download_image_list($upload_image_list);

        if (count(array_unique($download_image_map)) === 1 && end($download_image_map) === '') {
            // return false;
            return $upload_image_map;
        }

        $task_id_list = [];
        foreach($upload_image_list as $index=>$url) {
            $task_id_list[$index] = $url;
        }

        // upload image to wechat as a article material
        try {

            $curl_arr = array();

            $master = curl_multi_init();

            foreach($task_id_list as $id=>$url)
            {
                $local_image_path = $download_image_map[$url];

                if(!$local_image_path) {
                    continue;
                }

                $post = [
                    "media"=>new \CURLFile($local_image_path)
                ];
                $ch = $curl_arr[$id] = curl_init($UPLOAD_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true); // enable posting
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                curl_multi_add_handle($master, $ch);

            }

            do {
                curl_multi_exec($master, $running);
            } while($running > 0);

            foreach($task_id_list as $id=>$url)
            {

                if(!array_key_exists($id, $curl_arr)) {
                    continue;
                }

                $response = curl_multi_getcontent($curl_arr[$id]);

                // close the connection, release resources used
                $ch = $curl_arr[$id];
                if (curl_errno($ch)) {
                    Yii::error("Error: " . curl_error($ch));
                    continue;
                }

                curl_multi_remove_handle($master, $ch);

                try {

                    $result = json_decode($response);
                    $upload_image_map[$url] = $result->url;

                } catch (\Exception $e) {
                    $err_msg = sprintf("Fail to upload wechat article image(%s) to wechat with response(%s) with reason(%s).\n", $url, $response, $e->getMessage());
                    Yii::error($err_msg);
                    continue;
                }

            }

            curl_multi_close($master);

            // back up url
            $rows = [];
            foreach($upload_image_map as $source_url=>$wechat_source_url) {
                if($wechat_source_url) {
                    $rows[] = [$source_url, $wechat_source_url];
                }
            }

            Yii::$app->db->createCommand()
                         ->batchInsert(ArticleImageMap::tableName(), ['source_url','wechat_source_url'], $rows)
                         ->execute();


        } catch(\Exception $e) {
            $err_msg = sprintf("Fail to sync article image list(%s) to wechat cos(%s).\n", json_encode($task_id_list), $e);
            Yii::error($err_msg);
        }

        return $upload_image_map;
    }

    private function _download_image_list($upload_image_list) {

        $download_image_map = [];

        foreach($upload_image_list as $upload_image) {
            $download_image_map[$upload_image] = '';
        }

        try {

            $curl_arr = array();

            $master = curl_multi_init();

            $task_id_list = [];
            foreach($upload_image_list as $index=>$url) {
                $task_id_list[$index] = $url;
            }

            foreach($task_id_list as $id=>$url)
            {
                // set post fields
                $ch = $curl_arr[$id] = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
                curl_multi_add_handle($master, $ch);
            }

            do {
                curl_multi_exec($master, $running);
            } while($running > 0);

            foreach($task_id_list as $id=>$url)
            {
                $response = curl_multi_getcontent($curl_arr[$id]);

                // close the connection, release resources used
                $ch = $curl_arr[$id];
                if (curl_errno($ch)) {
                    Yii::error(sprintf("Error for downloading image(%s) for reason:%s ", $url, curl_error($ch)));
                    continue;
                }

                curl_multi_remove_handle($master, $ch);

                try {
                    // save to local path
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $ext = explode('/', $finfo->buffer($response))[1];

                    // save to local
                    $tempname = tempnam('/mnt/tmp', '');
                    $local_img_path = $tempname . '.' . $ext;
                    $is_save_local = file_put_contents($local_img_path, $response, LOCK_EX);
                    if(!$is_save_local) {
                        throw new \Exception(sprintf("Fail to save local img with params:source_url(%s)", $url));
                    }

                    $download_image_map[$url] = $local_img_path;

                } catch(\Exception $e) {
                    $err_msg = sprintf("Fail to download image(%s) with response(%s) cos error(%s).\n", $url, $response, $e);
                    Yii::error($err_msg);
                }
            }

            curl_multi_close($master);

        } catch(\Exception $e) {
            $err_msg = sprintf("Fail to download image list(%s) for saving to local for reason(%s).\n", json_encode($upload_image_list), $e);
            Yii::error($err_msg);
        }

        return $download_image_map;
    }

    private function _grap_all_wechat_img_link($raw_content) {

        $final_img_list = [];

        $img_list = Utils::imgLinkExtractor($raw_content);

        $pattern = \Yii::$app->params['WECHAT_IMG_DOMAIN_PATTERN'];

        foreach($img_list as $img) {

            preg_match($pattern, $img, $matches);

            if($matches) {
                $img_list[] = $img;
            }
        }

        return $final_img_list;
    }

    private function _grap_all_non_wechat_img_link($raw_content) {

        $final_img_list = [];

        $pattern = \Yii::$app->params['WECHAT_IMG_DOMAIN_PATTERN'];

        $img_list = Utils::imgLinkExtractor($raw_content);

        foreach($img_list as $img) {

            preg_match($pattern, $img, $matches);

            if(!$matches) {
                $final_img_list[] = $img;
            }
        }

        return $final_img_list;
    }

    private function _map_non_wechat_img($non_wechat_img_link_list) {

        $material_list = ArticleImageMap::find()->where(['in', 'source_url', $non_wechat_img_link_list])->all();

        $img_map = [];
        foreach($material_list as $material) {
            $img_map[$material['source_url']] = $material['wechat_source_url'];
        }

        return $img_map;
    }

    public static function _getFinalListByTitle($params,$page,$num) {

        $start = max(($page-1)*$num, 0);

        $parent_ids = array_unique(array_column(Material::find()->select(['parent_id'])
                     ->where(['is_synchronized'=>$params['is_synchronized']])
                     ->orderBy(["created_at" => SORT_DESC])
                     ->andWhere(['type'=>$params['type']])
                     ->andWhere(['status'=>self::STATUS_ACTIVE])
                     ->andWhere(['like','title',$params['title']])->asArray()->all(),'parent_id'));
        $parent_ids_list = array_slice($parent_ids,$start,$num);

        $total = count($parent_ids);

        $ids = array_unique(array_column(static::find()->select(['id'])->where(['in','parent_id',$parent_ids_list])->asArray()->all(),'id'));

        $id_list = array_merge($parent_ids_list,$ids);


        $total_list = static::find()->select([
            'id','official_account_id','media_id','original_id','parent_id','type','is_multi',
            'title','description','show_cover_pic','author','order','source_url','source_url','ad_source_url',
            'is_completed','is_synchronized','created_at','weixin_cover_url','cover_url','cover_media_id'])
            ->where(['in','id',$id_list])->asArray()->all();

        $p = [];
        foreach ($total_list as $material_info_p){
            if($material_info_p['parent_id'] == 0){
                $s = [];
                foreach ($total_list as $material_info_s){
                    if($material_info_s['weixin_cover_url']) {
                        $cover_url = Utils::prepare_cover_url($material_info_s['weixin_cover_url']);
                    } else {
                        $cover_url = $material_info_s['cover_url'];
                    }
                    if($material_info_p['id'] == $material_info_s['parent_id']){
                        $s[] = [
                            "id"=>$material_info_s['id'],
                            "title"=>$material_info_s['title'],
                            "description"=>$material_info_s['description'],
                            "cover_media_id"=>$material_info_s['cover_media_id'],
                            "cover_url"=>$cover_url,
                            "show_cover_pic"=>$material_info_s['show_cover_pic'],
                            "author"=>$material_info_s['author'],
                            "order"=>$material_info_s['order'],
                            "source_url"=>$material_info_s['source_url'],
                            "ad_source_url"=>$material_info_s['ad_source_url'],
                            "type"=>$material_info_s['type'],
                            "is_completed"=>$material_info_s['is_completed'],
                            "is_synchronized"=>$material_info_s['is_synchronized']
                        ];
                    }
                }
                foreach ($s as $key => $value) {
                    $order[$key] = $value['order'];
                }
                array_multisort($order,$s);
                $p[] = [
                    "id"=>$material_info_p['id'],
                    "media_id"=>$material_info_p['media_id'],
                    "is_completed"=>$material_info_p['is_completed'],
                    "is_synchronized"=>$material_info_p['is_synchronized'],
                    "create_time"=>$material_info_p['created_at'],
                    "item_list"=>$s
                ];
            }

        }

        $final_data = [
            "material_list" => $p,
            "page_num" => ceil($total/$num)
        ];

        return $final_data;

    }

    /*
     * 获取素材的广告信息
     * */
    public static function _getAdInfo($id){
        $ad_info = AdvertisementOfficial::find()->where(['material_id'=>$id])->asArray()->all();
        if($ad_info){
            $data = [];
            foreach($ad_info as $k=>$v){

                $order_info = Advertisement::find()->where(['id'=>$v['ad_id']])->asArray()->one();
                $customer_info = Customer::findById($order_info['customer_id']);
                $type_info = AdvertisementType::getTypeInfo($v['type_id']);

                if(!$customer_info) {
                    $customer_info = [
                        "customer"=>"",
                        "tel"=>""
                    ];
                }

                if($order_info){
                    $data[] =  [
                        "id"                => $v['id'],
                        "customer"          => $customer_info['customer'], //客户
                        "tel"               => $customer_info['tel'],
                        "order_amount"      => $order_info['order_amount'],
                        "ad_position"      => $v['ad_position'],
                        "retain_day"      => $v['retain_day'],
                        "product_type"      => $type_info,
                        "send_date"      => $v['send_date'],
                    ];
                }
            }
            return $data;
        }
        return [];
    }

    public static function _deleteMaterial($material_id){

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            static::deleteAll(["id"=>$material_id,"parent_id"=>$material_id]);
            $transaction->commit();

        } catch (\Exception $e)
        {
            $transaction->rollback();
            Yii::error(sprintf('Fail to delete material cos reason:(%s)', $e));
            return false;
        }

        return true;
    }

    public static function _checkIfOfficialAccountRight($official_account_id, $user_id=NULL) {

        $official_account_info = OfficialAccount::findById($official_account_id);
        if(!$official_account_info) {
            return false;
        }

        // check if user has the right to delete specific resources, may be not
        if(!$user_id) {

            if(Yii::$app->user->identity->company_id != $official_account_info['company_id']) {
                return false;
            }

            return true;
        }
        else {

            $user_info = User::findById($user_id, false);

            if(!$user_info) {
                return false;
            }

            if($user_info['company_id'] != $official_account_info['company_id']) {
                return false;
            }

            return true;
        }
    }

    public static function _deleteArticleMulti($material_info) {

        static::deleteAllChild(["parent_id"=>$material_info->id]);

        $is_synchronized = $material_info->is_synchronized;

        $material_info->delete();

        if($is_synchronized) {
            $wechat = WechatHelper::getWechat($material_info['official_account_id']);
            $wechat->material->delete($material_info->media_id);
        }

        return true;
    }

    public static function _updateArticleLocal($parent_article,$article_list){
        // find out all the child article
        $origin_article_id_arr = self::find()->select(['id','order'])->where(['parent_id'=>$parent_article->id])->orderBy('order')->asArray()->all();
        $origin_article_id_list = array_column($origin_article_id_arr,"id");
        $new_article_id_list = [];
        $i = 0;
        foreach($article_list as $new_article){
            if(isset($new_article['id']) && $new_article['id'] !== 0){
                $new_article_id_list[] = $new_article['id'];
                $update_article[] = $new_article;
            }elseif ($new_article['id'] == 0){
                if(isset($origin_article_id_list[$i])){
                    $new_article['id'] = $origin_article_id_list[$i];
                    $update_article[] = $new_article;
                    $wechat_article_id_list[] = $origin_article_id_list[$i];
                    $i++;
                }else{
                    $add_article[] = $new_article;
                }
            } else{
                $add_article[] = $new_article;
            }
        }
        if(!empty($wechat_article_id_list)){
            if(count($wechat_article_id_list) < count($origin_article_id_list)){
                $delete_article_id_list = array_diff($wechat_article_id_list,$origin_article_id_list);
            }
        }

        if (!empty($new_article_id_list)){
            $delete_id_list = array_diff($origin_article_id_list,$new_article_id_list);
            if(!empty($delete_article_id_list)){
                $delete_id_list = array_merge($delete_id_list,$delete_article_id_list);
            }
            self::deleteAll(["in","id",$delete_id_list]);
        }

        if (!empty($update_article)){
            $is_update = self::updateChildArticle($update_article);
            if(!$is_update) {
                Yii::error(sprintf('Fail to update child articles for material(%s).', $parent_article->id));
                return false;
            }
        }

        if (!empty($add_article)){
            $is_inserted = self::batchInsertChildArticle($parent_article, $add_article);
            if(!$is_inserted) {
                Yii::error(sprintf('Fail to batch insert child articles for material(%s).', $parent_article->id));
                return false;
            }
        }
    }

}