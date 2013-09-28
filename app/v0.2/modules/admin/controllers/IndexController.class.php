<?php
/**
 * 后台首页控制器类
 *
 * @file            IndexController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-15 14:38:28
 * @lastmodify      $Date$ $Author$
 */

class IndexController extends CommonController {
    /**
     * @var bool $_auto_check_priv true自动检测权限。默认false
     */
    protected $_auto_check_priv = false;
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = false;

    /**
     * 管理中心。如果未登陆，跳转至登陆页
     *
     * @author          mrmsl
     * @date            2012-07-02 11:12:49
     * @lastmodify      2013-03-29 15:58:41 by mrmsl
     *
     * @return void 无返回值。如果未登陆跳转至登陆页
     */
    function indexAction() {

        if (!$admin_info = $this->_admin_info) {
            $this->_redirect(WEB_ADMIN_ENTRY . '/login', false);
            return false;
        }

        $admin_priv = strtolower(json_encode(array_values($this->_role_info['priv'])));
        $role_info  = $this->cache($admin_info['role_id'], 'Role');
        //css文件
        $css_file  = $this->_loadTimeScript('START_TIME');//,extjs/v4.1.1/resources/css/ext-patch.css
        $css_file .= css('extjs/v4.1.1a//resources/css/ext-all-gray.css,extjs/v4.1.1a/resources/css/ext-patch.css', COMMON_IMGCACHE);
        $css_file .= css('app.css', ADMIN_IMGCACHE . 'css/');
        $js_file   = $this->_loadTimeScript('LOAD_CSS_TIME');
        $js_file  .= js('', true, COMMON_IMGCACHE . 'extjs/v4.1.1a/');
        $js_file  .= $this->_loadTimeScript('LOAD_EXT_TIME');
        $js_file  .= js('System.js', false, '/static/js/');
        $js_file  .= js('System.sys_base_admin_entry = "' . WEB_ADMIN_ENTRY . '"', 'script');//后台入口
        $js_file  .= js(MODULE_NAME . '.' . LANG . '.js', false, '/static/js/lang/') .
        //ext语言包
        ('en' != LANG ? js('ext-lang-' . LANG . '.js', false, '/static/js/lang/') : '');

        if (APP_DEBUG) {
            $js_arr = include(APP_PATH . 'include/required_js.php');

            $js_file .= js($js_arr, false, ADMIN_IMGCACHE . 'app/');
        }
        else {
            $js_file .= js('app/pack/app.js', false);
        }

        //$js_file  .= js('editor_config.js', false, COMMON_IMGCACHE . 'ueditor/v1.2.5/');

        $js_file  .= $this->_loadTimeScript('LOAD_JS_TIME');

        include(TEMPLATE_FILE);
    }//end indexAction
}