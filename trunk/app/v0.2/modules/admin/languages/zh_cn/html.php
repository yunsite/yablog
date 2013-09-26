<?php
/**
 * 生成静态页管理模块语言中文包
 *
 * @file            ssi.php
 * @package         Yab\Module\Admin\Language
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-18 09:23:41
 * @lastmodify      $Date$ $Author$
 */

return array(
    'CONTROLLER_NAME_HTML'  => L('STATIC_PAGE'),
    'HTML_NAME'         => L('STATIC_PAGE,FILENAME'),
    'TPL_NAME'          => '模板名',
    'TPL_NAME_TIP'      => '相对前台模板路径，格式：<span style="font-weight: bold">目录</span>/<span style="font-weight: bold">模板名</span>',

    'PAGE_NOT_FOUND'            => '404错误页面',
    'PAGE_NOT_FOUND_CONTENT'    => '<h4>抱歉，您访问的页面不存在或已经被删除。</h4><br /><strong>您可以：</strong><ul><li>检查您输入的网址拼写是否正确</li><li>进入<a href="' . BASE_SITE_URL . '">首页</a></li><li>返回<a href="javascript: history.back();" rel="nofollow">上一页</a></li></ul>',
    'SYSTEM_INFORMATION_CONTENT'=> '<h4 id="h4-info">您的{0}已经成功，感谢您的参与。</h4><br /><strong>您可以：</strong><ul><li>进入<a href="' . BASE_SITE_URL . '">首页</a></li><li>返回<a href="javascript: history.back();" rel="nofollow">上一页</a></li></ul>',
);