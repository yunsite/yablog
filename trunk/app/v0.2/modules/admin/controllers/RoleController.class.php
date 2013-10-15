<?php
/**
 * 管理角色控制器类
 *
 * @file            RoleController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-21 14:36:19
 * @lastmodify      $Date$ $Author$
 */

class RoleController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->cache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache    = true;
    /**
     * @var string $_exclude_delete_id 不可删除管理角色id。默认ADMIN_ROLE_ID
     */
    protected $_exclude_delete_id   = ADMIN_ROLE_ID;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认role_name
     */
    protected $_name_column         = 'role_name';
    /**
     * @var array $_no_need_priv_action 不需要验证权限方法。默认array('setCache')
     */
    protected $_no_need_priv_action = array('setCache');
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map            = array(
         'delete'  => 'add',//删除
         'info'    => 'add',//具体信息
    );

    /**
     * array_walk回调函数，分割权限为数组形式
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:57:32 by mrmsl
     *
     * @param array $v 角色信息
     *
     * @return void 无返回值
     */
    private function _explodePriv(&$v) {
        $v['priv'] = $v['priv'] ? array_combine(explode(',', $v['priv']), explode(',', $v['priv_letter'])) : array();
        unset($v['priv_letter']);
    }

    /**
     * 获取角色权限信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:57:54 by mrmsl
     *
     * @param mixed  $menu_id 权限菜单id串(数组)
     * @param string $key     权限信息key值。默认''
     *
     * @return mixed 如果$key为空，返回整个权限信息数组，否则返回指定信息
     */
    private function _getRolePriv($menu_id, $key = '') {
        $priv = $this->diffRolePriv('', $menu_id);
        $priv['msg'] = substr($priv['msg'], strlen(L('%[,DELETE,%]')));

        return $key ? $priv[$key] : $priv;
    }

    /**
     * 删除后置操作，重建管理员缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-28 15:13:55
     * @lastmodify      2013-01-22 10:58:44 by mrmsl
     *
     * @param array $pk_id 管理员id
     *
     * @return void 无返回值
     */
    protected function _afterDelete($pk_id) {
        $admin = $this->cache(0, 'Admin');

        foreach($admin as $key => $item) {

            if (in_array($item[$this->_pk_field], $pk_id)) {
                unset($admin[$key]);
            }
        }

        $this->cache(null, 'Admin', $admin);
    }

    /**
     * 设置写缓存数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-05 14:31:55
     * @lastmodify      2013-01-22 10:59:07 by mrmsl
     *
     * @return array 缓存数据
     */
    protected  function _setCacheData() {
        $data = $this->_model->alias('a')->join('LEFT JOIN ' . TB_ADMIN_ROLE_PRIV . ' AS b ON a.role_id=b.role_id')
        ->join('LEFT JOIN ' . TB_MENU . ' c ON b.menu_id=c.menu_id')
        ->field("a.*,GROUP_CONCAT(b.menu_id) AS priv,GROUP_CONCAT(c.controller,c.action) AS priv_letter")
        ->group('a.role_id')
        ->order('a.sort_order ASC, a.role_id ASC')
        ->index($this->_pk_field)->select();

        $data && array_walk($data, array($this, '_explodePriv'));

        return $data;
    }

    /**
     * 添加或保存
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:59:48 by mrmsl
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->startTrans()->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//角色id
        $this->_model->_priv_id = map_int($this->_model->_priv_id, true);//菜单权限
        $priv_id   = $this->_model->_priv_id;
        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = $this->_name_column . ',memo,sort_order';//比较差异字段
        $cache_data= $this->cache();

        if ($pk_value) {//编辑

            if ($pk_value == ADMIN_ROLE_ID && $this->_admin_info[$pk_field] != ADMIN_ROLE_ID) {//不可编辑指定角色。增加当前角色id判断 by mrmsl on 2012-07-05 08:50:27
                $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L('TRY,EDIT,CONTROLLER_NAME_ROLE') . "{$pk_field}: {$pk_value}";
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, L('EDIT,FAILURE'));
            }

            if (!isset($cache_data[$pk_value])) {//角色不存在
                $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L("EDIT,CONTROLLER_NAME_ROLE,FAILURE,%: ,INVALID_PARAM,%:,CONTROLLER_NAME_ROLE,%{$pk_field}({$pk_value}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, L('EDIT,FAILURE'));
            }

            $role_info = $cache_data[$pk_value];

            if ($this->_model->save() === false) {//更新出错
                $this->_sqlErrorExit(L('EDIT,CONTROLLER_NAME_ROLE') . "{$role_info[$this->_name_column]}({$pk_value})" . L('FAILURE'), L('EDIT,FAILURE'));
            }

            $diff_priv = $this->diffRolePriv(array_keys($role_info['priv']), $priv_id);
            $diff      = $this->_dataDiff($role_info, $data, $diff_key) . ($diff_priv['msg'] ? 'priv => ' . $diff_priv['msg'] : '');//差异

            //权限有变更
            $diff_priv['msg'] && $this->_model->setRolePriv($pk_value, $priv_id);

            //管理员操作日志
            $this->_model->addLog(L('EDIT,CONTROLLER_NAME_ROLE')  . "{$role_info[$this->_name_column]}({$pk_value})." . $diff. L('SUCCESS'));

            $this->cache(null, null, null)->_ajaxReturn(true, L('EDIT,SUCCESS'));
        }
        else {
            $priv        = $this->_getRolePriv($priv_id);
            $insert_data = "{$this->_name_column} => {$data[$this->_name_column]}, sort_order => " . (isset($data['sort_order']) ? $data['sort_order'] : -1) . " memo => {$data['memo']}" . ($priv['msg'] ? 'priv => ' . $priv['msg'] : '');//数据

            if (($insert_id = $this->_model->add()) === false) {//插入出错
                $this->_sqlErrorExit(L('ADD,CONTROLLER_NAME_ROLE') . $insert_data . L('FAILURE'), L('ADD,FAILURE'));
            }

            //权限
            $priv_id && $this->_model->setRolePriv($insert_id, $priv_id);

            $this->_model->addLog(L('ADD,CONTROLLER_NAME_ROLE') . $insert_data . L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, L('ADD,SUCCESS'));
        }
    }//end add

    /**
     * 比较两次权限差异
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:00:01 by mrmsl
     *
     * @param mixed $old_menu_id 老权限菜单id串(数组)
     * @param mixed $new_menu_id 新权限菜单id串(数组)
     *
     * @return array 权限信息数组
     */
    public function diffRolePriv($old_menu_id, $new_menu_id) {
        $cache_data  = $this->cache(0, 'Menu');//菜单缓存
        $old_menu_id = is_array($old_menu_id) ? $old_menu_id : explode(',', $old_menu_id);//
        $new_menu_id = is_array($new_menu_id) ? $new_menu_id : explode(',', $new_menu_id);
        $diff_old    = array_diff($old_menu_id, $new_menu_id);//删除的
        $diff_new    = array_diff($new_menu_id, $old_menu_id);//新增的
        $delete_msg  = '';//删除
        $delete_menu = array();//删除菜单id串
        $add_msg     = '';//新增
        $add_menu    = array();//新增菜单id串
        $priv_letter = array();//权限串`controller``action`

        foreach ($diff_old as $menu_id) {//删除的

            if (isset($cache_data[$menu_id])) {
                $menu          = $cache_data[$menu_id];
                $delete_msg   .= ($delete_msg ? ', ' : L('%[,DELETE,%]')) . $menu['menu_name'] . "({$menu_id})";
                $delete_menu[] = $menu_id;
            }
        }


        foreach ($diff_new as $menu_id) {//新增的

            if (isset($cache_data[$menu_id])) {
                $menu        = $cache_data[$menu_id];
                $add_msg    .= ($add_msg ? ', ' : L('%[,ADD,%]')) . $menu['menu_name'] . "({$menu_id})";
                $add_menu[]  = $menu_id;
                $priv_letter[] = strtolower($menu['controller'] . $menu['action']);
            }
        }

        return array(
            'msg'        => $delete_msg . $add_msg,//增删信息
            'delete'     => $delete_menu,//删除菜单id
            'add'        => $add_menu,//新增菜单id
            'priv_letter'=> $priv_letter,//权限串
        );
    }//end diffRolePriv

    /**
     * 列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-28 11:48:07
     * @lastmodify      2013-01-22 11:00:14 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $data = array_values($this->cache());
        $this->_unshift && array_unshift($data, array($this->_pk_field => 0, $this->_name_column => isset($_POST['emptyText']) ? Filter::string('emptyText') : L('PLEASE_SELECT')));
        $this->_ajaxReturn(true, '', $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {

        if ($info['priv']) {
            $info['_priv_id'] = join(',', array_keys($info['priv']));
            $menu_arr  = $this->cache(0, 'Menu');
            $priv_menu = '';

            foreach($info['priv'] as $menu_id => $item) {
                $priv_menu .= ',' . $menu_arr[$menu_id]['menu_name'];
            }

            $info['priv'] = substr($priv_menu, 1);
            unset($menu_arr, $priv_menu);
        }
        else {
            $info['_priv_id'] = '';
        }
    }

    /**
     * 编辑菜单，获取管理员权限
     *
     * @author          mrmsl
     * @date            2012-06-21 17:50:03
     * @lastmodify      2013-01-22 11:00:35 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicPrivAction() {
        $data        = $this->cache();
        $menu_id     = Filter::int('menu_id', 'get');
        $menu_info   = $this->cache($menu_id, 'Menu');
        $menu_priv   = $menu_id && $menu_info && $menu_info['priv'] ? array_keys($menu_info['priv']) : false;
        $tree        = array();

        foreach ($data as $role_id => $item) {
            $tree[] = array(
                'id'        => $role_id,
                'text'      => $item['role_name'],
                'leaf'      => true,
                'iconCls'   => 'icon-none',
                'checked'   => $role_id == ADMIN_ROLE_ID || $menu_priv && in_array($role_id, $menu_priv),
            );
        }

        $this->_ajaxReturn(true, '', $tree);
    }
}