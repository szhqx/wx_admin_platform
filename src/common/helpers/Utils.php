<?php

namespace common\helpers;

use Yii;

use PHPHtmlParser\Dom;

use yii\helpers\Url;


class Utils {

    /**
    * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
    *
    * @param $pArray 一个二维数组
    * @param $pKey 数组的键的名称
    * @return 返回新的一维数组
    */
    public static function getSubByKey($pArray, $pKey = "", $pCondition = "") {
        $result = array ();
        if (is_array ( $pArray )) {
            foreach ( $pArray as $temp_array ) {
                if (is_object ( $temp_array )) {
                    $temp_array = ( array ) $temp_array;
                }
                if (("" != $pCondition && $temp_array [$pCondition [0]] == $pCondition [1]) || "" == $pCondition) {
                    $result [] = ("" == $pKey) ? $temp_array : isset ( $temp_array [$pKey] ) ? $temp_array [$pKey] : "";
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /*
     * 创建绝对路径
     */
    public static function createAbsoluteUrl($params) {

        $final_url = '';

        $host_info = Yii::$app->params['HOST_INFO'];

        return $final_url;
    }

    public static function imgLinkExtractor($html){

        $linkArray = array();

        $preg_str = '/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>|background-image[=:]\s*url\((.*?)\)|border-image[=:]\s*url\((.*?)\)/i';

        if(preg_match_all($preg_str, $html, $matches, PREG_SET_ORDER)){
            foreach($matches as $match_ok_list){
                array_shift($match_ok_list);

                // TODO 兼容更多可能的情况
                // remove quote
                foreach($match_ok_list as $match) {
                    $match = trim($match, "&quote;|&#39|\'|\"");
                    if($match) {
                        array_push($linkArray, trim($match));
                    }
                }
            }
        }

        return $linkArray;
    }

    public static function clear_wechat_redirect_url($raw_content) {

        if(!$raw_content) {
            return '';
        }

        // $pattern = '/(.*)\/we_img_r_w_a_p\/\?q=(.*)/';
        $pattern = sprintf('/%s\?q=(.*)??/', trim(\Yii::$app->params['CUSTOM_IMG_DOMAIN_REGEX'], "'"));
        $raw_content = preg_replace($pattern, '\1', $raw_content);

        $dom = new Dom;
        $dom->load($raw_content);
        $img_list = $dom->find('img');

        foreach($img_list as $img) {

            $data_img_src = $img->getAttribute('data-src');
            $raw_img_src = $img->getAttribute('src');

            if($raw_img_src) {
                $img_src = $raw_img_src;
            } else {
                $img_src = $data_img_src;
            }

            // preg_match($pattern, $img_src, $matches, PREG_OFFSET_CAPTURE);

            // if($matches) {
            //     $img->setAttribute('src', $matches[2][0]);
            // } else {
            //     $img->setAttribute('src', $img_src);
            // }

            $img->setAttribute('src', $img_src);

            // simply delet the data-src attribute
            $img->removeAttribute('data-src');
        }

        return (string)$dom;
    }

    public static function prepare_cover_url($wx_cover_url) {

        // $fix_url = Url::base() . '/we_img_r_w_a_p/';

        $fix_url = \Yii::$app->params['CUSTOM_IMG_DOMAIN'];

        $cover_url = $fix_url . "?q=" . $wx_cover_url;

        return $cover_url;
    }

    public static function restore_wechat_cover_url($cover_url) {

        $restore_cover_url = $cover_url;

        $pattern = sprintf('/%s\?q=(.*)/', trim(\Yii::$app->params['CUSTOM_IMG_DOMAIN_REGEX'], "'"));

        preg_match($pattern, $cover_url, $matches, PREG_OFFSET_CAPTURE);

        if($matches) {
            $restore_cover_url = $matches[1][0];
        }

        return $restore_cover_url;
    }

    public static function prepare_image_weixin_source_url($weixin_source_url) {

        $fix_url = \Yii::$app->params['CUSTOM_IMG_DOMAIN'];

        $cover_url = $fix_url . "?q=" . $weixin_source_url;

        return $cover_url;

    }
}