<?php
/**
 * 管理员登陆模型
 *
 * @file            LoginModel.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-25 16:32:58
 * @lastmodify      $Date$ $Author$
 */

class LoginModel extends CommonModel {
    /**
     * @var bool $_patch_validate true批处理验证。默认false
     */
    //protected $_patch_validate = false;
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
        'username'     => array('validate' => 'notblank#USERNAME'),//用户名
        'password'     => array('validate' => '_checkPassword#PLEASE_ENTER,PASSWORD#data'),//密码
    );

    /**
     * 验证帐号是否被锁定
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-12 09:02:21
     * @lastmodify      2013-01-22 11:47:43 by mrmsl
     *
     * @param array $admin_info 管理员信息
     *
     * @return mixed true未被锁定，否则返回错误信息
     */
    private function _checkLock($admin_info) {

        if ($admin_info['lock_start_time'] && $admin_info['lock_start_time'] < APP_NOW_TIME && $admin_info['lock_end_time'] && $admin_info['lock_end_time'] > APP_NOW_TIME) {
            $info = L('ACCOUNT_IS_LOCKED,TO') . new_date(sys_config('sys_timezone_datetime_format'), $admin_info['lock_end_time']);
            $this->addLog("{$admin_info['username']}[{$admin_info['realname']}] {$info}", LOG_TYPE_ADMIN_LOGIN_INFO);
            return $info;
        }

        return true;
    }

    /**
     * 验证管理员是否绑定登陆
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-12 08:54:32
     * @lastmodify      2013-01-22 11:48:03 by mrmsl
     *
     * @param array  $admin_info  管理员信息
     * @param string $mac_address 网卡信息
     *
     * @return mixed true未绑定登陆或网卡信息正确，否则返回错误信息
     */
    private function _checkRestrict($admin_info, $mac_address) {

        if ($admin_info['is_restrict']) {//绑定登陆

            if ($mac_address === '') {//读取网卡信息失败
                $this->addLog(L('LOGIN,FAILURE,%:,MAC_ADDRESS_ERROR') . "({$admin_info['username']}[{$admin_info['realname']}])", LOG_TYPE_ADMIN_LOGIN_INFO);
                return L('MAC_ADDRESS_ERROR');
            }

            if ($admin_info['mac_address'] && $mac_address != $admin_info['mac_address']) {//网卡信息不正确
                $this->addLog(L('LOGIN,FAILURE,%:,PC_HAS_NOT_PERMISSION') . "({$admin_info['username']}[{$admin_info['realname']}]).{$admin_info['mac_address']}=>{$mac_address}", LOG_TYPE_ADMIN_LOGIN_INFO);
                return L('PC_HAS_NOT_PERMISSION,%，,CONTACT_WEB_MANAGER');
            }
        }

        return true;
    }

    /**
     * 验证用户密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-06-25 16:42:34
     * @lastmodify      2013-01-22 11:48:24 by mrmsl
     *
     * @param string $password 密码
     * @param array  $data     $_POST数组
     *
     * @return mixed true验证成功，否则返回对应提示信息
     */
    protected function _checkPassword($password, $data) {

        if ('' === $password) {
            return false;
        }

        $admin_arr       = $this->_module->cache(null, 'Admin');//管理员缓存
        $username        = strtolower($data['username']);//用户名
        $_password       = $password;
        $password        = md5($this->_encryptPassword($password));//密码，缓存密码，三重加密 by mrmsl on 2012-07-02 10:45:11
        $mac_address     = Filter::string('mac_address');//网卡信息

        foreach ($admin_arr as $admin_id => $item) {

            if (strtolower($item['username']) == $username) {//存在管理员

                if (($checkRestrict = $this->_checkRestrict($item, $mac_address)) !== true) {//绑定登陆
                    return $checkRestrict;
                }

                if (($checkLock = $this->_checkLock($item)) !== true) {//锁定
                    return $checkLock;
                }

                $check_password = $this->_checkPasswordIsCorrect($admin_arr, $item, $password, $mac_address);//验证密码是否正确

                if (is_string($check_password)) {//验证码
                    return $check_password;
                }
                elseif (true === $check_password) {
                    return true;
                }

                break;
            }
        }//end foreach

        $this->addLog(L('LOGIN,FAILURE,%:,USERNAME,OR,PASSWORD,HAS_ERROR') . ".{$username}({$_password})", LOG_TYPE_ADMIN_LOGIN_INFO);

        return L('USERNAME,OR,PASSWORD,HAS_ERROR');
    }//end _checkPassword

    /**
     * 验证用户密码是否正确
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-12 09:12:50
     * @lastmodify      2013-01-22 11:48:48 by mrmsl
     *
     * @param array  $admin_arr   所有管理员
     * @param array  $admin_info  管理员信息
     * @param string $password    密码
     * @param string $mac_address 网卡信息
     *
     * @return bool true密码正确，否则false
     */
    private function _checkPasswordIsCorrect(&$admin_arr, $admin_info, $password, $mac_address) {

        if ($admin_info['password'] == $password) {//密码正确
            $verifycode = Filter::string('_verify_code');

            if ('' === $verifycode) {
                return L('PLEASE_ENTER,VERIFY_CODE');
            }

            C('T_VERIFYCODE_ORDER', $admin_info['verify_code_order']);
            $check_verifycode = $this->_checkVerifycode($verifycode, 'module_admin');

            if (true !== $check_verifycode) {
                return $check_verifycode;
            }

            $admin_id = $admin_info['admin_id'];
            $user_ip  = get_client_ip();//登陆ip
            $time     = time();//登陆时间
            $mac      = $admin_info['is_restrict'] && !$admin_info['mac_address'] ? ",mac_address='{$mac_address}'" : '';//网卡信息为空，更新

            //更新管理员最后登陆时间，最后登陆ip，登陆次数
            $this->getDb()->execute('UPDATE ' . TB_ADMIN . " SET login_num=login_num+1,last_login_time={$time},last_login_ip='{$user_ip}'{$mac},lock_start_time=0,lock_end_time=0,lock_memo='' WHERE admin_id={$admin_id}");

            //记录管理员登陆历史
            $this->getDb()->execute('INSERT INTO ' . TB_ADMIN_LOGIN_HISTORY . "(admin_id,login_time,login_ip) VALUES({$admin_id},{$time}," . get_client_ip(1) . ')');
            $this->_module->setAdminSession($admin_info);//设置session

            //管理员日志
            $this->addLog(L('LOGIN,SUCCESS') . ".{$admin_info['username']}({$admin_info['realname']})", LOG_TYPE_ADMIN_LOGIN_INFO);

            $admin_arr[$admin_id]['login_num']++;
            $admin_arr[$admin_id]['last_login_time'] = $time;
            $admin_arr[$admin_id]['last_login_ip'] = $user_ip;
            $admin_arr[$admin_id]['is_lock'] = 0;
            $admin_arr[$admin_id]['lock_start_time'] = 0;
            $admin_arr[$admin_id]['lock_end_time'] = 0;
            $admin_arr[$admin_id]['lock_memo'] = '';

            if ($mac) {//网卡信息有变更
                $admin_arr[$admin_id]['mac_address'] = $mac_address;
            }

            $this->_module->cache(null, 'Admin', $admin_arr);//缓存
            clear_verifycoe('module_admin');//清空验证码

            return true;
        }

        return false;
    }//end _checkPasswordIsCorrect
}