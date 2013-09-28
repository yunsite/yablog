<?php
/**
 * 管理员控制器类
 *
 * @file            AdminController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-25 12:05:21
 * @lastmodify      $Date$ $Author$
 */

class AdminController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->cache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache   = true;
    /**
     * @var string $_exclude_delete_id 不可删除管理员id。默认ADMIN_ID
     */
    protected $_exclude_delete_id  = ADMIN_ID;
    /**
     * @var string $_exclude_setField_id 不可更新某字段管理员id。默认ADMIN_ID
     */
    protected $_exclude_setField_id = ADMIN_ID;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认username
     */
    protected $_name_column = 'username';
    /**
     * @var array $_no_need_priv_action 不需要验证权限方法
     */
    protected $_no_need_priv_action = array('setCache');
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map = array(//权限映射
    	   'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'move'     => 'add',//移动
        'restrict' => 'add',//绑定登陆
    );

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$admin_info) {
        unset($admin_info['password']);
        $admin_info['lock_start_time'] = $admin_info['lock_start_time'] ? new_date(sys_config('sys_timezone_datetime_format'), $admin_info['lock_start_time']) : '';
        $admin_info['lock_end_time'] = $admin_info['lock_end_time'] ? new_date(sys_config('sys_timezone_datetime_format'), $admin_info['lock_end_time']) : '';
    }

    /**
     * 获取写缓存数据
     * @date            2012-09-05 14:22:04
     * @lastmodify      2013-01-21 15:44:59 by mrmsl
     *
     * @return mixed 查询成功，返回数组，否则false
     */
    protected function _setCacheData() {
        return $this->_model->field('*,MD5(`password`) AS `password`')->index($this->_pk_field)->select();
    }

    /**
     * 添加或保存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-26 15:57:19
     * @lastmodify      2013-01-21 15:45:31 by mrmsl
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//管理员id

        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'username,realname,role_name,is_restrict,lock_start_time,lock_end_time,lock_memo,verify_code_order' . ($this->_model->password ? ',password' : '');//比较差异字段 增加锁定字列by mrmsl on 2012-07-11 11:42:33
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('CONTROLLER_NAME_ADMIN,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if (!$role_info = $this->cache($role_id = $this->_model->role_id, 'Role')) {//角色不存在
            $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ':' . L("INVALID_PARAM,%:,ROLE,%role_id({$role_id}),NOT_EXIST");
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, L('BELONG_TO_ROLE,NOT_EXIST'));
        }

        $data['role_name'] = $role_info['role_name'];//角色名

        if ($pk_value) {//编辑

            if ($pk_value == ADMIN_ID && $this->_admin_info[$pk_field] != ADMIN_ID) {//不可编辑指定管理员。增加当前管理员id判断 by mrmsl on 2012-07-05 08:52:14
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . L('TRY,EDIT,CONTROLLER_NAME_ADMIN') . "{$pk_field}: {$pk_value}";
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, L('EDIT,FAILURE'));
            }

            if (!$admin_info = $this->cache($pk_value)) {//管理员不存在
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . PHP_EOL . L("INVALID_PARAM,%:,CONTROLLER_NAME_ADMIN,%{$pk_field}({$pk_value}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            if ($this->_model->save() === false) {//更新出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME_ADMIN') . "{$admin_info['username']}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $role_info = $this->cache($admin_info['role_id'], 'Role');
            $admin_info['role_name'] = $role_info['role_name'];//角色名

            if (isset($data['password'])) {
                $data['password'] = md5($data['password']);
            }

            $diff   = $this->_dataDiff($admin_info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L('CONTROLLER_NAME_ADMIN')  . "{$admin_info['username']}({$pk_value})." . $diff. L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $data = $this->_dataDiff($data, false, $diff_key);//数据

            if ($this->_model->add() === false) {//插入出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME_ADMIN') . $data . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('CONTROLLER_NAME_ADMIN') . $data . L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }//end addAction

    /**
     * 修改密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-14 09:35:55
     * @lastmodify      2013-01-21 15:45:58 by mrmsl
     *
     * @return void 无返回值
     */
    public function changePasswordAction() {
        $this->_model->getAutoOperate('password');//自动完成

        //自动验证
        $_auto_validation = array(
            array('_verify_code', '_checkVerifycode', '{%PLEASE_ENTER,VERIFY_CODE}', Model::MUST_VALIDATE, 'callback', model::MODEL_BOTH, 'module_admin'),//验证码
            array('_old_password', '_checkOldPassword', '{%PLEASE_ENTER,CN_YUAN,PASSWORD}', Model::MUST_VALIDATE, 'callback', model::MODEL_BOTH, 'data'),//原密码
        );
        $_auto_validation = array_merge($_auto_validation, $this->_model->getValidate('password,_password_confirm', false));
        $this->_model->setProperty('_validate', $_auto_validation);

        $result = $this->_model->checkCreate();

        if ($result !== true) {
            $this->_ajaxReturn(false, str_replace(L('PLEASE_ENTER,PASSWORD'), L('PLEASE_ENTER,NEW,PASSWORD'), $result));
        }

        $pk_field = $this->_pk_field;
        $this->_model->$pk_field = $this->_admin_info[$pk_field];

        $data = $this->_model->getProperty('_data');

        if ($this->_model->save() === false) {
            $this->_ajaxReturn(false, L('CN_XIUGAI,FAILURE'));
        }

        clear_verifycoe('module_admin');//清空验证码
        $admin_arr = $this->cache();

        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);
            $admin_arr[$this->_admin_info[$pk_field]]['password'] = $data['password'];
        }

        $this->setAdminSession($admin_arr[$this->_admin_info[$pk_field]]);//重设session
        $this->_model->addLog(L('CN_XIUGAI,PASSWORD,SUCCESS'));//操作日志
        $this->cache(null, null, $admin_arr)->_ajaxReturn(true, L('CN_XIUGAI,SUCCESS'));
    }//end changePassword

    /**
     * 管理员列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-26 14:22:09
     * @lastmodify      2013-01-21 15:46:28 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $db_fields      = $this->_getDbFields();//表字段
        $db_fields      = array_filter($db_fields, create_function('$v', 'return strpos($v, "_") !== 0;'));//过滤_开头
        $sort           = Filter::string('sort', 'get', $this->_pk_field);//排序字段
        $sort           = in_array($sort, $db_fields) || $sort == 'is_lock' ? $sort : $this->_pk_field;
        $order          = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
        $order          = toggle_order($order);
        $keyword        = Filter::string('keyword', 'get');//关键字
        $date_start     = Filter::string('date_start', 'get');//注册开始时间
        $date_end       = Filter::string('date_end', 'get');//注册结束时间
        $role_id        = Filter::int('role_id', 'get');//所属管理组
        $column         = Filter::string('column', 'get');//搜索字段
        $is_lock        = Filter::int('is_lock', 'get');//锁定
        $is_restrict    = Filter::int('is_restrict', 'get');//绑定登陆 by mrmsl on 2012-09-15 11:53:58
        $where          = array();

        if ($keyword !== '' && in_array($column, array('username', 'realname'))) {
            $where['a.' . $column] = $this->_buildMatchQuery('a.' . $column, $keyword, Filter::string('match_mode', 'get'));
        }

        if ($date_start && ($date_start = strtotime($date_start))) {
            $where['a.add_time'][] = array('EGT', $date_start);
        }

        if ($date_end && ($date_end = strtotime($date_end))) {
            $where['a.add_time'][] = array('ELT', $date_end);
        }

        if (isset($where['a.add_time']) && count($where['a.add_time']) == 1) {
            $where['a.add_time'] = $where['a.add_time'][0];
        }

        if ($is_lock == 0) {//未锁定 by mrmsl on 2012-09-15 11:26:36
            $where['a.lock_end_time'] = array('ELT', APP_NOW_TIME);
        }
        elseif ($is_lock == 1) {//未锁定 by mrmsl on 2012-09-15 11:26:44
            $where['a.lock_start_time'] = array('ELT', APP_NOW_TIME);
            $where['a.lock_end_time'] = array('EGT', APP_NOW_TIME);
        }

        if ($role_id) {
            $where['a.role_id'] = $role_id;
        }

        if ($is_restrict == 0) {
            $where['a.is_restrict'] = $is_restrict;
        }
        elseif ($is_restrict == 1) {
            $where['a.is_restrict'] = $is_restrict;
        }

        $total      = $this->_model->alias('a')->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_ADMIN') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $now       = APP_NOW_TIME;
        $fields    = str_replace(array(',a.password', ',a.mac_address', ',a.lock_start_time', ',a.lock_end_time', ',a.lock_memo'), '', join(',a.', $db_fields));
        $page_info = Filter::page($total);
        $data      = $this->_model->alias('a')
        ->join('JOIN ' . TB_ADMIN_ROLE . ' AS r ON a.role_id=r.role_id')
        ->where($where)->field($fields . ',r.role_name,' . ("(a.lock_start_time AND a.lock_start_time<{$now} AND a.lock_end_time AND a.lock_end_time>{$now}) AS is_lock"))
        ->limit($page_info['limit'])
        ->order(($sort == 'is_lock' ? 'is_lock' : 'a.' .$sort) . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_ADMIN') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction

    /**
     * (解除)锁定
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-05 17:19:49
     * @lastmodify      2013-01-21 15:46:53 by mrmsl
     *
     * @return void 无返回值
     */
    function lockAction() {
        $pk_id       = Filter::string($this->_pk_field);//管理员id
        $is_lock     = Filter::int('is_lock') ? 1 : 0;//1:锁定;0:解除锁定

        if ($is_lock) {
            $msg  = '';
            $data = array('lock_start_time' => APP_NOW_TIME, 'lock_end_time' => APP_NOW_TIME + 7200, 'lock_memo' => '');
        }
        else {
            $msg  = L('RELEASE');
            $data = array('lock_start_time' => 0, 'lock_end_time' => 0, 'lock_memo' => '');
        }

        $this->_setField($data, $is_lock, $msg . L('LOCK'));
    }

    /**
     * 移动所属角色
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-28 10:55:23
     * @lastmodify      2013-01-21 15:47:04 by mrmsl
     *
     * @return void 无返回值
     */
    function moveAction() {
        $field       = 'role_id';//定段
        $role_id     = Filter::int($field);//所属角色id
        $msg         = L('MOVE');//提示
        $log_msg     = $msg . L('CONTROLLER_NAME_ADMIN,FAILURE');//错误日志
        $error_msg   = $msg . L('FAILURE');//错误提示信息

        if ($role_id) {//角色id
            $role_info = $this->cache($role_id, 'Role');

            if (!$role_info) {//角色不存在
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%:,ROLE,%{$field}({$role_id}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            $role_name = $role_info['role_name'];
        }
        else {
            //非法参数
            $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%: {$field},IS_EMPTY");
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, $error_msg);
        }

        $this->_setField($field, $role_id, $msg, L('TO') . $role_name);
    }//end moveAction

    /**
     * (解除)绑定登陆
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-28 10:55:37
     * @lastmodify      2013-01-21 15:47:17 by mrmsl
     *
     * @return void 无返回值
     */
    function restrictAction() {
        $field       = 'is_restrict';//字段
        $is_restrict = Filter::int($field) ? 1 : 0;//1:绑定;0:不绑定
        $msg         = $is_restrict ? '' : L('RELEASE');

        $this->_setField($field, $is_restrict, $msg . L('CN_BANGDING,LOGIN'));
    }
}
