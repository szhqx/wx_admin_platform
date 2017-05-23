<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
        ],

        'user' => [
            'identityClass' => 'app\models\User',
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

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
                    'maxFileSize' => 1000000 // 最大500m
                    // 'flushinterval' => 100,   // default is 1000，貌似字段有问题，待查证
                ],
            ],
        ],

        // 'authManager' => [
        //     'class' => 'app\controllers\DbManager',
        //     // 'defaultRoles' => ['guest'],
        // ],

        'exAuthManager' => [
            'class' => 'app\helpers\ExAuthManager'
        ],

        'db' => require(__DIR__ . '/db.php'),

        /*
          'urlManager' => [
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'rules' => [
          ],
          ],
        */
    ],

    'params' => $params,
];


if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}


return $config;
