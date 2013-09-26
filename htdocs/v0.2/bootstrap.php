<?php
/**
 * yablog入口引导文件
 *
 * @file            bootstrap.php
 * @package         Yab
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-26 10:28:08
 * @lastmodify      $Date$ $Author$
 */

!defined('MODULE_NAME') && exit('Access Denied');

define('APP_NAME'       , 'yablog');                //项目名称
define('YAB_VERSION'    , '0.2');                   //版本号
define('VERSION_PATH'   , 'v' . YAB_VERSION . '/'); //版本目录
define('WWWROOT'        , __DIR__ . '/');           //网站根目录

define('IS_LOCAL'       , true);                    //本地环境
define('APP_DEBUG'      , true);                    //调试
define('RUNTIME_FILE'   , WWWROOT . '~runtime.php');//运行时文件

define('YAB_PATH'       , dirname(dirname(WWWROOT)) . '/');     //系统目录
define('YAB_APP_PATH'   , YAB_PATH . 'app/' . VERSION_PATH);    //系统应用程序目录
define('INCLUDE_PATH'   , YAB_APP_PATH . 'include/');           //include包含路径
define('CORE_PATH'      , YAB_APP_PATH . 'core/');              //核心文件路径

require(CORE_PATH . 'Application.class.php');

$require_files = array(
    INCLUDE_PATH . 'app_config.php',
    INCLUDE_PATH . 'db_config.php',
);

new Application($require_files);