<?php
/**
 * 底层通用模型
 *
 * @file            Common.class.php
 * @package         Yab\Module\Home\Model
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-26 12:43:14
 * @lastmodify      $Date$ $Author$
 */

class CommonModel extends BaseModel {
    /**
     * @var bool $_patch_validate true批处理验证。默认false
     */
    protected $_patch_validate = false;
}