
DROP TABLE IF EXISTS `material`;
CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) DEFAULT 0 COMMENT '所属公众号id',
  `media_id` varchar(30) DEFAULT '0' COMMENT 'media_id，可以是图文素材的，也可以是图片的，等等',
  `original_id` varchar(60) DEFAULT '0' COMMENT '原始微信素材id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->article, 2->image, 3->voice, 4->video, 5->text',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(200) DEFAULT '' COMMENT '标题',  # 文字素材无标题
  `description` varchar(360) DEFAULT '' COMMENT '摘要',
  `content` text COMMENT '内容',
  `cover_media_id` varchar(1024) COMMENT '封面 media_id',
  `cover_url` varchar(1024) COMMENT '封面url',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `created_from` tinyint(1) DEFAULT 0 COMMENT '0微信同步到server，server同步到微信',
  `source_url` varchar(1024) COMMENT '内容连接资源',
  `content_url` varchar(1024) COMMENT '原文链接',
  `is_legal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0代表不违规，1代表违规定',
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表删除，1代表正常
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间，0为马上发布，其他为定时发送',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
