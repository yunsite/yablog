<?php
/**
 * 系统菜单控制器类
 *
 * @file            MenuController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-21 14:36:19
 * @lastmodify      $Date$ $Author$
 */

class MenuController extends CommonController {
    /**
     * @var bool $_get_children_ids true取所有子表单， CommonController->delete()会用到。默认true
     */
    protected $_get_children_ids   = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认menu_name
     */
    protected $_name_column        = 'menu_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'show'     => 'add',//显示隐藏
    );

    /**
     * array_walk回调函数，分割权限为数组形式
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:45:19 by mrmsl
     *
     * @param array $v 菜单信息
     *
     * @return void 无返回值
     */
    private function _explodePriv(&$v) {
        $v['priv'] = $v['priv'] ? array_combine(explode(',', $v['priv']), explode(',', $v['priv_letter'])) : array();
        unset($v['priv_letter']);
    }

    /**
     * 获取菜单树数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:45:37 by mrmsl
     *
     * @param $data 初始数据。默认array()，读菜单树缓存
     *
     * @return array 菜单树数据
     */
    private function _getTree($data = array()) {
        $data = $data ? $data : $this->cache(0, CONTROLLER_NAME . '_tree');
        $tree = array();
        $k    = 0;

        static $menu_id      = null;
        static $include_self = null;

        $menu_id      = $menu_id === null  ? Filter::int($this->_pk_field, 'get') : $menu_id;
        $include_self = $include_self === null ? isset($_GET['include_self']) : $include_self;
        $role_id      = $this->_admin_info['role_id'];

        foreach ($data as $menu) {

            //(站长或有该菜单权限)及(如果所属菜单，则干掉自己，否则显示状态) by mrmsl on 2012-08-16 11:55:22
            $has_priv   = ADMIN_ROLE_ID == $role_id || isset($menu['priv'][$role_id]);

            if ($has_priv && ($this->_unshift && !$include_self ? $menu_id != $menu[$this->_pk_field] : $menu['is_show'])) {
                $tree[$k] = array(
                    'menu_id' => $menu['menu_id'],
                    'parent_id' => $menu['parent_id'],
                    'menu_name' => $menu['menu_name'],
                    'action'    => $menu['action'],//增加action Yab.controller.Tree itemclick调到 by mrmsl on 2012-08-20 12:42:42
                    'href'      => $menu['href'],
                    'leaf'      => $menu['leaf'],
                );

                if (!empty($menu['data'])) {
                    $tree[$k]['data'] = $this->_getTree($menu['data']);
                }

                $k++;
            }
        }

        return $tree;
    }//end _getTree

    /**
     * 获取角色权限菜单树数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:46:00 by mrmsl
     *
     * @param array $data      菜单数组
     * @param int   $role_id   角色id
     * @param array $role_priv 角色权限
     *
     * @return array 菜单树数据
     */
    private function _priv($data = array(), $role_id, $role_priv) {
        $tree        = array();
        $k           = 0;

        foreach ($data as $menu) {
            $tree[$k] = array(
                'menu_id' => $menu['menu_id'],
                'menu_name' => $menu['menu_name'],
                'leaf'  => $menu['leaf'],
                'expanded'  => true,
                'checked'   => $role_id == ADMIN_ROLE_ID ? true : $role_id && $role_priv && array_key_exists($menu['menu_id'], $role_priv),
            );

            if (!empty($menu['data'])) {
                $tree[$k]['data'] = $this->_priv($menu['data'], $role_id, $role_priv);
            }

            $k++;
        }

        return $tree;
    }//end _priv

    /**
     * 获取角色权限信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:46:32 by mrmsl
     *
     * @param mixed  $role_id 权限角色id串(数组)
     * @param string $key     权限信息key值。默认''
     *
     * @return mixed 如果$key为空，返回整个权限信息数组，否则返回指定信息
     */
    protected function _getMenuPriv($role_id, $key = '') {
        $priv = $this->diffMenuPriv('', $role_id);
        $priv['msg'] = substr($priv['msg'], strlen(L('%[,DELETE,%]')));

        return $key ? $priv[$key] : $priv;
    }

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {
        $this->_treeInfoCallback($info);
    }

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:46:42 by mrmsl
     *
     * @return void 无返回值
     */
    public function addAction() {
        $this->_commonAddTreeData('controller,action,menu_name,parent_name,is_show,sort_order');
    }

    /**
     * 生成缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:46:49 by mrmsl
     *
     * @return object this
     */
    public function createAction() {
        $data      = $this->_model->alias('a')->join('LEFT JOIN ' . TB_ADMIN_ROLE_PRIV . ' AS b ON a.menu_id=b.menu_id')
        ->join('LEFT JOIN ' . TB_ADMIN_ROLE . ' AS c ON b.role_id=c.role_id')
        ->field(array('a.*', "CONCAT('#controller=', a.controller, '&action=', a.action)" => 'href', 'GROUP_CONCAT(c.role_id)' => 'priv', 'GROUP_CONCAT(c.role_name)' => 'priv_letter'))
        ->group('a.menu_id')
        ->order('a.parent_id ASC,a.is_show DESC,a.sort_order ASC, a.menu_id ASC')
        ->key_column($this->_pk_field)->select();

        if ($data === false) {
            $this->_model->addLog();
            $this->_ajaxReturn(false, L('CREATE_MENU_CACHE,FAILURE'), 'EXIT');
        }

        array_walk($data, array($this, '_explodePriv'));
        $tree_data = Tree::array2tree($data, $this->_pk_field);//树形式

        return $this->cache(null, null, $data)->cache(null, $this->_getControllerName() . '_tree', $tree_data);
    }

    /**
     * 比较两次权限差异
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:47:25 by mrmsl
     *
     * @param array|string $old_role_id 老权限角色id串(数组)
     * @param array|string $new_role_id 新权限角色id串(数组)
     *
     * @return array 权限信息数组
     */
    public function diffMenuPriv($old_role_id, $new_role_id) {
        $cache_data  = $this->cache(0, 'Role');//角色缓存
        $old_role_id = is_array($old_role_id) ? $old_role_id : explode(',', $old_role_id);//
        $new_role_id = is_array($new_role_id) ? $new_role_id : explode(',', $new_role_id);
        $diff_old    = array_diff($old_role_id, $new_role_id);//删除的
        $diff_new    = array_diff($new_role_id, $old_role_id);//新增的
        $delete_msg  = '';//删除
        $delete_role = array();//删除角色id串
        $add_msg     = '';//新增
        $add_role    = array();//新增角色id串
        $priv_letter = array();//权限串`controller``action`

        foreach ($diff_old as $role_id) {//删除的

            if (isset($cache_data[$role_id])) {
                $role          = $cache_data[$role_id];
                $delete_msg   .= ($delete_msg ? ', ' : L('%[,DELETE,%]')) . $role['role_name'] . "({$role_id})";
                $delete_role[] = $role_id;
            }
        }

        foreach ($diff_new as $role_id) {//新增的

            if (isset($cache_data[$role_id])) {
                $role        = $cache_data[$role_id];
                $add_msg    .= ($add_msg ? ', ' : L('%[,ADD,%]')) . $role['role_name'] . "({$role_id})";
                $add_role[]  = $role_id;
                $priv_letter[] = $role['role_name'];
            }
        }

        return array(
            'msg'     => $delete_msg . $add_msg,//增删信息
            'delete'     => $delete_role,//删除角色id
            'add'        => $add_role,//新增角色id
            'priv_letter'=> $priv_letter,//权限串
        );
    }//end diffMenuPriv

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
        $menu_id     = Filter::int('node', 'get');//菜单id
        $column_arr  = array(
            'menu_name' => 'menu_name',
            'model'     => 'controller',
            'view'      => 'action'
        );

        //搜索 by mrmsl on 2012-07-24 18:02:02
        if (isset($_GET['is_show'])) {
            $column      = Filter::string('column', 'get');//搜索字段
            $keyword     = Filter::string('keyword', 'get');//搜索关键字
            $is_show     = Filter::int('is_show', 'get');//是否显示 by mrmsl on 2012-09-15 12:14:57

            if ($is_show != -1) {
                $this->_queryTreeWhere = array('is_show' => array('eq', $is_show));
                $this->_queryTree($column_arr[$column], $keyword);
            }
            elseif ($column && $keyword && array_key_exists($column, $column_arr)) {
                $this->_queryTree($column_arr[$column], $keyword);
            }
        }
        elseif ($menu_id) {
            $this->_ajaxReturn(true, '', $this->_getTreeData($menu_id, false));
        }

        $data = $this->cache(0, CONTROLLER_NAME . '_tree');

        $this->_ajaxReturn(true, '', $data, count($this->cache()));
    }//end listAction

    /**
     * 角色菜单权限
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:48:39 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicPrivAction() {
        $data        = $this->cache(0, CONTROLLER_NAME . '_tree');
        $role_id     = Filter::int('role_id', 'get');
        $role_info   = $this->cache($role_id, 'Role');
        $role_priv   = $role_id && $role_info && $role_info['priv'] ? $role_info['priv'] : false;

        $this->_ajaxReturn(true, '', $this->_priv($data, $role_id, $role_priv));
    }

    /**
     * 导航功能菜单
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:48:51 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicTreeAction() {
        $data = $this->_getTree();

        //增加顶级菜单
        $this->_unshift && array_unshift($data, array('menu_id' => 0, 'menu_name' => isset($_GET['emptyText']) ? Filter::string('emptyText', 'get') : L('TOP_LEVEL_MENU'), 'leaf' => true));

        $parent_id = Filter::int('parent_id', 'get');

        //添加指定菜单子菜单，获取指定菜单信息by mashanlng on 2012-08-21 13:53:35
        if ($parent_id && ($parent_info = $this->cache($parent_id))) {
            $parent_info = array(
                 'menu_id'     => $parent_id,
                 'controller'  => $parent_info['controller'],
                 'parent_name' => $parent_info['menu_name'],
                 '_priv_id'    => join(',', array_keys($parent_info['priv'])),
                 'priv'        => join(',', $parent_info['priv'])
            );
            $this->_ajaxReturn(array('data' => $data, 'parent_data' => $parent_info));
        }

        $this->_ajaxReturn(true, '', $data);
    }

    /**
     * 显示/隐藏
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:49:07 by mrmsl
     *
     * @return void 无返回值
     */
    public function showAction() {
        $this->_setOneOrZero();
    }
}