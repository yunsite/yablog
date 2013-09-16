<?php
/**
 * 函数库。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            functions.php
 * @package         Yab
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-22 16:47:21
 * @lastmodify      $Date$ $Author$
 */
/**
 * ajax方式返回数据到客户端
 *
 * @param mixed  $success 返回状态或返回数组
 * @param string $msg     提示信息
 * @param mixed  $data    要返回的数据
 * @param mixed  $total   总数
 *
 * @return void 无返回值
 */
function ajax_return($success = true, $msg = '', $data = null, $total = null) {

    if (is_array($success)) {
        $result = $success;
    }
    else {
        $result = array(
            'success' => $success,
            'msg'     => $msg,
            'data'    => $data,
        );

        if (null !== $total) {
            $result['total'] = $total;
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    $result = json_encode($result);
    echo $result;
    exit();
}//end ajax_return

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