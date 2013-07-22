<?php
/**
 * 博客分类控制器类
 *
 * @file            Blog.class.php
 * @package         Yab\Module\Front\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-18 11:50:33
 * @lastmodify      $Date$ $Author$
 */

class CategoryController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = true;

    /**
     * 渲染博客列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-28 16:55:45
     *
     * @param string|array $cate_info 数组为分类，否则为标签
     *
     * @return void 无返回值
     */
    private function _fetchBlog($cate_info = null) {

        if (is_array($cate_info)) {//分类
            $is_tag     = false;
            $cate_id    = $cate_info['cate_id'];
            $table      = TB_BLOG;
            $where      = array('b.is_delete' => 0, 'b.is_issue' => 1);

            if ($cate_id) {//category.shtml
                $where['b.cate_id'] = array('IN', $this->_getChildrenIds($cate_id));
            }

            $url_tpl    = str_replace('.shtml', '/page/\\1.shtml', $cate_info['link_url']);
            $cache_flag = $cate_id;
            $total      = $this->_model
            ->table(TB_BLOG)
            ->alias('b')
            ->where($where)
            ->count();
            $this->_model->alias('b');//b.title
        }
        else {//标签
            $this->_model->table(TB_TAG)->where(array('tag' => $cate_info))->setInc('searches');//搜索次数+1
            $is_tag     = true;
            $table      = TB_BLOG . ' AS b JOIN ' . TB_TAG . ' AS t ON t.blog_id=b.blog_id';
            $where      = array('t.tag' => array('IN', $cate_info), 'b.is_delete' => 0, 'b.is_issue' => 1);
            $url_tpl    = BASE_SITE_URL . 'tag/' . urlencode($cate_info) . '/page/\\1.shtml';
            $cache_flag = md5(strtolower($cate_info));
            $total      = $this->_model
            ->table(TB_BLOG)
            ->alias('b')
            ->join(' JOIN ' . TB_TAG . ' AS t ON b.blog_id=t.blog_id')
            ->where($where)
            ->count('DISTINCT b.blog_id');
        }

        $page_info  = Filter::page($total, 'page', PAGE_SIZE);
        $page       = $page_info['page'];
        $page_one   = $page < 2;
        $blog_arr   = $this->_model
        ->table($table)
        ->where($where)
        ->order('b.blog_id DESC')
        ->limit($page_info['limit'])
        ->field('b.blog_id,b.title,b.link_url,b.cate_id,b.add_time,b.summary,b.seo_keyword,b.seo_description')
        ->select();

        $paging = new Paging(array(
            '_url_tpl'      => $url_tpl,
            '_total_page'   => $page_info['total_page'],
            '_now_page'     => $page,
            '_page_size'    => PAGE_SIZE,
        ));

        $o = $this->getViewTemplate($page_one && !$is_tag ? 'build_html' : null)
        ->assign(array(
            'blog_arr'  => $blog_arr,
            'paging'    => $paging->getHtml(),
            'page'      => $page_one ? '' : $page,
        ));

        if ($is_tag) {//标签
            $o->assign(array(
                'web_title'     => $cate_info . TITLE_SEPARATOR . L('TAG'),
                'tag'           => $cate_info,
                'seo_keywords'  => $cate_info,
            ));
        }
        else {//分类
            $o->assign(array(
                'web_title' => $cate_id ? $this->nav($cate_id, 'cate_name', null, TITLE_SEPARATOR) : $cate_info['cate_name'],
                'cate_info' => $cate_info,
                'tag'       => '',
            ));
        }

        $content = $o->fetch(CONTROLLER_NAME, ACTION_NAME, $cache_flag . '-' . $page);

        if ($page_one && !$is_tag) {
            $filename = str_replace(BASE_SITE_URL, WWWROOT, $cate_info['link_url']);
            new_mkdir(dirname($filename));
            //file_put_contents($filename, $content);
        }

        echo $content;
    }//end _fetchBlog

    /**
     * 博客列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-18 11:50:55
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $cate_name = Filter::string('name', 'get');

        if ('tag' == Filter::get('flag', 'get')) {//标签
            $this->_fetchBlog($cate_name);
            return;
        }

        $cate_arr  = $this->_getCache();

        if (!$cate_arr) {
            $this->_showMessage('no arr', null, 404);
        }

        if ('' === $cate_name) {//category.shtml
            $this->_fetchBlog(array('cate_id' => 0, 'cate_name' => L('CN_WANGWEN'), 'link_url' => BASE_SITE_URL . 'category' . C('HTML_SUFFIX')));
            return;
        }

        foreach($cate_arr as $v) {

            if ($v['en_name'] == $cate_name) {
                $cate_info = $v;
                break;
            }
        }

        if (!isset($cate_info)) {
            $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . $cate_name . ' ' . L('NOT_EXIST'));
            $this->_showMessage($cate_name . ' ' . L('NOT_EXIST'), null, 404);
        }

        $this->_fetchBlog($cate_info);
    }//end indexAction
}