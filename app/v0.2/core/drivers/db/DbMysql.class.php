<?php
/**
 * Mysql数据库驱动类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            DbMysql.class.php
 * @package         Yab\Db
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-16 14:21:41
 * @lastmodify      $Date$ $Author$
 */

final class DbMysql extends Db {

    /**
     * {@inheritDoc}
     */
    private function _getAll ($key_column = false) {
        $result = array();//返回数据集

        if ($this->_num_rows > 0) {

            if ($key_column) {//返回数组键值，用空间换时间 by mrmsl on 2012-12-20 17:59:46
                while ($row = mysql_fetch_assoc($this->_query_id)) {
                    $result[$row[$key_column]] = $row;
                }
            }
            else {
                while ($row = mysql_fetch_assoc($this->_query_id)) {
                    $result[] = $row;
                }
            }

            mysql_data_seek($this->_query_id, 0);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function _getError() {
        $this->_error = mysql_error($this->_current_link_id);

        return $this->_error;
    }

    /**
     * {@inheritDoc}
     */
    protected function _parseKey(&$key) {
        $key = trim($key);

        if( '*' == $key || false !== strpos($key, '(') || false !== strpos($key, '.') || false !== strpos($key, '`')) {
        }
        else {
            $key = '`' . $key . '`';
        }

        return $key;
    }

    /**
     * 构造函数 读取数据库配置信息
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:09:12 by mrmsl
     *
     * @param array $config 数据库配置数组
     *
     * @return void 无返回值
     */
    public function __construct($config = array()) {

        if (!extension_loaded('mysql')) {
            throw new Exception(L('_NOT_SUPPERT_') . ':mysql');
        }

        if (!empty($config)) {
            $this->_config = $config;

            if (empty($this->_config['params'])) {
                $this->_config['params'] = array();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function close() {
        $this->_current_link_id && mysql_close($this->_current_link_id);
        $this->_current_link_id = null;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:19:28 by mrmsl
     *
     * @throws Execption 如果提交事件失败
     * @return void 无返回值
     */
    public function commit() {

        if ($this->_trans_times) {
            $result = mysql_query('COMMIT', $this->_current_link_id);
            $this->_trans_times = 0;

            if (!$result) {
                throw new Exception($this->error());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect($config = array(), $link_num = 0) {

        if (!isset($this->_link_id[$link_num])) {

            if (empty($config)) {
                $config = $this->_config;
            }

            //处理不带端口号的socket连接情况
            $host = $config['hostname'] . ($config['hostport'] ? ':' . $config['hostport'] : '');

            //是否长连接
            $pconnect = empty($config['params']['persist']) ? $this->_pconnect : $config['params']['persist'];

            if ($pconnect) {
                $this->_link_id[$link_num] = mysql_pconnect($host, $config['username'], $config['password']);
            }
            else {
                $this->_link_id[$link_num] = mysql_connect($host, $config['username'], $config['password'], true);
            }

            if (!$this->_link_id[$link_num] || (!empty($config['database']) && !mysql_select_db($config['database'], $this->_link_id[$link_num]))) {
                /**
                 * @ignore
                 */
                define('DB_CONNECT_ERROR', true);
                throw new Exception(mysql_error());
            }

            $db_version = mysql_get_server_info($this->_link_id[$link_num]);

            //使用UTF8存取数据库 需要mysql 4.1.0以上支持
            $db_version >= '4.1' && mysql_query('SET NAMES ' . DB_CHARSET, $this->_link_id[$link_num]);
            $db_version > '5.0.1' && mysql_query("SET sql_mode=''", $this->_link_id[$link_num]);//设置 sql_model

            $this->_connected = true;//标记连接成功

            //注销数据库连接配置信息
            if (1 != C('DB_DEPLOY_TYPE')) {
                unset($this->_config);
            }
        }

        return $this->_link_id[$link_num];
    }//end connect

    /**
     * {@inheritDoc}
     */
    public function escapeString($str) {

        if ($this->_current_link_id) {
            $str = mysql_real_escape_string($str, $this->_current_link_id);
        }
        else {
            $str = addslashes($str);
        }

        return "'{$str}'";
    }

    /**
     * {@inheritDoc}
     */
    public function execute($str) {
        $this->_initConnect(true);

        if (!$this->_current_link_id) {
            return false;
        }

        $this->_setLastSql($str);
        $this->_sql_arr[] = $str;
        $this->_query_id && $this->free();//释放前次的查询结果

        N('db_write', 1);

        G('queryStartTime');//记录开始执行时间
        $result = mysql_query($str, $this->_current_link_id);
        G('queryEndTime');

        if (false === $result) {
            $this->error();
        }
        else {
            $this->_num_rows = mysql_affected_rows($this->_current_link_id);
            $this->_last_insert_id = mysql_insert_id($this->_current_link_id);
            $result = $this->_num_rows;
        }

        $this->_debug();

        return $result;
    }//end execute

    /**
     * {@inheritDoc}
     */
    public function free() {
        mysql_free_result($this->_query_id);
        $this->_query_id = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields($table_name) {
        $result = $this->query('SHOW COLUMNS FROM ' . $this->_parseKey($table_name));
        $info   = array();

        if ($result) {

            foreach ($result as $key => $val) {
                $info[$val['Field']] = array(
                    'name' => $val['Field'],
                    'type' => $val['Type'],
                    'notnull' => '' === $val['Null'],  // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => 'pri' == strtolower($val['Key']),
                    'autoinc' => 'auto_increment' == strtolower($val['Extra'])
                );
            }
        }

        return $info;
    }

    /**
     * {@inheritDoc}
     */
    public function query($str, $key_column = '') {

        if (0 === stripos($str, 'call')) { // 存储过程查询支持
            $this->close();
        }

        $this->_initConnect(false);

        if (!$this->_current_link_id) {
            return false;
        }

        $this->_setLastSql($str);
        $this->_sql_arr[] = $str;
        $this->_query_id && $this->free();//释放前次的查询结果

        N('db_query', 1);

        G('queryStartTime');//记录开始执行时间
        $this->_query_id = mysql_query($str, $this->_current_link_id);
        G('queryEndTime');

        if (false === $this->_query_id) {
            $this->error();
            $result = false;
        }
        else {
            $this->_num_rows = mysql_num_rows($this->_query_id);
            $result = $this->_getAll($key_column);
        }

        $this->_debug();

        return $result;
    }//end query

    /**
     * 替换记录
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:32:02 by mrmsl
     *
     * @param mixed $data    数据
     * @param array $options 参数表达式
     *
     * @return mixed $this->execute()结果
     */
    public function replace($data, $options = array()) {

        foreach ($data as $key => $val) {
            $value = $this->_parseValue($val);

            if (is_scalar($value)) {// 过滤非标量数据
                $values[] = $value;
                $fields[] = $this->_parseKey($key);
            }
        }
        $sql = 'REPLACE INTO ' . $this->_parseTable($options['table']) . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';

        return $this->execute($sql);
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {

        if ($this->_trans_times) {
            $this->_rollback_sql = join('<br >', $this->_sql_arr);
            $result = mysql_query('ROLLBACK', $this->_current_link_id);
            $this->_trans_times = 0;

            $this->_writeRollbackSql();//记录事务回滚

            if (!$result) {
                throw new Exception($this->error());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function startTrans() {
        $this->_initConnect(true);

        if (!$this->_current_link_id) {
            return false;
        }

        if (0 === $this->_trans_times) {//数据rollback支持
            mysql_query('START TRANSACTION', $this->_current_link_id);
        }

        $this->_trans_times++;
    }
}