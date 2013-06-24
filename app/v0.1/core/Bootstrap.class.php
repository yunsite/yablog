<?php
/**
 * 入口插件
 *
 * @file            Bootstrap.class.php
 * @package         Yab\Plugin
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-25 09:32:32
 * @lastmodify      $Date$ $Author$
 */

class BootstrapPlugin extends Yaf_Plugin_Abstract {
    /**
     * 禁止直接访问Error,Base控制器
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2013-02-17 11:39:45
     *
     * @throws RuntimeExpection 直接访问Error,Base控制器，抛出异常
     *
     * @return void 无返回值
     */
    private function _denyControllers() {
        $deny_controllers = array('Error', 'Common');

        if (in_array(CONTROLLER_NAME, $deny_controllers)) {
            throw new Exception(L('INVALID,VISIT'));
        }

    }//end _denyControllers

    /**
     * 启动
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:05:23
     * @lastmodify     2013-01-21 15:25:25 by mrmsl
     *
     * @return void 无返回值
     */
    private function _init() {
        set_error_handler('error_handler');
        set_exception_handler('exception_handler');
        register_shutdown_function('fatal_error');
        //spl_autoload_register('autoload');

        ob_get_level() != 0 && ob_end_clean();

        if (IS_LOCAL && APP_DEBUG) {//本地开发环境
            error_reporting(E_ALL|E_STRICT);//错误报告
            ini_set('display_errors', 1);//显示错误
        }
        else {
            ini_set('display_errors', 0);
        }

        if (get_magic_quotes_gpc()) {
            !empty($_GET) && ($_GET = stripslashes_deep($_GET));
            !empty($_POST) && ($_POST = stripslashes_deep($_POST));
            !empty($_COOKIE) && ($_COOKIE = stripslashes_deep($_COOKIE));
        }

        date_default_timezone_set(sys_config('sys_timezone_default_timezone', '', DEFAULT_TIMEZONE));//设置系统时区
        define('APP_NOW_TIME'       , time());//当前时间戳

        $this->_initLangTheme();//语言包及皮肤
        $this->_initSession();//session

        C(include(INCLUDE_PATH . 'config.inc.php'));//配置
    }//end _init

    /**
     * 初始化语言包及模板皮肤
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:49:38
     * @lastmodify     2013-01-21 15:27:02 by mrmsl
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

        $theme = cookie('theme');
        $theme = $theme ? $theme : sys_config('sys_base_theme', '', $default = 'default');//皮肤

        if (!is_dir(VIEW_PATH . $theme)) {//皮肤不存在
            $theme = $default;
            cookie('theme', null);
        }

        define('THEME'           , $theme);
        define('THEME_PATH'      , VIEW_PATH . THEME . '/');

        !defined('FRONT_THEME_PATH') && define('FRONT_THEME_PATH', str_replace('modules/admin/', 'modules/' . FRONT_MODULE_NAME . '/', THEME_PATH));
    }//end _initLangTheme

    /**
     * 初始化session
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:06:31
     * @lastmodify     2013-01-21 15:28:18 by mrmsl
     *
     * @return void 无返回值
     */
    private function _initSession() {
        $config = array(//session配置
            'name'              => sys_config('sys_session_name'),//指定会话名以用做 cookie 的名字，只能由字母组成，通常默认为 PHPSESSID
            'save_path'         => sys_config('sys_session_save_path'),//session保存路径,相对SESSION_PATH常量路径,仅当session.save_handler为files时有效
            'gc_maxlifetime'    => sys_config('sys_session_gc_maxlifetime'),//指定过了多少秒之后数据就会被视为“垃圾”并被清除
            'use_trans_sid'     => sys_config('sys_session_use_trans_sid'),//是否启用透明 SID 支持
            'use_cookies'       => sys_config('sys_session_use_cookies'),//是否在客户端用 cookie 来存放会话 ID
            'use_only_cookies'  => sys_config('sys_session_use_only_cookies'),//指定是否在客户端仅仅使用 cookie 来存放会话 ID
            'cookie_lifetime'   => sys_config('sys_session_cookie_lifetime'),//session cookie过期时间
            'cookie_path'       => sys_config('sys_session_cookie_path'),//session cookie保存路径
            'cookie_domain'     => WEB_SESSION_COOKIE_DOMAIN,//session cookie域名
            'cookie_secure'     => sys_config('sys_session_cookie_secure'),//是否仅通过安全连接发送 cookie
            'cookie_httponly'   => sys_config('sys_session_cookie_httponly'),//session cookie只能通过http获取，javascript无法获取
            'save_handler'      => sys_config('sys_session_save_handler'),//存储和获取与会话关联的数据的处理器的名字，默认files
        );

        if (!isset($_SERVER['argv'])) {
            //function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
            ob_start();
            session($config);
            Yaf_Registry::set(SESSION_ADMIN_KEY, session(SESSION_ADMIN_KEY));//管理员信息
        }
    }

    /**
     * 路由开始事件
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 10:03:34
     * @lastmodify     2013-01-21 15:28:59 by mrmsl
     *
     * @param object $request  Yaf_Request_Http实例
     * @param object $response Yaf_Response_Http实例
     *
     * @return void 无返回值
     */
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        Yaf_Loader::getInstance(LIB_PATH, LIB_PATH);//注册项目及全局类库路径
        Yaf_Dispatcher::getInstance()->disableView();//禁用自动渲染模板输出
        $this->_init();
    }

    /**
     * 路由结束事件
     *
     * @author         mrmsl <msl-138@163.com>
     * @date           2012-12-25 09:33:26
     * @lastmodify     2013-02-17 11:43:31 by mrmsl
     *
     * @param object $request  Yaf_Request_Http实例
     * @param object $response Yaf_Response_Http实例
     *
     * @return void 无返回值
     */
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        define('CONTROLLER_NAME'        , $request->getControllerName());   //控制器
        define('ACTION_NAME'            , $request->getActionName());       //操作方法
        define('REQUEST_METHOD'         , $request->getMethod());           //请求方法
        define('TEMPLATE_FILE'          , THEME_PATH . strtolower(CONTROLLER_NAME) . '/' . ACTION_NAME . C('TEMPLATE_SUFFIX'));//模板文件
        define('REFERER_PAGER'          , empty($_SERVER['HTTP_REFERER']) ? '' : urldecode($_SERVER['HTTP_REFERER']));//来路页面

        //请求uri
        if (isset($_SERVER['REQUEST_URI'])) {
            define('REQUEST_URI', urldecode($_SERVER['REQUEST_URI']));
        }
        else {
            $querystring = $request->getQuery();
            /**
             * @ignore
             */
            define('REQUEST_URI', urldecode($request->getRequestUri() . ($querystring ? '?' . http_build_query($querystring) : '')));
        }

        $this->_denyControllers();//禁止直接访问Error,Base控制器

        if ($languages = F(MODULE_NAME . DS . LANG . DS . strtolower(CONTROLLER_NAME), '', LANG_PATH)) {//当前控制器语言包
            L($languages);
        }

    }//end routerShutdown
}