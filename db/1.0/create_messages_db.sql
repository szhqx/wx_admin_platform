-- drop tables messages;

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `fans_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝id 不存在时为公众号回复',
  `sent_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息发送时间 OR 消息回复时间',
  `resource_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应消息资源',
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息回复id',
  `content` text COLLATE utf8_unicode_ci COMMENT '消息内容',
  `msg_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息回复id',
  `is_favorate` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `msg_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '消息类型',
  `picurl` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '图片url',
  `medis_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '媒体id',
  `voice_format` varchar(60) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '音频类型',
  `recognition` varchar(100) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '语音识别',
  `thumb_media_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '视频消息缩略图的媒体id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
