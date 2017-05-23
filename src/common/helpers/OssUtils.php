<?php

namespace common\helpers;

use Yii;

use common\models\Company;

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Oss配置管理类
 *
 */
class OssUtils {

    /**
     *
     */
    public static function constructConfig($company_id) {

        // global $id, $key, $host, $dir;
        $ALIYUN_INFO = Yii::$app->params['ALIYUN_INFO'];
        $urlManager = Yii::$app->urlManager;

        $id = $ALIYUN_INFO['KEY'];
        $key = $ALIYUN_INFO['SECRET'];
        $host = $urlManager->getHostInfo();
        $dir = self::cal_upload_dir($company_id);

        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = self::gmtIso8601($end);

        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 200000000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);

        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;

        return $response;
    }

    /**
     */
    public static function gmtIso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

    public static function cal_upload_dir($company_id)
    {
        return md5("upload_".$company_id);
    }

    public static function downLoadSourceFromAliyun($raw_image_content) {

        $oss_info = Yii::$app->params['ALIYUN_INFO'];
        $oss_client = new OssClient($oss_info['KEY'], $oss_info['SECRET'], $oss_info['END_POINT']);

        $source_key = $raw_image_content['image_key'];
        $extension = strtolower($raw_image_content['mime_type']);
        $tempname = tempnam('/mnt/tmp', '');
        $path = $tempname . '.' . $extension;
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $path
        );

        $oss_client->getObject($oss_info['BUCKET'], $source_key, $options);

        $uploadInfo = [
            "path"=>$path
        ];

        return $uploadInfo;
    }

    public static function uploadSourceToAliyun($object_key, $content) {

        $oss_info = Yii::$app->params['ALIYUN_INFO'];
        $oss_client = new OssClient($oss_info['KEY'], $oss_info['SECRET'], $oss_info['END_POINT']);

        $bucket = $oss_info['BUCKET'];

        try {
            $oss_client->putObject($bucket, $object_key, $content);
        } catch (\Exceptions $e) {
            Yii::error(sprintf("Fail to upload source to aliyun cos reason(%s).", $e->getMessage()));
            return;
        }

        return true;
    }

    public static function getObjectKey($length=50) {
        return uniqid('', true);
    }

    public static function getUploadKey($object_key, $company_id, $official_account_id) {
        return md5(sprintf('resources/company/%s/%s', $company_id, $official_account_id)) . '/' . $object_key;
    }

    /*
     * 构造图片的阿里云url
     */
    public static function constructAliSourceUrl($imgKey)
    {
        $aliyun_info = Yii::$app->params['ALIYUN_INFO'];

        $final_url = $aliyun_info['REQUEST_SCHEME'] . '://' . $aliyun_info['BUCKET'] . "." . $aliyun_info['END_POINT'] . '/' . $imgKey;

        return $final_url;
    }

}