<?php
/**
 * 管理员登陆历史模型
 *
 * @file            AdminLoginHistoryModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-17 22:06:26
 * @lastmodify      $Date$ $Author$
 */

class AdminLoginHistoryModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认login_id
     */
    protected $_pk_field        = 'login_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_ADMIN_LOGIN_HISTORY
     */
    protected $_true_table_name = TB_ADMIN_LOGIN_HISTORY;//表
}