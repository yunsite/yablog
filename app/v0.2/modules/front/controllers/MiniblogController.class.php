<?php
/**
 * 微博控制器类
 *
 * @file            MiniblogController.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-02-21 13:30:42
 * @lastmodify      $Date$ $Author$
 */

class MiniblogController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认true
     */
    protected $_init_model = true;

    /**
     * 根据指定微博id获取评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-28 15:16:54
     *
     * @param int $blog_id 当前微博id
     *
     * @return string 评论html
     */
    private function _getBlogComments($blog_id) {
        $comments = $this->_model
        ->table(TB_COMMENTS)
        ->where('type=' . COMMENT_TYPE_MINIBLOG . ' AND status=' . COMMENT_STATUS_PASS . ' AND parent_id=0 AND blog_id=' . $blog_id)
        ->order('last_reply_time DESC')
        ->select();

        return $this->_getRecurrsiveComments($comments);
    }

    /**
     * 首页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-21 13:30:55
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $total      = $this->_model
        ->count();
        $page_info  = Filter::page($total, 'page', PAGE_SIZE);
        $page       = $page_info['page'];
        $page_one   = $page < 2;
        $blog_arr   = $this->_model
        ->order('blog_id DESC')
        ->limit($page_info['limit'])
        ->select();

        $paging = new Paging(array(
            '_url_tpl'      => BASE_SITE_URL . 'miniblog/page/\\1.shtml',
            '_total_page'   => $page_info['total_page'],
            '_now_page'     => $page,
            '_page_size'    => PAGE_SIZE,
        ));

        $o = $this->getViewTemplate($page_one ? 'build_html' : null)
        ->assign(array(
            'web_title' => L('MINIBLOG'),
            'blog_arr'  => $blog_arr,
            'paging'    => $paging->getHtml(),
            'page'      => $page_one ? '' : $page,
        ));
        $content = $o->fetch(CONTROLLER_NAME, ACTION_NAME, $page);

        if ($page_one) {
            $filename =  WWWROOT . 'miniblog.shtml';
            //file_put_contents($filename, $content);
        }

        echo $content;
    }

    /**
     * 详请
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-21 13:47:40
     * @lastmodify      2013-04-26 23:13:01
     *
     * @return void 无返回值
     */
    public function detailAction() {
        $blog_id = Filter::int('id', 'get');
        $date    = Filter::int('date', 'get');

        if (!$blog_id || !$date) {//非法参数
            $log = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . "date=({$date}),id=({$blog_id})";
            trigger_error($log);
            $this->_showMessage('error' . $blog_id . $date, null, 404);
        }

        if ($blog_info = $this->_model->find($blog_id)) {

            if (date('Ymd', $blog_info['add_time']) != $date) {//日期与id不匹配
                $log = get_method_line(__METHOD__, __LINE__, LOG_INVALID_PARAM) . "date=({$date}),id=({$blog_id})";
                trigger_error($log);
                $this->_showMessage('error' . $blog_id . ',' . $date, null, 404);
            }

            $filename = str_replace(BASE_SITE_URL, WWWROOT, $blog_info['link_url']);
            new_mkdir(dirname($filename));

            $o = $this->getViewTemplate('build_html')
            ->assign('blog_info', $blog_info)//微博内容
            ->assign(array(
                'web_title'         => L('MINIBLOG,DETAIL') . TITLE_SEPARATOR . L('MINIBLOG'),
                'comments_html'     => $this->_getBlogComments($blog_id),
                //'seo_keywords'      => $blog_info['seo_keyword'],
                //'seo_description'   => $blog_info['seo_description'],
                //'tags'              => $this->tags($blog_info['seo_keyword']),
                //'relative_blog'     => $this->_getRelativeBlog($blog_id, $blog_info['seo_keyword']),
            ));

            $content = $o->fetch(CONTROLLER_NAME, 'detail', $blog_id);
            //file_put_contents($filename, $content);
            echo $content;

        }
        else {//微博不存在
            $this->_showMessage(L('MINIBLOG,NOT_EXIST'), null, 404);
        }
    }
}