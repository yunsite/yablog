<?php
/**
 * 前台首页控制器类
 *
 * @file            Index.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-15 14:38:28
 * @lastmodify      $Date$ $Author$
 */

class IndexController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认true
     */
    protected $_init_model = true;

    /**
     * 获取首页博客
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-26 22:57:37
     *
     * @return array 博客数组
     */
    private function _getBlogs() {
        $blog_arr   = $this->_model
        ->table(TB_BLOG)
        ->alias('b')
        ->join(' JOIN ' . TB_CATEGORY . ' AS c ON b.cate_id=c.cate_id')
        //->where($where)
        ->order('b.blog_id DESC')
        ->limit(10)
        ->field('b.blog_id,b.title,b.link_url,b.add_time,b.summary,c.cate_name,c.link_url AS cate_url')
        ->select();

        return $blog_arr;
    }

    /**
     * 获取最新一条微博信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-26 21:21:44
     *
     * @return array 微博信息
     */
    private function _getLatesttMiniblog() {
        $miniblog = $this->_model
        ->table(TB_MINIBLOG)
        ->field('blog_id,link_url,add_time,content')
        ->order('blog_id DESC')
        ->find();

        return $miniblog;
    }

    /**
     * 首页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 21:39:23
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $blog_arr = $this->_getBlogs();
        //$miniblog = $this->_getLatesttMiniblog();
        $this->getViewTemplate()
        //->assign('miniblog', $miniblog)
        ->assign('blog_arr', $blog_arr)
        ->assign('web_title', sys_config('sys_base_web_index_title'));
        $this->_display();
    }//end indexAction

}