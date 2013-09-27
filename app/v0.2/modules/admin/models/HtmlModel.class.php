<?php
/**
 * 生成静态页管理模型
 *
 * @file            HtmlModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-18 09:19:41
 * @lastmodify      $Date$ $Author$
 */

class HtmlModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认html_id
     */
    protected $_pk_field        = 'html_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_HTML
     */
    protected $_true_table_name = TB_HTML;
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
        'html_id'         => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        //模板名
        'tpl_name'        => array('validate' => array('_checkUnique#PLEASE_ENTER,TPL_NAME#data|tpl_name', array('_checkTplName', '{%PLEASE_ENTER,CORRECT,CN_DE,TPL_NAME,FORMAT}', Model::VALUE_VALIDATE, 'callback'), '_checkLength#TPL_NAME#value|0|30')),
        'html_name'        => array('validate' => array('_checkUnique#PLEASE_ENTER,HTML_NAME#data|html_name', array('validate_dir', '{%HTML_NAME,DATA,INVALID}', Model::VALUE_VALIDATE, 'function', Model::MODEL_BOTH, '{%HTML_NAME}|null|0|0|0'), '_checkLength#HTML_NAME#value|0|30')),
        'last_build_time' => array('filter' => 'int', 'validate' => array('_checkLength#LAST,BUILD,TIME,DATA#value|0')),
        //备注
        'memo'             => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
    );

    /**
     * 新增数据后，将排序设为该记录自动增长id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 14:59:46
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
     * swsi
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-18 09:50:30
     *
     * @param string $tpl_name s
     *
     * @return void 无返回值
     */
    protected function _checkTplName($tpl_name) {
        $arr = explode('/', $tpl_name);

        if (2 != count($arr)) {
            return false;
        }
        elseif (false !== strpos($tpl_name, '..')) {
            $this->addLog(L('TRY,USE,RELATIVE,PATH') . $tpl_name, LOG_TYPE_INVALID_PARAM);
            return L('TPL_NAME,CAN_NOT,USE,RELATIVE,PATH');
        }
        elseif (!is_file(FRONT_THEME_PATH . $tpl_name . C('TEMPLATE_SUFFIX'))) {
            return L('TEMPLATE,NOT_EXIST');
        }

        return true;
    }
}