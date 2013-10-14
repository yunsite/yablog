<?php
/**
 * 博客模型
 *
 * @file            BlogModel.class.php
 * @package         Yab\Module\Front\Model
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-17 11:01:55
 * @lastmodify      $Date$ $Author$
 */

class BlogModel extends CommonModel {
    /**
     * @var string $_pk_field 数据表主键字段名称。默认blog_id
     */
    protected $_pk_field        = 'blog_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_BLOG
     */
    protected $_true_table_name = TB_BLOG;
}