<?php
/**
 * 语言包模块控制器类
 *
 * @file            LanguageModules.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-21 14:36:19
 * @lastmodify      $Date$ $Author$
 */

class LanguageModulesController extends CommonController {
    /**
     * @var bool $_get_children_ids true取所有子表单， CommonController->delete()会用到。默认true
     */
    protected $_get_children_ids   = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认module_name
     */
    protected $_name_column        = 'module_name';
    /**
     * @var string $_exclude_delete_id 不可删除id。默认array('whole_site' => 1, 'front' => 2, 'admin' => 3)
     */
    protected $_exclude_delete_id  = array(
        'whole_site'    => 1,//整站
        'front'         => 2,//前台
        'admin'         => 3,//后台
    );
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'show'     => 'add',//显示隐藏
    );

    /**
     * 生成语言项js文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-20 10:56:55
     *
     * @param   array       $data       js语言项数据
     *
     * @return void 无返回值
     */
    private function _buildScriptItems($data) {

        if ($data) {
            $js_data  = array();
            $lang_arr   = C('LANGUAGE_ARR');

            foreach($data as $k => $v) {

                if (!in_array($k, $lang_arr)) {
                    list($module, $lang) = explode('.', $k);

                    $js_data[$k] = array_merge($data[$k], $data[$lang]);
                }
            }

            foreach($js_data as $filename => $content) {
                array2js($content, 'L', WEB_JS_LANG_PATH . $filename . '.js');
            }
        }
    }

    /**
     * combo store数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-20 10:56:55
     *
     * @return void 无返回值
     */
    private function _combo() {
        $module_id  = Filter::int('module_id', 'get');
        $parent_id  = Filter::int('parent_id', 'get');
        $cache      = $this->_getCache();

        if ($module_id) {

            if (in_array($module_id, $this->_exclude_delete_id)) {
                $cache = array();
            }
            else {
                unset($cache[$module_id]);
            }

        }
        elseif (isset($_GET['add'])) {//添加模块,干掉二级模块,仅一级模块可增加子模块
            $cache_copy = array();

            foreach($this->_exclude_delete_id as $item) {
                $cache_copy[$item] = $cache[$item];
            }

            $cache = $cache_copy;
        }

        $data       = $cache;

        //增加顶级菜单
        $this->_unshift && array_unshift($data, array('module_id' => 0, 'parent_id' => -1, 'module_name' => isset($_GET['emptyText']) ? Filter::string('emptyText', 'get') : L('PARENT_LANGUAGEMODULES'), 'leaf' => true));

        C('array2tree_unset_checked', true);
        $data = Tree::array2tree($data, $this->_pk_field);

        //添加子模块，获取模块信息
        if ($parent_id && isset($cache[$parent_id]) && ($parent_info = $cache[$parent_id])) {
            $parent_info = array(
                 'module_id'     => $parent_id,
                 'parent_name' => $parent_info['module_name'],
            );
            $this->_ajaxReturn(array('data' => $data, 'parent_data' => $parent_info));
        }

        $this->_ajaxReturn(true, '', $data);
    }//end _combo

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {
        $this->_treeInfoCallback($info, 'module_name');
    }

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @data            2013-06-19 16:49:43
     *
     * @return void 无返回值
     */
    public function addAction() {
        $this->_commonAddTreeData('module_name,parent_name,sort_order,memo', 'module_name');
    }

    /**
     * 生成语言包
     *
     * @author          mrmsl <msl-138@163.com>
     * @data            2013-06-21 16:03:22
     *
     * @return void 无返回值
     */
    public function buildAction() {
        $module_id   = Filter::string($this->_pk_field);
        $module_id   = 'all' == $module_id ? $this->_exclude_delete_id : map_int(Filter::string($this->_pk_field), true);

        if (!$module_id) {
            $this->_model->addLog(L('PRIMARY_KEY,DATA,IS_EMPTY'), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('BUILD,LANGUAGE_ITEM,CACHE,FAILURE'));
        }

        if ($intersect = array_intersect($this->_exclude_delete_id, $module_id)) {//是否包含1,2,3

            foreach ($intersect as $v) {
                $module_id = array_merge($module_id, $this->_getChildrenIds($v, false, true));
            }

            $module_id = array_unique($module_id);
        }

        $modules    = $this->_getCache();
        $error      = '';
        $log        = '';

        foreach($module_id as $k => $v) {//验证语言模块

            if (isset($modules[$v])) {
                $item   = $modules[$v];
                $log   .= ",{$item['module_name']}({$item[$this->_pk_field]})";
            }
            else {
                unset($module_id[$k]);
                $error .= ',id(' . $v . ')';
            }
        }


        if (!$module_id) {
            $this->_model->addLog(L('PRIMARY_KEY,DATA,IS_EMPTY'), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('BUILD,LANGUAGE_ITEM,CACHE,FAILURE'));
        }

        $path_arr   = array_flip($this->_exclude_delete_id);
        $lang_arr   = C('LANGUAGE_ARR');
        $js_data    = array();//生成语言项js文件
        $php_data   = array();//生成语言项php文件

        foreach ($this->_getCache(0, 'LanguageItems') as $v) {
            $_module_id = $v['module_id'];
            $node_arr   = explode(',', $modules[$_module_id]['node']);
            $first_node = $node_arr[0];
            $filename   = $modules[$_module_id]['module_name'];

            if (1 == $first_node) {
                $whole_site = true;
                $php_key    = '';
                $js_key     = '';
            }
            else {
                $php_key = $path_arr[$first_node] . DS;
                $js_key  = $path_arr[$first_node] . '.';
            }

            $var_name   = strtoupper($v['var_name']);

            foreach ($lang_arr as $lang) {
                $_v = '' === $v['var_value_' . $lang] ? $var_name : $v['var_value_' . $lang];

                if (in_array($_module_id, $module_id)) {
                    $key = $php_key . $lang . (isset($whole_site) ? '' : DS . $filename);
                    $php_data[$key][$var_name] = $_v;
                }

                if ($v['to_js']) {
                    $js_data[$js_key . $lang][$var_name] = $_v;
                }
            }
        }//end foreach

        foreach($php_data as $key => $content) {
            F($key, $content, LANG_PATH);
        }

        $this->_buildScriptItems($js_data);

        $error && $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $error . L('NOT_EXIST'));

        $this->_model->addLog(L('BUILD,LANGUAGE_ITEM,CACHE') . $log . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
        $this->_ajaxReturn(true, L('BUILD,SUCCESS'));
    }//end buildAction

    /**
     * 生成缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-19 17:47:25
     *
     * @return object this
     */
    public function createAction() {
        $data      = $this->_model
        ->order('parent_id ASC, sort_order ASC, module_id ASC')
        ->key_column($this->_pk_field)->select();

        if ($data === false) {
            $this->_model->addLog();
            $this->_ajaxReturn(false, L('CREATE_LANGUAGEMODULES_CACHE,FAILURE'));
        }

        $tree_data = Tree::array2tree($data, $this->_pk_field);//树形式

        return $this->_setCache($data)->_setCache($tree_data, CONTROLLER_NAME . '_tree');
    }

    /**
     * 列表管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-28 16:45:42
     * @lastmodify      2013-01-22 10:48:23 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $data = $this->_getCache(0, CONTROLLER_NAME . '_tree');

        if (isset($_GET['combo'])) {
            $this->_combo();
        }

        $this->_ajaxReturn(true, '', $data, count($this->_getCache()));
    }//end listAction
}