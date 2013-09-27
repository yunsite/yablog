<?php
/**
 * 系统日志控制器类
 *
 * @file            LogController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-21 14:36:19
 * @lastmodify      $Date$ $Author$
 */

class LogController extends CommonController {
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map = array(
        'delete' => 'list'//删除
    );

    /**
     * {@inheritDoc}
     */
    protected function _beforeExec(&$pk_id, &$log) {
        $log = join(', ', $pk_id);//操作日志

        return null;
    }

    /**
     * 列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-01-22 10:40:26 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $sort       = Filter::string('sort', 'get', $this->_pk_field);//排序字段
        $sort       = in_array($sort, $this->_getDbFields()) ? $sort : $this->_pk_field;
        $order      = !empty($_GET['dir']) ? Filter::string('dir', 'get') : Filter::string('order', 'get');//排序
        $order      = toggle_order($order);
        $keyword    = Filter::string('keyword', 'get');//关键字
        $date_start = Filter::string('date_start', 'get');//注册开始时间
        $date_end   = Filter::string('date_end', 'get');//注册结束时间
        $log_type   = Filter::int('log_type', 'get');//日志类型
        $where      = $log_type != LOG_TYPE_ALL ? array( 'log_type' => $log_type) : array();

        if ($keyword !== '') {
            $where['content'] = $this->_buildMatchQuery('content', $keyword, Filter::string('match_mode', 'get'));
        }

        if ($date_start && ($date_start = strtotime($date_start))) {
            $where['log_time'][] = array('EGT', $date_start);
        }

        if ($date_end && ($date_end = strtotime($date_end))) {
            $where['log_time'][] = array('ELT', $date_end);
        }

        if (isset($where['log_time']) && count($where['log_time']) == 1) {
            $where['log_time'] = $where['log_time'][0];
        }

        $total      = $this->_model->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_LOG') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model->where($where)->field('*,INET_NTOA(user_ip) AS user_ip')->limit($page_info['limit'])->order('' .$sort . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_LOG') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction
}