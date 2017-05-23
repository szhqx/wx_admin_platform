<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;


use EasyWeChat\Core\Exceptions\HttpException;


/**
 * Signup form.
 */
class ProxyDomain extends ActiveRecord
{
    public function rules()
    {
        return [];
    }

    public static function findById($id){
        return static::find()->where(['id' => $id])->one();
    }

    public static function findByDomain($domain){
        return static::find()->where(['domain' => $domain])->one();
    }

    public static function findByIdList($oidList) {

        $query = static::find()->where(['in', 'id', $oidList]);

        return $query->all();
    }

    public static function deleteByIdlist($oidList) {

        static::deleteAll(['in', 'id', $oidList]);

        return true;
    }

    public static function getDomainList($params, $page, $num) {

        $query = static::find();

        foreach($params as $key=>$value) {
            $query->andWhere([$key=>$value]);
        }

        $query->orderBy(["created_at" => SORT_DESC]);

        $start = max(($page-1)*$num, 0);

        $query->limit($num)->offset($start);

        return $query->all();
    }

    public static function getTotal($params) {

        $query = static::find();

        foreach($params as $key=>$value) {
            $query->andWhere([$key=>$value]);
        }

        return $query->count();
    }

    public static function getRandomDomain() {
        $query = self::find()->orderBy(new Expression('rand()'))->limit(1);

        return $query->one();
    }

    public static function prepareProxyUrl($url) {

        // Yii::info($url);

        if(!$url) {
            return '';
        }

        $ran_proxy = self::getRandomDomain();

        $final_url = $url;
        // $current_domain = \Yii::$app->params['HOST_INFO']['SCHEME'] + '://' + \Yii::$app->params['HOST_INFO']['API_DOMAIN_INFO'];

        // TODO 做安全校验
        if($ran_proxy) {
            $ran_proxy->req_nums += 1;
            $ran_proxy->save(false);
            $final_url = sprintf('%sindex.php?r=proxy-domain/redirect&id=%s&url=%s', $ran_proxy->domain, $ran_proxy['id'], urlencode(base64_encode(urlencode($url))));
        }

        return $final_url;
    }

}
