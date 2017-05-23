
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
  `admin_weixin_id` varchar(255) NOT NULL DEFAULT '', # 管理员的微信id
  `admin_email` varchar(255) NOT NULL DEFAULT '', # 管理员邮箱
  `operation_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体',
  `operation_certificate_no` varchar(255) NOT NULL DEFAULT '' COMMENT '运营主体证件号',
  `operator_name` varchar(255) NOT NULL DEFAULT '' COMMENT '运营者姓名',
  `editor_id` int(11) NOT NULL DEFAULT 0 COMMENT '编辑人员',
  `auditor_id` int(11) NOT NULL DEFAULT 0 COMMENT '审核人员',
  `company_id` int(11) NOT NULL DEFAULT 0 COMMENT '公司id',
  `annual_verification_time` int(11) NOT NULL DEFAULT 0 COMMENT '年审有效期',
  `is_annual_validity` tinyint(1) NOT NULL DEFAULT 0 COMMENT '年审是否通过，0代表未通过，1代表通过',
  `attention_link` varchar(255) NOT NULL, # 关注链接
  `group_id` int(11) NOT NULL DEFAULT '0', # 类型，默认是0代表未知
  `status` tinyint(1) NOT NULL DEFAULT '0', # 状态，0代表禁用，1代表启用
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
