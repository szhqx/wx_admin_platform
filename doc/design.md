# 系统设计文档

## 系统架构
- 基于yii2框架
- 前后端分离，本系统主要提供api接口
- 底层数据存储采用mysql，采用阿里云的mysql服务
- 缓存采用redis
- 异步任务
  - 采用beanstalkd
  - 采用python的[supervisord][3]来监控beanstalkd进程
  - 我们另外搭建了一个UI界面的监控程序，可以考虑使用
- 定时任务，采用crontab
- 微信交互采用开源库[overture][1]

## 数据库设计说明
- 请参考本文件夹下的`db.md`

## api文档
- 请参考本文件夹下的`api.md`

## 目录说明
```
.
├── README.md
├── db （数据库文件，每个版本独立分开放，主要是sql文件）
│   ├── 1.0
│   │   ├── create_article_db.sql
│   │   ├── create_mass_db.sql
│   │   ├── create_messages_db.sql
│   │   ├── init_db.sql
│   │   ├── init_permission.sql
│   │   ├── modify_article_material.sql
│   │   ├── schema.sql
│   │   ├── update_article.sql
│   │   ├── update_mass.sql
│   │   ├── update_material.sql
│   │   └── update_official_account.sql
│   ├── 2.0
│   │   ├── alter_authority_role.sql
│   │   └── create_user_role_map.sql
│   ├── init_sys
│   │   ├── db1.sql
│   │   └── statistic_menu.sql
│   └── readme.md
├── deploy （部署文件目录）
│   ├── crontab
│   ├── fabfile.py
│   └── fabfile.pyc
├── dist （打包专用目录）
│   └── wx_admin_platform.tar.gz
├── doc （所有关于这个系统的说明都在这里）
│   ├── api.md （api文档）
│   ├── backup （备份的一些旧文档）
│   │   └── db.bk.md
│   ├── cached.md （缓存设计说明）
│   ├── db.md （数据库设计说明）
│   ├── deploy.md （部署说明文档）
│   ├── design （每个功能模块的设计说明，在开发前，我们都有这个习惯）
│   │   └── ad.md
│   ├── design.md （系统的设计说明）
│   ├── permissions.md （权限相关说明）
│   ├── permssion_map.md （权限映射说明）
│   ├── status.md（系统的状态码设计）
│   └── wechat_sdk.md （对overture这个库的修改说明，为了后续修改的考虑，没有采用composer的方式来使用这个开源库）
├── scripts （放一些运维脚本）
│   ├── 2.0
│   │   ├── update_permission.php
│   │   ├── update_role.php
│   │   └── user_role.php
│   ├── README.md
│   └── init_sys.php
└── src （真正的代码目录，每个目录的具体使用，基本跟yii2的目录结构保持一致，保留了frontend目录，没有backend目录。具体的细节，可以直接参考Yii2的文档说明）
    ├── README.md
    ├── common
    │   ├── config
    │   │   ├── bootstrap.php
    │   │   ├── main-local.php
    │   │   ├── main.php
    │   │   ├── params-local.php
    │   │   └── params.php
    │   ├── helpers
    │   │   ├── ArticleTrait.php
    │   │   ├── Cron.php
    │   │   ├── DataAuthManager.php
    │   │   ├── EnvHelper.php
    │   │   ├── ExAuthManager.php
    │   │   ├── FileUtil.php
    │   │   ├── OssUtils.php
    │   │   ├── Utils.php
    │   │   └── WechatHelper.php
    │   ├── libraries
    │   │   └── CustomWechat
    │   │       ├── socialite
    │   │       │   ├── LICENSE.txt
    │   │       │   ├── README.md
    │   │       │   ├── composer.json
    │   │       │   ├── phpunit.xml
    │   │       │   ├── src
    │   │       │   │   ├── AccessToken.php
    │   │       │   │   ├── AccessTokenInterface.php
    │   │       │   │   ├── AuthorizeFailedException.php
    │   │       │   │   ├── Config.php
    │   │       │   │   ├── FactoryInterface.php
    │   │       │   │   ├── HasAttributes.php
    │   │       │   │   ├── InvalidArgumentException.php
    │   │       │   │   ├── InvalidStateException.php
    │   │       │   │   ├── ProviderInterface.php
    │   │       │   │   ├── Providers
    │   │       │   │   │   ├── AbstractProvider.php
    │   │       │   │   │   ├── DoubanProvider.php
    │   │       │   │   │   ├── FacebookProvider.php
    │   │       │   │   │   ├── GitHubProvider.php
    │   │       │   │   │   ├── GoogleProvider.php
    │   │       │   │   │   ├── LinkedinProvider.php
    │   │       │   │   │   ├── QQProvider.php
    │   │       │   │   │   ├── WeChatProvider.php
    │   │       │   │   │   └── WeiboProvider.php
    │   │       │   │   ├── SocialiteManager.php
    │   │       │   │   ├── User.php
    │   │       │   │   └── UserInterface.php
    │   │       │   └── tests
    │   │       │       └── OAuthTest.php
    │   │       └── wechat
    │   │           ├── BACKERS.md
    │   │           ├── LICENSE
    │   │           ├── README.md
    │   │           ├── composer.json
    │   │           └── src
    │   │               ├── Broadcast
    │   │               │   ├── Broadcast.php
    │   │               │   ├── LICENSE.txt
    │   │               │   ├── MessageBuilder.php
    │   │               │   ├── README.md
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Card
    │   │               │   ├── Card.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Core
    │   │               │   ├── AbstractAPI.php
    │   │               │   ├── AccessToken.php
    │   │               │   ├── Exception.php
    │   │               │   ├── Exceptions
    │   │               │   │   ├── ApiExceedException.php
    │   │               │   │   ├── FaultException.php
    │   │               │   │   ├── HttpException.php
    │   │               │   │   ├── InvalidArgumentException.php
    │   │               │   │   ├── InvalidConfigException.php
    │   │               │   │   ├── RuntimeException.php
    │   │               │   │   └── UnboundServiceException.php
    │   │               │   ├── Http.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Device
    │   │               │   ├── Device.php
    │   │               │   ├── DeviceHttpException.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Encryption
    │   │               │   ├── EncryptionException.php
    │   │               │   ├── Encryptor.php
    │   │               │   └── composer.json
    │   │               ├── Foundation
    │   │               │   ├── Application.php
    │   │               │   ├── Config.php
    │   │               │   └── ServiceProviders
    │   │               │       ├── BroadcastServiceProvider.php
    │   │               │       ├── CardServiceProvider.php
    │   │               │       ├── DeviceServiceProvider.php
    │   │               │       ├── JsServiceProvider.php
    │   │               │       ├── MaterialServiceProvider.php
    │   │               │       ├── MenuServiceProvider.php
    │   │               │       ├── NoticeServiceProvider.php
    │   │               │       ├── OAuthServiceProvider.php
    │   │               │       ├── POIServiceProvider.php
    │   │               │       ├── PaymentServiceProvider.php
    │   │               │       ├── QRCodeServiceProvider.php
    │   │               │       ├── ReplyServiceProvider.php
    │   │               │       ├── SemanticServiceProvider.php
    │   │               │       ├── ServerServiceProvider.php
    │   │               │       ├── StaffServiceProvider.php
    │   │               │       ├── StatsServiceProvider.php
    │   │               │       ├── UrlServiceProvider.php
    │   │               │       └── UserServiceProvider.php
    │   │               ├── Js
    │   │               │   ├── Js.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Material
    │   │               │   ├── LICENSE
    │   │               │   ├── Material.php
    │   │               │   ├── README.md
    │   │               │   ├── Temporary.php
    │   │               │   └── composer.json
    │   │               ├── Menu
    │   │               │   ├── LICENSE
    │   │               │   ├── Menu.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Message
    │   │               │   ├── AbstractMessage.php
    │   │               │   ├── Article.php
    │   │               │   ├── DeviceEvent.php
    │   │               │   ├── DeviceText.php
    │   │               │   ├── Image.php
    │   │               │   ├── Link.php
    │   │               │   ├── Location.php
    │   │               │   ├── Material.php
    │   │               │   ├── Music.php
    │   │               │   ├── News.php
    │   │               │   ├── README.md
    │   │               │   ├── Raw.php
    │   │               │   ├── ShortVideo.php
    │   │               │   ├── Text.php
    │   │               │   ├── Transfer.php
    │   │               │   ├── Video.php
    │   │               │   ├── Voice.php
    │   │               │   └── composer.json
    │   │               ├── Notice
    │   │               │   ├── LICENSE
    │   │               │   ├── Notice.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── POI
    │   │               │   ├── LICENSE
    │   │               │   ├── POI.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Payment
    │   │               │   ├── API.php
    │   │               │   ├── LICENSE
    │   │               │   ├── LuckyMoney
    │   │               │   │   ├── API.php
    │   │               │   │   └── LuckyMoney.php
    │   │               │   ├── Merchant.php
    │   │               │   ├── MerchantPay
    │   │               │   │   ├── API.php
    │   │               │   │   └── MerchantPay.php
    │   │               │   ├── Notify.php
    │   │               │   ├── Order.php
    │   │               │   ├── Payment.php
    │   │               │   ├── README.md
    │   │               │   ├── composer.json
    │   │               │   └── helpers.php
    │   │               ├── QRCode
    │   │               │   ├── LICENSE
    │   │               │   ├── QRCode.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Reply
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Reply.php
    │   │               │   └── composer.json
    │   │               ├── Semantic
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Semantic.php
    │   │               │   └── composer.json
    │   │               ├── Server
    │   │               │   ├── BadRequestException.php
    │   │               │   ├── Guard.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Staff
    │   │               │   ├── LICENSE
    │   │               │   ├── MessageBuilder.php
    │   │               │   ├── README.md
    │   │               │   ├── Session.php
    │   │               │   ├── Staff.php
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Stats
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Stats.php
    │   │               │   └── composer.json
    │   │               ├── Store
    │   │               │   ├── LICENSE
    │   │               │   ├── Model
    │   │               │   ├── README.md
    │   │               │   ├── Store.php
    │   │               │   └── composer.json
    │   │               ├── Support
    │   │               │   ├── Arr.php
    │   │               │   ├── Attribute.php
    │   │               │   ├── Collection.php
    │   │               │   ├── File.php
    │   │               │   ├── LICENSE
    │   │               │   ├── Log.php
    │   │               │   ├── README.md
    │   │               │   ├── Str.php
    │   │               │   ├── Url.php
    │   │               │   ├── XML.php
    │   │               │   └── composer.json
    │   │               ├── Url
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Url.php
    │   │               │   └── composer.json
    │   │               └── User
    │   │                   ├── Group.php
    │   │                   ├── LICENSE
    │   │                   ├── README.md
    │   │                   ├── Tag.php
    │   │                   ├── User.php
    │   │                   └── composer.json
    │   └── models
    │       ├── AccountForm.php
    │       ├── Advertisement.php
    │       ├── AdvertisementOfficial.php
    │       ├── AdvertisementType.php
    │       ├── Announcement.php
    │       ├── Article.php
    │       ├── ArticleImageMap.php
    │       ├── ArticleStatistics.php
    │       ├── AuthorityPermission.php
    │       ├── AuthorityRole.php
    │       ├── Company.php
    │       ├── Customer.php
    │       ├── ExcelForm.php
    │       ├── Fans.php
    │       ├── FansGroup.php
    │       ├── FansTag.php
    │       ├── FansTagMap.php
    │       ├── Image.php
    │       ├── LoginForm.php
    │       ├── ManagerLog.php
    │       ├── Mass.php
    │       ├── MassForm.php
    │       ├── Material.php
    │       ├── MaterialCate.php
    │       ├── MaterialForm.php
    │       ├── Menus.php
    │       ├── MenusNews.php
    │       ├── Messages.php
    │       ├── OfficialAccount.php
    │       ├── OfficialAccountForm.php
    │       ├── OfficialGroup.php
    │       ├── ProxyDomain.php
    │       ├── Reply.php
    │       ├── ReplyNews.php
    │       ├── StatisticMenu.php
    │       ├── StatisticMenuLog.php
    │       ├── StatisticNew.php
    │       ├── StatisticNews.php
    │       ├── StatisticUser.php
    │       ├── Teller.php
    │       ├── TellerOrder.php
    │       ├── User.php
    │       └── UserRoleMap.php
    ├── composer.json
    ├── composer.lock
    ├── conf
    │   ├── env.dev （开发环境的环境变量文件）
    │   ├── env.example （模板）
    │   ├── env.pro （生产环境的环境变量文件）
    │   └── env.protest
    ├── console
    │   ├── config
    │   │   ├── bootstrap.php
    │   │   ├── console.php
    │   │   ├── main-local.php
    │   │   ├── main.php
    │   │   ├── params-local.php
    │   │   └── params.php
    │   ├── controllers
    │   │   ├── AnalysisController.php
    │   │   ├── BaseController.php
    │   │   ├── FixStatisticsController.php
    │   │   ├── FixSynController.php
    │   │   ├── MassController.php
    │   │   ├── StatisticsController.php
    │   │   ├── SyncController.php
    │   │   ├── UtilsController.php
    │   │   ├── WorkerController.php
    │   │   ├── WorkerNewsController.php
    │   │   └── clean.sh
    │   └── models
    ├── frontend
    │   ├── assets
    │   │   └── AppAsset.php
    │   ├── codeception.yml
    │   ├── config
    │   │   ├── bootstrap.php
    │   │   ├── console.php
    │   │   ├── db.php
    │   │   ├── main-local.php
    │   │   ├── main.php
    │   │   ├── params-local.php
    │   │   ├── params.php
    │   │   ├── test.php
    │   │   ├── test_db.php
    │   │   └── web.php
    │   ├── controllers
    │   │   ├── AccountController.php
    │   │   ├── AdvertiseController.php
    │   │   ├── AnnouncementController.php
    │   │   ├── ArticleController.php
    │   │   ├── AuthorityController.php
    │   │   ├── BaseController.php
    │   │   ├── CompanyController.php
    │   │   ├── ExcelController.php
    │   │   ├── FansController.php
    │   │   ├── GrapNewsController.php
    │   │   ├── ManagerLogController.php
    │   │   ├── MassController.php
    │   │   ├── MaterialCateController.php
    │   │   ├── MaterialController.php
    │   │   ├── MenusController.php
    │   │   ├── MessageController.php
    │   │   ├── OfficialAccountController.php
    │   │   ├── OfficialGroupController.php
    │   │   ├── ProxyDomainController.php
    │   │   ├── ReplyController.php
    │   │   ├── ServiceController.php
    │   │   ├── ShareController.php
    │   │   ├── SiteController.php
    │   │   ├── StatisticController.php
    │   │   ├── UserController.php
    │   │   └── WechatController.php
    │   ├── libraries
    │   │   └── CustomWechat
    │   │       ├── socialite
    │   │       │   ├── LICENSE.txt
    │   │       │   ├── README.md
    │   │       │   ├── composer.json
    │   │       │   ├── phpunit.xml
    │   │       │   ├── src
    │   │       │   │   ├── AccessToken.php
    │   │       │   │   ├── AccessTokenInterface.php
    │   │       │   │   ├── AuthorizeFailedException.php
    │   │       │   │   ├── Config.php
    │   │       │   │   ├── FactoryInterface.php
    │   │       │   │   ├── HasAttributes.php
    │   │       │   │   ├── InvalidArgumentException.php
    │   │       │   │   ├── InvalidStateException.php
    │   │       │   │   ├── ProviderInterface.php
    │   │       │   │   ├── Providers
    │   │       │   │   │   ├── AbstractProvider.php
    │   │       │   │   │   ├── DoubanProvider.php
    │   │       │   │   │   ├── FacebookProvider.php
    │   │       │   │   │   ├── GitHubProvider.php
    │   │       │   │   │   ├── GoogleProvider.php
    │   │       │   │   │   ├── LinkedinProvider.php
    │   │       │   │   │   ├── QQProvider.php
    │   │       │   │   │   ├── WeChatProvider.php
    │   │       │   │   │   └── WeiboProvider.php
    │   │       │   │   ├── SocialiteManager.php
    │   │       │   │   ├── User.php
    │   │       │   │   └── UserInterface.php
    │   │       │   └── tests
    │   │       │       └── OAuthTest.php
    │   │       └── wechat
    │   │           ├── BACKERS.md
    │   │           ├── LICENSE
    │   │           ├── README.md
    │   │           ├── composer.json
    │   │           └── src
    │   │               ├── Broadcast
    │   │               │   ├── Broadcast.php
    │   │               │   ├── LICENSE.txt
    │   │               │   ├── MessageBuilder.php
    │   │               │   ├── README.md
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Card
    │   │               │   ├── Card.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Core
    │   │               │   ├── AbstractAPI.php
    │   │               │   ├── AccessToken.php
    │   │               │   ├── Exception.php
    │   │               │   ├── Exceptions
    │   │               │   │   ├── FaultException.php
    │   │               │   │   ├── HttpException.php
    │   │               │   │   ├── InvalidArgumentException.php
    │   │               │   │   ├── InvalidConfigException.php
    │   │               │   │   ├── RuntimeException.php
    │   │               │   │   └── UnboundServiceException.php
    │   │               │   ├── Http.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Device
    │   │               │   ├── Device.php
    │   │               │   ├── DeviceHttpException.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Encryption
    │   │               │   ├── EncryptionException.php
    │   │               │   ├── Encryptor.php
    │   │               │   └── composer.json
    │   │               ├── Foundation
    │   │               │   ├── Application.php
    │   │               │   ├── Config.php
    │   │               │   └── ServiceProviders
    │   │               │       ├── BroadcastServiceProvider.php
    │   │               │       ├── CardServiceProvider.php
    │   │               │       ├── DeviceServiceProvider.php
    │   │               │       ├── JsServiceProvider.php
    │   │               │       ├── MaterialServiceProvider.php
    │   │               │       ├── MenuServiceProvider.php
    │   │               │       ├── NoticeServiceProvider.php
    │   │               │       ├── OAuthServiceProvider.php
    │   │               │       ├── POIServiceProvider.php
    │   │               │       ├── PaymentServiceProvider.php
    │   │               │       ├── QRCodeServiceProvider.php
    │   │               │       ├── ReplyServiceProvider.php
    │   │               │       ├── SemanticServiceProvider.php
    │   │               │       ├── ServerServiceProvider.php
    │   │               │       ├── StaffServiceProvider.php
    │   │               │       ├── StatsServiceProvider.php
    │   │               │       ├── UrlServiceProvider.php
    │   │               │       └── UserServiceProvider.php
    │   │               ├── Js
    │   │               │   ├── Js.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Material
    │   │               │   ├── LICENSE
    │   │               │   ├── Material.php
    │   │               │   ├── README.md
    │   │               │   ├── Temporary.php
    │   │               │   └── composer.json
    │   │               ├── Menu
    │   │               │   ├── LICENSE
    │   │               │   ├── Menu.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Message
    │   │               │   ├── AbstractMessage.php
    │   │               │   ├── Article.php
    │   │               │   ├── DeviceEvent.php
    │   │               │   ├── DeviceText.php
    │   │               │   ├── Image.php
    │   │               │   ├── Link.php
    │   │               │   ├── Location.php
    │   │               │   ├── Material.php
    │   │               │   ├── Music.php
    │   │               │   ├── News.php
    │   │               │   ├── README.md
    │   │               │   ├── Raw.php
    │   │               │   ├── ShortVideo.php
    │   │               │   ├── Text.php
    │   │               │   ├── Transfer.php
    │   │               │   ├── Video.php
    │   │               │   ├── Voice.php
    │   │               │   └── composer.json
    │   │               ├── Notice
    │   │               │   ├── LICENSE
    │   │               │   ├── Notice.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── POI
    │   │               │   ├── LICENSE
    │   │               │   ├── POI.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Payment
    │   │               │   ├── API.php
    │   │               │   ├── LICENSE
    │   │               │   ├── LuckyMoney
    │   │               │   │   ├── API.php
    │   │               │   │   └── LuckyMoney.php
    │   │               │   ├── Merchant.php
    │   │               │   ├── MerchantPay
    │   │               │   │   ├── API.php
    │   │               │   │   └── MerchantPay.php
    │   │               │   ├── Notify.php
    │   │               │   ├── Order.php
    │   │               │   ├── Payment.php
    │   │               │   ├── README.md
    │   │               │   ├── composer.json
    │   │               │   └── helpers.php
    │   │               ├── QRCode
    │   │               │   ├── LICENSE
    │   │               │   ├── QRCode.php
    │   │               │   ├── README.md
    │   │               │   └── composer.json
    │   │               ├── Reply
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Reply.php
    │   │               │   └── composer.json
    │   │               ├── Semantic
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Semantic.php
    │   │               │   └── composer.json
    │   │               ├── Server
    │   │               │   ├── BadRequestException.php
    │   │               │   ├── Guard.php
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Staff
    │   │               │   ├── LICENSE
    │   │               │   ├── MessageBuilder.php
    │   │               │   ├── README.md
    │   │               │   ├── Session.php
    │   │               │   ├── Staff.php
    │   │               │   ├── Transformer.php
    │   │               │   └── composer.json
    │   │               ├── Stats
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Stats.php
    │   │               │   └── composer.json
    │   │               ├── Store
    │   │               │   ├── LICENSE
    │   │               │   ├── Model
    │   │               │   ├── README.md
    │   │               │   ├── Store.php
    │   │               │   └── composer.json
    │   │               ├── Support
    │   │               │   ├── Arr.php
    │   │               │   ├── Attribute.php
    │   │               │   ├── Collection.php
    │   │               │   ├── File.php
    │   │               │   ├── LICENSE
    │   │               │   ├── Log.php
    │   │               │   ├── README.md
    │   │               │   ├── Str.php
    │   │               │   ├── Url.php
    │   │               │   ├── XML.php
    │   │               │   └── composer.json
    │   │               ├── Url
    │   │               │   ├── LICENSE
    │   │               │   ├── README.md
    │   │               │   ├── Url.php
    │   │               │   └── composer.json
    │   │               └── User
    │   │                   ├── Group.php
    │   │                   ├── LICENSE
    │   │                   ├── README.md
    │   │                   ├── Tag.php
    │   │                   ├── User.php
    │   │                   └── composer.json
    │   ├── views
    │   └── web
    │       ├── assets
    │       │   ├── customer.xls
    │       │   └── import_tem.xls
    │       ├── css
    │       │   └── site.css
    │       ├── favicon.ico
    │       ├── index-test.php
    │       ├── index.php
    │       └── robots.txt
    ├── requirements.php
    ├── runtime
    ├── tests
    │   ├── _bootstrap.php
    │   ├── _data
    │   ├── _output
    │   ├── _support
    │   │   ├── AcceptanceTester.php
    │   │   ├── FunctionalTester.php
    │   │   └── UnitTester.php
    │   ├── acceptance
    │   │   ├── AboutCest.php
    │   │   ├── ContactCest.php
    │   │   ├── HomeCest.php
    │   │   ├── LoginCest.php
    │   │   └── _bootstrap.php
    │   ├── acceptance.suite.yml.example
    │   ├── bin
    │   │   ├── yii
    │   │   └── yii.bat
    │   ├── functional
    │   │   ├── ContactFormCest.php
    │   │   ├── LoginFormCest.php
    │   │   └── _bootstrap.php
    │   ├── functional.suite.yml
    │   ├── unit
    │   │   ├── _bootstrap.php
    │   │   └── models
    │   │       ├── ContactFormTest.php
    │   │       ├── LoginFormTest.php
    │   │       └── UserTest.php
    │   └── unit.suite.yml
    ├── worker_process
    │   ├── product.conf
    │   └── supervisord.conf
    ├── yii
    └── yii.bat
```

## 部署说明
- 部署采用脚本工具[fabric][2]
- 部署相关文件，全部放在`/path/to/project/deploy/`目录下面
- 发布到测试环境，`fab product_test deploy`
- 发布到正式环境，`fab product deploy`
- 关于beanstalkd的运维
  - 停止beanstalkd，`fab product stop_remote_worker`
  - 开始beanstalkd，`fab product start_remote_worker`
- 更多命令，可以查看`fab -h`或者`fab -l`

## 服务器说明
- 目前有两种环境，一个是针对内部开发的测试环境，一个是部署在线上的产品环境
- 具体的服务器信息，另外再交接

## 部分关键功能说明

### 异步任务
- 主要用在拉取公众号文章
- 目前就一个beanstalkd进程在跑，后续可以开多几个进程来应付公众号过多的情况

### 统计
- 目前都是每天定时任务在跑，全表扫描

### 权限控制
- 基于yii2的RBAC的方案进行修改，结合本系统的具体业务
- 如果需要增加新的权限，需要重新跑两个脚本，分别是`/path/to/project/`目录下的，`update_permission.php`和`update_role.php`脚本

### session控制
- 基于文件缓存的方式


[1]: https://github.com/overtrue/wechat
[2]: http://www.fabfile.org/
[3]: http://supervisord.org/
