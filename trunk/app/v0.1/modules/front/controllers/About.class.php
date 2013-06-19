<?php
/**
 * 关于控制器类
 *
 * @file            About.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-02-25 09:00:48
 * @lastmodify      $Date$ $Author$
 */

class AboutController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = false;

    /**
     * 关于
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-25 09:01:45
     *
     * @return void 无返回值
     */
    public function indexAction() {
    }
    public function pagenotfoundAction() {
        var_dump('Page Not Found');
        return false;
    }

    /**
     * 联系
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-02-25 09:02:13
     *
     * @return void 无返回值
     */
    public function contactAction() {
    }
}