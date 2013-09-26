<?php
/**
 * 管理员模型
 *
 * @file            Admin.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-26 13:56:20
 * @lastmodify      $Date$ $Author$
 */

class AdminModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认admin_id
     */
    protected $_pk_field        = 'admin_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_ADMIN
     */
    protected $_true_table_name = TB_ADMIN;
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'add_time'        => 'time#insert',//添加时间
        'password'        => '_setPassword#data',//密码
        'is_restrict'     => '_getCheckboxValue',//是否绑定登陆
        'lock_start_time' => '_strtotime',//锁定开始时间 by mrmsl on 2012-09-05 15:56:05
        'lock_end_time'   => '_strtotime',//锁定结束时间 by mrmsl on 2012-09-05 15:56:25
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
        'admin_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,DATA,INVALID'),//自增主键

        //所属角色id
        'role_id'           => array('filter' => 'int', 'validate' => 'unsigned#PLEASE_SELECT,BELONG_TO_ROLE#0}'),

        //用户名
        'username'          => array('validate' => array('_checkUsername#PLEASE_ENTER,USERNAME#data', '_checkLength#USERNAME#value|0|15')),

        //密码 AdminAction::edit调到 by mrmsl on 2012-07-13 17:44:10
        'password'          => array('validate' => array('_checkPassword#PLEASE_ENTER,PASSWORD#data', '_checkLength#PASSWORD#value|6|16')),

        //真实姓名 ActminAction::edit调到 by mrmsl on 2012-07-13 17:45:12
        'realname'          => array('validate' => array('notblank#REALNAME', '_checkLength#USERNAME#value|0|30')),
        'add_time'          => array('filter' => 'int'),//添加时间
        'last_login_time'   => array('filter' => 'int'),//最后登陆时间
        'last_login_ip'     => array('filter' => 'ip'),//最后登陆ip
        'login_num'         => array('filter' => 'int'),//登陆次数
        'is_restrict'       => array('filter' => 'int'),//是否绑定登陆
        'mac_address'       => null,//网卡信息
        'lock_start_time'   => array('validate' => '_checkLength#LOCK,START,TIME#value|19|null'),//锁定开始时间
        'lock_end_time'     => array('validate' => '_checkLength#LOCK,END,TIME#value|19|null'),//锁定结束时间
        'lock_memo'         => array('validate' => array('return#LOCK,MEMO', '_checkLength#LOCK,MEMO#value|0|60')),//锁定备注

        //确认密码，_开头，不会入库
        '_password_confirm' => array('validate' => array('_checkPassword#PLEASE_ENTER,CONFIRM_PASSWORD#data|1', '_checkLength#CONFIRM_PASSWORD#value|6|16')),
        '_old_password'     => null,//原密码，修改密码是用到 by mrmsl on 2012-07-13 17:55:11
        'verify_code_order' => array('validate' => array('notblank#VERIFY_CODE_ORDER', '_checkLength#VERIFY_CODE_ORDER#value|0|10')),
    );

    /**
     * 确认密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:35:06 by mrmsl
     *
     * @param string $password 密码
     * @param array  $data     _POST数据
     * @param bool   $confirm  true确认密码。默认false
     *
     * @return mixed 如果验证通过，返回true，否则，如果新增，确认密码为空，返回输入确认密码，否则返回false
     */
    protected function _checkPassword($password, $data, $confirm = false) {
        $password           = trim($password);
        $admin_id           = isset($data[$v = $this->getPk()]) ? $data[$v] : 0;
        $original_password  = isset($data['password']) ? $data['password'] : '';

        if ($admin_id && $confirm) {//编辑，确认密码，如果密码为空，返回true，否则判断两次输入的密码是否一致
            return $password == '' || $password == $original_password ? true : L('PASSWORD_NOT_SAME');
        }
        elseif (!$admin_id && $password == '') {//新增
            return false;
        }
        elseif (!$admin_id && $confirm && $password != $original_password) {//新增，确认密码
            return L('PASSWORD_NOT_SAME');
        }

        return true;
    }

    /**
     * 验证用户是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:35:54 by mrmsl
     *
     * @param string $username 用户名
     * @param array  $data     _POST数据
     *
     * @return mixed 验证成功，返回true。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkUsername($username, $data) {

        if ($username === '') {//如果未输入，提示输入
            return false;
        }

        $username = strtolower($username);
        $users    = $this->_getCache();
        $admin_id = isset($data[$v = $this->getPk()]) ? $data[$v] : 0;

        foreach ($users as $user_id => $user) {

            if (strtolower($user['username']) == $username && $admin_id != $user_id) {
                return L('USERNAME,EXIST');
            }
        }

        return true;
    }

    /**
     * 自动填充密码，如果确认密码为空，则不需要修改密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:36:40 by mrmsl
     *
     * @param string $password 密码
     * @param array  $data     _POST数据
     *
     * @return mixed 如果确认密码为空，返回false，否则返回经md5加密后的密码
     */
    protected function _setPassword($password, $data) {
       return $data['_password_confirm'] === '' ? false : $this->_encryptPassword($password);
    }

    /**
     * 验证原密码是否正确
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-14 10:40:46
     * @lastmodify      2013-01-22 11:37:06 by mrmsl
     *
     * @param string $password 密码
     *
     * @return mixed true验证通过，否则返回错误信息
     */
    protected function _checkOldPassword($password) {

        if ($password === '') {
            return false;
        }

        $admin_info = Yaf_Registry::get(SESSION_ADMIN_KEY);

        return md5($this->_encryptPassword($password)) == $admin_info['password'] ? true : L('CN_YUAN,PASSWORD,NOT_CORRECT');
    }
}