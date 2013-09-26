<?php
/**
 * 管理员模型
 *
 * @file            Menu.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-29 09:46:19
 * @lastmodify      $Date$ $Author$
 */

class MenuModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'is_show'    => '_getCheckboxValue',//是否显示
        'sort_order' => '_unsigned#data',//排序 by mrmsl on 2012-09-10 16:53:47
    );
    /**
     * @var array $_db_fields
     * 数据表字段信息
     * filter: 数据类型，array(数据类型(string,int,float...),Filter::方法参数1,参数2...)
     * validate: 自动验证，支持多个验证规则
     *
     * @see Model.class.php create()方法对数据过滤
     * @see CommonModel.class.php __construct()方法设置自动验证字段_validate
     */
    protected $_db_fields = array (
        'menu_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'parent_id'        => array('filter' => 'int', 'validate' => 'unsigned#PLEASE_SELECT,PARENT_MENU'),//父级菜单id

        //菜单名称
        'menu_name'        => array('validate' => array('notblank#CONTROLLER_NAME_MENU', '_checkLength#CONTROLLER_NAME_MENU,NAME#value|0|30')),

        //控制器
        'controller'       => array('validate' => array('_checkController#PLEASE_ENTER,CONTROLLER#data', '_checkLength#CONTROLLER#value|0|20')),

        //备注
        'memo'             => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        //操作方法
        'action'           => array('validate' => array('_checkAction#PLEASE_ENTER,ACTION#data', '_checkLength#ACTION#value|0|20')),
        'is_show'          => array('filter' => 'int'),//是否显示
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
        '_priv_id'         => array('validate' => 'return#PERMISSION'),//权限
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认menu_id
     */
    protected $_pk_field        = 'menu_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_MENU
     */
    protected $_true_table_name = TB_MENU;//表

    /**
     * {@inheritDoc}
     */
    protected function _afterDelete($data, $options = array()) {
        $this->_module->createAction();
        C(APP_FORWARD, true);
        $this->_module->forward('Role', 'setCache');//角色缓存
    }

    /**
     * 新增数据后，将排序设为该记录自动增长id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-07 13:48:05
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterInsert($data, $options) {
        $this->_afterInserted($data, $options);
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterUpdate($data, $options) {
        $count = count($data);

        if ($count == 1) {

            if (isset($data['is_show'])) {//显示，隐藏
                $this->_module->createAction();
            }
        }
    }

    /**
     * 验证操作方法是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:52:29 by mrmsl
     *
     * @param string $action 操作方法
     * @param array  $data   _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkAction($action, $data) {

        if ($action === '') {//如果未输入，提示输入
            return false;
        }

        $action      = strtolower($action);
        $controller  = isset($data['controller']) ? strtolower($data['controller']) : '';
        $pk_value    = isset($data[$this->_pk_field]) ? $data[$this->_pk_field] : 0;
        $menu_arr    = $this->_getCache();

        foreach ($menu_arr as $menu_id => $menu) {

            if ($action == strtolower($menu['action']) && $controller == strtolower($menu['controller']) && $pk_value != $menu_id && $controller != '#' && $action != '#') {
                return L('EXIST_SAME_CONTROLLER_AND_ACTION') . ': <br /><span style="color: #666;font-weight: bold;">' . $this->_module->nav($menu_id, 'menu_name') . '</span>';
            }
        }

        return true;
    }

    /**
     * 验证控制器是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:52:38 by mrmsl
     *
     * @param string $controller 控制器
     * @param array  $data       _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkController($controller, $data) {

        if ($controller === '') {//如果未输入，提示输入
            return false;
        }

        $parent_id = isset($data['parent_id']) ? $data['parent_id'] : 0;
        $pk_value  = isset($data[$this->_pk_field]) ? $data[$this->_pk_field] : 0;
        $menu_arr  = $this->_getCache();
        $controller = strtolower($controller);

        foreach ($menu_arr as $menu_id => $menu) {

            if ($parent_id == 0 && $controller == strtolower($menu['controller']) && $pk_value != $menu_id && $menu['parent_id'] == 0 && $controller != '#') {
                return L('CONTROLLER,EXIST') . ': <br /><span style="color: #666;font-weight: bold;">' . $this->_module->nav($menu_id, 'menu_name') . '</span>';
            }
        }

        return true;
    }

    /**
     * 设置角色权限
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:52:56 by mrmsl
     *
     * @param int   $menu_id
     * @param mixed $role_id 角色id串(数组)
     *
     * @return void 无返回值
     */
    public function setMenuPriv($menu_id, $role_id) {
        if (!$role_id) {//无权限，直接删除
            $this->getDb()->execute('DELETE s FROM ' . TB_SHORTCUT . ' AS s,' . TB_ADMIN . ' AS a,' . TB_ADMIN_ROLE . " AS r WHERE s.admin_id=a.admin_id AND a.role_id=r.role_id AND s.menu_id={$menu_id} AND a.role_id!=" . ADMIN_ROLE_ID);
            $this->table(TB_ADMIN_ROLE_PRIV)->where('menu_id=' . $menu_id)->delete();
        }
        else {
            $role_data   = $this->_getCache(0, 'Role');
            $menu_data   = $this->_getCache($menu_id);
            $priv_arr    = $this->_module->diffMenuPriv($menu_data ? array_keys($menu_data['priv']) : '', $role_id);

            if ($delete = $priv_arr['delete']) {//删除的
                $role_id = join(',', $delete);
                $this->getDb()->execute('DELETE s FROM ' . TB_SHORTCUT . ' AS s,' . TB_ADMIN . ' AS a,' . TB_ADMIN_ROLE . " AS r WHERE s.admin_id=a.admin_id AND a.role_id=r.role_id AND a.role_id IN({$role_id}) AND s.menu_id={$menu_id} AND a.role_id!=" . ADMIN_ROLE_ID);

                if ($role_data) {

                    foreach($delete as $role_id) {

                        if (isset($role_data[$role_id])) {
                            unset($role_data[$role_id]['priv'][$menu_id]);
                        }
                    }
                }

                $this->table(TB_ADMIN_ROLE_PRIV)->where(array('menu_id' => $menu_id, 'role_id' => array('IN', $delete)))->delete();
            }

            if ($add = $priv_arr['add']) {//新增的
                $priv_letter = $priv_arr['priv_letter'];
                $add         = array_unique($add);
                $values      = '';

                foreach($add as $role_id) {

                    if (isset($role_data[$role_id])) {
                        $role_data[$role_id]['priv'][$menu_id] = strtolower($menu_data['controller'] . $menu_data['action']);
                    }

                    $values .= ",({$role_id},{$menu_id})";
                }

                $this->execute('INSERT INTO ' . TB_ADMIN_ROLE_PRIV . '(role_id,menu_id) VALUES' . trim($values, ','));

            }
        }

        //重新生成管理角色缓存
        if (isset($role_data)) {
            $this->_setCache($role_data, 'Role', 'Role');
        }
        else {
            C(APP_FORWARD, true);
            $this->_module->forward('Role', 'setCache');
        }
    }//end setMenuPriv
}