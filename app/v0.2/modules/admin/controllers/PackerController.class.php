<?php
/**
 * 压缩js控制器类
 *
 * @file            PackerController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-15 16:22:58
 * @lastmodify      $Date$ $Author$
 */

/**
 * 压缩js路径
 */
define('PACKER_JS_PATH'     , IMGCACHE_PATH .  VERSION_PATH . 'admin/app/');

class PackerController extends CommonController {
    /**
     * @var array $_js_file 必须加载的js文件，自动加载APP_PATH . 'include/required_js.php'
     */
    private $_js_file = array();

    /**
     * 禁止路径
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-10 15:40:01
     * @lastmodify      2013-01-22 10:51:46 by mrmsl
     *
     * @param string $path 待检测路径
     *
     * @return void 无返回值
     */
    private function _denyDirectory($path) {

        if (false !== strpos($path, '..')) {
            $this->_model->addLog(L('LIST_DIRECTORY_FORBIDDEN') . PACKER_JS_PATH . $path, LOG_TYPE_INVALID_PARAM);
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            $this->_ajaxReturn(L('LIST_DIRECTORY_FORBIDDEN') . PACKER_JS_PATH . $path);
        }
    }

    /**
     * 获取文件列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-17 12:46:02
     * @lastmodify      2013-01-22 10:52:24 by mrmsl
     *
     * @param string $node 节点路径
     *
     * @return array 文件列表
     */
    private function _getFile($node) {
        $node = trim($node, '/');

        $this->_denyDirectory($node);

        $file_arr  = array();
        $directory = PACKER_JS_PATH . $node . '/';
        $k         = 0;

        if (is_dir($directory)) {
            $date_format = sys_config('sys_timezone_datetime_format');
            $d           = dir($directory);

            while ($f = $d->read()) {

                if ($f == '.' || $f == '..' || substr($f, 0, 1) == '.') {
                    continue;
                }

                $filename = $directory . '/' . $f;

                if (is_dir($filename)) {

                    $file_arr[$k] = array(
                        'text'    => $f,
                        'checked' => $f == 'pack' ? null : false,
                        'id'      => $node . '/' . $f,
                    );
                    $file_arr[$k]['data'] = $this->_getFile($f);
                    $k++;
                }
                elseif(substr($f, -3) == '.js' && !in_array($f, array('app.js'))) {
                    $desc = '';//js文件说明
                    $file = new SplFileObject($filename);

                    if (!strpos($filename, '.pack.')) {
                        $file->fgets();
                        $desc = trim(str_replace('*', '', $file->fgets()));//第二行为文件说明
                    }

                    $file_arr[] = array(
                        'text'     => $f,
                        'id'       => $node . '/' . $f,
                        'leaf'     => true,
                        'checked'  => $node == 'pack' ? null : false,
                        'filesize' => format_size($file->getSize()),
                        'filemtime'=> new_date($date_format, $file->getMTime()),
                        'desc'     => $desc,
                    );
                }

            }//end while

            $d->close();
        }//end if

        return $file_arr;
    }//end _getFile

    /**
     * 合并页面必要js文件至一个文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-10 15:27:06
     * @lastmodify      2013-01-22 10:52:40 by mrmsl
     *
     * @return void 无返回值
     */
    private function _merge() {
        $content = '';

        foreach ($this->_js_file as $filename) {
            $content .= is_file($filename = PACKER_JS_PATH . 'pack/' . basename($filename, '.js') . '.pack.js') ? file_get_contents($filename) : '';
        }

        file_put_contents(PACKER_JS_PATH . 'pack/app.js', $content);
    }

    /**
     * 压缩js文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-10 15:59:37
     * @lastmodify      2013-01-22 10:52:51 by mrmsl
     *
     * @param string $filename 文件名
     *
     * @return void 无返回值
     */
    private function _packFile($filename) {
        $packer = new JavascriptPacker(file_get_contents($filename));
        $packed = $packer->pack();
        file_put_contents(PACKER_JS_PATH . 'pack/' . basename($filename, '.js') . '.pack.js', $packed);

        unset($packer, $packed);
    }

    /**
     * 压缩文件列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-10 09:19:35
     * @lastmodify      2013-01-22 10:53:47 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $this->_ajaxReturn(true, '', $this->_getFile(Filter::string('node', 'get')));
    }

    /**
     * 压缩
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-06-15 16:35:42
     * @lastmodify      2013-01-22 10:53:56 by mrmsl
     *
     * @return void 无返回值
     */
    public function packAction() {
        $this->_js_file = include(APP_PATH . 'include/required_js.php');

        $file = Filter::string('file');

        !$file && $this->_ajaxReturn(false, L('FILENAME,IS_EMPTY'));

        if ($file == 'all') {

            require(CORE_PATH . 'functions/dir.php');

            $file = list_dir(PACKER_JS_PATH);

            foreach ($file as $v) {
                is_file($v) && '.js' == substr($v, -3) && strpos($v, '.pack.js') === false && $this->_packFile($v);
            }

            $this->_merge();
        }
        else {
            $this->_denyDirectory($file);
            $file  = explode(',', $file);
            $merge = false;

            foreach ($file as $v) {

                if (!is_file($filename = PACKER_JS_PATH . $v) || strpos($v, '.pack.') || substr($v, -3) != '.js') {
                    continue;
                }

                if (!$merge && in_array($v, $this->_js_file)) {
                    $merge = true;
                }

                $this->_packFile($filename);
            }

            $merge && $this->_merge();
        }

        $this->_model->addLog(L('COMPRESS,%js,FILENAME,%:') . join(',', $file), LOG_TYPE_ADMIN_OPERATE);
        $this->_ajaxReturn(true, L('COMPRESS,SUCCESS'));
    }//end packAction
}