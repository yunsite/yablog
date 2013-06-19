<?php
/**
 * 语言包管理控制器类
 *
 * @file            Lang.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-03 17:34:29
 * @lastmodify      $Date$ $Author$
 */

class LangController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model = false;

    /**
     * 生成语言包js
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-04 08:35:38
     * @lastmodify      2013-01-27 14:14:23 by mrmsl
     *
     * @return void 无返回值
     */
    public function createAction() {
        require(CORE_PATH . 'functions/dir.php');
        create_dir(WEB_JS_LANG_PATH);

        $loop_arr = array(
            'admin' => LANG_PATH,
            str_replace('modules/admin/', 'modules/' . FRONT_MODULE_NAME . '/', LANG_PATH),
        );

        foreach($loop_arr as $key => $item) {

            $lang_arr = scand_dir($item);//语言包

            foreach ($lang_arr as $k => $v) {
                $lang     = is_file($filename = SYS_LANG_PATH . $k . '.php') ? include($filename) : array();

                foreach ($v as $file) {
                    $lang = array_merge($lang, array_change_key_case(include($file), CASE_UPPER));
                }

                array2js($lang, 'L', WEB_JS_LANG_PATH . (is_string($key) ? $key . '.' : '') . $k . '.js');
            }
        }
    }
}