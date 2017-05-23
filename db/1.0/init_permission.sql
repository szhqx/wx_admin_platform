--
-- Dumping data for table `authority_permission`
--

DROP TABLE IF EXISTS `authority_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authority_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `display_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text COLLATE utf8_unicode_ci COMMENT '权限描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `authority_permission` WRITE;
/*!40000 ALTER TABLE `authority_permission` DISABLE KEYS */;
INSERT INTO `authority_permission` (`name`, `display_name`, `description`, `status`, `created_at`, `updated_at`) VALUES

('official-account/create','添加公众号',NULL,1,NULL,NULL),
('official-account/modify','修改公众号',NULL,1,NULL,NULL),
('official-account/delete','删除公众号',NULL,1,NULL,NULL),
('excel/export','导出公众号',NULL,1,NULL,NULL),
('excel/import','批量导入公众号',NULL,1,NULL,NULL),
('official-account/info','获取单个公众号信息',NULL,1,NULL,NULL),
('official-account/info-list','获取公众号列表',NULL,1,NULL,NULL),

('message/save-to-material','消息保存为素材',NULL,1,NULL,NULL),
('message/response','回复消息',NULL,1,NULL,NULL),
('message/collect','收藏消息',NULL,1,NULL,NULL),
('message/get-list','获取消息列表',NULL,1,NULL,NULL),

('reply/create','添加自动回复',NULL,1,NULL,NULL),
('reply/delete','删除自动回复',NULL,1,NULL,NULL),
('reply/update','更新自动回复',NULL,1,NULL,NULL),
('reply/get','获取自动回复信息',NULL,1,NULL,NULL),
('reply/get-list','获取自动回复列表',NULL,1,NULL,NULL),
('reply/info','获取单个自动回复信息',NULL,1,NULL,NULL),

('fans/tagging','粉丝打标签',NULL,1,NULL,NULL),
('fans/untagging','删除粉丝标签',NULL,1,NULL,NULL),
('fans/move-fans-to-group','加入黑名单',NULL,1,NULL,NULL),
('fans/mark','修改粉丝备注名称',NULL,1,NULL,NULL),
('fans/get-list','获取粉丝列表',NULL,1,NULL,NULL),
('fans/sync','同步粉丝数据',NULL,1,NULL,NULL),
('fans/create-tag','添加标签',NULL,1,NULL,NULL),
('fans/update-tag','修改标签',NULL,1,NULL,NULL),
('fans/delete-tag','删除标签',NULL,1,NULL,NULL),
('fans/get-tag-list','获取标签列表',NULL,1,NULL,NULL),

('menus/add','添加菜单',NULL,1,NULL,NULL),
('menus/update','更新菜单',NULL,1,NULL,NULL),
('menus/delete','删除菜单',NULL,1,NULL,NULL),
('menus/send-menu','同步菜单',NULL,1,NULL,NULL),
('menus/get-list','获取菜单列表',NULL,1,NULL,NULL),

('mass/modify','编辑定时群发',NULL,1,NULL,NULL),
('mass/delete','删除定时群发',NULL,1,NULL,NULL),
('mass/create','创建定时群发',NULL,1,NULL,NULL),
('mass/delete-send','删除已群发',NULL,1,NULL,NULL),
('mass/info-list', '获取群发排期列表',NULL,1,NULL,NULL),
('mass/get-send-list', '获取已群发列表',NULL,1,NULL,NULL),

('material/create', '添加素材',NULL,1,NULL,NULL),
('material/delete', '删除素材',NULL,1,NULL,NULL),
('material/modify', '编辑素材',NULL,1,NULL,NULL),
('material/info-list', '获取素材列表',NULL,1,NULL,NULL),
('material/info', '获取单个素材信息',NULL,1,NULL,NULL),
('material/sync', '同步素材',NULL,1,NULL,NULL),

('account/create', '添加用户',NULL,1,NULL,NULL),
('account/modify', '编辑用户',NULL,1,NULL,NULL),
('account/delete', '删除用户',NULL,1,NULL,NULL),
('account/info', '获取用户信息',NULL,1,NULL,NULL),
('account/info-list', '获取用户列表',NULL,1,NULL,NULL),

('authority/create-role', '添加角色',NULL,1,NULL,NULL),
('authority/modify-role', '编辑角色',NULL,1,NULL,NULL),
('authority/delete-role', '删除角色',NULL,1,NULL,NULL),
('authority/get-role-list', '获取角色列表',NULL,1,NULL,NULL),
('authority/get-permission-list', '获取权限列表',NULL,1,NULL,NULL),
('authority/get-role-permission', '获取权限信息',NULL,1,NULL,NULL),

('manager-log/info-list', '获取日志列表',NULL,1,NULL,NULL),

('announcement/create', '增加公告', NULL,1,NULL,NULL),
('announcement/delete', '删除公告', NULL,1,NULL,NULL),
('announcement/update', '更新公告', NULL,1,NULL,NULL),
('announcement/announcement-list', '获取公告列表', NULL,1,NULL,NULL),
('announcement/most-new-announcement', '获取最新公告', NULL,1,NULL,NULL);

/*!40000 ALTER TABLE `authority_permission` ENABLE KEYS */;
-- UNLOCK TABLES;
