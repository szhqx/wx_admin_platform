<?php

return [

    'vendorPath' => dirname(dirname(__DIR__)).'/vendor',

    'runtimePath' => '@root/runtime',

    'timezone' => 'PRC',

    'language' => 'zh-CN',

    'bootstrap' => ['log'],

    'components' => [

        'cache' => [
            'class' => 'yii\caching\FileCache'
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_POST', '_GET'],
                    'logFile' => env('LOG_FILE_PATH', '@root/runtime/logs/app.log'),
                    'except' => ['yii\db\*', 'console\controllers\*', 'common\helpers\Cron*'],
                    'enableRotation' => TRUE,
                    'maxFileSize' => 1000000 // 最大500M
                    // 'flushInterval' => 100,   // default is 1000，貌似字段有问题，待查证
                ],
            ],
        ],

        'db' => [

            'class' => 'yii\db\Connection',
            'dsn' => env('DB_DSN'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'tablePrefix' => env('DB_TABLE_PREFIX'),

            // 'enableSchemaCache' => true,

            // // Duration of schema cache.
            // 'schemaCacheDuration' => 3600,

            // // Name of the cache component used to store schema information
            // 'schemaCache' => 'cache',

            // 'initSQLs'=>'SET NAMES utf8mb4;'

            'on afterOpen' => function($event) {
                $event->sender->createCommand("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;")->execute();
            }
        ],

        'beanstalk'=>[
            'class' => 'udokmeci\yii2beanstalk\Beanstalk',
            'host'=> "127.0.0.1", // default host
            'port'=>11300, //default port
            'connectTimeout'=> 1,
            'sleep' => false, // or int for usleep after every job
        ],
    ],

];