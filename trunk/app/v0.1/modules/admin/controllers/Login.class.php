<?php
/**
 * 管理员登陆控制器类
 *
 * @file            Field.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-25 15:57:05
 * @lastmodify      $Date$ $Author$
 */

class LoginController extends CommonController {
    /**
     * @var bool $_auto_check_priv true自动检测权限。默认false
     */
    protected $_auto_check_priv = false;

	   /**
     * 管理员登陆
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-05 10:44:49
     * @lastmodify      2013-01-22 10:42:05 by mrmsl
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $css_file  = $this->_loadTimeScript('START_TIME');
        $css_file .= css('extjs/v4.1.1a//resources/css/ext-all-gray.css,extjs/v4.1.1a/resources/css/ext-patch.css', COMMON_IMGCACHE);
        $css_file .= css('app.css', ADMIN_IMGCACHE . 'css/');
        $js_file   = $this->_loadTimeScript('LOAD_CSS_TIME');
        $js_file  .= js('', true, COMMON_IMGCACHE . 'extjs/v4.1.1a/');
        $js_file  .= $this->_loadTimeScript('LOAD_EXT_TIME');
        $js_file  .= js('System.js,lang/' . MODULE_NAME . '.' . LANG . '.js' . ('en' != LANG ? ',lang/ext-lang-' . LANG . '.js' : ''), false, '/static/js/');
        $js_file  .= js('System.sys_base_admin_entry = "' . WEB_ADMIN_ENTRY . '"', 'script');//后台入口

        if (IS_LOCAL) {
            $js_file .= js('util/common.js,util/Yab.Field.js,ux/Yab.ux.Form.js,controller/Yab.controller.Base.js,controller/Yab.controller.Login.js', false, ADMIN_IMGCACHE . 'app/');
        }
        else {
            $js_file .= js('common.pack.js,Yab.controller.Base.pack.js,Yab.controller.Login.pack.js', false, ADMIN_IMGCACHE . 'app/pack/');
        }

        $js_file  .= $this->_loadTimeScript('LOAD_JS_TIME');

        require(TEMPLATE_FILE);
    }

    /**
     * 登陆操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-02 11:16:12
     * @lastmodify      2013-01-22 10:42:15 by mrmsl
     *
     * @return void 无返回值
     */
    public function loginAction() {
        $result = $this->_model->checkCreate();

        if ($result === true) {
            $this->_admin_info = true;//设为true，以记录css及js加载时间记录 by mrmsl on 2012-09-11 08:18:22
            $this->logLoadTimeAction()->_ajaxReturn(true);
        }
        else {
            $this->_ajaxReturn(false, $result);
        }
    }

    /**
     * 退出登陆
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-30 09:21:20
     * @lastmodify      2013-01-22 10:42:23 by mrmsl
     *
     * @return void 无返回值
     */
    public function logoutAction() {
        session(SESSION_ADMIN_KEY, null);
        $this->_redirect(WEB_ADMIN_ENTRY . '/login', '');
    }

    /**
     * 验证码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-02 11:14:30
     * @lastmodify      2013-01-22 10:42:32 by mrmsl
     *
     * @return void 无返回值
     */
    public function verifyCodeAction() {
        $img = new Image_Verifycode();
        $img->buildVerifyImage();
    }
}