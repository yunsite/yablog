<?php
/**
 * 参数验证及过滤类
 *
 * @file            Filter.class.php
 * @package         Yab\Filter
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-01-06 12:47:14
 * @lastmodify      $Date $Author$
 */

class Filter {
    /**
     * @var array $_filter_type 验证过滤类型，string、int、boolean、float、regexp、url、email
     */
    static private $_filter_type = array(
        'string'       => FILTER_SANITIZE_STRING,
        'int'          => FILTER_VALIDATE_INT,
        'boolean'      => FILTER_VALIDATE_BOOLEAN,
        'float'        => FILTER_VALIDATE_FLOAT,
        'regexp'       => FILTER_VALIDATE_REGEXP,
        'url'          => FILTER_VALIDATE_URL,
        'email'        => FILTER_VALIDATE_EMAIL,
        'ip'           => FILTER_VALIDATE_IP,
    );
    /**
     * @var array $_filter_type 外部变量类型，$_GET、$_POST、$_COOKIE、$_SERVER、$_ENV
     */
    static private $_input_type = array(
        'get'         => INPUT_GET,
        'post'        => INPUT_POST,
        'cookie'      => INPUT_COOKIE,
        'server'      => INPUT_SERVER,
        'env'         => INPUT_ENV
    );

    /**
     * 获取过滤类型
     *
     * @param string $filter_type 过滤类型key
     *
     * @return int|null 如果过滤类型存在，返回过滤类型，否则返回null
     */
    static private function _getFilterType($filter_type) {
        $filter_type = strtolower($filter_type);

        return isset(self::$_filter_type[$filter_type]) ? self::$_filter_type[$filter_type] : null;
    }

    /**
     * 获取外部变量验证类型
     *
     * @param string $input_type 变量类型
     *
     * @return int|null 如果变量类型存在，返回变量类型，否则返回null
     */
    static private function _getInputType($input_type) {
        $input_type = strtolower($input_type);

        return isset(self::$_input_type[$input_type]) ? self::$_input_type[$input_type] : null;
    }

    /**
     * 数组过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $filter_type         数组类型。默认int
     * @param string $default             如果过滤返回null时(不存在，过滤失败)时默认值。默认0
     *
     * @return array 过滤后数组
     */
    static public function _array($var_name, $type = 'post', $filter_type = 'int', $default = 0) {
        $array = self::filterInput($var_name, $filter_type, $type, array('options' => array('default' => $default), 'flags' => FILTER_REQUIRE_ARRAY|FILTER_NULL_ON_FAILURE));

        return null === $array ? array() : $array;
    }

    /**
     * 魔术方法，当调用方法不存在时，调用self::string方法
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-09-06 16:39:48
     * @lastmodify   2013-01-17 08:41:39 by mrmsl
     *
     * @param string $method 方法名
     * @param array  $args   参数
     *
     * @return string 过滤后字符串
     */
    static public function __callStatic($method, $args) {
        return call_user_func_array('self::string', $args);
    }

    /**
     * 布尔值过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认false
     *
     * @return string 过滤后的布尔值或默认值
     */
    static public function bool($var_name, $type = 'post', $default = false) {
        $string = self::filterInput($var_name, 'boolean', $type, array('options' => array('default' => $default)));

        return null === $string ? $default : $string;
    }

    /**
     * email过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认''
     *
     * @return string 过滤后的email或默认值
     */
    static public function email($var_name, $type = 'post', $default = '') {
        $string = self::filterInput($var_name, 'email', $type, array('options' => array('default' => $default)));

        return null === $string ? $default : $string;
    }

    /**
     * 提交变量过滤
     *
     * @param string $var_name    参数名
     * @param string $filter_type 过滤类型。默认string
     * @param string $input_type  请求方法，get|post。默认post
     * @param array  $options     过滤选项。默认array()
     *
     * @return mixed 过滤成功，返回过滤后的值，否则返回false或设置的默认值
     */
    static public function filterInput($var_name, $filter_type = 'string', $input_type = 'post', $options = array()) {
        $input_type  = __GET ? 'get' : $input_type;//调试模式下，支持通过$_GET获取数据 by mrmsl on 2012-06-30 14:00:36
        $input_type  = self::_getInputType($input_type);
        $filter_type = self::_getFilterType($filter_type);

        if (null === $input_type || null === $filter_type) {
            return null;
        }

        return filter_input($input_type, $var_name, $filter_type, array_merge(array('flags' => FILTER_NULL_ON_FAILURE), $options));
    }

    /**
     * 变量过滤
     *
     * @param string $var         变量名
     * @param string $filter_type 过滤类型。默认string
     * @param array  $options     过滤选项。默认null
     *
     * @return mixed 过滤成功，返回过滤后的值，否则返回false
     */
    static public function filterVar($var, $filter_type = 'string', $options = null) {
        $filter_type = self::_getFilterType($filter_type);

        return filter_var($var, $filter_type, $options);
    }

    /**
     * 浮点数过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认0.00
     *
     * @return string 过滤后的浮点数或默认值
     */
    static public function float($var_name, $type = 'post', $default = 0.00) {
        $string = self::filterInput($var_name, 'float', $type, array('options' => array('default' => $default)));

        return is_float($string) ? $string : $default;
    }

    /**
     * 整数过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认0
     *
     * @return string 过滤后的整数或默认值
     */
    static public function int($var_name, $type = 'post', $default = 0) {
        $string = self::filterInput($var_name, 'int', $type, array('options' => array('default' => $default)));

        return is_int($string) ? $string : $default;
    }

    /**
     * ip过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认''
     *
     * @return string 过滤后的ip或默认值
     */
    static public function ip($var_name, $type = 'post', $default = '') {
        $string = self::filterInput($var_name, 'ip', $type, array('options' => array('default' => $default)));

        return null === $string ? $default : $string;
    }

    /**
     * 获取页及总页数
     *
     * @param int   $count     总数
     * @param mixed $page      当前页或变量名。默认page
     * @param mixed $page_size 每页大小或变量名。默认page_size
     *
     * @return array 包含了当前页、总页数、偏移量数组
     */
    static public function page($count, $page = 'page', $page_size = 'page_size') {

        if (!is_int($page_size)) {

            if ($page_size = self::int($page_size, 'post')) {//_POST优先
            }
            else {
                $page_size = self::int($page_size, 'get', PAGE_SIZE);
            }
        }

        $total_page     = ceil($count / $page_size);

        if (is_int($page)) {
            $origin_page = $page;
        }
        else {

            if ($origin_page = self::int($page, 'post')) {//_POST优先
            }
            else {
                $origin_page = self::int($page, 'get', 1);
            }
        }

        $page           = $origin_page < 1 ? 1 : $origin_page;
        $page           = $page > $total_page ? $total_page : $page;
        $page           = $page < 1 ? 1 : $page;
        $limit          = ($page - 1) * $page_size . ',' . $page_size;

        return array(
            'origin_page'   => $origin_page,
            'page'          => $page,
            'total_page'    => $total_page,
            'limit'         => $limit
        );
    }//end page

    /**
     * 用$_GET或$_POST处理字符串请求变量
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-08-01 17:45:46
     * @lastmodify   2013-01-17 08:54:25 by mrmsl
     *
     * @param string $var_name      参数名
     * @param string $type          请求方法，get|post。默认post
     * @param bool   $trim          true使用trim()去除两边空白。默认true
     * @param bool   $stripslashes  true stripslashes内容。默认false
     * @param string $default       请求变量不存在或返回空值时的默认值。默认''
     *
     * @return string 变量值
     */
    static public function raw($var_name, $type = 'post', $trim = true, $stripslashes = false, $default = '') {
        $method = $type == 'post' ? $_POST : $_GET;
        $method = __GET ? $_GET : $method;

        if (!isset($method[$var_name]) || !is_string($method[$var_name])) {
            return $default;
        }

        $value  = $method[$var_name];
        $value  = $trim ? trim($value) : $value;
        $value  = $stripslashes ? stripslashes($value) : $value;

        return '' === $value ? $default : $value;
    }

    /**
     * 正则过滤
     *
     * @param string $var_name            参数名
     * @param string $regexp              正则表达式
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认''
     *
     * @return string 过滤后的字符串或默认值
     */
    static public function regexp($var_name, $regexp, $type = 'post', $default = '') {
        $string = self::filterInput($var_name, 'regexp', $type, array('options' => array('default' => $default, 'regexp' => $regexp)));

        return null === $string ? $default : $string;
    }

    /**
     * 字符串过滤
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回空值时（不存在，过滤失败，返回空字符串）默认值。默认''
     * @param bool   $html_entity_decode  true html_entity_decode字符串。默认true
     * @param bool   $trim                true trim字符串。默认true
     *
     * @return string 过滤后的字符串或默认值
     */
    static public function string($var_name, $type = 'post', $default = '', $html_entity_decode = true, $trim = true) {
        $string = self::filterInput($var_name, 'string', $type, array('options' => array('default' => $default)));

        if (null === $string) {
            return $default;
        }

        $string = $trim ? trim($string) : $string;
        $string = $html_entity_decode ? html_entity_decode($string, ENT_QUOTES) : $string;

        return '' === $string ? $default : $string;
    }

    /**
     * url过滤
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-09-06 16:03:23
     * @lastmodify   2013-01-17 09:00:52 by mrmsl
     *
     * @param string $var_name            参数名
     * @param string $type                请求方法，get|post。默认post
     * @param string $default             如果过滤返回null时(不存在，过滤失败)默认值。默认''
     *
     * @return string 过滤后的url或默认值
     */
    static public function url($var_name, $type = 'post', $default = '') {
        $string = self::filterInput($var_name, 'url', $type, array('options' => array('default' => $default)));

        return null === $string ? $default : $string;
    }
}