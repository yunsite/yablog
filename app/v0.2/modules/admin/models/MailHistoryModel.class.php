<?php
/**
 * 邮件历史模型
 *
 * @file            MailHistoryModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-07 11:04:16
 * @lastmodify      $Date$ $Author$
 */

class MailHistoryModel extends CommonModel {
    /**
     * @var array $_db_fields 表字段
     */
    protected $_db_fields = array (
        'history_id'        => null,//自增id
        'template_id'       => null,//邮件模板id
        'subject'           => null,//邮件主题
        'content'           => null,//邮件内容
        'email'             => null,//用户email
        'add_time'          => null,//发送时间
        'times'             => null,//发送次数,0表示发送失败
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认history_id
     */
    protected $_pk_field        = 'history_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_MAIL_HISTORY
     */
    protected $_true_table_name = TB_MAIL_HISTORY;//表
}