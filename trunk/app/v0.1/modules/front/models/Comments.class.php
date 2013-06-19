<?php
/**
 * 评论留言模型
 *
 * @file            Log.class.php
 * @package         Yab\Module\Yab\Model
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-26 17:40:44
 * @lastmodify      $Date$ $Author$
 */

class CommentsModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'add_time'          => 'time#insert',
        'last_reply_time'   => 'time#insert',
        'user_ip'           => 'get_client_ip#1',
        'content'           => '_setContent',
        'status'            => '_setStatus',
        'at_email'          => '_getCheckboxValue',//有人回复时通知我
        'real_parent_id'    => array('parent_id', Model::MODEL_INSERT, 'field', null),//实际回复id
    );
    /**
     * @var array $_db_fields 表字段
     */
    protected $_db_fields = array (
        'type'           => array('filter' => 'int', 'validate' => array('_checkType#INVALID_PARAM,TYPE')),
        'blog_id'        => array('filter' => 'int', 'validate' =>  '_checkBlog#BLOG,NOT_EXIST'),//博客id 或 微博id ,调用C('T_TYPE')放于type后面
        'parent_id'      => array('filter' => 'int', 'validate' =>  '_checkReply#INVALID,COMMENTS'),//父id,调到C('T_BLOG_ID')放于_checkBlog后面
        'real_parent_id' => array('filter' => 'int', 'validate' => array('_checkLength#REAL,PARENT_ID,DATA#value|0')),
        //用户名
        'username'       => array('validate' => array('_checkUsername#PLEASE_ENTER,USERNAME', '_checkLength#USERNAME#value|0|20')),
        'email'          => array('filter' => 'email', 'validate' => array('_checkLength#EMAIL#value|0|50')),
        'content'        => array('validate' => array('notblank#CONTENT')),
        'add_time'       => array('filter' => 'int', 'validate' => array('_checkLength#ADD_TIME,DATA#value|0')),//添加时间
        'last_reply_time'=> array('filter' => 'int', 'validate' => array('_checkLength#LAST_REPLY_TIME,DATA#value|0')),//最后回复时间
        'user_ip'        => array('filter' => 'int', 'validate' => array('_checkLength#USER_IP,DATA#value|0')),//用户ip
        'level'          => array('filter' => 'int', 'validate' => array('_checkLength#LEVEL,DATA#value|0')),
        'node'           => array('filter' => 'int', 'validate' => array('_checkLength#NODE,DATA#value|0')),
        'user_homepage'  => array('filter' => 'url', 'validate' => array(array('', '{%PLEASE_ENTER,CORRECT,CN_DE,HOMEPAGE,LINK}', Model::VALUE_VALIDATE, 'url'), '_checkLength#HOMEPAGE,LINK#value|0|50')),
        'user_pic'       => array('filter' => 'url', 'validate' => array('_checkLength#USER_PIC,DATA#value|0')),
        'status'         => array('filter' => 'int', 'validate' => array('_checkLength#STATUS,DATA#value|0')),
        '_verify_code'   => array('validate' => '_checkVerifycode#PLEASE_ENTER,VERIFY_CODE#module_admin'),//验证码
        'province'       => array('validate' => array('_checkLength#PROVINCE,DATA#value|0')),
        'city'           => array('validate' => array('_checkLength#CITY,DATA#value|0')),
        'at_email'       => array('filter' => 'int'),//有人回复时通知我
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认log_id
     */
    protected $_pk_field        = 'comment_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_COMMENTS
     */
    protected $_true_table_name = TB_COMMENTS;//表

    /**
     * 验证其它,包括提交频率,禁止ip等
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-05-24 09:15:07
     *
     * @return string|bool true验证成功,否则错误信息
     */
    private function _checkOther() {
        $module     = C('T_VERIFYCODE_MODULE');
        $last_time  = session($module);

        if ($last_time > time() && $this->_module->getGuestbookCommentsSetting($module, 'alternation')) {//提交过于频繁
            $error = sprintf(L('YOUR_SUBMIT_HIGH_FREQUENCY'), L(C('T_MODULE')));
            $log   = $module . $error . ',' . new_date(null, $last_time) . ' => ' . new_date();
            $error = $error . ',' . str_replace('{0}', new_date(null, $last_time), L('TRY_IT_UNTIL'));
        }

        if ($disabled_ip = $this->_module->getGuestbookCommentsSetting($module, 'disabled_ip')) {

            if (in_array($ip = get_client_ip(), explode(EOL_LF, $disabled_ip))) {
                $log = $error = L('FORBID,' . C('T_MODULE')) . 'ip:' . $ip;
            }
        }

        if (empty($log)) {
            return true;
        }

        $this->_module->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $log);
        C('T_REDIRECT', true);

        return $error;
    }//end _checkOther

    /**
     * 插入后置操作，向留言表增加刚插入id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-01 13:30:52
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterInsert($data, $options) {

        if (TB_COMMENTS != $options['table']) {
            return;
        }

        $pk_value = $data[$this->_pk_field];

        if ($parent_info = C('T_PARENT_INFO')) {//父
            $max_reply_level    = $this->_module->getGuestbookCommentsSetting(C('T_VERIFYCODE_MODULE'), 'max_reply_level');

            if ($max_reply_level == $parent_info['level']) {//最多5层回复
                $this->_module->triggerError(__METHOD__ . ': ' . __LINE__ . ',level>' . $max_reply_level . var_export($parent_info, true), E_USER_NOTICE);

                $parent_info['level']--;
                $parent_info['node'] = substr($parent_info['node'], 0, strrpos($parent_info['node'], ','));
                $node_arr  = explode(',', $parent_info['node']);
                $parent_id = $node_arr[$max_reply_level > 2 ? $max_reply_level - 2 : 1];//父级id取第四个

            }

            $update = array(
                'level'          => $parent_info['level'] + 1,//层级
                'node'           => $parent_info['node'] . ',' . $pk_value,//节点关系
            );

            if (!empty($parent_id)) {
                $update['parent_id'] = $parent_id;
            }

            $this->where($this->_pk_field . '=' . $pk_value)->save($update);
            $this->where(array($this->_pk_field => array('IN', $parent_info['node'])))->save(array('last_reply_time' => time()));//更新最上层最后回复时间
        }
        else {

            $update = array(
                'node'           =>  $pk_value,
            );

            $this->where($this->_pk_field . '=' . $pk_value)->save($update);//节点关系
        }

        if ($v = $this->_module->getGuestbookCommentsSetting(C('T_VERIFYCODE_MODULE'), 'alternation')) {//间隔
            session(C('T_VERIFYCODE_MODULE'), time() + $v);
        }

        $type = C('T_TYPE');

        if (!$this->_module->getGuestbookCommentsSetting(C('T_VERIFYCODE_MODULE'), 'check')) {//不需要审核

            if (COMMENT_TYPE_GUESTBOOK != $type) {//评论数+1
                $this->execute('UPDATE ' . (COMMENT_TYPE_BLOG == $type ? TB_BLOG : TB_MINIBLOG) . ' SET comments=comments+1 WHERE blog_id=' . $data['blog_id']);
            }

            if (($parent_info = C('T_PARENT_INFO')) && $parent_info['at_email']) {
                require_cache(LIB_PATH . 'Mailer.class.php');
                $mailer = new Mailer($this);
                $mailer->mail('comments_at_email', $parent_info);
            }
        }

        //总评论数+1
        COMMENT_TYPE_GUESTBOOK != $type && $this->execute('UPDATE ' . (COMMENT_TYPE_BLOG == $type ? TB_BLOG : TB_MINIBLOG) . ' SET total_comments=total_comments+1 WHERE blog_id=' . $data['blog_id']);

        $this->commit();
    }//end _afterInsert

    /**
     * {@inheritDoc}
     */
    protected function _beforeInsert(&$data, $options) {

        if (TB_COMMENTS != $options['table']) {
            return;
        }

        $ip_info = get_ip_info();//ip地址信息

        if (is_array($ip_info)) {
            $data['province'] = $ip_info[0];
            $data['city'] = $ip_info[1];
        }
        else {
            $data['city'] = $ip_info;
        }

        $data['email'] = empty($data['email']) ? '' : strtolower($data['email']);

        if (!empty($data['at_email']) && empty($data['email'])) {//勾选 有人回复我时通知我，邮箱却为空
            $this->_module->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . L(C('T_MODULE')) . ',' . L('AT_ME_NOTICE_ME') . ',' . L('EMAIL,IS_EMPTY'), E_USER_WARNING);
            unset($data['at_email']);
        }
    }

    /**
     * 检查博客或者微博是否存在
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-05-04 12:02:33
     *
     * @param int $blog_id 博客id 或 微博id
     *
     * @return true|string true存在，否则错误信息
     */
    protected function _checkBlog($blog_id) {
        $result = true;
        $type   = C('T_TYPE');
        $table  = array(
            COMMENT_TYPE_BLOG       => TB_BLOG,
            COMMENT_TYPE_MINIBLOG   => TB_MINIBLOG,
        );

        if (COMMENT_TYPE_GUESTBOOK == $type) {//留言,blog_id=0
            $result = !$blog_id;
        }
        elseif (!isset($table[$type])) {
            $result = false;
        }
        elseif (!$blog_id || !($blog_link_url = $this->table($table[$type])->where('blog_id=' . $blog_id)->getField('link_url'))) {
            $result = false;
        }

        !$result && C('T_REDIRECT', true);
        C('T_BLOG_ID', $blog_id);
        C('T_BLOG_LINK_URL', isset($blog_link_url) ? $blog_link_url : '');

        return $result;
    }

    /**
     * 检查回复回复是否存在
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-03-01 13:28:29
     *
     * @param int $parent_id 父id
     *
     * @return true|string true存在并且还可回复，即小于5层，否则错误信息
     */
    protected function _checkReply($parent_id) {

        if (!$parent_id) {//非回复
            return true;
        }

        $parent_info = $this->field('comment_id,parent_id,username,email,content,level,node,status,type,blog_id,at_email')->where(array($this->_pk_field => $parent_id, 'type' => C('T_TYPE'), 'blog_id' => C('T_BLOG_ID')))->find();//父亲信息

        if ($parent_info) {

            if (1 == $parent_info['status']) {//已通过

                if (COMMENT_TYPE_GUESTBOOK == C('T_TYPE')) {
                    $comment_name = L('GUESTBOOK');
                    $link_url = BASE_SITE_URL . 'guestbook.shtml';
                }
                else {
                    $comment_name = L('COMMENT');
                    $link_url = C('T_BLOG_LINK_URL');
                }

                $parent_info['link_url'] = $link_url . '#comment-' . $parent_id;
                $parent_info['comment_name'] = $comment_name;
                C('T_PARENT_INFO', $parent_info);

                return true;
            }

            $this->_module->triggerError(__METHOD__ . ': ' . __LINE__ . ',status=0' . var_export($parent_info, true));

            $error = L('INVALID,REPLY');
        }

        C('T_PARENT_INFO', $parent_info);

        $error = L('REPLY,NOT_EXIST');

        C('T_REDIRECT', true);

        return $error;
    }//end _checkReply

    /**
     * 验证评论类型
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-05-13 13:34:24
     *
     * @param int $type 类型
     *
     * @return bool true验证成功,否则false
     */
    protected function _checkType($type) {

        if (in_array($type, array(COMMENT_TYPE_GUESTBOOK, COMMENT_TYPE_BLOG, COMMENT_TYPE_MINIBLOG))) {
            C('T_TYPE', $type);
            C('T_MODULE', $module = COMMENT_TYPE_GUESTBOOK == $type ? 'guestbook' : 'comments');
            C('T_VERIFYCODE_MODULE', $module = 'module_' . $module);

            $check_other = $this->_checkOther();//其它验证,包括提交频率,禁止ip等

            return true === $check_other ? true : $check_other;
        }

        C('T_REDIRECT', true);

        return false;
    }

    /**
     * 检测用户名，包括禁用用户名
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-23 15:00:32
     *
     * @param string $username 用户名
     *
     * @return mixed true验证否则，如果未输入，返回提示信息，如果禁用，返回禁用信息，否则返回false
     */
    protected function _checkUsername($username) {

        if ($username === '') {//如果未输入，提示输入
            return false;
        }

        if ($disabled_username = $this->_module->getGuestbookCommentsSetting($module = C('T_VERIFYCODE_MODULE'), 'disabled_username')) {

            $separator = false === strpos($disabled_username, EOL_CRLF) ? EOL_LF : EOL_CRLF;//公司64位\n，家里32位\r\n,奇了怪了

            if (in_array(strtolower($username), explode($separator, strtolower($disabled_username)))) {
                $error = L('DISABLED,' . C('T_MODULE') . ',USERNAME') . $username;
                $this->_module->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $module . $error);
                C('T_REDIRECT', true);

                return $error;
            }
        }

        return true;
    }

    /**
     * html化内容
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-01 13:30:52
     *
     * @param string $content 内容
     *
     * @return string html化后的内容
     */
    protected function _setContent($content) {

        if ($v = C('T_PARENT_INFO')) {//回复 @用户名,务必@<a class="link",因为邮件会添加target="target_blank",后台会添加onclick="return false"属性
            $reply = '@<a class="link" href="#comment-' . $v['comment_id'] . '" rel="nofollow">' . $v['username'] .  '</a> ';
        }

        if (false !== strpos($content, 'http://') || false !== strpos($content, 'https://')) {//http 链接
            $content = preg_replace('#(https?://[\w+-]+\.[a-z0-9]+[^"\s]*)#', '<a href="\1" rel="nofollow">\1</a>', $content);
        }

        return '<p>' . (empty($reply) ? '' : $reply) . nl2br($content) . '</p>';
    }

    /**
     * 设置评论留言状态
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-13 13:46:17
     *
     * @param int $status 状态
     *
     * @return int 状态
     */
    protected function _setStatus() {
        return !$this->_module->getGuestbookCommentsSetting(C('T_VERIFYCODE_MODULE'), 'check');
    }
}