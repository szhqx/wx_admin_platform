-- alter table article add ad_source_url varchar(1024) default '' comment '推广链接' after `weixin_source_url`;
-- alter table material add ad_source_url varchar(1024) default '' comment '推广链接' after `weixin_source_url`;
ALTER TABLE article MODIFY source_url varchar(1024) default '' comment '内容连接资源，默认是阿里云的';
ALTER TABLE article MODIFY weixin_source_url varchar(1024) default '' comment '内容连接资源，微信返回的';

ALTER TABLE material MODIFY source_url varchar(1024) default '' comment '内容连接资源，默认是阿里云的';
ALTER TABLE material MODIFY weixin_source_url varchar(1024) default '' comment '内容连接资源，微信返回的';
ALTER TABLE material MODIFY content_url varchar(1024) default '' comment '原文链接';
