<?php
/**
 * 博客控制器类
 *
 * @file            BlogController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-03-23 11:21:07
 * @lastmodify      $Date$ $Author$
 */

class BlogController extends CommonController {
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
        $this->_model->table(TB_COMMENTS)->where(array($this->_pk_field => array('IN', $pk_id), 'type' => COMMENT_TYPE_BLOG))->delete();
        $this->_deleteBlogHtml(null);
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterSetField($field, $value, $pk_id) {

        if ('cate_id' == $field || ($value && 'is_delete' == $field) || (!$value && 'is_issue' == $field)) {//转移分类、未发布、已删除
            //$this->getViewTemplate()->clearCache($this->_getControllerName(), 'detail', $pk_id);
            $this->R('Category/publicDeleteHtmlAction');
            $this->_deleteBlogHtml(null);//删除静态文件
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeExec(&$pk_id, &$log) {
        $pk_field   = $this->_pk_field;
        $data       = $this->_model->where(array($pk_field => array('IN', $pk_id)))->field($pk_field . ',title,cate_id,link_url')->select();
        $log        = '';
        $info       = array();//记录操作博客信息，如删除静态文件，删除对应类静态文件等

        if (false !== $data) {

            foreach ($data as $v) {
                $log .= $v['title'] . "({$v[$pk_field]}),";
                $info[$v[$pk_field]] = array('cate_id' => $v['cate_id'], 'link_url' => $v['link_url']);
            }

            C('HTML_BUILD_INFO', $info);
        }

        return $log ? substr($log, 0, -1) : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$cate_info) {
        $cate_info['add_time'] = new_date(sys_config('sys_timezone_datetime_format'), $cate_info['add_time']);
        $cate_info['cate_name'] = $this->cache($cate_info['cate_id'] . '.cate_name', 'Category');
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
        $pk_value  = $this->_model->$pk_field;//博客id
        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $to_build  = $data['is_issue'] && !$data['is_delete'];
        $diff_key  = 'title,content,cate_name,is_issue,seo_keyword,seo_description,sort_order,is_delete';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('CONTROLLER_NAME_BLOG,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息
        $cate_info = $this->cache($cate_id = $this->_model->cate_id, 'Category');//所属分类

        $data['cate_name'] = $cate_info['cate_name'];//所属分类名称

        unset($data['link_url']);

        if ($pk_value) {//编辑

            if (!$blog_info = $this->_model->find($pk_value)) {//编辑博客不存在
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%:,CONTROLLER_NAME_BLOG,%{$pk_field}({$pk_value}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            if (false === $this->_model->save()) {//更新出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME_BLOG') . "{$blog['title']}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $cate_info = $this->cache($blog_info['cate_id'], 'Category');
            $blog_info['cate_name'] = $cate_info['cate_name'];//所属分类名

            $diff = $this->_dataDiff($blog_info, $data, $diff_key);//差异

            strpos($diff, 'seo_keyword') && $this->_model->addTags($pk_value, $data['seo_keyword']);

            $this->_model->addLog($msg . L('CONTROLLER_NAME_BLOG')  . "{$blog_info['title']}({$pk_value})." . $diff. L('SUCCESS'));

            if (!$to_build) {
                C('HTML_BUILD_INFO', array($pk_value => array('cate_id' => $blog_info['cate_id'] . ',' . $data['cate_id'], 'link_url' => $blog_info['link_url'])));
                $this->_deleteBlogHtml(null);
            }

            $this->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
        else {
            $data = $this->_dataDiff($data, false, $diff_key);//数据

            if (false === ($insert_id =$this->_model->add())) {//插入出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME_BLOG') . $data . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('CONTROLLER_NAME_BLOG') . $data . L('SUCCESS'));
            $this->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }//end addAction

    /**
     * {@inheritDoc}
     */
    public function deleteBlogHtmlAction() {
        $this->_name_column = 'title';
        parent::deleteBlogHtmlAction();
    }//end deleteBlogHtmlAction

    /**
     * 获取博客具体信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-26 12:14:15
     *
     * @return $this->_info()结果
     */
    function infoAction() {
        return $this->_info(false);
    }

    /**
     * 删除状态
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:32:41
     *
     * @return void 无返回值
     */
    public function isDeleteAction() {
        $this->_setOneOrZero('isDelete');
    }

    /**
     * 发布状态
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:32:41
     *
     * @return void 无返回值
     */
    public function issueAction() {
        $this->_setOneOrZero('is_issue');
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
        $order          = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
        $order          = toggle_order($order);
        $keyword        = Filter::string('keyword', 'get');//关键字
        $date_start     = Filter::string('date_start', 'get');//注册开始时间
        $date_end       = Filter::string('date_end', 'get');//注册结束时间
        $cate_id        = Filter::int('cate_id', 'get');//所属管理组
        $column         = Filter::string('column', 'get');//搜索字段
        $is_delete      = Filter::int('is_delete', 'get');//删除
        $is_issue       = Filter::int('is_issue', 'get');//状态
        $where          = array();

        if ($keyword !== '' && in_array($column, array('title', 'seo_keyword', 'seo_description', 'content', 'from_name', 'from_url'))) {
            $where['' . $column] = $this->_buildMatchQuery('' . $column, $keyword, Filter::string('match_mode', 'get'));
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

        if (-1 != $is_delete) {//删除
            $where['is_delete'] = $is_delete;
        }

        if (-1 != $is_issue) {//状态
            $where['is_issue'] = $is_issue;
        }

        if ($cate_id) {
            $cate_arr = $this->cache($cate_id, 'Category');

            if (!$cate_arr) {
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . L("INVALID_PARAM,%:,BELONG_TO_CATEGORY,%cate_id({$cate_id}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(true);
            }

            $where['cate_id'] = array('IN', $this->_getChildrenIds($cate_id, true, true, 'Category'));
        }

        $total      = $this->_model->where($where)->count();

        if ($total === false) {//查询出错
            $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_BLOG') . L('TOTAL_NUM,ERROR'));
        }
        elseif ($total == 0) {//无记录
            $this->_ajaxReturn(true, '', null, $total);
        }

        $page_info = Filter::page($total);
        $data      = $this->_model
        ->where($where)->field('content', true)
        ->limit($page_info['limit'])
        ->order(('' .$sort) . ' ' . $order)->select();

        $data === false && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME_BLOG') . L('LIST,ERROR'));//出错

        $cate_arr = $this->cache(false, 'Category');

        foreach($data as &$v) {
            $v['cate_name'] = $cate_arr[$v['cate_id']]['cate_name'];
        }

        $this->_ajaxReturn(true, '', $data, $total);
    }//end listAction

    /**
     * 移动所属分类
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-31 19:27:28
     *
     * @return void 无返回值
     */
    function moveAction() {
        $field       = 'cate_id';//定段
        $cate_id     = Filter::int($field);//所属分类id
        $msg         = L('MOVE');//提示
        $log_msg     = $msg . L('CONTROLLER_NAME_BLOG,FAILURE');//错误日志
        $error_msg   = $msg . L('FAILURE');//错误提示信息

        if ($cate_id) {//分类id
            $cate_info = $this->cache($cate_id, 'Category');

            if (!$cate_info) {//分类不存在
                $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%:,BELONG_TO_CATEGORY,%{$field}({$cate_id}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            $cate_name = $cate_info['cate_name'];
        }
        else {
            //非法参数
            $log    = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%: {$field},IS_EMPTY");
            trigger_error($log, E_USER_ERROR);
            $this->_ajaxReturn(false, $error_msg);
        }

        $this->_setField($field, $cate_id, $msg, L('TO') . $cate_name);
    }//end moveAction

    /**
     * 删除静态文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-17 14:33:27
     *
     * @param $build_arr array|null 已修改博客信息
     *
     * @return void 无返回值
     */
    public function publicDeleteHtmlAction($build_arr = array()) {
        $this->_deleteBlogHtml($build_arr);
    }

    /**
     * 测试
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-16 11:52:29
     *
     * @return void 无返回值
     */
    public function testAction() {
        !APP_DEBUG && exit('Access Denied');
        set_time_limit(0);

        $model  = $this->_model;
        $db     = $model->getDb();

        if (isset($_GET['getIp'])) {//重新获取ip地址信息
            static $ip_result = array();$c = '';

            $comment_arr = $model->field('user_ip,comment_id')->table('tb_comments')->select();

            foreach($comment_arr as $comment_info) {
                $user_ip = $comment_info['user_ip'];

                if (isset($ip_result[$user_ip])) {
                    $data = $ip_result[$user_ip];
                }
                else {

                    $ip_info = get_ip_info($user_ip);//ip地址信息

                    if (is_array($ip_info)) {
                        $data = array(
                            'province'  => $ip_info[0],
                            'city'      => $ip_info[1],
                        );
                    }
                    else {
                        $data = array('city' => $ip_info);
                    }

                    $ip_result[$user_ip] = $data;
                }

                $db->update($data, array('table' => TB_COMMENTS, 'where' => 'comment_id=' . $comment_info['comment_id']));
            }

            return;
        }//end if

        $model->startTrans();

        $blog_arr   = $model->table('tb_blog_laruence')->order('add_time ASC')->select();

        foreach($blog_arr as $blog_info) {
            $blog_id = $blog_info['blog_id'];
            unset($blog_info['blog_id']);
            $blog_info['title'] = html_entity_decode($blog_info['title']);
            $blog_info['content'] = trim($blog_info['content']);
            $blog_info['summary'] = trim($blog_info['summary']);

            if ($db->insert($blog_info, array('table' => TB_BLOG))) {
                $insert_id = $model->getLastInsertID();
                $model->save(array($this->_pk_field => $insert_id, 'link_url' => BASE_SITE_URL . 'blog/' . date('Ymd/', $blog_info['add_time']) . $insert_id . C('HTML_SUFFIX')));
                $model->addTags($insert_id, $blog_info['seo_keyword']);

                //评论
                $comment_arr = $model->table('tb_comments_laruence')->order('add_time ASC')->where('blog_id=' . $blog_id)->select();

                foreach($comment_arr as $comment_info) {
                    unset($comment_info['comment_id']);
                    $comment_info['username'] = htmlspecialchars_decode($comment_info['username'], ENT_QUOTES);
                    $comment_info['user_ip'] = sprintf('%u', ip2long($comment_info['node']));
                    $comment_info['blog_id'] = $insert_id;
                    $db->insert($comment_info, array('table' => TB_COMMENTS));
                }
            }
        }

        $db->query('UPDATE ' . TB_COMMENTS . ' SET node=comment_id');

        $model->commit();
    }//end testAction
}