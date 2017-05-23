<?php

$params = array_merge(
    require(__DIR__.'/../../common/config/params.php'),
    require(__DIR__.'/../../common/config/params-local.php'),
    require(__DIR__.'/params.php'),
    require(__DIR__.'/params-local.php')
);

return [

    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',

    'controllerMap' => [
        'serve' => [
            'class' => 'yii\console\controllers\ServeController',
            'docroot' => "@frontend" . '/web'
        ],
        'worker'=>[
            'class' => 'console\controllers\WorkerController',
        ],
        'workerNews'=>[
            'class' => 'console\controllers\WorkerNewsController',
        ]
    ],


    'components' => [

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,   // default is 1000，方便debug，生产环境建议修改成其他值
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_POST', '_GET'],
                    'logFile' => env('LOG_CRON_FILE_PATH', '@root/runtime/logs/cron.log'),
                    'categories' => ['console\controllers\*', 'common\helpers\Cron*', 'udokmeci.beanstalkd'],
                    'enableRotation' => TRUE,
                    'maxFileSize' => 10240, // 最大500M
                    'exportInterval' => 1
                ],
            ],
        ],

        // 'urlManager' => [
        //     'baseUrl' => env('HOST_BASE_URL', ''),
        //     'hostInfo' => env('e','e'),
        //     'scriptUrl' => env('index.php','e')
        // ]

    ],

//    'beanstalk'=>[
//        'class' => 'udokmeci\yii2beanstalk\Beanstalk',
//        'host'=> "127.0.0.1", // default host
//        'port'=>11300, //default port
//        'connectTimeout'=> 1,
//        'sleep' => false, // or int for usleep after every job
//    ],

    'aliases' => [
    ],

    'params' => $params,

    'language' => 'en',
    'sourceLanguage' => 'en'
];
