<?php
/**
 * 加载模板
 *
 * @file            include_template.php
 * @package         Yab\Admin
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-05 15:51:19
 * @lastmodify      $Date$ $Author$
 */

//css文件
$css_file   = css('Aqua/css/aqua-all.css,Gray/css/gray-all.css', COMMON_IMGCACHE . 'js/ligerui/skins/');//ligerui样式

//js文件
$js_file    = js('System.js,lang/' . MODULE_NAME . '.' . LANG . '.js', '/static/js/');//系统信息,语言包
$js_file   .= js('System.sys_base_admin_entry = "' . WEB_ADMIN_ENTRY . '";System.module_admin_verifycode_enable = ' . get_verifycode_setting('module_admin', 'enable') . ';var VERIFY_CODE_KEY = "' . SESSION_VERIFY_CODE . '"', 'script');//后台入口

if (APP_DEBUG) {
    $require_js = include(APP_PATH . 'include/required_js.php');
    $js_file .= js($require_js);

}
else {
    $js_file .= js('all.min.js', ADMIN_IMGCACHE . 'js/core/');
}

require(TEMPLATE_FILE);