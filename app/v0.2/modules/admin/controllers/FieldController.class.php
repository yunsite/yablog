<?php
/**
 * 表单域控制器类
 *
 * @file            FieldController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-08-01 16:37:11
 * @lastmodify      $Date$ $Author$
 */

class FieldController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->cache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache   = true;
    /**
     * @var bool $_get_children_ids true取所有子表单， CommonController->delete()会用到。默认false
     */
    protected $_get_children_ids   = false;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认field_name
     */
    protected $_name_column        = 'field_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'enable'   => 'add',//显示隐藏
    );

    /**
     * 获取通用Extjs 表单域代码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-22 14:54:38
     * @lastmodify      2013-01-22 10:21:03 by mrmsl
     *
     * @param string $key   代码类型，如verifycode_enable验证码是否启用
     * @param mixed  $extra 额外参数，默认''
     *
     * @return string Extjs 表单域代码
     */
    private function _fieldCode($key, $extra = '') {
        $default = "'<a class=\"a-font-000\" href=\"#' + this.getAction({$this->_get_action}) + '\">' + lang('SYSTEM,DEFAULT,VALUE') + '</a>'";

        $js_arr = array(

            //验证码启用
            'verifycode_enable' => "
                 extField.fieldContainer('%@fieldLabel',
                    [
                     extField.checkbox('@input_name', '', '', 'ENABLE', 1, '', {xtype: 'radio'}),
                     extField.checkbox('@input_name', '', '', 'DISABLED', 0, '', {xtype: 'radio'})
                     " . ($extra ? ",extField.checkbox('@input_name', '', '', '%' + {$default}, -1, '', {xtype: 'radio'})" : '') . "
                    ], true, {
                     xtype: 'radiogroup',
                        value: '@value' ? {'@input_name': '@value'} : false,
                        columns: 1,
                        vertical: true,
                        name: '@input_name'
                })",

            //验证码宽度
            'verifycode_width'  => "extField.fieldContainer(['%@fieldLabel', [['numberField', '@input_name', 'PLEASE_ENTER,%@field_name', '', '@value', {minValue: 0, maxValue: 100}], lang('UNIT') + '：px' + @tip], true])",

            //验证码高度
            'verifycode_height' => "extField.fieldContainer(['%@fieldLabel', [['numberField', '@input_name', 'PLEASE_ENTER,%@field_name', '', '@value', {minValue: 0, maxValue: 50}], lang('UNIT') + '：px' + @tip], true])",

            //验证码长度
            'verifycode_length' => "extField.fieldContainer(['%@fieldLabel', [['numberField', '@input_name', 'PLEASE_ENTER,%@field_name', '', '@value', {minValue: 0, maxValue: 10}], lang('UNIT') + '：px' + @tip], true])",

            //验证码顺序
            'verifycode_order'  => "extField.fieldContainer(['%@fieldLabel', [[null, '@input_name', 'PLEASE_ENTER,%@field_name', '', '@value'], lang('VERIFY_CODE_ORDER_TIP')" . ($extra ? " + lang('%。-1,MEAN,CN_QU') + {$default}" : '') . "], true, {vertical: true}])",

            //验证码刷新限制
            'verifycode_refresh_limit'  => "
                extField.fieldContainer(['%@fieldLabel', [
                [null,'@input_name','', '', '@value', {size: 10}],
                lang('VERIFY_CODE_REFRESH_LIMIT_TIP')" . ($extra ? " + lang('%。,KEEP_BLANK,CN_QU') + {$default}" : '') . "
                ], true])",

            //验证码错误限制
            'verifycode_error_limit'  => "
                extField.fieldContainer(['%@fieldLabel', [
                [null,'@input_name','', '', '@value', {size: 10}],
                lang('VERIFY_CODE_ERROR_LIMIT_TIP')" . ($extra ? " + lang('%。,KEEP_BLANK,CN_QU') + {$default}" : '') . "
                ], true])",

            //验证码区分大小写
            'verifycode_case' => "
                 extField.fieldContainer('%@fieldLabel',
                    [
                     extField.checkbox('@input_name', '', '', 'DIFFERENTIATE', 1, '', {xtype: 'radio'}),
                     extField.checkbox('@input_name', '', '', 'NO,DIFFERENTIATE', 0, '', {xtype: 'radio'}),
                     " . ($extra ? "extField.checkbox('@input_name', '', '', '%' + {$default}, -1, '', {xtype: 'radio'})" : '') . "
                    ], true, {
                     xtype: 'radiogroup',
                        value: '@value' ? {'@input_name': '@value'} : false,
                        columns: 1,
                        vertical: true,
                        name: '@input_name'
                })",

            //验证码类型
            'verifycode_type' => "
                extField.fieldContainer('%@fieldLabel',
                [
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_LETTERS', lang('VERIFY_CODE_TYPE_LETTERS_VALUE'), '', {xtype: 'radio'}),
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_LETTERS_UPPER', lang('VERIFY_CODE_TYPE_LETTERS_UPPER_VALUE'), '', {xtype: 'radio'}),
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_LETTERS_LOWER', lang('VERIFY_CODE_TYPE_LETTERS_LOWER_VALUE'), '', {xtype: 'radio'}),
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_NUMERIC', lang('VERIFY_CODE_TYPE_NUMERIC_VALUE'), '', {xtype: 'radio'}),
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_ALPHANUMERIC', lang('VERIFY_CODE_TYPE_ALPHANUMERIC_VALUE'), '', {xtype: 'radio'}),
                 extField.checkbox('@input_name', '', '', 'VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND', lang('VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND_VALUE'), '', {xtype: 'radio'})
                 " . ($extra ? ",extField.checkbox('@input_name', '', '', '%' + {$default}, -1, '', {xtype: 'radio'})" : '') . "
                ], true, {
                 xtype: 'radiogroup',
                    value: '@value' ? {'@input_name': '@value'} : false,
                    columns: 1,
                    vertical: true,
                    name: '@input_name'
                })",
            //评论留言是否需要审核
            'guestbook_comments_check' => "
                 extField.fieldContainer('%@fieldLabel',
                    [
                     extField.checkbox('@input_name', '', '', 'NEED,AUDITING', 1, '', {xtype: 'radio'}),
                     extField.checkbox('@input_name', '', '', 'NO,NEED,AUDITING', 0, '', {xtype: 'radio'}),
                     " . ($extra ? "extField.checkbox('@input_name', '', '', '%' + {$default}, -1, '', {xtype: 'radio'})" : '') . "
                    ], true, {
                     xtype: 'radiogroup',
                        value: '@value' ? {'@input_name': '@value'} : false,
                        columns: 1,
                        vertical: true,
                        name: '@input_name'
                })",

            //评论留言最大回复次数
            'guestbook_comments_max_reply_level' => "extField.fieldContainer(['%@fieldLabel', [
                ['numberField','@input_name','PLEASE_ENTER,%@field_name', '', '@value', {size: 4, minValue: " . ($extra ? -1 : 0) . ", maxValue: 5}],
                lang('ZERO_UN_LIMIT')" . ($extra ? " + ',' + lang('%。-1,MEAN,CN_QU') + {$default}" : '') . "
            ]])",

            //评论留言间隔
            'guestbook_comments_alternation' => "extField.fieldContainer(['%@fieldLabel', [
                ['numberField','@input_name','PLEASE_ENTER,%@field_name', '', '@value', {size: 4, minValue: " . ($extra ? -1 : 0) . "}],
                lang('UNIT,%：,SECOND,%。,ZERO_UN_LIMIT')" . ($extra ? " + ',' + lang('%。-1,MEAN,CN_QU') + {$default}" : '') . "
            ]])",

            //评论留言禁用用户名
            'guestbook_comments_disabled_username' => "extField.textField('@input_name', '', '%@fieldLabel', '@value', {xtype: 'textarea', height: 200, width: 500}),
                extField.textareaComment(lang('ONE_LINE_ONE')" . ($extra ? " + lang('%。,KEEP_BLANK,CN_QU') + {$default}" : '') . ", '180')",

            //评论留言禁用ip
            'guestbook_comments_disabled_ip' => "extField.textField('@input_name', '', '%@fieldLabel', '@value', {xtype: 'textarea', height: 200, width: 500}),
                extField.textareaComment(lang('ONE_LINE_ONE')" . ($extra ? " + lang('%。,KEEP_BLANK,CN_QU') + {$default}" : '') . ", '180')",
        );

        return isset($js_arr[$key]) ? $js_arr[$key] : '';
    }//end _fieldCode

    /**
     * 保存模块设置回调
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-22 15:34:37
     * @lastmodify      2013-01-22 10:22:11 by mrmsl
     *
     * @param array $menu_info  菜单信息
     *
     * @return void 无返回值
     */
    private function _saveValueCallbackModule($menu_info) {
        $menu_info  = is_int($menu_info) ? $this->cache($menu_info, 'Menu') : $menu_info;
        $controller = $menu_info['controller'];//控制器
        $cache_key  = ucfirst($controller);
        $node_arr   = explode(',', $menu_info['node']);
        $parent_id  = $node_arr[count($node_arr) - 2];//父级菜单id
        $parent_info= $this->cache($parent_id, 'Menu');

        if ('guestbook_comments' == $parent_info['action']) {//留言评论模块,包含留言模块,评论模块
            $parent_id = $parent_info['parent_id'];
        }

        $menu_ids   = $this->_getChildrenIds($parent_id, false, false, 'Menu');
        $data       = $this->_model->where("menu_id IN({$menu_ids}) AND is_enable=1")->getField('input_name,input_value');
        $this->ache(null, $cache_key, $data);

        $system_js_filename = WWWROOT . sys_config('sys_base_js_path') . 'System.js';
        $system_js_data     = file_get_contents($system_js_filename);
        $system_js_data     = json_decode(substr($system_js_data, strpos($system_js_data, 'var System = ') + strlen('var System = '), -1), true);
        $this->_writeSystemJsData($system_js_data);
    }

    /**
     * 保存系统设置回调
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-30 17:36:36
     * @lastmodify      2013-02-01 11:06:14 by mrmsl
     *
     * @param array $menu_info 菜单信息
     *
     * @return void 无返回值
     */
    private function _saveValueCallbackSystem($menu_info) {
        $menu_info  = is_int($menu_info) ? $this->cache($menu_info, 'Menu') : $menu_info;
        $controller = $menu_info['controller'];//控制器
        $cache_key  = ucfirst($controller);
        $node_arr   = explode(',', $menu_info['node']);
        $parent_id  = $node_arr[count($node_arr) - 2];//父级菜单id
        $menu_ids   = $this->_getChildrenIds($parent_id, false, true, 'Menu');
        //$data       = $this->_model->where("menu_id IN({$menu_ids}) AND is_enable=1")->field('input_name,input_value,customize_1,is_enable')->select();
        //走缓存 by mrmsl on 2012-09-10 09:49:03
        $menu_ids   = var_export($menu_ids, true);
        $data       = array_filter($this->cache(), create_function('$v', 'return in_array($v["menu_id"],' . $menu_ids . ') && $v["is_enable"];'));

        if (empty($data)) {//空数据，不修改，直接返回
            return;
        }

        $system_data = array();
        $js_data     = array();

        foreach ($data as $item) {
            $input_name  = $item['input_name'];
            $input_value = $item['input_value'];
            $system_data[$input_name] = $input_value;

            if ($item['customize_1']) {//js数据
                $js_data[$input_name] = $input_value;
            }
        }

        $system_data['sys_base_domain_scope'] = substr($system_data['sys_base_domain'], strpos($system_data['sys_base_domain'], '.'));//
        $system_data['sys_base_website'] = $system_data['sys_base_http_protocol'] . '://' . $system_data['sys_base_domain'] . '/';//网站url

        $this->cache(null, $cache_key, $system_data);

        $js_data['IS_LOCAL'] = IS_LOCAL;
        $js_data['sys_base_website'] = $system_data['sys_base_website'];//网站url
        $js_data['sys_base_site_url'] = $system_data['sys_base_website'] . $system_data['sys_base_wwwroot'];//网站url
        //$js_data['sys_base_admin_entry'] = 0 === strpos('http://', $v = $sys_config['sys_base_admin_entry']) ? $v : $js_data['sys_base_site_url'] . $system_data['sys_base_admin_entry'];//后台管理入口
        $js_data['sys_base_domain_scope'] = $system_data['sys_base_domain_scope'];//cookie作用域
        $js_data['sys_cookie_domain'] = $system_data['sys_cookie_domain'] == '@domain' ? $system_data['sys_base_domain_scope'] : $system_data['sys_cookie_domain'];//cookie域名
        $js_data['sys_show_title_separator'] = ' ' . $system_data['sys_show_title_separator'] . ' ';
        $js_data['sys_show_bread_separator'] = ' ' . $system_data['sys_show_bread_separator'] . ' ';

        unset($js_data['sys_base_admin_entry']);//不暴露后台入口至前台

        $this->_writeSystemJsData($js_data, $system_data);

        $this->publicDefineSystemConstantsAction($system_data);//生成系统常量
    }//end _saveValueCallbackSystem

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {

        if ($menu_info = $this->cache($info['menu_id'], 'Menu')) {
            $info['menu_name'] = $menu_info['menu_name'];
        }
    }

    /**
     * 设置写缓存数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-05 14:20:47
     * @lastmodify      2013-01-22 10:29:01 by mrmsl
     *
     * @return mixed 查询成功，返回数组，否则返回false
     */
    protected function _setCacheData() {
        return $this->_model->order('is_enable DESC,sort_order ASC, field_id ASC')->index($this->_pk_field)->select();
    }

    /**
     * 快捷表单域
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-20 11:37:51
     *
     * @param string $field_code 表单域
     *
     * @return string 快捷表单域
     */
    protected function _shortcutCode($field_code) {

        //验证码字段或评论留言
        if (($is_field_code = 0 === strpos($field_code, 'verifycode_')) || ($is_guestbook_comments = 0 === strpos($field_code, 'guestbook_comments_'))) {

            if (!empty($is_field_code)) {//验证码
                $this->_get_action = "'system', 'verifycode'";
            }
            elseif (!empty($is_guestbook_comments)) {//评论留言
                $this->_get_action = "'module', 'guestbook_comments'";
            }

            $_arr = explode('@', $field_code);
            $field_code = $this->_fieldCode($_arr[0], isset($_arr[1]));

            if (!$field_code) {
                return '';
            }

            if (!isset($_arr[1])) {//无提示
                return str_replace('@tip', "''", $field_code);
            }

            $field_code = str_replace('@tip', "lang('%。0,MEAN,CN_QU,SYSTEM') + '<a class=\"a-font-000\" href=\"#' + this.getAction({$this->_get_action}) + '\">' + lang('SYSTEM,DEFAULT,VALUE') + '</a>'", $field_code);

            if (!empty($is_guestbook_comments)) {
                $field_code = str_replace('SYSTEM,', '', $field_code);
            }

            return $field_code;
        }

        return $field_code;
    }

    /**
     * 写System.js
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-20 21:52:36
     *
     * @param array $js_data        js数据
     * @param array $system_data    系统数据。默认null， 取sys_config()
     *
     * @return void 无返回值
     */
    protected function _writeSystemJsData($js_data, $system_data = null) {
        $system_data = null === $system_data ? sys_config() : $system_data;

        //管理员,留言,评论模块是开启验证码
        foreach(array('guestbook', 'comments') as $item) {

            foreach(array('enable', 'order', 'case') as $v) {
                $js_data['module_' . $item . '_verifycode_' . $v] = get_verifycode_setting('module_' . $item, $v);
            }
        }

        array2js($js_data, 'System', WWWROOT . $system_data['sys_base_js_path'] . 'System.js');
    }

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-01 16:57:05
     * @lastmodify      2013-01-22 10:29:20 by mrmsl
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证
        $module_key= 'CONTROLLER_NAME_' . CONTROLLER_NAME;
        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//管理员id
        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'field_name,field_code,validate_rule,input_name,menu_name,is_enable,sort_order,memo,customize_1';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L($module_key . ',FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if (!$menu_info = $this->cache($menu_id = $this->_model->menu_id, 'Menu')) {//菜单不存在
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) .$log_msg . ': ' . L("INVALID_PARAM,%:,PARENT_FIELD,%menu_id({$menu_id}),NOT_EXIST");
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, $error_msg);
        }

        $data['menu_name'] = $menu_info['menu_name'];//菜单名

        if ($pk_value) {//编辑

            if (!$field_info = $this->cache($pk_value)) {//表单域不存在
                $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) .$log_msg . ': ' . L("INVALID_PARAM,%:,CONTROLLER_NAME_ADMIN,%{$pk_field}({$pk_value}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            if ($this->_model->save() === false) {//更新出错
                $this->_sqlErrorExit($msg . L($module_key) . "{$field_info[$this->_name_column]}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $menu_info = $this->cache($field_info['menu_id'], 'Role');
            $field_info['menu_name'] = $menu_info['menu_name'];//菜单名

            $diff = $this->_dataDiff($field_info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L($module_key)  . "{$field_info[$this->_name_column]}({$pk_value})." . $diff. L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $data = $this->_dataDiff($data, false, $diff_key);//数据

            if ($this->_model->add() === false) {//插入出错
                $this->_sqlErrorExit($msg . L($module_key) . $data . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L($module_key) . $data . L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }//end addAction

    /**
     * 启用/禁用
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-01 16:55:45
     * @lastmodify      2013-01-22 10:29:33 by mrmsl
     *
     * @return void 无返回值
     */
    public function enableAction() {
        $this->_setOneOrZero('is_enable');
    }

    /**
     * 列表管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-01 16:53:52
     * @lastmodify      2013-01-22 10:29:43 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $sort     = Filter::string('sort', 'get', $this->_pk_field);//排序字段
        $sort     = in_array($sort, $this->_getDbFields()) ? $sort : $this->_pk_field;
        $order    = !empty($_GET['dir']) ? Filter::string('dir', 'get') : Filter::string('order', 'get');//排序
        $order    = toggle_order($order);
        $column   = Filter::string('column', 'get');//搜索字段
        $keyword  = Filter::string('keyword', 'get');//搜索关键字
        $menu_id  = Filter::int('menu_id', 'get');//所属菜单
        $is_enable= Filter::int('is_enable', 'get');//是否启用 by mrmsl on 2012-09-15 02:18:18
        $where    = array();

        if ($menu_id) {
            //getChildrenIds($item_id, $include_self = true, $return_array = false, $filename = null, $level_field = 'level', $node_field = 'node') {
            $menu_id = $this->_getChildrenIds($menu_id, true, false, 'Menu');
            $menu_id ? $where['a.menu_id'] = array('IN', $menu_id) : '';
        }

        if ($keyword !== '' && in_array($column, array($this->_name_column, 'field_code', 'validate_rule', 'input_name'))) {
            $where['a.' . $column] = $this->_buildMatchQuery('a.' . $column, $keyword, Filter::string('match_mode', 'get'));
        }

        if ($is_enable != -1) {//启用状态 by mrmsl on 2012-09-15 02:20:43
            $where['a.is_enable'] = array('EQ', $is_enable);
        }

        $total      = $this->_model->alias('a')->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_ADMIN') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model->alias('a')->join('JOIN ' . TB_MENU . ' AS m ON a.menu_id=m.menu_id')->where($where)->field('a.*,m.menu_name')->limit($page_info['limit'])->order('a.' .$sort . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_FIELD') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data, $total);

        //搜索
        if (!$field_id && $column && $keyword && in_array($column, array($this->_name_column, 'field_code', 'field_value'))) {
            $this->_queryTree($column, $keyword);
        }
        elseif ($field_id) {
            $this->_ajaxReturn(true, '', $this->_getTreeData($field_id, false));
        }

        $data = $this->cache(0, CONTROLLER_NAME . '_tree');
        $this->_ajaxReturn(true, '', $data, count($this->cache()));
    }//end listAction

    /**
     * 生成系统常量
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-03 15:15:19
     *
     * @param array $sys_config 系统信息。默认array()，取sys_config()
     *
     * @return void 无返回值
     */
    public function publicDefineSystemConstantsAction($sys_config = array()) {
        $filename   = INCLUDE_PATH . 'app_config.tpl.php';//常量模板
        $tpl        = file_get_contents($filename);
        $sys_config = $sys_config ? $sys_config : sys_config();
        $content    = preg_replace("#sys_config\('(\w+)'\)#e", '"\'" . addslashes($sys_config["\1"]) . "\'"', $tpl);
        $find       = array(
            '@@lastmodify',
            '@WEB_COOKIE_DOMAIN',
            '@WEB_SESSION_COOKIE_DOMAIN',
            '@WEB_ADMIN_ENTRY',
            '@AUTO_CREATE_COMMENT',
        );
        $replace    = array(
            new_date(),
            '@domain' == ($cookie_domain = $sys_config['sys_cookie_domain']) ? 'WEB_DOMAIN_SCOPE' : "'{$cookie_domain}'",
            '@domain' == ($session_cookie_domain = $sys_config['sys_session_cookie_domain']) ? 'WEB_DOMAIN_SCOPE' : "'{$session_cookie_domain}'",
            0 === strpos('http://', $v = $sys_config['sys_base_admin_entry']) ? "'{$v}'" : "BASE_SITE_URL . '{$v}'",
            '后台自动生成，请毋修改。最后更新时间: ' . new_date()
        );
        $content    = str_replace($find, $replace, $content);

        file_put_contents(INCLUDE_PATH . 'app_config.php', $content);//写文件

        if (!APP_DEBUG && is_file(RUNTIME_FILE)) {//constants.php已经包含进运行时文件。干掉
            unlink(RUNTIME_FILE);
        }
    }//end publicDefineSystemConstantsAction

    /**
     * 所属表单
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-01 16:58:59
     * @lastmodify      2013-01-22 10:29:57 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicFieldAction() {
        $field_id = Filter::int('node', 'get');
        $data    = $this->_getTreeData($field_id, 'nochecked');

        //增加顶级表单
        $this->_unshift && !$field_id && array_unshift($data, array('field_id' => 0, $this->_name_column => L('TOP_LEVEL_FIELD'), 'leaf' => true));

        $this->_ajaxReturn(true, '', $data);
    }

    /**
     * 加载表单域
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-27 14:06:37
     * @lastmodify      2013-01-22 10:30:15 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicFormAction() {
        $controller = Filter::string('controller', 'get');//控制器
        $action     = Filter::string('action', 'get');//操作方法
        $callback   = Filter::string('callback', 'get');//jsonp callback
        $error_msg  = L('GET,CONTROLLER_NAME_FIELD,DATA,FAILURE') . "controller={$controller}&action={$action}";

        if ($controller && $action) {
            $this->_checkAdminPriv($controller, $action);//权限判断 by mashanlin on 2012-08-30 11:04:14

            $data   = $this->_model->alias('f')->field('f.field_id,f.menu_id,f.field_code,f.input_value,f.field_name,f.input_name,m.controller,m.action')
            ->join(TB_MENU . ' AS m ON f.menu_id=m.menu_id')
            ->where("m.controller='{$controller}' AND m.action='{$action}' AND f.is_enable=1")->
            order('f.sort_order ASC,f.field_id ASC')->select();
            $field  = array();

            $data === false && $this->_sqlErrorExit($error_msg);

            foreach ($data as $item) {
                $input_name = $item['input_name'];//输入框名称
                $field_name = $item['field_name'];//表单域名
                $field_code = $item['field_code'];//js代码

                if (!$field_code = $this->_shortcutCode($field_code)) {
                    continue;
                }

                $find       = array('@fieldLabel', '@field_name', '@input_name', '@value');
                $field_label= sprintf('<a class="a-font-000" href="#controller=field&action=add&field_id=%d&back=%s">%s</a>', $item['field_id'], urlencode("#controller={$controller}&action={$action}"), $field_name, $input_name);
                $replace    = array($field_label, $field_name, $input_name, strpos($item['input_value'], PHP_EOL) ? str_replace(PHP_EOL, '\\' . PHP_EOL, $item['input_value']) : $item['input_value']);
                $field_code = trim(str_ireplace($find, $replace, $field_code));
                $field[]    = strpos($field_code, 'extField.') === 0 ? $field_code : '{' . $field_code . '}';
            }//end foreach

            if (isset($item)) {
                $field[] = "{xtype: 'hidden', name: '_menu_id', value: {$item['menu_id']}}";
            }

            $field = "{$callback}(function () {var extField = Yab.Field.field();return " . ($field ? '[' . join(',' . PHP_EOL . PHP_EOL, $field) . ']' : $this->_model->table(TB_MENU)->where("controller='{$controller}' AND action='{$action}'")->getField('menu_id')) . ';})';
            exit($field);
        }
        else {
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) .$log_msg;
            trigger_error($log, E_USER_ERROR);
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            $this->_ajaxReturn(false);
        }
    }//end publicFormAction

    /**
     * 保存值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-29 13:47:39
     * @lastmodify      2013-01-22 10:30:42 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicSaveValueAction() {
        $error   = L('SAVE,FAILURE');//保存失败错误
        $menu_id = Filter::int('_menu_id');//菜单id
        $menu    = $this->cache(0, 'Menu');//菜单数据

        if (!isset($menu[$menu_id])) {//菜单不存在
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L("SAVE,CONTROLLER_NAME_FIELD,VALUE,FAILURE,%:(,MENU,%menu_id={$menu_id}}),NOT_EXIST");
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, $error);
        }

        $menu_info  = $menu[$menu_id];//菜单信息
        $controller = $menu_info['controller'];//控制器
        $action     = $menu_info['action'];//操作方法

        $this->_checkAdminPriv($controller, $action);//权限判断 by mashanlin on 2012-08-30 11:06:25

        $menu = $this->nav($menu_id, 'menu_name', 'Menu');//菜单名
        $info = L('CONTROLLER_NAME_FIELD,VALUE') . "({$menu})";//信息

        if (empty($_POST)) {//非法数据
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L('SAVE') . $info . L('FAILURE,%:,INVALID,DATA');
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, $error);
        }

        /*$field_arr  = $this->_model->alias('f')
        ->field('f.input_name,f.field_id,f.field_name,input_value,f.validate_rule,f.auto_operation')
        ->join(TB_MENU . ' AS m ON f.menu_id=m.menu_id')
        ->where("m.menu_id={$menu_id} AND f.is_enable=1")
        ->index($this->_pk_field)->select();*/
        //走缓存 by mrmsl on 2012-09-05 14:05:14
        $field_arr  = array_filter($this->cache(), create_function('$v', 'return $v["menu_id"] == ' . $menu_id . ' && $v["is_enable"];'));

        if (empty($field_arr)) {//查询出错或表单域为空

            if ($field_arr === false) {//查询出错
                $this->_sqlErrorExit(L('GET') . $menu . L('CONTROLLER_NAME_FIELD,FAILURE'), $error);
            }
            else {
                $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L('SAVE') . $info . L('FAILURE,%:,CONTROLLER_NAME_FIELD,IS_EMPTY');
                trigger_error($log, E_USER_ERROR);
            }

            $this->_ajaxReturn(false, $error);
        }

        $this->_model->saveValueCheckCreate($field_arr);//设置自动验证

        $checked = $this->_model->checkCreate('_validateSaveValue');//执行自动验证

        $checked !== true && $this->_ajaxReturn(false, $checked);//未通过验证

        $this->_model->autoOperation($_POST, Model::MODEL_BOTH);//自动填充 by mrmsl on 2012-09-07 13:07:57

        $log           = '';//管理日志
        $pk_field      = $this->_pk_field;//主键

        foreach ($field_arr as $field_id => $item) {
            $input_name  = $item['input_name'];

            if (isset($_POST[$input_name])) {
                $old_value = $item['input_value'];//原值
                $new_value = $_POST[$input_name];//新值

                if ($old_value != $new_value) {//值不相等
                    $this->_model->save(array($pk_field => $field_id, 'input_value' => $new_value));//更新
                    $log .= ", {$input_name}: {$old_value} => {$new_value}";//管理日志
                }
            }
        }

        $this->cache(null, null, null);//重新生成缓存
        //回调 by mrmsl on 2012-09-22 15:34:53
        method_exists($this, ($callback = '_saveValueCallback' . ucfirst($controller))) && $this->$callback($menu_info);

        $this->_model->addLog(L('SAVE') . $info . L('SUCCESS') . ($log ? $log : ''));
        $this->_ajaxReturn(true, L('SAVE,SUCCESS'));
    }//end publicSaveValueAction
}