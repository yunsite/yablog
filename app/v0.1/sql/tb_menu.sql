/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;

CREATE TABLE `tb_menu` (
  `menu_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `parent_id` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `menu_name` char(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `controller` char(20) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` char(20) NOT NULL DEFAULT '' COMMENT '操作',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sort_order` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '层级',
  `node` char(20) NOT NULL DEFAULT '' COMMENT '节点',
  `memo` char(60) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`menu_id`),
  KEY `controller` (`controller`),
  KEY `action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=gbk COMMENT='后台管理菜单表 by mashanling on 2012-12-27 12:44:04';

INSERT INTO `tb_menu` VALUES (1,24,'菜单管理','menu','#',1,0,2,'24,1','');
INSERT INTO `tb_menu` VALUES (2,1,'添加菜单','menu','add',1,1,3,'24,1,2','');
INSERT INTO `tb_menu` VALUES (3,0,'权限管理','priv','#',1,0,1,'3','');
INSERT INTO `tb_menu` VALUES (4,3,'管理员列表','admin','list',1,0,2,'3,4','');
INSERT INTO `tb_menu` VALUES (5,3,'添加管理员','admin','add',1,0,2,'3,5','');
INSERT INTO `tb_menu` VALUES (6,3,'角色管理','role','list',1,0,2,'3,6','');
INSERT INTO `tb_menu` VALUES (7,3,'添加角色','role','add',1,0,2,'3,7','');
INSERT INTO `tb_menu` VALUES (8,1,'菜单列表','menu','list',1,0,3,'24,1,8','');
INSERT INTO `tb_menu` VALUES (24,0,'系统管理','system','#',1,0,1,'24','');
INSERT INTO `tb_menu` VALUES (25,24,'系统日志','log','list',1,25,2,'24,25','');
INSERT INTO `tb_menu` VALUES (33,1,'生成菜单缓存','menu','create',0,5,3,'24,1,33','');
INSERT INTO `tb_menu` VALUES (34,24,'压缩js','packer','list',1,24,2,'24,34','');
INSERT INTO `tb_menu` VALUES (35,0,'修改密码','admin','changePassword',1,35,1,'35','');
INSERT INTO `tb_menu` VALUES (39,40,'地区列表','area','list',1,39,3,'24,40,39','');
INSERT INTO `tb_menu` VALUES (40,24,'国家地区管理','area','#',1,40,2,'24,40','');
INSERT INTO `tb_menu` VALUES (41,40,'添加地区','area','add',1,41,3,'24,40,41','');
INSERT INTO `tb_menu` VALUES (42,24,'系统设置','system','#',1,42,2,'24,42','');
INSERT INTO `tb_menu` VALUES (43,42,'基本信息','system','base',1,43,3,'24,42,43','');
INSERT INTO `tb_menu` VALUES (44,42,'SEO设置','system','seo',1,44,3,'24,42,44','');
INSERT INTO `tb_menu` VALUES (45,42,'邮箱配置','system','mail',1,45,3,'24,42,45','');
INSERT INTO `tb_menu` VALUES (46,42,'安全设置','system','security',1,46,3,'24,42,46','');
INSERT INTO `tb_menu` VALUES (47,24,'表单域管理','field','#',1,47,2,'24,47','');
INSERT INTO `tb_menu` VALUES (48,47,'表单域列表','field','list',1,0,3,'24,47,48','');
INSERT INTO `tb_menu` VALUES (49,47,'添加表单域','field','add',1,46,3,'24,47,49','');
INSERT INTO `tb_menu` VALUES (57,25,'管理员操作日志','log','admin',1,57,3,'24,25,57','');
INSERT INTO `tb_menu` VALUES (58,25,'sql错误','log','sql',1,58,3,'24,25,58','');
INSERT INTO `tb_menu` VALUES (59,25,'系统错误','log','system',1,59,3,'24,25,59','');
INSERT INTO `tb_menu` VALUES (60,25,'没有权限','log','permission',1,60,3,'24,25,60','');
INSERT INTO `tb_menu` VALUES (61,25,'非法参数','log','param',1,61,3,'24,25,61','');
INSERT INTO `tb_menu` VALUES (62,25,'定时任务','log','crontab',1,62,3,'24,25,62','');
INSERT INTO `tb_menu` VALUES (63,25,'后台登陆日志','log','adminLogin',1,63,3,'24,25,63','');
INSERT INTO `tb_menu` VALUES (66,25,'验证表单错误','log','form',1,66,3,'24,25,66','');
INSERT INTO `tb_menu` VALUES (67,25,'验证码错误','log','verifyCode',1,67,3,'24,25,67','');
INSERT INTO `tb_menu` VALUES (68,25,'css及js加载时间记录','log','loadScriptTime',1,68,3,'24,25,68','');
INSERT INTO `tb_menu` VALUES (69,42,'时间区域','system','timezone',1,69,3,'24,42,69','');
INSERT INTO `tb_menu` VALUES (70,42,'模板设置','system','template',1,70,3,'24,42,70','');
INSERT INTO `tb_menu` VALUES (71,42,'日志设置','system','log',1,71,3,'24,42,71','');
INSERT INTO `tb_menu` VALUES (72,42,'其它设置','system','other',1,72,3,'24,42,72','');
INSERT INTO `tb_menu` VALUES (74,25,'SQL慢查询','log','slowquery',1,74,3,'24,25,74','');
INSERT INTO `tb_menu` VALUES (75,42,'帐号设置','system','account',1,75,3,'24,42,75','');
INSERT INTO `tb_menu` VALUES (76,42,'session设置','system','session',1,76,3,'24,42,76','');
INSERT INTO `tb_menu` VALUES (77,42,'cookie设置','system','cookie',1,77,3,'24,42,77','');
INSERT INTO `tb_menu` VALUES (78,42,'验证码设置','system','verifycode',1,78,3,'24,42,78','');
INSERT INTO `tb_menu` VALUES (79,24,'模块设置','module','#',1,79,2,'24,79','');
INSERT INTO `tb_menu` VALUES (80,79,'管理员模块','module','admin',1,80,3,'24,79,80','');
INSERT INTO `tb_menu` VALUES (81,0,'mrmsl','test','test',0,81,1,'81','');
INSERT INTO `tb_menu` VALUES (82,25,'事务回滚SQL','log','rollbackSql',1,82,3,'24,25,82','');
INSERT INTO `tb_menu` VALUES (83,85,'留言评论管理','comments','list',1,83,2,'85,83','');
INSERT INTO `tb_menu` VALUES (84,101,'留言模块','module','guestbook',1,84,4,'24,79,101,84','');
INSERT INTO `tb_menu` VALUES (85,0,'内容管理','content','#',1,85,1,'85','');
INSERT INTO `tb_menu` VALUES (86,85,'博客管理','blog','#',1,86,2,'85,86','');
INSERT INTO `tb_menu` VALUES (87,86,'博客列表','blog','list',1,87,3,'85,86,87','');
INSERT INTO `tb_menu` VALUES (88,86,'添加博客','blog','add',1,88,3,'85,86,88','');
INSERT INTO `tb_menu` VALUES (89,85,'博客分类管理','category','#',1,89,2,'85,89','');
INSERT INTO `tb_menu` VALUES (90,89,'分类列表','category','list',1,90,3,'85,89,90','');
INSERT INTO `tb_menu` VALUES (91,89,'添加分类','category','add',1,91,3,'85,89,91','');
INSERT INTO `tb_menu` VALUES (93,85,'微博管理','miniblog','#',1,93,2,'85,93','');
INSERT INTO `tb_menu` VALUES (94,93,'微博列表','miniblog','list',1,94,3,'85,93,94','');
INSERT INTO `tb_menu` VALUES (95,93,'添加微博','miniblog','add',1,95,3,'85,93,95','');
INSERT INTO `tb_menu` VALUES (96,24,'生成静态页管理','html','#',1,96,2,'24,96','');
INSERT INTO `tb_menu` VALUES (97,96,'生成静态页列表','html','list',1,97,3,'24,96,97','');
INSERT INTO `tb_menu` VALUES (98,96,'添加生成静态页','html','add',1,98,3,'24,96,98','');
INSERT INTO `tb_menu` VALUES (99,101,'评论模块','module','comments',1,99,4,'24,79,101,99','');
INSERT INTO `tb_menu` VALUES (100,42,'显示设置','system','show',1,100,3,'24,42,100','');
INSERT INTO `tb_menu` VALUES (101,79,'留言评论模块','module','guestbook_comments',1,101,3,'24,79,101','');
INSERT INTO `tb_menu` VALUES (102,24,'邮件模板及历史管理','mail','#',1,102,2,'24,102','');
INSERT INTO `tb_menu` VALUES (103,102,'邮件模板列表','mail','list',1,103,3,'24,102,103','');
INSERT INTO `tb_menu` VALUES (104,102,'添加邮件模板','mail','add',1,104,3,'24,102,104','');
INSERT INTO `tb_menu` VALUES (105,85,'邮件历史','mailHistory','list',1,105,2,'85,105','');
INSERT INTO `tb_menu` VALUES (106,85,'管理员登陆历史','adminLoginHistory','list',1,106,2,'85,106','');
INSERT INTO `tb_menu` VALUES (107,24,'语言包管理','lang','#',1,107,2,'24,107','');
INSERT INTO `tb_menu` VALUES (108,107,'语言包模块管理','languageModules','#',1,108,3,'24,107,108','');
INSERT INTO `tb_menu` VALUES (109,108,'语言包模块列表','languageModules','list',1,109,4,'24,107,108,109','');
INSERT INTO `tb_menu` VALUES (110,108,'添加语言包模块','languageModules','add',1,110,4,'24,107,108,110','');
INSERT INTO `tb_menu` VALUES (111,107,'语言项管理','languageItems','#',1,111,3,'24,107,111','');
INSERT INTO `tb_menu` VALUES (112,111,'语言项列表','languageItems','list',1,112,4,'24,107,111,112','');
INSERT INTO `tb_menu` VALUES (113,111,'添加语言项','languageItems','add',1,113,4,'24,107,111,113','');

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
