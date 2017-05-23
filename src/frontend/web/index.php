<?php

require __DIR__.'/../../vendor/autoload.php';

if (is_file(__DIR__ . '/../../.env')) {
    (new \Dotenv\Dotenv(dirname(dirname(__DIR__))))->load();
}

defined('YII_DEBUG') or define('YII_DEBUG', env('YII_DEBUG', true));
defined('YII_ENV') or define('YII_ENV', env('YII_ENV', 'dev'));
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', env('YII_ENABLE_ERROR_HANDLER', true));

require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__.'/../../common/config/main.php'),
    require(__DIR__.'/../../common/config/main-local.php'),
    require(__DIR__.'/../config/main.php'),
    require(__DIR__.'/../config/main-local.php')
);

(new yii\web\Application($config))->run();
