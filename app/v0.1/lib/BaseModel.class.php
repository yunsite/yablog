<?php
/**
 * 底层模型
 *
 * @file            BaseModel.class.php
 * @package         Yab\Model
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-09 16:43:12
 * @lastmodify      $Date$ $Author$
 */

class BaseModel extends Model {
    /**
     * @var object $_module 对应控制器，即模块。默认null
     */
    protected $_module = null;

    /**
     * 添加时间自动完成回调
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-01 11:15:23
     *
     * @param string $datetime 时间表达式
     *
     * @return 转化成功，返回转化的时间戳，否则返回当前时间戳
     */
    protected function _addtime($datetime = '') {
        $v = $this->_strtotime($datetime);

        return $v ? $v : time();
    }

    /**
     * 新增数据后，将排序设为该记录自动增长id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-29 14:30:03
     * @lastmodify      2013-02-07 13:46:00 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterInserted($data, $options) {

        if (!isset($data['sort_order'])) {//排序-1，更新为对应id
            $pk_field = $this->getPk();
            $this->save(array('sort_order' => $data[$pk_field], $pk_field => $data[$pk_field]));
        }
    }

    /**
     * 验证 int1/int2 格式
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-28 11:46:12
     * @lastmodify      2013-01-22 11:11:08 by mrmsl
     *
     * @param string $string    待验证字符串
     * @param string $delimiter 字符串分割符。默认/
     *
     * @return bool true格式正确，否则false
     */
    protected function _checkExplodeNumericFormat($string, $delimiter = '/') {
        $arr = explode($delimiter, $string);

        return isset($arr[1]) && is_numeric($arr[0]) && is_numeric($arr[1]);
    }

    /**
     * 检测值长度是否在指定范围内
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:11:48 by mrmsl
     *
     * @param string $value 待检测值
     * @param string $name  检测名称
     * @param int    $min   最小值
     * @param int    $max   最大值
     *
     * @return mixed true检测成功，否则返回长度错误提示信息
     */
    protected function _checkLength($value, $name, $min, $max = null) {

        if (!is_numeric($max)) {//指定长度
            return $this->check($value, $min, 'length') ? true : L($name . ',LENGTH,MUST,EQ') . $min;
        }

        if (!$min) {//超出最大长度
            $msg = sprintf(L('GT_LENGTH'), L($name), $max);
        }
        elseif (!$max) {//小于最小长度
            $msg = sprintf(L('LT_LENGTH'), L($name), $min);
        }
        else {
            $msg = sprintf(L('CONSTRAIN_LENGTH'), L($name), $min, $max);
        }

        return $this->check($value, $min . ',' . $max, 'length') ? true : $msg;
    }

	   /**
     * 验证验证码是否正确
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-06-25 16:34:04
     * @lastmodify      2013-01-22 11:12:24 by mrmsl
     *
     * @param string $code   验证码
     * @param string $module 验证码模块
     *
     * @return mixed 如果正确，返回true，否则返回提示信息
     */
    protected function _checkVerifycode($code, $module) {

        if ($v = C('T_VERIFYCODE_MODULE')) {//动态设置模块 by mrmsl on 2013-05-21 11:42:23
            $module = $v;
        }

        $verifycode_setting = get_verifycode_setting($module);

        if (!$verifycode_setting['enable']) {//未开启验证码
            $this->_checkVerifycode = true;//通过验证码检测
            return true;
        }

        if ($code === '') {//未输入验证码
            return false;
        }

        if (($checked = check_verifycode($code, $module)) === true) {//转至check_verifycode验证 by mrmsl on 2012-07-13 16:54:54
            $this->_checkVerifycode = true;//通过验证码检测 by mrmsl on 2012-07-02 09:55:53
            return true;
        }

        $this->addLog(session(SESSION_VERIFY_CODE) . '(' . $verifycode_setting['order'] . ') => ' . $code, LOG_TYPE_VERIFYCODE_ERROR);

        return L('VERIFY_CODE,NOT_CORRECT');
    }

    /**
     * 双重加密密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-02 10:39:04
     * @lastmodify      2013-01-22 11:12:54 by mrmsl
     *
     * @param string $password 密码
     *
     * @return string 双重加密后的密码
     */
    protected function _encryptPassword($password) {
        return md5(md5($password));
    }

	   /**
     * 获取管理员id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-09 11:01:05
     * @lastmodify      2013-01-22 11:13:17 by mrmsl
     *
     * @return int 管理员id
     */
    protected function _getAdminId() {
        $admin_info = Yaf_Registry::get(SESSION_ADMIN_KEY);

        return $admin_info ? $admin_info['admin_id'] : 0;
    }

	   /**
     * 获取管理员姓名
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-09 11:11:54
     * @lastmodify      2013-01-22 11:13:27 by mrmsl
     *
     * @return string 管理 员姓名
     */
    protected function _getAdminName() {
        $admin_info = Yaf_Registry::get(SESSION_ADMIN_KEY);

        return $admin_info ? $admin_info['realname'] : '';
    }

    /**
     * 获取表数据缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:13:36 by mrmsl
     *
     * @param int    $id     数据id。默认0
     * @param string $name   文件名。默认null，模块名称
     * @param bool   $reload true重新加载。默认false
     * @param string $path   缓存路径。默认MODULE_CACHE_PATH
     *
     * @return mixed 如果不指定id，返回全部缓存，如果指定id并指定id缓存存在，返回指定id缓存，否则返回false
     */
    protected function _getCache($id = 0, $name = null, $reload = false, $path = MODULE_CACHE_PATH) {
        $data = F($name ? $name : $this->getModelName(), '', $path, $reload);

        if ($id) {

            if (strpos($id, '.')) {//直接获取某一字段值
                list($id, $key) = explode('.', $id);
                return isset($data[$id][$key]) ? $data[$id][$key] : false;
            }
            else {
                return isset($data[$id]) ? $data[$id] : false;
            }
        }

        return $data;
    }

    /**
     * 获取checkbox值
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:14:45 by mrmsl
     *
     * @param int    $value     值。默认0，即不勾选时
     * @param string $separator 一组复选框值连接符。默认,
     *
     * @return mixed checkbox数值或用$separator连接的字符串
     */
    protected function _getCheckboxValue($value = 0, $separator = ',') {

        if (func_num_args() == 2) {//一组复选框,name[],name[]...
            return join($separator, $value);
        }

        return is_numeric($value) ? intval($value) : '';
    }

    /**
     * 获取当前页面url
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:15:26 by mrmsl
     *
     * @return string 当前页面url
     */
    protected function _getPageUrl() {
        return REQUEST_METHOD . ' ' . SITE_URL . REQUEST_URI;
    }

    /**
     * 获取来路页面url
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:15:36 by mrmsl
     *
     * @return string 如果有来路，返回来路页面url，否则返回空字符串
     */
    protected function _getRefererUrl() {
        return REFERER_PAGER;
    }

    /**
     * 设置自动完成规则
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-26 08:58:58
     * @lastmodify      2013-01-22 11:15:50 by mrmsl
     *
     * @param string $field_name 字段名
     * @param mixed  $auto       自动完成规则
     *
     * @return object this
     */
    protected function _setAutoOperate($field_name, $auto) {
        $auto = is_array($auto) ? $auto : explode('#', $auto);

        if (count($auto) < 4) {//简写形式method#params
            $method = $auto[0];//验证方法
            $params = isset($auto[1]) ? $auto[1] :  null;//额外参数

            switch($method) {
                case '_setPassword'://密码array('password', 'setPassword', Model::MODEL_BOTH, 'callback', 'data')
                case '_unsigned'://unsigned类型array('sort_order', 'unsigned', Model::MODEL_BOTH, 'callback', 'data'),
                case '_getCheckboxValue': //复选框array('is_restrict', '_getCheckboxValue', Model::MODEL_BOTH, 'callback'
                case '_addtime'://strtotime array('add_time', 'add_time', Model::MODEL_BOTH, 'callback')
                case '_strtotime'://strtotime array('lock_start_time', 'strtotime', Model::MODEL_BOTH, 'callback')
                    $auto  = array($method, Model::MODEL_BOTH, 'callback');
                    isset($params) ? $auto[] = $params : '';
                    break;

                case 'get_user_id'://用户id array('user_id', 'get_user_id', Model::MODEL_BOTH, 'function')
                case 'get_client_ip'://用户ip  array('admin_ip', 'get_client_ip', Model::MODEL_INSERT, 'function', '1'),
                case 'time'://时间戳array('last_time', 'time', Model::MODEL_BOTH, 'function'),

                    if ('insert' == $params) {
                        $when = Model::MODEL_INSERT;
                    }
                    else {
                        $when = isset($this->_auto_validate_map[$params]) ? $this->_auto_validate_map[$params] : Model::MODEL_BOTH;
                    }

                    $auto  = array($method, $when, 'function');
                    isset($params) ? $auto[] = $params : '';
                    break;

                case 'string'://字符串填充array('controller', CONTROLLER_NAME, Model::MODEL_BOTH, 'string')
                    $auto = array($method, Model::MODEL_BOTH, 'string');
                    break;

                /*case 'getAdminId'://管理员idarray('admin_name', 'getAdminId', Model::MODEL_INSERT, 'callback')
                case 'getAdminName'://管理员姓名array('page_url', 'getAdminName', Model::MODEL_INSERT, 'callback')
                case 'getPageUrl'://当前页面array('page_url', 'getPageUrl', Model::MODEL_INSERT, 'callback'),
                case 'getRefererUrl'://来路页面array('referer_url', 'getRefererUrl', Model::MODEL_INSERT, 'callback')*/
                default:
                    $auto  = array($method, $params == 'insert' ? Model::MODEL_INSERT : Model::MODEL_BOTH, 'callback');
                    break;
            }//end switch
        }//end if

        array_unshift($auto, $field_name);
        $this->_auto[$field_name] = $auto;
        return $this;
    }//end _setAutoOperate

    /**
     * 设置表数据缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:17:05 by mrmsl
     *
     * @param array  $data  手工设置缓存数据
     * @param string $name  文件名。默认模块名称
     * @param mixed  $model 模型。默认null，当前模型
     * @param string $path  缓存路径。默认MODULE_CACHE_PATH
     *
     * @return object this
     */
    protected function _setCache($data = array(), $name = null, $model = null, $path = MODULE_CACHE_PATH) {

        if ($model === null) {
            $model = $this;
        }
        else {
            $model = is_string($model) ? D($model) : $model;
        }

        F($name ? $name : $this->getModelName(), $data ? $data : $model->key_column($model->getPk())->select(), $path);

        return $model;
    }

    /**
     * 设置自动验证规则
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-26 08:58:58
     * @lastmodify      2013-02-01 13:29:40 by mrmsl
     *
     * @param string $field_name   字段名
     * @param mixed  $validate     自动验证规则
     * @param bool   $set_property true重设_validate属性。默认true
     *
     * @return array 自动验证规则
     */
    protected function _setValidate($field_name, $validate, $set_property = true) {
        $validate = is_array($validate) ? $validate : explode('#', $validate);

        if (count($validate) < 4) {//简写形式method#msg#params
            $method     = $validate[0];//验证方法
            $msg        = $validate[1];//提示信息
            $msg        = strpos($msg, '%') === 0 ? substr($msg, 1) : L($msg);
            $params     = isset($validate[2]) ? $validate[2] :  null;//额外参数

            switch($method) {
                case 'return'://只过滤array('', '{%PERMISSION,DATA,INVALID}', Model::MUST_VALIDATE, 'return')
                    $validate = array('', $msg . L('DATA,INVALID'), Model::MUST_VALIDATE, $method);
                    break;

                case 'unsigned': //unsigned类型array('', '{%PRIMARY_KEY,DATA,INVALID}', Model::MUST_VALIDATE, 'unsigned')
                    $min      = isset($params) ? $params : -1;//PLEASE_SELECT,PARENT_MENU
                    $validate = array($min, $msg . (strpos($validate[1], ',') ? '' : L('MUST,GT') . $min), Model::MUST_VALIDATE, $method);
                    break;

                case 'notblank'://不允许为空array('', '{%PLEASE_ENTER,CONTROLLER_NAME_MENU}', Model::MUST_VALIDATE, 'notblank')
                    $validate = array('', L('PLEASE_ENTER') . $msg, Model::MUST_VALIDATE, $method);
                    break;

                case 'validate_dir'://路径验证array('validate_dir', 'css路径非法', Model::VALUE_VALIDATE, 'function', Model::MODEL_BOTH, 'css路径|ROOT')
                    $validate = array($method, $msg . L('DATA,INVALID'), Model::MUST_VALIDATE, 'function', Model::MODEL_BOTH, $msg . (isset($params) ? '|' . $params : ''));
                    break;

                case 'validate_path'://路径验证，不需要判断物理路径是否存在，只是判断/开始或结尾
                    $validate = array('validate_dir', $msg . L('DATA,INVALID'), Model::MUST_VALIDATE, 'function', Model::MODEL_BOTH, $msg . '|null' . (isset($params) ? '|' . $params : ''));
                    break;

                case '_checkLength'://验证长度 '_checkLength', '{%USERNAME,DATA,INVALID}', Model::VALUE_VALIDATE, 'callback', Model::MODEL_BOTH, array('USERNAME', 0, 20)
                    $params        = explode('|', $params);
                    $must_validate = $params[1] == 'must';//必须验证
                    $params[0]     = $validate[1];
                    $validate      = array('_checkLength', $msg . L('DATA,INVALID'), $must_validate ? Model::MUST_VALIDATE : Model::VALUE_VALIDATE, 'callback', Model::MODEL_BOTH, $params);
                    break;

                default://array('checkUsername', '{%PLEASE_ENTER,USERNAME}', Model::MUST_VALIDATE, 'callback', model::MODEL_BOTH, 'data')
                    $validate = array($method, $msg, Model::MUST_VALIDATE, 'callback', Model::MODEL_BOTH, $params);
                    break;
            }//end switch
        }//end if
        else {
            $validate = join('#', $validate);

            foreach ($this->_auto_validate_map as $k => $v) {//MUST_VALIDATE => self::MUST_VALIDATE
                $validate = strpos($validate, $k) ? str_replace($k, $v, $validate) : $validate;
            }

            $validate = explode('#', $validate);
        }

        array_unshift($validate, $field_name);

        if ($set_property) {
            $this->_validate[] = $validate;
        }

        return $validate;
    }//end _setValidate

    /**
     * 将时间表达式转化成unix时间戳
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-05 15:54:10
     * @lastmodify      2013-01-22 11:18:19 by mrmsl
     *
     * @param string $datetime 时间表达式
     *
     * @return 转化成功，返回转化的时间戳，否则返回0
     */
    protected function _strtotime($datetime = '') {
        return $datetime && ($datetime = strtotime($datetime)) ? $datetime : 0;
    }

    /**
     * 检测时间区域是否存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-11 13:18:01
     * @lastmodify      2013-01-22 11:18:39 by mrmsl
     *
     * @param string $timezone 区域
     *
     * @throws Exception 区域不存在
     * @return true存在，否则false
     */
    protected function _timezone($timezone) {
        try {
            new DateTimeZone($timezone);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }

    /**
     * 释放值，无须入库
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-10 15:40:08
     * @lastmodify      2013-01-22 11:19:48 by mrmsl
     *
     * @param mixed $value 值，没作用，只是条合语法。默认false
     *
     * @return bool false
     */
    protected function _unsetValue($value = false) {
        return false;
    }

    /**
     * 回调unsigned字段，如排序sort_order=-1时，unset掉，否则mysql会产生Out of range value for column 'sort_order' at row 1警告
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-10 16:03:55
     * @lastmodify      2013-01-22 11:20:37 by mrmsl
     *
     * @param mixed $value 值。默认0
     * @param array  $data _POST数据
     * @param int   $min   最小值。默认0
     *
     * @return mixed 小于0，新增返回false；编辑返回主键值；否则返回其值
     */
    protected function _unsigned($value = 0, $data = array(), $min = 0) {
        $pk_value = empty($data[$this->_pk_field]) ? 0 : $data[$this->_pk_field];

        return $value < $min ? ($pk_value ? $pk_value : false) : $value;
    }

    /**
     * 构造函数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-26 12:44:28
     * @lastmodify      2013-01-22 11:21:14 by mrmsl
     *
     * @return void 无返回值
     */
    public function __construct() {

        $this->_auto_validate_map = array(//验证key/value by mrmsl on 2013-03-23 14:19:38
            'MODEL_INSERT'      => self::MODEL_INSERT,
            'MODEL_UPDATE'      => self::MODEL_UPDATE,
            'MODEL_BOTH'        => self::MODEL_BOTH,
            'EXISTS_VALIDATE'   => self::EXISTS_VALIDATE,
            'MUST_VALIDATE'     => self::MUST_VALIDATE,
            'VALUE_VALIDATE'    => self::VALUE_VALIDATE,
        );

        if ($this->_db_fields) {//字段
            $db_fields = array();

            foreach ($this->_db_fields as $field => $item) {

                if (isset($item['validate'])) {//自动验证
                    $validator = $item['validate'];

                    if (is_array($validator)) {//多种验证规则

                        foreach ($validator as $v) {
                            $this->_setValidate($field, $v);
                        }
                    }
                    else {
                        $this->_setValidate($field, $validator);
                    }
                }

                $db_fields[] = $field;
            }

            $this->_fields = $db_fields;//表字段
        }

        if ($this->_auto) {//自动完成
            $auto = $this->_auto;//交换$this->_auto
            $this->_auto = array();

            foreach ($auto as $field => $v) {
                $this->_setAutoOperate($field, $v);
            }

        }

        parent::__construct();
    }//end __construct

	   /**
     * 添加系统操作日志
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-26 12:47:11
     * @lastmodify      2013-01-22 11:23:55 by mrmsl
     *
     * @param string $content   日志内容。默认''，取db最后执行sql
     * @param int    $log_type  日志类型。默认LOG_TYPE_SQL_ERROR，sql错误
     *
     * @return void 无返回值
     */
    public function addLog($content = '', $log_type = LOG_TYPE_SQL_ERROR) {
        $data = array(
            'content'  => LOG_TYPE_SQL_ERROR == $log_type && !$content ? $this->getLastSql() . '<br />' . $this->getDbError() : $content,
            'log_type' => $log_type,
        );

        $log_model = D('Log');
        $log_model->autoOperation($data, Model::MODEL_INSERT);
        $log_model->add($data);
        $log_model->commit();

        if ($trigger_error = C($key = 'TRIGGER_ERROR')) {//同时写文本记录
            call_user_func_array(array($this->_module, 'triggerError'), $trigger_error);
            C($key, false);
        }
    }

    /**
     * 验证自动创建数据是否成功
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 11:25:30 by mrmsl
     *
     * @param string $method 自动验证数据方法。默认create
     *
     * @return string|true true验证成功，返回true，否则返回错误信息
     */
    public function checkCreate($method = 'create') {

        if ('POST' != REQUEST_METHOD && !__GET) {
            $this->addLog(L(empty($this->data[$this->getPk()]) ? 'ADD' : 'EDIT') . L('CONTROLLER_NAME,FAILURE,%.,_DATA_TYPE_INVALID_'), LOG_TYPE_INVALID_PARAM);
            return L('_DATA_TYPE_INVALID_');
        }

        $result = true;

        if (!$this->$method()) {
            $error = $this->getError();
            $result = is_array($error) ? join('<br />', $error) : $error;
            $this->addLog(__function__ . '<br />' . $result, LOG_TYPE_VALIDATE_FORM_ERROR);
        }

        return $result;
    }

    /**
     * 检测是否是叶，即是否有子节点
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-19 21:39:32
     * @lastmodify      2013-01-22 11:26:15 by mrmsl
     *
     * @param int $pk_value 主键值
     * @param string $where where条件
     *
     * @return bool false有子节点，即非叶，否则true
     */
    public function checkIsLeaf($pk_value, $where) {
        static $data = null;

        $data = $data === null ? $this->field('parent_id')->group('parent_id')->where($where)->key_column('parent_id')->select() : $data;

        if (!$data) {
            return true;
        }

        return !isset($data, $pk_value);
    }

    /**
     * 获取自动完成
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-14 09:56:10
     * @lastmodify      2013-01-22 11:27:24 by mrmsl
     *
     * @param mixed $field 字段
     * @param bool  $set_property true重设$_auto属性。默认true
     *
     * @return array 自动完成
     */
    public function getAutoOperate($field, $set_property = true) {
        $field_arr = is_array($field) ? $field : explode(',', $field);
        $auto      = array();

        foreach ($field_arr as $field) {
            $auto[] = $this->_auto[$field];
        }

        if ($set_property) {
            $this->_auto = $auto;
        }


        return $auto;
    }

    /**
     * 动态获取自动验证规则
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 11:29:44
     * @lastmodify      2013-01-22 11:29:49 by mrmsl
     *
     * @param mixed $field        字段
     * @param bool  $set_property true重设_validate属性。默认true
     *
     * @return array 自动验证规则
     */
    public function getValidate($field, $set_property = true) {
        $field_arr  = is_array($field) ? $field : explode(',', $field);
        $validation = array();

        foreach ($field_arr as $field) {

            if (isset($this->_db_fields[$field]['validate'])) {//自动验证
                $validator = $this->_db_fields[$field]['validate'];

                if (is_array($validator)) {//多种验证规则

                    foreach ($validator as $v) {
                        $validation[] = $this->_setValidate($field, $v, $set_property);;
                    }
                }
                else {
                    $validation[] = $this->_setValidate($field, $validator, $set_property);;
                }
            }
        }

        return $validation;
    }
}