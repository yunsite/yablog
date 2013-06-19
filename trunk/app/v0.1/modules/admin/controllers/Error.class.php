<?php
/**
 * 错误控制器类
 *
 * @file            Error.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-14 22:04:56
 * @lastmodify      $Date$ $Author$
 */

class ErrorController extends Yaf_Controller_Abstract {
    /**
     * 错误处理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 10:13:05
     *
     * @param object $exception \Exception异常
     *
     * @return bool false
     */
    public function errorAction($exception) {
        var_dump($exception->getMessage());
        return false;
    }
}