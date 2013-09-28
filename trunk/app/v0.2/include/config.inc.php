<?php
/**
 * 动态配置
 *
 * @file            config.inc.php
 * @package         Yab\Config
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-26 12:04:24
 * @lastmodify      $Date$ $Author$
 */

return array(
    //项目设置
    'DEFAULT_AJAX_RETURN'   => 'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
    'LANGUAGE_ARR'          => array('zh_cn', 'en'),//语言
    'AUTOLOAD'              => array(//自动加载
        'Image'             => LIB_PATH . 'image/Image' . PHP_EXT,//图像处理类
        'Verifycode'        => LIB_PATH . 'image/Verifycode' . PHP_EXT,//验证码
        'Upload'            => LIB_PATH . 'image/Upload' . PHP_EXT,//上传
    ),

    //数据库配置
    'DB_TYPE'               => DB_TYPE,      //数据库类型
    'DB_HOST'               => DB_HOST,      //服务器地址
    'DB_NAME'               => DB_NAME,      //数据库名
    'DB_USER'               => DB_USER,      //用户名
    'DB_PWD'                => DB_PWD,       //密码
    'DB_PORT'               => DB_PORT,      //端口
    'DB_PREFIX'             => DB_PREFIX,    //表前缀
    'DB_DSN'                => DB_DSN,       //DSN
    'LOG_SQL'               => false,        //是否记录sql语句

    //日志配置
    'LOG_TYPE'              => 3,       //文件
    'LOG_FILE_SUFFIX'       => '.log',  //日志文件名后缀

    'JSONP_CALLBACK'        => JSONP_CALLBACK,//jsonp 回调参数名

    //模板设置
    'HTML_SUFFIX'           => '.shtml',//静态文件名后缀
    'TEMPLATE_SUFFIX'       => TEMPLATE_EXT,//模板后缀
    'TMPL_EXCEPTION_FILE'   => VIEW_PATH . 'error/error.phtml',//错误模板
    'TEMPLATE_CONFIG'       => array(//模板配置
        '_templates_path'   => FRONT_THEME_PATH,
        '_compile_path'     => FRONT_THEME_PATH . 'templates_c/',
        '_cache_path'       => FRONT_THEME_PATH . 'templates_d/',
        '_caching'          => !IS_LOCAL,
        '_force_compile'    => APP_DEBUG,
    ),

    //安全设置
    'CSRF_TOKEN'            => true,        //开启防csrf攻击
    'CSRF_TOKEN_ON'         => '_csrf',     //参数名称
);