/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

DROP TABLE IF EXISTS `tb_blog_laruence`;
CREATE TABLE `tb_blog_laruence` (
  `blog_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '标题',
  `cate_id` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_issue` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态;0;未发布;1已发布',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态;0未删除;1已删除',
  `sort_order` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序，越小越靠前。默认其id',
  `hits` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `comments` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `seo_keyword` varchar(180) NOT NULL DEFAULT '' COMMENT 'SEO关键字',
  `seo_description` varchar(300) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `content` text NOT NULL COMMENT '内容',
  `from_name` varchar(200) NOT NULL DEFAULT '' COMMENT '来源名称',
  `from_url` varchar(200) NOT NULL DEFAULT '' COMMENT '来源url',
  `summary` text COMMENT '摘要',
  `url_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'url名称',
  `link_url` varchar(150) NOT NULL DEFAULT '' COMMENT '博客链接',
  PRIMARY KEY (`blog_id`),
  UNIQUE KEY `title` (`title`),
  KEY `cate_id` (`cate_id`),
  KEY `issue_delete` (`is_issue`,`is_delete`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk COMMENT='博客表 by mashanling on 2013-03-22 15:56:41';

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
