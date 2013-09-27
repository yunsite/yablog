<?php
/**
 * 国家地区模型
 *
 * @file            AreaModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-29 14:21:26
 * @lastmodify      $Date$ $Author$
 */

class AreaModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'is_show'    => '_getCheckboxValue',//是否显示
        'sort_order' => '_unsigned#data',//排序 by mrmsl on 2012-09-10 16:36:38
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
        'area_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'parent_id'        => array('filter' => 'int', 'validate' => 'unsigned#PLEASE_SELECT,PARENT_AREA'),//父级地区id

        //地区名称
        'area_name'        => array('validate' => array('_checkAreaName#PLEASE_ENTER,CONTROLLER_NAME_AREA,NAME#data', '_checkLength#CONTROLLER_NAME_AREA,NAME#value|0|50')),
        'area_code'        => array('validate' => '_checkLength#AREA_CODE#value|0|15'),

        'is_show'          => array('filter' => 'int'),//是否显示
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认area_id
     */
    protected $_pk_field        = 'area_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_AREA
     */
    protected $_true_table_name = TB_AREA;

	   /**
     * {@inheritDoc}
     */
    protected function _afterDelete($data, $options = array()) {
        $this->_module->createAction();
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
     * 验证地区名称是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:40:06 by mrmsl
     *
     * @param string $name 地区名称
     * @param array  $data   _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkAreaName($name, $data) {

        if ($name === '') {//如果未输入，提示输入
            return false;
        }

        $parent_id = isset($data['parent_id']) ? $data['parent_id'] : 0;
        $area_id   = empty($data[$this->_pk_field]) ? 0 : $data[$this->_pk_field];

        //缓存数据比较大，通过循环缓存判断改为读数据库判断 by mrmsl on 2012-08-15 11:24:58
        $info = $this->where("parent_id={$parent_id} AND area_name='{$name}' AND {$this->_pk_field}!={$area_id}")->find();

        if ($info) {
            return L('CONTROLLER_NAME_AREA,EXIST') . ': <br /><span style="color: #666;font-weight: bold;">' . $this->_module->nav($info[$this->_pk_field], 'area_name') . '</span>';
        }

        return true;
    }
}