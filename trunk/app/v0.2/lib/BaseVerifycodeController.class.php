<?php
/**
 * 验证码控制器类
 *
 * @file            BaseVerifycodeController.class.php
 * @package         Yab\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-27 17:25:34
 * @lastmodify      $Date$ $Author$
 */

class BaseVerifycodeController extends CommonController {
    /**
     * @var bool $_auto_check_priv true自动检测权限。默认false
     */
    protected $_auto_check_priv = false;
    /**
     * @var bool $_verifycode_module 对应模块
     */
    private $_verifycode_module = array(
        'sys',//系统默认
        'module_admin',//管理员模块，包括后台登陆及修改密码
        'module_guestbook',//留言
        'module_comments',//评论
    );

    /**
     * 入口
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-27 17:26:23
     * @lastmodify      2013-01-22 11:02:32 by mrmsl
     *
     * @return void 无返回值
     */
    public function indexAction() {
        $module = Filter::get('module', 'get');//模块
        $error  = '';

        if (!APP_DEBUG) {//非调试模式

            if (!REFERER_PAGER) {
                $error = L('REFERER_PAGER,IS_EMPTY');
            }

            elseif (strpos(REFERER_PAGER, WEB_SITE_URL) === false) {
                $error = L('REFERER_PAGER') . '(' . REFERER_PAGER . ')' . L('IS_EMPTY');
            }
        }

        if (!$error) {

            if (!$module) {
                $error = 'module' . L('IS_EMPTY');
            }
            elseif (!in_array($module, $this->_verifycode_module)) {
                $error = 'module not in (' . join(',', $this->_verifycode_module) . ')';
            }
            else {
                $verifycode_setting  = get_verifycode_setting($module);//验证码设置

                //未开启验证码
                if (!$verifycode_setting['enable']) {
                    $default_setting     = get_verifycode_setting('sys', 'enable');//默认设置
                    $error = L('NOT_HAS,TURN_ON') . ("(module:{$verifycode_setting['enable']}|sys:{$default_setting})");
                }
            }
        }

        if ($error) {//有错误
            $log    = get_method_line(__METHOD__, __LINE__, LOG_VERIFYCODE_ERROR) . L('VERIFY_CODE') . "({$module})" . $error;
            trigger_error($log);
            $exit = true;
        }
        elseif (!check_verifycode_limit($module, 'refresh')) {//刷新次数限制
            $exit = true;
        }

        if (!empty($exit)) {
            header('Content-type: image/png');
            readfile(IMGCACHE_PATH . 'common/images/verifycode_error.png');
            exit();
        }

        $width  = $verifycode_setting['width'];//宽
        $height = $verifycode_setting['height'];//高
        $length = $verifycode_setting['length'];//字母长
        $type   = $verifycode_setting['type'];//类型
        $img    = new Verifycode();

        $img->buildVerifyImage($verifycode_setting['length'], $verifycode_setting['type'], $verifycode_setting['width'], $verifycode_setting['height']);
    }//end indexAction
}