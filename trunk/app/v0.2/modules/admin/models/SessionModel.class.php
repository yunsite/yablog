<?php
/**
 * session管理模型
 *
 * @file            SessionModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-18 14:47:52
 * @lastmodify      $Date$ $Author$
 */

class SessionModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认session_id
     */
    protected $_pk_field        = 'session_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_SESSION
     */
    protected $_true_table_name = TB_SESSION;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'last_time'    => 'time',
        'user_id'      => 'get_user_id',
        'user_ip'      => 'get_client_ip#1',
        'admin_id'     => '_getAdminId',
        'page_url'     => '_getPageUrl',
        'referer_url'  => '_getRefererUrl',
        'controller'   => array('string', CONTROLLER_NAME),
        'action'       => array('string', ACTION_NAME),
    );
    /**
     * @var array $_db_fields 数据表字段
     */
    protected $_db_fields = array (
        'session_id'     => null,//session id
        'data'           => null,//session内容
        'controller'     => null,//控制器
        'action'         => null,//操作方法
        'last_time'      => null,//最后活跃时间
        'page_url'       => null,//日志页面
        'referer_url'    => null,//来路页面
        'admin_id'       => null,//管理员ip
        'user_id'        => null,//管理员id
        'user_ip'        => null,//管理员姓名
    );
}