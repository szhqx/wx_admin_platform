# API 文档

## 基础信息
- baseUrl: http://admin-platform.kuvdm.cn/index.php?

## 账户相关

### 登录
- uri: r=user/login
- method: POST
- request
```
{
    "phone": "15986327888",
    "password": "test123",
    "rememberMe": 0 # 0表示没勾选，1代表勾选
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
       "user_info": {
          "id": 1,
          "phone": "15233331123",
          "nickname": "123kljasd",
          "weixin_id": "asdj123",
          "role_info": [
            "id": 1,
            "name": "超级管理员",
           // "is_super_admin": 1,
           // "permission_list": [1,2,3,4],
            "role_type": 1, # 角色类型，1代表超级管理员，2代表管理员，3代表编辑，4代表财务，5代表商务
            "role_level": 1 # 这个级别的level
          ],
          "status": 0,
      }
    }
}
```

### 登出
- uri: r=user/logout
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改密码
- uri: r=user/modify-password
- method: POST
- request
```
{
  "old_password": "asd123"
  "new_password": "12389a7sd)"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改自己的资料
- uri: r=user/modify-personal-info
- method: POST
- request
```
{
  "nickname": "asd123"
  "weixin_id": "weasd891s"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

## 公司管理

### 创建公司
- uri: r=company/create
- method: POST
- request
```
{
    "name": "name", //公司姓名
    "contact": "15080319025", //联系方式
    "phone": "15987899999", //登陆账号
    "password": "test123", //密码
    "nickname": "test",  //公司负责人
    "login_time" : 1531321321321
    "status": 1,  // 1启用  2禁用
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 删除公司
- uri: r=company/delete
- method: POST
- request
```
{
   "id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改公司
- uri: r=company/modify
- method: POST
- request
```
{
   "id": 1,
   "name": "name", //公司名称
   "contact": "test123", 联系方式
   "nickname": "昵称", //公司负责人
   "phone":"4454456" 登陆账号
   "password" : "466545614"  //空表示不修改
   "login_time" : "54251254" //
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 查询公司信息
- uri: r=company/info
- method: POST
- request
```
{
   "id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
      "company_info": {
          "id": 1,
          "description": "15233331123",
          "name": "123kljasd"
      }
    }
}
```

### 获取公司列表
- uri: r=company/info-list
- method: GET
- request
```
{
    "page": 0,  # 翻页页数
    "num": 20, # 每页的页数
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "company_list": [user_info_1, user_info2, ...],
        "page_num": 10 # total/num
    }
}
```


## 用户管理

### 创建用户
- uri: r=account/create
- method: POST
- request
```
{
    "phone": "15987899999",
    "password": "test123",
    "nickname": "test",
    <!-- "weixin_id": "test", -->
    "role_id": 1, # 角色id
    "role_uid_list": [1,2,3,4], # 下属成员的id列表，uid
    "status": 0, # 是否启用
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 删除用户
- uri: r=account/delete
- method: POST
- request
```
{
   "user_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改用户
- uri: r=account/modify
- method: POST
- request
```
{
   "user_id": 1,
   // "phone": "12388887654", // 电话不能改
   "password": "ZKSdjh*&",
   "nickname": "小名", # 不是用来登录的
   <!-- "weixin_id": "asdlkjiu", # 不是用来登录的 -->
   "status": 0,  # 0表示启用，1表示禁用
   "role_id": 1,  # 选填，该公众号的权限角色表，非超级管理员必填
   "role_uid_list": [1,2,3,4], # 选填，下属成员的id列表
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 查询用户信息
- uri: r=account/info
- method: POST
- request
```
{
   "user_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
      "user_info": {
          "id": 1,
          "phone": "15233331123",
          "nickname": "123kljasd",
          <!-- "weixin_id": "asdj123", -->
          "role_info": [
            "id": 1,
            "name": "超级管理员",
            // "is_super_admin": 1,
            // "permission_list": [1,2,3,4]
            "role_type": 1, # 角色类别
            "role_level": 2,  # 角色等级
            "role_uid_list": [1,2,3,4] # 角色下属成员的id列表
          ],
          "status": 0,
      }
    }
}
```

### 获取用户列表
- uri: r=account/info-list
- method: GET
- request
```
{
    "role_type": 1, # 可选，角色类别
    "username": "asdad", # 可选，用户名
    "page": 0,  # 翻页页数
    "num": 20, # 每页的页数
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "user_list": [user_info_1, user_info2, ...], // 没有role_uid_list字段信息
        "page_num": 10 # total/num
    }
}
```



## 权限管理

### 查看角色列表（弃用）
- uri: r=authority/get-role-list
- method: GET
- request
```
{
    "page": 0, # 第一版暂时用不着，暂时放下
    "num": 20 # 第一版暂时用不着，暂时放下
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "role_list": [
            {"id":1, "name": "超级管理员", "is_super_admin": 1, "description": "", "permission_list":[1,2,3,4], "status":1},
            {"id":2, "name": "编辑部长", "is_super_admin": 0, "description": "", "permission_list":[2,3,5,7], "status":0},
            ... ...
        ],
        "page_num": 10 # total/num
    }
}
```

### 创建角色（弃用）
- uri: r=authority/create-role
- method: post
- request
```
{
    "name": "总编辑",
    "description": "这里是角色描述",
    "permission_id_list": [1,2,3,4], # 权限的id列表
    "status": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 编辑角色（弃用）
- uri: r=authority/modify-role
- method: post
- request
```
{
  "role_id": 1,
  "name": "编辑部门",
  "description": "描述",
  "permission_id_list": [1,2,3,4]
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 拉取权限列表（弃用）
- uri: r=authority/get-permission-list
- method: get
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "permission_list" : {
            {"id":1,"name":"official/create","display_name": "添加公众号"},
            ......
        }
    }
}
```

### 拉取多级权限列表
- uri: r=authority/get-role-level-list
- method: get
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "role_list" : {
            {"name":"编辑", "role_type": 1, "subordinate_list": [{"id": 1, "role_level": 1, "name": "编辑主管"}, {"id":2, "role_level":2, "name": "编辑组长"}, ...]},
            {"name":"商务", "role_type": 2, "subordinate_list": [{"id": 1, "role_level": 1, "name": "商务主管"}, {"id": 2, "role_level":2, "name": "商务组长"}, ...]},
            ...
        }
    }
}
```

### 拉取下属列表
- uri: r=authority/get-subordinate-list
- method: get
- request
```
{
  "role_id": 1,
  "user_id": 2
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "user_list" : [user_info_1, user_info_2]
    }
}
```

### 拉取各个角色的列表
- uri: r=authority/get-member-list
- method: get
- request
```
{
  "role_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "user_list" : [user_info_1, user_info_2]
    }
}
```

## 公众号管理

### 增加公众号
- uri: r=official-account/create
- method: post
- request
```
{
  "weixin_id": "1211111",
  "weixin_name": "每日学瑜伽1",
  "weixin_password": "111",
  "official_origin_id": "111",
  "app_id": "wx27dc0fcba515b62f",
  "app_secret": "2cd3534132e4284fa500cf406b83c0ce",
  "encoding_aes_key": "GLo5VzcxX279JFjaNTHemp8f6RysvdKOQWu1wbgnCZP",
  "admin_weixin_id": "11",
  "admin_email": "11",
  "operation_subject": "11",
  "operator_name:": "111",
  "editor_id": 120,
  <!-- "auditor_id": 123, -->
  "annual_verification_time": 1238123129,
  "attention_link": "111",
  "status": 1,
  "is_verified": 1,
  "group_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "id": 1
    }
}
```

### 删除公众号
- uri: r=official-account/delete
- method: post
- request
```
{
  "id": 0
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改公众号
- uri: r=official-account/modify
- method: post
- request
```
{
  "id": 1,
  "weixin_id": "", # 微信号
  "weixin_name: "", # 微信名称
  "weixin_password": "", # 微信密码
  "official_origin_id": "", # 公众号原始id
  "app_id": "", # AppID
  "app_secret": "", # AppSecret
  "encoding_aes_key": "", # EncodingAesKey
  "admin_weixin_id": "", # 管理员的微信id
  "admin_email": "", # 管理员邮箱
  "operation_subject": "", # 运营主体
  "editor_id": 120, # 编辑人员id
  <!-- "auditor_id": 123, # 审核人员id -->
  "is_verified": 1, # 是否认证过，0代表未认证，1代表已认证
  "annual_verification_time": 1238123129, # 年审有效期
  "attention_link": "", # 关注链接
  "status": 0, # 状态，0代表禁用，1代表启用
  "group_id": 1 # 类型，0代表未知，其他
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 获取/搜索 公众号列表
- uri: r=official-account/info-list
- method: post
- request
```
{
  "page": 1, # 非必填，页数
  "num": 20, # 非必填，每页多少条
  "keyword": "公众号名字关键词", # 非必填，查询的关键字
  "group_id": 1, # 非必填，类别
  "editor_id": 2123, # 非必填，编辑人员id
  <!-- "auditor_id": 1231, # 非必填，审核人员id -->
  "fans_num_range_start": 123, # 非必填，关注人数范围，开始
  "fans_num_range_end": 1234, # 非必填，关注人数范围，结束
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "official_account_list": [official_account_info_1, official_account_info_2, ...],
        "page_num": 10 # total/num
    }
}
```

### 查看单个公众号
- uri: r=official-account/info
- method: post
- request
```
{
  "id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "official_account_info": {
            "id": 1,
            "weixin_id": "", # 微信号
            "weixin_name: "", # 微信名称
            "weixin_password": "", # 微信密码
            "official_origin_id": "", # 公众号原始id
            "app_id": "", # AppID
            "app_secret": "", # AppSecret
            "encoding_aes_key": "", # EncodingAesKey
            "token": "", # token
            "admin_weixin_id": "", # 管理员的微信id
            "admin_email": "", # 管理员邮箱
            "operation_subject": "", # 运营主体
            "is_verified": 1, # 是否认证过，0代表未认证，1代表已认证
            "editor_info": {"id":12, "nickname":"peter"}, # 编辑人员信息
            "auditor_info": {"id":14, "nickname": "david"}, # 审核人员信息
            "annual_verification_time": 1238123129, # 年审有效期
            "attention_link": "", # 关注链接
            "group_info": {"id":1, "name": "未知"}, # 类型，0代表未知，其他
            "fans_num": 123, # 累计关注数
        }
    }
}
```

#### 公众号和微信对接
### 页面显示
- uri: r=official-account/connect-info
- method: get
- request
```
{
    "official_account_id": 1,
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "info"=>{
            "url" : "http::wwwsdfa.com/id=2",
            "token" : "sdfafadfadfa",
            "encoding_aes_key" :"adfasdfasdfafas",
        }
    }
}
```

### 页面显示
- uri: r=official-account/connect
- method: get
- request
```
{
    "official_account_id": 1,
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```


### 自动回复
- uri: r=official-account/auto-response


## 分组管理
### 增加分组
- uri: r=official-group/create
- method: POST
- request
```
{
    "name": "test",
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 修改分组
- uri: r=official-group/modify
- method: POST
- request
```
{
    "id": 1, # 分组的id值
    "name": "test",
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {}
}
```

### 查看分组(暂时不做)
- uri: r=official-group/info
- method: POST
- request

### 获取分组列表(不做分页)
- uri: r=official-group/info-list
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "group_info_list":
        [
            {
                "id":"1",
                "name":"\u6050\u6016"
            },
            {
                "id":"2",
                "name":"\u641e\u7b11"
            }
            ......
        ],
    }
}
```
### 把公众号移动到分组
- uri: r=official-group/move
- method: POST
- request
```
{
    "official_account_ids" :[1,2,3],
    "group_id":1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,

}
```
### 删除一个分组
- uri: r=official-group/delete
- method: POST
- request
```
{
    "group_id":1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,

}
```

## 素材管理

### 增加素材
- uri: r=material/create
- method: POST
- request
```
{
  // 公共部分
  "official_account_id": 1, # 哪个公众号的id
  "type": 1, # 素材类型，1代表图文素材，2代表图片，3代表声音，4代表视频，5上传封面图，6代表上传文章图片，7代表素材模板
  "is_completed": 1, // 是否完成
  "is_synchronized": 1, // 是否同步到微信

  // 当type为1，代表图文素材
  "article_list": [
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg","show_cover_pic": 0, "author": "asldajdklj", "order":0, "ad_source_url": "http://baidu.com/a.html"},
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":1, "ad_source_url": "http://baidu.com/b.html"},
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":2, "ad_source_url": "http://google.com/c.html"},
      ... ...
   ],

  // 当type为2，图片的时候
  "image_key": "asdalsdjl",
  "mime_type": 'jpg', # 富媒体类型，只有在上传富媒体的时候必须指定

  // 当type为3，代表声音的时候
  "voice_key": "asdlkasjd",
  "mime_type": 'mp3', # 富媒体类型，只有在上传富媒体的时候必须指定

  // 当type为4，代表视频的时候
  "video_key": "asdkjahsdjkadhsk",
  "mime_type": 'mp4', # 富媒体类型，只有在上传富媒体的时候必须指定

  // 当type为5，代表封面图的时候
  "cover_image_key": "asdkljasdkljasdlkajd",
  "mime_type": 'png', # 富媒体类型，只有在上传富媒体的时候必须指定

  // 当type为6，代表文章图片
  "image_key": "asdjklaskldj78",
  "mime_type": 'png', # 富媒体类型，只有在上传富媒体的时候必须指定
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "material_info"=> {
            "media_id": "asdj",

            // 当type为2的时候
            "id": 1,
            "source_url": "http://google.com/a.jpg",

        }
    }
}
```

### 编辑素材
- uri: r=material/modify
- method: POST
- request
```
{
  "official_account_id": 1,
  "material_id": 1, # 父素材id
  "is_completed": 0, # 是否编辑完成

  // 目前只支持图文素材类型
  "article_list": [
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":0, "ad_source_url": "http://baidu.com/a.html"},
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":1, "ad_source_url": "http://baidu.com/a.html"},
      {"title": "文章1", "description": "描述", "content":"asdasdasd", "cover_media_id":"asdjh129083", "cover_url":"http://google.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order": 2, "ad_source_url": "http://baidu.com/a.html"},
      ... ...
   ],
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "material_id": 1, # 如果是增删章节，返回新的material_id
        "extra_msg_list": [{"order": 1, "msg": "保存失败"}, {...}, ... ]
    }
}
```

### 删除素材
- uri: r=material/delete
- method: POST
- request
```
{
  "id": 1 # 素材id
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 获取素材列表
- uri: r=material/info-list
- method: GET
- request
```
{
  "page": 0, # 非必填，不传代表第一页
  "num": 20, # 非必填，不传默认20条
  "type": 1, # 必填，素材类型，跟创建时指定的类型一致
  "official_account_id": 2, # 必填，公众号的id
  "is_synchronized": 1, # 必填，需要拉取的素材类型，已同步、未同步
  "is_completed": 0, # 非必填，默认全部，是否完成编辑
  "title" : "文章标题"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "page_num": 10 # total/num
        "material_list": [

            // 拉取图文素材类型(单图文/多图文)
            {
            "id":1,
            "is_completed": 1,
            "media_id": "a123908asdlij",
            "create_time": 12312312,
            "item_list"=>
            [{"id":1, "title": "文章1", "description": "描述", "cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":0, "ad_source_url": "http://google.com/article/view/1", "type":1, "is_completed":1},
            {"id":2, "title": "文章1", "description": "描述", "cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":1, "ad_source_url": "http://google.com/article/view/2", "type":1, "is_completed":1},
            {"id":3, "title": "文章1", "description": "描述", "cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":2, "ad_source_url": "http://google.com/article/view/3", "type":1, "is_completed":1}
            ],
            }, ...

            // 拉取图片类型
            {"id":1, "media_id": "12391283dh", "is_completed":1, "create_time": 123123123, "source_url":"http://douban.com/a.jpg", "weixin_source_url":"http://abc.com/a.jpg", "is_completed":1, "type": 1}, ...

            // TODO 拉取其他类型
        ]
    }
}
```

### 获取单个素材的信息
- uri: r=material/info
- method: GET
- request
```
{
  "id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {

      // 拉取图文素材类型(单图文/多图文)
      "material_info": {
        "id":1,
        "media_id": "12312b3kjshd",
        "is_completed": 1,
        "is_synchronized": 1,
        "create_time": 12312312,
        "item_list"=>
            [{"id":1,"media_id":"jkAHSdk7890123", "title": "文章1", "description": "描述", "content":"asdljasdh","cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":0, "ad_source_url": "http://google.com/article/view/1", "type":1, "is_completed":1},
            {"id":2, "media_id":"jkAHSdk7890123","title": "文章1", "description": "描述", "content":"asdljasdh","cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":1, "ad_source_url": "http://google.com/article/view/2", "type":1, "is_completed":1},
            {"id":3,"media_id":"jkAHSdk7890123", "title": "文章1", "description": "描述", "content":"asdljasdh","cover_media_id":"asdjh129083", "cover_url":"http://aa.com/a.jpg", "show_cover_pic": 0, "author": "asldajdklj", "order":2, "ad_source_url": "http://google.com/article/view/3", "type":1, "is_completed":1}
            ],
        }

        // 拉取图片类型
        "material_info": {"id":1, "media_id":"jkAHSdk7890123", "source_url":"http://douban.com/a.jpg", "weixin_source_url":"http://abc.com/a.jpg", "is_completed":1, "type": 1},
    }
}
```

### 获取图文素材的单个图文信息
- uri: r=materil/single-info
- method: GET
- request
```
{
  "id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
      "material_info": {
        "id":2,
        "title": "文章1",
        "description": "描述",
        "content":"asdljasdh",
        "cover_media_id":"asdjh129083",
        "cover_url":"http://aa.com/a.jpg",
        "show_cover_pic": 0,
        "author": "asldajdklj",
        "order":1,
        "source_url": "http://google.com/article/view/2",
        "ad_source_url": "http://google.com/article/view/2",
        "type":1,
        "is_completed":1,
        "is_synchronized":1
      }
    }
}
```

### 同步素材
- uri: r=material/sync
- method: GET
- request
```
{
  "offset":0,
  "type": 1, # 同步的素材类型
  "official_account_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "offset": 20 # 返回位移位置，如果没有更多数据，offset就不传
    }
}
```

### 同步单个素材
- uri: r=material/sync-single
- method: GET
- request
```
{
  "material_id": 123
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "media_id": 123
    }
}
```

### 发送到手机预览
- uri: r=material/preview
- method: post
- request
```
{
  "official_account_id":2,
  "weixin_name": 'aaaa',
  "type":1, //1表示图文消息  2代表图片
  "id": 1 //素材id
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 上传微信图文素材的图片
- uri: r=material/upload-article-image
- method: POST
- request
```
{
    "image_list": ["http://baidu.com/a.jpg", "http://baidu.com/b.jpg", ...],
    "official_account_id": 0 # 公众号id
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "image_map": {
          "http://baidu.com/a.jpg": "http://weixin.com/a.jpg",
          "http://baidu.com/b.jpg": "http://weixin.com/b.jpg"
      }
    }
}
```

## 内容管理

### 获取已发文章列表
- uri: r=mass/get-send-list（直接调用mass模块的api）
- method: GET
- request
```
{
  "page": 0, # 非必填，待获取的文章列表
  "num": 20, # 非必填，每页拉取数量
  "official_account_id": 1, # 非必填，公众号id
  "user_id": 1, # 非必填，用户id
  "type": 1, # 非必填，文章类型
  "pub_at_begin": 1232137807, # 非必填，开始时间
  "pub_at_end": 1232137807, # 非必填，结束时间
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
      "mass_list": [{
          "id": 2, # 群发id
          "type": 1,
          "article_list": [
            {
              "id": 1,
              "cover_url": "http://baidu.com/a.jpg",
              "title": "哈哈哈哈",
              "read_num": 123,
              "type": 1,
              "order": 1,
              "fav_num": 123,
              "editor_info": {"id":1, "nickname":"石饶阁"},
              "receiver": "全部用户",
              "source_url": "http://baidu.com/a.html",
              "ad_source_url": "http://baidu.com/a.html",
              "published_at": 123123123
            }, {}, ...
          ]
        }, {}, {}, ...]
    }
}
```

### 删除已发文章
- uri: r=mass/delete（直接调用mass的delete方法）
- method: POST
- request
```
{
   "mass_id": 1, # 群发id
}
```
- response
```
{
    "msg": "ok",
    "code": 0
}
```

### 查看某篇文章
- uri: r=article/detail
- method: GET
- request
```
{
   "id": 12, # 文章id
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
      "article_info": {
          "id": 1,
          "title": "测试",
          "content": "爱上你的金卡让大家",
          "author": "作者",
          "published_at": 1231293
      }
    }
}
```

### 抓取文章（前端完成）
- uri: r=article/scrap

### 拉取违规文章（暂时实现不了，放到二期做）
- uri: r=article/get-break-rule-list


## 内容分类管理

### 创建分类
- uri: r=category/create
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 删除分类
- uri: r=category/delete
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 修改分类
- uri: r=category/modify
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 查看分类信息
- uri: r=category/info
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 获取分类列表
- uri: r=category/get-list
- method: POST
- request
```
{
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```


## 群发管理

### 获取群发排期列表
- uri: r=mass/info-list
- method: GET
- request
```
{
  "page": 0, # 非必填，待获取的文章列表
  "num": 20, # 非必填，每页拉取数量
  "official_account_id": 1, # 非必填，公众号id
  "user_id": 1, # 非必填，用户id
  "type": 1, # 非必填，文章类型
  "pub_at_begin": 1232137807, # 非必填，开始时间
  "pub_at_end": 1232137807, # 非必填，结束时间
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "mass_list": [
         {
          "id": 1,
          "pub_at": 10012313, # 发布时间
          "type": 1,
          "material_id": 1,
          "material_list": [{
             "id": 1,
             "title": "落红不是无情物，医院三百能修复。",
             "cover_url": "http://baidu.com/a.jpg",
             "type": 1,
             "show_cover_pic": 1,
             "order": 1
             }, ...]
          }, ...]
    }
}
```

### 获取已群发列表
- uri: r=mass/get-send-list
- method: GET
- request
```
{
  "page": 0, # 非必填，待获取的文章列表
  "num": 20, # 非必填，每页拉取数量
  "official_account_id": 1, # 非必填，公众号id
  "user_id": 1, # 非必填，用户id
  "type": 1, # 非必填，文章类型
  "pub_at_begin": 1232137807, # 非必填，开始时间
  "pub_at_end": 1232137807, # 非必填，结束时间
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "mass_list": [{
          "id": 2, # 群发id
          "type": 1,
          "article_list": [
            {
              "id": 1,
              "cover_url": "http://baidu.com/a.jpg",
              "title": "哈哈哈哈",
              "read_num": 123,
              "type": 1,
              "order": 1,
              "fav_num": 123,
              "editor_info": {"id":1, "nickname":"石饶阁"},
              "receiver": "全部用户",
              "source_url": "http://baidu.com/a.html",
              "ad_source_url": "http://baidu.com/a.html",
              "published_at": 123123123
            }, {}, ...
          ]
        }, {}, {}, ...]
    }
}
```

### 添加群发
- uri: r=mass/create
- method: POST
- request
```
{
    "material_id": 1,
    "pub_at": 123891273, # 非必填，如果是定时，请带上这个参数
    "user_tag_id": 1, # 非必填，标签id
}
```
- response
```
{
    "msg": "ok",
    "code": 0
}
```

### 调整群发
- uri: r=mass/modidy
- method: POST
- request
```
{
    "id": 1,
    "material_id": 123, # 非必填，需要更换的素材id
    "pub_at": 123891273, # 非必填，需要更换的发布时间
    "user_tag_id": 2 # 非必填，标签id
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 删除群发
- uri: r=mass/delete
- method: POST
- request
```
{
    "mass_id": 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

## 菜单管理

### 增加菜单
- uri: r=menus/create
- method: post
- request
```
//type='view'  'text'  'img'  'news'
{
    "official_account_id":49,
    "button":[
        {
            "name":"女人秘密",
            "sub_button":[
                {
                    "name":"瘦到88斤",
                    "type":"view",
                    "value": "http://t.cn/RISlY3V"
                },
                {
		            "name": "快速丰胸",
		            "type": "view",
		            "value": "http://t.cn/RIUf7AI"
		        }
            ]
        },
        {
            "name": "小说铺",
            "type": "view",
            "value": "http://t.cn/RJhbEC3"
        },
        {
            "name":"自助服务",
            "sub_button":[
                {
                    "name": "🔥商务合作",
                    "type": "text",
                    "value": "商务合作商务合作商务合作商务合作商务合作商务合作商务合作"
                },
                {
                    "name": "🔥精彩推荐",
                    "type": "view",
                    "value": "http://mp.weixin.qq.com/s?__biz=MzI5ODA3NjEwNA==&mid=100000866&idx=1&sn=ede65b444fc9f4ce64dc2f5edfb171ff&chksm=6caa16c55bdd9fd3830ee1785d032a7a09b3ff8893086c7494dd544e74480f52499d04e4c1a2&scene=20#wechat_redirect"
                },
                {
                    "name": "测试图文",
		            "type": "news",
		            "value": "bineIi8wWSeMDZueEHLziVcH22OgtCdXhEPaRX2HDGs"
                }
            ]
        }
    ]
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 删除菜单(本地列表)
- uri: r=menus/delete
- method: get
- request
```
{
    "menu_id" : 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 修改菜单
- uri: r=menus/update
- method: post
- request
```
{
    "menu_id" : 1
    "official_account_id" :2, //公众号id  必须
    "parent_id" : 0, //必须
    "type" : 'view', //
    "name" : "菜单名称", //必须
    "sort" : 1  //非必须
    "target_id" : "1" //微信端的media_id
    "url" : "http://www.baidu.com" //非必填
    "key_word" : "aaaa" //非必填
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 获取菜单列表
- uri: r=menus/get-list
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "menu_list": [
      {
        "name": "女人秘密",
        "sub_button": [
          {
            "name": "瘦到88斤",
            "type": "view",
            "value": "http://t.cn/RISlY3V"
          },
          {
            "name": "快速丰胸",
            "type": "view",
            "value": "http://t.cn/RIUf7AI"
          }
        ]
      },
      {
        "name": "小说铺",
        "type": "view",
        "value": "http://t.cn/RJhbEC3"
      },
      {
        "name": "自助服务",
        "sub_button": [
          {
            "name": "🔥商务合作",
            "type": "text",
            "value": "商务合作请加QQ：823806611 备注 每日学瑜伽 合作\n\n公众号互推联系QQ：3001117430 备注 每日学瑜伽 合作"
          },
          {
            "name": "🔥精彩推荐",
            "type": "view",
            "value": "http://mp.weixin.qq.com/s?__biz=MzI5ODA3NjEwNA==&mid=100000866&idx=1&sn=ede65b444fc9f4ce64dc2f5edfb171ff&chksm=6caa16c55bdd9fd3830ee1785d032a7a09b3ff8893086c7494dd544e74480f52499d04e4c1a2&scene=20#wechat_redirect"
          },
          {
            "name": "测试",
            "type": "news",
            "value": "bineIi8wWSeMDZueEHLziVcH22OgtCdXhEPaRX2HDGs",
            "news_info": [
              {
                "title": "【2.9】老婆夜晚突然要加班，下班的时候却看到老公……",
                "digest": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
                "cover_url": "http://img.xzhwjx.cn?q=http://mmbiz.qpic.cn/mmbiz_png/X3eO3d2gJ1JknnnGlFGibZfwlgPnH1VzmSaoibQ7zYaun9LwoiaibNxmXvCVzXURrXWRK0JHIUPJm9aQ9uK9kHRoLQ/0?wx_fmt=png"
              },
              {
                "title": "你绝不知道！4个时机男人易出轨？",
                "digest": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
                "cover_url": "http://img.xzhwjx.cn?q=http://mmbiz.qpic.cn/mmbiz_png/X3eO3d2gJ1JknnnGlFGibZfwlgPnH1Vzms6Uwico4rol1e0CQHzTubSeYPeMarbD2Q9am9gLJrcMmqCZPA9MzZIA/0?wx_fmt=png"
              },
              {
                "title": "7个瑜伽体式锻炼核心，让你越练越优雅！",
                "digest": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
                "cover_url": "http://img.xzhwjx.cn?q=http://mmbiz.qpic.cn/mmbiz_png/X3eO3d2gJ1JknnnGlFGibZfwlgPnH1VzmkhNp3E98tl63PTLRwBqCTrVwHsozyNBKShErYvviafdsfl0NYZFnOIw/0?wx_fmt=png"
              }
            ]
          },
          {
            "name": "图片测试",
            "type": "img",
            "value": "K-nqKqCARvPt4CiFZuwr6NXn9WCWog58R4Ozq0OFKzFKcsDNoGM7ncPNksrzESdw"
          }
        ]
      }
    ]
  }
}
```

### 同步到微信
- uri: r=menus/send-menu
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "code":0,
    "msg":"ok"
}
```


## 粉丝管理
### 查看粉丝列表
- uri: r=fans/get-list
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "page" : 0, //非必须
    "num" : 20, //非必须
    "tag_id" : 1  //非必须
    "nickname" : "星空" //非必填
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
        "fans_list" : [
            {
                "id" : 1,
                "nickname" : "星空云",
                "mark_name" : "哈哈",
                "tag" : "美女、帅哥",
                "head_img" : "http://head.jig",
                ......
            }
            ......
        ]
    }
}
```

### 同步粉丝列表
#### 1同步粉丝openid
- uri: r=fans/sync-openid
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : {
        "status" : 1, //0,1 0已完成 1继续
        "url" : 'fans/sync-fans-info' 继续访问的接口地址
    }
}
```
#### 2同步粉丝信息
- uri: r=fans/sync-fans-info
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : {
        "status" : 1,
        "total" : 20000  //总粉丝数
        "synced" : 1000 //已同步数
        "url" : 'fans/sync-fans-info' 继续访问的接口地址
    }
}
```
#### 3同步粉丝分组信息
- uri: r=fans/sync-fans-group
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : {
        "status" : 1, // 4已完成
        "url" : 'fans/sync-fans-info' 继续访问的接口地址
    }
}
```
#### 4同步粉丝分组信息
- uri: r=fans/sync-fans-tag
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : {
        "status" : 0, // 4已完成
        "url" : 'fans/sync-fans-info' 继续访问的接口地址
    }
}
```

### 粉丝分组管理
#### 增加粉丝标签
- uri: r=fans/create-tag
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "tag_name" : "美女",
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

#### 修改粉丝标签
- uri: r=fans/update-tag
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "tag_id" : 1,
    "tag_name" : "美女"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

#### 删除粉丝标签
- uri: r=fans/delete-tag
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "tag_id" : 1,
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

#### 获取粉丝标签列表
- uri: r=fans/get-tag-list
- method: get
- request
```
{
    "official_account_id" : 1,
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : {
        "fans_tag_list" : [
            {
                "id" : 1,
                "title" : "星空云",
                "official_account_id" : 2,
            }
            ......
        ]
    }
}
```


### 对粉丝进行打tag
- uri: r=fans/tagging
- method: post
- request
```
{
    "official_account_id" : 1,
    "fans_ids" : [1,2,3,4,5]
    "tag_id" : 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```


### 对粉丝进行卸载tag
- uri: r=fans/un-tagging
- method: post
- request
```
{
    "official_account_id" : 1,
    "fans_ids" : [1,2,3,4,5]
    "tag_id" : 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 修改粉丝备注
- uri: r=fans/mark
- method: post
- request
```
{
    "official_account_id" : 1,
    "fans_id" : 1,
    "remark_name" : "小星"
}
```
- response
```
{
    "msg": "ok",
    "code": 0
}
```
#### 增加粉丝分组
- uri: r=fans/create-group
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "group_name" : "美女",
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

#### 修改粉丝分组
- uri: r=fans/update-group
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "group_name" : "美女",
    "group_id" : 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```


#### 删除粉丝分组
- uri: r=fans/delete-group
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "group_id" : 1
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```


#### 粉丝分组列表
- uri: r=fans/get-group-list
- method: get
- request
```
{
    "official_account_id" :2, //公众号id  必须
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data" : [
        "fans_group_list" :
        [
            "id" : 1,
            "name" : "美女",
        ]
        .....
    ]
}
```

### 将粉丝加入分组（黑名单）
- uri: r=fans/move-fans-to-group
- method: post
- request
```
{
    "official_account_id" : 1,
    "fans_id" : [1,2,3,4,5],
    "group_id" : 1 //黑名单的分组id是固定为1的，此处留这个字段是为后续扩展需要
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

## 自动回复

### 获取自动回复列表（目前只支持‘文本(text)’、‘图片(image)’、‘图文(article)’的自动回复，暂不支持音频和视频）
- uri: r=reply/get-list
- method: get
- request
```
{
    "official_account_id" : 2,
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "auto_reply": {
      "id": "1",
      "type_msg": "5",
      "keyword": null,
      "rule": null,
      "content": "亲！等你很久了哦！\n欢迎你的到来，么么哒/示爱"
    },
    "msg_reply": {
      "id": "2",
      "type_msg": "5",
      "keyword": null,
      "rule": null,
      "content": "每天回复的信息太多不能一一回复，希望亲可以见谅/"
    },
    "keyword_reply": [
      {
        "id": "3",
        "keyword": "图文 测试图文",
        "rule": "测试图文",
        "type_msg": "1",
        "media_id": "bineIi8wWSeMDZueEHLzieOhIV7VVRFVcToqS-gHcYk",
        "news_info": [
          {
            "title": "73岁老头娶28岁女子，竟然还现场....惊呆全场！",
            "author": "",
            "description": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
            "cover_url": "http://mmbiz.qpic.cn/mmbiz_jpg/X3eO3d2gJ1Lv5gtkJZ9BxLBT0uKyJuzCYgKk7yeyMWuoNGQoiaTu4J1nF0FpHtFFicPUK8RSAZL3BZV4fwDvScMQ/0?wx_fmt=jpeg",
            "content_url": "http://mmbiz.qpic.cn/mmbiz_jpg/X3eO3d2gJ1Lv5gtkJZ9BxLBT0uKyJuzCYgKk7yeyMWuoNGQoiaTu4J1nF0FpHtFFicPUK8RSAZL3BZV4fwDvScMQ/0?wx_fmt=jpeg"
          },
          {
            "title": "2017年这条裤子要打败阔腿裤了，你敢穿吗？",
            "author": "",
            "description": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
            "cover_url": "http://mmbiz.qpic.cn/mmbiz_jpg/XvvLibiaBcw9Pa2ibyHicOwD3NcwaqvJYoiaLfgibwyQHjUVGL03XhI8M7Wlfgic8X8NJmbc0byrLjdcbfG5zibfsicy59Q/640",
            "content_url": "http://mmbiz.qpic.cn/mmbiz_jpg/XvvLibiaBcw9Pa2ibyHicOwD3NcwaqvJYoiaLfgibwyQHjUVGL03XhI8M7Wlfgic8X8NJmbc0byrLjdcbfG5zibfsicy59Q/640"
          },
          {
            "title": "九个动作，目前最流汗最累的细腰翘臀！",
            "author": "",
            "description": "点击 上方蓝字 每日学瑜伽阅读本文前，请您先点击本文上面的蓝色字体“每日学瑜伽”再点击“关注”，这样您就可以",
            "cover_url": "http://mmbiz.qpic.cn/mmbiz_jpg/X3eO3d2gJ1Lv5gtkJZ9BxLBT0uKyJuzCZgVthlM4odTPmXP7OWdeYE7n7es4nuxEKAibLJcKUaODvictQXrD7VnA/0?wx_fmt=jpeg",
            "content_url": "http://mmbiz.qpic.cn/mmbiz_jpg/X3eO3d2gJ1Lv5gtkJZ9BxLBT0uKyJuzCZgVthlM4odTPmXP7OWdeYE7n7es4nuxEKAibLJcKUaODvictQXrD7VnA/0?wx_fmt=jpeg"
          }
        ]
      }
    ]
  }
}
```

### 添加自动回复
- uri: r=reply/create
- method: post
- request
```
{
    "official_account_id" :2, //公众号id  必须
    "type_reply" : 1   //0->被添加自动回复 1->未识别自动回复 2->关键字自动回复
    "type_msg" : 1  //1->news, 2->image, 3->voice, 4->video, 5->text //当type_msg消息类型不是text时必填
    "wx_media_id" : "lpE7FlFNFOL1iTHATRrEZ6HWmETqRHhdITh6ynA1HEg"  //微信端media_id
    "content" : "欢迎关注！", //当type_msg消息类型为text时必填
    "keyword" : "文章1 文章2" //多个关键字用空格分割
    "rule" : "规则名"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```

### 删除自动回复
- uri: r=reply/delete
- method: post
- request
```
{
    "id" : [1,2,3] //数组
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
}
```


## 导入导出

### 导出公众号列表
- uri: r=excel/export
- method: post
- request
```
{
  "page": 1, # 非必填，页数
  "num": 20, # 非必填，每页多少条
  "keyword": "公众号名字关键词", # 非必填，查询的关键字
  "group_id": 1, # 非必填，类别
  "editor_id": 2123, # 非必填，编辑人员id
  <!-- "auditor_id": 1231, # 非必填，审核人员id -->
  "fans_num_range_start": 123, # 非必填，关注人数范围，开始
  "fans_num_range_end": 1234, # 非必填，关注人数范围，结束
}
```
- response
```
{

}
```
### 导入公众号
- uri: r=excel/import
- method: post
- request
```
{
    "file":"file"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "msg": "成功导入数据",
    "data-msg" : {
        [
           "公众号appid不合法"，
        ]，
        [
           "公众号appid不合法"，
        ]，
        [
           "公众号appid不合法"，
        ]，
        [
           "公众号appid不合法"，
        ]，
    }

}
```


### 下载导入公众号模板文件
- uri: r=excel/download
- method: get
- request
```
{

}
```
- response
```
raw excel file
```


### 导入客户
- uri: r=excel/import-customer
- method: post
- request
```
{
    "file":"file"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "msg": "成功导入数据",
    "data-msg" : {
        [
           "客户不合法"，
        ]，
        [
           "客户不合法"，
        ]，
        [
           "客户不合法"，
        ]，
        [
           "客户不合法"，
        ]，
    }

}
```


### 下载导入公众号模板文件
- uri: r=excel/download-customer
- method: get
- request
```
{

}
```
- response
```
raw excel file
```

## 消息管理

### 查看消息列表
- uri: r=message/get-list

### 消息收藏
- uri: r=message/collect

### 消息保存为素材
- uri: r=message/save-to-material


## 公告

### 增加公告
- uri: r=announcement/create
- method: POST
- request
```
{
     "content" : "公告内容",
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 查询最新公告
- uri: r=announcement/most-new-announcement
- method: get
- request
```
{
}
```
- response
```
{
    "code":0,
    "msg":"ok",
    "data":
    {
        "announcement-info":
        {
            "id":"8",
            "content":"\u516c\u544a\u516c\u544a1\u516c\u544a\u5de51121213",
            "created_at":1481540311,
            "user_id":"1"
        }
    }
}
```

### 查询公告列表
- uri: r=announcement/announcement-list
- method: get
- request
```
{
}
```
- response
```
{
    "code":0,
    "msg":"ok",
    "data":
    {
        "announcement-info":
        [
            {
                "id":"8",
                "content":"\u516c\u544a\u516c\u544a1\u516c\u544a\u5de51121213",
                "created_at":1481540311,
                "nickname":"111"
            },
            {
                "id":"8",
                "content":"\u516c\u544a\u516c\u544a1\u516c\u544a\u5de51121213",
                "created_at":1481540311,
                "nickname":"1111"
            }
            ......
        ]
    }
}
```


### 删除公告
- uri: r=announcement/delete
- method: POST
- request
```
{
     "id":[1,2,3... ],
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 修改公告
- uri: r=announcement/update
- method: POST
- request
```
{
     "id": 1,
     "content" : "asdfadfadfa"
}
```
- response
```
{
    "msg": "ok",
    "code": 0,
    "data": {
    }
}
```

### 标记公告为已读（暂时不做）
- uri: r=announcement/mark_read

## 服务模块
- uri: r=service/mark-official
- method: POST
- request
```
{
  'official_account_id': 1 # 标记当前的公众号id
}
```
- response
```
{
  'code': 0,
  'msg': 'ok'
}
```

### 拉取上传信息
- uri: r=service/get-upload-info
- method: GET
- request
```
{
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "upload_info": {
      "token_info": {
        "access_key_id": "STS.Htkr4htviPcbHPD18Ueg8VBWE",
        "access_key_secret": "3G3gu63Zop6R5JMXbXGnZ8gNJMLtHfMbukAGKMeWQU7s",
        "security_token": "CAESiQMIARKAASsG9lJhd8b1eVKXq5HD6QNEOQvEcRK5+O3EI0vw/4jlaRaS/dt9CF5S88/K+vb5To0CEh2Ivc2om+WaZjUQseO/kadGGtxX7I44mV3GJCRFJ4QzGZFcOml3kZ215SlC2N/ffFeMm3USekey/4wBUjkdrrB2VfxnrYuqyc9ADUGiGh1TVFMuSHRrcjRodHZpUGNiSFBEMThVZWc4VkJXRSISMzYwMDMxMDgxNjIyMTQzMDY0KgtjbGllbnRfbmFtZTCm1ezDjys6BlJzYU1ENUJcCgExGlcKBUFsbG93Ei0KDEFjdGlvbkVxdWFscxIGQWN0aW9uGhUKCG9zczpHZXQqCglvc3M6TGlzdCoSHwoOUmVzb3VyY2VFcXVhbHMSCFJlc291cmNlGgMKASpKEDE5NTA4NjE2Mzc5NzQ2MDVSBTI2ODQyWg9Bc3N1bWVkUm9sZVVzZXJgAGoSMzYwMDMxMDgxNjIyMTQzMDY0cgx3eC1hZG1pbi1vc3N4zfS/98XJuwM=",
        "expiration": "2016-12-13T13:59:31Z"
      },
      "dir": "d053afa76e2e27ae3bd4517996d4e301"
    }
  }
}
```

### 拉取ueditor配置信息
- uri: /controller.php?action=config
- method: GET
- request
```
{
}
```
- response
```
{
  走json或者jsonp格式，具体参数，请参考https://github.com/minms/ueditor-oss/blob/master/dist/php/controller.php
}
```

### ueditor多媒体上传(图片,音频,视频素材上传)
- uri: /controller.php?action=uploadimage|uploadscrawl|uploadvideo|uploadfile&type=1
- method: POST
- request
url参数
```
    'action': 'uploadimage',
    "type": 2, # 2代表图片，6代表上传文章图片
```
body
```
{
    'upfile': 'filename' # Content-Type:multipart/form-data,
}
```
- response
```
{
    "state":"SUCCESS",
    "url":"/static/ueditor/php/upload/image/20161227/1482848233255908.jpg",
    "title":"1482848233255908.jpg",
    "original":"1.jpg",
    "type":".jpg",
    "size":17199,
    "media_id":"asdjh129083"
}
```

### ueditor多媒体文件列表(图片,音频,视频素材列表)
- uri: /controller.php?action=listimage|listfile
- method: GET
- request
```
{
    start:0,
    size:20,
    #参照 ueditor request
}
```
- response
```
{
    #参照ueditor response
    "state": "SUCCESS",
    "list": [{"url":"http://a.jpg", "mtime": 1231238, "media_id": "asdhakld*&^&*^", "wx_url":""}, {}, {}, ...],
    "start": 20,
    "total": 100
}
```

## 日志模块
### 获取日志列表 (分页)
- uri: r=manager-log/info-list
- method: get
- request
```
{
    "page": 1, //非必填
    "num" :20, //非必填
    "nickname" : "hehe", //非必填
    "weixin_name" : "hehe" //非必填
}
```
- response
```
{
    "code":0,
    "msg":"ok",
    "data":
    {
        "manager_log_list":
        [
            {
                "id":"8",
                "weixin_name":"hehe",
                "nickname":"hehe",
                "description":"\u516c\u544a\u516c\u544a1\u516c\u544a\u5de51121213"，
                "ip":"127.0.0.1"，
                "created_at":1481335495
            },
            {
                "id":"8",
                "weixin_name":"hehe",
                "nickname":"hehe",
                "description":"\u516c\u544a\u516c\u544a1\u516c\u544a\u5de51121213"，
                "ip":"127.0.0.1"，
                "created_at":1481335495
            },
            ......
        ],
        "page_num" : 1
    }
}
```

## 统计模块
### 今日分时->day=1   day>1表示天，最多三十天
- uri: r=statistic/get-fans-data
- method: get
- request
```
{
    "official_account_id": 1, //必填
    "day":7
}
```
- response
```
{
    "time":["2017-2-24","2017-2-24","2017-2-24","2017-2-24","2017-2-24"]
    "data":[
        {
            "name":"新增用户",
            "data":[7,6,9,9,5]
        },
        {
            "name":"取关用户",
            "data":[7,6,9,9,5]
        },
        {
            "name":"总用户",
            "data":[7,6,9,9,5]
        },
        {
            "name":"净增长用户",
            "data":[7,6,9,9,5]
        },
    ]
}
```

### 图文分析->day=1表示昨天   day>1表示天，最多三十天
- uri: r=statistic/get-news-data
- method: get
- request
```
{
    "official_account_id": 1, //必填 0:会话;1.好友;2.朋友圈;4.历史消息页;5.其他
    "start_time":1652322312
    "end_time":1652322312
}
```
- response
```
{
  "0": {
    "data": [
      {
        "name": "图文阅读人数",
        "data": [0,2,0]
      },
      {
        "name": "图文阅读次数",
        "data": [0,3,0]
      }
    ]
  },
  "1": {
    "data": [
      {
        "name": "图文阅读人数",
        "data": [13,21,28]
      },
      {
        "name": "图文阅读次数",
        "data": [17,25,34]
      }
    ]
  },
  "2": {
    "data": [
      {
        "name": "图文阅读人数",
        "data": [15,25,50]
      },
      {
        "name": "图文阅读次数",
        "data": [15,29,54]
      }
    ]
  },
  "4": {
    "data": [
      [
        {
          "name": "图文阅读人数",
          "data": [0,0]
        }
      ],
      [
        {
          "name": "图文阅读次数",
          "data": [0,0,0]
        }
      ]
    ]
  },
  "5": {
    "data": [
      {
        "name": "图文阅读人数",
        "data": [1188,1348,3445]
      },
      {
        "name": "图文阅读次数",
        "data": [3214,2438,6786]
      }
    ]
  },
  "time": ["2017-02-23","2017-02-22","2017-02-21"]
}
```

### 单图文
- uri: r=statistic/get-new-data
- method: get
- request
```
{
    "official_account_id": 1, //必填 0:会话;1.好友;2.朋友圈;4.历史消息页;5.其他
    "start_time":4513125321321
    "end_time":251321213
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": [
    {
      "title": "男人多久碰你一次才正常？",
      "ref_date": "1487520000",
      "target_user": "33054",
      "int_page_read_user": "3413",
      "share_user": "7"
    },
    {
      "title": "墙上挂一物，家中阴气环绕，财运越来越差！",
      "ref_date": "1487520000",
      "target_user": "33054",
      "int_page_read_user": "1300",
      "share_user": "21"
    }
  ]
}
```

### 获取图文分析表格数据
- uri: r=statistic/get-news-table-data
- method: get
- request
```
{
    "official_account_id": 1, //必填 0:会话;1.好友;2.朋友圈;4.历史消息页;5.其他
    "start_time":4513125321321
    "end_time":251321213
    "page" : 1
    "num" : 20
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "list": [
      {
        "ref_date": "02-25",
        "int_page_read_user": "483",
        "int_page_read_count": "900",
        "int_page_read_user_0": "0",
        "int_page_read_count_0": "0",
        "int_page_read_user_2": "10",
        "int_page_read_count_2": "11",
        "share_user": "4",
        "share_count": "4",
        "add_to_fav_user": "0",
        "add_to_fav_count": "0"
      },
      {
        "ref_date": "02-24",
        "int_page_read_user": "867",
        "int_page_read_count": "2135",
        "int_page_read_user_0": "0",
        "int_page_read_count_0": "0",
        "int_page_read_user_2": "10",
        "int_page_read_count_2": "13",
        "share_user": "7",
        "share_count": "7",
        "add_to_fav_user": "0",
        "add_to_fav_count": "0"
      }
    ],
    "page_num": 4
  }
}
```

### 获取昨天图文数据
- uri: r=statistic/get-news-Yesterday-data
- method: get
- request
```
{
    "official_account_id": 1,
}
```
- response
```
{
     "code": 0,
     "msg": "ok",
     "data": {
       "int_page_read_count": 5635,
       "ori_page_read_count": 391,
       "share_user": 57,
       "add_to_fav_user": 13
     }
}

```

### 导出图文分析详细数据
- uri: r=statistic/export-news-data
- method: get
- request
```
{
    "official_account_id": 1,
    "start_time" : 11111
    "end_time" : 11111
    "page" : 1
    "num" : 20
}
```
- response
```
{

}

```
### 导出用户分析详细数据
- uri: r=statistic/export-user-data
- method: get
- request
```
{
    "official_account_id": 1,
    "start_time" : 11111
    "end_time" : 11111
    "page" : 1
    "num" : 20
}
```
- response
```
{

}

```


## 广告模块

### 广告订单列表
- uri: r=advertise/get-list
- method: get
- request
```
{
   "status": 0, //0操作中 1已完成
   "receipt_date" :15613513213,  //按天查找，
   "customer" :"sdfadfas", //客户名称
   "page" : 1,
   "num" : 20
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "list": [
      {
        "order_id" : 111
        "username": "admin",  //接单人
        "receipt_date": 1484118463, //
        "customer": "小红",  //客户,直接通过客户id获得
        "customer_id" : 1,
        "qq": 4294967295,  //QQ
        "order_amount": "50000",  //订单金额
        "deposit" : 1000,
        "status": "0",  //订单状态 0操作中，1已完成
        "order_info": [
          {
            "id_son" : 11,
            "send_date": 1484118463, //发送日期
            "ad_position": "首条", //广告位
            "retain_day": 1,  // 保留小时
            "type_info": {
               "son" : [
                   "id" : 1,
                   "name": "健康"
               ],
               "parent" : [
                   "id" : 1,
                   "name" : "减肥"
               ]
            }
            "million_fans_price" : 1000  //万粉单价
            "official_account": "教妈妈学会做编织",
            "official_account_id" : 1,
            "fans_num": 1567,  //粉丝数量
            "amount" : 10000,
            "status": 0  //状态 0待发送  1已发送  2已结束
          },
        ]
      }
    ],
    "page_num": 1
  }
}
```


### 添加广告订单
- uri: r=advertise/add-order
- method: post
- request
```
{
    "receipt_date":1491795256,
    "customer_id":1,
    "order_amount":10000,
    "deposit":1000,
    "order_info":[
        {
            "official_account_id":12,
            "ad_position":"首条",
            "retain_day": 1, // 小时为单位
            "send_date":1491795256,
            "type_id":1,  //广告类型id  为0的时候表示未知类型
            "million_fans_price" : 1000,  //万粉单价
            "amount" : 1000
        },
        {
            "official_account_id":12,
            "ad_position":"首条",
            "retain_day": 1, // 小时为单位
            "send_date":1491795256,
            "type_id":1,  //广告类型id
            "million_fans_price" : 1000,  //万粉单价
            "amount" : 1000
        }
    ]
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```

### 修改广告订单 （order_amount = amount*count(order_info)）
- uri: r=advertise/modify-order
- method: post
- request
```
{
    "order_id" : "1234567890123456", // 订单id
    "receipt_date":1490239039,
    "customer_id":1,
    "order_amount":10000,
    "deposit":1000,
    "order_info":[
        {
            "id_son" : 2,//必填，如果有则修改，0 表示添加  -2表示删除
            "official_account_id":12,
            "ad_position":"首条",
            "retain_day": 1, // 小时为单位
            "send_date":1490371200,
            "type_id":1,  //广告类型id
            "million_fans_price" : 1000,  //万粉单价
            "amount" : 1000
        },
        {
            "id_son" : 0,
            "official_account_id":12,
            "ad_position":"首条",
            "retain_day": 1, // 小时为单位
            "send_date":1490371200,
            "type_id":1,  //广告类型id  为0的时候表示未知类型
            "million_fans_price" : 1000,  //万粉单价
            "amount" : 1000
        },
        {
            "id_sun" : -2,
        }
    ]
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```

### 删除广告订单
- uri: r=advertise/delete-order
- method: get
- request
```
{
    "id":1,
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```

## 广告类型模块
### 广告类型列表
- uri: r=advertise/type-list
- method: get
- request
```
{

}
```
- response
```
{
    "code":0,
    "msg":"ok",
    "data":[
        {
            "p_id":111,
            "p_name":"健康",
            "count" : 10,
            "son_list":[
                {
                    "s_id":22,
                    "s_name":"减肥"
                    "created_at":1231313132
                },
                {
                    "s_id":33,
                    "s_name":"健身"
                    "created_at":1231313132
                }
            ]
        },
        {
            "p_id":111,
            "p_name":"旅游",
            "count" : 20,
            "son_list":[
                {
                    "s_id":22,
                    "s_name":"黄山"
                    "created_at":1231313132
                },
                {
                    "s_id":33,
                    "s_name":"泰山"
                    "created_at":1231313132
                }
            ]
        }
    ]
}
```


### 添加广告类型
- uri: r=advertise/add-type
- method: post
- request
```
{
    "name" : "减肥",
    "p_id" : 0 //0表示添加父类，不为零表示添加子类
}
```
- response
```
{
    "code":0,
    "msg":"ok",
}
```

### 修改广告类型
- uri: r=advertise/modify-type
- method: post
- request
```
{
    "id" : 1,
    "name" : "减肥",
}
```
- response
```
{
    "code":0,
    "msg":"ok",
}
```



### 删除广告类型
- uri: r=advertise/delete-type
- method: get
- request
```
{
    "id" : 1,
}
```
- response
```
{
    "code":0,
    "msg":"ok",
}
```
## 广告客户模块
### 客户列表
- uri: r=advertise/customer-list
- method: get
- request
```
{
   "created_at": 3214621313
   "customer" :"asdfas",
   "page": 1,
   "num" : 20
}
```
- response
```
{
    "code":0,
    "msg":"ok",
    "data":{
        "list":[
            {
                "id":1,
                "created_at":13213213213,
                "customer":"小明",
                "realname" : "djfak",
                "qq": 16545646546,
                "wechat_id": "asdasdjalsdj", # 用户的微信id
                "tel" : 154122212,
                "company":"中华人民共和国",
                "mark":"很吊",
                "ad_type_info":[
                    {
                        "name":"健康/减肥"
                    }
                ]
            }
        ],
        "page_num":1
    }
}
```


### 添加客户
- uri: r=advertise/customer-add
- method: post
- request
```
{
   "customer" :"小明", // 客户名称，必填
   "realname" :"明道", // 选填
   "tel" : 154561465135, // 选填
   "qq" : 35454845, // qq，必填
   "company" : "中华人民共和国", // 选填
   "mark" : "很吊", // 选填
   "wechat_id": ""
}
```
- response
```
{
  "code": 0,
  "msg": "ok"
}
```


### 修改客户
- uri: r=advertise/customer-modify
- method: post
- request
```
{
   "id"
   "customer" :"小明",
   "realname" :"明道",
   "tel" : 154561465135,
   "qq" : 35454845,
   "company" : "中华人民共和国",
   "mark" : "很吊"
}
```
- response
```
{
  "code": 0,
  "msg": "ok"
}
```



### 删除客户
- uri: r=advertise/customer-delete
- method: get
- request
```
{
   "id" : 1
}
```
- response
```
{
  "code": 0,
  "msg": "ok"
}
```

### 导出广告订单列表
- uri: r=advertise/export-advertise-data
- method: get
- request
```
{

}
```
- response
```
{

}
```

### 导出客户列表
- uri: r=advertise/export-customer-data
- method: get
- request
```
{

}
```
- response
```
{

}
```



### 出纳流水
- uri: r=advertise/teller-list
- method: get
- request
```
{
   "receipt_date": 3214621313
   "customer" :"asdfas",
   "user_id" :1  //出纳人员
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "list": [
      {
        "username": "admin",  //出纳人员
        "receipt_date": "165112316",  收款时间
        "order_comment": "0", 收款说明
        "customer": "小明",
        "receipt_bank_name": "农业银行", 收款银行
        "receipt_bank_num": "1.3514316513217E+15",  收款账号
        "pay_bank_name": "建设银行", 付款银行
        "pay_bank_num": "1.5615613246156E+14",  付款账号
        "amount": 20000,  收入金额
        "order_info": [
          {
            "order_id": "1", 订单Id
            "order_user": "admin",  接单人
            "order_amount": "10000",  金额
            "receipt_date": "165112316" 日期
          },
          ......
        ]
      },
    ],
    "page_num": 1
  }
}
```


### 添加出纳流水
- uri: r=advertise/add-teller
- method: post
- request
```
{
    "receipt_date":1497721600,
    "customer_id":"小明",
    "order_comment":"广告费",
    "receipt_bank_name":"农业银行",
    "receipt_bank_num":1351431651321651,
    "pay_bank_name":"建设银行",
    "pay_bank_num":156156132461561,
    "amount_total" : 100000,
    "order_info":[
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        }
     ]
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```

### 修改出纳流水
- uri: r=advertise/modify-teller
- method: post
- request
```
{
    "id":11
    "receipt_date":1497721600,
    "customer":"小明",
    "order_comment":"广告费",
    "receipt_bank_name":"农业银行",
    "receipt_bank_num":1351431651321651,
    "pay_bank_name":"建设银行",
    "pay_bank_num":156156132461561,
    "amount_total" : 100000,
    "order_info":[
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        },
        {
            "order_id":1,
            "amount":10000
        }
     ]
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```


### 删除出纳流水
- uri: r=advertise/delete-teller
- method: get
- request
```
{
    "id":1
}
```
- response
```
{
  "code":0,
  "msg":"ok",
}
```



### 广告收入列表
- uri: r=advertise/ad-income
- method: get
- request
```
{
    "page":1,
    "num":20,
    "user_id":1,
    "day":1 //1表示昨天，2表示前天。。。。

}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "list": [
      {
        "order_id": "1",
        "receipt_date": 165112316,
        "username": "admin",
        "customer": "小明",
        "amount": 10000,
        "deposit": 1000,
        "income_date": "1487606400",
        "income": "1000"
      },
      {
        "order_id": "1",
        "receipt_date": 165112316,
        "username": "admin",
        "customer": "小明",
        "amount": 10000,
        "deposit": 1000,
        "income_date": "1487606400",
        "income": "1000"
      },
      {
        "order_id": "1",
        "receipt_date": 165112316,
        "username": "admin",
        "customer": "小明",
        "amount": 10000,
        "deposit": 1000,
        "income_date": "1487606400",
        "income": "7000"
      }
    ],
    "total_income": "9000",
    "page_num": 1
  }
}
```
### 广告收入图表
- uri: r=advertise/income-chart
- method: get
- request
```
{
    "day":1 //1表示昨天，2表示前天。。。。
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
    "time": [
      "2017-02-23",
      "2017-02-26",
      "2017-06-18"
    ],
    "data": [
        {
          "name": "收入汇总",
          "data": [111,2,80000]
        }
    ]
  },
  "total": {
    "yesterday_amount": "80113",
    "mouth_amount": "80113"
  }
}
```

### 分类收入汇总
- uri: r=advertise/cate-income-chart
- method: get
- request
```
{
    "day":1 //1表示昨天，2表示前天。。。。

}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": [
    {
      "母婴": {
        "income": 7000
      }
    },
    {
      "帅哥": {
        "income": 2000
      }
    },
    {
      "美女": {
        "income": 2000
      }
    }
  ]
}
```

### 公众号当月收入汇总
- uri: r=advertise/official-income-chart
- method: get
- request
```
{
    "day":1 //1表示昨天，2表示前天。。。。
    "official_account_id" : 1
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": [
    {
      "2031-02-18": {
        "income": 1000
      }
    },
    {
      "2031-02-07": {
        "income": 6000
      }
    }
  ]
}
```

### 分享接口
- uri: r=share/index
- method: post
- request
```
{
   "app_id": "wx89efd9398453e27e",
   "app_secect" :"07f21c5be9c9b5f2a38b881133401feb"
}
```
- response
```
{
  "debug": true,
  "beta": false,
  "appId": "wx89efd9398453e27e",
  "nonceStr": "YVIvTfM6GZ",
  "timestamp": 1484992242,
  "url": "http://www.wx_admin_platform.com/index.php?r=share/index",
  "signature": "640b7ca0964bc64bb1ab19c651c1478b7c3c5435",
  "jsApiList": [
    "onMenuShareTimeline",
    "onMenuShareAppMessage"
  ]
}
```

## 养域名模块

### 重定向
- uri: r=proxy-domain/redirect
- method: get
- request
```
{
   "id": "1",
   "url" :"http://wxaskdjaskldj.com"
}
```
- response
```
raw 301 重定向
```

### 创建
- uri: r=proxy-domain/create
- method: post
- request
```
{
   "url_list" : ["http://wxaskdjaskldj.com"]
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
}
```

### 删除
- uri: r=proxy-domain/delete
- method: post
- request
```
{
   "id_list" : [1,2,3,4,5]
}
```
- response
```
{
  "code": 0,
  "msg": "ok"
}
```

### 拉取域名列表
- uri: r=proxy-domain/info-list
- method: get
- request
```
{
  "page": 0,  # 翻页页数
  "num": 20, # 每页的页数
}
```
- response
```
{
  "code": 0,
  "msg": "ok",
  "data": {
      "url_list" : [
        {"id":1, "domain":"http://wxaskdjaskldj.com", "req_nums": 1},
        {"id":1, "domain":"http://wxaskdjaskldj.com", "req_nums": 1},
        {"id":1, "domain":"http://wxaskdjaskldj.com", "req_nums": 1},
       ]
   }
}
```
