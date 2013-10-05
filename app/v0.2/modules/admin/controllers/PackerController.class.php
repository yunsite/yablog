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
define('PACKER_JS_PATH'     , IMGCACHE_PATH .  VERSION_PATH);

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
     *
     * @param string $path 待检测路径
     *
     * @return void 无返回值
     */
    private function _denyDirectory($path) {

        if (false !== strpos($path, '..')) {
            $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . L('LIST_DIRECTORY_FORBIDDEN') . PACKER_JS_PATH . $path;
            trigger_error($log, E_USER_ERROR);
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            $this->_ajaxReturn(true, L('LIST_DIRECTORY_FORBIDDEN') . PACKER_JS_PATH . $path);
        }
    }

    /**
     * 获取文件列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-17 12:46:02
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

                    if (!strpos($filename, '.min.')) {
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
     *
     * @return void 无返回值
     */
    private function _merge() {
        $path       = str_replace(COMMON_IMGCACHE, basename(COMMON_IMGCACHE), PACKER_JS_PATH);
        $content    = '';

        foreach ($this->_js_file as $filename => $path) {
            $file       = str_replace('http://imgcache.yablog.cn/', IMGCACHE_PATH, $path) . substr_replace($filename, '.min.js', -3);
            $content   .= '//' . $path . $filename . PHP_EOL;
            $content   .= is_file($file) ? file_get_contents($file) : '';
            $content   .= PHP_EOL . PHP_EOL;
        }

        file_put_contents(PACKER_JS_PATH . 'admin/js/core/all.min.js', $content);
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
        $packer     = new JavascriptPacker(file_get_contents($filename));
        $packed     = $packer->pack();
        file_put_contents(substr_replace($filename, '.min.js', -3), $packed);

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
                is_file($v) && '.js' == substr($v, -3) && false === strpos($v, '.min.js') && $this->_packFile($v);
            }

            $this->_merge();
        }
        else {
            $this->_denyDirectory($file);
            $file  = explode(',', $file);
            $merge = false;

            foreach ($file as $v) {

                if (!is_file($filename = PACKER_JS_PATH . $v) || strpos($v, '.min.') || '.js' != substr($v, -3)) {
                    continue;
                }

                $basename = basename($v);

                if (!$merge && (isset($this->_js_file[$basename]) || 'base.js' == $basename)) {
                    $merge = true;
                }

                $this->_packFile($filename);
            }

            $merge && $this->_merge();
        }

        $this->_model->addLog(L('COMPRESS,%js,FILENAME,%:') . join(',', $file));
        $this->_ajaxReturn(true, L('COMPRESS,SUCCESS'));
    }//end packAction

    /**
     * ligerui css 采用@import,节省http请求,合并ligerui css至一个文件加载
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-05 10:15:23
     *
     * @return void 无返回值
     */
    public function mergeLigerCssAction() {
        require(CORE_PATH . 'functions/dir.php');
        $skin_arr   = array('Aqua' => 'ligerui-all.css', 'Gray' => 'all.css' ,'Silvery' => 'style.css');//css目录
        $path       = IMGCACHE_PATH . 'common/js/ligerui/skins/';

        foreach($skin_arr as $skin => $css_file) {
            $skin_path  = $path . $skin . '/css/';

            if ('Aqua' == $skin) {
                $content = '';
            }
            else {//将Aqua/aqua-all.css合并至当前css中
                $content  = '/*Aqua/css/aqua-all.css*/' . PHP_EOL;
                $content .= file_get_contents($path . 'Aqua/css/aqua-all.css') . PHP_EOL . PHP_EOL;
            }

            /*
            @import url("ligerui-common.css");
            @import url("ligerui-dialog.css");
            @import url("ligerui-form.css");
            @import url("ligerui-grid.css");
            @import url("ligerui-layout.css");
            @import url("ligerui-menu.css");
            @import url("ligerui-tab.css");
            @import url("ligerui-tree.css");
             */
            $css = file_get_contents($skin_path . $css_file);
            preg_match_all('/[a-z-]+\.css/', $css, $matches);

            foreach ($matches[0] as $file) {
                $content .= "/*{$skin}/{$file}*/" . PHP_EOL;
                $content .= file_get_contents($skin_path . $file) . PHP_EOL . PHP_EOL;
            }

            file_put_contents($skin_path . strtolower($skin) . '-all.css', $content);

        }

        $this->_model->addLog(L('MERGE,CSS_STYLE'));
        $this->_ajaxReturn(true, L('MERGE,SUCCESS'));
    }//end mergeLigerCssAction
}