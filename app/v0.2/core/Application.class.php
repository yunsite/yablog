<?php
/**
 * 应用程序类
 *
 * @file            Application.class.php
 * @package         Yab\Core
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-26 10:37:05
 * @lastmodify      $Date$ $Author$
 */

class Application {
    /**
     * @var array $_require_files 加载核心文件
     */
    private $_require_files = array();

    /**
     * 创建运行时文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 10:37:40
     *
     * @return void 无返回值
     */
    private function _buildRuntimeFile() {
        $filesize = 0;//加载文件大小
        $compile  = '<?php' . PHP_EOL . "!defined('YAB_PATH') && exit('Access Denied');";//编译内容

        //加载核心文件，用空间换时间
        if (APP_DEBUG) {//调试

            foreach ($this->_require_files as $file) {
                require($file);
            }
        }
        else {

            foreach ($this->_require_files as $file) {
                require($file);

                $filesize += filesize($file);
                $compile  .= compile_file($file);
            }
        }

        $require_files = array(
            CORE_PATH . 'Template' . PHP_EXT,       //模板类
            CORE_PATH . 'Controller' . PHP_EXT,     //控制器类
            CORE_PATH . 'Model' . PHP_EXT,          //模型类
            CORE_PATH . 'Logger' . PHP_EXT,         //日志类
            CORE_PATH . 'Filter' . PHP_EXT,         //参数验证及过滤类
            CORE_PATH . 'Db' . PHP_EXT,             //Db类
            CORE_PATH  . 'drivers/db/Db' . ucfirst(DB_TYPE) . PHP_EXT,//数据库驱动类

            LIB_PATH . 'BaseModel' . PHP_EXT,       //底层基础模型类
            LIB_PATH . 'SVNDiff' . PHP_EXT,         //仿svn比较字符内容差异类

            APP_PATH . '/controllers/CommonController' . PHP_EXT,   //当前模块通用控制器
            APP_PATH . '/models/CommonModel' . PHP_EXT,             //当前模块通用模型

            LIB_PATH . 'LogModel' . PHP_EXT,        //日志模型,extends CommonModel
        );

        //加载核心文件，用空间换时间
        if (APP_DEBUG) {//调试

             foreach ($require_files as $file) {
                require($file);
            }
        }
        else{

            foreach ($require_files as $file) {
                require($file);

                $filesize += filesize($file);
                $compile  .= compile_file($file);
            }

            file_put_contents(RUNTIME_FILE, $compile);
            $size = filesize(RUNTIME_FILE);//编译后大小
            file_put_contents(LOG_PATH. 'compile_runtime_file.log', new_date() . '(' . format_size($filesize) . ' => ' . format_size($size) . ')' . PHP_EOL, FILE_APPEND);

        }
    }//end _buildRuntimeFile

    /**
     * 检查运行环境，必须要满足：1、PHP版本大于5.3
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 10:50:20
     *
     * @return void 无返回值
     */
    private function _checkRuntimeRequirements() {
        !version_compare(PHP_VERSION, '5.3', '>') && exit('php5.3 or higher required!');
    }

    /**
     * 禁止直接访问Error,Base,Common控制器
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2013-02-17 11:39:45
     *
     * @throws RuntimeExpection 直接访问Error,Base,Common控制器，抛出异常
     *
     * @return void 无返回值
     */
    private function _denyControllers() {
        $deny_controllers = array('Error', 'Common', 'Base');

        if (in_array(CONTROLLER_NAME, $deny_controllers)) {
            throw new Exception(L('INVALID,VISIT'));
        }

    }

    /**
     * 初始化
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:05:23
     *
     * @return void 无返回值
     */
    private function _init() {
        set_error_handler('error_handler');
        set_exception_handler('exception_handler');
        register_shutdown_function('fatal_error');
        spl_autoload_register('autoload');
        error_reporting(E_ALL|E_STRICT);//错误报告

        if (IS_LOCAL && APP_DEBUG) {//本地开发环境
            ini_set('display_errors', 1);//显示错误
        }
        else {
            ini_set('display_errors', 0);
        }

        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {

            if (!empty($_GET)) {
                $_GET = stripslashes_deep($_GET);
            }

            if (!empty($_POST)) {
                $_POST = stripslashes_deep($_POST);
            }

            if (!empty($_COOKIE)) {
                $_COOKIE = stripslashes_deep($_COOKIE);
            }
        }

        date_default_timezone_set(sys_config('sys_timezone_default_timezone', '', DEFAULT_TIMEZONE));//设置系统时区
        define('APP_NOW_TIME'       , time());//当前时间戳
        define('REQUEST_METHOD'     , isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'empty');            //请求方法
        define('REFERER_PAGER'      , empty($_SERVER['HTTP_REFERER']) ? '' : urldecode($_SERVER['HTTP_REFERER']));          //来路页面
        define('IS_AJAX'            , isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']);//ajax请求

        //请求uri
        if (isset($_SERVER['REQUEST_URI'])) {
            define('REQUEST_URI', urldecode($_SERVER['REQUEST_URI']));
        }
        else {
            /**
             * @ignore
             */
            define('REQUEST_URI', 'empty');
        }

    }//end _init

    /**
     * 初始化语言包及模板皮肤
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:49:38
     *
     * @return void 无返回值
     */
    private function _initLangTheme() {
        $lang = cookie('lang');
        $lang = $lang ? $lang : sys_config('sys_base_lang', '', DEFAULT_LANG);//语言

        if ($languages = F($lang, '', LANG_PATH)) {
            L($languages);
        }
        else {//语言包不存在
            $lang = DEFAULT_LANG;
            cookie('lang', null);
        }

        define('LANG', $lang);

        if ($languages = F(MODULE_NAME . DS . LANG . DS . 'common', '', LANG_PATH)) {//模块通用语言包
            L($languages);
        }

        if ($languages = F(MODULE_NAME . DS . LANG . DS . strtolower(CONTROLLER_NAME), '', LANG_PATH)) {//当前控制器语言包
            L($languages);
        }

        $theme = cookie('theme');
        $theme = $theme ? $theme : sys_config('sys_base_theme', '', $default = 'default');//皮肤

        if (!is_dir(VIEW_PATH . $theme)) {//皮肤不存在
            $theme = $default;
            cookie('theme', null);
        }

        define('THEME'           , $theme);
        define('THEME_PATH'      , VIEW_PATH . THEME . '/');
        define('FRONT_THEME_PATH', str_replace('modules/admin/', 'modules/' . FRONT_MODULE_NAME . '/', THEME_PATH));
    }//end _initLangTheme

    /**
     * 初始化session
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:06:31
     *
     * @return void 无返回值
     */
    private function _initSession() {
        $config = array(//session配置
            'name'              => sys_config('sys_session_name'),              //指定会话名以用做 cookie 的名字，只能由字母组成，通常默认为 PHPSESSID
            'save_path'         => sys_config('sys_session_save_path'),         //session保存路径,相对SESSION_PATH常量路径,仅当session.save_handler为files时有效
            'gc_maxlifetime'    => sys_config('sys_session_gc_maxlifetime'),    //指定过了多少秒之后数据就会被视为“垃圾”并被清除
            'use_trans_sid'     => sys_config('sys_session_use_trans_sid'),     //是否启用透明 SID 支持
            'use_cookies'       => sys_config('sys_session_use_cookies'),       //是否在客户端用 cookie 来存放会话 ID
            'use_only_cookies'  => sys_config('sys_session_use_only_cookies'),  //指定是否在客户端仅仅使用 cookie 来存放会话 ID
            'cookie_lifetime'   => sys_config('sys_session_cookie_lifetime'),   //session cookie过期时间
            'cookie_path'       => sys_config('sys_session_cookie_path'),       //session cookie保存路径
            'cookie_domain'     => WEB_SESSION_COOKIE_DOMAIN,                   //session cookie域名
            'cookie_secure'     => sys_config('sys_session_cookie_secure'),     //是否仅通过安全连接发送 cookie
            'cookie_httponly'   => sys_config('sys_session_cookie_httponly'),   //session cookie只能通过http获取，javascript无法获取
            'save_handler'      => sys_config('sys_session_save_handler'),      //存储和获取与会话关联的数据的处理器的名字，默认files
        );

        if (!isset($_SERVER['argv'])) {
            //function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
            ob_start();
            session($config);
            C(SESSION_ADMIN_KEY, session(SESSION_ADMIN_KEY));//管理员信息
        }
    }//end _initSession

    /**
     * 路由
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 11:25:10
     *
     * @throws 控制器或操作方法不满足/^[a-zA-Z]\w+$/时抛出异常
     *
     * @return void 无返回值
     */
    private function _route() {

        if (isset($_GET['__router'])) {//路由
            $pathinfo = $_GET['__router'];
        }
        else {
            $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        }

        $pathinfo_arr = explode(DS, trim($pathinfo, DS));

        if (1 == count($pathinfo_arr)) {
            $pathinfo_arr = array($pathinfo_arr[0], 'index');
        }

        define('CONTROLLER_NAME'    , empty($pathinfo_arr[0]) ? 'Index' : ucfirst($pathinfo_arr[0]));       //控制器
        define('ACTION_NAME'        , empty($pathinfo_arr[1]) ? 'index' : strtolower($pathinfo_arr[1]));    //操作方法
    }//end _route

    /**
     * 构造函数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 10:50:37
     *
     * @param array $require_files 预加载核心文件
     *
     * @return void 无返回值
     */
    public function __construct($require_files) {
        $this->_checkRuntimeRequirements();//运行环境检查
        $this->_require_files[] = CORE_PATH . 'functions/functions.php';//函数库

        if ($require_files) {
            $this->_require_files = array_merge($this->_require_files, $require_files);//合并核心文件
        }

        $this->bootstrap();
    }

    /**
     * 启动程序
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 10:51:34
     *
     * @return void 无返回值
     */
    public function bootstrap() {
        ob_get_level() != 0 && ob_end_clean();
        header('content-type: text/html; charset=utf-8');

        if (APP_DEBUG || !is_file(RUNTIME_FILE)) {
            $this->_buildRuntimeFile();

            if (APP_DEBUG && is_file(RUNTIME_FILE)) {
                unlink(RUNTIME_FILE);
            }
        }
        else {
            require(RUNTIME_FILE);
        }

        $this->_init();             //初始化
        $this->_route();            //路由
        $this->_denyControllers();  //禁止直接访问指定控制器
        $this->_initLangTheme();    //语言包及皮肤
        $this->_initSession();      //session

        C(include(INCLUDE_PATH . 'config.inc.php'));//加载配置
        define('TEMPLATE_FILE'      , THEME_PATH . strtolower(CONTROLLER_NAME) . '/' . ACTION_NAME . C('TEMPLATE_SUFFIX')); //模板文件

        $this->run();
    }//end bootstrap

    /**
     * 执行程序
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 11:57:35
     *
     * @return void 无返回值
     */
    public function run() {

        if (!preg_match('/^[a-zA-Z]\w+$/', CONTROLLER_NAME)) {
            throw new Exception(L('INVALID,CONTROLLER') . ': ' . CONTROLLER_NAME);
        }
        elseif (!preg_match('/^[a-zA-Z]\w+$/', ACTION_NAME)) {
            throw new Exception(L('INVALID,ACTION') . ': ' . ACTION_NAME);
        }

        $controller = A(CONTROLLER_NAME);
        $action     = ACTION_NAME . 'Action';

        if (!$controller) {
            throw new Exception(L('CONTROLLER') . ': ' . CONTROLLER_NAME . L('NOT_EXIST'));
        }

        if (method_exists($controller, $action) || method_exists($controller, '__call')) {

            if (method_exists($controller, $before = 'before' . $action)) {//前置操作
                call_user_func(array(&$controller, $before));
            }

            call_user_func(array(&$controller, $action));

            if (method_exists($controller, $after = 'after' . $action)) {//后置操作
                call_user_func(array(&$controller, $after));
            }
        }
        else {
            throw new Exception(L('INVALID,ACTION') . ': ' . $action);
        }
    }//end run
}