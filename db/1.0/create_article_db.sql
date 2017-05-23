DROP TABLE IF EXISTS `article`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->图文, 2->图片, 3->声音, 4->视频，5->封面照片，6->文章照片',
  `description` varchar(360) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` text COMMENT '内容',
  `cover_url` varchar(1024) COMMENT '封面url',
  `source_url` varchar(1024) COMMENT '内容连接资源，默认是阿里云的',
  `weixin_source_url` varchar(1024) COMMENT '内容连接资源，微信返回的',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `author` varchar(256) DEFAULT '' COMMENT '发布内容的作者',

  `official_account_id` int(10) DEFAULT 0 COMMENT '所属公众号id',
  `material_id` int(10) unsigned NOT NULL,

  `show_cover_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表不显示，1代表显示',
  `is_legal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表违规，1代表不违规',
  `order` smallint(4) NOT NULL DEFAULT '1' COMMENT '多图文素材的时候使用，代表文章的顺序，从1开始',

  `msg_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_id',
  `msg_data_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信端返回的msg_data_id；如果是多图文，会叠上order',

  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表删除，1代表正常
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布的时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
