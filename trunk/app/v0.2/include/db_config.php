<?php
/**
 * 数据库配置
 *
 * @file            db_config.php
 * @package         Yab\Config
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-18 15:40:22
 * @lastmodify      $Date$ $Author$
 */

define('DB_TYPE'                , 'pdo');          //数据库类型
define('DB_HOST'                , 'localhost');    //数据库主机名
define('DB_PORT'                , '');             //数据库端口
define('DB_NAME'                , 'db_yablog_v' . str_replace('.', '_', YAB_VERSION));  //数据库名
define('DB_USER'                , 'root');         //数据库用户名
define('DB_PWD'                 , '');             //数据库密码
define('DB_PREFIX'              , 'tb_');          //数据表前缀
define('DB_CHARSET'             , 'utf8');         //数据库编码
define('DB_DSN'                 , 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME);//DSN

define('TB_PREFIX'              , DB_PREFIX);                           //数据表前缀
define('TB_ADMIN'               , TB_PREFIX . 'admin');                 //管理员表
define('TB_ADMIN_LOGIN_HISTORY' , TB_PREFIX . 'admin_login_history');   //管理员登陆历史表
define('TB_ADMIN_ROLE'          , TB_PREFIX . 'admin_role');            //管理员角色表
define('TB_ADMIN_ROLE_PRIV'     , TB_PREFIX . 'admin_role_priv');       //管理员角色权限表
define('TB_AREA'                , TB_PREFIX . 'area');                  //国家地区表
define('TB_MENU'                , TB_PREFIX . 'menu');                  //后台菜单表
define('TB_LOG'                 , TB_PREFIX . 'log');                   //系统日志表
define('TB_FIELD'               , TB_PREFIX . 'field');                 //表单域表
define('TB_SESSION'             , TB_PREFIX . 'session');               //session表
define('TB_COMMENTS'            , TB_PREFIX . 'comments');              //留言评论表
define('TB_CATEGORY'            , TB_PREFIX . 'category');              //博客分类表
define('TB_BLOG'                , TB_PREFIX . 'blog');                  //博客表
define('TB_MINIBLOG'            , TB_PREFIX . 'miniblog');              //微博表
define('TB_TAG'                 , TB_PREFIX . 'tag');                   //标签表
define('TB_HTML'                , TB_PREFIX . 'html');                  //生成静态页管理
define('TB_MAIL_TEMPLATE'       , TB_PREFIX . 'mail_template');         //邮件模板
define('TB_MAIL_HISTORY'        , TB_PREFIX . 'mail_history');          //邮件历史
define('TB_LANGUAGE_MODULES'    , TB_PREFIX . 'language_modules');      //语言包模块
define('TB_LANGUAGE_ITEMS'      , TB_PREFIX . 'language_items');        //语言项
define('TB_SHORTCUT'            , TB_PREFIX . 'shortcut');              //快捷方式