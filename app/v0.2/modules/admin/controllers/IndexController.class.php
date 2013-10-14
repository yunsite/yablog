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
     * 自动登陆,本地环境下有效
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-14 09:29:18
     *
     * @return void 无返回值
     */
    function autoLoginAction() {

        if (IS_LOCAL) {
            $this->setAdminSession($this->cache(1, 'Admin'));
        }
        else {
            throw new Exception(L('_TRY_AUTO_LOGIN'));
        }
    }//end indexAction

    /**
     * 管理中心。如果未登陆，跳转至登陆页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-02 11:12:49
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
        require(APP_PATH . 'include/include_template.php');
    }//end indexAction
}