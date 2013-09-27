<?php
/**
 * 博客控制器类
 *
 * @file            BlogController.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-02-19 14:23:35
 * @lastmodify      $Date$ $Author$
 */

class BlogController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = true;

    /**
     * 根据指定博客id获取评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-28 14:56:35
     *
     * @param int $blog_id 当前博客id
     *
     * @return string 评论html
     */
    private function _getBlogComments($blog_id) {
        $comments = $this->_model
        ->table(TB_COMMENTS)
        ->where('type=' . COMMENT_TYPE_BLOG . ' AND status=' . COMMENT_STATUS_PASS . ' AND parent_id=0 AND blog_id=' . $blog_id)
        ->order('last_reply_time DESC')
        ->select();

        return $this->_getRecurrsiveComments($comments);
    }

    /**
     * 根据指定博客id获取上、下一篇博客标题及链接
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-21 15:26:00
     *
     * @param int $blog_id 当前博客id
     *
     * @return array 上、下一篇博客
     */
    private function _getNextAndPrevBlog($blog_id) {
        $field = 'blog_id,title,link_url';
        $where = 'is_delete=0 AND is_issue=1';

        return array(
            'next_blog' => $this->_model->field($field)->where($where . ' AND blog_id>' . $blog_id)->order('blog_id ASC')->find(),
            'prev_blog' => $this->_model->field($field)->where($where . ' AND blog_id<' . $blog_id)->order('blog_id DESC')->find(),
        );
    }

    /**
     * 根据指定博客id获取相关博客
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-24 11:34:18
     *
     * @param int $blog_id 当前博客id
     *
     * @return array 相关博客
     */
    private function _getRelativeBlog($blog_id, $tags) {
        $tags = $this->tags($tags, true);

        if (!$tags) {
            return array();
        }

        $t = '';

        foreach($tags as $v) {
            $t .= ",'" . addslashes($v) . "'";
        }

        $data = $this->_model
        ->alias('b')
        ->field('b.title,b.link_url')
        ->join('JOIN ' . TB_TAG . ' AS t ON b.blog_id=t.blog_id')
        ->where(array('t.tag' => array('IN', $tags), 't.blog_id' => array('NEQ', $blog_id), 'b.is_delete' => 0, 'b.is_issue' => 1))
        ->limit(5)
        ->order('b.blog_id DESC')
        ->select();

        return $data;
    }

    /**
     * 详请
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-21 15:26:00
     * @lastmodify      2013-04-23 14:32:00 by mrmsl
     *
     * @return void 无返回值
     */
    public function detailAction() {
        $blog_id = Filter::int('id', 'get');
        $date    = Filter::int('date', 'get');

        if (!$blog_id || !$date) {//非法参数
            $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . "date=({$date}),id=({$blog_id})");
            $this->_showMessage('error' . $blog_id . $date, null, 404);
        }

        if ($blog_info = $this->_model->find($blog_id)) {

            if (date('Ymd', $blog_info['add_time']) != $date) {//日期与id不匹配
                $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . "date=({$date}),id=({$blog_id})");
                $this->_showMessage('error' . $blog_id . ',' . $date, null, 404);
            }

            if (!$blog_info['is_issue'] || $blog_info['is_delete']) {//未发布或已删除
                $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . "is_delete=({$blog_info['is_delete']}),is_issue=({$blog_info['is_issue']})");
                $this->_showMessage('error' . $blog_info['is_issue'] . ',' . $blog_info['is_delete'], null, 404);
            }

            $filename = str_replace(BASE_SITE_URL, WWWROOT, $blog_info['link_url']);
            new_mkdir(dirname($filename));

            $o = $this->getViewTemplate('build_html')
            ->assign($this->_getNextAndPrevBlog($blog_id))//上下篇
            ->assign('blog_info', $blog_info)//博客内容
            ->assign(array(
                'web_title'         => $blog_info['title'] . TITLE_SEPARATOR . $this->nav($blog_info['cate_id'], 'cate_name', 'Category', TITLE_SEPARATOR) . TITLE_SEPARATOR . L('CN_WANGWEN'),
                'seo_keywords'      => $blog_info['seo_keyword'],
                'seo_description'   => $blog_info['seo_description'],
                'tags'              => $this->tags($blog_info['seo_keyword']),
                'relative_blog'     => $this->_getRelativeBlog($blog_id, $blog_info['seo_keyword']),
                'comments_html'     => $this->_getBlogComments($blog_id),
            ));

            $content = $o->fetch(CONTROLLER_NAME, 'detail', $blog_id);
            //file_put_contents($filename, $content);
            echo $content;

        }
        else {//博客不存在
            $this->_showMessage(L('BLOG,NOT_EXIST'), null, 404);
        }
    }//end detailAction
}