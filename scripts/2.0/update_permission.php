<?php

$dbhost = '127.0.0.1:3306';
$dbuser = 'root';
$dbpass = '#sCWeC^2';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);

mysql_set_charset('utf8',$conn);

mysql_select_db('wx_admin_platform');

if(! $conn ) {
    die('Could not connect: ' . mysql_error());
}

// clear
$sql = 'delete from authority_permission;';
mysql_query($sql, $conn );
echo "Done to clear data\n";

$sql = 'ALTER TABLE authority_permission AUTO_INCREMENT = 1;';
mysql_query($sql, $conn );
echo "Done to reset index\n";

$sql = <<<EOT

insert  into `authority_permission`(`name`,`display_name`,`description`, `status`, `created_at`,`updated_at`) values

('official-account/create','添加公众号',NULL, 1, NULL, NULL),
('official-account/modify','修改公众号',NULL, 1, NULL, NULL),
('official-account/delete','删除公众号',NULL, 1, NULL, NULL),
('official-account/info','获取公众号单个信息',NULL, 1, NULL, NULL),
('official-account/info-list','获取公众号列表信息',NULL, 1, NULL, NULL),

('excel/import','批量导入公众号',NULL, 1, NULL, NULL),
('excel/export','批量导出公众号',NULL, 1, NULL, NULL),

('official-group/create','创建公众号分组',NULL, 1,NULL,NULL),
('official-group/modify','修改公众号分组',NULL, 1,NULL,NULL),
('official-group/move','把公众号移动到分组',NULL, 1,NULL,NULL),
('official-group/delete','删除一个分组',NULL, 1,NULL,NULL),
('official-group/info-list','查看公众号分组列表',NULL, 1,NULL,NULL),

('message/get-list','获取消息列表',NULL, 1,NULL,NULL),
('message/response','消息回复',NULL, 1,NULL,NULL),
('message/collect','消息收藏',NULL, 1,NULL,NULL),
('message/save-to-material','消息保存为素材',NULL, 1,NULL,NULL),

('reply/create','添加自动回复',NULL, 1,NULL,NULL),
('reply/delete','删除自动回复',NULL, 1,NULL,NULL),
('reply/update','更新自动回复',NULL, 1,NULL,NULL),
('reply/get-list','获取自动回复列表',NULL, 1,NULL,NULL),
('reply/info','获取单个自动回复信息',NULL, 1,NULL,NULL),

('fans/get-list','获取粉丝列表',NULL, 1,NULL,NULL),
('fans/sync','同步粉丝数据',NULL, 1,NULL,NULL),
('fans/create-tag','添加标签',NULL, 1,NULL,NULL),
('fans/update-tag','修改标签',NULL, 1,NULL,NULL),
('fans/delete-tag','删除标签',NULL, 1,NULL,NULL),
('fans/get-tag-list','获取标签列表',NULL, 1,NULL,NULL),
('fans/mark','修改粉丝备注名称',NULL, 1,NULL,NULL),
('fans/tagging','粉丝打标签',NULL, 1,NULL,NULL),
('fans/un-tagging','删除粉丝标签',NULL, 1,NULL,NULL),
('fans/move-fans-to-group','把粉丝移动到分组',NULL, 1,NULL,NULL),
('fans/create-group','创建分组',NULL, 1,NULL,NULL),
('fans/update-group','更新分组',NULL, 1,NULL,NULL),
('fans/delete-group','删除分组',NULL, 1,NULL,NULL),
('fans/get-grou-list','获取分组列表',NULL, 1,NULL,NULL),
('fans/block','将粉丝加入黑名单',NULL, 1,NULL,NULL),

('menus/create','添加菜单',NULL, 1,NULL,NULL),
('menus/modify','更新菜单',NULL, 1,NULL,NULL),
('menus/delete','删除菜单',NULL, 1,NULL,NULL),
('menus/get-list','获取菜单列表',NULL, 1,NULL,NULL),

('mass/modify','调整群发',NULL, 1,NULL,NULL),
('mass/delete','删除定时群发',NULL, 1,NULL,NULL),
('mass/create','创建定时群发',NULL, 1,NULL,NULL),
('mass/delete-send','删除已群发',NULL, 1,NULL,NULL),
('mass/info-list','获取群发排期列表',NULL, 1,NULL,NULL),
('mass/get-send-list','获取已群发列表',NULL, 1,NULL,NULL),

('material/create','添加素材（本地/微信）',NULL, 1,NULL,NULL),
('material/delete','删除素材（本地/微信）',NULL, 1,NULL,NULL),
('material/modify','编辑素材（本地/微信）',NULL, 1,NULL,NULL),
('material/preview','预览（定时群发/微信素材）',NULL, 1,NULL,NULL),
('material/uplaod-article-image','上传图文素材的图片',NULL, 1,NULL,NULL),
('material/sync-single','同步单个素材',NULL, 1,NULL,NULL),

('account/create','创建用户',NULL, 1,NULL,NULL),
('account/modify','编辑用户',NULL, 1,NULL,NULL),
('account/delete','删除用户',NULL, 1,NULL,NULL),
('account/info','获取用户信息',NULL, 1,NULL,NULL),
('account/info-list','获取用户列表',NULL, 1,NULL,NULL),

('authority/create-role','添加角色',NULL, 1,NULL,NULL),
('authority/modify-role','编辑角色',NULL, 1,NULL,NULL),
('authority/delete-role','删除角色',NULL, 1,NULL,NULL),
('authority/get-role-list','获取角色列表',NULL, 1,NULL,NULL),
('authority/get-permission-list','获取权限列表',NULL, 1,NULL,NULL),
('authority/info','查看单个角色信息',NULL, 1,NULL,NULL),
('authority/get-role-level-list','拉取多级权限列表',NULL, 1,NULL,NULL),
('authority/get-subordinate-list','拉取下属列表',NULL, 1,NULL,NULL),

('manager-log/info-list','获取日志列表',NULL, 1,NULL,NULL),

('announcement/create','增加公告',NULL, 1,NULL,NULL),
('announcement/delete','删除公告',NULL, 1,NULL,NULL),
('announcement/update','更新公告',NULL, 1,NULL,NULL),
('announcement/announcement-list','获取公告列表',NULL, 1,NULL,NULL),
('announcement/most-new-announcement','获取最新公告',NULL, 1,NULL,NULL),

('advertise/get-list','查看广告订单列表',NULL, 1,NULL,NULL),
('advertise/add-order','添加广告订单',NULL, 1,NULL,NULL),
('advertise/modify-order','修改广告订单',NULL, 1,NULL,NULL),
('advertise/delete-order','删除广告订单',NULL, 1,NULL,NULL),

('finance/teller-list','拉取出纳流水列表',NULL, 1,NULL,NULL),
('finance/add-teller','添加出纳流水',NULL, 1,NULL,NULL),
('finance/modify-teller','修改出纳流水',NULL, 1,NULL,NULL),
('finance/delete-teller','删除出纳流水',NULL, 1,NULL,NULL),
('finance/ad-income','广告收入',NULL, 1,NULL,NULL),
('finance/income-chart','广告收入图表',NULL, 1,NULL,NULL),
('finance/cate-income-chart','分类收入汇总',NULL, 1,NULL,NULL),
('finance/official-income-chart','公众号当月收入汇总',NULL, 1,NULL,NULL),

('statics/get-fans-data','获取用户分析',NULL, 1,NULL,NULL),
('statics/get-news-data','获取图文分析',NULL, 1,NULL,NULL),
('statics/export-news-data','导出图文分析',NULL, 1,NULL,NULL),
('statics/export-user-data','导出用户分析',NULL, 1,NULL,NULL);

EOT;

$retval = mysql_query( $sql, $conn );

if(! $retval ) {
    die('Could not enter data: ' . mysql_error());
}

echo "Entered data successfully\n";

mysql_close($conn);

?>