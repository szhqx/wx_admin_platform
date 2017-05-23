# 数据库设计

## 表前缀 yii2cmf_

- authority_permission
```
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
```

- authority_role
```
CREATE TABLE `authority_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text COMMENT '角色描述',
  `company_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '公司id，2.0版本开始这个字段弃用',
  `is_super_admin` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否超级管理员，0代表否，1代表是，弃用',
  `role_type` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT '角色类型，1代表超级管理员，2代表管理员，3代表编辑，4代表财务，5代表商务，以此类推',
  `role_level` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT '角色等级，数字越小，等级越高',
  `permission_id_list` text COMMENT '角色权限id列表，json化',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_company` (`name`, `company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- user
```
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '登录名',
  `nickname` varchar(255) NOT NULL COMMENT '用户名',
  `weixin_id` varchar(255) DEFAULT NULL COMMENT '微信号',
  `authKey` varchar(32) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `login_at` int(11) DEFAULT NULL,
  `blocked_at` int(11) DEFAULT NULL,
  `confirmed_at` int(11) DEFAULT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户的角色id',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `weixin_id` (`weixin_id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- official_account (公众号表)
```
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
  `admin_weixin_id` varchar(255) NOT NULL DEFAULT '', # 管理员的微信id
  `admin_email` varchar(255) NOT NULL DEFAULT '', # 管理员邮箱
  `operation_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体',
  `operation_certificate_no` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体证件号',
  `operator_name` varchar(255) NOT NULL DEFAULT '' COMMENT '运营者姓名',
  `operator_certificate_no` varchar(255) NOT NULL DEFAULT '运营者证件号',
  `editor_id` int(11) NOT NULL DEFAULT 0 COMMENT '编辑人员',
  `auditor_id` int(11) NOT NULL DEFAULT 0 COMMENT '审核人员',
  `company_id` int(11) NOT NULL DEFAULT 0 COMMENT '公司id',
  `annual_verification_time` int(11) NOT NULL DEFAULT 0 COMMENT '年审有效期',
  `is_annual_validity` tinyint(1) NOT NULL DEFAULT 0 COMMENT '年审是否通过，0代表未通过，1代表通过',
  `attention_link` varchar(255) NOT NULL DEFAULT '', # 关注链接
  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表禁用，1代表启用
  `group_id` int(11) NOT NULL DEFAULT '0', # 类型，默认是0代表未知
  `fans_num` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '粉丝数',
  `is_connect` tinyint(2) DEFAULT '0' COMMENT '0未接通 1接通',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY app_id (`app_id`),
  UNIQUE KEY weixin_id (`weixin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- official_group (公众号分组表)
```
CREATE TABLE `official_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表禁用，1代表启用
  `name` varchar(255) NOT NULL DEFAULT '', # 公众号分组名
  `desc` varchar(255) NOT NULL DEFAULT '', # 公众号分组描述
  `company_id` INT(11),
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- material (素材表)
```
CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) DEFAULT 0 COMMENT '所属公众号id',
  `media_id` varchar(100) DEFAULT NULL COMMENT 'media_id，可以是图文素材的，也可以是图片的，等等',
  `original_id` varchar(60) DEFAULT '0' COMMENT '原始微信素材id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->图文, 2->图片, 3->声音, 4->视频，5->封面照片，6->文章照片, 7->模板素材',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(200) DEFAULT '' COMMENT '标题',  // 文字素材无标题
  `description` varchar(360) DEFAULT '' COMMENT '摘要',
  `content` MEDIUMTEXT COMMENT '内容',
  `cover_media_id` varchar(1024) DEFAULT '' COMMENT '封面 media_id',
  `cover_url` varchar(1024) DEFAULT '' COMMENT '封面url',
  `weixin_cover_url` varchar(1024) DEFAULT '' COMMENT '微信的cover url',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `author` varchar(256) DEFAULT '' COMMENT '发布文章的作者',
  `created_from` tinyint(1) DEFAULT 0 COMMENT '0微信同步到server，server同步到微信',
  `source_url` varchar(1024) DEFAULT '' COMMENT '内容连接资源，默认是阿里云的',
  `weixin_source_url` varchar(1024) DEFAULT '' COMMENT '内容连接资源，微信返回的',
  `ad_source_url` varchar(1024) DEFAULT '' COMMENT '推广链接',
  `content_url` varchar(1024) DEFAULT '' COMMENT '原文链接',
  `show_cover_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表不显示，1代表显示',
  `is_legal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表违规，1代表不违规',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0代表未完成，1代表完成',
  `is_synchronized` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表未同步到微信，1代表已同步到微信',
  `order` smallint(4) NOT NULL DEFAULT '0' COMMENT '多图文素材的时候使用，代表文章的顺序，从0开始',
  `fail_times` smallint(4) NOT NULL DEFAULT '0' COMMENT 'fix同步次数',
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表删除，1代表正常
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间，0为马上发布，其他为定时发送',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `official_account_id` (`official_account_id`),
  UNIQUE KEY `media_official` (`media_id`, `official_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- fans（粉丝表）
```
CREATE TABLE `fans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号',
  `open_id` varchar(100)  DEFAULT '' COMMENT 'OPENID',
  `nickname` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` varchar(300)  DEFAULT '' COMMENT '签名',
  `remark` text  COMMENT '备注',
  `sex` tinyint(1) DEFAULT '0' COMMENT '性别，1代表男，2代表女',
  `language` varchar(300)  DEFAULT '' COMMENT '语言',
  `city` varchar(300)  DEFAULT '' COMMENT '城市',
  `province` varchar(300)  DEFAULT '' COMMENT '省',
  `country` varchar(300)  DEFAULT '' COMMENT '国家',
  `avator` varchar(300)  DEFAULT '' COMMENT '头像',
  `unionid` int(10) unsigned DEFAULT '0' COMMENT 'unionid',
  `liveness` int(10) unsigned DEFAULT '0' COMMENT '用户活跃度',
  `subscribed_at` int(10) NOT NULL DEFAULT '0' COMMENT '关注时间',
  `last_online_at` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次在线时间',
  `status` smallint(1) NOT NULL DEFAULT '1' COMMENT '0代表删除，1代表正常',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `tagid_list` varchar(300)  DEFAULT '' COMMENT '标签数组（序列号存储）',
  `is_syc` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未同步 1已同步',
  `mark_name` varchar(100)  DEFAULT '' COMMENT '微信备注名',
  `is_subscribe` tinyint(3) DEFAULT '1' COMMENT '是否关注',
  PRIMARY KEY (`id`),
  KEY `indexs` (`account_id`, `is_syc`, `open_id`, `nickname`, `status`, `group_id`);
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

- fans_tag (标签)
```
CREATE TABLE `fans_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(50) COLLATE  DEFAULT NULL COMMENT '标签名称',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `token` varchar(100) COLLATE  DEFAULT NULL COMMENT 'token',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  `wechat_tag_id` int(11) DEFAULT '0' COMMENT '微信端Id',
  `wechat_tag_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '微信端名',
  `wechat_tag_count` int(20) DEFAULT '0' COMMENT '微信端数',
  PRIMARY KEY (`id`),
  KEY `indexs` (`official_account_id`, `is_sync`, `title`, `wechat_tag_id`, `wechat_tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

- fans_tag_map （粉丝标签表）
```
CREATE TABLE `fans_tag_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) DEFAULT NULL COMMENT 'uid',
  `tag_id` int(10) DEFAULT NULL COMMENT 'tag_id',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indexs`(`tag_id`, `uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- menus (公众号菜单表)
```
CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '菜单父id',
  `id_s` int(11) DEFAULT '0',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '1->click/2->view/3->scancode_push/4->scancode_waitmsg/5->pic_sysphoto/6->pic_photo_or_album/7->pic_weixin/8->location_select',
  `key` varchar(200) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '菜单触发值',
  `url` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '为view时的url',
  `media_id` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` smallint(4) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

- reply（自动回复表）
```
CREATE TABLE `reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '公众号Id',
  `type_reply` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0被添加自动回复 1消息自动回复 2关键字自动回复',
  `type_msg` tinyint(2) NOT NULL DEFAULT '5' COMMENT '1->article, 2->image, 3->voice, 4->video, 5->text',
  `wx_media_id` varchar(200) DEFAULT NULL,
  `content` text COMMENT '内容（可以是HTML）',
  `keyword` varchar(1000) DEFAULT '' COMMENT '关键字',
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8
```

- messages（消息列表）
```
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `fans_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝id 不存在时为公众号回复',
  `msg_id` bigint(40) DEFAULT NULL,
  `is_reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已回复',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `msg_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '消息类型',
  `content` text COLLATE utf8_unicode_ci COMMENT '消息内容',
  `media_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imgurl` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '图片url',
  `voice_format` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '音频类型',
  `recognition` varchar(100) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '自动识别音频',
  `thumb_media_id` varchar(200) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '视频消息缩略图的媒体id',
  `is_collection` tinyint(2) DEFAULT '0',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

- message_resources（消息资源列表）
```
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
```

- manager_log（前端的管理日志表）
```
CREATE TABLE `manager_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `official_account_id` int(10) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `created_at` int(10) NOT NULL,
  `ip` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `indexs` (`official_account_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- announcement(公告表)
```
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
```

- company (公司表)
```
CREATE TABLE `company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '公司名字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '1表示正常 0表示禁用',
  `created_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT 0 COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

```
REATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `is_multi` tinyint(1) DEFAULT '0' COMMENT '是否多图文，0否，1是',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `type` smallint(4) DEFAULT '0' COMMENT '素材类型，1->图文, 2->图片, 3->声音, 4->视频，5->封面照片，6->文章照片',
  `description` varchar(360) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` MEDIUMTEXT COMMENT '内容',
  `cover_url` varchar(1024) COMMENT '封面url',
  `source_url` varchar(1024) DEFAULT '' COMMENT '内容连接资源，默认是阿里云的',
  `weixin_source_url` varchar(1024) DEFAULT '' COMMENT '内容连接资源，微信返回的',
  `ad_source_url` varchar(1024) DEFAULT '' COMMENT '推广链接',
  `user_id` int(10) DEFAULT NULL COMMENT '创建人id',
  `author` varchar(256) DEFAULT '' COMMENT '发布内容的作者',
  `official_account_id` int(10) DEFAULT 0 COMMENT '所属公众号id',
  `material_id` int(10) unsigned NOT NULL,
  `show_cover_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表不显示，1代表显示',
  `is_legal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0代表违规，1代表不违规',
  `order` smallint(4) NOT NULL DEFAULT '0' COMMENT '多图文素材的时候使用，代表文章的顺序，从0开始',
  `mass_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群发的id',
  `msg_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '微信端返回的msg_id',
  `msg_data_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信端返回的msg_data_id；如果是多图文，会叠上order',
  `int_page_read_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章阅读数',
  `add_to_fav_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章点赞数',
  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表删除，1代表正常
  `published_at` int(10) NOT NULL DEFAULT '0' COMMENT '发布的时间',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

- mass(群发表)
```
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
```

- article_statistics(文章数据统计表 - 总表 - 小时、天维度的后续再考虑)
```
CREATE TABLE `article_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '统计的文章id',
  `msg_data_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信端返回的msg_data_id；如果是多图文，会叠上order',
  `int_page_read_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图文页（点击群发图文卡片进入的页面）的阅读人数',
  `int_page_read_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图文页的阅读次数',
  `ori_page_read_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0',
  `ori_page_read_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原文页的阅读次数',
  `target_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '送达人数，一般约等于总粉丝数（需排除黑名单或其他异常情况下无法收到消息的粉丝）',
  `add_to_fav_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏的人数',
  `add_to_fav_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏的次数',
  `share_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分享的人数，好友',
  `share_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分享的次数，好友',
  `status` tinyint(1) NOT NULL DEFAULT '1', # 状态，0代表删除，1代表正常
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

- statistic_new
```
CREATE TABLE `statistic_new` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `company_id` int(11) DEFAULT '1' NOT NULL,
  `ref_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文章群发出日期',
  `stat_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据统计日期',
  `msgid` varchar(100) NOT NULL DEFAULT '0' COMMENT '图文消息id',
  `title` varchar(200) NOT NULL DEFAULT '0' COMMENT '标题',
  `user_source` int(11) NOT NULL DEFAULT '0' COMMENT '原文页的阅读次数',
  `target_user` int(10) NOT NULL DEFAULT '0' COMMENT '总用户量',
  `int_page_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '图文页（点击群发图文卡片进入的页面）的阅读人数',
  `int_page_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '图文页的阅读次数',
  `ori_page_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0',
  `ori_page_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '原文页的阅读次数',
  `share_user` int(10) NOT NULL DEFAULT '0' COMMENT '分享的人数',
  `share_count` int(10) NOT NULL DEFAULT '0' COMMENT '分享的次数',
  `add_to_fav_user` int(10) NOT NULL DEFAULT '0' COMMENT '收藏的次数',
  `add_to_fav_count` int(10) NOT NULL DEFAULT '0' COMMENT '收藏的次数',
  `int_page_from_session_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '公众号会话阅读人数',
  `int_page_from_session_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '公众号会话阅读次数',
  `int_page_from_hist_msg_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '历史消息页阅读人数',
  `int_page_from_hist_msg_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '历史消息页阅读次数',
  `int_page_from_feed_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '朋友圈阅读人数',
  `int_page_from_feed_read_count` int(10) NOT NULL DEFAULT '0' COMMENT ' 朋友圈阅读次数',
  `int_page_from_friends_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '好友转发阅读人数',
  `int_page_from_friends_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '好友转发阅读次数',
  `int_page_from_other_read_user` int(10) NOT NULL DEFAULT '0' COMMENT '其他场景阅读人数',
  `int_page_from_other_read_count` int(10) NOT NULL DEFAULT '0' COMMENT '其他场景阅读次数',
  `feed_share_from_session_user` int(10) NOT NULL DEFAULT '0' COMMENT '公众号会话转发朋友圈人数',
  `feed_share_from_session_cnt` int(10) NOT NULL DEFAULT '0' COMMENT '公众号会话转发朋友圈次数',
  `feed_share_from_feed_user` int(10) NOT NULL DEFAULT '0' COMMENT '朋友圈转发朋友圈人数',
  `feed_share_from_feed_cnt` int(10) NOT NULL DEFAULT '0' COMMENT '朋友圈转发朋友圈次数',
  `feed_share_from_other_user` int(10) NOT NULL DEFAULT '0' COMMENT '其他场景转发朋友圈人数',
  `feed_share_from_other_cnt` int(10) NOT NULL DEFAULT '0' COMMENT '其他场景转发朋友圈次数',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8
```

- statistic_news
```
CREATE TABLE `statistic_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `company_id` int(11) DEFAULT '1' NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8
```

- statistic_user
```
CREATE TABLE `statistic_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属公众号id',
  `company_id` int(11) DEFAULT '1' NOT NULL,
  `ref_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据的日期',
  `user_source` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '用户的渠道，数值代表的含义如下：\n0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页） 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告',
  `new_user` int(11) NOT NULL DEFAULT '0' COMMENT '新增的用户数量',
  `cancel_user` int(11) NOT NULL DEFAULT '0' COMMENT '取消关注的用户数量，new_user减去cancel_user即为净增用户数量',
  `cumulate_user` int(11) NOT NULL DEFAULT '0' COMMENT '总用户量',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8
```

- article_image_map
```
CREATE TABLE `article_wechat_image_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `source_url` varchar(255) NOT NULL DEFAULT '' COMMENT "图片链接",
  `wechat_source_url` varchar(255) NOT NULL DEFAULT '' COMMENT "微信端的图片链接",
  PRIMARY KEY (`id`),
  KEY `source_url` (`source_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

- advertisement
```
CREATE TABLE `advertisement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接单人',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户',
  `order_amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单金额',
  `deposit` int(11) NOT NULL DEFAULT '0' COMMENT '订金金额',
  `receipt_date` int(11) NOT NULL DEFAULT '0' COMMENT '接单日期',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0->操作中 1->已完成',
  `order_id` varchar(21) DEFAULT NULL COMMENT '订单id',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_userid_cus` (`user_id`,`customer_id`,`company_id`),
  UNIQUE KEY order_id (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8
```

- advertisement_official
```
CREATE TABLE `advertisement_official` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ad_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '广告id',
  `ad_position` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位',
  `retain_day` tinyint(2) NOT NULL DEFAULT '0' COMMENT '保留小时数',
  `type_id` int(10) NOT NULL DEFAULT '0' COMMENT '产品类型id',
  `official_account_id` int(10) NOT NULL DEFAULT '0' COMMENT '公众号',
  `material_id` int(11) NOT NULL DEFAULT '0' COMMENT '文章Id',
  `send_date` int(11) NOT NULL DEFAULT '0' COMMENT '发送日期',
  `company_id` int(11) DEFAULT '0',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单',
  `million_fans_price` int(11) NOT NULL DEFAULT '0' COMMENT '万粉单价',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0->待发送 1->已发送 2->已结束',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_ad_mate` (`material_id`,`ad_id`,`type_id`,`official_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8
```

- advertisement_type
```
CREATE TABLE `advertisement_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '类型名称',
  `desc` varchar(100) NOT NULL DEFAULT '' COMMENT '类型描述',
  `company_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8
```

- customer
```
CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `customer` varchar(60) NOT NULL DEFAULT '' COMMENT '客户昵称',
  `realname` varchar(60) COMMENT '真实姓名',
  `tel` varchar(60) COMMENT '电话',
  `qq` varchar(60) NOT NULL DEFAULT '' COMMENT 'qq',
  `company` varchar(60) COMMENT '公司名称',
  `type_ids` varchar(100) DEFAULT '' COMMENT '类型id',
  `mark` varchar(200) COMMENT '备注',
  `company_id` int(11) unsigned NOT NULL DEFAULT 0,
  `wechat_id` varchar(60) COMMENT '微信名',
  `created_at` int(11) unsigned DEFAULT 0,
  `updated_at` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8
```

- teller
```
CREATE TABLE `teller` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出纳人员',
  `customer` varchar(100) NOT NULL DEFAULT '' COMMENT '客户',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `receipt_date` int(11) NOT NULL DEFAULT '0' COMMENT '收款日期',
  `order_comment` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '款项说明',
  `company_id` int(11) DEFAULT '0',
  `receipt_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收付银行',
  `receipt_bank_num` varchar(100) NOT NULL DEFAULT '' COMMENT '收付账号',
  `pay_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '付款银行',
  `pay_bank_num` varchar(100) NOT NULL DEFAULT '' COMMENT '付款账户',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8
```

- teller_order
```
CREATE TABLE `teller_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `teller_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应的出纳号',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8
```

- statistic_menu
```
CREATE TABLE `statistic_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `click_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击人数',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
  `ref_date` int(11) NOT NULL DEFAULT '0' COMMENT '统计日期',
  `menus_name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `value` varchar(500) NOT
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8
```

- statistic_menu_log
```
CREATE TABLE `statistic_menu_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `official_account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公众号id',
  `click_user` varchar(200) NOT NULL DEFAULT '0' COMMENT '点击人',
  `key` varchar(500) NOT NULL DEFAULT '' COMMENT 'key值',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8
```

- user_role_map（一对多表）
```
CREATE TABLE `user_role_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级角色的uid',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  CONSTRAINT user_role UNIQUE (user_id, role_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
```

- proxy_domain（跳转域名列表）
```
CREATE TABLE `proxy_domain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `domain` varchar(255) DEFAULT NULL COMMENT '需要养的域名',
  `req_nums` int(11) DEFAULT '0' COMMENT '被调用次数',
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT domain UNIQUE (domain)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
```
