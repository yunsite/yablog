<?php
/**
 * 表单域模型
 *
 * @file            FieldModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-08-01 17:10:31
 * @lastmodify      $Date$ $Author$
 */

class FieldModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认field_id
     */
    protected $_pk_field        = 'field_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_FIELD
     */
    protected $_true_table_name = TB_FIELD;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'is_enable'  => '_getCheckboxValue',//是否启用
        'sort_order' => '_unsigned#data',//排序 by mrmsl on 2012-09-10 16:53:02
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
        'field_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'menu_id'           => array('filter' => 'int', 'validate' => 'unsigned#PLEASE_SELECT,PARENT_FIELD#0'),//所属表单域

        //字段名称
        'field_name'        => array('validate' => array('notblank#FIELD_NAME', '_checkLength#FIELD_NAME#value|0|50')),
        'field_code'       	=> array('filter' => 'raw', 'validate' => 'notblank#FIELD_CODE'),

        //验证规则 by mrmsl on 2012-08-31 15:15:17
        'validate_rule'     => array('filter' => 'raw', 'validate' => 'notblank#VALIDATE_RULE'),

        //自动填充 by mrmsl on 2012-09-07 12:41:06
        'auto_operation'    => array('filter' => 'raw', 'validate' => array('return#AUTO_OPERATION', '_checkLength#AUTO_OPERATION#value|0|60')),

        //输入框名称 by mrmsl on 2012-08-28 13:39:32
        'input_name'        => array('validate' => array('_checkInputname#PLEASE_ENTER,INPUT_NAME#data', '_checkLength#INPUT_NAME#value|0|450')),
        'input_value'       => null,//输入框值 by mrmsl on 2012-08-29 16:37:37
        'is_enable'         => array('filter' => 'int'),//是否显示
        'sort_order'        => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序

        //备注
        'memo'              => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|100')),
        'customize_1'       => array('filter' => 'int', 'validate' => 'unsigned#CUSTOMIZE,COLUMN,%_1'),//自定义字段1 by mrmsl on 2012-09-04 18:13:40
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
     * 验证表单域否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-21 22:32:36
     * @lastmodify      2013-01-22 11:42:15 by mrmsl
     *
     * @param string $input_name 表单域名
     * @param array  $data       _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkInputname($input_name, $data) {

        if ($input_name === '') {//如果未输入，提示输入
            return false;
        }

        $input_name = strtolower($input_name);
        $menu_id    = isset($data['menu_id']) ? $data['menu_id'] : 0;
        $pk_value   = empty($data[$this->_pk_field]) ? 0 : $data[$this->_pk_field];
        $fields     = $this->_module->cache();

        foreach ($fields as $field_id => $field) {

            if ($input_name == strtolower($field['input_name']) && $menu_id == $field['menu_id'] && $pk_value != $field_id) {
                return L('INPUT_NAME,EXIST');
            }
        }

        return true;
    }

    /**
     *  自动验证数据方法
     *
     * @see CommonModel.class.php checkCreate
     */
    protected function _validateSaveValue() {
         return $this->_autoValidate($_POST, Model::MODEL_BOTH);
    }

    /**
     * 保存值自动验证及自动填充
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-31 16:48:45
     * @lastmodify      2013-01-22 11:43:19 by mrmsl
     *
     * @param array $field_arr 字段信息
     *
     * @return void 无返回值
     */
    public function saveValueCheckCreate($field_arr) {
        $validate = array();
        //Model常量替换
        $find     = array('MUST_VALIDATE', 'EXISTS_VALIDATE', 'VALUE_VALIDATE', 'MODEL_BOTH');
        $replace  = array(Model::MUST_VALIDATE, Model::EXISTS_VALIDATE, Model::VALUE_VALIDATE, Model::MODEL_BOTH);
        $this->_validate = array();//重置验证规则
        $this->_auto = array();//重置自动填充 by mrmsl on 2012-09-07 13:03:16

        foreach ($field_arr as $item) {
            $field_name    = $item['field_name'];
            $input_name    = $item['input_name'];
            $validate_rule = str_replace('@field_name', $field_name, $item['validate_rule']);
            $validate_rule = str_replace($find, $replace, $validate_rule);
            $validate_arr  = explode(PHP_EOL, $validate_rule);
            $filter        = array_shift($validate_arr);//值过滤类型

            if (isset($_POST[$input_name])) {//过滤
                $filter = explode(',', $filter);//过滤参数用,隔开 by mrmsl on 2012-09-13 09:35:39
                $method = $filter[0];
                $filter[0] = $input_name;
                $_POST[$input_name] = call_user_func_array(array('Filter', $method), $filter);
            }

            if ($validate_arr) {//自动验证规则

                foreach ($validate_arr as $k => $v) {
                    $v = explode('#', $v);

                    if (strpos($v[0], '@') === 0) {//第一个为指定验证字段名
                        $input_name = substr(array_shift($v), 1);
                    }

                    count($v) < 4 && array_splice($v, 1, 0, '%' . $field_name);
                    $this->_setValidate($input_name, $v);
                }

            }

            if ($auto = $item['auto_operation']) {//自动填充 by mrmsl on 2012-09-07 13:05:12
                $auto = str_replace($find, $replace, $auto);
                $auto = explode('#', $auto);

                if (strpos($auto[0], '@') === 0) {//第一个为指定自动完成字段名
                    $input_name = substr(array_shift($auto), 1);
                }

                $this->_setAutoOperate($input_name, $auto);
            }
        }
    }//saveValueCheckCreate
}