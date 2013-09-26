<?php
/**
 * Pdo数据库驱动类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            DbPdo.class.php
 * @package         Yab\Db
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-16 14:21:41
 * @lastmodify      $Date$ $Author$
 */

class DbPdo extends Db {
    /**
     * @var object _PDOStatement PDOStatement。默认null
     */
    private $_PDOStatement = null;

    /**
     * {@inheritDoc}
     */
    private function _getAll($key_column = false) {

        if ($key_column) {//返回数组键值
            $result = array();
            $num    = 0;

            while ($row = $this->_PDOStatement->fetch(PDO::FETCH_ASSOC)) {
                $result[$row[$key_column]] = $row;
                $num++;
            }

            $this->_num_rows = $num;
        }
        else {
            $result = $this->_PDOStatement->fetchAll(PDO::FETCH_ASSOC);//返回数据集
            $this->_num_rows = count($result);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function _getError() {

        if ($this->_PDOStatement) {
            $error = $this->_PDOStatement->errorInfo();
            $this->_error = $error[2];
        }
        else {
            $this->_error = '';
        }

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
     * @lastmodify      2013-01-16 16:37:51 by mrmsl
     *
     * @access public
     *
     * @param array $config 数据库配置数组
     *
     * @return void 无返回值
     */
    public function __construct($config = array()) {

        if (!class_exists('PDO')) {
            throw new Exception(L('_NOT_SUPPERT_') . ':PDO');
        }

        if ($config) {
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
        $this->_current_link_id = null;
        $this->_PDOStatement = null;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-16 16:39:10 by mrmsl
     *
     * @throws Execption 如果提交事件失败
     * @return void 无返回值
     */
    public function commit() {

        if ($this->_trans_times) {
            $result = $this->_current_link_id->commit();
            $this->_trans_times = 0;

            if (!$result) {
                throw new Execption($this->_error());
            }
        }

        return true;
    }

    /**
     * {{@inheritDoc} }
     * @throws Exception 如果驱动类型不为mysql
     */
    public function connect($config = array(), $link_num = 0) {

        if (!isset($this->_link_id[$link_num])) {

            if (empty($config)) {
                $config = $this->_config;
            }

            if ($this->_pconnect) {
                $config['params'][PDO::ATTR_PERSISTENT] = true;
            }

            try {
                $this->_link_id[$link_num] = new PDO($config['dsn'], $config['username'], $config['password'], $config['params']);
            }
            catch (PDOException $e) {
                /**
                 * @ignore
                 */
                defined('DB_CONNECT_ERROR') or define('DB_CONNECT_ERROR', true);
                throw new Exception($e->getMessage());
            }

            if ('MYSQL' != $this->_db_type) {
                throw new Exception(L('_ONLY_SUPPORT_MYSQL_PDO_'));
            }

            $this->_link_id[$link_num]->exec('SET NAMES ' . DB_CHARSET);

            $this->_connected = true;//标记连接成功

            if (1 != C('DB_DEPLOY_TYPE')) {//注销数据库连接配置信息
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
            return $this->_current_link_id->quote($str);
        }

        return "'" . addslashes($str) . "'";
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

        //释放前次的查询结果
        $this->_PDOStatement && $this->free();

        N('db_write', 1);

        G('queryStartTime');//记录开始执行时间
        $this->_PDOStatement = $this->_current_link_id->prepare($str);
        G('queryEndTime');

        if (false === $this->_PDOStatement) {
            throw new Exception($this->errorInfo());
        }

        $result = $this->_PDOStatement->execute();

        if (false === $result) {
            $this->error();
        }
        else {
            $this->_num_rows = $this->_PDOStatement->rowCount();

            $sql = strtoupper(trim($str));

            if (0 === strpos($sql, 'INSERT') || 0 === strpos($sql, 'REPLACE')) {//插入，记录最后插入id
                $this->_last_insert_id = $this->_current_link_id->lastInsertId();
            }
        }

        $this->_debug();

        return $result;
    }//end execute

    /**
     * {@inheritDoc}
     */
    public function free() {
        $this->_PDOStatement = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields($table_name) {
        $this->_initConnect(true);

        if (C('DB_DESCRIBE_TABLE_SQL')) {//定义特殊的字段查询SQL
            $sql = str_replace('%table%', $table_name, C('DB_DESCRIBE_TABLE_SQL'));
        }
        else {
            $sql = 'DESC ' . $table_name; //备注: 驱动类不只针对mysql，不能加``
        }

        $result = $this->query($sql);
        $info   = array();

        if ($result) {

            foreach ($result as $key => $val) {
                $val['Name'] = isset($val['name']) ? $val['name'] : $val['Name'];
                $val['Type'] = isset($val['type']) ? $val['type'] : $val['Type'];
                $name        = strtolower(isset($val['Field']) ? $val['Field'] : $val['Name']);
                $info[$name] = array(
                    'name' => $name,
                    'type' => $val['Type'],
                    'notnull' => (bool)((isset($val['Null']) && '' === $val['Null']) || (isset($val['notnull']) && '' === $val['notnull'])),  // not null is empty, null is yes
                    'default' => isset($val['Default']) ? $val['Default'] : (isset($val['dflt_value']) ? $val['dflt_value'] : ''),
                    'primary' => isset($val['Key']) ? 'pri' == strtolower($val['Key']) : (isset($val['pk']) ? $val['pk'] : false),
                    'autoinc' => isset($val['Extra']) ? 'auto_increment' == strtolower($val['Extra']) : (isset($val['Key']) ? $val['Key'] : false)
                );
            }
        }

        return $info;
    }//end getFields

    /**
     * {@inheritDoc}
     */
    public function query($str, $key_column = '') {
        $this->_initConnect(false);

        if (!$this->_current_link_id) {
            return false;
        }

        $this->_setLastSql($str);
        $this->_sql_arr[] = $str;

        !empty($this->_PDOStatement) && $this->free();//释放前次的查询结果

        N('db_query', 1);

        G('queryStartTime');//记录开始执行时间
        $this->_PDOStatement = $this->_current_link_id->prepare($str);
        G('queryEndTime');

        if (false === $this->_PDOStatement) {
            throw new Exception($this->errorInfo());
        }

        $result = $this->_PDOStatement->execute();

        if (false === $result) {
            $this->error();
        }
        else {
            $result = $this->_getAll($key_column);
        }

        $this->_debug();

        return $result;
    }//end query

    /**
     * {@inheritDoc}
     */
    public function rollback() {

        if ($this->_trans_times) {
            $result = $this->_current_link_id->rollback();
            $this->_trans_times = 0;

            $this->_writeRollbackSql();//记录事务回滚

            if (!$result) {
                throw new Exception($this->error());
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function startTrans() {
        $this->_initConnect(true);

        if (!$this->_current_link_id) {
            return false;
        }

        //数据rollback 支持
        !$this->_trans_times && $this->_current_link_id->beginTransaction();

        $this->_trans_times++;
    }
}