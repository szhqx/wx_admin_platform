<?php

namespace frontend\controllers;

use common\models\Menus;
use common\models\StatisticMenu;
use common\models\StatisticNew;
use common\models\StatisticNews;
use common\models\StatisticUser;
use Yii;


use EasyWeChat\User\User;

use EasyWeChat\Core\Exceptions\HttpException;

class StatisticController extends BaseController
{
    public $wechat;
    /**
     * 获取统计数据. (从数据库中拉去)
     *
     * @return string
     */

    /**
     * 获取用户分析数据.
     *
     * @return array
     */
    public function actionGetFansData(){
       if(!Yii::$app->exAuthManager->can('statics/get-fans-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",12);
//        $day = Yii::$app->request->get("day",30);
        $from = Yii::$app->request->get("start_time")-86399;
        $to = Yii::$app->request->get("end_time");

        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }
//        var_dump($day);exit;

        #TODO 此处应该使用文件缓存来做
        $this->_checkData($official_account_id,'fans');

        $model = new StatisticUser();
//        $from = strtotime(date("Y-m-d",strtotime("-".$day." day")));
//        $to = strtotime(date("Y-m-d",strtotime("-1 day")));
        $data_list = $model->find()
            ->where(['between','ref_date',$from,$to])
            ->andWhere(['official_account_id'=>$official_account_id])
            ->orderBy('ref_date')->asArray()->all();

        $add_user_data=[];$cancel_user_data=[];$cumulate_user_data=[];$new_user_data=[];$time=[];

//        var_dump($data_list);exit;

        if($data_list){
            foreach($data_list as $k=>$v){

                $time[] = date("m-d",$v['ref_date']);

                $add_user[] = (int)$v['new_user'];
                $cancel_user[] = (int)$v['cancel_user'];
                $cumulate_user[] = (int)$v['cumulate_user'];
                $new_user[] = $v['new_user']-$v['cancel_user'];
                $add_user_data = [
                    "name" => "新增关注",
                    "data" => $add_user
                ];
                $cancel_user_data = [
                    "name" => "取消关注",
                    "data" => $cancel_user
                ];
                $cumulate_user_data = [
                    "name" => "累积关注",
                    "data" => $cumulate_user
                ];
                $new_user_data = [
                    "name" => "净增关注",
                    "data" => $new_user
                ];
            }
        }

        $final_data = [
            "time" => $time,
            "data" => [$add_user_data,$cancel_user_data,$cumulate_user_data,$new_user_data]
        ];
//        var_dump($final_data);exit;
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }

    /**
     * 获取菜单分析数据.
     *
     * @return array
     */
    public function actionGetMenuData(){
       if(!Yii::$app->exAuthManager->can('statics/get-fans-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",79);
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));

        $from = Yii::$app->request->get("start_time",$yesterday-86400*7);
        $to = Yii::$app->request->get("end_time",$yesterday);
        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }

        $count = ceil(($to-$from)/86400);
        $format_time = [];
        $data_time = [];
        for ($i=1;$i<=$count+1;$i++){
            $format_time[] = strtotime(date("Y-m-d",strtotime("-".$i." day")));
            $data_time[] = date("Y-m-d",strtotime("-".$i." day"));
        }
        sort($format_time);
        sort($data_time);

//        var_dump(111);exit;
        $model = new StatisticMenu();
        $menus = Menus::find()->where(['official_account_id'=>$official_account_id])->andWhere(['in','msg_type',['view','text']])->asArray()->all();
        $menu_name_list = array_column($menus,'name');
//        $menu_name_arr = [];
//        foreach ($menu_name_list as $menu_name){
//            $menu_name_arr[] = $this->_preMenuName($menu_name,$official_account_id);
//        }
        $org_data = [];
//        var_dump($menu_name_arr);
        foreach ($menu_name_list as $menu_name){
            $data = [];
            foreach ($format_time as $time){
                $data[$time] = 0;
            }
            $org_data[] = [
                "name" =>$menu_name,
                "data" =>$data,
            ];
        }

        $data_list = $model->find()
            ->where(['official_account_id'=>$official_account_id])
            ->andWhere(['between','ref_date',$from,$to])
            ->andWhere(['in','menus_name',$menu_name_list])
            ->orderBy('ref_date desc')
            ->asArray()->all();

//        var_dump($data_list);exit;
        foreach ($org_data as $k=>$menu_name_data){
            foreach ($menu_name_data['data'] as $time=>$data_org){
                foreach ($data_list as $list){
                    if($list['ref_date'] == $time && $menu_name_data['name'] == $this->_preMenuName($list['menus_name'],$official_account_id)){
                        $org_data[$k]['data'][$time] = (int)$list['click_count'];
                    }
                }
            }
        }
//        var_dump($org_data);exit;
        $data_f = [];
        foreach ($org_data as $k=>$v){
            $data = [];
            foreach ($v['data'] as $time=>$d){
                $data [] = $d;
            }
            $data_f[] = [
                "name" => $this->_preMenuName($v['name'],$official_account_id),
                "data" => $data
            ];
        }

        $yesterday_data = $model->find()
            ->where(['between','ref_date',$yesterday,$yesterday+86400])
            ->select(['sum(click_count) as click_count' ,'sum(click_user) as click_user'])
            ->andWhere(['official_account_id'=>$official_account_id])
            ->andWhere(['in','menus_name',$menu_name_list])
            ->orderBy('ref_date desc')
            ->groupBy('ref_date')
            ->asArray()->one();
        if(!$yesterday_data){
            $yesterday_data['click_count'] =0;
            $yesterday_data['click_user'] =0;
            $yesterday_data['click_avg'] =0;
        }else{
            if($yesterday_data['click_user'] == 0){
                $yesterday_data['click_avg'] = $yesterday_data['click_count'];
            }else{
                $yesterday_data['click_avg'] = sprintf("%.2f",$yesterday_data['click_count']/$yesterday_data['click_user']);
            }
//            $yesterday_data['click_avg'] = 1;
        }

//        var_dump($yesterday_data);exit;

//        $ref_date_ar = array_unique(array_column($data_list,'ref_date'));
//
//        $ref_date_arr = [];
//        foreach ($ref_date_ar as $k=>$v){
//            $ref_date_arr[] = date("m-d",$v);
//        }
//
//        $data_menu_name = [];
//
//        foreach ($data_list as $k=>$v){
//            $data_menu_name[$v['menus_name']][] = (int)$v['click_count'];
//        }
//        $data = [];
//        foreach ($data_menu_name as $k=>$v){
//            $data[] = [
//                "name" => $k,
//                "data" => $v
//            ];
//        }

        $final_data = [
            "yesterday_data" => $yesterday_data,
            "time" => $data_time,
            "data" => $data_f
        ];
//        var_dump($final_data);exit;
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);

    }

    /**
 * 获取图文分析数据.
 *
 * @return array
 */
    public function actionGetNewsData(){

       if(!Yii::$app->exAuthManager->can('statics/get-news-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",12);
//        $day = Yii::$app->request->get("day",5);
        $from = Yii::$app->request->get("start_time")-86399;
        $to = Yii::$app->request->get("end_time");

        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }
        $day = $days=round(($to-$from)/3600/24) ;

        $this->_checkData($official_account_id,'news');

        $model = new StatisticNews();
//        $from = strtotime(date("Y-m-d",strtotime("-".$day." day")));
//        $to = strtotime(date("Y-m-d",strtotime("-1 day")));
        $data_list = $model->find()
            ->where(['between','ref_date',$from,$to])->andWhere(['official_account_id'=>$official_account_id])
            ->orderBy('ref_date')->asArray()->all();
//        var_dump($data_list);exit;
        $final_data_0 = [];
        $final_data_1 = [];
        $final_data_2 = [];
        $final_data_4 = [];
        $final_data_5 = [];

        $int_page_read_user0= [];
        $int_page_read_user1= [];
        $int_page_read_user2= [];
        $int_page_read_user4= [];
        $int_page_read_user5= [];

        $int_page_read_count0 = [];
        $int_page_read_count1 = [];
        $int_page_read_count2 = [];
        $int_page_read_count4 = [];
        $int_page_read_count5 = [];

        $time0 = [];

        foreach($data_list as $k => $v){

            if($v['user_source'] == 0){
                $time0[] = date("m-d",$v['ref_date']);
                $int_page_read_user0[] = (int)$v['int_page_read_user'];
                $int_page_read_count0[] = (int)$v['int_page_read_count'];
                $int_page_read_user_data0 = [
                    "name" => "图文阅读人数",
                    "data" => $int_page_read_user0
                ];
                $int_page_read_count_data0 = [
                    "name" => "图文阅读次数",
                    "data" => $int_page_read_count0
                ];
                $final_data_0 = [
                    //  "time" => $time0,
                    "data" => [$int_page_read_user_data0,$int_page_read_count_data0]
                ];
            }
            if($v['user_source'] == 1){
                //$time1[] = date("Y-m-d",$v['ref_date']);
                $int_page_read_user1[] = (int)$v['int_page_read_user'];
                $int_page_read_count1[] = (int)$v['int_page_read_count'];
                $int_page_read_user_data1 = [
                    "name" => "图文阅读人数",
                    "data" => $int_page_read_user1
                ];
                $int_page_read_count_data1 = [
                    "name" => "图文阅读次数",
                    "data" => $int_page_read_count1
                ];
                $final_data_1 = [
                    // "time" => $time1,
                    "data" => [$int_page_read_user_data1,$int_page_read_count_data1]
                ];
            }
            if($v['user_source'] == 2){
                // $time2[] = date("Y-m-d",$v['ref_date']);
                $int_page_read_user2[] = (int)$v['int_page_read_user'];
                $int_page_read_count2[] = (int)$v['int_page_read_count'];
                $int_page_read_user_data2 = [
                    "name" => "图文阅读人数",
                    "data" => $int_page_read_user2
                ];
                $int_page_read_count_data2 = [
                    "name" => "图文阅读次数",
                    "data" => $int_page_read_count2
                ];
                $final_data_2 = [
                    // "time" => $time2,
                    "data" => [$int_page_read_user_data2,$int_page_read_count_data2]
                ];
            }
            if($v['user_source'] == 4){
                //$time4[] = date("Y-m-d",$v['ref_date']);
                $int_page_read_user4[] = (int)$v['int_page_read_user'];
                $int_page_read_count4[] = (int)$v['int_page_read_count'];
                $int_page_read_user_data4 = [
                    "name" => "图文阅读人数",
                    "data" => $int_page_read_user4
                ];
                $int_page_read_count_data4 = [
                    "name" => "图文阅读次数",
                    "data" => $int_page_read_count4
                ];
                $final_data_4 = [
                    // "time" => $time4,
                    "data" => [$int_page_read_user_data4,$int_page_read_count_data4]
                ];
            }
            if($v['user_source'] == 5){
                // $time5[] = date("Y-m-d",$v['ref_date']);
                $int_page_read_user5[] = (int)$v['int_page_read_user'];
                $int_page_read_count5[] = (int)$v['int_page_read_count'];
                $int_page_read_user_data5 = [
                    "name" => "图文阅读人数",
                    "data" => $int_page_read_user5
                ];
                $int_page_read_count_data5 = [
                    "name" => "图文阅读次数",
                    "data" => $int_page_read_count5
                ];
                $final_data_5 = [
                    // "time" => $time5,
                    "data" => [$int_page_read_user_data5,$int_page_read_count_data5]
                ];
            }

        }

//        var_dump($final_data_0);
//        var_dump($final_data_2);

        $final_data_0 = $this->_returnEmptyData($final_data_0,$day);
        $final_data_1 = $this->_returnEmptyData($final_data_1,$day);
        $final_data_2 = $this->_returnEmptyData($final_data_2,$day);
        $final_data_4 = $this->_returnEmptyData($final_data_4,$day);
        $final_data_5 = $this->_returnEmptyData($final_data_5,$day);

        $final_data = [
            "time" => $time0,
            "dialogue" => $final_data_0,
            "friend" => $final_data_1,
            "circle_friend" => $final_data_2,
            "history" => $final_data_4,
            "other" => $final_data_5,
        ];
        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);


    }

    /**
     * 获取图文分析表格数据.
     *
     * @return array
     */
    public function actionGetNewsTableData(){

       if(!Yii::$app->exAuthManager->can('statics/get-news-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",12);
//        $day = Yii::$app->request->get("day",5);
        $default_from = strtotime(date("Y-m-d",strtotime("-7 day")));
        $to_from = strtotime(date("Y-m-d",strtotime("-1 day")));
        $from = Yii::$app->request->get("start_time",$default_from)-86399;
        $to = Yii::$app->request->get("end_time",$to_from);
        $page = 1;
        $num = 30;
        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }
//        $day = $days=round(($to-$from)/3600/24) ;

        $this->_checkData($official_account_id,'news');

        $params = [
             "from" => $from,
             "to" => $to,
             "page" => $page,
             "num" => $num,
             "official_account_id" =>$official_account_id
        ];

        $final_data = StatisticNews::getlist($params);

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$final_data]);


//        $count = $day*5;
//        $date = [];
//        for($d=1;$d<=$day;$d++){
//            $date[] =  date("Y-m-d",strtotime("-".$d." day"));
//        }
//
//        for($i=0; $i<$count;$i++){//0:会话;1.好友;2.朋友圈;3.腾讯微博;4.历史消息页;5.其他
//            foreach($date as $d){
////                var_dump(date("Y-m-d",$data_list[$i]['ref_date']));exit;
//                if(isset($data_list[$i]['ref_date'])){
//                    if($d == date("Y-m-d",$data_list[$i]['ref_date'])){
//                        $final_data[date("Y-m-d",$data_list[$i]['ref_date'])]['user_source_'.$data_list[$i]['user_source']] = [
//                            "add_to_fav_count"=>$data_list[$i]['add_to_fav_count'], //收藏的次数
//                            "add_to_fav_user"=>$data_list[$i]['add_to_fav_user'], //收藏的人数
//                            "int_page_read_count"=>$data_list[$i]['int_page_read_count'], //图文页阅读次数
//                            "int_page_read_user"=>$data_list[$i]['int_page_read_user'], //图文页阅读人数
//                            "ori_page_read_count"=>$data_list[$i]['ori_page_read_count'],//原文页阅读 没有原文页的数据为零
//                            "ori_page_read_user"=>$data_list[$i]['ori_page_read_user'],//原文页阅读人数
//                            "share_count"=>$data_list[$i]['share_count'],//分享的次数
//                            "share_user"=>$data_list[$i]['share_user'], //分享的人数
//                        ];
//
//                    }
//                }
//
//            }
//        }
//        return json_encode(['code'=>0,'msg'=>"ok",'data'=>$final_data]);
    }

    /**
     * 获取图文分析数据.
     *
     * @return array
     */
    public function actionGetNewsYesterdayData(){

       if(!Yii::$app->exAuthManager->can('statics/get-news-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",12);
//        $day = Yii::$app->request->get("day",5);

        $this->_checkData($official_account_id,'news');

        $model = new StatisticNews();
        $from = strtotime(date("Y-m-d",strtotime("-1 day")));
        $to = strtotime(date("Y-m-d",time()));
        $data_list = $model->find()
            ->where(['between','ref_date',$from,$to])->andWhere(['official_account_id'=>$official_account_id])
            ->orderBy('ref_date desc')->asArray()->all();
//        var_dump($data_list);exit;

        $int_page_read_count = 0;
        $ori_page_read_count = 0;
        $share_count = 0;
        $add_to_fav_user = 0;
        foreach($data_list  as $k=>$v){
            $int_page_read_count +=$v['int_page_read_count'];
            $ori_page_read_count +=$v['ori_page_read_count'];
            $share_count +=$v['share_count'];
            $add_to_fav_user +=$v['add_to_fav_user'];
        }
        $newdata = [
            'int_page_read_count' => $int_page_read_count,
            'ori_page_read_count' => $ori_page_read_count,
            'share_count' => $share_count,
            'add_to_fav_user' =>  $add_to_fav_user
        ];

        return json_encode(["code"=>0, "msg"=>$this->status_code_msg[0], "data"=>$newdata]);


//        $count = $day*5;
//        $date = [];
//        for($d=1;$d<=$day;$d++){
//            $date[] =  date("Y-m-d",strtotime("-".$d." day"));
//        }
//
//        for($i=0; $i<$count;$i++){//0:会话;1.好友;2.朋友圈;3.腾讯微博;4.历史消息页;5.其他
//            foreach($date as $d){
////                var_dump(date("Y-m-d",$data_list[$i]['ref_date']));exit;
//                if(isset($data_list[$i]['ref_date'])){
//                    if($d == date("Y-m-d",$data_list[$i]['ref_date'])){
//                        $final_data[date("Y-m-d",$data_list[$i]['ref_date'])]['user_source_'.$data_list[$i]['user_source']] = [
//                            "add_to_fav_count"=>$data_list[$i]['add_to_fav_count'], //收藏的次数
//                            "add_to_fav_user"=>$data_list[$i]['add_to_fav_user'], //收藏的人数
//                            "int_page_read_count"=>$data_list[$i]['int_page_read_count'], //图文页阅读次数
//                            "int_page_read_user"=>$data_list[$i]['int_page_read_user'], //图文页阅读人数
//                            "ori_page_read_count"=>$data_list[$i]['ori_page_read_count'],//原文页阅读 没有原文页的数据为零
//                            "ori_page_read_user"=>$data_list[$i]['ori_page_read_user'],//原文页阅读人数
//                            "share_count"=>$data_list[$i]['share_count'],//分享的次数
//                            "share_user"=>$data_list[$i]['share_user'], //分享的人数
//                        ];
//
//                    }
//                }
//
//            }
//        }
//        return json_encode(['code'=>0,'msg'=>"ok",'data'=>$final_data]);
    }

    /**
     * 获取昨天图文分析数据.
     *
     * @return array
     */
    public function actionGetYesterdayNewsData(){

       if(!Yii::$app->exAuthManager->can('statics/get-news-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",61);

        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            return json_encode(["code"=>10101, "msg"=>$this->status_code_msg[10101]]);
        }
        $stats = $this->wechat->stats;
        $userReadHourly = $stats->userReadHourly(date("Y-m-d",strtotime("-1 day")), date("Y-m-d",strtotime("-1 day")))->toArray();

        $data_list = $userReadHourly['list'];

        $final_data = [];
        $count = count($data_list);

        for($i=0; $i<$count;$i++){//0:会话;1.好友;2.朋友圈;3.腾讯微博;4.历史消息页;5.其他
            $time = sprintf("%.2f",$data_list[$i]['ref_hour']/100);
            $final_data[$time]['user_source_'.$data_list[$i]['user_source']] = [
                "add_to_fav_count"=>$data_list[$i]['add_to_fav_count'], //收藏的次数
                "add_to_fav_user"=>$data_list[$i]['add_to_fav_user'], //收藏的人数
                "int_page_read_count"=>$data_list[$i]['int_page_read_count'], //图文页阅读次数
                "int_page_read_user"=>$data_list[$i]['int_page_read_user'], //图文页阅读人数
                "ori_page_read_count"=>$data_list[$i]['ori_page_read_count'],//原文页阅读 没有原文页的数据为零
                "ori_page_read_user"=>$data_list[$i]['ori_page_read_user'],//原文页阅读人数
                "share_count"=>$data_list[$i]['share_count'],//分享的次数
                "share_user"=>$data_list[$i]['share_user'], //分享的人数
            ];
        }
        return json_encode(['code'=>0,'msg'=>"ok",'data'=>$final_data]);
    }

    /**
     * 获取单图文分析数据.
     *
     * @return array
     */
    public function actionGetNewData(){

       if(!Yii::$app->exAuthManager->can('statics/get-news-data')) {
           return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
       }

        $official_account_id = Yii::$app->request->get("official_account_id",12);
        $this->_checkArticleData($official_account_id);

        $from = Yii::$app->request->get("start_time");
        $to = Yii::$app->request->get("end_time");

        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }
//        $day = $days=round(($to-$from)/3600/24);

        $model = new StatisticNew();
//        $from = strtotime(date("Y-m-d",strtotime("-".$day." day")));
//        $to = strtotime(date("Y-m-d",strtotime("-1 day")));
        $data_list = $model->find()
            ->where(['between','ref_date',$from,$to])->andWhere(['official_account_id'=>$official_account_id])
            ->orderBy('stat_date desc')
            ->groupBy('title')
            ->asArray()->all();

//        var_dump($data_list);exit;
        $final_data = [];
        foreach($data_list as $k =>$v){
            $final_data[] = [
                "title" => $v['title'],
                "ref_date" =>$v['ref_date'],
                "target_user" => $v['target_user'],
                "int_page_read_user" =>$v['int_page_read_user'],
                "share_user" => $v['share_user']
            ];
        }

        return json_encode(["code"=>0, "msg"=>"ok","data"=>$final_data]);

    }

    /**
     * 导出图文详细数据.
     *
     * @return string
     */
    public function actionExportNewsData(){

        if(!Yii::$app->exAuthManager->can('statics/export-news-data')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get("official_account_id",76);
        $default_from = strtotime(date("Y-m-d",strtotime("-7 day")));
        $to_from = strtotime(date("Y-m-d",strtotime("-1 day")));
        $from = Yii::$app->request->get("start_time",$default_from)-86399;
        $to = Yii::$app->request->get("end_time",$to_from);
        $page = 1;
        $num = 30;
        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }

//        $this->_checkData($official_account_id,'news');

        $params = [
            "from" => $from,
            "to" => $to,
            "page" => $page,
            "num" => $num,
            "official_account_id" =>$official_account_id
        ];

        $final_data = StatisticNews::getlist($params);
//        var_dump($final_data);exit;
        $list = [];
        foreach($final_data['list'] as $k=>$v){
            $list[] = [
                "ref_date" => $v['ref_date'],
                "int_page_read_user" => (int)$v['int_page_read_user'],
                "int_page_read_count" => (int)$v['int_page_read_count'],
                "int_page_read_user_0" => (int)$v['int_page_read_user_0'],
                "int_page_read_count_0" => (int)$v['int_page_read_count_0'],
                "int_page_read_user_2" => (int)$v['int_page_read_user_2'],
                "int_page_read_count_2" => (int)$v['int_page_read_count_2'],
                "share_user" => (int)$v['share_user'],
                "share_count" => (int)$v['share_count'],
                "add_to_fav_user" => (int)$v['add_to_fav_user'],
                "add_to_fav_count" => (int)$v['add_to_fav_count']
            ];
        }

        $format = [
            "日期" => 'ref_date',
            "图文页阅读人数" => 'int_page_read_user',
            "图文页阅读次数" => 'int_page_read_count',
            "从公众号会话打开人数" => 'int_page_read_user_0',
            "从公众号会话打开次数" => 'int_page_read_count_0',
            "从朋友圈打开人数" => 'int_page_read_user_2',
            "从朋友圈打开次数" => 'int_page_read_count_2',
            "分享转发人数" => 'share_user',
            "分享转发次数" => 'share_count',
            "微信收藏人数" => 'add_to_fav_user',
            "微信收藏次数" => 'add_to_fav_count'
        ];
        $title = "图文分析详细数据";
        $list = array_reverse($list);
        $format = array_reverse($format);

        $this->Export($format,$list,$title);


    }

    /**
     * 导出用户详细数据.
     *
     * @return string
     */
    public function actionExportUserData(){

        if(!Yii::$app->exAuthManager->can('statics/export-user-data')) {
            return json_encode(["code"=>20004, "msg"=>$this->status_code_msg[20004]]);
        }

        $official_account_id = Yii::$app->request->get("official_account_id",76);
        $default_from = strtotime(date("Y-m-d",strtotime("-7 day")));
        $to_from = strtotime(date("Y-m-d",strtotime("-1 day")));
        $from = Yii::$app->request->get("start_time",$default_from)-86399;
        $to = Yii::$app->request->get("end_time",$to_from);
        $page = 1;
        $num = 30;
        if($to<$from){
            return json_encode(["code"=>-1, "msg"=>"开始时间不能大于结束时间"]);
        }

//        $this->_checkData($official_account_id,'news');

        $params = [
            "from" => $from,
            "to" => $to,
            "page" => $page,
            "num" => $num,
            "official_account_id" =>$official_account_id
        ];

        $final_data = StatisticUser::getlist($params);
//        var_dump($final_data);exit;
        $list = [];
        foreach($final_data as $k=>$v){
            $list[] = [
                "ref_date" => (string)date("Y/m/d",$v['ref_date']),
                "new_user" => (int)$v['new_user'],
                "cancel_user" => (int)$v['cancel_user'],
                "cumulate_user" => (int)$v['cumulate_user'],
                "add_user" => (int)($v['new_user']-$v['cancel_user'])
            ];
        }

        $format = [
            "日期" => 'ref_date',
            "新增关注" => 'new_user',
            "取消关注" => 'cancel_user',
            "累积关注" => 'cumulate_user',
            "净增关注" => 'add_user'
        ];
        $title = "用户分析详细数据";

        $list = array_reverse($list);
        $format = array_reverse($format);

        $this->Export($format,$list,$title);

    }

    private function _returnEmptyData($data,$day){
        $d = [];
        for($i=1;$i<=$day;$i++){
           $d[] = 0;
        }
        if(count($data) == 0){
            $arr1[] = [
                "name" => "图文阅读人数",
                "data" => $d
            ];
            $arr2[] = [
                "name" => "图文阅读次数",
                "data" => $d
            ];
            $final_data = [
                "data" => [$arr1,$arr2]
            ];
            return $final_data;
        }
        return $data;
    }

    private function _preMenuName($name,$official_account_id){
        $model = new Menus();
        $res  = $model->find()->where(['official_account_id'=>$official_account_id,'name'=>$name])->andWhere(['<>','parent_id',0])->one();
        if($res){
            $res_return = $model->find()->where(['official_account_id'=>$official_account_id,'id_s'=>$res->parent_id])->one();
            return $res_return->name."-".$res->name;
        }else{
            return $name;
        }
    }

    /**
     * 检测本地是否存在统计数据，如果存在，跳过，如果不存在，向微信端拉去数据到本地.
     *
     * @return array
     */
    private function _checkData($official_account_id,$type){
        return true;
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));
        if($type == 'fans'){
            $model = new StatisticUser();
            $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
            if(!$res){
                for($i=0;$i<6;$i++) {
                    $from = date("Y-m-d", strtotime("-" . (7 * $i + 7) . " day"));
                    $to = date("Y-m-d", strtotime("-" . (7 * $i + 1) . " day"));
                    $FansData = $this->_getData($official_account_id, 'fans', $from, $to);
                    $this->_saveData($FansData, null, 'fans');
                }
            }else{
                $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
                if(!$res){
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'fans',$from, $to);
//                    var_dump($yesterdayData);exit;
                    $this->_saveData($yesterdayData,null,'fans');
                }
            }
        }
        else{
            $model = new StatisticNews();
            $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
            if(!$res){
                for($i=0;$i<15;$i++){
                    $from = date("Y-m-d",strtotime("-".(3*$i+3)." day"));
                    $to = date("Y-m-d",strtotime("-".(3*$i+1)." day"));
                    $NewsData = $this->_getData($official_account_id,'news',$from,$to);
                    $this->_saveData(null,$NewsData,'news');
                }
            }else{
                $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
                if(!$res){
                    $from = date("Y-m-d", strtotime("-1 day"));
                    $to = date("Y-m-d", strtotime("-1 day"));
                    $yesterdayData = $this->_getData($official_account_id,'news',$from,$to);
                    $this->_saveData(null,$yesterdayData,'news');
                }
            }
        }
        return true;

    }

    /**
     * 获取微信端统计数据
     *
     * @return array
     */
    private function _getData($official_account_id,$type,$from,$to){

        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
        }
        $stats = $this->wechat->stats;

        if($type == 'fans'){
            $userSummary = $stats->userSummary($from, $to)->toArray();
            $userCumulate = $stats->userCumulate($from, $to)->toArray();

            $data = [];

            foreach($userCumulate['list'] as $cumulate){
                $new_user = 0;
                $cancel_user = 0;
                foreach($userSummary['list'] as $summary){
                    if($cumulate['ref_date'] == $summary['ref_date']){
                        $new_user += $summary['new_user'];
                        $cancel_user += $summary['cancel_user'];
                        $ref_date = $summary['ref_date'];
                    }
                }
                $data[] = [
                    $official_account_id,
                    strtotime($cumulate['ref_date']),
                    $cumulate['user_source'],
                    $new_user,
                    $cancel_user,
                    $cumulate['cumulate_user'],
                    time()
                ];

            }
//var_dump($data);exit;
            return $data;
        }else{
            $data = [];
            $userReadSummary = $stats->userReadSummary($from, $to)->toArray();
            $userSource = [0,1,2,4,5];
            $ref_date = [];
            $news = [];
            $datass = [];
            foreach($userReadSummary['list'] as $k=>$v){
                $ref_date[$v['ref_date']][] = $v['user_source'];
            }

            foreach($ref_date as $k=>$v){
                $dif = array_diff($userSource,$v);
                if(count($dif) > 0 ){
                    foreach($dif as $kk =>$vv){
                        $news[$k][] = $vv;
                    }
                }
            }
            foreach($news as $k =>$v){
                foreach($v as $kk =>$vv){
                    $datass[] = [
                        $official_account_id,
                        strtotime($k),
                        $vv,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        time()
                    ];
                }

            }
            foreach ($userReadSummary['list'] as $list){
                $data[] = [
                    $official_account_id,
                    strtotime($list['ref_date']),
                    $list['user_source'],
                    $list['int_page_read_user'],
                    $list['int_page_read_count'],
                    $list['ori_page_read_user'],
                    $list['ori_page_read_count'],
                    $list['share_user'],
                    $list['share_count'],
                    $list['add_to_fav_user'],
                    $list['add_to_fav_count'],
                    time(),
                ];
            }
            $insert_data = array_merge($data,$datass);
            return $insert_data;
        }

    }

    /**
     * 保存数据
     *
     * @return true
     */
    private function _saveData($FansData=null,$newsData=null,$type){
//        var_dump($FansData);exit;
        if($type == 'fans'){
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticUser::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'user_source',
                    'new_user',
                    'cancel_user',
                    'cumulate_user',
                    'created_at'], $FansData)
                ->execute();
        }else{
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticNews::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'user_source',
                    'int_page_read_user',
                    'int_page_read_count',
                    'ori_page_read_user',
                    'ori_page_read_count',
                    'share_user',
                    'share_count',
                    'add_to_fav_user',
                    'add_to_fav_count',
                    'created_at'], $newsData)
                ->execute();
        }

    }

    private function _getArticleData($official_account_id,$day){

        $this->wechat = $this->getWechat($official_account_id);
        if(!$this->wechat) {
            Yii::error(sprintf("Fail to get WeChat by official_account_id at date(%s).\n", date("Y-m-d H:i:s")), __METHOD__);
        }
        $stats = $this->wechat->stats;
        $from = date("Y-m-d",strtotime("-".$day." day"));

//        $articleSummary = $stats->articleSummary($from,$to);
        $articleTotal = $stats->articleTotal($from,$from)->toArray();
        if(count($articleTotal['list'])>0){
            $data = [];
            foreach ($articleTotal['list'] as $list){
                foreach($list['details'] as $details){
                    $data[] = [
                        $official_account_id,
                        strtotime($list['ref_date']),
                        strtotime($details['stat_date']),
                        $list['msgid'],
                        $list['title'],
                        $list['user_source'],
                        $details['target_user'],
                        $details['int_page_read_user'],
                        $details['int_page_read_count'],
                        $details['ori_page_read_user'],
                        $details['ori_page_read_count'],
                        $details['share_user'],
                        $details['share_count'],
                        $details['add_to_fav_user'],
                        $details['add_to_fav_count'],
                        $details['int_page_from_session_read_user'],
                        $details['int_page_from_session_read_count'],
                        $details['int_page_from_hist_msg_read_user'],
                        $details['int_page_from_hist_msg_read_count'],
                        $details['int_page_from_feed_read_user'],
                        $details['int_page_from_feed_read_count'],
                        $details['int_page_from_friends_read_user'],
                        $details['int_page_from_friends_read_count'],
                        $details['int_page_from_other_read_user'],
                        $details['int_page_from_other_read_count'],
                        $details['feed_share_from_session_user'],
                        $details['feed_share_from_session_cnt'],
                        $details['feed_share_from_feed_user'],
                        $details['feed_share_from_feed_cnt'],
                        $details['feed_share_from_other_user'],
                        $details['feed_share_from_other_cnt'],
                        time(),
                    ];

                }
            }
            return $data;
        }
        return [];


    }

    private function _saveArticleData($data){
        if(count($data) > 0){
            Yii::$app->db->createCommand()
                ->batchInsert(StatisticNew::tableName(), [
                    'official_account_id',
                    'ref_date',
                    'stat_date',
                    'msgid',
                    'title',
                    'user_source',
                    'target_user',
                    'int_page_read_user',
                    'int_page_read_count',
                    'ori_page_read_user',
                    'ori_page_read_count',
                    'share_user',
                    'share_count',
                    'add_to_fav_user',
                    'add_to_fav_count',
                    'int_page_from_session_read_user',
                    'int_page_from_session_read_count',
                    'int_page_from_hist_msg_read_user',
                    'int_page_from_hist_msg_read_count',
                    'int_page_from_feed_read_user',
                    'int_page_from_feed_read_count',
                    'int_page_from_friends_read_user',
                    'int_page_from_friends_read_count',
                    'int_page_from_other_read_user',
                    'int_page_from_other_read_count',
                    'feed_share_from_session_user',
                    'feed_share_from_session_cnt',
                    'feed_share_from_feed_user',
                    'feed_share_from_feed_cnt',
                    'feed_share_from_other_user',
                    'feed_share_from_other_cnt',
                    'created_at'], $data)
                ->execute();
        }

    }

    private function _checkArticleData($official_account_id){
        return true;
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $yesterday_7 = strtotime(date("Y-m-d",strtotime("-7 day")));

        $model = new StatisticNew();
        $res = $model->find()->where(['ref_date'=>$yesterday_7,'official_account_id'=>$official_account_id])->asArray()->one();
        if(!$res){
            for($i=1;$i<=15;$i++) {
                $articleData = $this->_getArticleData($official_account_id,$i);

                $this->_saveArticleData($articleData);
            }
        }else{
            $res = $model->find()->where(['ref_date'=>$yesterday,'official_account_id'=>$official_account_id])->asArray()->one();
            if(!$res){
                $FansData = $this->_getArticleData($official_account_id,1);
                $this->_saveArticleData($FansData);
            }
        }

    }



}
