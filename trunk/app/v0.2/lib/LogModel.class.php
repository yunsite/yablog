<?php
/**
 * 系统日志模型
 *
 * @file            LogModel.class.php
 * @package         Yab\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-14 09:15:54
 * @lastmodify      $Date$ $Author$
 */

class LogModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'add_time'     => 'time',
        'user_ip'      => 'get_client_ip#1',
        'user_id'      => '_getUserId',
        'username'     => '_getUsername',
        'page_url'     => '_getPageUrl',
        'referer_url'  => '_getRefererUrl',
    );
    /**
     * @var array $_db_fields 表字段
     */
    protected $_db_fields = array (
        'log_id'            => null,//自增id
        'content'           => null,//日志内容
        'type'              => null,//日志类型
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认log_id
     */
    protected $_pk_field        = 'log_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_LOG
     */
    protected $_true_table_name = TB_LOG;//表

    /**
     * 获取用户名
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-14 09:18:24
     *
     * @return string 后台返回管理员真实姓名,前台返回空字符串
     */
    protected function _getUsername() {
        return 'admin' == MODULE_NAME ? C(SESSION_ADMIN_KEY . '.realname', null, '') : '';
    }
}