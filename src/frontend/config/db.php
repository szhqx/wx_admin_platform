<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => env('DB_DSN','mysql:host=127.0.0.1;dbname=wx_admin_platform'),
    'username' => env('DB_USERNAME','root'),
    'password' => env('DB_PASSWORD', '123'),
    'tablePrefix' => env('DB_TABLE_PREFIX', ''),
    'charset' => 'utf8',
];