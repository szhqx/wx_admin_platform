DROP TABLE IF EXISTS `mass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `mass` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(10) unsigned NOT NULL,
  `official_account_id` int(10) unsigned NOT NULL,
  `pub_at` int(10) NOT NULL DEFAULT '0' COMMENT '群发的时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT "创建mass的用户",
  `user_tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收的用户组id',
  `user_sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接收的用户组性别，0代表未设置，1代表男，2代表女',
  `user_area` varchar(256) NOT NULL DEFAULT '' COMMENT '接收用户的区域',
  `msg_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_id',
  `msg_data_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_data_id，多图文类型才会有',
  `msg_status` varchar(256) NOT NULL DEFAULT '' COMMENT '消息发送状态，微信端返回',
  `fail_times` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '发送失败的次数'
  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表删除，1代表正常，2代表发送准备中，3代表发送中，4代表发送完毕，5代表发送异常
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
