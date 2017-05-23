<?php

// 更新1.0版本的用户权限脚本

$dbhost = '127.0.0.1:3306';
$dbuser = 'root';
$dbpass = '#sCWeC^2';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);

mysql_select_db('wx_admin_platform');

if(! $conn ) {
    die('Could not connect: ' . mysql_error());
}

// TODO 待完善，直接手动改吧

mysql_close($conn);

?>