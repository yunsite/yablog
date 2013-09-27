<?php
/**
 * 管理员登陆历史控制器
 *
 * @file            AdminLoginHistoryController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-17 22:02:54
 * @lastmodify      $Date$ $Author$
 */

class AdminLoginHistoryController extends CommonController {
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
        $sort       = 'a.' . $sort;
        $order      = !empty($_GET['dir']) ? Filter::string('dir', 'get') : Filter::string('order', 'get');//排序
        $order      = toggle_order($order);
        $keyword    = Filter::string('keyword', 'get');//关键字
        $date_start = Filter::string('date_start', 'get');//开始时间
        $date_end   = Filter::string('date_end', 'get');//结束时间
        $column     = Filter::string('column', 'get');//搜索字段
        $where      = array();

        if ($keyword !== '' && in_array($column, array('username', 'realname', 'admin_id'))) {
            $where['b.' . $column] = $this->_buildMatchQuery('b.' . $column, $keyword, Filter::string('match_mode', 'get'), 'admin_id');
        }

        if ($date_start && ($date_start = strtotime($date_start))) {
            $where['a.login_time'][] = array('EGT', $date_start);
        }

        if ($date_end && ($date_end = strtotime($date_end))) {
            $where['a.login_time'][] = array('ELT', $date_end);
        }

        if (isset($where['a.login_time']) && count($where['a.login_time']) == 1) {
            $where['a.login_time'] = $where['a.login_time'][0];
        }

        $where && $this->_model->join('JOIN ' . TB_ADMIN . ' AS b ON a.admin_id=b.admin_id');

        $total      = $this->_model->alias('a')->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model->alias('a')
        ->join('JOIN ' . TB_ADMIN . ' AS b ON a.admin_id=b.admin_id')
        ->where($where)->field('a.*,b.username,b.realname,INET_NTOA(a.login_ip) AS login_ip')->limit($page_info['limit'])->order('' .$sort . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction
}