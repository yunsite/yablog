<?php
/**
 * 数据库中间层实现类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            Db.class.php
 * @package         Yab\Db
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-16 14:21:41
 * @lastmodify      $Date$ $Author$
 */

class Db {
    /**
     * @var string $_db_type 数据库类型，首字母大写，如Mysql、Pdo。默认null
     */
    protected $_db_type   = null;
    /**
     * @var bool $_auto_free true自动释放查询结果。默认false
     */
    protected $_auto_free = false;
    /**
     * @var string $_model 当前查询模型名称。默认_think_
     */
    protected $_model     = '_think_';
    /**
     * @var bool $_pconnect true使用永久连接。默认false
     */
    protected $_pconnect  = false;
    /**
     * @var array $_sql_arr sql记录
     */
    protected $_sql_arr   = array();
    /**
     * @var string $_query_str 当前sql
     */
    protected $_query_str = '';
    /**
     * @var string $_rollback_sql 事务回滚sql
     */
    protected $_rollback_sql = '';
    /**
     * @var array $_model_sql 模型sql记录
     */
    protected $_model_sql = array();
    /**
     * @var int $_num_rows INSERT，REPLACE INTO，UPDATE影响记录数或SELECT返回结果数。默认0
     */
    protected $_num_rows  = 0;//返回或者影响记录数
    /**
     * @var int $_num_cols SELECT返回记录字段数。默认0
     */
    protected $_num_cols  = 0;
    /**
     * @var string $_error 查询错误信息
     */
    protected $_error     = '';//错误信息
    /**
     * @var array $_link_id 数据库连接id
     */
    protected $_link_id   = array();
    /**
     * @var resource $_current_link_id 当前数据库连接会话
     */
    protected $_current_link_id   = null;//当前连接ID。默认null
    /**
     * @var resource $_query_id 当前查询句柄。默认null
     */
    protected $_query_id  = null;
    /**
     * @var bool $_connected 连接状态标识，true已连接。默认false
     */
    protected $_connected = false;//是否已经连接数据库
    /**
     * @var array|string $_config 数据库连接参数配置，默认''
     */
    protected $_config    = '';
    /**
     * @var int $_last_insert_id 最后插入id。默认null
     */
    protected $_last_insert_id = null;
    /**
     * @var int $_trans_times 事务启动次数。默认0
     */
    protected $_trans_times    = 0;//事务指令数
    /**
     * @var array $_comparsion 数据库表达式
     */
    protected $_comparsion     = array(//式
        'eq'      => '=',
        'neq'     => '<>',
        'gt'      => '>',
        'egt'     => '>=',
        'lt'      => '<',
        'elt'     => '<=',
        'notlike' => 'NOT LIKE',
        'like'    => 'LIKE'
    );
    /**
     * @var array $_select_sql 数据库表达式
     */
    protected $_select_sql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%';
    /**
     * @var bool $debug true调试模式，记录sql(如果开启)、记录慢查询(如果开启)。默认false
     */
    public  $debug  = false;

    /**
     * 获取数据库驱动类实例
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:03:58 by mrmsl
     *
     * @return object 数据库驱动类实例
     */
    static public function getInstance() {
        $args = func_get_args();

        return get_instance_of(__CLASS__, 'factory', $args);
    }

    /**
     * 获得所有的查询数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-16 16:02:26
     * @lastmodify      2013-01-16 16:02:26 by mrmsl
     *
     * @param string $key_column 返回数组作为key值字段名。默认''，数字索引，从0开始
     *
     * @return array 数据集
     */
    private function _getAll($key_column = false) {
    }

    /**
     * 分析数据库配置信息，支持数组和DSN
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:05:17 by mrmsl
     *
     * @param array|string $db_config 数据库配置信息。默认''，自动获取
     *
     * @return array 数据库配置信息
     */
    private function _parseConfig($db_config = '') {

        if (!empty($db_config) && is_string($db_config)) {
            $db_config = $this->parseDSN($db_config);//如果DSN字符串则进行解析
        }
        elseif (is_array($db_config)) { //数组配置
            $db_config = array(
                'dbms'     => $db_config['db_type'],
                'username' => $db_config['db_user'],
                'password' => $db_config['db_pwd'],
                'hostname' => $db_config['db_host'],
                'hostport' => $db_config['db_port'],
                'database' => $db_config['db_name'],
                'dsn'      => $db_config['db_dsn'],
                'params'   => $db_config['db_params']
            );
        }
        elseif (empty($db_config)) {//如果配置为空，读取配置文件设置

            if (C('DB_DSN') && 'pdo' != strtolower(C('DB_TYPE'))) {//如果设置了DB_DSN 则优先
                $db_config = $this->parseDSN(C('DB_DSN'));
            }
            else {
                $db_config = array(
                    'dbms'     => C('DB_TYPE'),
                    'username' => C('DB_USER'),
                    'password' => C('DB_PWD'),
                    'hostname' => C('DB_HOST'),
                    'hostport' => C('DB_PORT'),
                    'database' => C('DB_NAME'),
                    'dsn'      => C('DB_DSN'),
                    'params'   => C('DB_PARAMS')
                );
            }
        }

        return $db_config;
    }//end _parseConfig

    /**
     * 数据库调试，记录sql(如果开启)、记录慢查询(如果开启)
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:09:23 by mrmsl
     *
     * @return void 无返回值
     */
    protected function _debug() {
        $this->_model_sql[$this->_model] = $this->_query_str;
        $this->_model = '_think_';
        $query_time = G('queryStartTime', 'queryEndTime', 6);//记录操作结束时间
        $log        = $this->_query_str . ' [ RunTime:' . $query_time . 's ]';
        C(array('LOG_LEVEL' => E_APP_SQL, 'LOG_FILENAME' => 'sql'));
        trigger_error($log);

        //记录慢查询 by mrmsl on 2012-09-12 15:08:30
        if (($log_sloqeury = sys_config('sys_log_slowquery')) && $query_time > $log_sloqeury && false === strpos($this->_query_str, ' ' . $this->_parseTable(TB_LOG) . ' ')) {
            D('Log')->addLog($log, LOG_TYPE_SLOWQUERY);
        }
    }

    /**
     * 根据DSN获取数据库类型 返回大写
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:09:54 by mrmsl
     *
     * @param string $dsn dsn字符串，如Mysqli、Pdo:Mysql
     *
     * @return string 数据库类型，大写，如MYSQL、PDO
     *
     */
    protected function _getDsnType($dsn) {
        $match   = explode(':', $dsn);
        $db_type = strtoupper(trim($match[0]));

        return $db_type;
    }

    /**
     * 获取数据库错误信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:37:10 by mrmsl
     *
     * @return string 数据库错误信息|''
     */
    protected function _getError() {
    }

    /**
     * 初始化数据库连接
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:11:37 by mrmsl
     *
     * @param bool $master true连接主服务器。默认true
     *
     * @return void 无返回值
     */
    protected function _initConnect($master = true) {

        if (1 == C('DB_DEPLOY_TYPE')) {//采用分布式数据库
            $this->_current_link_id = $this->_multiConnect($master);
        }
        elseif (!$this->_connected) {//默认单数据库
            $this->_current_link_id = $this->connect();
        }
    }

    /**
     * 十进制数检测
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-03 11:13:56
     * @lastmodify      2013-01-16 15:12:57 by mrmsl
     *
     * @param mixed $value 待检测值
     *
     * @return bool 如果$value为十进制数，返回true，否则返回false
     */
    protected function _isNumeric($value) {
        return is_numeric($value) && ltrim($value, '0');
    }

    /**
     * 连接分布式服务器
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:13:37 by mrmsl
     *
     * @param bool $master $master true连接主服务器。默认true
     *
     * @return mixed $this->connect()结果
     */
    protected function _multiConnect($master = false) {
        static $_config = array();

        if (empty($_config)) {//缓存分布式数据库配置解析

            foreach ($this->_config as $key => $val) {
                $_config[$key] = explode(',', $val);
            }
        }

        if (C('DB_RW_SEPARATE')) {//数据库读写是否分离

            if ($master) {//主从式采用读写分离
                $r = floor(mt_rand(0, C('DB_MASTER_NUM') - 1));//主服务器写入
            }
            else {//读操作连接从服务器
                $r = floor(mt_rand(C('DB_MASTER_NUM'), count($_config['hostname']) - 1)); //每次随机连接的数据库
            }
        }
        else {//读写操作不区分服务器
            $r = floor(mt_rand(0, count($_config['hostname']) - 1)); //每次随机连接的数据库
        }

        $db_config = array(
            'username' => isset($_config['username'][$r]) ? $_config['username'][$r] : $_config['username'][0],
            'password' => isset($_config['password'][$r]) ? $_config['password'][$r] : $_config['password'][0],
            'hostname' => isset($_config['hostname'][$r]) ? $_config['hostname'][$r] : $_config['hostname'][0],
            'hostport' => isset($_config['hostport'][$r]) ? $_config['hostport'][$r] : $_config['hostport'][0],
            'database' => isset($_config['database'][$r]) ? $_config['database'][$r] : $_config['database'][0],
            'dsn'      => isset($_config['dsn'][$r]) ? $_config['dsn'][$r] : $_config['dsn'][0],
            'params'   => isset($_config['params'][$r]) ? $_config['params'][$r] : $_config['params'][0]
        );

        return $this->connect($db_config, $r);
    }//end _multiConnect

    /**
     * DISTINCT分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:14:28 by mrmsl
     *
     * @param string $distinct distinct
     *
     * @return string DISTINCT 字段|''
     */
    protected function _parseDistinct($distinct) {
        return $distinct ? ' DISTINCT ' . $distinct : '';
    }

    /**
     * 字段分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:16:05 by mrmsl
     *
     * @todo   如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
     *
     * @param mixed $fields 字段
     *
     * @return string 选取field字段
     */
    protected function _parseField($fields) {

        if (is_string($fields) && strpos($fields, ',')) {
            $fields = explode(',', $fields);
        }

        if (is_array($fields)) {//完善数组方式传字段名的支持
            //支持 'field1'=>'field2' 这样的字段别名定义
            $array = array();

            foreach ($fields as $key => $field) {
                if (0 !== strpos($field, '_')) {

                    if (is_numeric($key)) {
                        $array[] = $this->_parseKey($field);
                    }
                    else {
                        $array[] = $this->_parseKey($key) . ' AS ' . $this->_parseKey($field);
                    }
                }
            }

            $field_str = implode(',', $array);
        }
        elseif (is_string($fields) && !empty($fields)) {
            $field_str = $this->_parseKey($fields);
        }
        else {
            $field_str = '*';
        }

        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $field_str;
    }//end _parseField

    /**
     * GROUP BY分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:17:06 by mrmsl
     *
     * @param string $group 分组
     *
     * @return string GROUP BY 字段|''
     */
    protected function _parseGroup($group) {
        return $group ? ' GROUP BY ' . $group : '';
    }

    /**
     * HAVING分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:16:05 by mrmsl
     *
     * @param string $having having
     *
     * @return string HAVING 字段|''
     */
    protected function _parseHaving($having) {
        return $having ? ' HAVING ' . $having : '';
    }

    /**
     * JOIN分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:18:07 by mrmsl
     *
     * @param array|string $join join
     *
     * @return string JOIN 表名|''
     */
    protected function _parseJoin($join) {
        $join_str = '';

        if (!empty($join)) {

            if (is_array($join)) {

                foreach ($join as $key => $_join) {

                    if (false !== stripos($_join, 'JOIN')) {
                        $join_str .= ' ' . $_join;
                    }
                    else {
                        $join_str .= ' LEFT JOIN ' . $_join;
                    }
                }
            }
            else {
                $join_str .= ' LEFT JOIN ' . $join;
            }
        }

        //将__TABLE_NAME__这样的字符串替换成正规的表名,并且带上前缀和后缀
        $join_str = false === strpos($join_str, '__') ? $join_str : preg_replace('/__([a-z_]+)__/e', "C('DB_PREFIX') . strtolower('$1')", $join_str);

        return $join_str;
    }//end join

    /**
     * 字段名分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:19:16 by mrmsl
     *
     * @param string $key 字段名
     *
     * @return string 处理后字段名
     */
    protected function _parseKey(&$key) {
        return $key;
    }

    /**
     * LIMIT分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:20:02 by mrmsl
     *
     * @param string $limit limit
     *
     * @return string LIMIT offset|''
     */
    protected function _parseLimit($limit) {
        return $limit ? ' LIMIT ' . $limit : '';
    }

    /**
     * 设置锁机制
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:20:25 by mrmsl
     *
     * @param bool $lock true锁定。默认false
     *
     * @return string lock字符串|''
     */
    protected function _parseLock($lock = false) {

        if (!$lock) {
            return '';
        }

        return 'ORACLE' == $this->_db_type ? ' FOR UPDATE NOWAIT ' : ' FOR UPDATE ';
    }

    /**
     * ORDER BY排序分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:20:25 by mrmsl
     *
     * @param array|string $order order
     *
     * @return string ORDER BY 排序字段|''
     */
    protected function _parseOrder($order) {

        if (is_array($order)) {
            $array = array();

            foreach ($order as $key => $val) {

                if (is_numeric($key)) {
                    $array[] = $this->_parseKey($val);
                }
                else {
                    $array[] = $this->_parseKey($key) . ' ' . $val;
                }
            }

            $order = implode(',', $array);
        }

        return $order ? ' ORDER BY ' . $order : '';
    }

    /**
     * SET分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:24:04 by mrmsl
     *
     * @param array $data 更新数据，键为字段，值为字段值
     *
     * @throws Execption 如果$data过滤非标量后为空
     * @return string SET
     */
    protected function _parseSet($data) {

        foreach ($data as $key => $val) {
            $value = $this->_parseValue($val);

            if (is_scalar($value)) {//过滤非标量数据
                $set[] = $this->_parseKey($key) . '=' . $value;
            }
        }

        if (!$set) {
            $this->rollback();
            throw new Exception(__FUNCTION__ . ' Arguments Invalid');
        }

        return ' SET ' . implode(',', $set);
    }

    /**
     * 表分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:24:04 by mrmsl
     *
     * @param array|string $tables 表名
     *
     * @return string 表名
     */
    protected function _parseTable($tables) {

        if (is_array($tables)) { //支持别名定义
            $array = array();

            foreach ($tables as $table => $alias) {
                $array[] = (is_numeric($table) ? '' : $this->_parseKey($table) . ' AS ') . $this->_parseKey($alias);
            }

            $tables = $array;
        }
        elseif (is_string($tables)) {
            $tables = explode(',', $tables);
            array_walk($tables, array(&$this, '_parseKey'));
        }

        return implode(',', $tables);
    }

    /**
     * 特殊条件分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:29:16 by mrmsl
     *
     * @param string $key 健
     * @param array|string $val 值
     *
     * @return string WHERE 条件
     */
    protected function _parseThinkWhere($key, $val) {
        $where_str = '';

        switch ($key) {
            case '_string'://字符串模式查询条件
                $where_str = $val;
                break;

            case '_complex'://复合查询条件
                $where_str = substr($this->_parseWhere($val), 6);
                break;

            case '_query' ://字符串模式查询条件
                parse_str($val, $where);

                if (isset($where['_logic'])) {
                    $op = ' ' . strtoupper($where['_logic']) . ' ';
                    unset($where['_logic']);
                }
                else {
                    $op = ' AND ';
                }

                $array = array();

                foreach ($where as $field => $data) {
                    $array[] = $this->_parseKey($field) . ' = ' . $this->_parseValue($data);
                }

                $where_str = implode($op, $array);
                break;
        }

        return $where_str;
    }//end _parseThinkWhere

    /**
     * UNION分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:29:32 by mrmsl
     *
     * @param mixed $union union
     *
     * @return string UNION|''
     */
    protected function _parseUnion($union) {

        if (empty($union)) {
            return '';
        }

        if (isset($union['_all'])) {
            $str = 'UNION ALL ';
            unset($union['_all']);
        }
        else {
            $str = 'UNION ';
        }

        foreach ($union as $u) {
            $sql[] = $str . (is_array($u) ? $this->buildSelectSql($u) : $u);
        }

        return implode(' ', $sql);
    }

    /**
     * 字段值分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:30:19 by mrmsl
     *
     * @param mixed $value 值
     *
     * @return array|string 处理后字段值
     */
    protected function _parseValue($value) {

        if ($this->_isNumeric($value)) {//数字
        }
        elseif (is_string($value)) {
            $value = $this->escapeString($value);
        }
        if (is_array($value)) {

            if (isset($value[0]) && is_string($value[0]) && 'exp' == strtolower($value[0])) {
                $value = isset($value[2]) ? $value[1] : $this->escapeString($value[1]);
            }
            else {
                $value = array_map(array($this, '_parseValue'), $value);
            }
        }
        elseif (null === $value) {
            $value = 'null';
        }

        return $value;
    }

    /**
     * WHERE条件分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:31:29 by mrmsl
     *
     * @param array|string $where where
     *
     * @return string WHERE条件|''
     */
    public function _parseWhere($where) {
        $where_str = '';

        if (is_string($where)) {//直接使用字符串条件
            $where_str = $where;
        }
        else {//使用数组或者对象条件表达式

            if (isset($where['_logic'])) {//定义逻辑运算规则 例如 OR XOR AND NOT
                $operate = ' ' . strtoupper($where['_logic']) . ' ';
                unset($where['_logic']);
            }
            else {//默认进行 AND 运算
                $operate = ' AND ';
            }

            foreach ($where as $key => $val) {
                $where_str .= '(';

                if (0 === strpos($key, '_')) {//解析特殊条件表达式
                    $where_str .= $this->_parseThinkWhere($key, $val);
                }
                else { //查询字段的安全过滤

                    /*if (!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/', trim($key))) {
                        throw new Exception(L('_EXPRESS_ERROR_') . ':' . $key);
                    }*/

                    $multi = is_array($val) && isset($val['_multi']);//多条件支持
                    $key   = trim($key);

                    if (strpos($key, '|')) { //支持 name|title|nickname 方式定义查询字段
                        $array = explode('|', $key);
                        $str   = array();

                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->_parseWhereItem($this->_parseKey($k), $v) . ')';
                        }

                        $where_str .= implode(' OR ', $str);
                    }
                    elseif (strpos($key, '&')) {
                        $array = explode('&', $key);
                        $str   = array();

                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->_parseWhereItem($this->_parseKey($k), $v) . ')';
                        }

                        $where_str .= implode(' AND ', $str);
                    }
                    else {
                        $where_str .= $this->_parseWhereItem($this->_parseKey($key), $val);
                    }
                }

                $where_str .= ')' . $operate;
            }

            $where_str = substr($where_str, 0, -strlen($operate));
        }

        return $where_str ? ' WHERE ' . $where_str : '';
    }//end _parseWhere

    /**
     * WHERE子单元分析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:33:16 by mrmsl
     *
     * @param string $key 健
     * @param mixed  $val 值
     *
     * @return string WHERE条件
     */
    protected function _parseWhereItem($key, $val) {
        $where_str = '';

        if (is_array($val)) {

            if (is_string($val[0])) {
                $v  = trim(strtolower($val[0]));
                $v1 = $val[1];

                if (isset($this->_comparsion[$v])) { //比较运算
                    $where_str .= $key . ' ' . $this->_comparsion[$v] . ' ' . $this->_parseValue($v1);
                }
                elseif ('exp' == $v) { //使用表达式
                    $where_str .= '(' . $key . ' ' . $v1 . ') ';
                }
                elseif ('in' == $v) { //IN 运算

                    if (isset($val[2]) && 'exp' == $val[2]) {
                        $where_str .= $key . ' ' . strtoupper($v) . ' ' . $v1;
                    }
                    else {
                        $v1   = is_array($v1) ? $v1 : explode(',', $v1);
                        $zone = implode(',', $this->_parseValue($v1));
                        $where_str .= $key . ' ' . strtoupper($v) . '(' . $zone . ')';
                    }
                }
                elseif ('between' == $v) { //BETWEEN运算
                    $data = is_string($v1) ? explode(',', $v1) : $v1;
                    $where_str .= '(' . $key . ' ' . strtoupper($v) . ' ' . $this->_parseValue($data[0]) . ' AND ' . $this->_parseValue($data[1]) . ')';
                }
                else {
                    throw new Exception(L('_EXPRESS_ERROR_') . ':' . $v);
                }
            }//end if is_string
            else {
                $count = count($val);
                if (is_string($v = $val[$count - 1]) && in_array($v = strtoupper(trim($v)), array('AND', 'OR', 'XOR'))) {
                    $rule  = $v;
                    $count = $count - 1;
                }
                else {
                    $rule = 'AND';
                }
                for ($i = 0; $i < $count; $i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];

                    if ('exp' == strtolower($val[$i][0])) {
                        $where_str .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
                    }
                    else {
                        $op = is_array($val[$i]) && isset($this->_comparsion[$v = strtolower($val[$i][0])]) ? $this->_comparsion[$v] : '=';
                        $where_str .= '(' . $key . ' ' . $op . ' ' . $this->_parseValue($data) . ') ' . $rule . ' ';
                    }
                }

                $where_str = substr($where_str, 0, -4);
            }
        }//end if is_array
        else {
            //对字符串类型字段采用模糊匹配
            if (C('DB_LIKE_FIELDS') && preg_match('/(' . C('DB_LIKE_FIELDS') . ')/i', $key)) {
                $val = '%' . $val . '%';
                $where_str .= $key . ' LIKE ' . $this->_parseValue($val);
            }
            else {
                $where_str .= $key . ' = ' . $this->_parseValue($val);
            }
        }

        return $where_str;
    }//end _parseWhereItem

    /**
     * 设置最后查询sql
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-29 12:41:05
     * @lastmodify      2013-01-16 15:34:28 by mrmsl
     *
     * @param string $sql sql
     *
     * @return void 无返回值
     */
    protected function _setLastSql($sql) {
        $this->_query_str = $sql;
    }

    /**
     * 记录错误查询
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-02 14:25:36
     * @lastmodify      2013-01-16 15:35:56 by mrmsl
     *
     * @return void 无返回值
     */
    protected function _writeErrorSql() {

        if (sys_config('sys_log_sqlerror') && !defined('DB_CONNECT_ERROR') && false === strpos($this->_query_str, ' ' . $this->_parseTable(TB_LOG) . ' ')) {
            $last_sql = $this->_query_str;
            C(array('LOG_LEVEL' => E_APP_SQL, 'LOG_FILENAME' => 'errorsql'));
            trigger_error($error = $this->_query_str . '<br />' . $this->_error);
            D('Log')->addLog($error);
            $this->_query_str = $last_sql;
        }
    }

    /**
     * 记录事务回滚
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-02 14:29:11
     * @lastmodify      2013-01-16 15:36:32 by mrmsl
     *
     * @return void 无返回值
     */
    protected function _writeRollbackSql() {

        C(array('LOG_LEVEL' => E_APP_ROLLBACK_SQL, 'LOG_FILENAME' => 'rollbacksql'));
        trigger_error($rollback_sql = join(EOL_LF . '<br />', $this->_sql_arr));

        if (sys_config('sys_log_rollback_sql') && false === strpos($this->_query_str, ' ' . $this->_parseTable(TB_LOG) . ' ')) {
            $last_sql = $this->_query_str;
            D('Log')->addLog($rollback_sql, LOG_TYPE_ROLLBACK_SQL);
            $this->_setLastSql($last_sql);
        }
    }

    /**
     * 构造函数
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:36:49 by mrmsl
     *
     * @param array|string $config 数据库配置。默认''
     *
     * @return object 数据库实例
     */
    public function __construct($config = '') {
        return $this->factory($config);
    }

    /**
     * 析构方法
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:37:36 by mrmsl
     *
     * @return void 无返回值
     */
    public function __destruct() {
        $this->_query_id && $this->free();//释放查询
        $this->close();//关闭连接
    }

    /**
     * 生成查询SQL
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:37:54 by mrmsl
     *
     * @param array $options 表达式
     *
     * @return string 查询sql
     */
    public function buildSelectSql($options = array()) {

        if (isset($options['page'])) {//根据页数计算limit

            if (strpos($options['page'], ',')) {
                list($page, $list_rows) = explode(',', $options['page']);
            }
            else {
                $page = $options['page'];
            }

            $page = $page ? $page : 1;

            $list_rows = isset($list_rows) ? $list_rows : (is_numeric($options['limit']) ? $options['limit'] : 20);

            $offset = $list_rows * (intval($page) - 1);
            $options['limit'] = $offset . ',' . $list_rows;
        }

        if (C('DB_SQL_BUILD_CACHE')) { //SQL创建缓存
            $key = md5(serialize($options));
            $value = S($key);

            if (false !== $value) {
                return $value;
            }
        }

        $sql  = $this->parseSql($this->_select_sql, $options);
        $sql .= $this->_parseLock(isset($options['lock']) ? $options['lock'] : false);

        //写入SQL创建缓存
        isset($key) && S($key, $sql, 0, '', array('length' => C('DB_SQL_BUILD_LENGTH'), 'queue' => C('DB_SQL_BUILD_QUEUE')));

        return $sql;
    }//end buildSelectSql

    /**
     * 关闭数据库
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:38:26 by mrmsl
     *
     * @return void 无返回值
     */
    public function close() {
    }

    /**
     * 连接数据库方法
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:25:44 by mrmsl
     *
     * @param array $config   配置信息
     * @param int   $link_num 连接号
     *
     * @throws Exception 如果无法连接数据库或者无法选择数据库
     * @return resource 数据库连接资源
     */
    public function connect($config = array(), $link_num = 0) {
    }

    /**
     * 删除记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:38:44 by mrmsl
     *
     * @param array $options 表达式
     *
     * @return mixed $this->execute()返回结果
     */
    public function delete($options = array()) {
        $this->_model = isset($options['model']) ? $options['model'] : '_think_';
        $sql = 'DELETE FROM ' .
        $this->_parseTable($options['table']) .
        $this->_parseWhere(isset($options['where']) ? $options['where'] : '') .
        $this->_parseOrder(isset($options['order']) ? $options['order'] : '') .
        $this->_parseLimit(isset($options['limit']) ? $options['limit'] : '') .
        $this->_parseLock(isset($options['lock']) ? $options['lock'] : false);

        return $this->execute($sql);
    }

    /**
     * 数据库错误处理，事务回滚，记录错误查询
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:39:13 by mrmsl
     *
     * @return string 数据库错误信息
     */
    public function error() {
        $this->_error = $this->_getError();
        $this->rollback();//回滚 by mrmsl on 2012-12-27 18:04:47

        $this->_writeErrorSql();//记录错误查询

        return $this->_error;
    }

    /**
     * SQL指令安全过滤
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:39:54 by mrmsl
     *
     * @param string $str SQL字符串
     *
     * @return string 安全过滤后sql
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 执行语句
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:41:53 by mrmsl
     *
     * @param string $str  sql指令
     *
     * @return mixed 执行成功，返回影响行数，否则返回false
     *
     */
    public function execute($str) {
    }

    /**
     * 加载数据库 支持配置文件或者 DSN
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:40:05 by mrmsl
     *
     * @param array|string $db_config 数据库配置信息
     *
     * @throws Execption 如果未设置数据库类型
     * @return object 数据库实例
     */
    public function factory($db_config = '') {
        $db_config = $this->_parseConfig($db_config);//读取数据库配置

        if (empty($db_config['dbms'])) {
            throw new Exception(L('_NO_DB_CONFIG_'));
        }

        //数据库类型
        $this->_db_type = ucfirst(strtolower($db_config['dbms']));
        $class = 'Db' . $this->_db_type;
        $db     = new $class($db_config);

       //获取当前的数据库类型
       if ('pdo' != strtolower($db_config['dbms'])) {
            $db->_db_type = strtoupper($this->_db_type);
       }
       else {
           $db->_db_type = $this->_getDsnType($db_config['dsn']);
       }

       $db->debug = APP_DEBUG && C('LOG_SQL') ? true : false;

        return $db;
    }//end factory

    /**
     * 释放查询结果
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:43:05 by mrmsl
     *
     * @return void 无返回值
     */
    public function free() {
    }

    /**
     * 取得数据表的字段信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:27:30 by mrmsl
     *
     * @param string $table_name 表名
     *
     * @return array 表字段信息
     */
    public function getFields($table_name) {
    }

    /**
     * 插入记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:42:29 by mrmsl
     *
     * @param mixed   $data    数据
     * @param array   $options 参数表达式
     * @param bool    $replace true使用REPLACE INTO。默认false
     *
     * @return mixed $this->execute()返回结果
     */
    public function insert($data, $options = array(), $replace = false) {
        $values = $fields = array();
        $this->_model = isset($options['model']) ? $options['model'] : '_think_';

        foreach ($data as $key => $val) {
            $value = $this->_parseValue($val);

            if (is_scalar($value)) { //过滤非标量数据
                $values[] = $value;
                $fields[] = $this->_parseKey($key);
            }
        }
        $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->_parseTable($options['table']) . '(' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        $sql .= $this->_parseLock(isset($options['lock']) ? $options['lock'] : false);

        return $this->execute($sql);
    }

    /**
     * 获取最近的错误信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:43:27 by mrmsl
     *
     * @return string 最近的错误信息
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * 获取最近一次查询的sql语句
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:43:54 by mrmsl
     *
     * @param string $model 模型名
     *
     * @return string 最近一次查询的sql语句
     */
    public function getLastSql($model = '') {
        //return isset($this->_model_sql[$model]) ? $this->_model_sql[$model] : $this->_query_str;
        return $this->_query_str;
    }

    /**
     * 获取属性
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-15 12:43:49
     * @lastmodify      2013-01-16 15:44:10 by mrmsl
     *
     * @param string $property 属性名
     *
     * @return mixed 属性值
     */
    function getProperty($property) {
        return $this->$property;
    }

    /**
     * DSN解析
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:44:34 by mrmsl
     *
     * @param string $dsn_str dsn配置，如mysql://username:passwd@localhost:3306/DbName
     *
     * @return array DSN配置
     */
    public function parseDSN($dsn_str) {

        if (empty($dsn_str)) {
            return false;
        }

        $info = parse_url($dsn_str);
        if ($info['scheme']) {
            $dsn = array(
                'dbms'     => $info['scheme'],
                'username' => isset($info['user']) ? $info['user'] : '',
                'password' => isset($info['pass']) ? $info['pass'] : '',
                'hostname' => isset($info['host']) ? $info['host'] : '',
                'hostport' => isset($info['port']) ? $info['port'] : '',
                'database' => isset($info['path']) ? substr($info['path'], 1) : ''
            );
        }
        else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/', trim($dsn_str), $matches);
            $dsn = array(
                'dbms'     => $matches[1],
                'username' => $matches[2],
                'password' => $matches[3],
                'hostname' => $matches[4],
                'hostport' => $matches[5],
                'database' => $matches[6]
            );
        }

        $dsn['dsn'] = ''; //兼容配置信息数组

        return $dsn;
    }//end parseDSN

    /**
     * 获取最近插入的ID
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:45:14 by mrmsl
     *
     * @return string 最近插入的id
     */
    public function getLastInsertID() {
        return $this->_last_insert_id;
    }

    /**
     * 取得数据库的表信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:46:22 by mrmsl
     *
     * @param string $db_name 数据库名。默认''，取当前数据库
     *
     * @return array 数据库表信息
     */
    public function getTables($db_name = '') {
        $sql    = 'SHOW TABLES' . ($db_name ? 'FROM ' . $db_name : '');
        $result = $this->query($sql);
        $info   = array();

        if ($result) {

            foreach ($result as $key => $val) {
                $info[$key] = current($val);
            }
        }

        return $info;
    }

    /**
     * 替换SQL语句中表达式
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:47:01 by mrmsl
     *
     * @param string $sql     sql语句
     * @param array  $options 表达式
     *
     * @return string sql语句
     */
    public function parseSql($sql, $options = array()) {
        $sql = str_replace(array(
            '%TABLE%',
            '%DISTINCT%',
            '%FIELD%',
            '%JOIN%',
            '%WHERE%',
            '%GROUP%',
            '%HAVING%',
            '%ORDER%',
            '%LIMIT%',
            '%UNION%'
        ), array(
            $this->_parseTable($options['table']),
            $this->_parseDistinct(isset($options['distinct']) ? $options['distinct'] : false),
            $this->_parseField(isset($options['field']) ? $options['field'] : '*'),
            $this->_parseJoin(isset($options['join']) ? $options['join'] : ''),
            $this->_parseWhere(isset($options['where']) ? $options['where'] : ''),
            $this->_parseGroup(isset($options['group']) ? $options['group'] : ''),
            $this->_parseHaving(isset($options['having']) ? $options['having'] : ''),
            $this->_parseOrder(isset($options['order']) ? $options['order'] : ''),
            $this->_parseLimit(isset($options['limit']) ? $options['limit'] : ''),
            $this->_parseUnion(isset($options['union']) ? $options['union'] : '')
        ), $sql);

        return $sql;
    }//end parseSql

    /**
     * 执行查询 返回数据集
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:50:47 by mrmsl
     *
     * @param string $str sql指令
     * @param string $key_column 返回数组作为key值字段名。默认''，数字索引，从0开始
     *
     * @return mixed 查询出错，返回false，否则返回$this->_getAll()结果
     */
    public function query($str, $key_column = '') {
    }

    /**
     * 事务回滚
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:52:21 by mrmsl
     *
     * @throws Execption 事务回滚失败
     * @return void 无返回值
     */
    public function rollback() {
    }

    /**
     * 查询
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:47:37 by mrmsl
     *
     * @param array $options 表达式
     *
     * @return array|false 查询成功，返回结果集，否则返回false
     */
    public function select($options = array()) {
        $this->_model = isset($options['model']) ? $options['model'] : '_think_';
        $sql        = $this->buildSelectSql($options);
        $cache      = isset($options['cache']) ? $options['cache'] : false;
        $key_column = isset($options['key_column']) ? $options['key_column'] : false;//返回结果数e键值

        if ($cache) {//查询缓存检测
            $key   = is_string($cache['key']) ? $cache['key'] : md5($sql);
            $value = S($key, '', '', $cache['type']);

            if (false !== $value) {
                return $value;
            }
        }

        $result = $this->query($sql, $key_column);

        if ($cache && false !== $result) { //查询缓存写入
            S($key, $result, $cache['expire'], $cache['type']);
        }

        return $result;
    }

    /**
     * 通过Select方式插入记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:48:26 by mrmsl
     *
     * @param string $fields  要插入的数据表字段名
     * @param string $table   要插入的数据表名
     * @param array  $options 查询数据参数
     *
     * @return mixed $this->execute() 结果
     */
    public function selectInsert($fields, $table, $options = array()) {
        $this->_model = $options['model'];
        $fields = is_string($fields) ? explode(',', $fields) : $fields;

        array_walk($fields, array($this, '_parseKey'));

        $sql  = 'INSERT INTO ' . $this->_parseTable($table) . '(' . implode(',', $fields) . ')';
        $sql .= $this->buildSelectSql($options);

        return $this->execute($sql);
    }

    /**
     * 切换模型
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:48:44 by mrmsl
     *
     * @param string $model 模型名
     *
     * @return void 无返回值
     */
    public function setModel($model) {
        $this->_model = $model;
    }

    /**
     * 启动事务
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:52:56 by mrmsl
     *
     * @return void 无返回值
     */
    public function startTrans() {
    }

    /**
     * 更新记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 15:48:53 by mrmsl
     *
     * @param mixed $data    数据
     * @param array $options 表达式
     *
     * @return mixed $this->execute() 返回结果
     */
    public function update($data, $options) {
        $this->_model = isset($options['model']) ? $options['model'] : '_think_';

        $sql = 'UPDATE ' .
        $this->_parseTable($options['table']) .
        $this->_parseSet($data) .
        $this->_parseWhere(isset($options['where']) ? $options['where'] : '') .
        $this->_parseOrder(isset($options['order']) ? $options['order'] : '') .
        $this->_parseLimit(isset($options['limit']) ? $options['limit'] : '') .
        $this->_parseLock(isset($options['lock']) ? $options['lock'] : false);

        return $this->execute($sql);
    }
}