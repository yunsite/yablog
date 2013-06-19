<?php
/**
 * 模板编译类
 *
 * @file            Template.class.php
 * @package         Yab
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-05 10:30:19
 * @lastmodify      $Date$ $Author$
 */
class Template {
    /**
     * @var array $_instance 缓存实例。默认array()
     */
    static private $_instance = array();

    /**
     * @var bool $_caching true开启模板缓存。默认false
     */
    private $_caching = false;

    /**
     * @var string $_cache_lifetime 缓存时间，单位秒。默认3600
     */
    private $_cache_lifetime = 3600;

    /**
     * @var string $_cache_id 缓存标识id。默认''
     */
    private $_cache_id = '';

    /**
     * @var string $_cache_path 编译缓存路径。默认null，自定义路径
     */
    private $_cache_path = null;

    /**
     * @var string $_templates_path 模板路径。默认
     */
    private $_templates_path = null;

    /**
     * @var string $_compile_path 编译路径。默认null，自定义路径
     */
    private $_compile_path = null;

    /**
     * @var array $_tpl_vars 模板变量。默认array()
     */
    private $_tpl_vars = array();

    /**
     * @var bool $_force_compile true强制每次都需要编译。默认false
     */
    private $_force_compile = false;

    /**
     * 获取本类实例
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 12:55:37
     *
     * @param array $config 配置。默认null，C('TEMPLATE_CONFIG')
     *
     * @return object 本类实例
     */
    static public function getInstance($config = null) {

        if (null === $config && ($v = C('TEMPLATE_CONFIG'))) {
            $config = $v;
        }

        $config = $config ? $config : array();

        $identify = to_guid_string($config);

        if (!isset(self::$_instance[$identify])) {
            self::$_instance[$identify] = new Template($config);
        }

        return self::$_instance[$identify];
    }

    /**
     * 获取本类实例
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 12:57:06
     *
     * @param array $config 配置
     *
     * @return object 本类实例
     */
    public function __construct($config = array()) {

        foreach($config as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * 魔术方法__get，获取属性
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-12 15:41:06
     *
     * @param mixed $key 属性
     *
     * @return mixed 属性值
     */
    public function __get($key) {
        return $this->$key;
    }

    /**
     * 魔术方法__set，设置属性
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-12 15:39:06
     *
     * @param mixed $key 属性名
     * @param mixed $val 属性值
     *
     * @return object 本类实例
     */
    public function __set($key, $val) {
        $this->$key = $val;

        return $this;
    }

    /**
     * 赋值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 16:00:41
     *
     * @param mixed $key 变量名称或一组变量数组
     * @param mixed $val 变量值。默认null
     *
     * @return object 本类实例
     */
    public function assign($key, $value = null) {

        if (is_array($key)) {

            foreach ($key as $k => $v) {
                $this->assign($k, $v);
            }
        }
        else {
            $this->_tpl_vars[$key] = $value;
        }

        return $this;
    }

    /**
     * 清除编译缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-12 15:16:23
     *
     * @param string $controller 控制器。默认CONTROLLER_NAME。如果为空，则清除全部编译文件
     * @param string $action     操作方法。默认ACTION_NAME。如果为空，则清除$controller所有编译文件
     *
     * @return void 无返回值
     */
    public function clearCache($controller = CONTROLLER_NAME, $action = ACTION_NAME, $cache_id =  '') {
        $controller  = strtolower($controller ? $controller : CONTROLLER_NAME);
        $action      = $action ? $action : ACTION_NAME;
        $compile_dir = $this->_compile_path . $controller . '/';
        $cache_dir   = $this->_cache_path . $controller . '/';

        if ($action) {

            if (C('CLEAR_COMPILE_FILE') && is_file($filename = $compile_dir . "{$action}.php")) {//编译文件
                unlink($filename);
            }

            is_file($filename = $cache_dir . $action . $cache_id . C('HTML_SUFFIX')) && unlink($filename);//缓存文件

            if ($cache_id) {//同时清除多个
                $cache_id = is_array($cache_id) ? $cache_id : explode(',', $cache_id);

                foreach ($cache_id as $v) {
                    is_file($filename = $cache_dir . $action . $v . C('HTML_SUFFIX')) && unlink($filename);
                }
            }
        }
        else {

            if (C('CLEAR_COMPILE_FILE')) {

                foreach(glob($compile_dir . '*') as $filename) {//编译文件
                    is_file($filename) && unlink($filename);var_dump($filename);
                }
            }

            foreach(glob($cache_dir . '*') as $filename) {//缓存文件
                is_file($filename) && unlink($filename);
            }
        }
    }//end clearCache

    /**
     * 编译文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-05 22:02:21
     *
     * @param string $controller 控制器
     * @param string $action     操作方法
     *
     * @throw Exception 调用模板不存在
     * @return string 编译后文件路径名
     */
    public function compile($controller = CONTROLLER_NAME, $action = ACTION_NAME) {
        $controller     = strtolower($controller ? $controller : CONTROLLER_NAME);
        $action         = $action ? $action : ACTION_NAME;
        $template_file  = $this->_templates_path . $controller . '/' . $action . C('TEMPLATE_SUFFIX');
        $compile_dir    = $this->_compile_path . $controller . '/';
        $compile_file   = $compile_dir . $action . '.php';

        new_mkdir($compile_dir);

        if (!is_file($template_file)) {
            throw new Exception(L('_TEMPLATE_NOT_EXIST_') . "($template_file)");
        }
        elseif(!$this->_force_compile && is_file($compile_file) && filemtime($template_file) < filemtime($compile_file)) {//已编译并且自上次编译后模板文件未修改
            return $compile_file;
        }

        $source = file_get_contents($template_file);

        if (false !== strpos($source, '{template')) {//template()
            $source = preg_replace('#\{template\s+(.+)\}#', '<?php require(template($1)); ?>', $source);
        }

        if (false !== strpos($source, '{require')) {//require()
            $source = preg_replace('#\{include\s+(.+)\}#', '<?php include($1); ?>', $source);
        }

        if (false !== strpos($source, '{php')) {//php
            $source = preg_replace('#\{php\s+([^\}]+)\}#', '<?php $1 ?>', $source);
        }

        if (false !== strpos($source, '{echo')) {//echo
            $source = preg_replace('#\{echo\s+([^\}]+)\}#', '<?php echo $1 ?>', $source);
        }

        //if
        if (false !== strpos($source, '{if')) {
            $source = preg_replace('#\{if\s+(.+?)\}#', '<?php if($1) { ?>', $source);
            $source = preg_replace('#\{/if\}#', '<?php } ?>', $source);
        }
        if (false !== strpos($source, '{else')) {
            $source = preg_replace('#\{else\}#', '<?php } else { ?>', $source);
            $source = preg_replace('#\{elseif\s+(.+?)\}#', '<?php } elseif ($1) { ?>', $source);
        }

        //foreach
        if (false !== strpos($source, '{foreach')) {
            $source = preg_replace('#\{foreach\s+(\S+)\s+(\S+)\}#', '<?php \$n=1;if(is_array($1)) { foreach($1 as $2) { ?>', $source);
            $source = preg_replace('#\{foreach\s+(\S+)\s+(\S+)\s+(\S+)\}#', '<?php \$n=1; if(is_array($1)) { foreach($1 as $2 => $3) { ?>', $source);
            $source = preg_replace('#\{/foreach\}#', '<?php \$n++; }} unset(\$n); ?>', $source);
        }

        //for 循环
        if (false !== strpos($source, '{for')) {
            $source = preg_replace('#\{for\s+(.+?)\}#','<?php for($1) { ?>', $source);
            $source = preg_replace('#\{/for\}#','<?php } ?>', $source);
        }

        //L()
        if (false !== strpos($source, '{L ')) {
            $source = preg_replace('#\{L\s+([\w,]+)\}#', '<?php echo L(\'$1\');?>', $source);
        }

        //C()
        if (false !== strpos($source, '{C ')) {
            $source = preg_replace('#\{C\s+(\w+)\}#', '<?php echo C(\'$1\');?>', $source);
        }

        $source = preg_replace('#\{([a-z_]\w+)\}#i', '<?php echo $1;?>', $source);//{CONSTANT
        $source = preg_replace('#\{((\$\w+)\.(\w+))\}#', '<?php echo $2[\'$3\'];?>', $source);//{$array.key 数组，仅支持一维
        $source = preg_replace('#\{(\$\w+)\}#', '<?php echo $1;?>', $source);//{$var
        $source = "<?php\n!defined('YAB_PATH') && exit('Access Denied'); ?>" . $source;

        file_put_contents($compile_file, $source);

        return $compile_file;
    }//end compile

    /**
     * 渲染模板
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 09:33:01
     *
     * @param string $controller 控制器
     * @param string $action     操作方法
     * @param string $cache_id   缓存标识。默认''
     *
     * @return void 无返回值
     */
    public function display($controller = CONTROLLER_NAME, $action = ACTION_NAME, $cache_id = '') {
        $controller  = strtolower($controller ? $controller : CONTROLLER_NAME);
        $action      = $action ? $action : ACTION_NAME;
        echo $this->fetch($controller, $action, $cache_id, true);
    }

    /**
     * 编译并获取编译文件内容
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 09:20:43
     *
     * @param string $controller 控制器
     * @param string $action     操作方法
     * @param string $cache_id   缓存标识。默认''
     * @param bool   $return     true返回编译文件内容。默认true
     *
     * @return string|true $return=true返回编译文件内容，否则true
     */
    public function fetch($controller = CONTROLLER_NAME, $action = ACTION_NAME, $cache_id = '', $return = true) {
        $controller     = strtolower($controller ? $controller : CONTROLLER_NAME);
        $action         = $action ? $action : ACTION_NAME;
        $compile_file   = $this->compile($controller, $action);

        if (!$this->_force_compile && $this->_caching) {//缓存
            $cache_dir = $this->_cache_path . $controller . '/';

            new_mkdir($cache_dir);

            $cache_file = $cache_dir . $action . ($cache_id ? $cache_id : $this->_cache_id) . C('HTML_SUFFIX');

            if (is_file($cache_file) && filemtime($cache_file) > time() - $this->_cache_lifetime) {//缓存未过期

                if ($return) {
                    return file_get_contents($cache_file);
                }
            }
        }

        ob_start();

        extract($this->_tpl_vars, EXTR_OVERWRITE);

        require($compile_file);

        if ($return || $this->_caching) {//返回内容或写入缓存
            $content = ob_get_contents();
            ob_end_clean();

            if (!$this->_force_compile && $this->_caching) {
                file_put_contents($cache_file, $content);
            }

            return $content;
        }

        return true;
    }//end fetch
}