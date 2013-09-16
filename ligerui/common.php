<?php
header('content-type: text/html; charset=utf-8');
define('APP_DEBUG'              , true);
define('E_APP_EXCEPTION'        , 'E_APP_EXCEPTION');//异常
define('E_APP_INFO'             , 'E_APP_INFO');     //信息
define('E_APP_DEBUG'            , 'E_APP_DEBUG');    //调试
define('E_APP_SQL'              , 'E_APP_SQL');      //SQL
define('E_APP_ROLLBACK_SQL'     , 'E_APP_ROLLBACK_SQL');      //事务回滚SQL
define('INCLUDE_PATH'           , __DIR__ . '/include/');

require(INCLUDE_PATH . 'functions.php');
require(INCLUDE_PATH . 'db_config.php');
require(INCLUDE_PATH . 'Db.class.php');
require(INCLUDE_PATH . 'DbPdo.class.php');
C(include(INCLUDE_PATH . 'config.inc.php'));

$db = Db::getInstance();