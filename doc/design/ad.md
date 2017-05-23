# 广告模块方案设计说明

每接一个广告就产生一条订单信息，根据订单信息中的广告派发时间(send_date)改变订单对应的状态，根据出纳填写的出纳
信息改变对应订单中的已付金额，当已付金额和订单金额相等后，此订单已付清；

出纳员每添加一条出纳记录，对应该订单中的已付金额，一条出纳里面只有一个订单号，对于一条出纳记录对应多条订单的情况
可以使用合并显示，就是合并一样的数据，不一样的的数据使用数组显示.



## 广告模块API接口设计
### 广告订单列表
- uri: r=advertise/get-list
- method: post
- request
```
{
   "status": 0, //0表示待发送，1表示已发送  2表示已结束
   "receipt_date" :"15613513213",
   "send_date" :"1651653213",
   "user_id" :1,
   "official_account" :1,
   "page" : 1,
   "num" : 20
}
```
- response
```
{
  "code":0,
  "msg":"ok",
  "data":{
      "list":{
          [
              "id" : 1,
              "username" : "sdfasd",
              "receipt_date" : 1212132,
              "send_date" : 1212132adfga,
              "customer" : "sdfaa",
              "tel" : 13612129702,
              "ad_position" : "首条",
              "retain_day" : 1,
              "product_type" : 减肥,
              "official_account" : "教你每天瘦身",
              "fans_num" : 10000,
              "order_amount" : 50000,
              "deposit" : 10000
              "status" : 0,
          ]
          .....
      }
  }
}
```

### 出纳流水
- uri: r=advertise/teller-list
- method: post
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
  "code":0,
  "msg":"ok",
  "data":{
      "list":{
          [
              "id" : 1,
              "username" : "sdfasd",
              "receipt_date" : 1212132,
              "order_comment" : 1212132adfga,
              "customer" : "sdfaa",
              "receipt_bank_name" : "收付银行",
              "receipt_bank_num" : "收付账号",
              "pay_bank_name" : "付款银行",
              "pay_bank_num" : "付款账户",
              "amount" : 10000,
              "order_id" : 10000
          ]
          .....
      }
  }
}
```

##数据库设计
- advertisement
```
CREATE TABLE `advertisement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接单人',
  `customer` varchar(100)  NOT NULL DEFAULT '' COMMENT '客户',
  `tel` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '电话号码',
  `ad_position` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位',
  `retain_day` tinyint(2) NOT NULL DEFAULT '' COMMENT '保留天数',
  `product_type` varchar(50) NOT NULL DEFAULT '' COMMENT '产品类型',
  `official_account_id` int(10) NOT NULL DEFAULT '0' COMMENT '公众号',
  `order_amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单金额',
  `deposit` int(11) NOT NULL DEFAULT '0' COMMENT '订金金额',
  `receipt_date` int(11) NOT NULL DEFAULT '0' COMMENT '接单日期',
  `send_date` int(11) NOT NULL DEFAULT '0' COMMENT '发送日期',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8
```

- teller
```
CREATE TABLE `teller` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出纳人员',
  `customer` varchar(100)  NOT NULL DEFAULT '' COMMENT '客户',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `receipt_date` int(11)  NOT NULL DEFAULT '' COMMENT '收款日期',
  `order_comment` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '款项说明',
  `receipt_bank_name` varchar(30) NOT NULL DEFAULT '' COMMENT '收付银行',
  `receipt_bank_num` varchar(30) NOT NULL DEFAULT '' COMMENT '收付账号',
  `pay_bank_name` varchar(30) NOT NULL DEFAULT '' COMMENT '付款银行',
  `pay_bank_num` varchar(30) NOT NULL DEFAULT '' COMMENT '付款账户',
  `amount` int(10) NOT NULL DEFAULT '0' COMMENT '收入金额',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8
```


