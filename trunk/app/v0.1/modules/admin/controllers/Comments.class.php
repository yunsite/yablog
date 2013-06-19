<?php
/**
 * 留言评论控制器类
 *
 * @file            Comments.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-28 11:52:20
 * @lastmodify      $Date$ $Author$
 */

class CommentsController extends CommonController {
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'issue'    => 'add',//发布状态
        'isdelete' => 'add',//删除状态
    );

    /**
     * 操作留言评论后置
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-30 08:28:11
     *
     * @return void 无返回值
     */
    private function _afterAction() {
        $info = C('T_INFO');

        if (!empty($info['delete_blog_html'])) {
            $this->_deleteBlogHtml($info['delete_blog_html']);//删除静态页

            //更新评论数
            !empty($info[COMMENT_TYPE_BLOG]) && $this->_updateBlogCommentsNum($info[COMMENT_TYPE_BLOG], COMMENT_TYPE_BLOG);
            !empty($info[COMMENT_TYPE_MINIBLOG]) && $this->_updateBlogCommentsNum($info[COMMENT_TYPE_MINIBLOG], COMMENT_TYPE_MINIBLOG);
        }
    }

    /**
     * 获取有回复需要发邮件留言评论信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-16 21:51:08
     *
     * @param array|string $comment_ids comment_id串或数组
     * @param array $blog_info 评论博客或微博
     *
     * @return array 留言评论信息
     */
    private function _getMailCommentsInfo($comment_ids, $blog_info) {
        $info = $this->_model->field('type,email,content,comment_id,blog_id,parent_id')->where(array($this->_pk_field => array('IN', $comment_ids), 'at_email' => 1))->select();

        if ($info) {

            foreach ($info as &$v) {
                $blog_id    = $v['blog_id'];
                $type       = $v['type'];

                if (COMMENT_TYPE_GUESTBOOK == $type) {
                    $v['comment_name'] = L('GUESTBOOK');
                    $link_url = BASE_SITE_URL . 'guestbook.shtml';
                }
                else {
                    $v['comment_name'] = L('COMMENT');
                    $link_url = $blog_info[$blog_id]['link_url'];
                }

                $v['link_url'] = $link_url . '#comment-' . $v['comment_id'];
            }
        }

        return $info;
    }//end _getMailCommentsInfo

    /**
     * 更新博客,微博评论数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-29 16:24:19
     *
     * @param array|int $blog_id blog_id
     * @param int       $type    评论类型
     *
     * @return void 无返回值
     */
    private function _updateBlogCommentsNum($blog_id, $type) {
        $table      = COMMENT_TYPE_BLOG == $type ? TB_BLOG : TB_MINIBLOG;
        $blog_id    = is_array($blog_id) ? $blog_id : explode(',', $blog_id);

        static $updated    = array();

        foreach($blog_id as $v) {
            $_key = $type . '_' . $v;

            if (!isset($updated[$_key])) {
                $arr    = $this->_model->field('status')->where("type={$type} AND blog_id={$v}")->select();
                $total  = $this->_model->getDb()->getProperty('_num_rows');
                $pass   = 0;

                if ($total) {

                    foreach ($arr as $item) {

                        if (COMMENT_STATUS_PASS == $item['status']) {
                            $pass++;
                        }
                    }
                }

                $this->_model->execute("UPDATE {$table} SET comments={$pass},total_comments={$total} WHERE blog_id={$v}");
                $updated[$_key] = true;
            }
        }
    }

    /**
     * 删除后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-18 11:05:45
     *
     * @param array $pk_id 主键值
     *
     * @return void 无返回值
     */
    protected function _afterDelete($pk_id) {

        if ($node_arr = C('T_INFO.node')) {

            foreach ($node_arr as $v) {
                $this->_model->where("node LIKE '{$v}%'")->delete();
                $this->_afterAction();
            }
        }

        //$this->_model->rollback();
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterSetField($field, $value, $pk_id) {

        if ('status' == $field) {//审核状态
            $this->_afterAction();

            if ($at_email = C('T_INFO.at_email')) {
                require_cache(LIB_PATH . 'Mailer.class.php');
                $mailer = new Mailer($this->_model);

                foreach($at_email as $v) {
                    $mailer->mail('comments_at_email', $v);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeExec(&$pk_id, &$log) {
        $pk_field   = $this->_pk_field;
        $data       = $this->_model->where(array($pk_field => array('IN', $pk_id)))
        ->field($pk_field . ',type,status,parent_id,blog_id,node')
        ->key_column($pk_field)
        ->select();
        $log        = '';
        $info       = array();//记录操作博客信息，如删除静态文件，删除对应类静态文件等

        if (false !== $data) {

            $selected = array(
                COMMENT_TYPE_BLOG       => array(),
                COMMENT_TYPE_MINIBLOG   => array(),
                'at_email'              => array(),
                'node'                  => array(),
            );

            foreach ($data as $k => $v) {
                $log       .= $k . ',';
                $type       = $v['type'];
                $blog_id    = $v['blog_id'];

                if (COMMENT_TYPE_GUESTBOOK != $type && !isset($selected[$type][$blog_id])) {
                    $a = $this->_model->table(COMMENT_TYPE_BLOG == $type ? TB_BLOG : TB_MINIBLOG)->where('blog_id=' . $blog_id)->field('link_url')->find();
                    $info[$type][] = $blog_id;
                    $info['delete_blog_html'][$blog_id] = array('link_url' => $a['link_url']);
                    $selected[$type][$blog_id] = true;
                }

                if (($parent_id = $v['parent_id']) && !in_array($parent_id, $selected['at_email'])) {//有回复时是否发送邮件
                    $selected['at_email'][] = $parent_id;

                    /*if ($at_email = $this->_model->where('at_email=1 AND comment_id=' . $parent_id)->getField('at_email')) {
                        $info['at_email'][] = $this->_model->field('type,email,content,comment_id')->find($parent_id);
                    }*/
                }

                if ('delete' == ACTION_NAME) {//删除,同时需要删除回复
                    $node   = $v['node'] . ',';

                    if (!isset($selected[$node])) {
                        $selected[$node] = true;
                        $info['node'][] = $node;
                    }
                }
            }

            if (COMMENT_STATUS_PASS == C('T_STATUS') && $selected['at_email']) {
                $info['at_email'] = $this->_getMailCommentsInfo($selected['at_email'], $info['delete_blog_html']);
            }

            C('T_INFO', $info);
        }

        return $log ? substr($log, 0, -1) : null;
    }//end _beforeExec

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
        $pk_value  = $this->_model->$pk_field;//博客id
        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'username,user_homepage,email,content';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('CONTROLLER_NAME,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if (!$commetn_info = $this->_model->find($pk_value)) {//编辑留言评论不存在
            $this->_model->addLog($log_msg . '<br />' . L("INVALID_PARAM,%:,CONTROLLER_NAME,%{$pk_field}({$pk_value}),NOT_EXIST"), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, $error_msg);
        }

        if (false === $this->_model->save()) {//更新出错
            $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . "({$pk_value})" . L('FAILURE'), $error_msg);
        }

        $diff = $this->_dataDiff($commetn_info, $data, $diff_key);//差异

        $this->_model->addLog($msg . L('CONTROLLER_NAME')  . "({$pk_value})." . $diff. L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);

        $this->_ajaxReturn(true, $msg . L('SUCCESS'));
    }//end addAction

    /**
     * 重新获取ip
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-31 08:39:20
     *
     * @return void 无返回值
     */
    public function afreshIpAction() {
        //1631|3399838689,1632|3399838679,1633|3399838526,1634|2101762044,1635|3399838523,
        //1636|3673755165,1637|989600357,1638|3673755356,1639|3399838674,1640|3399838705
        //,1641|1032096356,1642|976345078,1643|3391871307,1644|2043434434,1645|3395489940,
        //1646|3549731567,1647|3399838587,1648|3549731521,1649|3708103387,1650|3708103373
        if (!$data = Filter::string('data')) {
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('AFRESH,GET,%ip,AREA,FAILURE') . '.data' . L('PARAM,IS_EMPTY');
            C('TRIGGER_ERROR', array($log));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('INVALID_PARAM,%data。') . $msg . L('FAILURE'));
        }

        $msg        = L('AFRESH,GET,%ip,AREA');
        $error      = '';
        $id_arr     = array();
        $data_arr   = explode(',', $data);

        foreach($data_arr as $v) {
            $arr    = explode('|', $v);
            $count  = count($arr);

            if (2 != $count || !($id = intval($arr[0])) || !($user_ip = sprintf('%u', ip2long($ip = $arr[1]))) || !$this->_model->where(array('comment_id' => $id, 'user_ip' => $user_ip))->field('comment_id')->find()) {
                $error .= ',' . $v;
            }
            else {
                $id_arr[] = $id;

                $ip_info = get_ip_info($ip);
                $ip_info = addslashes_deep($ip_info);

                if (is_array($ip_info)) {
                    $update = "province='{$ip_info[0]}',city='{$ip_info[1]}'";
                }
                else {
                    $update = "city='{$ip_info}'";
                }

                $this->_model->execute('UPDATE ' . TB_COMMENTS . " SET {$update} WHERE comment_id={$id}");
            }
        }

        if (!$id_arr) {
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('AFRESH,GET,%ip,AREA,FAILURE,%。,INVALID,%data=') . $data;
            C('TRIGGER_ERROR', array($log));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('INVALID_PARAM,%data。') . $msg . L('FAILURE'));
        }

        $error && $this->triggerError(substr($error, 1) . L('FORMAT,NOT_CORRECT'));

        $this->_model->addLog($msg . join(',',$id_arr) . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
        $this->_ajaxReturn(true, $msg . L('SUCCESS'));

    }//end afreshIpAction

    /**
     * 审核状态
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-29 09:13:46
     *
     * @return void 无返回值
     */
    public function auditingAction() {
        $this->_model->startTrans();

        $field      = 'status';
        $value      = Filter::int($field);
        $status_arr = array(
            COMMENT_STATUS_UNAUDITING   => L('CN_WEI,AUDITING'),
            COMMENT_STATUS_PASS         => L('CN_YI,PASS'),
            COMMENT_STATUS_UNPASS       => L('CN_WEI,PASS'),
        );

        if (!isset($status_arr[$value])) {
            $log = L('INVALID,AUDITING,STATUS');
            $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $log . ': ' . $value);
            $this->_ajaxReturn(false, $log);
        }

        C('T_STATUS_ARR', array($field => $status_arr));
        C('T_STATUS', $value);
        $this->_setOneOrZero($field, $value);
    }//end auditingAction

    /**
     * 获取博客具体信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-26 12:14:15
     *
     * @return $this->_info()结果
     */
    public function infoAction() {
        return $this->_info(false);
    }

    /**
     * 管理员列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-26 14:22:09
     * @lastmodify      2013-03-31 19:03:13 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $db_fields      = $this->_getDbFields();//表字段
        $sort           = Filter::string('sort', 'get', $this->_pk_field);//排序字段
        $sort           = in_array($sort, $db_fields) ? $sort : $this->_pk_field;
        $sort           = 'c.' . $sort;
        $order          = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
        $order          = toggle_order($order);
        $keyword        = Filter::string('keyword', 'get');//关键字
        $date_start     = Filter::string('date_start', 'get');//添加开始时间
        $date_end       = Filter::string('date_end', 'get');//添加结束时间
        $column         = Filter::string('column', 'get');//搜索字段
        $type           = Filter::int('type', 'get');//类型
        $status         = Filter::int('auditing', 'get');//状态
        $reply_type     = Filter::int('admin_reply_type', 'get');//回复状态
        $where          = array();
        $column_arr     = array(
            'username'      => 'c.username',
            'email'         => 'c.email',
            'content'       => 'c.content',
            'blog_id'       => 'c.blog_id',
            'miniblog_id'   => 'c.blog_id',
            'blog_content'  => 'b.content',
            'blog_title'    => 'b.title',
        );

        if ($keyword !== '' && isset($column_arr[$column])) {
            $where[$column_arr[$column]] = $this->_buildMatchQuery($column_arr[$column], $keyword, Filter::string('match_mode', 'get'));

            if ('blog_content' == $column || 'blog_title' == $column) {
                $table = ' JOIN ' . TB_BLOG . ' AS b ON b.blog_id=c.blog_id';
            }
        }

        if ($date_start && ($date_start = strtotime($date_start))) {
            $where['c.add_time'][] = array('EGT', $date_start);
        }

        if ($date_end && ($date_end = strtotime($date_end))) {
            $where['c.add_time'][] = array('ELT', $date_end);
        }

        if (isset($where['c.add_time']) && count($where['c.add_time']) == 1) {
            $where['c.add_time'] = $where['c.add_time'][0];
        }

        if (-1 != $type) {//类型
            $where['c.type'] = $type;
        }

        if (-1 != $status) {//状态
            $where['c.status'] = $status;
        }

        if (-1 != $reply_type) {//回复状态
            $where['c.admin_reply_type'] = $reply_type;
        }

        isset($table) && $this->_model->join($table);

        $total      = $this->_model->alias('c')->where($where)->count('c.blog_id');

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);

        isset($table) && $this->_model->join($table);

        $data      = $this->_model->alias('c')
        ->field('c.*,INET_NTOA(user_ip) AS user_ip')
        ->where($where)
        ->limit($page_info['limit'])
        ->order(('' .$sort) . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('LIST,ERROR'));//出错

        $selected = array(COMMENT_TYPE_BLOG => array(), COMMENT_TYPE_MINIBLOG => array());

        foreach($data as $k => $v) {
            $type       = $v['type'];
            $blog_id    = $v['blog_id'];

            if (COMMENT_TYPE_BLOG == $type) {

                if (isset($selected[COMMENT_TYPE_BLOG][$blog_id])) {
                    $info = $selected[COMMENT_TYPE_BLOG][$blog_id];
                }
                else {
                    $info = $this->_model->table(TB_BLOG)->where('blog_id=' . $blog_id)->field('title,link_url')->find();
                    $selected[COMMENT_TYPE_BLOG][$blog_id] = $info;
                }

                $data[$k]['title'] = $info['title'];
                $data[$k]['link_url'] = $info['link_url'];
            }
            elseif (COMMENT_TYPE_MINIBLOG == $type) {

                if (isset($selected[COMMENT_TYPE_MINIBLOG][$blog_id])) {
                    $info = $selected[COMMENT_TYPE_MINIBLOG][$blog_id];
                }
                else {
                    $info = $this->_model->table(TB_MINIBLOG)->where('blog_id=' . $v['blog_id'])->field('add_time,link_url')->find();
                    $selected[COMMENT_TYPE_MINIBLOG][$blog_id] = $info;
                }

                $data[$k]['title'] = new_date('Y-m-d', $info['add_time']) . ' ' . L('MINIBLOG');
                $data[$k]['link_url'] = $info['link_url'];
            }
        }//end foreach

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction

    /**
     * 回复留言评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-03 17:11:14
     *
     * @return void 无返回值
     */
    public function replyAction() {
        $comment_id = Filter::int($this->_pk_field);
        $add_time   = Filter::int('add_time');

        if (!$comment_id && !$add_time) {//非法参数
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('REPLY,CONTROLLER_NAME,%.,INVALID_PARAM') . "{$this->_pk_field}({$comment_id}),add_time({$add_time})";
            $msg = L('INVALID_PARAM');
        }
        elseif (!$comment_info = $this->_model->where(array($this->_pk_field => $comment_id, 'add_time' => $add_time))->find()) {//不存在
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('REPLY,CONTROLLER_NAME') . ".{$this->_pk_field}({$comment_id}),add_time({$add_time})" . L('NOT_EXIST');
            $msg = L('CONTROLLER_NAME,NOT_EXIST');
        }
        elseif (COMMENT_REPLY_TYPE_ADMIN == $comment_info['admin_reply_type']) {//
            $msg = L('CONTROLLER_NAME_ADMIN,REPLY,%。,CAN_NOT,REPLY');
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('REPLY,CONTROLLER_NAME') . '.admin_reply_type=' . COMMENT_REPLY_TYPE_ADMIN . ',' . $msg;
        }
        elseif (!$content = Filter::raw('content')) {
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('REPLY,CONTROLLER_NAME') . ".{$this->_pk_field}" . L('REPLY,CONTENT,IS_EMPTY');
            $msg = L('PLEASE_ENTER,REPLY,CONTENT');
        }
        elseif ('<p>' != substr($content, 0, 3)) {
            $content = '<p>' . $content . '</p>';
        }

        if (!empty($msg)) {//错误
            C('TRIGGER_ERROR', array($log));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, $msg);
        }

        $this->_model->startTrans();

        $ip_info = get_ip_info();//ip地址信息

        if (is_array($ip_info)) {
            $province   = $ip_info[0];
            $city       = $ip_info[1];
        }
        else {
            $province   = '';
            $city       = $ip_info;
        }

        $db   = $this->_model->getDb();
        $data = array(
            'province'  => $province,
            'city'      => $city,
            'content'   => $content,
        );

        if (COMMENT_REPLY_TYPE_REPLIED == $comment_info['admin_reply_type']) {//已经回复
            $add        = false;
            $reply_info = $this->_model->field($this->_pk_field . ',content')->where('admin_reply_type=' . COMMENT_REPLY_TYPE_ADMIN  . ' AND real_parent_id=' . $comment_id)->find();
            $log        = $this->_dataDiff(array('content' => $reply_info['content']), $data);
            $db->update($data, array('table' => TB_COMMENTS, 'where' => "{$this->_pk_field}={$reply_info[$this->_pk_field]}"));
        }
        else {
            $add  = true;
            $data = $data + array(
                'parent_id'         => $comment_id,
                'real_parent_id'    => $comment_id,
                'add_time'          => time(),
                'username'          => sys_config('module_guestbook_comments_reply_admin_username', 'Module'),
                'email'             => sys_config('module_guestbook_comments_reply_admin_email', 'Module'),
                'user_pic'          => str_replace('@common_imgcache@', COMMON_IMGCACHE, sys_config('module_guestbook_comments_reply_admin_img', 'Module')),
                'user_ip'           => get_client_ip(1),
                'user_homepage'     => BASE_SITE_URL,
                'admin_reply_type'  => COMMENT_REPLY_TYPE_ADMIN,
                'status'            => COMMENT_STATUS_PASS,
                'type'              => $comment_info['type'],
                'blog_id'           => $comment_info['blog_id'],
                'at_email'          => 1,
                'level'             => $comment_info['level'] + 1,
            );

            $reply = '@<a class="link" href="#comment-' . $comment_info['comment_id'] . '" rel="nofollow">' . $comment_info['username'] .  '</a> ';
            $data['content'] = substr_replace($data['content'], '<p>' . $reply, 0, 3);

            if (false === $this->_model->getDb()->insert($data, array('table' => TB_COMMENTS))) {
                $this->_sqlErrorExit(L('REPLY,FAILURE'));
            }

            $insert_id          = $db->getLastInsertID();
            $max_reply_level    = $this->getGuestbookCommentsSetting(COMMENT_TYPE_GUESTBOOK == $comment_info['type'] ? 'module_guestbook' : 'module_comments', 'max_reply_level');

            if ($max_reply_level == $comment_info['level']) {//最多5层回复
                $comment_info['level']--;
                $comment_info['node'] = substr($comment_info['node'], 0, strrpos($comment_info['node'], ','));
                $node_arr  = explode(',', $comment_info['node']);
                $parent_id = $node_arr[$max_reply_level > 2 ? $max_reply_level - 2 : 1];//父级id取第四个
            }

            $update = array(
                'level'             => $comment_info['level'] + 1,//层级
                'node'              => $comment_info['node'] . ',' . $insert_id,//节点关系
            );

            if (!empty($parent_id)) {
                $update['parent_id'] = $parent_id;
            }

            C('_FACADE_SKIP', true);
            $this->_model->where($this->_pk_field . '=' . $comment_id)->save(array('admin_reply_type' => COMMENT_REPLY_TYPE_REPLIED));//已经回复
            $this->_model->where($this->_pk_field . '=' . $insert_id)->save($update);
            $this->_model->where(array($this->_pk_field => array('IN', $comment_info['node'])))->save(array('last_reply_time' => time()));//更新最上层最后回复时间

            //干掉静态页,重新统计评论数等
            $a = array($comment_id);
            $b = '';
            $this->_beforeExec($a, $b);

            //处理发送有回复邮件
            if ($comment_info['at_email']) {
                C('T_INFO.at_email', $this->_getMailCommentsInfo($comment_id, C('T_INFO.delete_blog_html')));
                $this->_afterSetField('status', null, null);
            }

            $log = $content;
        }

        $this->_model->addLog(L('REPLY,GUESTBOOK_COMMENTS') . $this->_pk_field . "({$comment_id})" . $log, LOG_TYPE_ADMIN_OPERATE);
        $this->_ajaxReturn(true, L('REPLY,SUCCESS'));
    }//end replyAction

    /**
     * 查看某一条留言评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-01 11:00:03
     *
     * @return void 无返回值
     */
    public function viewAction() {
        $comment_id = Filter::int($this->_pk_field, 'get');
        $add_time   = Filter::int('add_time', 'get');
        $field      = '*,INET_NTOA(user_ip) AS user_ip';
        if (!$comment_id && !$add_time) {//非法参数
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('CN_CHAKAN,CONTROLLER_NAME,%.,INVALID_PARAM') . "{$this->_pk_field}({$comment_id}),add_time({$add_time})";
            $msg = L('INVALID_PARAM');
        }
        elseif (!$comment_info = $this->_model->field($field)->where(array($this->_pk_field => $comment_id, 'add_time' => $add_time))->select()) {//不存在
            $log = __METHOD__ . ': ' . __LINE__ . ',' . L('CN_CHAKAN,CONTROLLER_NAME') . ".{$this->_pk_field}({$comment_id}),add_time({$add_time})" . L('NOT_EXIST');
            $msg = L('CONTROLLER_NAME,NOT_EXIST');
        }

        if (!empty($msg)) {//错误
            C('TRIGGER_ERROR', array($log));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, $msg);
        }

        $store  = array($this->_pk_field => $comment_id, 'add_time' => $add_time, 'content' => '');
        $info   = $comment_info[0];

        if (COMMENT_REPLY_TYPE_REPLIED == $info['admin_reply_type']) {//
            $reply_content = $this->_model->where('admin_reply_type=' . COMMENT_REPLY_TYPE_ADMIN  . ' AND real_parent_id=' . $info[$this->_pk_field])->getField('content');
            $store['content'] = $reply_content;
        }

        if ($parent_id = $info['parent_id']) {
            $node_arr       = explode(',', $info['node']);
            $comment_info   = $this->_model->field($field)->where("type={$info['type']} AND (node LIKE '{$node_arr[0]},%' OR {$this->_pk_field} = {$node_arr[0]}) AND comment_id<={$comment_id}")->select();
        }

        $this->_ajaxReturn(true, $store, Tree::array2tree($comment_info, $this->_pk_field));
    }//end viewAction
}