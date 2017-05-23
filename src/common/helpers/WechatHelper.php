<?php

namespace common\helpers;

use EasyWeChat\Foundation\Application as WeChat;

use common\models\OfficialAccount;

class WechatHelper {

    public static function getWechat($official_account_id)
    {

        $official_account = OfficialAccount::findById($official_account_id);
        if(!$official_account) {
            return NULL;
        }

        // TODO 添加default的options，具体参考：https://easywechat.org/zh-cn/docs/configuration.html

        $options = [
            "app_id"=>$official_account['app_id'],
            "secret"=>$official_account['app_secret'],

            /**
             * Guzzle 全局设置
             *
             * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
             */
            'guzzle' => [
                'timeout' => 180.0, // 超时时间（秒）
                //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
            ],
        ];

        $wechat = new WeChat($options);

        return $wechat;
    }

}
