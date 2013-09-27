<?php
/**
 * Model模型类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            Model.class.php
 * @package         Yab\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-21 10:36:54
 * @lastmodify      $Date$ $Author$
 */

class Model {
    //操作状态
    /**
     * 插入模型数据
     */
    const MODEL_INSERT    = 1;
    /**
     * 更新模型数据
     */
    const MODEL_UPDATE    = 2;
    /**
     * 插入或更新模型数据
     */
    const MODEL_BOTH      = 3;
    /**
     * 表单存在字段则验证
     */
    const EXISTS_VALIDATE = 0;
    /**
     * 必须验证
     */
    const MUST_VALIDATE   = 1;
    /**
     * 表单值不为空则验证
     */
    const VALUE_VALIDATE  = 2;

    /**
     * @var object $_ext_model 当前使用的扩展模型。默认null
     */
    private   $_ext_model    = null;//当前使用的扩展模型
    /**
     * @var object $_db 当前数据库操作对象。默认null
     */
    protected $_db           = null;
    /**
     * @var string $_pk_field 数据表主键字段名称。默认id
     */
    protected $_pk_field     = 'id';
    /**
     * @var string|null $_table_prefix 数据表前缀，null为无前缀。默认''，自动获取C('DB_PREFIX')
     */
    protected $_table_prefix = '';
    /**
     * @var string $_model_name 模型名称。默认''，自动获取
     */
    protected $_model_name         = '';
    /**
     * @var string $_db_name 数据库名称。默认''，如果__construct('dbname.modelname')，则_db_name=dbname，否则_db_name=DB_NAME
     */
    protected $_db_name      = '';
    /**
     * @var string $_table_name 数据表名(不包含表前缀)。默认''，自动获取
     */
    protected $_table_name   = '';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认''，自动获取
     */
    protected $_true_table_name = '';
    /**
     * @var string $_error 最近错误信息。默认''
     */
    protected $_error        = '';//最近错误信息
    /**
     * @var array $_fields 字段信息
     */
    protected $_fields       = array();
    /**
     * @var array $_data 模型数据信息，用于INSERT、UPDATE
     */
    protected $_data         = array();
    /**
     * @var array $_options 查询表达式参数
     */
    protected $_options      = array();
    /**
     * @var array $_validate 自动验证定义
     */
    protected $_validate    = array();
    /**
     * @var array $_auto 自动完成定义
     */
    protected $_auto        = array();
    /**
     * @var array $_map 字段映射定义
     */
    protected $_map         = array();
    /**
     * @var bool $_auto_check_fields true自动检测数据表字段信息。默认false
     */
    protected $_auto_check_fields = false;
    /**
     * @var bool $_patch_validate true批处理验证。默认true
     */
    protected $_patch_validate = true;

    /**
     * 自动记录数据表信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:29:44 by mrmsl
     *
     * @return object this
     */
    private function _checkTableInfo() {

        if (empty($this->_fields)) {//只在第一次执行记录

            if (C('DB_FIELDS_CACHE')) {//如果数据表字段没有定义则自动获取
                $db = $this->_db_name ? $this->_db_name : DB_NAME;
                !($this->_fields = F('_fields/' . $db . '.' . $this->_modle_name)) && $this->flush();
            }
            else {
                $this->flush();
            }
        }

        return $this;
    }

    /**
     * 对保存到数据库的数据进行处理
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:30:43 by mrmsl
     *
     * @param array $data 要操作的数据
     *
     * @return array 处理后的数据
     */
    private function _facade($data) {

        if ($this->_fields && !C('_FACADE_SKIP')) {//检查非数据字段

            foreach ($data as $key => $val) {

                if ((!in_array($key, $this->_fields, true) || 0 === strpos($key, '_')) && !isset($this->_auto[$key])) {//_开头
                    unset($data[$key]);
                }
                elseif (C('DB_FIELDTYPE_CHECK') && is_scalar($val)) {//字段类型检查
                    $this->_parseType($data, $key);
                }
            }
        }

        'skip' === C('_FACADE_SKIP') && C('_FACADE_SKIP', false);

        $this->_beforeWrite($data);

        return $data;
    }

    /**
     * 分析表达式
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:07:15 by mrmsl
     *
     * @param array $options 表达式参数
     *
     * @return array 表达式参数
     */
    private function _parseOptions($options = array()) {
        is_array($options) && ($options = array_merge($this->_options, $options));
        $this->_options = array();//查询过后清空sql表达式组装 避免影响下次查询
        !isset($options['table']) && ($options['table'] = $this->getTableName());//自动获取表名
        !empty($options['alias']) && ($options['table'] = array($options['table'] => $options['alias']));//别名
        $options['model'] = $this->_modle_name;//记录操作的模型名称

        if (C('DB_FIELDTYPE_CHECK')) {//字段类型验证
            if (isset($options['where']) && is_array($options['where'])) {

                foreach ($options['where'] as $key => $val) {//对数组查询条件进行字段类型检查

                    if (in_array($key, $this->_fields, true) && is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    }
                }
            }
        }

        $this->_afterOptions($options);//表达式过滤

        return $options;
    }

    /**
     * 处理字段映射
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:59:16 by mrmsl
     *
     * @param array $data 当前数据
     * @param int  $type  类型 0写入 1读取。默认1
     *
     * @return array 处理后数据
     *
     */
    private function _parseFieldsMap($data, $type = 1) {

        if ($this->_map) {//检查字段映射

            //用空间换时间
            if ($type) {//读取

                foreach ($this->_map as $key => $val) {

                    if (isset($data[$val])) {
                        $data[$key] = $data[$val];
                        unset($data[$val]);
                    }
                }
            }
            else {

                foreach ($this->_map as $key => $val) {

                    if (isset($data[$key])) {
                        $data[$val] = $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }

        return $data;
    }//_parseFieldsMap

    /**
     * 数据类型检测
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:00:04 by mrmsl
     *
     * @param mixed  $data 数据
     * @param string $key  字段名
     *
     * @return void 无返回值
     */
    private function _parseType(&$data, $key) {
        $field_type = strtolower($this->_fields['_type'][$key]);

        if (false === strpos($field_type, 'bigint') && false !== strpos($field_type, 'int')) {
            $data[$key] = intval($data[$key]);
        }
        elseif (false !== strpos($field_type, 'float') || false !== strpos($field_type, 'double')) {
            $data[$key] = floatval($data[$key]);
        }
        elseif (false !== strpos($field_type, 'bool')) {
            $data[$key] = (bool)$data[$key];
        }
    }

    /**
     * 验证表单字段
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:00:19 by mrmsl
     *
     * @param array $data 整个验证数组
     * @param array $val  验证因子
     *
     * @return bool true验证成功，否则如果非批量，返回false，否则返回null
     */
    private function _validateField($data, $val) {

        if (!isset($data[$val[0]]) || true !== ($result = $this->_validateFieldItem($data, $val))) {
            $error = empty($result) ? $val[2] : $result;

            if ($this->_patch_validate) { //批量
                $this->_error[$val[0]] = isset($this->_error[$val[0]]) ? $this->_error[$val[0]] . ',' . $error : $error;

                return null;
            }

            $this->_error = $error;

            return false;
        }

        return true;
    }

    /**
     * 根据验证因子验证字段
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:01:03 by mrmsl
     *
     * @param array $data 整个验证数组
     * @param array $val  验证因子
     *
     * @return true|string true验证成功，否则返回false或错误信息
     */
    private function _validateFieldItem($data, $val) {

        switch ($val[3]) {
            case 'function': //使用函数进行验证
            case 'callback': //调用方法进行验证
                $args = array();

                if (isset($val[6])) { //附加参数
                    $args  = is_array($val[6]) ? $val[6] : explode('|', $val[6]);
                    $index = array_search('data', $args, true);

                    //传整个_POST数组
                    if (false !== $index) {
                        $args[$index] = __GET ? $_GET : $_POST;
                    }
                }

                array_unshift($args, $data[$val[0]]);

                if ('function' == $val[3]) {

                    //是否为允许的回调函数 by mrmsl on 2012-09-07 14:47:27
                    if (!ALLOW_AUTO_VALIDATE_FUNCTION || false === strpos(ALLOW_AUTO_VALIDATE_FUNCTION, ',' . $val[1] . ',')) {
                        $this->addLog(L('TRY,USE,AUTO,VALIDATE,FUNCTION') . $val[1], LOG_TYPE_INVALID_PARAM);

                        return false;
                    }

                    return call_user_func_array($val[1], $args);
                }
                else {
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
                break;

            case 'notblank':
                return isset($data[$val[0]]) && '' !== $data[$val[0]];
                break;

            case 'notempty': //!empty() by mrmsl on 2012-08-24 09:28:47
                return !empty($data[$val[0]]);
                break;

            case 'unsigned': //unsigned类型判断 by mrmsl on 2012-08-24 09:35:50
                return isset($data[$val[0]]) && intval($data[$val[0]]) > ('' === $val[1] ? - 1 : intval($val[1]));
                break;

            case 'return' : //仅仅使用Filter过滤，不需要验证 by mrmsl on 2012-09-07 16:23:58
                return true;
                break;

            case 'confirm' : //验证两个字段是否相同
                return isset($data[$val[0]]) && isset($data[$val[1]]) && $data[$val[0]] == $data[$val[1]];
                break;

            case 'url' : //url 通过Filter类验证链接地址 by mrmsl on 2013-04-27 10:38:35
                return !empty($data[$val[0]]) && strlen($data[$val[0]]) > 9 && Filter::filterVar($data[$val[0]], 'url') && preg_match('#^http://[a-z0-9]+\.[a-z0-9]+#i', $data[$val[0]]);
                break;

            case 'unique' : //验证某个值是否唯一

                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }

                $map = array();

                if (is_array($val[0])) { //支持多个字段验证

                    foreach ($val[0] as $field) {
                        $map[$field] = $data[$field];
                    }
                }

                else {
                    $map[$val[0]] = $data[$val[0]];
                }

                if (!empty($data[$this->getPk()])) { //完善编辑的时候验证唯一
                    $map[$this->getPk()] = array('neq', $data[$this->getPk()]);
                }

                return $this->field($this->getPk())->where($map)->find() ? false : true;
                break;

            default: //检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[3]);
        } //end switch
    } //end _validateFieldItem

    /**
     * 切换数据库后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:57:44 by mrmsl
     *
     * @return void 无返回值
     */
    protected function _afterDb() {
    }

    /**
     * 删除后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:54:38 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterDelete($data, $options) {
    }

    /**
     * find后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:57:03 by mrmsl
     *
     * @param $data     结果集数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterFind(&$data, $options) {
    }

    /**
     * 插入后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:52:30 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterInsert($data, $options) {
    }

    /**
     * options后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:09:13 by mrmsl
     *
     * @param $options     表达式
     *
     * @return void 无返回值
     */
    protected function _afterOptions(&$options) {
    }

    /**
     * select后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:55:58 by mrmsl
     *
     * @param $data     结果集数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterSelect(&$data, $options) {
    }

    /**
     * 更新后置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:53:04 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterUpdate($data, $options) {
    }

    /**
     * 自动表单验证
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:35:45 by mrmsl
     *
     * @param array  $data 验证数据
     * @param int    $type 验证类型
     *
     * @return bool true验证成功，否则false
     */
    protected function _autoValidate($data, $type) {

        if ($this->_validate) {//如果设置了数据自动验证则进行数据验证
            $this->_error = $this->_patch_validate ? array() : $this->_error; //重置验证错误信息

            //验证因子定义格式array(field,rule,message,condition,type,when,params) thinkphp
            //验证因子定义格式array(field,rule,message,type,condition,when,params) yablog by mrmsl on 2013-09-27 11:13:03
            foreach ($this->_validate as $key => $val) {

                if (empty($val[5]) || $val[5] == self::MODEL_BOTH || $val[5] == $type) {//判断是否需要执行验证

                    if (0 === strpos($val[2], '{%') && strpos($val[2], '}')) { //支持提示信息的多语言 使用 {%语言定义} 方式
                        $val[2] = L(substr($val[2], 2, -1));
                    }

                    $val[3] = isset($val[3]) ? $val[3] : 'regex';
                    $val[4] = empty($val[4]) ? self::MUST_VALIDATE : $val[4];

                    switch ($val[4]) { //判断验证条件
                        case self::MUST_VALIDATE: //必须验证,不管表单是否有设置该字段

                            if (false === $this->_validateField($data, $val)) {
                                return false;
                            }

                            break;

                        case self::VALUE_VALIDATE: //值不为空的时候才验证

                            if (isset($data[$val[0]]) && '' != trim($data[$val[0]]) && false === $this->_validateField($data, $val)) {
                                return false;
                            }

                            break;

                        default: //默认表单存在该字段就验证
                            if (isset($data[$val[0]]) && false === $this->_validateField($data, $val)) {
                                return false;
                            }
                    } //end switch
                } //end if
            } //end foreach

            return $this->_error ? false : true; //验证结果
        } //end if

        return true;
    } //end _autoValidate

    /**
     * 插入前置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:39:45 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return mixed|false false终止插入操作，否则继续执行
     */
    protected function _beforeInsert(&$data, $options) {
    }

    /**
     * 更新前置操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:46:18 by mrmsl
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return mixed|false false终止更新操作，否则继续执行
     */
    protected function _beforeUpdate(&$data, $options) {
    }

    /**
     * 写前操作
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 13:49:56 by mrmsl
     *
     * @param $data     操作数据数据
     *
     * @return void 无返回值
     */
    protected function _beforeWrite(&$data) {
    }

    /**
     * 解析SQL语句
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:37:09 by mrmsl
     *
     * @param string  $sql  SQL指令
     * @param bool   $parse true解析SQL
     *
     * @return string sql语句
     */
    protected function _parseSql($sql, $parse) {

        if ($parse) {//分析表达式
            $options = $this->_parseOptions();
            $sql     = $this->_db->parseSql($sql, $options);
        }
        elseif (strpos($sql, $v = '__TABLE__')) {//替换表名
            $sql = str_replace($v, $this->getTableName(), $sql);
        }

        $this->_db->setModel($this->_modle_name);

        return $sql;
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:37:47 by mrmsl
     *
     * @param string $method 方法名称
     * @param array  $args   调用参数

     * @throw Exception 如果调用了不存在的方法
     * @return mixed
     */
    public function __call($method, $args) {

        //连贯操作的实现 'table', 'where', 'order', 'limit', 'page', 'alias', 'having', 'group', 'lock', 'distinct', 'key_column'
        if (in_array($method = strtolower($method), array('table', 'where', 'order', 'limit', 'page', 'alias', 'having', 'group', 'lock', 'distinct', 'key_column'), true)) {
            $this->_options[$method] = $args[0];

            return $this;
        }
        //统计查询的实现'count', 'sum', 'min', 'max', 'avg'
        elseif (in_array($method, array('count', 'sum', 'min', 'max', 'avg'), true)) {
            $field = isset($args[0]) ? $args[0] : '*';

            return $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
        }
        //根据某个字段获取记录getByUserId => user_id
        elseif (0 === strpos($method, 'getby')) {
            $field = parse_name(substr($method, 5));
            $where[$field] = $args[0];

            return $this->where($where)->find();
        }
        //根据某个字段获取记录的某个值getFieldByUserId => user_id
        elseif (0 === strpos($method, 'getfieldby')) {
            $name = parse_name(substr($method, 10));
            $where[$name] = $args[0];

            return $this->where($where)->getField($args[1]);
        }

        throw new Exception(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
    }

    /**
     * 架构函数 取得DB类的实例对象 字段检查
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:41:12 by mrmsl
     *
     * @param string $name         模型名称。默认''
     * @param string $table_prefix 表前缀。默认''
     * @param mixed  $connection   数据库连接信息。默认''
     *
     * @return void 无返回值
     */
    public function __construct($name = '', $table_prefix = '', $connection = '') {
        method_exists($this, '_initialize') && $this->_initialize();//模型初始化

        if (!empty($name)) {//获取模型名称

            if (strpos($name, '.')) { //支持 数据库名.模型名的 定义
                list($this->_db_name, $this->_modle_name) = explode('.', $name);
            }
            else {
                $this->_modle_name = $name;
            }
        }
        elseif (empty($this->_modle_name)) {
            $this->_modle_name = $this->getModelName();
        }

        //设置表前缀
        if (null === $table_prefix) {//前缀为Null表示没有前缀
            $this->_table_prefix = '';
        }
        elseif ('' != $table_prefix) {
            $this->_table_prefix = $table_prefix;
        }
        else {
            $this->_table_prefix = $this->_table_prefix ? $this->_table_prefix : C('DB_PREFIX');
        }

        //数据库初始化操作
        //获取数据库操作对象
        //当前模型有独立的数据库连接信息
        $this->db(0, empty($this->connection) ? $connection : $this->connection);
    }//end __construct

    /**
     * 获取数据对象的值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:43:41 by mrmsl
     *
     * @param string $name 名称
     *
     * @return mixed 数据对象值。如果数据对象不存在，返回null
     */
    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    /**
     * 检测数据对象的值是否已经设置
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:44:31 by mrmsl
     *
     * @param string $name 名称
     *
     * @return bool true已经设置，否则false
     */
    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    /**
     * 设置数据对象的值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:46:06 by mrmsl
     *
     * @param string $name 名称
     * @param mixed  $value 值
     *
     * @return void 无返回值
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;//设置数据对象属性
    }

    /**
     * 销毁数据对象的值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:46:23 by mrmsl
     *
     * @param string $name 名称
     *
     * @return void 无返回值
     */
    public function __unset($name) {
        unset($this->_data[$name]);
    }

    /**
     * 新增数据
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:47:00 by mrmsl
     *
     * @param mixed   $data    数据
     * @param array   $options 参数表达式
     * @param boolean $replace 是否replace
     *
     * @return bool|int 新增成功，返回true或自增id，否则返回false
     */
    public function add($data = '', $options = array(), $replace = false) {

        if (empty($data)) {

            if (!empty($this->_data)) {//没有传递数据，获取当前数据对象的值
                $data = $this->_data;
                $this->_data = array();//重置数据
            }
            else {
                $this->_error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }

        $options = $this->_parseOptions($options); //分析表达式
        $data    = $this->_facade($data);//数据处理

        if (false === $this->_beforeInsert($data, $options)) {
            return false;
        }

        $result = $this->_db->insert($data, $options, $replace);//写入数据到数据库

        if (false !== $result && ($insert_id = $this->getLastInsertID())) {//自增主键返回插入ID
            $data[$this->getPk()] = $insert_id;
            $this->_afterInsert($data, $options);

            return $insert_id;
        }

        return $result;
    }//end add

    /**
     * 自动表单令牌验证
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:48:45 by mrmsl
     *
     * @param array $data 整个表单数据
     *
     * @todo 支持ajax无刷新多次提交
     * @return bool true验证成功，否则false
     */
    public function autoCheckToken($data) {

        if ($name = C('TOKEN_ON')) {

            if (!isset($data[$name]) || !isset($_SESSION[$name])) { //令牌数据无效
                return false;
            }

            //令牌验证
            list($key, $value) = explode('_', $data[$name]);

            $check_result = $_SESSION[$name][$key] == $value;

            if (C('TOKEN_RESET')) { //开启TOKEN重置
                unset($_SESSION[$name][$key]);
            }

            return $check_result;
        }

        return true;
    }

    /**
     * 自动填充处理
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:50:11 by mrmsl
     *
     * @param array $data 整个表单数据
     * @param int   $type 类型
     *
     * @return array $data 处理后数据
     */
    public function autoOperation(&$data, $type) {

        if ($this->_auto) { //自动填充

            foreach ($this->_auto as $auto) {
                //填充因子定义格式 array('field','填充内容','填充条件','附加规则',[额外参数]) thinkphp
                //填充因子定义格式 array('field','填充内容','附加规则','填充条件',[额外参数]) yablog by mrmsl on 2013-09-27 11:18:15
                $auto[3] = empty($auto[3]) ? self::MODEL_INSERT : $auto[3]; //默认为新增的时候自动填充

                if ($type == $auto[3] || self::MODEL_BOTH == $auto[3]) {

                    switch ($auto[2]) {//附加规则
                        case 'function': //使用函数进行填充 字段的值作为参数
                        case 'callback': //使用回调方法
                            $args = array();

                            if (isset($auto[4])) { //附加参数
                                $args  = is_array($auto[4]) ? $auto[4] : explode('|', $auto[4]);
                                $index = array_search('data', $args, true);

                                //传整个_POST数组
                                if (false !== $index) {
                                    $args[$index] = __GET ? $_GET : $_POST;
                                }
                            }

                            isset($data[$auto[0]]) && array_unshift($args, $data[$auto[0]]);

                            if ('function' == $auto[2]) {

                                //是否为允许的回调函数 by mrmsl on 2012-09-07 14:47:27
                                if (false === strpos(ALLOW_AUTO_OPERATION_FUNCTION, ',' . $auto[1] . ',')) {
                                    $this->addLog(L('TRY,USE,AUTO_OPERATION,FUNCTION') . $auto[1], LOG_TYPE_INVALID_PARAM);
                                }
                                else {
                                    $data[$auto[0]] = call_user_func_array($auto[1], $args);
                                }
                            }
                            else {
                                $data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
                            }
                            break;

                        case 'field': //用其它字段的值进行填充
                            $data[$auto[0]] = $data[$auto[1]];
                            break;

                        case 'string':
                        default : //默认作为字符串填充
                            $data[$auto[0]] = $auto[1];
                    }

                    if (false === $data[$auto[0]]) {
                        unset($data[$auto[0]]);
                    }
                }
            }//end switch
        }//end if

        return $data;
    }//end autoOperation

    /**
     * 生成查询SQL 可用于子查询
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:51:19 by mrmsl
     *
     * @param array $options 表达式参数
     *
     * @return string 生成好的sql语句
     *
     */
    public function buildSql($options = array()) {
        $options = $this->_parseOptions($options);//分析表达式

        return '(' . $this->_db->buildSelectSql($options) . ')';
    }

    /**
     * 查询缓存
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:51:30 by mrmsl
     *
     * @param mixed  $key    键。默认true
     * @param int    $expire 过期时间。默认''
     * @param string $type   缓存类型。默认''
     *
     * @return object this
     */
    public function cache($key = true, $expire = '', $type = '') {
        $this->_options['cache'] = array(
        	   'key'    => $key,
            'expire' => $expire,
            'type'	  => $type
        );

        return $this;
    }

    /**
     * 验证数据，支持 in between equal length regex expire ip_allow ip_deny
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:52:35 by mrmsl
     *
     * @param string $value 验证数据
     * @param string $rule  验证表达式
     * @param string $type  验证方式，默认regex，正则验证
     *
     * @return bool true验证成功，否则false
     */
    public function check($value, $rule, $type = 'regex') {

        switch (strtolower($type)) {
            case 'in': //验证是否在某个指定范围之内 逗号分隔字符串或者数组
                $range = is_array($rule) ? $rule : explode(',', $rule);

                return in_array($value, $range);
                break;

            case 'between': //验证是否在某个范围
                list($min, $max) = explode(',', $rule);
                return $value >= $min && $value <= $max;
                break;

            case 'equal': //验证是否等于某个值
                return $value == $rule;
                break;

            case 'length': //验证长度
                return $this->check(strlen($value), $rule, strpos($rule, ',') ? 'between' : 'equal');
                break;

            case 'expire':
                list($start, $end) = explode(',', $rule);
                $start = is_numeric($start) ? $start : strtotime($start);
                $end   = is_numeric($end) ? $end : strtotime($end);

                return time() >= $start && time() <= $end;
                break;

            case 'ip_allow': //IP操作许可验证
                return in_array(get_client_ip(), explode(',', $rule));
                break;

            case 'ip_deny': //IP操作禁止验证
                return !$this->check($value, $rule, 'ip_allow');

            case 'regex' :
            default: //默认使用正则验证 可以使用验证类中定义的验证名称
                return $this->regex($value, $rule); //检查附加规则
        } //end foreach
    } //end check

    /**
     *
     * 提交事务
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:53:15 by mrmsl
     *
     * @return object this
     */
    public function commit() {
        $this->_db->commit();

        return $this;
    }

    /**
     *
     * 切换当前的数据库连接
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:53:25 by mrmsl
     *
     * @param int   $link_num  连接序号
     * @param mixed $config    数据库连接信息
     * @param array $params    模型参数
     *
     * @return object this
     */
    public function db($link_num, $config = '', $params = array()) {
        static $_db = array();

        if (!isset($_db[$link_num])) {//创建一个新的实例

            if (!empty($config) && false === strpos($config, '/')) { //支持读取配置参数
                $config = C($config);
            }

            $_db[$link_num] = Db::getInstance($config);
        }
        elseif (null === $config) {
            $_db[$link_num]->close(); //关闭数据库连接
            unset($_db[$link_num]);
            return;
        }

        if (!empty($params)) {
            is_string($params) && parse_str($params, $params);

            foreach ($params as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        $this->_db = $_db[$link_num];//切换数据库连接
        $this->_afterDb();

        if (!empty($this->_modle_name) && $this->_auto_check_fields) { //字段检测
            $this->_checkTableInfo();
        }

        return $this;
    }//end db

    /**
     * 创建数据对象 但不保存到数据库
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:53:56 by mrmsl
     *
     * @param mixed $data 创建数据
     * @param int   $type 类型
     *
     * @return mixed
     *
     */
    public function create($data = '', $type = '') {

        if (empty($data)) {//如果没有传值默认取POST数据
            $data = __GET ? $_GET : $_POST; //调试模式下，可通过GET获取数据对象 by mrmsl on 2012-06-21 17:52:09
        }
        elseif (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (empty($data) || !is_array($data)) {//验证数据
            $this->_error = L('_DATA_TYPE_INVALID_');
            return false;
        }

        $data = $this->_parseFieldsMap($data, 0);//检查字段映射

        if (!empty($this->_db_fields)) {

            foreach ($this->_db_fields as $field => $item) {

                if (isset($data[$field])) {

                    if (isset($item['filter'])) {//FilterClass过滤变量
                        $filter = is_array($item['filter']) ? $item['filter'] : explode(',', $item['filter']);
                    }
                    else {
                        $filter = array('string');
                    }

                    $method = $filter[0];
                    $filter[0] = $field;
                    $data[$field] = call_user_func_array(array('Filter', $method), $filter);
                }
            }
        }

        if (!$type) {//类型
            $type = empty($data[$this->getPk()]) ? self::MODEL_INSERT : self::MODEL_UPDATE;
        }

        if (!$this->autoCheckToken($data)) {//表单令牌验证
            $error = L('_TOKEN_ERROR_');

            if ($this->_patch_validate) {
                $this->_error['token'] = $error;
            }
            else {
                $this->_error = $error;
            }

            return false;
        }

        if (!$this->_autoValidate($data, $type)) {//数据自动验证
            return false;
        }

        //验证完成生成数据对象
        if ($this->_auto_check_fields) { //开启字段检测 则过滤非法字段数据
            $vo = array();

            foreach ($this->_fields as $key => $name) {

                if (0 === strpos($key, '_')) {
                    continue;
                }

                $val = isset($data[$name]) ? $data[$name] : null;

                if (!is_null($val)) {//保证赋值有效
                    $vo[$name] = $val;//(MAGIC_QUOTES_GPC && is_string($val)) ? stripslashes($val) : $val;
                }
            }
        }
        else {
            $vo = $data;
        }

        $this->autoOperation($vo, $type);//创建完成对数据进行自动处理
        $this->_data = $vo;//赋值当前数据对象

        return $vo;//返回创建的数据以供其他调用
    }//end create

    /**
     *
     * 设置数据对象值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 14:55:27 by mrmsl
     *
     * @param mixed $data 数据
     *
     * @throw Exception 如果数据对象非法
     * @return object this
     *
     */
    public function data($data) {

        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        elseif (is_string($data)) {
            parse_str($data, $data);
        }
        elseif (!is_array($data)) {
            throw new Exception(L('_DATA_TYPE_INVALID_'));
        }

        $this->_data = $data;

        return $this;
    }


    /**
     * 删除数据
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:03:16 by mrmsl
     *
     * @param array $options 参数表达式
     *
     * @return bool true删除成功，否则false
     */
    public function delete($options = array()) {

        if (empty($options) && empty($this->_options['where'])) {

            if (!empty($this->_data) && isset($this->_data[$v = $this->getPk()])) {//如果删除条件为空 则删除当前数据对象所对应的记录
                return $this->delete($this->_data[$v]);
            }

            return false;
        }

        if (is_numeric($options) || is_string($options)) {
            $pk = $this->getPk();//根据主键删除记录

            $where[$pk] = strpos($options, ',') ? array('IN', $options) : $options;
            $pk_value   = $where[$pk];
            $options    = array('where' => $where);
        }

        $options = $this->_parseOptions($options);//分析表达式
        $result  = $this->_db->delete($options);

        if (false !== $result) {
            $data = array();
            isset($pk_value) && ($data[$pk] = $pk_value);
            $this->_afterDelete($data, $options);
        }

        return $result;
    }//end delete

    /**
     * 执行SQL语句
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:01:03 by mrmsl
     *
     * @param string  $sql   SQL指令
     * @param boolean $parse true解析SQL。默认false
     *
     * @return mixed db->execute结果
     */
    public function execute($sql, $parse = false) {
        return $this->_db->execute($this->_parseSql($sql, $parse));
    }

    /**
     * 指定查询字段 支持字段排除
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:08:34 by mrmsl
     *
     * @param mixed   $field  字段
     * @param boolean $except true排除该字段。默认false
     *
     * @return object this
     */
    public function field($field, $except = false) {
        $fields = $this->getDbFields();

        if (true === $field) { //获取全部字段
            $field  = $fields ? $fields : '*';
        }
        elseif ($except) { //字段排除
            $field  = is_string($field) ? explode(',', $field) : $field;
            $field  = $fields ? array_diff($fields, $field) : $field;
        }

        $this->_options['field'] = $field;

        return $this;
    }

    /**
     * 查询数据
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-03-26 10:56:13 by mrmsl
     *
     * @param mixed $options            表达式参数
     * @param bool  $set_data_property  true设置$this->_data = $result;。默认false
     *
     * @return mixed 查询成功，如果有数据，返回该数据数组，否则返回空数组。查询失败则返回false
     */
    public function find($options = array(), $set_data_property = false) {

        if (is_numeric($options) || is_string($options)) {
            $where[$this->getPk()] = $options;
            $options = array('where' => $where);
        }

        $options['limit'] = 1;//总是查找一条记录

        $options    = $this->_parseOptions($options);//分析表达式
        $result_set = $this->_db->select($options);

        if (false === $result_set) {
            return false;
        }
        elseif (empty($result_set)) {//查询结果为空
            return array();
        }
        //不需要设置$this->_data。如果true，在添加时，有调用->find()时，原来已经验证好的$_POST数据将被覆盖，造成添加数据不一致 by mrmsl on 2013-03-26 10:56:04
        elseif (!$set_data_property) {
            return $result_set[0];
        }

        $this->_data = $result_set[0];
        $this->_afterFind($this->_data, $options);

        return $this->_data;
    }

    /**
     * 获取字段信息并缓存
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:09:38 by mrmsl
     *
     * @return bool true成功获取信息，否则false
     */
    public function flush() {
        //缓存不存在则查询数据表信息
        $this->_db->setModel($this->_modle_name);
        $fields = $this->_db->getFields($this->getTableName());

        if (!$fields) { //无法获取字段信息
            return false;
        }

        $this->_fields = array_keys($fields);
        $this->_fields['_autoinc'] = false;

        foreach ($fields as $key => $val) {//记录字段类型
            $type[$key] = $val['type'];

            if ($val['primary']) {
                $this->_fields['_pk'] = $key;
                $val['autoinc'] && ($this->_fields['_autoinc'] = true);
            }
        }

        C('DB_FIELDTYPE_CHECK') && ($this->_fields['_type'] = $type);//记录字段类型信息

        if (C('DB_FIELDS_CACHE')) {//永久缓存数据表信息
            $db = $this->_db_name ? $this->_db_name : C('DB_NAME');
            F('_fields/' . $db . '.' . $this->_modle_name, $this->_fields);
        }
    }//end flush

    /**
     * 获取数据库实例
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:10:34 by mrmsl
     *
     * @return object 数据库实例
     */
    public function getDb() {
        return $this->_db;
    }

    /**
     * 返回数据库的错误信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:10:46 by mrmsl
     *
     * @return string 数据库错误信息
     *
     */
    public function getDbError() {
        return $this->_db->getError();
    }

    /**
     * 获取数据表字段信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:10:53 by mrmsl
     *
     * @return array|false 获取成功，返回表字段信息数组，否则false
     */
    public function getDbFields() {

        if ($this->_fields) {
            $fields = $this->_fields;
            unset($fields['_autoinc'], $fields['_pk'], $fields['_type']);

            return $fields;
        }

        return false;
    }

    /**
     * 返回当前模型的错误信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:11:15 by mrmsl
     *
     * @return string 错误信息
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * 获取一条记录的某个字段值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:11:46 by mrmsl
     *
     * @param string $field  字段名
     * @param string|null $sepa   字段数据间隔符号，nullL返回数组。默认null
     *
     * @return mixed
     */
    public function getField($field, $sepa = null) {
        $options['field'] = $field;
        $options = $this->_parseOptions($options);

        if (strpos($field, ',')) {//多字段
            $result_set = $this->_db->select($options);

            //array(array('user_id' => 1, 'username' => 'mrmsl'...), array('user_id' => 2, 'username' => 'mr'...)...)
            if (!empty($result_set)) {
                $_field = explode(',', $field);
                $field  = array_keys($result_set[0]);
                $move   = $_field[0] == $_field[1] ? false : true;
                $key    = array_shift($field);//user_id
                $key2   = array_shift($field);//username
                $cols   = array();
                $count  = count($_field);

                foreach ($result_set as $result) {
                    $name = $result[$key];

                    if ($move) {//删除键值记录
                        unset($result[$key]);
                    }

                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    }
                    else {
                        $cols[$name] = is_null($sepa) ? $result : implode($sepa, $result);
                    }
                }

                return $cols;
            }
        }
        else {//查找一条记录
            $options['limit'] = 1;
            $result = $this->_db->select($options);

            if (!empty($result)) {
                return reset($result[0]);
            }

            return '';
        }

        return false;
    }//end getField

    /**
     * 返回最后插入的ID
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:12:31 by mrmsl
     *
     * @return mixed db->getLastInsertID()返回结果
     */
    public function getLastInsertID() {
        return $this->_db->getLastInsertID();
    }

    /**
     * 返回最后执行的sql语句
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:12:38 by mrmsl
     *
     * @return string 执行的sql语句
     */
    public function getLastSql() {
        return $this->_db->getLastSql($this->_modle_name);
    }

    /**
     * 得到当前的数据对象名称
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:12:46 by mrmsl
     *
     * @return string 当前模型名称
     *
     */
    public function getModelName() {
        $this->_modle_name = $this->_modle_name ? $this->_modle_name : substr(get_class($this), 0, -5);

        return $this->_modle_name;
    }

    /**
     * 获取主键名称
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:13:06 by mrmsl
     *
     * @return string 主键名称
     */
    public function getPk() {
        return isset($this->_fields['_pk']) ? $this->_fields['_pk'] : $this->_pk_field;
    }

    /**
     * 获取属性值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:13:16 by mrmsl
     *
     * @param string $name 属性值
     *
     * @return mixed 属性存在，返回该值，否则false
     */
    public function getProperty($name) {
        return property_exists($this, $name) ? $this->$name : '';
    }

    /**
     * 得到完整的数据表名
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:13:39 by mrmsl
     *
     * @return string 完整的数据表名
     */
    public function getTableName() {

        if (empty($this->_true_table_name)) {
            $table_name = empty($this->_table_prefix) ? '' : $this->_table_prefix;

            if (empty($this->_table_name)) {
                $table_name .= parse_name($this->_modle_name);
            }
            else {
                $table_name .= $this->_table_name;
            }

            $this->_true_table_name = strtolower($table_name);
        }

        return (empty($this->_db_name) ? '' : $this->_db_name . '.') . $this->_true_table_name;
    }

    /**
     * 查询SQL组装 join
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:14:01 by mrmsl
     *
     * @param array|string $join join
     *
     * @return this
     */
    public function join($join) {

        if (is_array($join)) {
            $this->_options['join'] = $join;
        }
        elseif (!empty($join)) {
            $this->_options['join'][] = $join;
        }

        return $this;
    }

    /**
     * SQL查询
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:14:23 by mrmsl
     *
     * @param mixed   $sql   SQL指令
     * @param bool    $parse true需要解析SQL。默认false
     *
     * @return mixed db->query()返回结果
     */
    public function query($sql, $parse = false) {
        return $this->_db->query($this->_parseSql($sql, $parse));
    }

    /**
     * 使用正则验证数据
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:14:46 by mrmsl
     *
     * @param string $value 要验证的数据
     * @param string $rule  验证规则
     *
     * @return bool true验证通过，否则false
     */
    public function regex($value, $rule) {
        $validate = array(
            'require'     => '/.+/',//长度大于0
            'email'       => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',//电子邮件
            'url'         => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',//url链接地址
            'currency'	   => '/^\d+(\.\d+)?$/',//货币
            'number'	     => '/^\d+$/',//数字
            'zip'         => '/^[1-9]\d{5}$/',//邮政编码
            'integer'     => '/^[-\+]?\d+$/',//整数
            'double'      => '/^[-\+]?\d+(\.\d+)?$/',//浮点数
            'english'     => '/^[A-Za-z]+$/'//英文字母
        );

        if (isset($validate[$v = strtolower($rule)])) { //检查是否有内置的正则表达式
            $rule = $validate[$v];
        }

        return 1 === preg_match($rule, $value);
    }

    /**
     * 事务回滚
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:15:06 by mrmsl
     *
     * @return object this
     */
    public function rollback() {
        $this->_db->rollback();

        return $this;
    }

    /**
     * 保存数据
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:15:16 by mrmsl
     *
     * @param mixed $data    数据
     * @param array $options 表达式
     *
     * @return true保存成功，否则false
     *
     */
    public function save($data = '', $options = array()) {

        if (empty($data)) {//没有传递数据，获取当前数据对象的值

            if (!empty($this->_data)) {
                $data = $this->_data;
                $this->_data = array();//重置数据
            }
            else {
                $this->_error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }

        $data    = $this->_facade($data);//数据处理
        $options = $this->_parseOptions($options);//分析表达式

        if (false === $this->_beforeUpdate($data, $options)) {
            return false;
        }

        if (!isset($options['where'])) {

            if (isset($data[$pk = $this->getPk()])) {//如果存在主键数据 则自动作为更新条件
                $where[$pk] = $data[$pk];
                $options['where'] = $where;
                $pk_value = $data[$pk];
                unset($data[$pk]);
            }
            else {//如果没有任何更新条件则不执行
                $this->_error = L('_OPERATION_WRONG_');
                return false;
            }
        }

        $result = $this->_db->update($data, $options);

        if (false !== $result) {
            isset($pk_value) && $data[$pk] = $pk_value;
            $this->_afterUpdate($data, $options);
        }

        return $result;
    }//end save

    /**
     * 查询select
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:15:45 by mrmsl
     *
     * @param array $options 表达式参数
     *
     * @return mixed 查询成功，有数据，返回数据数组，否则返回空数组。查询失败，返回false
     *
     */
    public function select($options = array()) {

        if (is_string($options) || is_numeric($options)) {//根据主键查询
            $pk = $this->getPk();
            $where[$pk] = strpos($options, ',') ? array('IN', $options) : $options;
            $options = array('where' => $where);
        }
        elseif (false === $options) { //用于子查询 不查询只返回SQL
            $options = $this->_parseOptions(array());//分析表达式

            return '(' . $this->_db->buildSelectSql($options) . ')';
        }


        $options    = $this->_parseOptions($options);//分析表达式
        $result_set = $this->_db->select($options);

        if (false === $result_set) {
            return false;
        }
        elseif (empty($result_set)) {//查询结果为空
            return array();
        }

        $this->_afterSelect($result_set, $options);

        return $result_set;
    }//end select

    /**
     * 通过select into方式添加记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:16:01 by mrmsl
     *
     * @param string $fields  要插入的数据表字段名。默认''
     * @param string $table   要插入的数据表名。默认''
     * @param array  $options 表达式
     *
     * @return bool db->selectInsert()返回结果
     */
    public function selectAdd($fields = '', $table = '', $options = array()) {

        $options = $this->_parseOptions($options);//分析表达式

        //写入数据到数据库
        if (false === ($result = $this->_db->selectInsert($fields ? $fields : $options['field'], $table ? $table : $this->getTableName(), $options))) {
            $this->_error = L('_OPERATION_WRONG_');//数据库插入操作失败
        }

        return $result;
    }

    /**
     * 字段值减少
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:16:35 by mrmsl
     *
     * @param string  $field 字段名
     * @param int     $step  减少值
     *
     * @return mixed $this->setField()返回结果
     */
    public function setDec($field, $step = 1) {
        return $this->setField($field, array('exp', $field . '-' . $step, true));
    }

    /**
     * 设置记录的某个字段值，支持使用数据库字段和方法
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:16:58 by mrmsl
     *
     * @param string|array $field  字段名
     * @param string $value  字段值
     *
     * @return mixed $this->save()返回结果
     */
    public function setField($field, $value = '') {
        if (is_array($field)) {
            $data = $field;
        }
        else {
            $data[$field] = $value;
        }

        return $this->save($data);
    }

    /**
     * 字段值增加
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:17:34 by mrmsl
     *
     * @param string  $field 字段名
     * @param int     $step  增加值
     *
     * @return $this->setField()返回结果
     */
    public function setInc($field, $step = 1) {
        return $this->setField($field, array('exp', $field . '+' . $step, true));
    }

    /**
     * 设置模型的属性值
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:17:46 by mrmsl
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return object this
     */
    public function setProperty($name, $value) {
        property_exists($this, $name) && ($this->$name = $value);

        return $this;
    }

    /**
     * 启动事务
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:18:10 by mrmsl
     *
     * @return mixed db->startTrans()结果
     */
    public function startTrans() {
        $this->commit()->_db->startTrans();

        return $this;
    }

    /**
     * 动态切换扩展模型
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:18:27 by mrmsl
     *
     * @param string $type 模型类型名称
     * @param array  $vars 要传入扩展模型的属性变量

     * @throw Exception 如果扩展模型类不存在
     * @return object 扩展模型
     *
     */
    public function switchModel($type, $vars = array()) {
        $class = ucwords(strtolower($type)) . 'Model';

        if (!class_exists($class)) {
            throw new Exception($class . L('_MODEL_NOT_EXIST_'));
        }

        //实例化扩展模型
        $this->_ext_model = new $class($this->_modle_name);

        if (!empty($vars)) {//传入当前模型的属性到扩展模型

            foreach ($vars as $var) {
                $this->_ext_model->setProperty($var, $this->$var);
            }
        }

        return $this->_ext_model;
    }

    /**
     * 查询SQL组装 union
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-21 15:18:34 by mrmsl
     *
     * @param mixed  $union union
     * @param bool   $all   UNION ALL

     * @throw Exception $union不符合规范
     * @return object this
     */
    public function union($union, $all = false) {

        if (empty($union)) {
            return $this;
        }

        $all && ($this->_options['union']['_all'] = true);

        $union = is_object($union) ? get_object_vars($union) : $union;

        if (is_string($union)) {//转换union表达式
            $options = $union;
        }
        elseif (is_array($union)) {

            if (isset($union[0])) {
                $this->_options['union'] = array_merge($this->_options['union'], $union);
                return $this;
            }
            else {
                $options = $union;
            }
        }
        else {
            throw new Exception(L('_DATA_TYPE_INVALID_'));
        }

        $this->_options['union'][] = $options;

        return $this;
    }//end union
}