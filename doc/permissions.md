# 权限设计

## 映射接口
```
{
  ## 公众号管理
  "official-account/create" : "添加公众号",
  "official-account/modify": "修改公众号",
  "official-account/delete": "删除公众号",
  "official-account/info": "获取公众号单个信息",
  "official-account/info-list": "获取公众号列表信息",

  ## 导入、导出公众号权限
  "excel/import": "批量导入公众号",
  "excel/export": "批量导出公众号",
  "excel/download": "下载模板",

  ## 公众号分组管理
  "official-group/create": "创建公众号分组",
  "official-group/modify": "修改公众号分组",
  "official-group/move": "把公众号移动到分组",
  "official-group/delete": "删除一个分组",
  "official-group/info-list": "查看公众号分组列表",

  ## 消息模块管理
  "message/get-list": "获取消息列表",
  "message/response": "消息回复",
  "message/collect": "消息收藏",
  "message/save-to-material": "消息保存为素材",

  ## 自动回复模块
  "reply/get-list": "获取自动回复列表",
  "reply/info": "获取单个自动回复信息",
  "reply/create": "创建自动回复",
  "reply/delete": "删除自动回复",
  "reply/update": "更新自动回复",

  ## 粉丝模块
  "fans/get-list": "获取粉丝列表",
  "fans/sync": "同步粉丝、标签、分组、标签分组映射信息",
  "fans/create-tag": "创建新标签",
  "fans/update-tag": "更新标签",
  "fans/delete-tag": "删除标签",
  "fans/get-tag-list": "获取标签列表",
  "fans/mark": "修改粉丝备注",
  "fans/tagging": "给粉丝打标签",
  "fans/un-tagging": "移除粉丝标签",
  "fans/move-fans-to-group": "把粉丝移到分组",
  "fans/create-group": "创建分组",
  "fans/update-group": "更新分组",
  "fans/delete-group": "删除分组",
  "fans/get-group-list": "获取分组列表",
  "fans/block": "将粉丝加入黑名单",

  ## 菜单模块
  "menus/get-list": "获取自定义菜单列表",
  "menus/create": "创建自定义菜单", // modify、delete都放到create接口里面了
  "menus/modify": "修改自定义菜单",
  "menus/delete": "删除自定义菜单",

  ## 群发管理
  "mass/info-list": "获取群发排期列表",
  "mass/get-send-list": "获取群发排期列表",
  "mass/create": "添加群发",
  "mass/modify": "调整群发",
  "mass/delete": "删除群发",
  "mass/delete-send": "删除已群发",

  ## 素材管理
  "material/create": "添加素材（本地/微信）",
  "material/modify": "编辑素材（本地/微信）",
  "material/delete": "删除素材（本地/微信）",
  "material/preview": "预览（定时群发/微信素材）",
  "material/upload-article-image": "上传图文素材的图片",
  "material/sync-single": "同步单个素材",

  ## 用户管理
  "account/create": "创建用户",
  "account/modify": "修改用户",
  "account/delete": "禁用用户",
  "account/info": "查看单个用户信息",
  "account/info-list": "查看用户信息列表",

  ## 角色管理
  "authority/create-role": "添加角色",
  "authority/delete-role": "删除角色",
  "authority/modify-role": "编辑角色"
  "authority/info-list": "拉取角色列表"
  "authority/info": "查看单个角色信息"
  "authority/get-role-level-list": "拉取多级权限列表"
  "authority/get-subordinate-list": "拉取下属列表"

  ## 日志模块
  "manager-log/info-list": "查看日志列表",

  ## 公告模块
  "announcement/create": "增加公告",
  "announcement/delete": "删除公告",
  "announcement/update": "更新公告",
  "announcement/announcement-list": "获取公告列表",

  ## 广告模块
  "advertise/get-list" : "查看广告订单列表"
  "advertise/add-order" : "添加广告订单"
  "advertise/modify-order" : "修改广告订单",
  "advertise/delete-order" : "删除广告订单",

  ## 财务模块（出纳模块）
  "advertise/teller-list": "拉取出纳流水列表",
  "advertise/add-teller": "添加出纳流水",
  "advertise/modify-teller": "修改出纳流水",
  "advertise/delete-teller": "删除出纳流水"
  "advertise/ad-income" : "广告收入"
  "advertise/income-chart": "广告收入图表",
  "advertise/cate-income-chart": "分类收入汇总",
  "advertise/official-income-chart": "公众号当月收入汇总",

  ## 统计模块
  "statistic/get-fans-data" : "获取用户分析"
  "statistic/get-news-data" : "获取图文分析"
  "statistic/export-news-data" : "导出图文分析"
  "statistic/export-user-data" : "导出用户分析"

}
```
