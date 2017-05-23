<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

use common\helpers\OssUtils;

use EasyWeChat\Core\Exceptions\HttpException;

use GuzzleHttp\Exception\ConnectException;


/**
 * ArticleImageMap model.
 *
 * @property int $id
 * @property string $source_link
 * @property string $wechat_source_link
 */
class ArticleImageMap extends ActiveRecord
{
    public static function findBySourceUrl($source_url) {
        $query = static::find()->where(['source_url'=>$source_url]);
        return $query->one();
    }

    public static function findBySourceUrlList($source_url_list) {
        $query = static::find()->where(['in', 'source_url', $source_url_list]);
        return $query->all();
    }

    public static function getImageMap($source_url_list) {

        $image_map = [];
        $has_downloaded = [];

        $query = static::find()->where(['in', 'source_url', $source_url_list]);
        $image_list = $query->all();

        foreach($image_list as $image) {
            $image_map[$image['source_url']] = $image['wechat_source_url'];
        }

        $has_downloaded = array_keys($image_map);

        $map_result = [
            "has_downloaded"=>$has_downloaded,
            "image_map"=>$image_map
        ];

        return $map_result;
    }
}