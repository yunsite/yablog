<?php
/**
 * 标签控制器类
 *
 * @file            Tag.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-02-25 09:54:41
 * @lastmodify      $Date$ $Author$
 */

class TagController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = true;

    /**
     * 首页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-28 17:14:18
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $page_size  = 60;
        $total      = $this->_model
        ->table(TB_TAG)
        //->alias('b')
        //->join(' JOIN ' . TB_TAG . ' AS t ON b.blog_id=t.blog_id')
        //->where($where)
        ->count('DISTINCT tag');
        $page_info      = Filter::page($total, 'page', $page_size);
        $page           = $page_info['page'];
        $page_one       = $page < 2;
        $tag_arr        = $this->_model
        ->table(TB_TAG)
        //->alias('b')
        //->join(' JOIN ' . TB_TAG . ' AS t ON b.blog_id=t.blog_id')
        ->order('searches DESC')
        ->field('DISTINCT `tag`')
        ->limit($page_info['limit'])
        ->select();

        $paging = new Paging(array(
            '_url_tpl'      => BASE_SITE_URL . 'tag/page/\\1.shtml',
            '_total_page'   => $page_info['total_page'],
            '_now_page'     => $page,
            '_page_size'    => $page_size,
        ));

        $o = $this->getViewTemplate()
        ->assign(array(
            'web_title'     => L('TAG'),
            'tag_arr'       => $tag_arr,
            'paging'        => $paging->getHtml(),
            'page'          => $page_one ? '' : $page,
        ));

        $this->_display(null, null, $page);
    }//end indexAction
}