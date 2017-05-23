-- MySQL dump 10.13  Distrib 5.6.35, for Linux (x86_64)
--
-- Host: localhost    Database: wx_admin_platform
-- ------------------------------------------------------
-- Server version	5.6.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `advertisement`
--

DROP TABLE IF EXISTS `advertisement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接单人',
  `customer` varchar(100) NOT NULL DEFAULT '' COMMENT '客户',
  `tel` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '电话号码',
  `order_amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单金额',
  `deposit` int(11) NOT NULL DEFAULT '0' COMMENT '订金金额',
  `receipt_date` int(11) NOT NULL DEFAULT '0' COMMENT '接单日期',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0->操作中 1->已完成',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advertisement_official`
--

DROP TABLE IF EXISTS `advertisement_official`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisement_official` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ad_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接单人',
  `ad_position` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位',
  `retain_day` tinyint(2) NOT NULL DEFAULT '0' COMMENT '保留天数',
  `product_type` varchar(50) NOT NULL DEFAULT '' COMMENT '产品类型',
  `official_account_id` int(10) NOT NULL DEFAULT '0' COMMENT '公众号',
  `send_date` int(11) NOT NULL DEFAULT '0' COMMENT '发送日期',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0->待发送 1->已发送 2->已结束',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `analysis`
--

DROP TABLE IF EXISTS `analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `analysis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_image_map`
--

DROP TABLE IF EXISTS `article_image_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_image_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `source_url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片链接',
  `wechat_source_url` varchar(255) NOT NULL DEFAULT '' COMMENT '微信端的图片链接',
  PRIMARY KEY (`id`),
  KEY `source_url` (`source_url`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authority_permission`
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
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authority_role`
--

DROP TABLE IF EXISTS `authority_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fans`
--

DROP TABLE IF EXISTS `fans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fans_group`
--

DROP TABLE IF EXISTS `fans_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fans_tag`
--

DROP TABLE IF EXISTS `fans_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fans_tag_map`
--

DROP TABLE IF EXISTS `fans_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fans_tag_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) DEFAULT NULL COMMENT 'uid',
  `tag_id` int(10) DEFAULT NULL COMMENT 'tag_id',
  `is_sync` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manager_log`
--

DROP TABLE IF EXISTS `manager_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mass`
--

DROP TABLE IF EXISTS `mass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `is_synchronized` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0代表未同步到微信，1代表已同步到微信',
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_official` (`media_id`,`official_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7540 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `materials_category`
--

DROP TABLE IF EXISTS `materials_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '名称',
  `display_order` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages_resources`
--

DROP TABLE IF EXISTS `messages_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `official_account`
--

DROP TABLE IF EXISTS `official_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `official_group`
--

DROP TABLE IF EXISTS `official_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `official_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) DEFAULT NULL,
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reply`
--

DROP TABLE IF EXISTS `reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '公众号Id',
  `type_reply` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0被添加自动回复 1消息自动回复 2关键字自动回复',
  `type_msg` tinyint(2) NOT NULL DEFAULT '5' COMMENT '1->article, 2->image, 3->voice, 4->video, 5->text',
  `wx_media_id` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `thumb_media_id` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '内容（可以是HTML）',
  `keyword` varchar(1000) CHARACTER SET utf8 DEFAULT '' COMMENT '关键字',
  `rule` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '规则名称',
  `img_url` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `url` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `desctiption` varchar(600) CHARACTER SET utf8 DEFAULT NULL,
  `title` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=950 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistic_news`
--

DROP TABLE IF EXISTS `statistic_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistic_user`
--

DROP TABLE IF EXISTS `statistic_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teller`
--

DROP TABLE IF EXISTS `teller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teller` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出纳人员',
  `customer` varchar(100) NOT NULL DEFAULT '' COMMENT '客户',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `receipt_date` int(11) NOT NULL DEFAULT '0' COMMENT '收款日期',
  `order_comment` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '款项说明',
  `receipt_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收付银行',
  `receipt_bank_num` varchar(100) NOT NULL DEFAULT '' COMMENT '收付账号',
  `pay_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '付款银行',
  `pay_bank_num` varchar(100) NOT NULL DEFAULT '' COMMENT '付款账户',
  `amount` int(10) NOT NULL DEFAULT '0' COMMENT '收入金额',
  `teller_num` varchar(20) NOT NULL DEFAULT '' COMMENT '出纳号，唯一',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_announcement_map`
--

DROP TABLE IF EXISTS `user_announcement_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_announcement_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发起人',
  `announcement_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告id',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读, 0代表未读，1代表已经读',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-18 10:48:04
