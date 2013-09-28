<?php
/**
 * 底层控制器类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            Controller.class.php
 * @package         Yab\Core
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-05-09 16:50:29
 * @lastmodify      $Date$ $Author$
 */

class Controller {
    /**
     * @var object $_view_template 模板编译对象。默认null
     */
    protected $_view_template = null;
    /**
     * @var bool $_init_model true实例对应模型。默认true
     */
    protected $_init_model = true;
    /**
     * @var string $_model_name 模型名称。默认null，对应控制器名
     */
    protected $_model_name         = null;
    /**
     * @var object $_model 对应模型实例。默认null
     */
    protected $_model = null;
    /**
     * @var array $_controller_name 控制器名称。默认null
     */
    protected $_controller_name = null;
    /**
     * @var string $_pk_field 数据表主键字段。默认null
     */
    protected $_pk_field = null;

    /** ajax方式返回数据到客户端
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 16:05:40 by mrmsl
     *
     * @param mixed  $success 返回状态或返回数组
     * @param string $msg     提示信息
     * @param mixed  $data    要返回的数据
     * @param mixed  $total   总数
     * @param string $type    ajax返回类型 JSON XML等
     *
     * @return void 无返回值
     */
    protected function _ajaxReturn($success = true, $msg = '', $data = null, $total = null, $type = '') {

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

        $result['time'] = round(microtime(true) - REQUEST_TIME_MICRO, 6);

        //扩展ajax返回数据, 在Action中定义function ajaxAssign(&$result){} 方法 扩展ajax返回数据。
        method_exists($this, 'ajaxAssign') && $this->_ajaxAssign($result);

        $type = strtoupper($type ? $type : C('DEFAULT_AJAX_RETURN'));

        if ('JSON' == $type) {//返回JSON数据格式到客户端 包含状态信息
            if (__GET) {
                echo var_export($result, true); //调试模式下，不需要json_encode，以可读
            }
            else {
                header('Content-Type: application/json; charset=utf-8');

                $result = json_encode($result);
                $v      = C('JSONP_CALLBACK');

                if ($v && isset($_GET[$v])) {//jsonp
                    $result = $_GET[$v] . '(' . $result . ')';
                }

                echo $result;
            }

            exit();
        }
        elseif ('XML' == $type) {//返回xml格式数据
            header('Content-Type: text/xml; charset=utf-8');
            exit(xml_encode($result));
        }
        elseif ('EVAL' == $type) {//返回可执行的js脚本
            exit($data);
        }
    }//end _ajaxReturn

    /**
     * 生成静态页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-12 15:01:54
     *
     * @param string $filename 文件路径
     * @param string $content  文件内容
     *
     * @return void 无返回值
     */
    protected function _buildHtml($filename, $content) {
        new_mkdir(dirname($filename));

        file_put_contents($filename, $content);
    }

    /**
     * 渲染模板并输出
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-06 17:32:39
     *
     * @param string $controller 控制器。默认null=CONTROLLER_NAME
     * @param string $action     操作方法。默认null=ACTION_NAME
     * @param string $cache_id   缓存标识。默认''
     *
     * @return void 无返回值
     */
    protected function _display($controller = null, $action = null, $cache_id = '') {
        echo $this->_fetch($controller ? $controller : CONTROLLER_NAME, $action ? $action : ACTION_NAME, $cache_id);
    }

    /**
     * 渲染模板
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 08:49:33
     *
     * @param string $controller 控制器。默认null=CONTROLLER_NAME
     * @param string $action     操作方法。默认null=ACTION_NAME
     * @param string $cache_id   缓存标识。默认''
     *
     * @return void 无返回值
     */
    protected function _fetch($controller = null, $action = null, $cache_id = '') {
        return $this->getViewTemplate()
        ->fetch($controller ? $controller : CONTROLLER_NAME, $action ? $action : ACTION_NAME, $cache_id);
    }

    /**
     * 获取指定分类下所有子类id
     *
     * @param int    $item_id      分类id
     * @param bool   $include_self true包含本身。默认true
     * @param bool   $return_array true返回数组形式。默认false
     * @param string $filename     缓存文件名。默认nulll，当前模块名
     * @param string $level_field  层次字段。默认level
     * @param string $node_field   节点字段。默认node
     *
     * @return string 所有子类id，如果没有子类，返回空字符串或空数组
     */
    protected function _getChildrenIds($item_id, $include_self = true, $return_array = false, $filename = null, $level_field = 'level', $node_field = 'node') {
        $filename      = $filename ? $filename : $this->_getControllerName();
        $cache_data    = $this->cache(null, $filename);

        if (!isset($cache_data[$item_id])) {
            return $return_array ? array() : '';
        }

        $item_info     = $cache_data[$item_id];
        $item_node     = $item_info[$node_field];
        $item_level    = $item_info[$level_field];
        $children_ids  = $include_self ? $item_id : '';

        foreach ($cache_data as $k => $v) {

            if (0 === strpos($v[$node_field], $item_node . ',') && $v[$level_field] > $item_level && $k != $item_id) {
                $children_ids .= ',' . $k;
            }
        }

        $children_ids = trim($children_ids, ',');

        return $return_array ? explode(',', $children_ids) : $children_ids;
    }

    /**
     * 获取当前控制器名称
     *
     * @author            mrmsl <msl-138@163.com>
     * @data              2012-12-25 11:59:35
     * @lastmodify        2013-01-21 16:07:20 by mrmsl
     *
     * @return string 当前控制器名称
     */
    protected function _getControllerName() {
        $this->_controller_name = $this->_controller_name ? $this->_controller_name : substr(get_class($this), 0, -10);

        return $this->_controller_name;
    }

    /**
     * 获取表字段
     *
     * @return mixed 获取成功，将返回包含字段名的数组，否则false
     */
    protected function _getDbFields() {
        return $this->_model->getDbFields();
    }

    /**
     * 根据两字段组合值获取数据,如id及add_time匹配才能获取到数据,而不仅仅根据id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-14 16:22:53
     *
     * @param string|array $field_arr   组合字段,通常为array('id','add_time')
     * @param string $data 组合信息,默认null=$_POST['data'],格式:id1|add_time1,id2|add_time2,...
     * @param string $field 选取字段,默认*
     * @param string $table ,默认null
     *
     * @return array 数据
     */
    protected function _getPairsData($field_arr, $data = null, $field = '*', $pk_field = null, $table = null) {
        $data       = null === $data ? Filter::string('data') : $data;
        $return_arr = array();

        if (!$data) {
            return $return_arr;
        }

        $data = explode(',', $data);

        foreach($data as $k => $v) {
            $v_arr = explode('|', $v);

            if (isset($v_arr[0], $v_arr[1]) && ($column_1 = intval($v_arr[0])) && ($column_2 = intval($v_arr[1]))) {
                $return_arr[$column_1] = $column_2;
            }
        }

        if (!$return_arr) {
            $error = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . L('INVALID_PARAM') . var_export($data, true);
            trigger_error($error, E_USER_ERROR);

            return $return_arr;
        }

        $column_1_arr   = array_keys($return_arr);
        $column_2_arr   = array_values($return_arr);
        $pk_field       = $pk_field ? $pk_field : $this->_pk_field;
        $field_arr      = is_array($field_arr) ? $field_arr : explode(',', $field_arr);

        $table && $this->_model->table($table);

        $data           = $this->_model
        ->where(array($field_arr[0] => array('IN', $column_1_arr), $field_arr[1] => array('IN', $column_2_arr)))
        ->field($field)
        ->key_column($pk_field)
        ->select();

        $un_match       = count($data) == count($return_arr) ? '' : 'data count not match.';

        foreach($data as $k => $v) {

            if ($return_arr[$k] != $v[$field_arr[1]]) {//id与时间不匹配
                $un_match .= ",{$k}({$return_arr[$k]}) => {$k}({$v[$field_arr[1]]})[correct]";
                unset($data[$k]);
            }
        }

        if ($un_match) {
            $error = get_method_line(__METHOD__, __LINE__, LOG_NORMAL_ERROR) . L('PAIRS_DATA_UN_MATCH') . $un_match;
            trigger_error($error, E_USER_ERROR);
        }

        return $data;
    }//end _getPairsData

    /**
     * url跳转
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-21 16:13:40 by mrmsl
     *
     * @param string $url         跳转url。默认''，跳转到网站首页
     * @param string $base_url    基链接。默认null，相对网站根目录
     * @param int    $status_code 头部状态码。默认0，不发送头部状态码
     *
     * @return void 无返回值
     */
    protected function _redirect($url = '', $base_url = null, $status_code = 0) {
        $url = null === $base_url ? to_website_url('admin.php/') . $url : $base_url . $url;
        redirect($url, 0, '', $status_code);
    }

    /**
     * 查询数据库出错并exit
     *
     * @param string $content  错误信息。默认''，自动获取最后出错sql
     * @param string $msg      返回错误提示信息。默认''，取$content
     * @param string $db_sql   sql语句。默认''，取当前模型最后sql
     * @param string $db_error sql错误。默认''，取当前db错误
     *
     * @return void 无返回值
     */
    protected function _sqlErrorExit($content = '', $msg = '', $db_sql = '', $db_error = '') {
        $db_sql   = $db_sql ? $sql : $this->_model->getLastSql();
        $db_error = $db_error ? $db_error : $this->_model->getDbError();
        $error = '<br />' . $db_sql . '<br />' . $db_error;

        $this->_ajaxReturn(false, ($msg ? $msg : $content) . (APP_DEBUG ? $error : ''));
    }

    /**
     * 构造方法
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-26 18:19:24
     *
     * @return bool false跨模块,否则true
     */
    public function __construct() {
        method_exists($this, '_initialize') && $this->_initialize();//控制器初始化

        if ($this->_init_model) {//实例对应模型

            if (is_file(APP_PATH . 'models/' . $this->_getControllerName() . 'Model' . PHP_EXT)) {
                $this->_model = D($this->_getControllerName());//模型
            }
            else {
                $this->_model = D((empty($this->_model_name) ? 'Common' : $this->_model_name) . 'Model');//模型

                //无对应模型类,指定模型表名
                !empty($this->_true_table_name) && $this->_model->setProperty('_true_table_name', $this->_true_table_name);
            }

            $this->_model->setProperty('_module', $this);
            $this->_pk_field = $this->_model->getPk();//主键字段
        }

        if (defined('APP_INIT')) {//跨模块，直接返回
            return false;
        }

        define('APP_INIT' , true);   //跨模块调用时，不再往下

        L('CONTROLLER_NAME', L('CONTROLLER_NAME_' .  $this->_getControllerName()));//C => L

        return true;
    }//end __construct

    /** 设置(获取)表数据缓存
     *
     * @param int    $id     数据id。默认0
     * @param mixed  $value  数据,空表示取数据,否则,设置数据
     * @param string $name   文件名。默认null，模块名称
     * @param string $path   缓存路径。默认null=MODULE_CACHE_PATH
     *
     * @return mixed 如果不指定id，返回全部缓存，如果指定id并指定id缓存存在，返回指定id缓存，否则返回false
     */
    public function cache($id = 0, $name = null, $value = '', $path = null) {
        $name = $name ? $name : $this->_getControllerName();
        $path = $path ? $path : MODULE_CACHE_PATH;

        if ('' === $value) {
            $data = F($name, '', $path);

            if ($id) {

                if (strpos($id, '.')) {//直接获取某一字段值
                    list($id, $key) = explode('.', $id);
                    return isset($data[$id][$key]) ? $data[$id][$key] : false;
                }
                else {
                    return isset($data[$id]) ? $data[$id] : false;
                }
            }

            return $data;
        }
        else {

            if (null === $value) {

                if (method_exists($this, '_setCacheData')) {
                    $value = $this->_setCacheData();
                }
                else {
                    $value = $this->_model->key_column($this->_model->getPk())->select();
                }
            }

            F($name, $value, $path);

            return $this;
        }
    }//end cache

    /**
     * 获取留言评论设置值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-22 10:56:07
     *
     * @param string $module 模块
     * @param string $index  具体键值。默认''
     *
     * @return mixed 如果$index不为空，返回该健值，否则返回设置值数组
     */
    public function getGuestbookCommentsSetting($module, $index = '') {
        static $data = array();

        if (isset($data[$module])) {
            return $index ? $data[$module][$index] : $data[$module];
        }
        else {
            $data[$module] = array();
        }

        $key_arr = array(//验证码设置健=>取默认值
            'check'             => -1,//是否审核
            'alternation'       => -1,//间隔
            'max_reply_level'   => -1,//最大回复层数
            'disabled_username' => '',//禁用用户名
            'disabled_ip'       => '',//禁止ip
        );

        $key      = $module . '_';

        if('module_guestbook_comments' == $module) {//用空间换时间

            foreach ($key_arr as $k => $v) {
                $data[$module][$k] = sys_config($key . $k, 'Module');
            }
        }
        else {
            $default = $this->getGuestbookCommentsSetting('module_guestbook_comments');

            foreach ($key_arr as $k => $v) {
                $_v = sys_config($key . $k, 'Module');
                $data[$module][$k] = $v == $_v ? $default[$k] : $_v;
            }
        }

        return $index ? $data[$module][$index] : $data[$module];
    }//end getGuestbookCommentsSetting

    /**
     * 获取属性值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-20 09:25:43
     *
     * @param string $name 属性值
     *
     * @return mixed 属性存在，返回该值，否则''
     */
    public function getProperty($name) {
        return property_exists($this, $name) ? $this->$name : '';
    }

    /**
     * 获取视图模板引擎实例
     *
     * @author            mrmsl <msl-138@163.com>
     * @data              2013-04-12 15:36:13
     * @lastmodify        2013-04-15 17:05:13 by mrmsl
     *
     * @param mixed $config 模板引擎配置。默认null.为build_html生成静态页时，$config = array('_caching' => true, '_force_compile' => false);
     *
     * @return object 视图模板引擎实例
     */
    public function getViewTemplate($config = null) {

        if (!$this->_view_template) {
            $this->_view_template = Template::getInstance();
            $this->_view_template->assign(sys_config())
            //->assign('L', L())
            //->assign('C', C())
            ->assign('me', $this)
            ->assign('nav_id', strtolower(CONTROLLER_NAME));
        }

        if (null !== $config) {//属性

            if ('build_html' === $config) {//生成静态页
                $config = array(
                    '_caching'          => false,
                    '_force_compile'    => false,
                );
            }

            foreach($config as $k => $v) {
                $this->_view_template->$k = $v;

            }
        }

        return $this->_view_template;
    }

     /**
     * 获取不带链接的类似面包屑导航，如菜单管理»添加菜单
     *
     * @param int    $id         id字段值
     * @param string $name_field 名称字段
     * @param string $filename   缓存文件。默认null，当前模块名
     * @param string $separator  导航分割符。默认null
     *
     * @return string 面包屑导航
     */
    public function nav($id, $name_field, $filename = null, $separator = null) {
        $separator  = null === $separator ? BREAD_SEPARATOR : $separator;
        $nav        = array();
        $data       = $this->cache(null, $filename ? $filename : $this->_getControllerName());
        $info       = $data[$id];

        foreach(explode(',', $info['node']) as $item) {
            $nav[] = $data[$item][$name_field];
        }

        if (TITLE_SEPARATOR == $separator) {
            return join(TITLE_SEPARATOR, array_reverse($nav));
        }

        return join($separator, $nav);
    }

    /**
     * 远程调用模块的操作方法 URL 参数格式: 模块/操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-09-27 16:53:36
     *
     * @param string $url 调用地址
     * @param string|array $vars 调用参数,支持字符串和数组

     * @return mixed 模块的操作方法返回结果
     */
    public function R($url, $vars = array()) {
        R($url, $vars);
    }
}