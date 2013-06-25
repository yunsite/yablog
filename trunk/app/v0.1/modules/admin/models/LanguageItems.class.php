<?php
/**
 * 语言项模块模型
 *
 * @file            LanguageItems.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-21 11:25:56
 * @lastmodify      $Date$ $Author$
 */

class LanguageItemsModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'sort_order' => '_unsigned#data',//排序
        'to_js'      => '_getCheckboxValue',//生成js
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
        'item_id'           => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'module_id'         => array('filter' => 'int', 'validate' => array('unsigned#PLEASE_SELECT,LANGUAGE_MODULE', array('_checkCacheExists', '{%LANGUAGE_MODULE,NOT_EXIST}', Model::VALUE_VALIDATE, 'callback', Model::MODEL_BOTH, 'LanguageModules'))),//语言模块id
        'var_name'          => array('validate' => array('_checkUnique#PLEASE_ENTER,LANGUAGE_ITEM_VAR_NAME#data|var_name|LANGUAGE_ITEM_VAR_NAME|module_id', array('_checkVarName', '', Model::VALUE_VALIDATE, 'callback', Model::MODEL_BOTH), '_checkLength#LANGUAGE_ITEM_VAR_NAME#value|0|50')),
        'var_value_zh_cn'   => array('filter' => 'raw', 'validate' => 'notblank#VAR_VALUE_ZH_CN'),
        'var_value_en'      => array('filter' => 'raw', 'validate' => 'notblank#VAR_VALUE_EN'),
        'memo'              => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        'sort_order'        => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认item_id
     */
    protected $_pk_field        = 'item_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_LANGUAGE_ITEMS
     */
    protected $_true_table_name = TB_LANGUAGE_ITEMS;

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
     * 验证变量名
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-21 14:15:51
     *
     * @param string       $var_name         变量名
     *
     * @return true|string true验证成功,否则提示信息
     */
    protected function _checkVarName($var_name) {
        return 'MODULE_NAME' == strtoupper($var_name) ? L('LANGUAGE_ITEM_VAR_NAME,CAN_NOT,EQ') . 'MODULE_NAME' : true;
    }
}