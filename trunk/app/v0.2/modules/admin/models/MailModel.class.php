<?php
/**
 * 邮件模板模型
 *
 * @file            MailModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-06 16:23:58
 * @lastmodify      $Date$ $Author$
 */

class MailModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认template_id
     */
    protected $_pk_field        = 'template_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_MAIL_TEMPLATE
     */
    protected $_true_table_name = TB_MAIL_TEMPLATE;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'add_time'   => '_addtime#insert',//添加时间
        'update_time'=> 'time#update',//更新时间
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
        'template_id'       => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        //模板名
        'template_name'     => array('validate' => array('_checkUnique#PLEASE_ENTER,TEMPLATE,NAME#data|template_name|TEMPLATE,NAME', '_checkLength#TEMPLATE,NAME#value|0|20')),
        'subject'           => array('validate' => array('notblank#MAIL_SUBJECT', '_checkLength#MAIL_SUBJECT#value|0|150')),
        'content'           => array('filter' => 'raw', 'validate' => 'notblank#MAIL_CONTENT'),
        //备注
        'memo'              => array('validate' => array('return#MEMO', '_checkLength#MEMO#value|0|60')),
        'sort_order'        => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
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
}