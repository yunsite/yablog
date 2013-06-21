/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

CREATE TABLE `tb_language_modules` (
  `module_id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `parent_id` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `module_name` char(20) NOT NULL DEFAULT '' COMMENT '语言文件名',
  `sort_order` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '排序,越小越靠前',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '层级',
  `node` char(20) NOT NULL DEFAULT '' COMMENT '节点',
  `memo` char(60) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `module_name` (`module_name`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=gbk COMMENT='语言包模块表 by mrmsl 2013-06-19 15:52:20';

INSERT INTO `tb_language_modules` VALUES (1,0,'整站',1,1,'1','');
INSERT INTO `tb_language_modules` VALUES (2,0,'前台',2,1,'2','');
INSERT INTO `tb_language_modules` VALUES (3,0,'后台',3,1,'3','');

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
