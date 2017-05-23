DROP TABLE IF EXISTS `authority_permission`;
CREATE TABLE `authority_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `display_name` varchar(255) NOT NULL DEFAULT '' COMMENT "中文名",
  `description` text COMMENT '权限描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert  into `authority_permission`(`name`,`display_name`) values ('official/create','添加公众号'),('official/modify','修改公众号'),('official/delete','删除公众号'),('official/checkPassword','查看公众号密码'),('official/batch-import','批量导入公众号'),('official/batch-dump','批量导出公众号');

insert into `authority_permission` (`name`, `display_name`) values ('message/collect','消息收藏'),('message/save-to-material','消息保存为素材'), ('message/response', '回复消息');

insert into `authority_permission` (`name`, `display_name`) values ('fans/create-tag','创建新标签'),('fans/delete-tag','删除标签'), ('fans/mark', '修改粉丝备注'), ('fans/block', '将粉丝加入黑名单'), ('fans/tagging', '给粉丝打标签');

insert into `authority_permission` (`name`, `display_name`) values ('menu/create','自定义菜单');

insert into `authority_permission` (`name`, `display_name`) values ('official/auto-response','自动回复设置');

insert into `authority_permission` (`name`, `display_name`) values ('mass/create','添加群发');

insert into `authority_permission` (`name`, `display_name`) values ('material/create','添加素材'), ('material/update', '编辑素材'), ('material/delete', '删除素材');

insert into `authority_permission` (`name`, `display_name`) values ('account/create','创建用户'), ('account/modify', '修改用户'), ('account/delete', '禁用用户');

insert into `authority_permission` (`name`, `display_name`) values ('authority/create-role','添加角色'), ('authority/modify-role', '编辑角色'), ('authority/delete-role', '删除角色');

DROP TABLE IF EXISTS `authority_role`;
CREATE TABLE `authority_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text COMMENT '角色描述',
  `company_id` int(10) unsigned NOT NULL DEFAULT 0,
  `is_super_admin` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否超级管理员，0代表否，1代表是',
  `permission_id_list` text COMMENT '角色权限id列表，json化',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert  into `authority_role`(`name`,`description`, `company_id`, `is_super_admin`, `permission_id_list`) values ('超级管理员', '描述描述', 1, 1, '[]');


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '登录名',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `weixin_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信号',
  `authKey` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `login_at` int(11) DEFAULT NULL,
  `blocked_at` int(11) DEFAULT NULL,
  `confirmed_at` int(11) DEFAULT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户的角色id',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

insert  into `user`(`id`, `nickname`,`weixin_id`, `authKey`, `email`, `phone`, `password_hash`,`password_reset_token`,`created_at`,`updated_at`,`login_at`,`blocked_at`,`confirmed_at`, `company_id`, `role_id`) values (1,'测试账号','asdjaksj','1lQl4TG6sYlyWRqXZEWL0ZhQkPATVnMs','hehe@xxx.com','12312341234','$2y$13$lYlhIcBcs6jBr7yTd6YrWueckcs.Cvx70juIHs6wEfjtUwnA318VW','',1441766741,1477907816,1478058574,NULL,1441766741,1,1);


DROP TABLE IF EXISTS `official_account`;
CREATE TABLE `official_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weixin_id` varchar(255) NOT NULL COMMENT '微信号',
  `weixin_name` varchar(255) COMMENT '微信名称',
  `weixin_password` varchar(255) COMMENT '公众号登录密码',
  `official_id` varchar(255) COMMENT '公众号id',
  `official_origin_id` varchar(255) COMMENT '公众号原始id',
  `app_id` varchar(255) COMMENT 'AppID',
  `app_secret` varchar(255) COMMENT 'AppSecret',
  `encoding_aes_key` varchar(255) COMMENT 'EncodingAesKey',
  `token` varchar(255) COMMENT 'token',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0', # 是否已经认证，0代表未认证，1代表已认证
  `admin_wexin_id` varchar(255) NOT NULL DEFAULT '', # 管理员的微信id
  `admin_email` varchar(255) NOT NULL DEFAULT '', # 管理员邮箱
  `operation_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体',
  `operation_certificate_no` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体证件号',
  `operator_name` varchar(255) NOT NULL DEFAULT '' COMMENT '运营者姓名',
  `operator_certificate_no` varchar(255) NOT NULL DEFAULT '运营者证件号',
  `editor_id` int(11) NOT NULL DEFAULT 0 COMMENT '编辑人员',
  `auditor_id` int(11) NOT NULL DEFAULT 0 COMMENT '审核人员',
  `annual_verification_time` int(11) NOT NULL DEFAULT 0 COMMENT '年审有效期',
  `is_annual_validity` tinyint(1) NOT NULL DEFAULT 0 COMMENT '年审是否通过，0代表未通过，1代表通过',
  `attention_link` varchar(255) NOT NULL, # 关注链接
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表禁用，1代表启用
  `group_id` int(11) NOT NULL DEFAULT '0', # 类型，默认是0代表未知
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `official_group`;
CREATE TABLE `official_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表禁用，1代表启用
  `name` varchar(255) NOT NULL DEFAULT '', # 公众号分组名
  `desc` varchar(255) NOT NULL DEFAULT '', # 公众号分组描述
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

DROP TABLE IF EXISTS `fans`;
CREATE TABLE `fans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `open_id` varchar(100) DEFAULT '' COMMENT 'OPENID',
  `nickname` varchar(300) DEFAULT '' COMMENT '昵称',
  `signature` varchar(300) DEFAULT '' COMMENT '签名',
  `remark` text COMMENT '备注',
  `sex` tinyint(1) DEFAULT 0 COMMENT '性别，1代表男，2代表女',
  `language` varchar(300) DEFAULT '' COMMENT '语言',
  `city` varchar(300) DEFAULT '' COMMENT '城市',
  `province` varchar(300) DEFAULT '' COMMENT '省',
  `country` varchar(300) DEFAULT '' COMMENT '国家',
  `avator` varchar(300) DEFAULT '' COMMENT '头像',
  `unionid` int(10) unsigned DEFAULT '0' COMMENT 'unionid',
  `liveness` int(10) unsigned DEFAULT '0' COMMENT '用户活跃度',
  `subscribed_at` int(10) NOT NULL DEFAULT '0' COMMENT '关注时间',
  `last_online_at` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次在线时间',
  `status` smallint(1) NOT NULL DEFAULT '1' COMMENT '0代表删除，1代表正常',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `fans_tag`;
CREATE TABLE `fans_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(50) DEFAULT NULL COMMENT '标签名称',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `token` varchar(100) DEFAULT NULL COMMENT 'token',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `fans_tag_map`;
CREATE TABLE `fans_tag_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) DEFAULT NULL COMMENT 'uid',
  `tag_id` int(10) DEFAULT NULL COMMENT 'tag_id',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '菜单父id',
  `name` varchar(30) DEFAULT '' COMMENT '',
  `type` smallint(4) DEFAULT '0' COMMENT '1->click/2->view/3->scancode_push/4->scancode_waitmsg/5->pic_sysphoto/6->pic_photo_or_album/7->pic_weixin/8->location_select',
  `key` varchar(200) DEFAULT '' COMMENT '菜单触发值',
  `sort` smallint(4) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表禁用，1代表启用
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `replies`;
CREATE TABLE `replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回复类型，1-follow，2-no-match，3-keywords',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '规则名称',
  `trigger_keywords` varchar(500) DEFAULT '' COMMENT '触发文字',
  `trigger_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '触发条件类型, 0->equal, 1->contain',
  `content` text COMMENT '触发内容 events',
  `status` tinyint(1) NOT NULL DEFAULT  '1', # 状态，0代表禁用，1代表启用
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `fans_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝id 不存在时为公众号回复',
  `sent_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息发送时间 OR 消息回复时间',
  `resource_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应消息资源',
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息回复id',
  `content` text COMMENT '消息内容',
  `msg_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息回复id',
  `is_favorate` tinyint(1) NOT NULL DEFAULT '0', # 是否收藏，0代表为收藏，1代表收藏
  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表删除，1代表为未回复，2代表已回复
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `messages_resources`;
CREATE TABLE `messages_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `detail` text COMMENT '详细',
  `type` smallint(4) DEFAULT '0' COMMENT '1->text/2->image/3->voice/4->shortvideo/5->link/6->location',
  `sync_status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表未同步，1代表已经完成同步
  `status` tinyint(1) NOT NULL DEFAULT  '1', # 状态，0代表删除，1代表正常
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `manager_log`;
CREATE TABLE `manager_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `official_account_id` int(10) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `created_at` int(10) NOT NULL,
  `ip` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `analysis`;
CREATE TABLE `analysis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  -- // TODO 二期完成
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `announcement`;
CREATE TABLE `announcement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '发起人',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '标题',
  `content` text NOT NULL COMMENT '公告内容',
  `is_top` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否置顶，0代表不置顶，1代表置顶',
  `created_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT 0 COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `user_announcement_map`;
CREATE TABLE `user_announcement_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '发起人',
  `announcement_id` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '公告id',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已读, 0代表未读，1代表已经读',
  `created_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT 0 COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '公司名字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT 0 COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into `company` (`id`, `name`, `description`) values (1, '十点信息科技有限公司', '微信业务相关');
