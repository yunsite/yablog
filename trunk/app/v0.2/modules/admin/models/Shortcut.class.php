<?php
/**
 * 快捷方式模型
 *
 * @file            Shortcut.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-03 22:10:54
 * @lastmodify      $Date$ $Author$
 */

class ShortcutModel extends CommonModel {
    /**
     * @var string $_pk_short 数据表主键字段名称。默认short_id
     */
    protected $_pk_field        = 'short_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_SHORTCUT
     */
    protected $_true_table_name = TB_SHORTCUT;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'sort_order'        => '_unsigned#data',//排序
        'admin_id'          => '_getAdminId#insert',
        'additional_param'  => array('trim', Model::MODEL_BOTH, 'function', '?&'),
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
        'menu_id'           => array('filter' => 'int', 'validate' => '_checkMenuId#PLEASE_SELECT,BELONG_TO_MENU'),//所属菜单
        'additional_param'  => array('validate' => array('return#ADDITIONAL_PARAM', '_checkLength#ADDITIONAL_PARAM#value|0|100')),//附加参数
        'sort_order'        => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
        'memo'              => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        'short_id'          => array('filter' => 'int', 'validate' => '_checkShortId#PRIMARY_KEY,INVALID#data'),//自增主键,放于最后，以判断是否有重复
    );

    /**
     * 新增数据后，将排序设为该记录自动增长id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-03 22:20:04
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
     * 验证所属菜单
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-03 22:47:03
     *
     * @param string $menu_id 菜单id
     *
     * @return mixed 验证成功，返回true。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkMenuId($menu_id) {

        if (!$menu_id) {//如果未输入，提示输入
            return false;
        }

        if (!$this->_getCache($menu_id, 'Menu')) {
            return L('BELONG_TO_MENU,NOT_EXIST');
        }

        return true;
    }

    /**
     * 验证short_id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-04 21:44:29
     *
     * @param string $short_id short_id
     * @param array  $data     _POST数据
     *
     * @return mixed 验证成功，返回true。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkShortId($short_id, $data = array()) {

        if (!$short_id) {
        }
        elseif (!$short_info = $this->find($short_id)) {
            return L('SHORTCUT,NOT_EXIST');
        }

        $menu_id            = isset($data['menu_id']) ? $data['menu_id'] : 0;
        $additional_param   = isset($data['additional_param']) ? $data['additional_param'] : '';
        $id                 = $this->where(array('menu_id' => $menu_id, 'admin_id' => $this->_getAdminId(), 'additional_param' => $additional_param))->getField('short_id');

        if ($id && $short_id != $id) {
            return L('SHORTCUT,CN_YI,ADD');
        }

        $short_id && C('T_SHORT_INFO', $short_info);

        return true;
    }
}