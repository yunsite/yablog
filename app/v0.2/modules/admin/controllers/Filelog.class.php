<?php
/**
 * 文件日志控制器类
 *
 * @file            Filelog.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-27 15:11:31
 * @lastmodify      $Date$ $Author$
 */

class FilelogController extends CommonController {
    /**
     * 按时间倒序文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-27 16:30:54
     *
     * @param array $a 文件a
     * @param array $b 文件b
     *
     * @return int -1,0,1
     */
    private function _cmp($a, $b) {
        static $sort    = null;
        static $order   = null;
        static $array   = null;

        if (null === $sort) {
            $sort   = Filter::string('sort', 'get', 'time');//排序字段
            $sort   = in_array($sort, array('time', 'size', 'filename')) ? $sort : 'time';
            $order  = empty($_GET['dir']) ? Filter::string('order', 'get') : Filter::string('dir', 'get');//排序
            $order  = toggle_order($order);
            $array  = 'ASC' == $order ? array(1, -1) : array(-1, 1);
        }

        if (isset($a['is_file']) && isset($b['is_file'])) {//都是文件
            return $a[$sort] > $b[$sort] ? $array[0] : $array[1];
        }
        elseif (isset($a['is_file']) && !isset($b['is_file'])) {//$a文件,$b文件夹
            return $array[0];
        }
        elseif (!isset($a[$sort]) && isset($b[$sort])) {//$a文件夹,$b文件
            return $array[1];
        }

        return 0;
    }

    /**
     * 禁止路径
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-27 15:14:08
     *
     * @param string $path 待检测路径
     *
     * @return void 无返回值
     */
    private function _denyDirectory($path) {

        if (false !== strpos($path, '..')) {
            $this->_model->addLog(L('LIST_DIRECTORY_FORBIDDEN') . LOG_PATH . $path, LOG_TYPE_INVALID_PARAM);
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            $this->_ajaxReturn(L('LIST_DIRECTORY_FORBIDDEN') . $path);
        }
    }

    /**
     * 关键字查询文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-28 09:38:10
     *
     * @param string $log_path  日志路径
     * @param string $keyword   关键字
     *
     * @return array 匹配关键字文件(夹)列表
     */
    private function _keywordQuery($log_path, $keyword) {
        $column     = Filter::string('column', 'get');
        $column     = in_array($column, array('filename', 'content')) ? $column : 'filename';
        $match_mode = Filter::string('match_mode', 'get');
        $mode_arr   = array('eq' => '/^%s$/i', 'leq' => '/^%s*/i', 'req' => '/%s$/i', 'like' => '/%s/i');
        $match_mode = isset($mode_arr[$match_mode]) ? $match_mode : 'eq';
        $pattern    = sprintf($mode_arr[$match_mode], preg_quote($keyword, '/'));
        $file_arr   = list_dir($log_path, true);//文件列表

        //空间换时间
        if ('filename' == $column) {//文件名

            foreach($file_arr as $k => $v) {

                if (!preg_match($pattern, basename($v))) {
                    unset($file_arr[$k]);
                }
            }
        }
        else {

            foreach($file_arr as $k => $v) {

                if (!is_file($v) || !preg_match($pattern, file_get_contents($v))) {
                    unset($file_arr[$k]);
                }
            }
        }

        return $file_arr;
    }//end _keywordQuery

    /**
     * {@inheritDoc}
     */
    protected function init() {
        require(CORE_PATH . 'functions/dir.php');

        parent::init();
    }

    /**
     * combo store数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-27 17:45:25
     *
     * @return void 无返回值
     */
    public function comboAction() {
        $dir_arr = dir_tree(LOG_PATH);

        foreach($dir_arr as $k => $item) {
            $dir_arr[$k] = array('filename' => str_replace(LOG_PATH, '', $item['dir']));
        }

        array_unshift($dir_arr, array('filename' => DS));
        $this->_ajaxReturn(true, '', $dir_arr);
    }

    /**
     * 删除
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-27 17:45:25
     *
     * @return void 无返回值
     */
    public function deleteAction() {
        $file   = Filter::string('filename');

        if (!$file) {
            $this->_model->addLog(L('DELETE,LOG,FILE,FAILURE') . '<br />' . L("INVALID_PARAM,%: file,IS_EMPTY"), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('DELETE,FAILURE'));
        }

        $file_arr = explode(',', $file);
        $log      = '';
        $error    = '';

        foreach ($file_arr as $v) {
            $filename = trim($v, '/');

            if ($filename && false === strpos($filename, '..')) {
                $filename = LOG_PATH . $filename;

                if (is_file($filename) && unlink($filename) || is_dir($filename) && delete_dir($filename)) {
                    $log .= ',' . $filename;
                }
                else {
                    $error .= ',' . $filename . L('NOT_EXIST');
                }
            }
            else {
                $error .= ',' . $v;
            }
        }

        $error && $this->_model->addLog(L('DELETE,INVALID,LOG,FILE') . $error, LOG_TYPE_INVALID_PARAM);//删除非法文件

        if ($log) {
            $this->_model->addLog(L('DELETE,LOG,FILE') . $log . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);//管理员操作日志
            $this->_ajaxReturn(true, L('DELETE,SUCCESS'));
        }
        else {
            $this->_ajaxReturn(false, L('DELETE,FAILURE'));
        }
    }//end deleteAction

    /**
     * 获取文件列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-27 15:14:17
     *
     * @param string $node 节点路径
     *
     * @return array 文件列表
     */
    public function listAction() {
        $path = Filter::string('path', 'get');//路径

        if ($path) {
            $this->_denyDirectory($path);
            $path = trim($path, '/');
        }
        else {
            $path = new_date('Y/md/');
        }

        $log_path   = LOG_PATH . $path;

        if (!is_dir($log_path)) {//路径不存在
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            $this->_model->addLog(L('path') . LOG_PATH . $path . L('NOT_EXIST'), LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false, L('path') . $path . L('NOT_EXIST'));
        }

        if ($keyword = Filter::string('keyword', 'get')) {//关键字查询
            $file_arr = $this->_keywordQuery($log_path, $keyword);
        }
        else {
            $file_arr   = list_dir($log_path, false);//文件列表
        }

        $LOG_PATH   = str_replace('\\', '/', LOG_PATH);

        foreach ($file_arr as $k => $filename) {//var_dump($filename, basename($filename));
            $temp = str_replace($LOG_PATH, '', $filename);

            if (is_file($filename)) {//文件
                $file_arr[$k] = array(
                    'filename'	=> $temp,
                    'time'      => new_date(null, filemtime($filename)),
                    'size'      => filesize($filename),
                    'is_file'   => true
                );
            }
            else {//文件夹
                $file_arr[$k] = array(
                    'filename'	=> $temp . '/',
                    'time'      => '--',
                    'size'      => '--'
                );
            }
        }

        usort($file_arr, array($this, '_cmp'));//按时间倒序

        $this->_ajaxReturn(true, '', $file_arr, count($file_arr));
    }//end listAction

    /**
     * 查看文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-28 11:22:40
     *
     * @return void 无返回值
     */
    public function viewAction() {
        $filename = Filter::string('filename', 'get');//文件名
        $path     = LOG_PATH . trim($filename, '/');
        $data     = array('filename' => $filename);

        if (!is_file($path)) {//文件不存在
            $this->_model->addLog(L('LOG,FILE') . $path . L('NOT_EXIST'), LOG_TYPE_INVALID_PARAM);
            $data['content'] = L('LOG,FILE') . $filename . L('NOT_EXIST');
        }
        else {
            $content = file_get_contents($path);
            $replace = false;
            $find    = array('error', 'eval', 'invalid', 'failed');

            foreach(array('error', 'eval', 'invalid', 'failed') as $v) {

                if (stripos($content, $v)) {
                    $replace = true;
                    break;
                }
            }

            $content = $replace ? preg_replace('/(.+(' . join('|', $find) . ').+)/i', '<span style="color: red">\1</span>', $content) : $content;
            $data['content'] = nl2br($content);
        }

        $this->_ajaxReturn(true, '', $data);
    }//end viewAction
}