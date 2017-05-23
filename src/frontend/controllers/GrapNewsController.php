<?php

namespace frontend\controllers;

use common\helpers\Utils;
use Yii;

use PHPHtmlParser\Dom;

class GrapNewsController extends BaseController
{

    /**
     * 抓取文章内容
     *
     * @return string
     */
    
    public function actionGetNews(){
        $url = Yii::$app->request->post('url');
//        var_dump(strpos($url,'mp.weixin.qq.com'));exit;
        if(!strpos($url,'mp.weixin.qq.com')){
            return json_encode(["code"=>-1, "msg"=>"不是微信文章",]);
        }
//        $url = 'http://mp.weixin.qq.com/s/86DuvupixlkJ0gdPF3FgWQ';
        $html = file_get_contents($url);

        $st =stripos($html,'msg_cdn_url');
        $imgurl = substr($html,$st,300);
        $imgurl1 = $this ->getNeedBetween3($imgurl,'msg_cdn_url =','";');
        $imgurl2 = str_ireplace('sg_cdn_url = "','',$imgurl1);
        $imgurl3 = Utils::prepare_cover_url($imgurl2);

        $title1 = $this ->getNeedBetween2($html,'<title>','</title>');
        $title = str_ireplace('title>','',$title1);



        $content = $this->getNeedBetween2($html,'<div class="rich_media_content " id',"</body>");
        $content2 = $this->getNeedBetween3($content,'id="js_content">','<script nonce=');
        $content3 = trim(str_ireplace('d="js_content">',"",$content2));
        $content4 = $this->_prepare_content($content3);
        $final_data = [
            'res_article_title' => $title,
            'res_content' => $content4,
            'res_thumb_image' => $imgurl3,
            'url' => $url,
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);


    }

    function getNeedBetween($input,$start,$end){
        $substr = substr($input, strlen($start)+strpos($input, $start),
            (strlen($input) - strpos($input, $end))*(-1));
        return $substr;
    }

    function getNeedBetween2($kw1,$mark1,$mark2){
        $kw=$kw1;
        $kw='123'.$kw.'123';
        $st =stripos($kw,$mark1);
        $ed =strrpos($kw,$mark2);

        if(($st==false||$ed==false)||$st>=$ed)
            return 0;
        $kw=substr($kw,($st+1),($ed-$st-1));
        return $kw;
    }
    function getNeedBetween3($kw1,$mark1,$mark2){
        $kw=$kw1;
        $kw='123'.$kw.'123';
        $st =stripos($kw,$mark1);
        $ed =stripos($kw,$mark2);
        if(($st==false||$ed==false)||$st>=$ed)
            return 0;
        $kw=substr($kw,($st+1),($ed-$st-1));
        return $kw;
    }

    private function _prepare_content($content) {

        // 调整wechat img 的link
        if(!$content) {
            return '';
        }

        $pattern = \Yii::$app->params['WECHAT_IMG_DOMAIN_PATTERN_WITH_POS'];
        $fix_url = \Yii::$app->params['CUSTOM_IMG_DOMAIN'];
        $content = preg_replace($pattern, sprintf('%s?q=\1\2', $fix_url), $content);

        $dom = new Dom;

        $dom->load($content);
        $img_list = $dom->find('img');

        foreach($img_list as $img) {

            $data_img_src = $img->getAttribute('data-src');
            $raw_img_src = $img->getAttribute('src');

            if($raw_img_src) {
                $img_src = $raw_img_src;
            } else {
                $img_src = $data_img_src;
            }

            $img->setAttribute('src', $img_src);

            // preg_match($pattern, $img_src, $matches);

            // // TODO 调整这里的代码
            // if($matches) {
            //     $img->setAttribute('src', $fix_url . '?q=' . $img_src);
            // } else {
            //     $img->setAttribute('src', $img_src);
            // }

            // simply delet the data-src attribute
            $img->removeAttribute('data-src');
        }

        return (string)$dom;
        // return $content;
    }




}
