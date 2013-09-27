<?php
/**
 * 语言包模块模型
 *
 * @file            LanguageModulesModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-20 11:09:45
 * @lastmodify      $Date$ $Author$
 */

class LanguageModulesModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
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
        'module_id'        => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'parent_id'        => array('filter' => 'int', 'validate' => array('unsigned#PLEASE_SELECT,PARENT_LANGUAGEMODULES', array('_checkParentId', '{%CN_CI,CONTROLLER_NAME_LANGUAGEMODULES,CAN_NOT,EDIT,PARENT_LANGUAGEMODULES}', Model::VALUE_VALIDATE, 'callback', Model::MODEL_BOTH, 'data'))),//父级id
        'module_name'      => array('validate' => array('_checkUnique#PLEASE_ENTER,LANGUAGE_MODULE_NAME#data|module_name|LANGUAGE_MODULE_NAME|1', '_checkLength#LANGUAGE_MODULE_NAME#value|0|20')),
        'memo'             => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认module_id
     */
    protected $_pk_field        = 'module_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_LANGUAGE_MODULES
     */
    protected $_true_table_name = TB_LANGUAGE_MODULES;

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
     * 验证所属模块,1,2,3不可编辑所属模块
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-20 09:48:48
     *
     * @param int       $parent_id      父级id
     * @param array     $data           $_POST
     *
     * @return bool true验证成功,否则false
     */
    protected function _checkParentId($parent_id, $data) {

        //1,2,3不可编辑所属模块,即parent_id=0
        if (isset($data[$pk_field = $this->getPk()]) && $parent_id && in_array($pk_value = $data[$pk_field], $this->_module->getProperty('_exclude_delete_id'))) {
            return false;
        }
        elseif (empty($data[$pk_field]) && !$parent_id) {//添加,必须选择所属模块
            return L('PLEASE_SELECT,PARENT_LANGUAGEMODULES');
        }
        elseif (!$parent_info = $this->_getCache($parent_id)) {//模块不存在
            return L('PARENT_LANGUAGEMODULES,NOT_EXIST');
        }
        elseif (!in_array($parent_id, $this->_module->getProperty('_exclude_delete_id'))) {//二级模块不可以增加子模块
            return L('CAN_NOT_ADD_CHILD_MODULE');
        }

        return true;
    }
}