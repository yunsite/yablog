<?php
/**
 * 系统日志模型
 *
 * @file            LogModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-26 15:01:44
 * @lastmodify      $Date$ $Author$
 */

class LogModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'log_time'     => 'time',
        'user_ip'      => 'get_client_ip#1',
        'admin_id'     => '_getAdminId',
        'admin_name'   => '_getAdminName',
        'page_url'     => '_getPageUrl',
        'referer_url'  => '_getRefererUrl',
    );
    /**
     * @var array $_db_fields 表字段
     */
    protected $_db_fields = array (
        'log_id'         => null,//自增id
        'content'        => null,//日志内容
        'log_type'       => null,//日志类型
        'log_time'       => null,//日志时间
        'page_url'       => null,//日志页面
        'referer_url'    => null,//来路页面
        'user_ip'        => null,//管理员ip
        'admin_id'       => null,//管理员id
        'admin_name'     => null,//管理员姓名
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
     * 获取管理员姓名
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-09 11:11:54
     * @lastmodify      2013-01-22 11:46:27 by mrmsl
     *
     * @return string 管理员姓名
     */
    protected function _getAdminName() {
        $admin_info = Yaf_Registry::get(SESSION_ADMIN_KEY);

        return $admin_info ? $admin_info['realname'] : '';
    }
}