<?php
/**
 * 邮件历史控制器
 *
 * @file            MailHistoryController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-07 11:00:15
 * @lastmodify      $Date$ $Author$
 */

class MailHistoryController extends CommonController {
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map = array(//权限映射
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
     * 重新发送邮件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-13 17:39:13
     *
     * @return void 无返回值
     */
    public function afreshSendAction() {
        $msg        = L('AFRESH,SEND,CN_YOUJIAN');

        if (!$data = $this->_getPairsData('history_id,add_time')) {
            $this->_ajaxReturn(false, L('INVALID_PARAM,%data。') . $msg . L('FAILURE'));
        }

        require(LIB_PATH . 'Mailer.class.php');
        $mailer = new Mailer($this->_model);

        foreach ($data as $item) {
            $mailer->doMail($item);
        }

        $this->_model->addLog($msg . join(',', array_keys($data)) . L('SUCCESS'));
        $this->_ajaxReturn(true, $msg . L('SUCCESS'));

    }//end afreshIpActi

    /**
     * 列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-07 11:09:51
     *
     * @return void 无返回值
     */
    public function listAction() {
        $sort           = Filter::string('sort', 'get', $this->_pk_field);//排序字段

        if (!in_array($sort, $this->_getDbFields())) {
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L('QUERY,CONTROLLER_NAME,%。,ORDER,COLUMN') . $sort . L('NOT_EXIST');
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, L('SERVER_ERROR'));
        }

        $order          = !empty($_GET['dir']) ? Filter::string('dir', 'get') : Filter::string('order', 'get');//排序
        $order          = toggle_order($order);
        $keyword        = Filter::string('keyword', 'get');//关键字
        $date_start     = Filter::string('date_start', 'get');//开始时间
        $date_end       = Filter::string('date_end', 'get');//结束时间
        $template_id    = Filter::int('template_id', 'get');//
        $column         = Filter::string('column', 'get');//搜索字段
        $where          = array();

        if ('' !== $keyword && in_array($column, array('subject', 'content', 'email'))) {
            $where[$column] = $this->_buildMatchQuery($column, $keyword, Filter::string('match_mode', 'get'));
        }

        if ($date_start && ($date_start = strtotime($date_start))) {
            $where['add_time'][] = array('EGT', $date_start);
        }

        if ($date_end && ($date_end = strtotime($date_end))) {
            $where['add_time'][] = array('ELT', $date_end);
        }

        if (isset($where['add_time']) && count($where['add_time']) == 1) {
            $where['add_time'] = $where['add_time'][0];
        }

        if ($template_id) {
            $where['template_id'] = $template_id;
        }

        $total      = $this->_model->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model->where($where)->limit($page_info['limit'])->order('' .$sort . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('LIST,ERROR'));//出错

        $templates = $this->cache(false, 'Mail');

        foreach($data as &$v) {
            $v['template_name'] = isset($templates[$id = $v['template_id']]) ? $templates[$id]['template_name'] : '';
        }

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction
}