<?php

$params = array_merge(
    require(__DIR__.'/../../common/config/params.php'),
    require(__DIR__.'/params.php')
);

return [

    'id' => 'app-frontend',

    'basePath' => dirname(__DIR__),

    'bootstrap' => [
        'log'
    ],

    'controllerNamespace' => 'frontend\controllers',

    'components' => [

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '9Rva_UoXl236GFEfA_jZNZbRI31OjN5t',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '/mnt/wx_admin_platform/cache'
        ],

        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'loginUrl'=>'user/login',
            'identityCookie' => [ // <---- here!
                'name' => '_identity',
                'httpOnly' => false,
            ],
        ],

        'session' => [
            'cookieParams' => [
                'httpOnly' => false,
            ],
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'log' => [
            'tracelevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_POST', '_GET'],
                    'logFile' => env('LOG_FILE_PATH', '@root/runtime/logs/app.log'),
                    'except' => ['yii\db\*', 'console\controllers\*'],
                    'enableRotation' => TRUE,
                    'maxFileSize' => 10240 // 最大500m
                    // 'flushinterval' => 100,   // default is 1000，貌似字段有问题，待查证
                ],
            ],
        ],

        'exAuthManager' => [
            'class' => 'common\helpers\ExAuthManager'
        ],

        'dataAuthManager' => [
            'class' => 'common\helpers\DataAuthManager'
        ],

        'db' => require(__DIR__ . '/db.php'),

    ],

    'language' => 'en',
    'sourceLanguage' => 'en',

    'params' => $params,
];