<?php
/**
 * 管理角色模型
 *
 * @file            Role.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-29 09:46:19
 * @lastmodify      $Date$ $Author$
 */

class RoleModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认role_id
     */
    protected $_pk_field        = 'role_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_ADMIN_ROLE
     */
    protected $_true_table_name = TB_ADMIN_ROLE;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'sort_order' => '_unsigned#data',//排序
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
        'role_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键

        //角色名
        'role_name'        => array('validate' => array('_checkRolename#PLEASE_ENTER,ROLENAME#data', '_checkLength#ROLENAME#value|0|30')),

        //备注
        'memo'             => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        '_priv_id'         => array('validate' => 'return#PERMISSION'),//权限
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序 by masnanling on 22:03 2012-7-8
    );

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
     * 验证角色名是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:56:56 by mrmsl
     *
     * @param string $rolename 用户名
     * @param array  $data     _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkRolename($rolename, $data) {
        $pk_field = $this->getPk();

        if ($rolename === '' || !isset($data[$pk_field])) {//如果未输入，提示输入
            return false;
        }

        $rolename = strtolower($rolename);
        $pk_value = isset($data[$pk_field]) ? $data[$pk_field] : 0;
        $roles    = $this->_getCache();

        foreach ($roles as $role_id => $role) {

            if ($rolename == $role['role_name'] && $pk_value != $role_id) {
                return L('ROLENAME,EXIST');
            }
        }

        return true;
    }

    /**
     * 设置角色权限
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:57:11 by mrmsl
     *
     * @param int   $role_id
     * @param mixed $menu_id 菜单id串(数组)
     *
     * @return void 无返回值
     */
    public function setRolePriv($role_id, $menu_id) {

        if (!$menu_id) {//无权限，直接删除
            $this->getDb()->execute('DELETE s FROM ' . TB_SHORTCUT . ' AS s,' . TB_ADMIN . ' AS a,' . TB_ADMIN_ROLE . " AS r WHERE s.admin_id=a.admin_id AND a.role_id=r.role_id AND a.role_id={$role_id} AND a.role_id!=" . ADMIN_ROLE_ID);
            $this->table(TB_ADMIN_ROLE_PRIV)->where('role_id=' . $role_id)->delete();
        }
        else {
            $role_data   = $this->_getCache($role_id);
            $priv_arr    = $this->_module->diffRolePriv($role_data ? array_keys($role_data['priv']) : '', $menu_id);

            if ($delete = $priv_arr['delete']) {//删除的
                $menu_id = join(',', $delete);
                $this->getDb()->execute('DELETE s FROM ' . TB_SHORTCUT . ' AS s,' . TB_ADMIN . ' AS a,' . TB_ADMIN_ROLE . " AS r WHERE s.admin_id=a.admin_id AND a.role_id=r.role_id AND a.role_id={$role_id} AND s.menu_id IN({$menu_id}) AND a.role_id!=" . ADMIN_ROLE_ID);
                $this->table(TB_ADMIN_ROLE_PRIV)->where(array('role_id' => $role_id, 'menu_id' => array('IN', $delete)))->delete();
            }

            if ($add = $priv_arr['add']) {//新增的
                $add     = array_unique($add);
                $values  = '';

                foreach($add as $menu_id) {
                    $values .= ",({$role_id},{$menu_id})";
                }

                $this->execute('INSERT INTO ' . TB_ADMIN_ROLE_PRIV . '(role_id,menu_id) VALUES' . substr($values, 1));
            }
        }

        C(APP_FORWARD, true);
        $this->_module->forward('Menu', 'create');//菜单缓存
    }//end setRolePriv
}