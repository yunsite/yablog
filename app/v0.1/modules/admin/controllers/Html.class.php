<?php
/**
 * 生成静态页管理控制器类
 *
 * @file            Html.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-18 09:33:40
 * @lastmodify      $Date$ $Author$
 */

class HtmlController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->_setCache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache    = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认tpl_name
     */
    protected $_name_column         = 'tpl_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map            = array(
         'delete'  => 'add',//删除
         'info'    => 'add',//具体信息
         'build'   => 'add',//生成ssi
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
        $caches = $this->_getCache();

        foreach($pk_id as $id) {
            is_file($filename = WWWROOT . $caches[$id]['html_name'] . C('HTML_SUFFIX')) && unlink($filename);
        }
    }

    /**
     * 生成静态页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-18 13:54:56
     *
     * @param array $item 信息
     *
     * @return string 错误信息
     */
    protected function _build($item) {

        //首页,网文首页,留言首页,微博首页 直接删除对应静态页即可
        if (in_array($item['html_name'], array('index', 'category', 'guestbook', 'miniblog'))) {
            is_file($filename = WWWROOT . $item['_controller'] . C('HTML_SUFFIX')) && unlink($filename);
            return '';
        }

        $method = "_{$item['_controller']}_{$item['_action']}";
        $error  = '';

        if (method_exists($this, $method)) {
            $this->$method($item);
        }
        else {
            $error = ',' . $method;
        }

        return $error;
    }

    /**
     * 分类导航
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 09:18:19
     *
     * @return void 无返回值
     */
    private function _categoryNav($parent_id = 0) {
        static $cate_arr = null;

        if (null === $cate_arr) {
            $cate_arr = $this->_getCache(0, 'Category');
        }

        $html      = '';

        foreach($cate_arr as $cate_id => $item) {

            if ($parent_id == $item['parent_id'] && $item['is_show']) {
                $a = sprintf('<li@class><a href="%s">%s</a>', $item['link_url'], $item['cate_name']);
                $b = $this->_categoryNav($cate_id);
                $a = str_replace('@class', $b ? ' class="dropdown-submenu"' : '', $a);

                $html .= $a . $b . '</li>';

                unset($cate_arr[$cate_id]);
            }
        }

        return $html ? '<ul class="dropdown-menu">' . $html . '</ul>' : '';
    }//end _categoryNav

    /**
     * 系统提示页面
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-16 11:33:43
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _html_msg($info) {
        $this->getViewTemplate('build_html')->assign('web_title', L('SYSTEM_INFOMATION'));
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 404页面
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-15 11:05:51
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _html_page_not_found($info) {
        $this->getViewTemplate('build_html')->assign('web_title', L('PAGE_NOT_FOUND'));
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 底部
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 13:30:10
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _ssi_footer($info) {
        $this->getViewTemplate('build_html')->assign('footer', sys_config('sys_base_copyright'));
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 热门网文
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 10:59:29
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _ssi_hot_blogs($info) {
        $blogs = $this->_model
        ->table(TB_BLOG)
        ->order('hits DESC')
        ->where('is_issue=1 AND is_delete=0')
        ->field('link_url,title')
        ->limit(10)
        ->select();
        $this->getViewTemplate('build_html')->assign('blogs', $blogs);
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 导航条
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 08:33:53
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _ssi_navbar($info) {
        $this->getViewTemplate('build_html')->assign('category_html', $this->_categoryNav());
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 最新评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 10:38:39
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _ssi_new_comments($info) {
        $data = $this->_model
        ->table(TB_COMMENTS)
        ->where('status=' . COMMENT_STATUS_PASS)
        ->order('comment_id DESC')
        ->limit(10)
        ->select();

        $selected = array(COMMENT_TYPE_BLOG => array(), COMMENT_TYPE_MINIBLOG => array());

        foreach($data as $k => $v) {
            $type       = $v['type'];
            $blog_id    = $v['blog_id'];

            if (COMMENT_TYPE_BLOG == $type) {

                if (isset($selected[COMMENT_TYPE_BLOG][$blog_id])) {
                    $blog_info = $selected[COMMENT_TYPE_BLOG][$blog_id];
                }
                else {
                    $blog_info = $this->_model->table(TB_BLOG)->where('blog_id=' . $blog_id)->field('title,link_url')->find();
                    $selected[COMMENT_TYPE_BLOG][$blog_id] = $blog_info;
                }

                $data[$k]['title'] = $blog_info['title'];
                $data[$k]['link_url'] = $blog_info['link_url'];
            }
            elseif (COMMENT_TYPE_MINIBLOG == $type) {

                if (isset($selected[COMMENT_TYPE_MINIBLOG][$blog_id])) {
                    $blog_info = $selected[COMMENT_TYPE_MINIBLOG][$blog_id];
                }
                else {
                    $blog_info = $this->_model->table(TB_MINIBLOG)->where('blog_id=' . $v['blog_id'])->field('add_time,link_url')->find();
                    $selected[COMMENT_TYPE_MINIBLOG][$blog_id] = $blog_info;
                }

                $data[$k]['title'] = new_date('Y-m-d', $blog_info['add_time']) . ' ' . L('MINIBLOG');
                $data[$k]['link_url'] = $blog_info['link_url'];
            }
        }//end foreach

        $this->getViewTemplate('build_html')->assign('comments', $data);
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }//end _ssi_new_comments

    /**
     * 标签云
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 10:38:39
     *
     * @param array $info ssi信息
     *
     * @return void 无返回值
     */
    private function _ssi_tags($info) {
        $tags = $this->_model
        ->table(TB_TAG)
        ->order('searches DESC')
        ->field('DISTINCT `tag`')
        ->limit(50)
        ->select();
        $this->getViewTemplate('build_html')->assign('tags', $tags);
        $this->_buildHtml(WWWROOT . $info['html_name'] . C('HTML_SUFFIX'), $this->_fetch($info['_controller'], $info['_action']));
    }

    /**
     * 获取写缓存数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 15:32:21
     *
     * @return mixed 查询成功，返回数组，否则false
     */
    protected function _setCacheData() {
        $data = $this->_model->key_column($this->_pk_field)->order('sort_order ASC,' . $this->_pk_field . ' ASC')->select();

        if ($data) {

            foreach($data as $k => $v) {
                $arr = explode('/', $v['tpl_name']);
                $data[$k]['_controller'] = $arr[0];
                $data[$k]['_action'] = $arr[1];
            }
        }

        return $data;
    }

    /**
     * 生成成功
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 16:07:14
     *
     * @return void 无返回值
     */
    private function _successAction() {

        if ('all' == ACTION_NAME) {
            $is_all = true;
            $this->_model->execute('UPDATE ' . TB_HTML . ' SET last_build_time=' . time());
        }
        elseif ('build' == ACTION_NAME) {
            $is_build = true;
            C('T_HTML_ID') && $this->_model->save(array($this->_pk_field => array('IN', C('T_HTML_ID')), 'last_build_time' => time()));
        }

        $this->_model->addLog(L('BUILD,STATIC_PAGE') . ',' . ACTION_NAME . C('T_LOG'), LOG_TYPE_ADMIN_OPERATE);
        $this->_setCache()->_ajaxReturn(true, L('BUILD,STATIC_PAGE,SUCCESS'));
    }

    /**
     * 添加
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 15:39:44
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//ssiid

        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'tpl_name,html_name,sort_order,memo';//比较差异字段 增加锁定字列by mrmsl on 2012-07-11 11:42:33
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('CONTROLLER_NAME,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if ($pk_value) {//编辑

            if (!$info = $this->_getCache($pk_value)) {//ssi不存在
                $this->_model->addLog($log_msg . '<br />' . L("INVALID_PARAM,%:,CONTROLLER_NAME,%{$pk_field}({$pk_value}),NOT_EXIST"), LOG_TYPE_INVALID_PARAM);
                $this->_ajaxReturn(false, $error_msg);
            }

            if (false === $this->_model->save()) {//更新出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . "{$info['tpl_name']}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $diff = $this->_dataDiff($info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L('CONTROLLER_NAME')  . "{$info['tpl_name']}({$pk_value})." . $diff. L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->_setCache()->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $data = $this->_dataDiff($data, false, $diff_key);//数据

            if ($this->_model->add() === false) {//插入出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . $data . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('CONTROLLER_NAME') . $data . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->_setCache()->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }

    /**
     * 全部生成ssi
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-10 10:40:15
     *
     * @return void 无返回值
     */
    public function allAction() {

        if ($data = $this->_getCache()) {
            $error  = '';

            foreach($data as $item) {
                $error .= $this->_build($item);
            }

           $error && $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $error . L('NOT_EXIST'));
        }

        $this->_successAction();
    }

    /**
     * 生成
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-14 11:30:05
     *
     * @return void 无返回值
     */
    public function buildAction() {
        $html_id   = map_int(Filter::string($this->_pk_field), true);

        if (!$html_id) {
            $this->_model->addLog(L('PRIMARY_KEY,DATA,IS_EMPTY'), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('BUILD,STATIC_PAGE,FAILURE'));
        }

        $caches = $this->_getCache();
        $error  = '';
        $log    = '';

        foreach($html_id as $k => $v) {

            if (isset($caches[$v])) {
                $item   = $caches[$v];
                $error .= $this->_build($item);
                $log   .= ",{$item['tpl_name']}({$item[$this->_pk_field]})";
            }
            else {
                unset($html_id[$k]);
                $error .= ',id(' . $v . ')';
            }
        }

        $html_id && C('T_HTML_ID', $html_id);
        $log && C('T_LOG', $log);

        $error && $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $error . L('NOT_EXIST'));

        $this->_successAction();
    }//end build

    /**
     * 列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 15:54:17
     *
     * @return void 无返回值
     */
    public function listAction() {
        $db_fields      = $this->_getDbFields();//表字段
        $sort           = Filter::string('sort', 'get', $this->_pk_field);//排序字段
        $sort           = in_array($sort, $db_fields) ? $sort : 'sort_order';
        $order          = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
        $order          = toggle_order($order);
        $data           = $this->_model->order($sort . ' ' . $order)->select();

        false === $data && $this->_sqlErrorExit(L('QUERY,CONTROLLER_NAME') . L('LIST,ERROR'));//出错

        $this->_ajaxReturn(true, '', $data);
    }
}