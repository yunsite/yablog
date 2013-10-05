<?php
/**
 * 验证码控制器类
 *
 * @file            VerifycodeController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-27 17:25:34
 * @lastmodify      $Date$ $Author$
 */

class VerifycodeController extends BaseVerifycodeController {
    /**
     * @var bool $_auto_check_priv true自动检测权限。默认false
     */
    protected $_auto_check_priv = false;
}