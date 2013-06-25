<?php
/**
 * 语言项模块控制器类
 *
 * @file            LanguageItems.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-21 11:03:19
 * @lastmodify      $Date$ $Author$
 */

class LanguageItemsController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->_setCache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache   = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认item_name
     */
    protected $_name_column        = 'var_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'show'     => 'add',//显示隐藏
    );

    /**
     * 删除后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-25 10:07:35
     *
     * @param array $pk_id 主键值
     *
     * @return void 无返回值
     */
    protected function _afterDelete($pk_id) {
        $items      = $this->_getCache();
        $module_id  = array();

        foreach($items as $k => $v) {

            if (in_array($k, $pk_id)) {
                $module_id[] = $v['module_id'];
            }
        }

        C(array(APP_FORWARD => true, 'T_MODULE_ID' => $module_id));
        $this->forward('LanguageModules', 'build');
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterSetField($field, $value, $pk_id) {
        C(APP_FORWARD, true);
        $this->forward('LanguageModules', 'languageItemsAfterSetField');
    }

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {

        if ($menu_info = $this->_getCache($info['module_id'], 'LanguageModules')) {
            $info['module_name'] = $menu_info['module_name'];
        }
    }

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @data            2013-06-21 11:05:10
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//id

        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'module_name,var_name,var_value_zh_cn,var_value_en,sort_order,memo,to_js';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('LANGUAGE_ITEM,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if (!$module_info = $this->_getCache($module_id = $this->_model->module_id, 'LanguageModules')) {//语言包模块不存在
            $this->_model->addLog($log_msg . '<br />' . L("INVALID_PARAM,%:,LANGUAGE_MODULE,%module_id({$module_id}),NOT_EXIST"), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, $error_msg);
        }

        $data['module_name'] = $module_info['module_name'];//语言包模块名

        if ($pk_value) {//编辑

            if (!$item_info = $this->_getCache($pk_value)) {//语言项不存在
                $this->_model->addLog($log_msg . '<br />' . L("INVALID_PARAM,%:,CONTROLLER_NAME,%{$pk_field}({$pk_value}),NOT_EXIST"), LOG_TYPE_INVALID_PARAM);
                $this->_ajaxReturn(false, $error_msg);
            }

            if ($this->_model->save() === false) {//更新出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . "{$item_info[$this->_name_column]}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $module_info = $this->_getCache($item_info['module_id'], 'LanguageModules');
            $item_info['module_name'] = $module_info['module_name'];//语言包模块名

            $diff = $this->_dataDiff($item_info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L('CONTROLLER_NAME')  . "{$item_info[$this->_name_column]}({$pk_value})." . $diff. L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->createAction();
            C(array(APP_FORWARD => true, 'T_MODULE_ID' => array($data['module_id'], $item_info['module_id'])));
            $this->forward('LanguageModules', 'build', array('all'));
            $this->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $diff = $this->_dataDiff($data, false, $diff_key);//数据

            if ($this->_model->add() === false) {//插入出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . $diff . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('CONTROLLER_NAME') . $diff . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->createAction();
            C(array(APP_FORWARD => true, 'T_MODULE_ID' => array($data['module_id'])));
            $this->forward('LanguageModules', 'build', array('all'));
            $this->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }

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
        ->order('sort_order ASC, item_id ASC')
        ->key_column($this->_pk_field)->select();

        if ($data === false) {
            $this->_model->addLog();
            $this->_ajaxReturn(false, L('BUILD,LANGUAGE_ITEM,CACHE,FAILURE'));
        }


        return $this->_setCache($data);
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
        $sort           = Filter::string('sort', 'get', 'sort_order');//排序字段
        $order          = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
        $order          = toggle_order($order);
        $keyword        = Filter::string('keyword', 'get');//关键字
        $module_id      = Filter::int('module_id', 'get');//所属模块
        $column         = Filter::string('column', 'get');//搜索字段
        $where          = array();

        if ($keyword !== '' && in_array($column, array('var_name', 'var_value_zh_cn', 'var_value_en'))) {
            $where['a.' . $column] = $this->_buildMatchQuery('a.' . $column, $keyword, Filter::string('match_mode', 'get'));
        }

        if ($module_id) {

            !$this->_getCache($module_id, 'LanguageModules') && $this->_ajaxReturn(true, '', array(), 0);

            $where['a.module_id'] = $module_id;
        }

        $total      = $this->_model->alias('a')->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model->alias('a')
        ->join('JOIN ' . TB_LANGUAGE_MODULES . ' AS m ON a.module_id=m.module_id')
        ->where($where)->field('a.*,m.module_name')
        ->limit($page_info['limit'])
        ->order('a.' . $sort . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction

    /**
     * 本地环境,将之前的语言文件语言项入库
     *
     * @author          mrmsl <msl-138@163.com>
     * @data            2013-06-24 08:53:24
     *
     * @return void 无返回值
     */
    public function localInsertItemsAction() {

        if (!is_dir($dir = YAB_APP_PATH . 'language_backup/') || !IS_LOCAL) {
            return;
        }

        require(CORE_PATH . 'functions/dir.php');

        $file_arr   = scand_dir($dir);
        $module_arr = array(
            'front' => array(
                'blog'              => 24,
                'comments'          => 25,
                'common'            => 5,
            ),
            'admin' => array(
                'admin'             => 7,
                'adminloginhistory' => 8,
                'area'              => 9,
                'blog'              => 10,
                'category'          => 11,
                'comments'          => 12,
                'js'                => 6,
                'common'            => 6,
                'field'             => 13,
                'html'              => 14,
                'languageitems'     => 15,
                'languagemodules'   => 16,
                'log'               => 17,
                'login'             => 18,
                'mail'              => 19,
                'mailhistory'       => 20,
                'menu'              => 21,
                'miniblog'          => 22,
                'role'              => 23,
            ),
        );

        $values = '';

        foreach ($module_arr as $k => $v) {

            foreach($file_arr[$k]['zh_cn'] as $item) {
                $basename = basename($item, '.php');

                if ('guestbook' == $basename) {
                    continue;
                }

                foreach(include($item) as $key => $lang) {
                    $values .= ",({$v[$basename]},'{$key}','" . addslashes($lang) . "','')";
                };
            }
        }

        foreach(include(array_pop($file_arr)) as $key => $lang) {
            $values .= ",(1,'{$key}','" . addslashes($lang) . "','')";
        };

        if ($values) {
            $this->_model->startTrans();
            $db = $this->_model->getDb();
            $result1 = $db->execute('DELETE FROM ' . TB_LANGUAGE_ITEMS);
            $result2 = $db->execute('INSERT INTO ' . TB_LANGUAGE_ITEMS . '(module_id,var_name,var_value_zh_cn,var_value_en) VALUES' . substr($values, 1) . ' ON DUPLICATE KEY UPDATE module_id=module_id');

            if ($result1 && $result2 && $db->execute('UPDATE ' . TB_LANGUAGE_ITEMS . ' SET sort_order=item_id,to_js=1')) {
                $this->_model->commit();
            }
        }
    }//end localInsertItemsAction

    /**
     * 是否生成js
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-25 21:58:34
     *
     * @return void 无返回值
     */
    function toJsAction() {
        $field  = 'to_js';//字段
        $to_js  = Filter::int($field) ? 1 : 0;//1:生成;0:不生成
        $msg    = $to_js ? '' : L('NO');

        $this->_setField($field, $to_js, $msg . L('TO_JS'));
    }
}