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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=gbk COMMENT='语言包模块表 by mrmsl 2013-06-19 15:52:20';

INSERT INTO `tb_language_modules` VALUES (1,0,'整站',1,1,'1','');
INSERT INTO `tb_language_modules` VALUES (2,0,'前台',2,1,'2','');
INSERT INTO `tb_language_modules` VALUES (3,0,'后台',3,1,'3','');
INSERT INTO `tb_language_modules` VALUES (5,2,'common',5,2,'2,5','前台通用语言包');
INSERT INTO `tb_language_modules` VALUES (6,3,'common',6,2,'3,6','后台通用语言包');
INSERT INTO `tb_language_modules` VALUES (7,3,'admin',7,2,'3,7','管理员模块');
INSERT INTO `tb_language_modules` VALUES (8,3,'adminloginhistory',8,2,'3,8','管理员登陆历史');
INSERT INTO `tb_language_modules` VALUES (9,3,'area',9,2,'3,9','国家地区');
INSERT INTO `tb_language_modules` VALUES (10,3,'blog',10,2,'3,10','博客');
INSERT INTO `tb_language_modules` VALUES (11,3,'category',11,2,'3,11','博客分类');
INSERT INTO `tb_language_modules` VALUES (12,3,'comments',12,2,'3,12','留言评论');
INSERT INTO `tb_language_modules` VALUES (13,3,'field',13,2,'3,13','表单域');
INSERT INTO `tb_language_modules` VALUES (14,3,'html',14,2,'3,14','生成静态页管理');
INSERT INTO `tb_language_modules` VALUES (15,3,'languageitems',15,2,'3,15','语言项');
INSERT INTO `tb_language_modules` VALUES (16,3,'languagemodules',16,2,'3,16','语言包模块');
INSERT INTO `tb_language_modules` VALUES (17,3,'log',17,2,'3,17','系统日志');
INSERT INTO `tb_language_modules` VALUES (18,3,'login',18,2,'3,18','后台登陆');
INSERT INTO `tb_language_modules` VALUES (19,3,'mail',19,2,'3,19','邮件模板');
INSERT INTO `tb_language_modules` VALUES (20,3,'mailhistory',20,2,'3,20','邮件历史');
INSERT INTO `tb_language_modules` VALUES (21,3,'menu',21,2,'3,21','后台菜单');
INSERT INTO `tb_language_modules` VALUES (22,3,'miniblog',22,2,'3,22','微博');
INSERT INTO `tb_language_modules` VALUES (23,3,'role',23,2,'3,23','管理员角色');
INSERT INTO `tb_language_modules` VALUES (24,2,'blog',24,2,'2,24','博客');
INSERT INTO `tb_language_modules` VALUES (25,2,'comments',25,2,'2,25','留言评论');
INSERT INTO `tb_language_modules` VALUES (26,2,'guestbook',26,2,'2,26','留言');

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
