/*
SQLyog Ultimate v12.08 (64 bit)
MySQL - 5.5.53 : Database - wx_admin_platform
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`wx_admin_platform` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `wx_admin_platform`;

/*Table structure for table `analysis` */

DROP TABLE IF EXISTS `analysis`;

CREATE TABLE `analysis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `analysis` */

/*Table structure for table `announcement` */

DROP TABLE IF EXISTS `announcement`;

CREATE TABLE `announcement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发起人',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '标题',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '公告内容',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否置顶，0代表不置顶，1代表置顶',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `announcement` */

insert  into `announcement`(`id`,`user_id`,`title`,`content`,`is_top`,`created_at`,`updated_at`) values (5,1,'标题','gasdgasgd',0,1482677192,0),(8,1,'标题','测试发布内容：201612311740',0,1483177138,0),(9,1,'标题','哈哈',0,1483190422,0),(10,36,'标题','fagaga',0,1484122627,0);

/*Table structure for table `article` */

DROP TABLE IF EXISTS `article`;

CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `mass_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章标题',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->图文, 2->图片, 3->声音, 4->视频，5->封面照片，6->文章照片',
  `description` varchar(360) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '摘要',
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `cover_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '封面url',
  `source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '内容连接资源，默认是阿里云的',
  `weixin_source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '内容连接资源，微信返回的',
  `ad_source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推广链接',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `author` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '发布内容的作者',
  `official_account_id` int(10) DEFAULT '0' COMMENT '所属公众号id',
  `material_id` int(10) unsigned NOT NULL,
  `show_cover_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表不显示，1代表显示',
  `is_legal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表违规，1代表不违规',
  `order` smallint(4) NOT NULL DEFAULT '0' COMMENT '多图文素材的时候使用，代表文章的顺序，从0开始',
  `msg_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_id',
  `msg_data_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信端返回的msg_data_id；如果是多图文，会叠上order',
  `add_to_fav_count` int(10) unsigned NOT NULL DEFAULT '0',
  `int_page_read_count` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布的时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `article` */

/*Table structure for table `authority_permission` */

DROP TABLE IF EXISTS `authority_permission`;

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
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `authority_permission` */

insert  into `authority_permission`(`id`,`name`,`display_name`,`description`,`status`,`created_at`,`updated_at`) values (27,'official-account/create','添加公众号',NULL,1,NULL,NULL),(28,'official-account/modify','修改公众号',NULL,1,NULL,NULL),(29,'official-account/delete','删除公众号',NULL,1,NULL,NULL),(30,'excel/export','导出公众号',NULL,1,NULL,NULL),(31,'excel/import','批量导入公众号',NULL,1,NULL,NULL),(32,'official-account/info','获取单个公众号信息',NULL,1,NULL,NULL),(33,'official-account/info-list','获取公众号列表',NULL,1,NULL,NULL),(34,'message/save-to-material','消息保存为素材',NULL,1,NULL,NULL),(35,'message/response','回复消息',NULL,1,NULL,NULL),(36,'message/collect','收藏消息',NULL,1,NULL,NULL),(37,'message/get-list','获取消息列表',NULL,1,NULL,NULL),(38,'reply/create','添加自动回复',NULL,1,NULL,NULL),(39,'reply/delete','删除自动回复',NULL,1,NULL,NULL),(40,'reply/update','更新自动回复',NULL,1,NULL,NULL),(41,'reply/get-list','获取自动回复列表',NULL,0,NULL,NULL),(42,'reply/info','获取单个自动回复信息',NULL,0,NULL,NULL),(43,'reply/get','获取自动回复信息',NULL,1,NULL,NULL),(44,'fans/tagging','粉丝打标签',NULL,1,NULL,NULL),(45,'fans/untagging','删除粉丝标签',NULL,1,NULL,NULL),(46,'fans/move-fans-to-group','加入黑名单',NULL,1,NULL,NULL),(47,'fans/mark','修改粉丝备注名称',NULL,1,NULL,NULL),(48,'fans/get-list','获取粉丝列表',NULL,1,NULL,NULL),(49,'fans/sync','同步粉丝数据',NULL,1,NULL,NULL),(50,'fans/create-tag','添加标签',NULL,1,NULL,NULL),(51,'fans/update-tag','修改标签',NULL,1,NULL,NULL),(52,'fans/delete-tag','删除标签',NULL,1,NULL,NULL),(53,'fans/get-tag-list','获取标签列表',NULL,1,NULL,NULL),(54,'menus/add','添加菜单',NULL,1,NULL,NULL),(55,'menus/update','更新菜单',NULL,1,NULL,NULL),(56,'menus/delete','删除菜单',NULL,1,NULL,NULL),(57,'menus/send-menu','同步菜单',NULL,0,NULL,NULL),(58,'menus/get-list','获取菜单列表',NULL,0,NULL,NULL),(59,'mass/modify','编辑定时群发',NULL,1,NULL,NULL),(60,'mass/delete','删除定时群发',NULL,1,NULL,NULL),(61,'mass/create','创建定时群发',NULL,1,NULL,NULL),(62,'mass/delete-send','删除已群发',NULL,1,NULL,NULL),(63,'mass/info-list','获取群发排期列表',NULL,0,NULL,NULL),(64,'mass/get-send-list','获取已群发列表',NULL,0,NULL,NULL),(65,'material/create','添加素材',NULL,1,NULL,NULL),(66,'material/delete','删除素材',NULL,1,NULL,NULL),(67,'material/modify','编辑素材',NULL,1,NULL,NULL),(68,'material/info-list','获取素材列表',NULL,0,NULL,NULL),(69,'material/info','获取单个素材信息',NULL,0,NULL,NULL),(70,'account/create','添加用户',NULL,1,NULL,NULL),(71,'account/modify','编辑用户',NULL,1,NULL,NULL),(72,'account/delete','删除用户',NULL,1,NULL,NULL),(73,'account/info','获取用户信息',NULL,0,NULL,NULL),(74,'account/info-list','获取用户列表',NULL,0,NULL,NULL),(75,'authority/create-role','添加角色',NULL,1,NULL,NULL),(76,'authority/modify-role','编辑角色',NULL,1,NULL,NULL),(77,'authority/delete-role','删除角色',NULL,1,NULL,NULL),(78,'authority/get-role-list','获取角色列表',NULL,0,NULL,NULL),(79,'authority/get-permission-list','获取权限列表',NULL,0,NULL,NULL),(80,'authority/get-role-permission','获取权限信息',NULL,1,NULL,NULL),(81,'manager-log/info-list','获取日志列表',NULL,1,NULL,NULL),(82,'announcement/create','增加公告',NULL,1,NULL,NULL),(83,'announcement/delete','删除公告',NULL,1,NULL,NULL),(84,'announcement/update','更新公告',NULL,1,NULL,NULL),(85,'announcement/announcement-list','获取公告列表',NULL,0,NULL,NULL),(86,'announcement/most-new-announcement','获取最新公告',NULL,0,NULL,NULL);

/*Table structure for table `authority_role` */

DROP TABLE IF EXISTS `authority_role`;

CREATE TABLE `authority_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8_unicode_ci COMMENT '角色描述',
  `company_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_super_admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否超级管理员，0代表否，1代表是',
  `permission_id_list` mediumtext COLLATE utf8_unicode_ci COMMENT '角色权限id列表，json化',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_company` (`name`,`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `authority_role` */

insert  into `authority_role`(`id`,`name`,`description`,`company_id`,`is_super_admin`,`permission_id_list`,`status`,`created_at`,`updated_at`) values (1,'超级管理员','描述描述',1,1,'[1,2]',1,NULL,NULL),(2,'11111111','111221212',1,0,'[2,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26]',1,NULL,NULL),(3,'尼玛角色','尼玛角色',1,0,'[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26]',1,NULL,NULL),(4,'阿斯顿发斯蒂芬','是的飞洒发',1,0,'[27,28,29,30,31,32,33,34,35,36,37,38,39,40,43,44,45,46,47,48,49,50,51,52,53,54,55,56,59,60,61,62,65,66,67,70,71,72,76,77,80,81,82,83,84,75]',1,NULL,NULL),(5,'超级管理员','超级管理员',3,0,'[27,28,29,30,31,32,33,34,35,36,37,38,39,40,43,44,45,46,47,48,49,50,51,52,53,54,55,56,59,60,61,62,65,66,67,70,71,72,75,76,77,80,81,82,83,84]',1,NULL,NULL),(6,'fqfq','qfggqgq',1,0,'[27,28,29,30,32,33,34,35,36,37,38,39,40,44,45,46,47,48,49,50,51,52,53,54,55,56,59,60,61,62,65,66,67,70,71,72,75,76,77,80,81,82,83,84]',1,NULL,NULL),(9,'超级管理员','超级管理员',6,1,'1',1,1484279625,NULL),(10,'超级管理员','超级管理员',7,1,'1',1,1484968143,NULL),(11,'超级管理员','超级管理员',8,1,'[1,2]',1,1484970563,NULL);

/*Table structure for table `company` */

DROP TABLE IF EXISTS `company`;

CREATE TABLE `company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '公司名字',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `login_time` int(15) DEFAULT NULL COMMENT '登陆时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `company` */

insert  into `company`(`id`,`name`,`description`,`status`,`login_time`,`created_at`,`updated_at`) values (1,'十点信息科技有限公司','微信业务相关',1,NULL,0,0),(2,'name','test123',1,NULL,1483430425,0),(3,'微众信息科技有限公司','微众信息科技有限公',1,NULL,1483929457,0),(6,'test001','15080319027',1,2147483647,1484279625,0),(7,'深圳微播信息技术有限公司','15919994697',1,1484963683,1484968143,0),(8,'深圳微播信息技术有限公司1','15919994691',1,1484963683,1484970563,0);

/*Table structure for table `fans` */

DROP TABLE IF EXISTS `fans`;

CREATE TABLE `fans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `open_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'OPENID',
  `nickname` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '签名',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `sex` tinyint(1) DEFAULT '0' COMMENT '性别，1代表男，2代表女',
  `language` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '语言',
  `city` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '城市',
  `province` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '省',
  `country` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '国家',
  `avator` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
  `unionid` int(10) unsigned DEFAULT '0' COMMENT 'unionid',
  `liveness` int(10) unsigned DEFAULT '0' COMMENT '用户活跃度',
  `subscribed_at` int(10) NOT NULL DEFAULT '0' COMMENT '关注时间',
  `last_online_at` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次在线时间',
  `status` smallint(1) NOT NULL DEFAULT '1' COMMENT '0代表删除，1代表正常',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `tagid_list` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '标签数组（序列号存储）',
  `is_syc` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未同步 1已同步',
  `mark_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '微信备注名',
  `is_subscribe` tinyint(3) DEFAULT '1' COMMENT '是否关注',
  PRIMARY KEY (`id`),
  KEY `indexs` (`account_id`,`is_syc`,`open_id`,`nickname`,`status`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `fans` */

/*Table structure for table `fans_group` */

DROP TABLE IF EXISTS `fans_group`;

CREATE TABLE `fans_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `fans_id` int(11) DEFAULT '0',
  `open_id` varchar(120) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `wechat_group_id` int(11) DEFAULT '0' COMMENT '微信端Id',
  `wechat_group_name` varchar(200) DEFAULT '' COMMENT '微信端分组名',
  `wechat_group_count` int(20) DEFAULT '0' COMMENT '微信端分组数',
  `account_id` int(11) DEFAULT '0' COMMENT '所属公众号',
  PRIMARY KEY (`id`),
  KEY `fans_id` (`name`,`fans_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `fans_group` */

/*Table structure for table `fans_tag` */

DROP TABLE IF EXISTS `fans_tag`;

CREATE TABLE `fans_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签名称',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'token',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `wechat_tag_id` int(11) DEFAULT '0' COMMENT '微信端Id',
  `wechat_tag_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '微信端名',
  `wechat_tag_count` int(20) DEFAULT '0' COMMENT '微信端数',
  `is_sync` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indexs` (`official_account_id`,`is_sync`,`title`,`wechat_tag_id`,`wechat_tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `fans_tag` */

/*Table structure for table `fans_tag_map` */

DROP TABLE IF EXISTS `fans_tag_map`;

CREATE TABLE `fans_tag_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) DEFAULT NULL COMMENT 'uid',
  `tag_id` int(10) DEFAULT NULL COMMENT 'tag_id',
  `is_sync` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `fans_tag_map` */

/*Table structure for table `manager_log` */

DROP TABLE IF EXISTS `manager_log`;

CREATE TABLE `manager_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `official_account_id` int(10) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `created_at` int(10) NOT NULL,
  `ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(11) NOT NULL COMMENT '公司id',
  PRIMARY KEY (`id`),
  KEY `indexs` (`official_account_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `manager_log` */

insert  into `manager_log`(`id`,`user_id`,`official_account_id`,`description`,`created_at`,`ip`,`company_id`) values (1,1,0,'用户登录',1484271830,'127.0.0.1',1),(2,1,0,'用户登录',1484272237,'127.0.0.1',1),(3,1,0,'用户登录',1484705255,'127.0.0.1',1),(4,1,12,'给粉丝打标签',1484712044,'127.0.0.1',1),(5,1,12,'给粉丝打标签',1484721332,'127.0.0.1',1),(6,1,0,'用户登录',1484791783,'127.0.0.1',1),(7,1,0,'用户登录',1484881206,'127.0.0.1',1),(8,1,0,'用户登录',1484962378,'127.0.0.1',1),(9,1,0,'用户登录',1484988689,'127.0.0.1',1),(10,1,0,'用户登录',1486172978,'127.0.0.1',1),(11,1,0,'用户登录',1486372822,'127.0.0.1',1),(12,1,0,'用户登录',1486433538,'127.0.0.1',1);

/*Table structure for table `mass` */

DROP TABLE IF EXISTS `mass`;

CREATE TABLE `mass` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(10) unsigned NOT NULL,
  `official_account_id` int(10) unsigned NOT NULL,
  `pub_at` int(10) NOT NULL DEFAULT '0' COMMENT '群发的时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建mass的用户',
  `user_tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收的用户组id',
  `user_sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接收的用户组性别，0代表未设置，1代表男，2代表女',
  `user_area` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '接收用户的区域',
  `msg_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_id',
  `msg_data_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_data_id，多图文类型才会有',
  `msg_status` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息发送状态，微信端返回',
  `fail_times` smallint(4) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `mass` */

/*Table structure for table `material` */

DROP TABLE IF EXISTS `material`;

CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) DEFAULT '0' COMMENT '所属公众号id',
  `media_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'media_id，可以是图文素材的，也可以是图片的，等等',
  `original_id` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '原始微信素材id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->article, 2->image, 3->voice, 4->video, 5->text',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '标题',
  `description` varchar(360) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '摘要',
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `cover_media_id` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '封面 media_id',
  `cover_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '封面url',
  `weixin_cover_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `author` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '发布的文章作者',
  `created_from` tinyint(1) DEFAULT '0' COMMENT '0微信同步到server，server同步到微信',
  `source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '内容连接资源，默认是阿里云的',
  `weixin_source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '内容连接资源，微信返回的',
  `ad_source_url` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推广链接',
  `show_cover_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表不显示，1代表显示',
  `is_legal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表违反规定，1代表不违反',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0代表未完成，1代表已完成',
  `order` smallint(4) NOT NULL DEFAULT '0' COMMENT '多图文素材的时候使用，代表文章的顺序，从0开始',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间，0为马上发布，其他为定时发送',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `fail_times` smallint(4) NOT NULL DEFAULT '0' COMMENT 'fix同步次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_official` (`media_id`,`official_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `material` */

/*Table structure for table `materials_category` */

DROP TABLE IF EXISTS `materials_category`;

CREATE TABLE `materials_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '名称',
  `display_order` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `materials_category` */

/*Table structure for table `menus` */

DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '菜单父id',
  `id_s` int(11) DEFAULT '0',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '1->click/2->view/3->scancode_push/4->scancode_waitmsg/5->pic_sysphoto/6->pic_photo_or_album/7->pic_weixin/8->location_select',
  `key` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '菜单触发值',
  `url` varchar(255) DEFAULT NULL,
  `media_id` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'media_id',
  `msg_type` varchar(30) DEFAULT '' COMMENT '消息类型',
  `sort` smallint(4) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '为text对应的值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4;

/*Data for the table `menus` */

insert  into `menus`(`id`,`official_account_id`,`parent_id`,`id_s`,`name`,`type`,`key`,`url`,`media_id`,`msg_type`,`sort`,`status`,`created_at`,`updated_at`,`value`) values (39,12,0,1,'测试','none',NULL,NULL,NULL,'0',0,0,1484989525,0,''),(40,12,1,0,'多图文测试','click','2wG1484989525',NULL,'lpE7FlFNFOL1iTHATRrEZ9Hq8QrjC_7-amY5yb2PHYk','news',0,0,1484989525,0,''),(41,12,1,0,'文本测试','click','lDO1484989525',NULL,NULL,'text',0,0,1484989525,0,'文本测试文本测试文本测试'),(42,12,1,0,'url测试','view',NULL,'http://mp.weixin.qq.com/s?__biz=MzI0NTQzNTY4OQ==&mid=100001344&idx=1&sn=d82b7bae7416711485f8b667de2a3ad5&chksm=694fda065e385310fe3dbe0226560c60d9830aef6db1cccd88ee072e6d8e56035f534399a521&scene=18#wechat_redirect',NULL,'text',0,0,1484989525,0,'文本测试文本测试文本测试'),(43,12,0,2,'图文测试','click','SHR1484989525',NULL,'lpE7FlFNFOL1iTHATRrEZ1WyJtGIRPFhBOgoYrn7xxY','news',1,0,1484989525,0,''),(44,12,0,3,'图片测试','click','GBO1484989525',NULL,'IeE9AGRVwvGyTUzL_JtkQbpiBUpx2IMvVmrdF5bfKLxY2QcYJg78iLyKPIfOcpvb','img',2,0,1484989525,0,'');

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `fans_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝id 不存在时为公众号回复',
  `msg_id` bigint(40) DEFAULT NULL,
  `is_reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已回复',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `content` text COLLATE utf8_unicode_ci COMMENT '消息内容',
  `media_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `voice_format` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '音频类型',
  `recognition` varchar(100) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '自动识别音频',
  `thumb_media_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '视频消息缩略图的媒体id',
  `is_collection` tinyint(2) DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `msg_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '消息类型',
  `imgurl` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '图片url',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `messages` */

/*Table structure for table `messages_resources` */

DROP TABLE IF EXISTS `messages_resources`;

CREATE TABLE `messages_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `detail` text COLLATE utf8_unicode_ci COMMENT '详细',
  `type` smallint(4) DEFAULT '0' COMMENT '1->text/2->image/3->voice/4->shortvideo/5->link/6->location',
  `sync_status` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `messages_resources` */

/*Table structure for table `official_account` */

DROP TABLE IF EXISTS `official_account`;

CREATE TABLE `official_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weixin_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '微信号',
  `weixin_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '微信名称',
  `weixin_password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '公众号登录密码',
  `official_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '公众号id',
  `official_origin_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '公众号原始id',
  `app_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'AppID',
  `app_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'AppSecret',
  `encoding_aes_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'EncodingAesKey',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'token',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `admin_weixin_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `admin_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `operation_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '运营主体',
  `operation_certificate_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '运营主体证件号',
  `operator_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '运营者姓名',
  `editor_id` int(11) NOT NULL DEFAULT '0' COMMENT '编辑人员',
  `auditor_id` int(11) NOT NULL DEFAULT '0' COMMENT '审核人员',
  `company_id` int(11) NOT NULL DEFAULT '0' COMMENT '公司id',
  `annual_verification_time` int(11) NOT NULL DEFAULT '0' COMMENT '年审有效期',
  `is_annual_validity` tinyint(1) NOT NULL DEFAULT '0' COMMENT '年审是否通过，0代表未通过，1代表通过',
  `attention_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `fans_num` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `is_connect` tinyint(2) DEFAULT '0' COMMENT '0未接通 1接通',
  `sync_img_status` tinyint(2) DEFAULT '0',
  `sync_news_status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `weixin_id` (`weixin_id`),
  UNIQUE KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `official_account` */

insert  into `official_account`(`id`,`weixin_id`,`weixin_name`,`weixin_password`,`official_id`,`official_origin_id`,`app_id`,`app_secret`,`encoding_aes_key`,`token`,`is_verified`,`admin_weixin_id`,`admin_email`,`operation_subject`,`operation_certificate_no`,`operator_name`,`editor_id`,`auditor_id`,`company_id`,`annual_verification_time`,`is_annual_validity`,`attention_link`,`group_id`,`fans_num`,`status`,`created_at`,`updated_at`,`is_connect`,`sync_img_status`,`sync_news_status`) values (12,'ymsw007','漫亿商务','qwe123',NULL,'gh_9c7e59ac7991','wx89efd9398453e27e','cb11c889864974bac30b6ff8b2158ba1','JlU5K7U19kT4zj51U5NuK755715Y1uNk5555JkJ0755','vYhc0PIHsT',1,'test','test@gmail.com','深圳市恒红亿漫传媒有限公司 (企业)','','',31,31,1,1482883200,0,'www.baidu.com',1,425,1,0,1483951815,1,1,0),(15,'zonghwl','众惠网络','qwe123',NULL,'gh_b658e6dff3ec','wx3d8eb4f425f731eb','082f07c4b8e89385d1e23afe61193f45','7u1ltxnciF8wMz95KJrysSeBEqXhf4oOVDA30Q','Gw5SaCvD6H',1,'','','','','',28,28,1,0,0,'',0,139,1,0,1483951815,1,1,0),(19,'yjjq678','教你怎么管教孩子','',NULL,'gh_e165b298e25b','wx79c21957c5af3b88','b88239f657bab618f011cb53c3263a4a','1aOUWKcCITVtwEDpsSf0Hgrh7Fm653djNu98iP','m8Ct0wjOD4',1,'','','','','',36,0,1,0,0,'',0,16,1,0,1484215927,1,1,0),(28,'ytxx79','唯美音悦台','mjx654321',NULL,'gh_80165604c63f','wxf7b0e871934f8aff','4ac54313fe1d936ee071be2b3af3113f','5PAFweGU6lC8NsxS1zHRjIbvK47iY2VOoQJDMn','E8FznfKrAa',0,'','','','','',0,0,1,0,0,'',0,1,1,0,1483951815,1,1,0),(29,'sanmeiqq666','围巾这么系才时髦','',NULL,'gh_cb39db927982','wxecd295002ba47dad','e3f26314cf45401418402592e7cf2522','P50XLnZ9Amahl1IMtgFuQNBrsCRxH7YV6fw4Wq','Fju782CPbE',0,'','','','','',0,0,1,0,0,'',0,15152,1,0,1483951815,1,1,0),(30,'yeqq02','好妈妈育儿小妙招','',NULL,'gh_2c2b67ff3f0a','wx1c61fb2c597b0e43','32d634204cbabc927439a5878425cc5b','1NKTCdtHnUr4RivQIbkpFmz7y6sAMeqfl32oS9','d7XRiK5n6o',0,'','','','','',0,0,1,0,0,'',0,29160,1,0,1484122480,1,1,0),(31,'SY-1588','思源在线','',NULL,'gh_63f587e9af66','wx698accfdb0cfddde','5df0bdad9bb4fed659d5f51cbf3e5b8c','JlU5K7U19kT4zj51U5NuK755715Y1uNk5555JkJ0755','s1HMBlq58Z',1,'','','','','',0,0,3,0,0,'',1,22565,1,0,1484130354,1,1,0),(32,'jxnz33','精选女装特卖Y','',NULL,'gh_15bd8bdfa479','wx8a7c6cc066775013','23b804a7116816080fd9558c38818c37','59ZqUzb8kNi4yVAYm2KwXLMISaTDvBpe7HGCoEW1g6l','qVRHDs0tIT',0,'','','ffffffff','','',0,0,3,0,0,'',0,478,0,0,1484130715,1,1,0),(33,'qsbz33','教你轻松做编织','mjx654321',NULL,'gh_2815e1089533','wx4592a72d75b75e27','3cbc5a64dff395e5dca7f2a93da457bc','mWIHJXdchfnv27TbVESMKPFGx4s5uB1ZeUCDgLar8O0','NacT16DWfI',1,'','','苏州妍鹏文化传播有限公司','','',36,0,1,0,0,'',0,3,1,0,0,1,1,0),(34,'bznn96','学会编织很简单','',NULL,'gh_c6a3c4ddf408','wx945ed643e7322587','3d2af128781102c3ab9c43c4ddb56a5a','JwfjxgzVcHtRyiOhIT6aYWAkLZNUD7PX1MverFmoqbQ','DSqVxYZiBr',0,'','','','','',0,0,1,0,0,'',0,11,1,0,0,1,1,1),(37,'亿画商务','亿画商务','ay112233',NULL,'gh_775ca6833b7f','wxc3cae3b90d691b3f','4aed0064256ec8cf91a6132b948a88db','9Be8gJM4cArnY6XFPUTklu7SbHfZN3paQ5CVoO2hR1y','ToKP8Fd9ED',1,'admin','admin','admin','','',34,0,3,0,0,'',1,282,1,0,1484125044,1,1,1),(46,'h5gamescenter','征途网络','qwe123',NULL,'gh_017520f4f82c','wx43771255a0ccd1bb','1490199c46defe6c680e72fbefd74c67','g5jPUfMq9vtLJGEaZWenOAVm6TSc3dxhQD8Flrpkobw','5f9EI4hiLl',1,'test','test','','','',1,1,3,1476201600,0,'http:://www.baidu.com',0,115,1,1484127127,0,0,1,1),(47,'ysdq69','女人养身大全','mjx654321',NULL,'gh_f5b6d0ef1c83','wxa6e4c3a2f8ca19c5','e6b6b9ab8ca493b73052532460c4d91e','Vy2ibx6pvzr7elgkaBMFtA0ZoPhWXnUKuswTI3jC15S','HluhsTiPqa',1,'','','','','',0,0,1,0,0,'',0,21,1,1484185202,1484202150,1,1,1),(48,'tf6569','每天教你编发扎头','huliang123',NULL,'gh_c1c1f12fb1cc','wx91f1db02ca2fc779','c5c9457f5255c9013ea39333485a3e4a','TuDbvKsEAlUkfotzNhRp1nX4ZFd7O8HVS2yg6CxqwjI','2lk5f6EgQi',1,'','','','','',38,0,1,0,0,'',0,26327,1,1484188246,0,0,1,0),(49,'yujia654','每日学瑜伽','',NULL,'gh_546de3cd57e1','wx27dc0fcba515b62f','2cd3534132e4284fa500cf406b83c0ce','6AENfbCFIokLX0BKxdjwzs2e9PiMHYDqWytJ3cRv1hG','TSR08YIeQL',1,'','','','','',36,0,1,0,0,'',0,10787,1,0,0,1,1,0),(50,'nrdd96','教女人会穿搭','',NULL,'gh_33b418a19770','wx43414a1a9a9e598d','72db9ef3c66237d57faf072062e2181a','0q87vdr4xYoQPVigDX2eByCw9KFcTfNJzlS6tapLRsE','K3BQyoFDXh',1,'','','','','',0,0,1,0,0,'',0,0,0,0,0,1,1,0),(51,'jnshxmz880 ','教您学会365道家常菜','mjx654321',NULL,'gh_2e7f7b628458','wx458e05eea482df92','cbf69ff4c35776c755ce14e4da5f3c97','tzAiWLs5Z4R71G2jDaUmHQnSoYxhevEPJuFOyK36cqM','TXxjGiA5LF',1,'Gioo660','小二子','','','',0,0,1,0,0,'',0,16997,1,1484207562,0,0,1,0),(52,'yxqq26','这样的妈妈很优秀','mjx654321',NULL,'gh_ae704da00ec7','wx5a8c4c99624fcd04','c3b365412a27935eca31eae7313bf822','U4c1SMQGJ9xdZEA8fy3wp6bXCrYa0uPntTL7ijzqDBW','nlMfrQvamo',1,'xlytfh','禁止自拍','','','',0,0,1,0,0,'',0,38857,1,1484207562,0,0,1,0),(53,'mf3338','3分钟轻松扎美发','mjx654321',NULL,'gh_6e900c262905','wxda05f04b6fbf882b','615dabf7491f622449ef06a94d4e093b','4C2Sqcn7TjYw1Xp8esKQA5Zav0uOlNDb6BtdVGxIh9W','XsQyFkxNm7',1,'Uc4678','小黑','','','',0,0,1,0,0,'',0,23653,1,1484207563,0,0,1,1),(54,'nvqq65','女装特卖Y','mjx654321',NULL,'gh_a2217d4f9e40','wx5648ea8a7d47aebb','0faa9b37aaffcd1f36420e19ade85a26','ApexDsrVGQz1Fwtlu2M49TJHqIRg5m7Cif83Yb6Onyj','8tCu4kyd3I',1,'lhp6960','小白','','','',0,0,1,0,0,'',0,36391,1,1484207563,0,0,1,1),(55,'hmma90','好妈妈育儿百招','mjx654321',NULL,'gh_4c48b5228ad2','wx18a5f67adbb9b193','a8f559c413c81d62164d87bb016850c1','ezkOEH8WgMNK6n9I10jiLa5FhrbRUmCZBQucfxPpwtA','XJzd6G5n9g',1,'Yd0782','小黑','','','',0,0,1,0,0,'',0,20782,1,1484207564,0,0,1,1),(56,'nztm99','秋冬女装特卖会','mjx654321',NULL,'gh_daff6fece5de','wxc33e389df90cbfef','07f21c5be9c9b5f2a38b881133401feb','hQEPtbdV5zD38vC7gwYkFRZm2GWMquNfnHcI0iyeXUj','jBh1SRkUAw',1,'Uc4678','小黑','','','',0,0,1,0,0,'',0,37417,1,1484207565,0,0,0,1),(57,'wdqq69','秋冬围巾穿搭','mjx654321',NULL,'gh_49fa9978e791','wx413d0814955d08c9','fcc25ed4e849930e4d23066657458066','NydTuDbnoicRH85LV0JXlfkmwz39a6WUFBtPpqSMrvj','NdxA7znftV',1,'Xh9680','小黑','','','',0,0,1,0,0,'',0,33527,1,1484207566,0,0,1,1),(58,'zm8546','做个好妈妈o','ggg654321',NULL,'gh_73caad7bbabe','wxc51de3716394b49d','613d21c5d25d6b5e39e2447163e4ae3b','VltiSKHcLTpIy2r9QEPd3bO5BwJFY7fkm6gq8hvRCeX','KDoNJ9wGIC',1,'gh5416','小二子','','','',0,0,1,0,0,'',0,37098,1,1484207567,0,0,1,1),(59,'wjqq56','围巾这样系显气质','mjx654321',NULL,'gh_5d61615b3f61','wxdb499f9d0831964d','4ee8484a3b26d82e8dc20563dbf6d3ad','LcTF6alZ7Q5qpGUeKVnhRw0WDrCkPm1zXs2u3fgI9yx','FlMTyD01S3',1,'Lb6478','小黑','','','',0,0,1,0,0,'',0,37104,1,1484207567,0,0,1,1),(60,'yqtt98','学盘个洋气头发','mjx654321',NULL,'gh_90c3c0ea8d85','wxa64725ba3ab46cb2','7021f1d061b6308133e87db25c8f8e2a','zlxFb9JAdkrV8a2t7wcmCeIHGBRypPTjQXM6SsLnK15','o0e1bic2WO',1,'Gb8068','小黑','','','',0,0,1,0,0,'',0,35610,1,1484207568,0,0,1,1),(61,'hzrh66','孩子如何管才好','mjx654321',NULL,'gh_6a7d20ffe6a6','wxc79ea1e6f4f76882','d9d5826e61e6ee42f92c36d7f7ebca8d','moik53fBZvSjtxOULd2eKQCMyA0uPDcRVNg4WXJqGn6','GAKDeIrs1p',1,'rygftv','禁止自拍','','','',0,0,1,0,0,'',0,29757,1,1484207568,0,0,1,1),(62,'ks7822 ','快速丰乳妙招','ggg654321',NULL,'gh_343524962251','wx0a87ea1fda717f22','e12c39bb3c0065287f73c4b105b9f354','6BvoRSVFi2nxODdpl4g1IANyCWXzUZ5PLfkeET7ch9a','A5myVD1Lrf',1,'yetffg','禁止自拍','','','',0,0,1,0,0,'',0,14684,1,1484207569,0,0,1,0),(63,'jnhm66 ','教你学会化眉','mjx654321',NULL,'gh_deee52a51333','wx84c893325fd15c7e','d116d0a2f409811d8ad0b648cf9abd5f','EyZVjgOFA9kS2J65miqaRslXhpTdKQ83LHoBfUGCv1I','oRqesXKmAJ',1,'rygftv','禁止自拍','','','',0,0,1,0,0,'',0,31328,1,1484207569,0,0,1,1),(64,'bz3880','巧手妈妈教你做编织','mjx654321',NULL,'gh_cc8a440e7179','wxab647b8733965389','29f05c475f64cc196cb459dfe1066f4c','75mNjeyXWwscGOuZqL8lzr9aMhgPkQJopVHnBt6b1AK','AHICFbtdaR',1,'sfd677','小白','','','',0,0,1,0,0,'',0,25128,1,1484207570,0,0,1,1),(65,'jmmbz123','教妈妈学会做编织','mjx654321',NULL,'gh_a970f8a6571a','wx2664ed293874f347','81d3d2a0694874fdae8c736b660bf2e9','F7L3O6btEl0sahzfNDU2eJg5KqHYydnjIcQvkXSR1r9','JlLzHkPwZc',1,'Gioo660','小二子','','','',0,0,1,0,0,'',0,21140,1,1484207572,0,0,1,1);

/*Table structure for table `official_group` */

DROP TABLE IF EXISTS `official_group`;

CREATE TABLE `official_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) DEFAULT NULL,
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `official_group` */

insert  into `official_group`(`id`,`status`,`name`,`desc`,`company_id`,`created_at`,`updated_at`) values (1,1,'美女','美女',3,1484118463,0),(2,1,'帅哥','帅哥',1,1484121482,0),(3,1,'母婴','母婴',1,1484121559,0),(4,1,'情感','情感',1,1484124263,0);

/*Table structure for table `reply` */

DROP TABLE IF EXISTS `reply`;

CREATE TABLE `reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '公众号Id',
  `type_reply` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0被添加自动回复 1消息自动回复 2关键字自动回复',
  `type_msg` tinyint(2) NOT NULL DEFAULT '5' COMMENT '1->article, 2->image, 3->voice, 4->video, 5->text',
  `wx_media_id` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `thumb_media_id` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '内容（可以是HTML）',
  `keyword` varchar(1000) CHARACTER SET utf8 DEFAULT '' COMMENT '关键字',
  `img_url` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `url` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `desctiption` varchar(600) CHARACTER SET utf8 DEFAULT NULL,
  `title` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=915 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `reply` */

insert  into `reply`(`id`,`account_id`,`type_reply`,`type_msg`,`wx_media_id`,`thumb_media_id`,`content`,`keyword`,`img_url`,`url`,`desctiption`,`title`,`created_at`,`updated_at`) values (909,12,0,5,NULL,NULL,'欢迎关注！?',NULL,NULL,NULL,NULL,NULL,1483610817,0),(910,12,1,5,NULL,NULL,'?未识别关键字<a href=\"http://www.baidu.com\" >百度</a>',NULL,NULL,NULL,NULL,NULL,1483610860,1483610951),(911,15,1,5,NULL,NULL,'哈哈哈哈',NULL,NULL,NULL,NULL,NULL,1483675056,0),(912,30,2,5,NULL,NULL,'哈哈','你好',NULL,NULL,NULL,NULL,1483947228,0),(913,19,0,5,NULL,NULL,'亲，欢迎关注！！mo-色mo-色\n\n\n↓↓精彩微信推荐↓↓\n\n\n?点击下面蓝字免费订阅?\n\n?<a href=\"http://t.cn/RIUcgpk\">女人心计</a>?<a href=\"http://t.cn/RIUcTAZ\">健康美胸</a>\n\n?<a href=\"http://t.cn/RIUVKCV\">瘦到90斤</a>?<a href=\"http://t.cn/RIUcujn\">健康常识</a>\n\n?<a href=\"http://t.cn/RI13cjd\">搞定男人</a>?<a href=\"http://t.cn/RIUcO68\">易经风水</a>\n\n?<a href=\"http://t.cn/RIUclkK\">魅力女人</a>?<a href=\"http://t.cn/RIUcs0B\">美发秘笈</a>\n\n?<a href=\"http://t.cn/RIUVPkG\">生活助手</a>?<a href=\"http://t.cn/RIUckf4\">化妆护肤</a>\n\n?<a href=\"http://t.cn/RIUVZmw\">排毒养颜</a>?<a href=\"http://t.cn/RIUVEdZ\">穿衣打扮</a>',NULL,NULL,NULL,NULL,NULL,1484103796,0),(914,15,2,5,NULL,NULL,'测试1','测试1',NULL,NULL,NULL,NULL,1484188001,0);

/*Table structure for table `statistic_news` */

DROP TABLE IF EXISTS `statistic_news`;

CREATE TABLE `statistic_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `ref_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据的日期',
  `user_source` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '在获取图文阅读分时数据时才有该字段，代表用户从哪里进入来阅读该图文。0:会话;1.好友;2.朋友圈;3.腾讯微博;4.历史消息页;5.其他',
  `int_page_read_user` int(11) NOT NULL DEFAULT '0' COMMENT '图文页（点击群发图文卡片进入的页面）的阅读人数',
  `int_page_read_count` int(11) NOT NULL DEFAULT '0' COMMENT '原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0',
  `ori_page_read_user` int(11) NOT NULL DEFAULT '0' COMMENT '原文页的阅读次数',
  `ori_page_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '总用户量',
  `share_user` int(10) NOT NULL DEFAULT '0' COMMENT '分享的人数',
  `share_count` int(10) NOT NULL DEFAULT '0' COMMENT '分享的次数',
  `add_to_fav_user` int(10) NOT NULL DEFAULT '0' COMMENT '收藏的人数',
  `add_to_fav_count` int(10) NOT NULL DEFAULT '0' COMMENT '收藏的次数',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `statistic_news` */

/*Table structure for table `statistic_user` */

DROP TABLE IF EXISTS `statistic_user`;

CREATE TABLE `statistic_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `ref_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据的日期',
  `user_source` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '用户的渠道，数值代表的含义如下：\n0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页） 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告',
  `new_user` int(11) NOT NULL DEFAULT '0' COMMENT '新增的用户数量',
  `cancel_user` int(11) NOT NULL DEFAULT '0' COMMENT '取消关注的用户数量，new_user减去cancel_user即为净增用户数量',
  `cumulate_user` int(11) NOT NULL DEFAULT '0' COMMENT '总用户量',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8;

/*Data for the table `statistic_user` */

insert  into `statistic_user`(`id`,`official_account_id`,`ref_date`,`user_source`,`new_user`,`cancel_user`,`cumulate_user`,`created_at`,`updated_at`) values (470,61,1485792000,0,5,0,30330,1486439025,0),(471,61,1485878400,0,2,0,30322,1486439025,0),(472,61,1485964800,0,0,0,30313,1486439025,0),(473,61,1486051200,0,0,0,30297,1486439025,0),(474,61,1486137600,0,0,0,30285,1486439025,0),(475,61,1486224000,0,0,0,30272,1486439025,0),(476,61,1486310400,0,0,0,30251,1486439025,0),(477,62,1485792000,0,6,1,14223,1486439025,0),(478,62,1485878400,0,3,0,14208,1486439025,0),(479,62,1485964800,0,0,0,14187,1486439025,0),(480,62,1486051200,0,0,0,14156,1486439025,0),(481,62,1486137600,0,0,0,14134,1486439025,0),(482,62,1486224000,0,0,0,14115,1486439025,0),(483,62,1486310400,0,0,0,14085,1486439025,0),(484,63,1485792000,0,3,1,30696,1486439025,0),(485,63,1485878400,0,2,0,30669,1486439025,0),(486,63,1485964800,0,4,0,30640,1486439025,0),(487,63,1486051200,0,0,0,30610,1486439025,0),(488,63,1486137600,0,0,0,30579,1486439025,0),(489,63,1486224000,0,0,0,30540,1486439025,0),(490,63,1486310400,0,0,0,30509,1486439025,0),(491,64,1485792000,0,7,0,24777,1486439026,0),(492,64,1485878400,0,8,1,24730,1486439026,0),(493,64,1485964800,0,0,0,24697,1486439026,0),(494,64,1486051200,0,0,0,24659,1486439026,0),(495,64,1486137600,0,0,0,24623,1486439026,0),(496,64,1486224000,0,0,0,24585,1486439026,0),(497,64,1486310400,0,0,0,24544,1486439026,0),(498,65,1485792000,0,6,1,20304,1486439026,0),(499,65,1485878400,0,1,0,20271,1486439026,0),(500,65,1485964800,0,1,33,20249,1486439026,0),(501,65,1486051200,0,0,0,20218,1486439026,0),(502,65,1486137600,0,0,0,20193,1486439026,0),(503,65,1486224000,0,0,0,20159,1486439026,0),(504,65,1486310400,0,0,0,20131,1486439026,0),(505,56,1485792000,0,7,0,38731,1486439026,0),(506,56,1485878400,0,1,0,38674,1486439026,0),(507,56,1485964800,0,0,0,38625,1486439026,0),(508,56,1486051200,0,0,0,38576,1486439026,0),(509,56,1486137600,0,0,0,38528,1486439026,0),(510,56,1486224000,0,0,0,38460,1486439026,0),(511,56,1486310400,0,0,0,38386,1486439026,0),(512,57,1485792000,0,5,0,32496,1486439027,0),(513,57,1485878400,0,5,1,32449,1486439027,0),(514,57,1485964800,0,1,0,32406,1486439027,0),(515,57,1486051200,0,0,0,32363,1486439027,0),(516,57,1486137600,0,0,0,32321,1486439027,0),(517,57,1486224000,0,0,0,32274,1486439027,0),(518,57,1486310400,0,0,0,32227,1486439027,0),(519,58,1485792000,0,5,0,36860,1486439027,0),(520,58,1485878400,0,4,0,36825,1486439027,0),(521,58,1485964800,0,0,29,36809,1486439027,0),(522,58,1486051200,0,0,0,36785,1486439027,0),(523,58,1486137600,0,0,0,36766,1486439027,0),(524,58,1486224000,0,0,0,36753,1486439027,0),(525,58,1486310400,0,0,0,36722,1486439027,0),(526,59,1485792000,0,10,2,36070,1486439027,0),(527,59,1485878400,0,3,0,36027,1486439027,0),(528,59,1485964800,0,9,1,35970,1486439027,0),(529,59,1486051200,0,0,0,35928,1486439027,0),(530,59,1486137600,0,0,0,35867,1486439027,0),(531,59,1486224000,0,0,0,35806,1486439027,0),(532,59,1486310400,0,0,0,35770,1486439027,0),(533,60,1485792000,0,7,0,34946,1486439031,0),(534,60,1485878400,0,5,1,34919,1486439031,0),(535,60,1485964800,0,0,44,34885,1486439031,0),(536,60,1486051200,0,0,0,34854,1486439031,0),(537,60,1486137600,0,0,0,34828,1486439031,0),(538,60,1486224000,0,0,0,34791,1486439031,0),(539,60,1486310400,0,0,0,34749,1486439031,0),(540,51,1485792000,0,3,1,17150,1486439031,0),(541,51,1485878400,0,1,0,17127,1486439031,0),(542,51,1485964800,0,0,25,17106,1486439031,0),(543,51,1486051200,0,0,0,17084,1486439031,0),(544,51,1486137600,0,0,0,17069,1486439031,0),(545,51,1486224000,0,0,0,17061,1486439031,0),(546,51,1486310400,0,0,0,17042,1486439031,0),(547,52,1485792000,0,9,1,38212,1486439037,0),(548,52,1485878400,0,9,0,38188,1486439037,0),(549,52,1485964800,0,4,0,38152,1486439037,0),(550,52,1486051200,0,0,0,38127,1486439037,0),(551,52,1486137600,0,0,0,38094,1486439037,0),(552,52,1486224000,0,0,0,41039,1486439037,0),(553,52,1486310400,0,0,0,41638,1486439037,0),(554,53,1485792000,0,1,0,23118,1486439039,0),(555,53,1485878400,0,1,0,23094,1486439039,0),(556,53,1485964800,0,0,34,23068,1486439039,0),(557,53,1486051200,0,0,0,23057,1486439039,0),(558,53,1486137600,0,0,0,23036,1486439039,0),(559,53,1486224000,0,0,0,23018,1486439039,0),(560,53,1486310400,0,0,0,22986,1486439039,0),(561,54,1485792000,0,3,0,35238,1486439039,0),(562,54,1485878400,0,3,0,35180,1486439039,0),(563,54,1485964800,0,0,39,35146,1486439039,0),(564,54,1486051200,0,0,0,35106,1486439039,0),(565,54,1486137600,0,0,0,35065,1486439039,0),(566,54,1486224000,0,0,0,35022,1486439039,0),(567,54,1486310400,0,0,0,34974,1486439039,0),(568,55,1485792000,0,5,1,20487,1486439042,0),(569,55,1485878400,0,8,0,20482,1486439042,0),(570,55,1485964800,0,9,0,20479,1486439042,0),(571,55,1486051200,0,0,0,20476,1486439042,0),(572,55,1486137600,0,0,0,20468,1486439042,0),(573,55,1486224000,0,0,0,20447,1486439042,0),(574,55,1486310400,0,0,0,20432,1486439042,0),(575,46,1485792000,0,0,0,108,1486439043,0),(576,46,1485878400,0,0,0,108,1486439043,0),(577,46,1485964800,0,2,1,109,1486439043,0),(578,46,1486051200,0,0,0,109,1486439043,0),(579,46,1486137600,0,0,1,108,1486439043,0),(580,46,1486224000,0,1,1,108,1486439043,0),(581,46,1486310400,0,0,0,108,1486439043,0),(582,47,1485792000,0,0,0,23,1486439043,0),(583,47,1485878400,0,1,0,24,1486439043,0),(584,47,1485964800,0,0,0,24,1486439043,0),(585,47,1486051200,0,0,0,24,1486439043,0),(586,47,1486137600,0,0,0,24,1486439043,0),(587,47,1486224000,0,0,0,24,1486439043,0),(588,47,1486310400,0,0,0,24,1486439043,0),(589,48,1485792000,0,1,0,25571,1486439043,0),(590,48,1485878400,0,1,0,25540,1486439043,0),(591,48,1485964800,0,0,0,25507,1486439043,0),(592,48,1486051200,0,0,0,25477,1486439043,0),(593,48,1486137600,0,0,0,25436,1486439043,0),(594,48,1486224000,0,0,0,25389,1486439043,0),(595,48,1486310400,0,0,0,25354,1486439043,0),(596,49,1485792000,0,0,7,10742,1486439044,0),(597,49,1485878400,0,2,0,10738,1486439044,0),(598,49,1485964800,0,2,0,10735,1486439044,0),(599,49,1486051200,0,1,8,10732,1486439044,0),(600,49,1486137600,0,0,0,10726,1486439044,0),(601,49,1486224000,0,0,0,10717,1486439044,0),(602,49,1486310400,0,0,0,10713,1486439044,0),(603,31,1485792000,0,0,4,22471,1486439045,0),(604,31,1485878400,0,0,3,22468,1486439045,0),(605,31,1485964800,0,0,5,22463,1486439045,0),(606,31,1486051200,0,1,1,22460,1486439045,0),(607,31,1486137600,0,1,0,22456,1486439045,0),(608,31,1486224000,0,0,0,22451,1486439045,0),(609,31,1486310400,0,0,0,22449,1486439045,0),(610,33,1485792000,0,0,0,5,1486439046,0),(611,33,1485878400,0,0,0,5,1486439046,0),(612,33,1485964800,0,0,0,5,1486439046,0),(613,33,1486051200,0,0,0,5,1486439046,0),(614,33,1486137600,0,2,1,7,1486439046,0),(615,33,1486224000,0,5,2,11,1486439046,0),(616,33,1486310400,0,1,0,15,1486439046,0),(617,34,1485792000,0,0,0,13,1486439047,0),(618,34,1485878400,0,0,0,13,1486439047,0),(619,34,1485964800,0,1,0,14,1486439047,0),(620,34,1486051200,0,0,0,14,1486439047,0),(621,34,1486137600,0,0,0,14,1486439047,0),(622,34,1486224000,0,0,0,14,1486439047,0),(623,34,1486310400,0,0,1,13,1486439047,0),(624,12,1485792000,0,0,0,404,1486439047,0),(625,12,1485878400,0,0,0,404,1486439047,0),(626,12,1485964800,0,0,1,403,1486439047,0),(627,12,1486051200,0,0,0,403,1486439047,0),(628,12,1486137600,0,0,0,403,1486439047,0),(629,12,1486224000,0,0,1,402,1486439047,0),(630,12,1486310400,0,0,0,402,1486439047,0),(631,15,1485792000,0,1,0,145,1486439047,0),(632,15,1485878400,0,0,0,145,1486439047,0),(633,15,1485964800,0,0,0,145,1486439047,0),(634,15,1486051200,0,0,0,145,1486439047,0),(635,15,1486137600,0,0,0,145,1486439047,0),(636,15,1486224000,0,0,0,145,1486439047,0),(637,15,1486310400,0,0,0,145,1486439047,0),(638,19,1485792000,0,5,2,16048,1486439048,0),(639,19,1485878400,0,1,0,16107,1486439048,0),(640,19,1485964800,0,0,0,16079,1486439048,0),(641,19,1486051200,0,0,0,16043,1486439048,0),(642,19,1486137600,0,0,0,15983,1486439048,0),(643,19,1486224000,0,0,0,17424,1486439048,0),(644,19,1486310400,0,0,0,20156,1486439048,0),(645,28,1485792000,0,0,0,2,1486439049,0),(646,28,1485878400,0,0,0,2,1486439049,0),(647,28,1485964800,0,0,0,2,1486439049,0),(648,28,1486051200,0,0,0,2,1486439049,0),(649,28,1486137600,0,0,0,2,1486439049,0),(650,28,1486224000,0,0,0,2,1486439049,0),(651,28,1486310400,0,0,0,2,1486439049,0);

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '登录名',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `weixin_id` varchar(255) DEFAULT NULL COMMENT '微信号',
  `authKey` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱名',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `login_at` int(11) DEFAULT NULL,
  `blocked_at` int(11) DEFAULT NULL,
  `confirmed_at` int(11) DEFAULT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户的角色id',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `weixin_id` (`weixin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

/*Data for the table `user` */

insert  into `user`(`id`,`username`,`nickname`,`weixin_id`,`authKey`,`email`,`phone`,`password_hash`,`password_reset_token`,`login_at`,`blocked_at`,`confirmed_at`,`company_id`,`role_id`,`status`,`created_at`,`updated_at`) values (1,NULL,'admin','asdjaksj','E3r2e9pE90LN1JcHYFVQq4Id0njpIT-c','hehe@xxx.com','13212341234','$2y$13$bOFWvH9R4deahwYnc/xk6uwlp25zKyd1M8gZWod8ovQ5thk8.3eZ6','',1478058574,NULL,1441766741,1,1,1,1441766741,1482661272),(2,NULL,'test',NULL,'c6kCoQE_NWdb9vdivl4p3w_yrbekz_1r',NULL,'15982839394','$2y$13$vaw7rtUIfKdU0vdxbq8mN.7Y5D0mA1BllDTwwroLIeL/p6fxi0W5C',NULL,NULL,NULL,NULL,1,1,1,1481193370,1482552995),(20,NULL,'test',NULL,'2XEu3Bh4sfOXoCy6oIRoUJBLRKev1QGc',NULL,'15987839999','$2y$13$2l3hSfkvYkiB6KkBQ8iMhudLm8iS3E/MZiUNmDD4Egw3SucT1CG7y',NULL,NULL,NULL,NULL,1,1,0,1481196266,NULL),(21,NULL,'test',NULL,'kADZNgIPTmt-Y0fbrmQsWLY923G1Wk5R',NULL,'15982839999','$2y$13$UgQwWkg66oIo90TJSjwajuTnMZXSlpwC7KHHpo/YSmLRhuKpofnM6',NULL,NULL,NULL,NULL,1,1,0,1481196277,NULL),(22,NULL,'test',NULL,'4rhPFUVL77z-ktlu4_lVGYNFFvYZGAQo',NULL,'15982839399','$2y$13$x5KshAmQL7gYcJJ6JRBbNu826tQbQuSKankM/1KnIABIqN1P6gNVS',NULL,NULL,NULL,NULL,1,1,0,1481196326,NULL),(23,NULL,'test',NULL,'27OmHxwsYvu5wGonxqmUxX3F9KvhGOWh',NULL,'15982837999','$2y$13$0W35ZF6zbv/m/ljfP2hUIeB/OQPuo4oF6/ke8iyRgp5NBKEzqhYTO',NULL,NULL,NULL,NULL,1,1,1,1481196519,1481196652),(24,NULL,'CZL',NULL,'PvF3qWCv451ZxYfB0ANKfExmxFTfkj2X',NULL,'15982837995','$2y$13$70Dgu9ayTObc0RUXRP4tauJOBsAjJOh9pumHe2MF8fTz0PIvxSvAW',NULL,NULL,NULL,NULL,1,1,1,1481335352,NULL),(25,NULL,'test','test_2','wDDOVrfHi_BqvfxHM1WPr8lb0gqBkRPO',NULL,'15982837991','$2y$13$Q5aj61WQcNCQptMqKp2BDeVIYznM7JvsLBLuwcoBDlekT.I1V6WnG',NULL,NULL,NULL,NULL,1,1,1,1481335495,1482394985),(26,NULL,'中文zhoig\'n\'wen','test','qaD7YrWpEg81_VPsDJtZrm68McOdgSo6',NULL,'15982837992124','$2y$13$gSAn/T8CNPL.dE3xO1YzwuErxksih7CcG5N/y8iPJzRP7qyEORQ66',NULL,NULL,NULL,NULL,1,1,1,1481339084,1482552907),(27,NULL,'test',NULL,'SPHvU4Q_A30o2lIwlDnOiVfBuzKPJiqt',NULL,'15987299999','$2y$13$i3pnMGc.TV0kNCNHdi9XIOaDkPWovHFZYtlAbxcaVJdH2ErONSk56',NULL,NULL,NULL,NULL,1,1,0,1481535267,NULL),(28,NULL,'CZL',NULL,'At2P8j1YDMbMAP0Tv3w2Mc_V2qPtNtVc',NULL,'136111111111111','$2y$13$AFzgZrgD2wE/kOB5t/940ODd6BLu88CKgv6MRBkjSGtabEY/eEa4.',NULL,NULL,NULL,NULL,1,1,1,1482676031,1482737702),(29,NULL,'1111111111111111',NULL,'fe2vMP0BbDtdupGHOfQOVYC05K9l_p-0',NULL,'12345678912','$2y$13$g9Xm3OwdbSUWzE.tX/kj4u4br/ZggiN.yuugJNJaF9ilKr4ySkhty',NULL,NULL,1483172409,NULL,1,3,0,1483172062,1483174387),(30,NULL,'13670246396',NULL,'aibdokWrC7MNWxIB2Jmf8HJ-uUfj6x6d',NULL,'13670246396','$2y$13$b7MHdTlijhyjS7LLWppyqeC0rpsFHViR0Kk1dS/0/AaFxdQI0UdDq',NULL,NULL,NULL,NULL,1,3,0,1483174421,1483174425),(31,NULL,'德莱文',NULL,'h8pm6iKPN-P0TnXioU91wCxpVpttPRTt',NULL,'18234146081','$2y$13$i.VrYIyRaxKK0x0hC6Jn4.grML71tfE4VPCjzPygNRbwUKreXKwVC',NULL,NULL,NULL,NULL,1,2,1,1483191632,NULL),(32,NULL,'test',NULL,'7NcdDerNrgxQt5oFC4k-w4imd7HipJ0R',NULL,'15987899999','$2y$13$W7J1REmcdCxx0ZkXIwGP3.SsVwbmCvKgH5LlaKDM/Ugn0PtUyb8sK',NULL,NULL,NULL,NULL,2,0,1,1483430425,NULL),(33,NULL,'asdf',NULL,'9iyWX8vLFP2gxi67vdyVcCsSOn7oImsr',NULL,'13652330065','$2y$13$Ymvh8mkopetsU0Ysc2B04.Qm3J/lnxXlg4YL7qpYh675OFjBQn.Cm',NULL,NULL,NULL,NULL,1,4,0,1483522379,1483522386),(34,NULL,'admin',NULL,'X2sdtPk5Ghdy3MQSNDH2YU3RgzeoZyAC',NULL,'17876013413','$2y$13$ZIj5n3zUqniIKl7HqTm5iOxRM2x2YsKFx.U1z/0jdJgSnyItE3Myy',NULL,NULL,NULL,NULL,3,1,1,1483929457,NULL),(35,NULL,'company_admin',NULL,'NTWHHE0LYJuttqixC3CSN4i8jp3lTHmY',NULL,'15386826583','$2y$13$r/w2uI9gXwut1R2cevyemuojjgjjX1fXeuFegPXGp0t6bLEp9c.9a',NULL,NULL,NULL,NULL,0,1,1,1484038570,NULL),(36,NULL,'cfe',NULL,'mQtxdvKVF8g3BOnatLk41CoaoFIAs5Bc',NULL,'15951006272','$2y$13$RaRKU.mh5B4lTOL/U3mj3.ffV4GTlXUn.Qq3DrDEqvRgeACPwJ.KC',NULL,NULL,NULL,NULL,1,1,1,1484098679,NULL),(37,NULL,'ggg',NULL,'Wjce3znJrk7tD6kH8wCg5cl697kqbTzj',NULL,'13415127288','$2y$13$k1UpYj9s4TI80UhcdpzXoeL6kKyRmJCKhs9bFINqKf60Lh1suDw3u',NULL,NULL,NULL,NULL,1,3,0,1484130061,1484130214),(38,NULL,'yuvisang',NULL,'lmC64hR6sFQT-dsZ7hclpp10oTjTnTXp',NULL,'13951216418','$2y$13$tML/l/79THkKiqvW.yJ4kONyYtcJpNdS1ySsG0qYrqi72iOuizkEe',NULL,NULL,NULL,NULL,1,1,1,1484200615,NULL),(39,NULL,'test',NULL,'zgRqMrZa4G24Gz9Ml9VEF385lCz-q6ck',NULL,'15987899991','$2y$13$9irmnYC.EwjWxRaUHt89Vuw/kViWZTWwEG48XG59DBgqU4SJj3THm',NULL,NULL,NULL,NULL,6,9,1,1484279625,NULL),(40,NULL,'王师彦',NULL,'t69VlMlnjoaLWl8x9b2x9QkD7fqkcbDA',NULL,'weiboxinxi123','$2y$13$SOFv6Ff.g1iHeC6vtcLc..uiBCqnEyRwigJfz2/o2EnjFHaJsuU72',NULL,NULL,NULL,NULL,7,10,1,1484968143,NULL),(41,NULL,'王师彦',NULL,'eUK_7co0mwy1D23PusL65oEEyCVxRbGD',NULL,'15919994691','$2y$13$72GlVI5N.Cv0Ladip3jIz.N55qzuae6FvRU02JOitS1YAfhrMBCmu',NULL,NULL,NULL,NULL,8,11,1,1484970563,NULL);

/*Table structure for table `user_announcement_map` */

DROP TABLE IF EXISTS `user_announcement_map`;

CREATE TABLE `user_announcement_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发起人',
  `announcement_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告id',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读, 0代表未读，1代表已经读',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `user_announcement_map` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
