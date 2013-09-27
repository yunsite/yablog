<?php
/**
 * 函数库。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            functions.php
 * @package         Yab
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-22 16:47:21
 * @lastmodify      $Date$ $Author$
 */


/**
 * 实例化模块Action
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:06:39 by mrmsl
 *
 * @param string $name      模块名称
 * @param array  $argumetns 传递给模块构造函数参数
 *
 * @return object 模块实例
 */
function A($name) {
    static $_action = array();

    if (isset($_action[$name])) {
        return $_action[$name];
    }

    if (class_exists($class = $name . 'Controller')) {
        $_action[$name] = new $class();
    }
    else {
        trigger_error($class . L('NOT_EXIST'), E_USER_ERROR);
        $_action[$name] = false;
    }

    return $_action[$name];
}

/**
 * 获取或设置配置值
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:48:17 by mrmsl
 *
 * @param mixed $name    配置名或配置数组。默认null
 * @param mixed $value   配置值。默认null
 * @param mixed $default 默认值。默认null
 *
 * @return mixed
 */
function C($name = null, $value = null, $default = null) {
    static $_config = array();

    if (empty($name)) {//无参数时获取所有
        return $_config;
    }

    if (is_string($name)) {//优先执行设置获取或赋值

        if (!strpos($name, '.')) {
            $name = strtolower($name);

            if (null === $value) {
                return isset($_config[$name]) ? $_config[$name] : $default;
            }

            $_config[$name] = $value;

            return;
        }

        //二维数组设置和获取支持
        $name    = explode('.', $name);
        $name[0] = strtolower($name[0]);

        if (null === $value) {
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }

        $_config[$name[0]][$name[1]] = $value;

        return;
    }

    if (is_array($name)) {//批量设置
        return $_config = array_merge($_config, array_change_key_case($name));
    }

    return $default;
}//end C

/**
 * Cookie 设置、获取、删除
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:49:32 by mrmsl
 *
 * @param string $name   名称
 * @param string $value  值。默认''
 * @param array  $option 参数设置。默认null
 *
 * @return mixed
 */
function cookie($name, $value = '', $option = null) {
    $config = array(//默认设置
        'prefix' => COOKIE_PREFIX,//cookie 名称前缀
        'expire' => COOKIE_EXPIRE,//cookie 保存时间
        'path'   => COOKIE_PATH,  //cookie 保存路径
        'domain' => COOKIE_DOMAIN,//cookie 有效域名
    );

    if (!empty($option)) {//参数设置(会覆盖黙认设置)

        if (is_numeric($option)) {
            $option = array('expire' => $option);
        }
        elseif (is_string($option)) {
            parse_str($option, $option);
        }

        $config = array_merge($config, array_change_key_case($option));
    }

    if (is_null($name)) {//清除指定前缀的所有cookie

        if (empty($_COOKIE)) {
            return;
        }

        //要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;

        if (!empty($prefix)) { //如果前缀为空字符串将不作处理直接返回

            foreach ($_COOKIE as $key => $val) {

                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }

        return;
    }

    $name = $config['prefix'] . $name;

    if ('' === $value) {
        return isset($_COOKIE[$name]) ? sys_auth($_COOKIE[$name], false) : null; //获取指定Cookie
    }
    else {

        if (is_null($value)) {//删除cookie
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); //删除指定cookie
        }
        else {//设置cookie
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, sys_auth($value), $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }

}//end cookie

/**
 * 实例化模型Model
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:50:46 by mrmsl
 *
 * @param string $name 模型名称
 *
 * @return object 模型实例
 */
function D($name) {
    static $_model = array();

    if (isset($_model[$name])) {
        return $_model[$name];
    }

    if (class_exists($class = $name . 'Model')) {
        $_model[$name] = new $class();
    }
    else {
        $_model[$name] = false;
    }

    return $_model[$name];
}

/**
 * 开始区间调试
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:51:42 by mrmsl
 *
 * @param string $label 标识符。默认global
 *
 * @return void 无返回值
 */
function debug_start($label = 'global') {
    $GLOBALS[$label]['_beginTime'] = microtime(true);
    MEMORY_LIMIT_ON && $GLOBALS[$label]['_beginMem'] = memory_get_usage();
}

/**
 * 区间调试结束，显示指定标记到当前位置的调试
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:52:00 by mrmsl
 *
 * @param string $label 标识符。默认global
 * @param bool   $echo  true输出。默认false
 *
 * @return void 无返回值
 */
function debug_end($label = 'global', $echo = false) {
    $GLOBALS[$label]['_endTime'] = microtime(true);
    $result = '<div style="text-align:center;width:100%">Process ' . $label . ': Times ' . number_format($GLOBALS[$label]['_endTime'] - $GLOBALS[$label]['_beginTime'], 6) . 's ';

    if (MEMORY_LIMIT_ON) {
        $GLOBALS[$label]['_endMem'] = memory_get_usage();
        $result .= ' Memories ' . format_size($GLOBALS[$label]['_endMem'] - $GLOBALS[$label]['_beginMem']);
    }

    $result .= '</div>';

    if ($echo) {
        echo $result;
    }
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:53:29 by mrmsl
 *
 * @param string $name   文件名，不包括.php拓展名
 * @param mixed  $value  文件内容。默认''
 * @param string $path   文件所在目录。默认null=CACHE_PATH常量
 *
 * @return mixed
 */
//快速文件数据读取和保存 针对简单类型数据 字符串、数组
function F($name, $value = '', $path = null) {
    static $_cache = array();

    $path       = $path ? $path : CACHE_PATH;
    $filename   = $path . $name . '.cache.php';
    $cache_key  = md5($filename);

    if ('' !== $value) {

        if (is_null($value)) {//删除缓存
            return new_unlink($filename);
        }
        else {//缓存数据
            $dir = dirname($filename);

            new_mkdir($dir);//目录不存在则创建

            $_cache[$cache_key] = $value;

            return file_put_contents($filename, '<?php' . PHP_EOL . sprintf(AUTO_CREATE_COMMENT, new_date()) . PHP_EOL . 'return ' . var_export($value, true) . ';');
        }
    }

    //获取缓存数据
    if (array_key_exists($cache_key, $_cache)) {
        return $_cache[$cache_key];
    }

    $_cache[$cache_key] = is_file($filename) ? include($filename) : false;

    return $_cache[$cache_key];
}//end F

/**
 * 记录和统计时间
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:55:41 by mrmsl
 *
 * @param string $start 开始标识符
 * @param mixed  $end   结束标识符或结束时间。默认''
 * @param int    $dec   小数点精度。默认4
 *
 * @return void 无返回值
 */
function G($start, $end = '', $dec = 4) {
    static $_info = array();

    if (is_float($end)) {//记录时间
        $_info[$start] = $end;
    }
    elseif (!empty($end)) {//统计时间

        if (!isset($_info[$end])) {
            $_info[$end] = microtime(true);
        }

        return number_format(($_info[$end] - $_info[$start]), $dec);
    }
    else {//记录时间
        $_info[$start] = microtime(true);
    }
}

/**
 * 获取客户端IP地址
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:56:33 by mrmsl
 *
 * @param bool $ip2long true返回ip2long。默认false，返回ip地址
 *
 * @return mixed 如果$ip2long为false，返回ip地址，否则返回ip2long
 */
function get_client_ip($ip2long = false) {
    $index = $ip2long ? 1 : 0;

    static $ip = null;

    if (null !== $ip) {
        return $ip[$index];
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos =  array_search('unknown',$arr);

        if(false !== $pos) {
            unset($arr[$pos]);
        }

        $ip = trim($arr[0]);
    }
    elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $ip2long = ip2long($ip);//IP地址合法验证

    $ip = $ip2long ? array($ip, sprintf('%u', $ip2long)) : array('0.0.0.0', 0);

    return $ip[$index];

}//end get_client_ip

/**
 * 实例化类
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:57:29 by mrmsl
 *
 * @param string $name   类名
 * @param string $method 调用类方法。默认''
 * @param array  $args   传递给$method参数。默认array()
 *
 * @return object 类实例化
 */
//取得对象实例 支持调用类的静态方法
function get_instance_of($name, $method = '', $args = array()) {
    static $_instance = array();

    $identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);

    if (!isset($_instance[$identify])) {
        $o = new $name();

        if (method_exists($o, $method)) {

            if (!empty($args)) {
                $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
            }
            else {
                $_instance[$identify] = $o->$method();
            }
        }
        else {
            $_instance[$identify] = $o;
        }
    }

    return $_instance[$identify];
}//end get_instance_of

/**
 * 获取和设置语言定义(不区分大小写)
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:57:54 by mrmsl
 *
 * @param mixed $name  语言名称或语言数组。默认null
 * @param mixed $value 语言值。默认null
 *
 * @return mixed
 */
function L($name = null, $value = null) {
    static $_lang = array();

    if (empty($name)) {//空参数返回所有定义
        return $_lang;
    }

    //判断语言获取(或设置),若不存在,直接返回全大写$name
    if (is_string($name)) {
        $copy = $name;
        $name = strtoupper($name);

        if (is_null($value)) {

            if (!isset($_lang[$name]) && false !== strpos($name, ',')) {//批量
                $msg = '';

                foreach (explode(',', $copy) as $v) {

                    if (0 === strpos($v, '%')) {//第一个为%，原形返回
                        $msg .= substr($v, 1);
                    }
                    else {
                        $msg .= L($v);
                    }
                }

                $_lang[$name] = $msg; //语言定义

                return $msg;
            }

            return isset($_lang[$name]) ? $_lang[$name] : $name;
        }

        $_lang[$name] = $value; //语言定义

        return;
    }

    if (is_array($name)) {//批量定义
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    }

    return;
}//end L

/**
 * 设置和获取统计数据
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:58:25 by mrmsl
 *
 * @param string $key  键值
 * @param int    $step 步长。默认0，表示取值
 *
 * @return mixed
 */
function N($key, $step = 0) {
    static $_num = array();

    if (!isset($_num[$key])) {
        $_num[$key] = 0;
    }

    if (empty($step)) {
        return $_num[$key];
    }
    else {
        $_num[$key] = $_num[$key] + $step;
    }
}

/**
 * 字符串命名风格转换
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 16:58:35 by mrmsl
 *
 * @param string $name 待转换字符串
 * @param int    $type 转换格式，0 => user_action => userAction, 1=> UserAction => user_action。默认0
 *
 * @return string 转换后字符串
 */
function parse_name($name, $type = 0) {

    if ($type) {//user_action => userAction
        return ucfirst(preg_replace('/_([a-zA-Z])/e', "strtoupper('\\1')", $name));
    }
    else {//UserAction => user_action
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

/**
 * 远程调用模块的操作方法 URL 参数格式: 模块/操作
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-09-26 14:17:49 by mrmsl
 *
 * @param string $url 调用地址
 * @param string|array $vars 调用参数,支持字符串和数组

 * @return mixed 模块的操作方法返回结果
 */
function R($url, $vars = array()) {
    //R('Index/index') => array('dirname' => 'Index', 'basename' => 'index', 'filename' => 'index')
    $info       =   pathinfo($url);
    $action     =   $info['basename'];
    $controller =   $info['dirname'];
    $class      =   A($module);

    if($class){

        if(is_string($vars)) {
            parse_str($vars,$vars);
        }

        return call_user_func_array(array(&$class, strpos($action, 'Action') ? $action : $action . 'Action'), $vars);
    }
    else{
        return false;
    }
}

/**
 * 页面重定向
 *
 * @lastmodify 2012-12-03 13:25:06 by mrmsl
 *
 * @param string $url    重定向页面url
 * @param int    $time   停留时间。默认0
 * @param string $msg    页面内容。默认''
 * @param $int   $status_code 头部状态码。默认0，不发送
 *
 * @return void 无返回值
 */
function redirect($url, $time = 0, $msg = '', $status_code = 0) {
    $status_code && send_http_status($status_code);//支持发送头部状态码 by mrmsl on 2012-07-02 09:45:53

    $url = str_replace(array("\n", "\r"), '', $url);//多行URL地址支持
    $msg = $msg ? $msg : "系统将在{$time}秒之后自动跳转到{$url}！";

    if (!headers_sent()) {

        if (0 === $time) {
            header('Location: ' . $url);
        }
        else {
            header("refresh:{$time};url={$url}");
            echo $msg;
        }

        exit();
    }
    else {
        $str  = "<meta http-equiv='refresh' content='{$time};URL={$url}'>";
        $str .= $time ? $msg : '';

        exit($str);
    }
} //end redirect

/**
 * 优化的require_once
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:00:48 by mrmsl
 *
 * @param string $filename 包含文件
 *
 * @return bool true成功包含，否则false
 */
function require_cache($filename) {
    static $_import_files = array();

    if (!isset($_import_files[$filename])) {

        if (is_file($filename)) {
            require($filename);
            $_import_files[$filename] = true;
        }
        else {
            $_import_files[$filename] = false;
        }
    }

    return $_import_files[$filename];
}

/**
 * 全局缓存设置和读取
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:01:11 by mrmsl
 *
 * @param string $name    缓存名称
 * @param mixed  $value   缓存内容。默认''
 * @param mixed  $expire  过期时间。默认null
 * @param string $type    头部状态码。默认''
 * @param mixed  $options 缓存参数。默认null
 *
 * @return mixed
 */
function S($name, $value = '', $expire = null, $type = '', $options = null) {
    static $_cache = array();

    $cache_key = $type . '_' . $name;

    $cache = Cache::getInstance($type, $options); //取得缓存对象实例

    if ('' !== $value) {

        if (is_null($value)) { //删除缓存

            $result = $cache->rm($name);

            if ($result) {
                unset($_cache[$cache_key]);
            }

            return $result;
        }
        else { //缓存数据
            $cache->set($name, $value, $expire);
            $_cache[$cache_key] = $value;
        }

        return;
    }
    elseif (isset($_cache[$cache_key])) {
        return $_cache[$cache_key];
    }

    //获取缓存数据
    $value = $cache->get($name);
    $_cache[$cache_key] = $value;

    return $value;
}//end S


/**
 * 发送http头部状态码信息
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:01:58 by mrmsl
 *
 * @param int    $code 状态码
 * @param string $msg  自定义状态信息。默认''
 *
 * @return void 无返回值
 */
function send_http_status($code, $msg = '') {
    $_status = array(
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        //Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',//1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        //306 is deprecated but reserved
        307 => 'Temporary Redirect',

        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    if (isset($_status[$code])) {
        $msg = $msg ? $msg : $_status[$code];
        $v   = $code . ' ' . $msg;

        header('HTTP/1.1 ' . $v);
    }
}//end send_http_status

/**
 * session管理函数
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:02:11 by mrmsl
 *
 * @param mixed $name  session名称或session初始配置项
 * @param mixed $value session值。默认''
 *
 * @return mixed
 */
function session($name, $value = '') {
    $prefix = SESSION_PREFIX;//session前缀

    if (is_array($name)) {//session初始化 在session_start 之前调用

        if (!empty($name['use_trans_sid']) && !empty($_REQUEST[C($v = 'VAR_SESSION_ID')])) {
            session_id($_REQUEST[$v]);
        }
        elseif (!empty($name['id'])) {
            session_id($name['id']);
        }

        ini_set('session.auto_start', 0);
        !empty($name['name']) && session_name($name['name']);
        !empty($name['gc_maxlifetime']) && ini_set('session.gc_maxlifetime', $name['gc_maxlifetime']);
        isset($name['use_trans_sid']) && ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        isset($name['use_cookies']) && ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        isset($name['use_only_cookies']) && ini_set('session.use_only_cookies', $name['use_only_cookies'] ? 1 : 0);
        isset($name['cookie_lifetime']) && ini_set('session.cookie_lifetime', $name['cookie_lifetime']);
        !empty($name['cookie_path']) && ini_set('session.cookie_path', $name['cookie_path']);
        !empty($name['cookie_domain']) && ini_set('session.cookie_domain', $name['cookie_domain']);
        isset($name['cookie_secure']) && ini_set('session.cookie_secure', $name['cookie_secure'] ? 1 : 0);
        isset($name['cookie_httponly']) && ini_set('session.cookie_httponly', $name['cookie_httponly'] ? 1 : 0);
        $save_handler = empty($name['save_handler']) ? 'files' : $name['save_handler'];

        if ('files' == $save_handler) { //文件

            if (!empty($name['save_path'])) {
                session_save_path(0 === strpos($name['save_path'], DS) ? SESSION_PATH . substr($name['save_path'], 1) : $name['save_path']);
            }

            ini_set('session.save_handler', 'files');
        }
        else { //session驱动
            $class = 'Session' . ucwords(strtolower($save_handler));

            if (require_cache(EXTEND_PATH . 'Driver/Session/' . $class . '.class.php')) { //加载驱动类
                $handler = new $class();
                method_exists($handler, 'execute') && $handler->execute();
            }
            else { //类没有定义
                throw new Exception(L('_CLASS_NOT_EXIST_') . ': ' . $class);
            }
        }

        session_start(); //启动session
    } //end if(is_array($name))
    elseif ('' === $value) {

        if (0 === strpos($name, '[')) { //session 操作
            if ('[pause]' == $name) { //暂停session
                session_write_close();
            }
            elseif ('[start]' == $name) { //启动session
                session_start();
            }
            elseif ('[destroy]' == $name) { //销毁session
                $_SESSION = array();
                session_destroy();
            }
            elseif ('[regenerate]' == $name) {//重新生成id
                session_regenerate_id();
            }
        }
        elseif (0 === strpos($name, '?')) {//检查session
            $name = substr($name, 1);

            return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
        }
        elseif (is_null($name)) {//清空session

            if ($prefix) {
                unset($_SESSION[$prefix]);
            }
            else {
                $_SESSION = array();
            }
        }
        elseif ($prefix) {//获取session
            return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
        }
        else {
            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }
    }//end if ('' === $value)
    elseif (is_null($value)) { //删除session

        if ($prefix) {
            unset($_SESSION[$prefix][$name]);
        }
        else {
            unset($_SESSION[$name]);
        }
    }
    else {//设置session
        if ($prefix) {

            if (!isset($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }

            $_SESSION[$prefix][$name] = $value;
        }
        else {
            $_SESSION[$name] = $value;
        }
    }
}//end session

/**
 * 根据PHP各种类型变量生成唯一标识号
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:05:16 by mrmsl
 *
 * @param string $mix 标识号
 *
 * @return 标识号
 */
function to_guid_string($mix) {
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    }
    elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    }
    else {
        $mix = serialize($mix);
    }

    return md5($mix);
}

//以下函数不是核心文件函数 by mrmsl on 2012-06-11 08:59:06

/**
 * 批量unset
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-12 11:07:35
 *
 * @param array $data 数据
 * @param mixed $keys key值
 *
 * @return void 无返回值
 */
function _unset(&$data, $keys) {
    $keys = is_array($keys) ? $keys : explode(',', $keys);

    foreach ($keys as $key) {
        unset($data[trim($key)]);
    }
}

/**
 * 转义字符串，支持数组
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:06:54 by mrmsl
 *
 * @param mixed $value 待转义字符串或数组
 *
 * @return mixed 转义后字符串
 */
function addslashes_deep($value) {
    return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
}

/**
 * 写php数组至js文件
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-04 17:12:59
 * @lastmodify      2013-01-22 17:08:48 by mrmsl
 *
 * @param array    $data     数组
 * @param string   $varname  js变量名
 * @param filename $filename 写入文件名
 *
 * @return void 无返回值
 */
function array2js($data, $varname, $filename) {
    $data = json_encode($data);
    file_put_contents($filename, sprintf(AUTO_CREATE_COMMENT, new_date()) . "var {$varname} = {$data};");
}

/**
 * 自动加载类库
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-16 16:21:50
 *
 * @param string $class 类名
 *
 * @return bool true
 */
function autoload($class) {
    $autoload = C('AUTOLOAD');

    if (isset($autoload[$class])) {
        return require_cache($autoload[$class]);
    }

    $filename = $class . PHP_EXT;

    if (is_file($file = LIB_PATH . $filename)) {//lib目录下自动加载
        return require_cache($file);
    }
    elseif (strpos($class, 'Controller') || ($b = strpos($class, 'Model'))) {//控制器或模型
        return require_cache(APP_PATH . (isset($b) ? 'models' : 'controllers') . DS . $filename);//当前模块控制器或模型
    }

    return false;
}

/**
 * 验证验证码是否正确
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-13 16:45:47
 * @lastmodify      2013-01-22 17:08:59 by mrmsl
 *
 * @param string $code   验证码
 * @param string $module 验证码模块
 *
 * @return mixed true正确，否则返回提示信息
 */
function check_verifycode($code, $module) {

    static $_checked = array();

    if (isset($_checked[$module])) {//已经验证过
        return $_checked[$module];
    }
    elseif (!check_verifycode_limit($module)) {//错误次数限制
        $result = false;
    }
    else {
        $session            = session(SESSION_VERIFY_CODE);
        $verifycode_setting = get_verifycode_setting($module);
        $verifycode_order   = $verifycode_setting['order'];

        if (!$verifycode_setting['case']) {//不区分大小写
            $code    = strtolower($code);
            $session = strtolower($session);
        }

        if (!is_numeric($verifycode_order)) {//非数字，$verifycode_order即为验证码
            $result = $code == $verifycode_order;
        }
        elseif (!$verifycode_order) {//验证码顺序
            $result = $code == $session;
        }
        elseif (($len = strlen($verifycode_order)) != strlen($code)) {//输入长度与顺序长度一致，避免abcd => abcde也通过
            $result = false;
        }
        else {

            for($i = 0; $i < $len; $i++) {
                $n      = $verifycode_order{$i} - 1;
                $result = isset($code{$i}) && isset($session{$n}) && $code{$i} == $session{$n};
            }
        }
    }

    $_checked[$module] = $result;

    return $result;
}//end check_verifycode

/**
 * 验证验证码限制，包括刷新次数及错误次数
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-29 09:16:24
 * @lastmodify      2013-01-22 17:09:30 by mrmsl
 *
 * @param string $module 验证码模块
 * @param string $type   限制类型。默认error，错误
 *
 * @return bool true通过验证，否则false
 */
function check_verifycode_limit($module, $type = 'error') {
    $verifycode_setting = get_verifycode_setting($module);
    $key                = $module . '_verifycode_';
    $temp               = $type . '_limit';

    if ($verifycode_setting[$temp]) {//次数限制

        list($num, $lock_time) = explode('/', $verifycode_setting[$temp]);

        if ($num) {//次数
            $session = session($key . $temp);
            $new_num = null === $session ? 0 : $session  + 1;

            //超出次数限制
            if ($new_num > $num) {
                !session($key . $temp . '_time') && session($key . $temp . '_time', APP_NOW_TIME + $lock_time);

                if (session($key . $temp . '_time') > APP_NOW_TIME) {
                    session($key . $temp, $new_num);

                    if ($new_num - $num < 11) {
                        $log = D('Log');
                        $log && $log->addLog(L('VERIFY_CODE') . "({$module})" . L(($type == 'error' ? 'ERROR' : 'REFRESH') . ",CN_CISHU,%({$new_num}),EXCEED,LIMIT") . $num, LOG_TYPE_VERIFYCODE_ERROR);
                    }

                    return false;
                }
                else {
                    session($key . $temp, 0);
                    session($key . $temp . '_time', 0);
                }
            }
            else {
                session($key . $temp, $new_num);
            }
        }
    }

    return true;
}//end check_verifycode_limit

/**
 * 清空验证码session
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-28 17:04:50
 * @lastmodify      2013-01-22 17:10:42 by mrmsl
 *
 * @param string $module 验证码模块
 *
 * @return void 无返回值
 */
function clear_verifycoe($module) {
    session(SESSION_VERIFY_CODE, null);//清空验证码
    session($module . '_verifycode_refresh_limit', null);//刷新次数限制
    session($module . '_verifycode_refresh_limit_time', null);
    session($module . '_verifycode_error_limit', null);//错误次数限制
    session($module . '_verifycode_error_limit_time', null);
}

/**
 * 截取字符串,适用中文,摘自网络,discuz uchome
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-09 15:06:16
 *
 * @param string $string   待截取字符
 * @param int    $length   截取字符数
 * @param string $dot      省略字符,默认...
 * @param string $encoding 编码,默认utf-8
 *
 * @return string 截取后的字符串
 */
function cn_substr($string, $length, $dot = '...', $encoding = 'utf-8') {
    $string         = trim($string);
    $string         = htmlspecialchars_decode(strip_tags($string), ENT_QUOTES);
    $string         = str_replace('&nbsp;', ' ', $string);
    $len            = strlen($string);
    $include_dot    = false;

    if ($len > $length) {
        $include_dot    = true;
        $word_cut       = '';

        if ('utf-8' == strtolower($encoding)) { //utf8编码
            $n      = 0;
            $tn     = 0;
            $noc    = 0;

            while ($n < $len) {
                $t = ord($string[$n]);

                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                }
                elseif (194 <= $t && $t <= 223) {
                    $tn     = 2;
                    $n     += 2;
                    $noc   += 2;
                }
                elseif (224 <= $t && $t < 239) {
                    $tn     = 3;
                    $n     += 3;
                    $noc   += 2;
                }
                elseif (240 <= $t && $t <= 247) {
                    $tn     = 4;
                    $n     += 4;
                    $noc   += 2;
                }
                elseif (248 <= $t && $t <= 251) {
                    $tn     = 5;
                    $n     += 5;
                    $noc   += 2;
                }
                elseif ($t == 252 || $t == 253) {
                    $tn     = 6;
                    $n     += 6;
                    $noc   += 2;
                }
                else {
                    $n++;
                }

                if ($noc >= $length) {
                    break;
                }

            }

            if ($noc > $length) {
                $n -= $tn;
            }

            $word_cut = substr($string, 0, $n);
        }
        else {

            for ($i = 0; $i < $length - 1; $i++) {

                if (ord($string[$i]) > 127) {
                    $word_cut .= $string[$i] . $string[$i + 1];
                    $i++;
                }
                else {
                    $word_cut .= $string[$i];
                }
            }
        }

        $string = $word_cut;
    }

    $string = htmlspecialchars($string, ENT_QUOTES);
    $string = str_replace(' ', '&nbsp;', $string);

    return $string . ($include_dot ? $dot : '');
}

/**
 * 获取php文件内容，并去掉注释及空白
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-02-18 17:02:16
 *
 * @return string 去掉注释及空白后php代码
 */
function compile_file($filename) {
    $content = substr(php_strip_whitespace($filename), 7);

    if (strpos($content, '?>') && '?>' == substr($content = rtrim($content), -2)) {//php关闭标签
        $content = substr($content, 0, -2);
    }

    return  PHP_EOL . PHP_EOL . '//' . $filename . PHP_EOL . $content;
}

/**
 * 包含css
 *
 * @param mixed  $files    css文件
 * @param string $base_url 基路径。默认''，取IMGCACHE_CSS
 *
 * @return string css文件链接
 */
function css($files, $base_url = '') {

    if ($base_url) {
        $base_url = 0 === strpos($base_url, '/') ? to_website_url($base_url) : $base_url;
    }
    else {
        $base_url = IMGCACHE_CSS;
    }

    $str       = '';
    $files     = is_array($files) ? $files : explode(',', $files);

    foreach ($files as $css) {
        $str .= PHP_EOL . '<link rel="stylesheet" type="text/css" href="' . $base_url . trim($css) . '" />';
    }

    return $str . PHP_EOL;
}

/**
 * 取字符串前N位作为目录
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-17 09:08:23
 *
 * @param string $str  待取字符串
 * @param string $base 基路径。默认WWWROOT，网站根目录
 * @param int    $n    前N位
 *
 * @return string 路径
 */
function get_substr_dir($str, $base = WWWROOT, $n = 4) {
    $dir  = $base;

    if ('/' != substr($dir, -1)) {
        $dir .= '/';
    }

    $dir .= strlen($str) > $n ? substr($str, 0, $n) : $str;
    $dir .= '/';

    new_mkdir($dir);

    return $dir;
}

/**
 * 日期函数
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-22 17:30:11
 * @lastmodify      2013-01-22 17:30:11 by mrmsl
 *
 * @param string $format 日期格式。默认null，取sys_config('sys_timezone_datetime_format')
 * @param int    $time   unix时间戳。默认null，取time
 *
 * @return string 格式化后的日期
 */
function new_date($format = null, $time = null) {
    $time   = null === $time ? time() : $time;
    $format = null === $format ? sys_config('sys_timezone_datetime_format') : $format;

    return date($format, $time);
}

/**
 * 循环创建目录
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-17 09:03:59
 *
 * @param string $path 路径
 * @param int    $mode 权限。默认0755
 *
 * @throw 创建目录失败,抛出异常
 *
 * @return bool true创建成功，否则false
 */
function new_mkdir($path, $mode = 0755) {

    if (!is_dir($path) && !mkdir($path, $mode, true)) {
        throw new Exception(L('CN_CHUANGJIAN,DIRECTORY') . ': ' . $path . L('FAILURE'));
    }

    return true;
}

/**
 * 文件存在时再删除文件
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-07 10:24:41
 *
 * @param string $filename 待删除文件
 *
 * @return bool true删除成功，否则false
 */
function new_unlink($filename) {
    return is_file($filename) && unlink($filename);
}

/**
 * mb_substr截取字符串
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-10 11:46:01
 *
 * @param string $string   待截取字符
 * @param int    $length   截取字符数
 * @param int    $start    开始位置.默认0
 * @param string $dot      省略字符,默认...
 * @param string $encoding 编码,默认utf-8
 *
 * @return string 截取后的字符串
 */
function new_mb_substr($string, $length, $start = 0, $dot = '...', $encoding = 'utf-8') {
    $string = strip_tags($string);

    if ($length >= mb_strlen($string, $encoding)) {
        return $string;
    }

    $string = htmlspecialchars_decode($string, ENT_QUOTES);
    $string = str_replace('&nbsp;', ' ', $string);
    $string = mb_substr($string, $start, $length, $encoding);
    $string = htmlspecialchars($string, ENT_QUOTES);
    $string = str_replace(' ', '&nbsp;', $string);

    return $string . $dot;
}

/**
 * 自定义错误处理
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-12 13:31:07
 * @lastmodify      2013-01-22 17:12:01 by mrmsl
 *
 * @param int    $errno   错误号
 * @param string $errstr  错误信息
 * @param string $errfile 错误文件
 * @param int    $errline 错误文件行号
 * @param mixed  $vars    用户变量。默认''
 *
 * @return void 无返回值
 */
function error_handler($errno, $errstr, $errfile, $errline, $vars = '') {

    if (0 === error_reporting()) {//@抑制错误
        return false;
    }

    $log_filename = C('LOG_FILENAME');
    C('LOG_FILENAME', false);

    if ($log_level = C('LOG_LEVEL')) {//通过trigger_error触发
        C('LOG_LEVEL', false);

        if (strpos(sys_config('sys_log_level'), $log_level) === false) {
            return;
        }

        $errno = $log_level;
    }

    $quit_arr = array(
        E_ERROR              => 'PHP Error',
        E_PARSE              => 'PHP Parsing Error',
        E_CORE_ERROR         => 'PHP Core Error',
        E_CORE_WARNING       => 'PHP Core Warning',
        E_COMPILE_ERROR      => 'PHP Compile Error',
        E_RECOVERABLE_ERROR  => 'PHP Catchable Fatal Error',
        E_APP_EXCEPTION      => 'PHP Uncaught Exception',
    );

    $error_arr = $quit_arr + array(
        E_NOTICE             => 'PHP Notice',
        E_WARNING            => 'PHP Warning',
        E_COMPILE_WARNING    => 'PHP Compile Warning',
        E_STRICT             => 'PHP Strict standards',

        E_USER_ERROR         => 'User Error',
        E_USER_WARNING       => 'User Warning',
        E_USER_NOTICE        => 'User Notice',

        E_APP_DEBUG          => 'User Debug',
        E_APP_INFO           => 'User Info',
        E_APP_SQL            => 'User SQL',
        E_APP_ROLLBACK_SQL   => 'User ROLLBACK SQL',
    );

    defined('E_USER_DEPRECATED') ? $error_arr[E_USER_DEPRECATED] = 'PHP E_USER_DEPRECATED' : '';
    defined('E_DEPRECATED') ? $error_arr[E_DEPRECATED] = 'PHP E_DEPRECATED' : '';

    $user_errors = array(E_USER_WARNING, E_USER_NOTICE, E_USER_ERROR);

    list($usec, $sec) = explode(' ', microtime());

    $error       = '[' . new_date(null, time(false)) . substr($usec, 1, 5) . '] [Client: ' . get_client_ip() . ']';
    $error      .= defined('REQUEST_METHOD' ? REQUEST_METHOD : $_SERVER['REQUEST_METHOD']) . ' ';
    $error      .= defined('REQUEST_URI') ? REQUEST_URI : (empty($_SERVER['REQUEST_URI']) ? '': urldecode($_SERVER['REQUEST_URI']));
    $error      .= PHP_EOL;
    $error      .= "{$error_arr[$errno]}[$errno]: ";
    $error      .= "{$errstr} in {$errfile} on line {$errline} " . PHP_EOL;

    //if (in_array($errno, $user_errors)) {
        //$error .= PHP_EOL . var_export($vars, true);
    //}

    Logger::record($error, $log_filename);

    if (isset($quit_arr[$errno])) {
        Logger::save();
        $trace = $vars && is_string($vars) && 0 === strpos($vars, '__') ? substr($vars, 2) : '';
        halt($errstr, $errfile, $errline, $trace);
    }
}//end error_handler

/**
 * 自定义异常处理
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-12 13:30:32
 * @lastmodify      2013-01-22 17:13:09 by mrmsl
 *
 * @param object $e 异常
 *
 * @return void 无返回值
 */
function exception_handler($e) {
    $message = $e->__toString();

    //入库
    if (sys_config('sys_log_systemerror')) {
        $log = D('Log');
        $log && $log->addLog(nl2br($message), LOG_TYPE_SYSTEM_ERROR);

    }
    error_handler(E_APP_EXCEPTION, $e->getMessage(), $e->getFile(), $e->getLine(), '__' . $e->getTraceAsString());
}

/**
 *
 * register_shutdown_function脚本终止前回调函数
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-12 13:25:24
 * @lastmodify      2013-01-22 17:15:52 by mrmsl
 *
 * @return void 无返回值
 */
function fatal_error() {
    Logger::save();
    //session_write_close();//必须

    if ($e = error_get_last()) {
        error_handler($e['type'], $e['message'], $e['file'], $e['line']);
    }
}

/**
 * 格式化字节大小
 *
 * @param int $filesize  文件大小，单位：字节
 * @param int $precision 小数点数。默认2
 *
 * @return string 带单位的文件大小
 */
function format_size($filesize, $precision = 2) {
    if ($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100;
        $unit     = 'GB';
    }
    elseif ($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 ;
        $unit     = 'MB';
    }
    elseif($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100;
        $unit     = 'KB';
    }
    else {
        $filesize = $filesize;
        $unit     = 'Bytes';
    }

    return sprintf('%.' . $precision . 'f', $filesize) . ' ' . $unit;;
}

/**
 * 获取验证码设置值
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-28 09:38:21
 * @lastmodify      2013-01-22 17:16:27 by mrmsl
 *
 * @param string $module 模块
 * @param string $index  具体键值。默认''
 *
 * @return mixed 如果$index不为空，返回该健值，否则返回验证码设置值数组
 */
function get_verifycode_setting($module, $index = '') {
    static $data = array();

    if (isset($data[$module])) {
        return $index ? $data[$module][$index] : $data[$module];
    }
    else {
        $data[$module] = array();
    }

    $key_arr = array(//验证码设置健=>取默认值
        'enable'         => -1,//是否开启
        'width'          => 0,//宽
        'height'         => 0,//高
        'length'         => 0,//字母长
        'order'          => -1,//顺序
        'refresh_limit'  => '',//刷新次数限制
        'error_limit'    => '',//错误次数限制
        'case'           => -1,//区分大小写
        'type'           => -1,//类型
    );

    $filename = 'sys' == $module ? 'System' : 'Module';
    $key      = $module . '_verifycode_';

    if('sys' == $module) {//用空间换时间
        foreach ($key_arr as $k => $v) {
            $data[$module][$k] = sys_config($key . $k, $filename);
        }
    }
    else {
        $default = get_verifycode_setting('sys');

        foreach ($key_arr as $k => $v) {
            $_v = sys_config($key . $k, $filename);

            if ('order' == $k && 'module_admin' == $module && -1 !== intval(C('T_VERIFYCODE_ORDER'))) {//管理员后台登陆验证码顺序
                $_v = C('T_VERIFYCODE_ORDER');
            }

            $data[$module][$k] = $v == $_v ? $default[$k] : $_v;
        }
    }

    return $index ? $data[$module][$index] : $data[$module];
}//end get_verifycode_setting

/**
 * 获取浏览器信息
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-27 16:19:37
 * @lastmodify      2013-01-22 17:17:11 by mrmsl
 *
 * @return string 浏览器信息
 */
function get_browser_name () {
    static $_browser_name = null;

    if (null === $_browser_name) {

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $_browser_name = 'Empty';
        }
        else {

            if (strpos($v = $_SERVER['HTTP_USER_AGENT'], 'MSIE ')) {//ie

                if (preg_match('#(MSIE) (\d+)#', $v, $match)) {
                    $match[1] = 'IE';
                }
            }
            elseif (0 === strpos($v, 'Opera/')){//opera
                preg_match('#(Opera)/.+Version/(\d+)#', $v, $match);
            }
            elseif (strpos($v, 'Firefox/')) {//firefox
                preg_match('#(Firefox)/(\d+)#', $v, $match);
            }
            elseif (strpos($v, 'Chrome/')) {//chrome
                preg_match('#(Chrome)/(\d+)#', $v, $match);
            }
            elseif (strpos($v, 'Safari/')){//safari
                if (preg_match('#Version/(\d+).+(Safari)/#', $v, $match)) {
                    $match = array(1 => $match[2], 2 => $match[1]);
                }
            }

            $_browser_name = empty($match) ? 'Unknow' : $match[1] . ' ' . $match[2];
        }
    }

    return $_browser_name;
}//end get_browser_name

/**
 * 去淘宝数据库获取ip地址信息，如广东省深圳市
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-21 17:27:37
 *
 * @param string $ip ip地址,默认null,自动获取
 *
 * @return array|string 成功获取，返回array(province, city)，否则返回字符串作为城市
 */
function get_ip_info($ip = null) {

    if (null === $ip) {
        $ip = get_client_ip();//'14.153.254.58'
    }
    else {
        $ip = is_numeric($ip) ? long2ip($ip) : $ip;
    }

    if (!$ip2long = intval($ip)) {//无法获取到ip
        return '';
    }
    elseif ('127.0.0.1' == $ip) {//本机电脑
        return L('SELF_COMPUTER');
    }
    elseif (0 === strpos($ip, '192.168.')) {//局域网
        return L('LOCAL_AREA_NETWORK');
    }

    $opt = array (
        'http'  => array (
            'method'    => 'GET',
            'timeout'   => 1,
        )
    );

    $context = stream_context_create($opt);

    $data = file_get_contents(TAOBAO_IP_API . $ip, false, $context);

    if (!$data) {//获取失败
        C('LOG_FILENAME', CONTROLLER_NAME);
        trigger_error(L('GET,PROVINCE,CITY,FAILURE'));
        return '';
    }

    $data = json_decode($data, true);

    if (!$data || $data['code']) {
        C('LOG_FILENAME', CONTROLLER_NAME);
        trigger_error(L('GET,PROVINCE,CITY,FAILURE'));
        return '';
    }

    $data = $data['data'];

    return array($data['region'], $data['city']);
}//end get_ip_info

/**
 * 获取用户id
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-18 16:29:58
 * @lastmodify      2013-01-22 17:17:26 by mrmsl
 *
 * @return int 用户id
 */
function get_user_id() {
    $user_info = session('user_info');

    return $user_info ? $user_info['user_id'] : 0;
}

/**
 * 错误处理
 *
 * @author          liu21st <liu21st@gmail.com>
 * @lastmodify      2013-01-22 17:18:53 by mrmsl
 *
 * @param mixed  $errstr  错误信息
 * @param string $errfile 错误文件。默认''
 * @param int    $errline 错误文件行号。默认''
 * @param mixed  $trace   跟踪轨迹。默认''
 *
 * @return void 无返回值
 */
function halt($errstr, $errfile = '', $errline = '', $trace = '') {

    if (APP_DEBUG) {//调试模式下输出错误信息

        if ($errfile) {
            $e = array(
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
                'trace'   => $trace,
            );
        }
        elseif ($errstr instanceof Exception) {
            $e = array(
                'message' => $errstr->getMessage(),
                'file'    => $errstr->getLine(),
                'line'    => $errstr->getLine(),
                'trace'   => $trace->getTraceAsString(),
            );
        }
        elseif (is_string($errstr)) {
            $trace        = debug_backtrace();
            $shift        = $trace[0];
            $e['message'] = $errstr;
            $e['file']    = $shift['file'];
            $e['line']    = $shift['line'];
        }
        else {
            $e = $errstr;
        }

        if (empty($e['trace'])) {
            $trace        = empty($trace) ? debug_backtrace() : $trace;
            $trace_info   = '';

            foreach ($trace as $t) {
                $trace_info .= '#';
                $trace_info .=  isset($t['file']) ? $t['file'] . ' (' . $t['line'] . ') ' : '';
                $trace_info .= isset($t['class']) ? $t['class'] . $t['type'] : '';
                $trace_info .= $t['function'] . '(';
                $trace_info .= empty($t['args']) ? '' : substr(stripslashes(str_replace(PHP_EOL, ' ', print_r($t['args'], true))), 0, 200);
                $trace_info .= ')' . PHP_EOL;
            }

            $e['trace'] = $trace_info;
        }
echo $e['message'] . ' in file ' . $e['file'] . ' on line ' . $e['line'], '<br /';
return;
        include C('TMPL_EXCEPTION_FILE');//包含异常页面模板
    }
    else {//否则定向到错误页面

        $error_page = C('ERROR_PAGE');

        if (!empty($error_page)) {
            redirect($error_page);
        }
        else {

            if (C('SHOW_ERROR_MSG')) {
                $e['message'] = is_array($error) ? $error['message'] : $error;
            }
            else {
                $e['message'] = C('ERROR_MESSAGE');
            }

            include C('TMPL_EXCEPTION_FILE');//包含异常页面模板
        }
    }
    //(C('LOG_RECORD') || C('SQL_LOG_SYSTEM_ERROR')) && trigger_error(stripslashes(var_export($e, true)), E_USER_ERROR);
    exit();
}//end halt

/**
 * 将单引号'，双引号"转化为对应html实体
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:23:47 by mrmsl
 *
 * @param string $string 待转化字符串
 *
 * @return mixed 转化后的字符串或数组
 */
function htmlquotes($string) {
    return is_array($string) ? array_map('htmlquotes', $string) : str_replace("'", '&#039;', str_replace('"', '&quot;', $string));
}

/**
 * 包含js
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:23:39 by mrmsl
 *
 * @param mixed  $files         js文件
 * @param bool   $include_ext   true包含ext。默认true
 * @param string $base_url      基路径，为script时返回javascript片段。默认''，取IMGCACHE_JS
 *
 * @return string js文件链接
 */
function js($files, $include_ext = true, $base_url = '') {

    if ('script' === $include_ext) {//js代码 by mrmsl on 2012-09-06 16:51:57
        return '<script>' . $files . '</script>' . PHP_EOL;
    }

    if ($base_url) {
        $base_url = 0 === strpos($base_url, '/') ? to_website_url($base_url) : $base_url;
    }
    else {
        $base_url = IMGCACHE_JS;
    }

    $str       = '';
    $files     = is_array($files) ? $files : explode(',', $files);
    $include_ext && array_unshift($files, 'ext-all' . (IS_LOCAL ? '-debug' : '.min') . '.js');

    foreach ($files as $js) {
        $str .= $js ? PHP_EOL . '<script src="' . $base_url . trim($js) . '"></script>' : '';
    }

    return $str . PHP_EOL;
}

/**
 * 返回整形数组或字符串，POST主键时，一般不会过滤，直接IN($id)或=$id，此时，人为的修改: 1) OR (1=1，
 * 本来是UPDATE ... WHERE IN(1)，此刻为 IN(1) OR (1=1)永为真
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:23:32 by mrmsl
 *
 * @param mixed $string       待转换字符串或数组
 * @param bool  $return_array true返回数组。默认false，返回字符串
 * @param mixed $exclude      排除值。默认0
 *
 * @return mixed 如果$return_array为true，返回整数数组，否则返回用半角逗号隔开的字符串
 */
function map_int($string, $return_array = false, $exclude = 0) {
    $array = is_array($string) ? $string : explode(',', $string);
    $array = array_map('intval', $array);

    if (false !== $exclude) {
        $exclude = is_array($exclude) ? $exclude : explode(',', $exclude);

        foreach($array as $k => $v) {

            if (in_array($v, $exclude)) {
                unset($array[$k]);
            }
        }
    }

    return $return_array ? $array : join(',', $array);
}

/**
 * 产生随机字符串，可用来自动生成密码 默认长度6位 字母和数字混合
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-10 15:33:40
 * @lastmodify      2013-01-22 17:22:56 by mrmsl
 *
 * @param ing    $len        长度。默认4
 * @param int    $type       模式。默认VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND，去掉了容易混淆的字符oOLl和数字01
 * @param string $add_chars  额外字符。默认空
 *
 * @return string 随机字符串
 */
function rand_string($len = 4, $mode = VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND, $add_chars = '') {
    $str = '';

    switch ($mode) {
        case VERIFY_CODE_TYPE_LETTERS://大小写字母a-zA-Z
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $add_chars;
            break;

        case VERIFY_CODE_TYPE_LETTERS_UPPER://大写字母A-Z
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $add_chars;
            break;

        case VERIFY_CODE_TYPE_LETTERS_LOWER://小写字母a-z
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $add_chars;
            break;

        case VERIFY_CODE_TYPE_NUMERIC://数字0-9
            $chars = str_repeat('0123456789', 3);
            break;

        case VERIFY_CODE_TYPE_ALPHANUMERIC://字母与数字a-zA-z0-9
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;

        default://默认去掉了容易混淆的字符oOLl和数字01，要添加请使用add_chars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $add_chars;
            break;
    }

    if ($len > 10) { //位数过长重复字符串一定次数
        $chars = 2 == $mode ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }

    $chars = str_shuffle($chars);
    $str   = substr($chars, 0, $len);

    return $str;
}//end rand_string

/**
 * 反转义字符串
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:23:25 by mrmsl
 *
 * @param mixed $value 待反转义字符串或数组
 */
function stripslashes_deep($value) {
    return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
}

/**
 *
 * 字符串加密、解密函数，摘自{@link http://www.phpcms.cn phpcms}
 *
 * @lastmodify 2013-01-22 17:24:08 by mrmsl
 *
 * @param string $str       字符串
 * @param string $encode    是否为加密,默认true,是
 * @param string $key       密钥
 *
 * @return string 经加密或解密后字符串
 */
function sys_auth($str, $encode = true, $key = '') {
    $key = $key ? $key : sys_config('sys_security_auth_key');
    $str = $encode ? $str : base64_decode($str);

    if ($key) {//密钥为空
        $len = strlen($key);
        $code = '';

        for ($i = 0, $n = strlen($str); $i < $n; $i++) {
            $k = $i % $len;
            $code .= $str[$i] ^ $key[$k];
        }
    }
    else {
        $code = $str;
    }

    return $encode ? base64_encode($code) : $code;
}

/**
 * 获取缓存指定字段值
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-03 10:03:16
 * @lastmodify      2013-01-22 17:24:38 by mrmsl
 *
 * @param string $key        获取字段。默认''
 * @param string $cache_name 缓存文件名。默认''，取System
 * @param string $default    缓存字段不存在默认值。默认''
 * @param string $cache_name 缓存路径。默认null=MODULE_CACHE_PATH
 *
 * @return mixed 如果未传字段，返回正在缓存;如果字段存在，返回该字段值，否则返回空字符串
 */
function sys_config($key = '', $cache_name = '', $default = '', $cache_path = null) {
    $cache_name = $cache_name ? $cache_name : 'System';
    $data       = F($cache_name, '', $cache_path ? $cache_path : MODULE_CACHE_PATH);

    if ('' === $key) {
        return $data;
    }

    return isset($data[$key]) ? $data[$key] : $default;
}

/**
 * 编译包含模板
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-05 18:33:48
 *
 * @param string $controller 控制器
 * @param string $action     操作方法
 *
 * @return string 编译后文件路径
 */
function template($controller, $action) {
    $template = Template::getInstance();

    return $template->compile($controller, $action);
}

/**
 * 获取网站绝对url地址
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-04 16:27:02
 * @lastmodify      2013-02-01 11:02:42 by mrmsl
 *
 * @param string $path 地址
 *
 * @return string 网站绝对url地址
 */
function to_website_url($path) {
    $path = 0 === strpos($path, DS) ? substr($path, 1) : $path;

    return BASE_SITE_URL . $path;
}

/**
 * 切换排序
 *
 * @author          mrmsl <msl-138@163.com>
 * @lastmodify      2013-01-22 17:26:06 by mrmsl
 *
 * @param string $order 排序
 */
function toggle_order($order = 'DESC') {
    return  'DESC' == strtoupper($order) ? 'DESC' : 'ASC';
}

/**
 * 检测目录是否存在
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-06 14:23:23
 * @lastmodify      2013-01-31 14:23:05 by mrmsl
 *
 * @param string $path             路径
 * @param string $name             路径提示名称。默认''
 * @param string $relative_path    相对路径。默认WWWROOT，网站根目录
 * @param bool   $must_end_with    true必须以'/'结尾。默认true
 * @param bool   $allow_start_with true允许以'/'开头。默认false
 * @param bool   $allow_dot        true允许../或./出现。默认false
 *
 * @return mixed true路径存在，否则返回相应提示信息
 */
function validate_dir($path, $name = '', $relative_path = 'WWWROOT', $must_end_with = true, $allow_start_with = false, $allow_dot = false) {

    if ('null' == $relative_path) {//只是对路径 / 判断

        if (DS == $path) {// /，直接返回true
            return true;
        }

        $relative_path = null;
    }
    else {
        $relative_path = defined($relative_path) ? constant($relative_path) : WWWROOT;
    }

    $name   = 0 === strpos($name, '{%') ? L(substr($name, 2, -1)) : $name;
    $path   = false === strpos($path, '\\') ? $path : str_replace('\\', DS, $path);

    if ($must_end_with && DS != substr($path, -1)) {
        return $name . sprintf(L('MUST,END_WITH'), DS);
    }

    if (!$allow_start_with && 0 === strpos($path, DS)) {
        return $name . sprintf(L('CAN_NOT,START_WITH'), DS);
    }

    if (!$allow_dot && false !== strpos($path, '.' . DS)) {
        //写错误日志 by mrmsl on 2012-09-06 14:56:03
        defined('TB_LOG') && D(CONTROLLER_NAME)->addLog(L('TRY,USE,RELATIVE,PATH') . $path, LOG_TYPE_INVALID_PARAM);

        return $name . L('CAN_NOT,USE,RELATIVE,PATH');
    }

    return null === $relative_path || is_dir($relative_path . $path) ? true :  $name . $path . L('NOT_EXIST');
}// end validate_dir