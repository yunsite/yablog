<?php
/**
 * 管理员登陆控制器类
 *
 * @file            FieldController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
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
        $css_file   = css('Aqua/css/ligerui-all.css,Gray/css/all.css', COMMON_IMGCACHE . 'js/ligerui/skins/');//ligerui样式
        $js_file    = js('System.js,lang/' . MODULE_NAME . '.' . LANG . '.js', '/static/js/');//系统信息,语言包
        $js_file   .= js('System.sys_base_admin_entry = "' . WEB_ADMIN_ENTRY . '"', 'script');//后台入口

        if (IS_LOCAL) {
            $require_js = include(APP_PATH . 'include/required_js.php');
            $js_file .= js($require_js);

        }
        else {
            $js_file .= js('bootstrap.min.js', ADMIN_IMGCACHE . 'js/');
        }

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