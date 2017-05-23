<?php

return [

    'user.passwordResetTokenExpire' => 3600,

    'availableLocales'=>[
        'zh-CN' => '简体中文'
    ],

    # 群发最大失败次数
    'MAX_MASS_FAIL_TIMES'=>5,

    # TODO fix me for alias not working here
    "LOCK_DIR" => env('LOCK_DIR', '/mnt/tmp/'),
    "LOCK_SUFFIX" => env('LOCK_SUFFIX', '.lock'),

    "HOST_INFO" => [
        "SCHEME" => env('HOST_SHCEME', 'http'),
        "API_DOMAIN_INFO" => env('API_DOMAIN_INFO', 'admin-platform.kuvdm.cn'),
        "FRONTEND_DOMAIN_INFO" => env('FRONTEND_DOMAIN_INFO', 'static.kuvdm.cn'),
    ],

    'STATUS_CODE_MSG' => [
        "-1" => "系统错误",

        "0" => "ok",

        "10100" => "缺失参数",
        "10101" => "参数不正确",

        "20001" => "登录账号或密码有误",
        "20002" => "修改密码失败",
        "20003" => "请求的方法不存在",
        "20004" => "无权限请求该方法",
        "20005" => "注册用户失败",
        "20006" => "手机号码重复",
        "20007" => "请重新登录",
        "20008" => "原密码错误",
        "20100" => "已发送素材不能修改",
        "20101" => "素材正在被使用中，请先删除群发",
    ],

    # 临时文件路劲
    'TEMPNAME' => env('TEMPNAME', '/mnt/tmp'),

    'ALIYUN_INFO' => [
        "KEY"=>"LTAI8BXkPEIEHWvR",
        "SECRET"=>"6euhZoiUiH7eFwVwPi70OqkooXAa06",
        "END_POINT"=>"oss-cn-shenzhen.aliyuncs.com",
        "ROLE_ARN"=>"",
        "TOKEN_EXPIRE_TIME"=>"3600",
        "BUCKET"=>"wx-admin-platform-new",
        "REQUEST_SCHEME"=>"http"
    ],

    'DEFAULT_HEAD_IMG' =>'http://p1.bpimg.com/581835/b997192675a2a2ee.png',

    'UEDITOR_OSS_INFO' => [
        /* 上传图片配置项 */
        "imageActionName"=>"uploadimage", /* 执行上传图片的action名称 */
        "imageFieldName"=>"upfile", /* 提交的图片表单名称 */
        "imageMaxSize"=>2048000, /* 上传大小限制，单位B */
        "imageAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
        "imageCompressEnable"=>true, /* 是否压缩图片,默认是true */
        "imageCompressBorder"=>1600, /* 图片压缩最长边限制 */
        "imageInsertAlign"=>"none", /* 插入的图片浮动方式 */
        "imageUrlPrefix"=>"", /* 图片访问路径前缀 */
        "imagePathFormat"=>"/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
        /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
        /* {time} 会替换成时间戳 */
        /* {yyyy} 会替换成四位年份 */
        /* {yy} 会替换成两位年份 */
        /* {mm} 会替换成两位月份 */
        /* {dd} 会替换成两位日期 */
        /* {hh} 会替换成两位小时 */
        /* {ii} 会替换成两位分钟 */
        /* {ss} 会替换成两位秒 */
        /* 非法字符 \ : * ? " < > | */
        /* 具请体看线上文档: fex.baidu.com/ueditor/#use-format_upload_filename */
        /* 涂鸦图片上传配置项 */
        "scrawlActionName"=>"uploadscrawl", /* 执行上传涂鸦的action名称 */
        "scrawlFieldName"=>"upfile", /* 提交的图片表单名称 */
        "scrawlPathFormat"=>"/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "scrawlMaxSize"=>2048000, /* 上传大小限制，单位B */
        "scrawlUrlPrefix"=>"", /* 图片访问路径前缀 */
        "scrawlInsertAlign"=>"none",
        /* 截图工具上传 */
        "snapscreenActionName"=>"uploadimage", /* 执行上传截图的action名称 */
        "snapscreenPathFormat"=>"/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "snapscreenUrlPrefix"=>"", /* 图片访问路径前缀 */
        "snapscreenInsertAlign"=>"none", /* 插入的图片浮动方式 */
        /* 抓取远程图片配置 */
        "catcherLocalDomain"=>["127.0.0.1", "localhost", "img.baidu.com"],
        "catcherActionName"=>"catchimage", /* 执行抓取远程图片的action名称 */
        "catcherFieldName"=>"source", /* 提交的图片列表表单名称 */
        "catcherPathFormat"=>"/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "catcherUrlPrefix"=>"", /* 图片访问路径前缀 */
        "catcherMaxSize"=>2048000, /* 上传大小限制，单位B */
        "catcherAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */
        /* 上传视频配置 */
        "videoActionName"=>"uploadvideo", /* 执行上传视频的action名称 */
        "videoFieldName"=>"upfile", /* 提交的视频表单名称 */
        "videoPathFormat"=>"/ueditor/php/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "videoUrlPrefix"=>"", /* 视频访问路径前缀 */
        "videoMaxSize"=>102400000, /* 上传大小限制，单位B，默认100MB */
        "videoAllowFiles"=>[
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */
        /* 上传文件配置 */
        "fileActionName"=>"uploadfile", /* controller里,执行上传视频的action名称 */
        "fileFieldName"=>"upfile", /* 提交的文件表单名称 */
        "filePathFormat"=>"/ueditor/php/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "fileUrlPrefix"=>"", /* 文件访问路径前缀 */
        "fileMaxSize"=>51200000, /* 上传大小限制，单位B，默认50MB */
        "fileAllowFiles"=>[
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ], /* 上传文件格式显示 */
        /* 列出指定目录下的图片 */
        "imageManagerActionName"=>"listimage", /* 执行图片管理的action名称 */
        "imageManagerListPath"=>"/ueditor/php/upload/image/", /* 指定要列出图片的目录 */
        "imageManagerListSize"=>20, /* 每次列出文件数量 */
        "imageManagerUrlPrefix"=>"", /* 图片访问路径前缀 */
        "imageManagerInsertAlign"=>"none", /* 插入的图片浮动方式 */
        "imageManagerAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */
        /* 列出指定目录下的文件 */
        "fileManagerActionName"=>"listfile", /* 执行文件管理的action名称 */
        "fileManagerListPath"=>"/ueditor/php/upload/file/", /* 指定要列出文件的目录 */
        "fileManagerUrlPrefix"=>"", /* 文件访问路径前缀 */
        "fileManagerListSize"=>20, /* 每次列出文件数量 */
        "fileManagerAllowFiles"=>[
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ] /* 列出的文件类型 */
    ],

    # Producing
    'QUEUE_MATERIAL_ARTICLE' => 'sync_article_list',
    'QUEUE_MATERIAL_IMAGE'   => 'sync_image_list',
    'QUEUE_ONCE_MATERIAL_ARTICLE' => 'once_sync_article_list',
    'QUEUE_ONCE_MATERIAL_IMAGE' => 'once_sync_image_list',
    'QUEUE_SYNC_STATISTIC' => 'sync_statistic_data',
    'QUEUE_SYNC_MENU' => 'sync_menus_data',
    'QUEUE_SYNC_REPLY' => 'sync_reply_data',

    # cache time expiration
    'CACHE_WECHAT_EXPIRED_TIME' => 2 * 60,

    'WECHAT_IMG_DOMAIN_PATTERN' => '/https?:\/\/mmbiz.qlogo.cn|https?:\/\/mmbiz.qpic.cn/',

    'WECHAT_IMG_DOMAIN_PATTERN_WITH_POS' => '/(https?:\/\/mmbiz.qlogo.cn)|(https?:\/\/mmbiz.qpic.cn)/',

    'CUSTOM_IMG_DOMAIN' => env('CUSTOM_IMG_DOMAIN', ''),

    'CUSTOM_IMG_DOMAIN_REGEX' => env('CUSTOM_IMG_DOMAIN_REGEX', ''),

    'CURRENT_DOMAIN' => env('CURRENT_DOMAIN', '')
];
