/**
 * 数据库结构 sql 历史
 *
 * @file            db_yablog_v0_1.history.sql
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-19 15:51:38
 * @lastmodify      $Date$ $Author$
 */

/*新建 语言包模块表 by mrmsl on 2013-06-19 15:52:20*/
CREATE TABLE tb_language_modules(
    `module_id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
    `parent_id` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
    `module_name` char(20) NOT NULL DEFAULT '' COMMENT '语言文件名',
    `sort_order` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '排序,越小越靠前',
    `level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '层级',
    `node` char(20) NOT NULL DEFAULT '' COMMENT '节点',
    `memo` char(60) NOT NULL DEFAULT '' COMMENT '备注',
    PRIMARY KEY (`module_id`),
    UNIQUE KEY (`module_name`, parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=gbk COMMENT='语言包模块表 by mrmsl 2013-06-19 15:52:20';

INSERT INTO tb_language_modules VALUES
(1, 0, '整站', 1, 1, 1, ''),
(2, 0, '前台', 2, 1, 2, ''),
(3, 0, '后台', 3, 1, 3, '')


/*新建 语言项表 by mrmsl on 2013-06-19 16:31:56*/
CREATE TABLE tb_language_items(
    `item_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
    `module_id` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '语言模块id',
    `var_name` varchar(50) NOT NULL DEFAULT '' COMMENT '语言变量名',
    `var_value_zh_cn` text NOT NULL COMMENT '语言值',
    `var_value_en` text NOT NULL COMMENT '语言值',
    `sort_order` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序,越小越靠前',
    `level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '层级',
    `node` varchar(20) NOT NULL DEFAULT '' COMMENT '节点',
    `memo` varchar(60) NOT NULL DEFAULT '' COMMENT '备注',
    `to_js` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否生成js语言;0否,1是',
    PRIMARY KEY (`language_id`),
    UNIQUE KEY (`module_id`, var_name)
) ENGINE=InnoDB DEFAULT CHARSET=gbk COMMENT='语言项表 by mrmsl 2013-06-19 16:31:56';

ALTER TABLE `tb_language_items`
ADD CONSTRAINT `tb_language_items_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `tb_language_modules` (`module_id`) ON DELETE CASCADE ON UPDATE CASCADE;