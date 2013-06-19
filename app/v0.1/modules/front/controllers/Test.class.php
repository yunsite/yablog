<?php
/**
 * 测试控制器类
 *
 * @file            Test.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-04 10:03:10
 * @lastmodify      $Date$ $Author$
 */

!IS_LOCAL && exit('Access Denied');

class TestController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = true;

    /**
     * 添加留言
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-04 10:03:53
     *
     * @return void 无返回值
     */
    public function addGuestbookAction() {
        $rand_content = $this->_model->table(TB_COMMENTS)
        ->field('0 AS `parent_id`,0 AS `type`, 0 AS `blog_id`,username,"http://www.abc.com/path\"/?querystring => https://www.abc.com/path/?querystring" AS content,user_homepage')->order('RAND()')->limit(1)->select();
        $this->getViewTemplate()
        ->assign('guestbook', $rand_content);
        $this->_display(CONTROLLER_NAME, 'index');
    }

    /**
     * 顶操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-08 14:50:17
     *
     * @return void 无返回值
     */
    public function diggAction() {
        $rand_content = $this->_model->table(TB_BLOG)
        ->field(array('CONCAT("blog,",blog_id,",",add_time)' => 'diggs'))->where('blog_id=687')->find();
        //order('RAND()')->find();
        $this->getViewTemplate()
        ->assign('digg', $rand_content);
        $this->_display(CONTROLLER_NAME, 'index');
    }
}