<?php
/**
 * 文件上传类
 *
 * @file            Upload.class.php
 * @package         Yab\Image
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-18 13:44:13
 * @lastmodify      $Date$ $Author$
 */

class Image_Upload extends Image_Image {
    //上传文件设置
    /**
     * @var string $_upload_dir 上传路径。默认./upload/
     */
    private $_upload_dir       = './upload/';
    /**
     * @var int $_size_limit 上传大小限制，单位kb，-1表示不限制。默认2048，2M
     */
    private $_size_limit       = 2048;
    /**
     * @var array $_uploaded_file 已上传文件信息
     */
    private $_uploaded_file = array();
    /**
     * @var int $_max_width 图片最大宽度，与最大高度同时大于0时则进行等比例压缩。默认0
     */
    private $_max_width = 0;
    /**
     * @var int $_max_height 图片最大高度，与最大宽度同时大于0时则进行等比例压缩。默认0
     */
    private $_max_height  = 0;
    /**
     * @var int $_rename_rule 文件重命名规则。-1：保持文件名不变；0：以时间戳+4位随机数命名；否则为自定义函数。默认0
     */
    private $_rename_rule = 0;
    /**
     * @var array $_mime_type mime类型
     */
    private $_mime_types  = array(
        //texts
        'txt'   => 'text/plain',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'php'   => 'text/x-php',

        //images
        'png'   => 'image/png',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'gif'   => 'image/gif',

        //archives
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar',

        //ms office
        'doc'   => 'application/msword',
        'docx'  => 'application/msword',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.ms-excel',
    );

    /**
     * @var array $_allow_extensions 允许上传文件后缀。默认array('jpg', 'jpeg', 'gif', 'png')
     */
    private $_allow_extensions  = array('jpg', 'jpeg', 'gif', 'png');
    /**
     * @var array $_allow_mime_types 允许上传文件mime类型
     */
    private $_allow_mime_types  = array();
    /**
     * @var array $_error 上传错误数组
     */
    private $_error = array(
        //1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
        UPLOAD_ERR_INI_SIZE => '上传文件大小超出了php.ini中upload_max_filesize选项指定的值，即：%s',
        //2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
        UPLOAD_ERR_FORM_SIZE => '上传文件大小超出了表单中指定的最大值，即：%s',
        //3，文件只有部分被上传。
        UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
        //4，没有文件被上传。
        UPLOAD_ERR_NO_FILE => '没有文件被上传',
        //6，找不到临时文件夹
        UPLOAD_ERR_NO_TMP_DIR => '无法找上传临时文件夹',
        ////7，文件写入失败
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        //其它错误
        -1 => '求知上传错误',

        'UPLOAD_ERR_CANT_MAKE_UPLOAD_DIR'     => '不能创建上传目',
        'UPLOAD_ERR_UPLOAD_DIR_CANT_WRITABLE' => '上传目录不可写',
        'UPLOAD_ERR_EXTENSION_NOT_ALLOW'      => '上传文件类型不允许。可上传类型为：%s。当前上传类型为：%s',
        'UPLOAD_ERR_MIME_TYPE_NOT_ALLOW'      => '上传文件mime类型不允许。可上传mime类型为：%s。当前上传类型为：%s',
        'UPLOAD_ERR_SIZE_LIMIT'               => '上传文件大小超出最大值，即：%s。当前上传文件大小：%s',
        'UPLOAD_ERR_NOT_UPLOADED_FILE'        => '非法上传文件',
        'UPLOAD_ERR_MOVED_FAILED'             => '移动文件失败',

        //水印错误
        'WATER_ERR_FILE_NOT_EXIST'            => '待加水印图片不存在',
        'WATER_ERR_FONT_NOT_EXIST'            => '水印字体不存在',
        'WATER_ERR_IMAGE_TOO_SMALL'           => '待加水印图片比水印图片或文字区域还小，无法生成水印'
    );

    /**
     * @var bool $_set_water true设置水印，默认false
     */
    protected $_set_water    = false;

    /**
     * 检查文件后缀名是否在允许后缀内
     *
     * @return bool true在允许范围内，否则false
     */
    private function _checkExtension() {
        $extension = pathinfo($this->_uploaded_file['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        $this->_setFileinfo('extension', $extension);

        return in_array($extension, $this->_allow_extensions);
    }

    /**
     * 检查文件mime类型是否在允许后缀内
     *
     * @return bool true在允许范围内，否则false
     */
    private function _checkMimeType() {
        $check_file = empty($this->_uploaded_check_mime_type) ? $this->_uploaded_file['tmp_name'] : $this->_uploaded_file['pathname'];

        if (class_exists('finfo', false)) {
            $finfo      = new finfo(FILEINFO_MIME_TYPE);
            $mime_type  = $finfo->file($check_file);
        }
        //图片
        elseif(in_array($this->_uploaded_file['extension'], array('jpg', 'jpeg', 'gif', 'png'))) {
            $image_info = getimagesize($check_file);

            if ($image_info) {
                $mime_type  = $image_info['mime'];
            }
            else {
                return false;
            }
        }
        else {
            $mime_type  = $this->_uploaded_file[empty($this->_uploaded_check_mime_type) ? 'type' : 'mime_type'];
        }

        $this->_setFileinfo('mime_type', $mime_type);

        if (in_array($mime_type, $this->_allow_mime_types)) {
            $this->_uploaded_check_mime_type = null;
            return true;
        }
        //ueditor上传，mime_type=application/octet-stream,默认通过验证，上传后，再验证已经上传文件的类型 by mrmsl on 2013-07-15 22:34:53
        elseif (empty($this->_uploaded_check_mime_type) && 'application/octet-stream' == $mime_type && defined('REFERER_PAGER') && strpos(REFERER_PAGER, 'imageUploader.swf')) {
            $this->_uploaded_check_mime_type = true;
            return true;
        }

        $this->_uploaded_check_mime_type = null;

        return false;
    }//end _checkMimeType

    /**
     * 检查文件是否上传成功
     *
     * @return true|string true上传成功，否则返回上传错误信息
     */
    private function _checkUpload() {
        $errno = $this->_uploaded_file['error'];

        switch ($errno) {

            case UPLOAD_ERR_OK://上传成功
                return true;
                break;

            case UPLOAD_ERR_INI_SIZE;//超出ini大小限制
                return sprintf($this->_error[UPLOAD_ERR_INI_SIZE], $this->_formatSize(ini_get('upload_max_filesize')));
                break;

            case UPLOAD_ERR_FORM_SIZE;//超出表单MAX_FILE_SIZE限制
                return sprintf($this->_error[UPLOAD_ERR_INI_SIZE], $this->_formatSize(intval($_POST['MAX_FILE_SIZE'])));
                break;

            default:
                return isset($this->_error[$errno]) ? $this->_error[$errno] : $this->_error[-1];
                break;
        }
    }

    /**
     * 检查文件大小是否超出限制
     *
     * @return bool true未超出，否则false
     */
    private function _checkSize() {
        return -1 == $this->_size_limit || $this->_size_limit >= $this->_uploaded_file['size'];
    }

    /**
     * 返回文件大小，带单位
     *
     * @param int $filesize  文件大小，单位：字节
     * @param int $precision 小数点数，默认：2
     *
     * @return string 带单位的文件大小
     */
    private function _formatSize($filesize, $precision = 2) {

        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100;
            $unit     = 'GB';
        }
        elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 ;
            $unit     = 'MB';
        }
        elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100;
            $unit     = 'KB';
        }
        else {
            $filesize = $filesize;
            $unit     = 'Bytes';
        }

        return sprintf('%.' . $precision . 'f', $filesize) . ' ' . $unit;
    }

    /**
     * 重命名
     *
     * @return string 新文件名
     */
    private function _resetName() {
        $rename_rule = $this->_rename_rule;

        if (-1 === $rename_rule) {//保持文件名不变
            return $this->_uploaded_file['name'];
        }
        elseif (0 === $rename_rule) {//以时间戳+4位随机数
            $filename = function_exists('new_date') ? new_date('YmdHis') : new_date('YmdHis') . mt_rand(1000, 9999);
        }
        else {
            $filename  = basename($this->_uploaded_file['name'], '.' . $this->_uploaded_file['extension']);
            $filename = $rename_rule($filename);
        }

        return $filename . '.' . $this->_uploaded_file['extension'];
    }

    /**
     * 设置$this->_uploaded_file信息
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return object this
     */
    private function _setFileinfo($key, $value) {
        $this->_uploaded_file[$key] = $value;

        return $this;
    }

    /**
     * 构造函数
     *
     * @param array $array 属性数组，key为属性名，value为属性值
     *
     * @return void 无返回值
     */
    function __construct($array = array()) {

        if (!empty($array)) {

            foreach ($array as $k => $v) {
                $this->$k = $v;
            }
        }

        $this->_size_limit = $this->_size_limit * 1024;

        foreach ($this->_allow_extensions as $v) {//允许上传mime_type类型

            if (!in_array($this->_mime_types[$v], $this->_allow_mime_types)) {
                $this->_allow_mime_types[$v] = $this->_mime_types[$v];
            }
        }

        if (function_exists('L') && 'UPLOAD_ERR_NO_FILE' != L('UPLOAD_ERR_NO_FILE')) {//存在语言包
            $this->_error = array(
                //1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
                UPLOAD_ERR_INI_SIZE => L('UPLOAD_ERR_INI_SIZE'),
                //2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
                UPLOAD_ERR_FORM_SIZE => L('UPLOAD_ERR_FORM_SIZE'),
                //3，文件只有部分被上传。
                UPLOAD_ERR_PARTIAL => L('UPLOAD_ERR_PARTIAL'),
                //4，没有文件被上传。
                UPLOAD_ERR_NO_FILE => L('UPLOAD_ERR_NO_FILE'),
                //6，找不到临时文件夹
                UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
                ////7，文件写入失败
                UPLOAD_ERR_CANT_WRITE => L('UPLOAD_ERR_CANT_WRITE'),
                //其它错误
                -1 => L('UPLOAD_ERR_OTHER'),
                'UPLOAD_ERR_CANT_MAKE_UPLOAD_DIR'     => L('UPLOAD_ERR_CANT_MAKE_UPLOAD_DIR'),
                'UPLOAD_ERR_UPLOAD_DIR_CANT_WRITABLE' => L('UPLOAD_ERR_UPLOAD_DIR_CANT_WRITABLE'),
                'UPLOAD_ERR_EXTENSION_NOT_ALLOW'      => L('UPLOAD_ERR_EXTENSION_NOT_ALLOW'),
                'UPLOAD_ERR_MIME_TYPE_NOT_ALLOW'      => L('UPLOAD_ERR_MIME_TYPE_NOT_ALLOW'),
                'UPLOAD_ERR_SIZE_LIMIT'               => L('UPLOAD_ERR_SIZE_LIMIT'),
                'UPLOAD_ERR_NOT_UPLOADED_FILE'        => L('UPLOAD_ERR_NOT_UPLOADED_FILE'),
                'UPLOAD_ERR_MOVED_FAILED'             => L('UPLOAD_ERR_MOVED_FAILED'),

                //水印错误
                'WATER_ERR_FILE_NOT_EXIST'            => L('WATER_ERR_FILE_NOT_EXIST'),
                'WATER_ERR_FONT_NOT_EXIST'            => L('WATER_ERR_FONT_NOT_EXIST'),
                'WATER_ERR_IMAGE_TOO_SMALL'           => L('WATER_ERR_IMAGE_TOO_SMALL'),
            );
        }
    }//end __construct

    /**
     * 上传文件
     *
     * @param array|string  $file		     $_FILES[inputname]文件或file域名称
     * @param string        $upload_dir 上传路径。默认''，取./upload/
     *
     * @return array 上传文件信息，如
     * array(
     *'name' => 'test.jpg'//文件名
     * 'type' => 'image/jpeg'//文件mime,$_FILE获取，不可信
     * 'tmp_name' => '/tmp/phpF6B.tmp'临时文件
     * 'error' => 0//上传错误
     * 'size' => 35909//文件大小，字节
     * 'extension' => 'jpg'//文件后缀
     * 'mime_type' => 'image/jpeg'//finfo获取文件mime，可信
     * 'pathname' => '/usr/www/upload/05cb327bd3108cb1a6fee42364cce175.jpg'//文件保存路径
     * 'filename' => '05cb327bd3108cb1a6fee42364cce175.jpg'//重命名文件后新名称,
     * 'errstr' => 'error string'//当文件上传失败时才会有此key
     * )
     */
    function execute($file, $upload_dir = '') {

        if (is_string($file)) {
            $file = empty($_FILES[$file]) ? false : $_FILES[$file];
        }

        if (!$file) {//无效文件 或 文件大小超出ini限制
            return array('errstr' => $this->_error[UPLOAD_ERR_NO_FILE]);
        }

        $this->_uploaded_file = $file;

        unset($file);

        $upload_dir = $upload_dir ? $upload_dir : $this->_upload_dir;

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {//上传目录检测
            $this->_setFileinfo('errstr', $this->_error['UPLOAD_ERR_CANT_MAKE_UPLOAD_DIR']);
        }
        elseif (!is_writable($upload_dir)) {//上传目录不可写
            $this->_setFileinfo('errstr', $this->_error['UPLOAD_ERR_UPLOAD_DIR_CANT_WRITABLE']);
        }
        elseif (true !== ($result = $this->_checkUpload())) {//上传文件是否成功
            $this->_setFileinfo('errstr', $result);
        }
        elseif (!$this->_checkExtension()) {//后缀名检测
            $error = sprintf($this->_error['UPLOAD_ERR_EXTENSION_NOT_ALLOW'], join('、', $this->_allow_extensions), $this->_uploaded_file['extension']);
            $this->_setFileinfo('errstr', $error);
        }
        elseif (!$this->_checkMimeType()) {//mime类型检测
            $error = sprintf($this->_error['UPLOAD_ERR_MIME_TYPE_NOT_ALLOW'], join('、', $this->_allow_mime_types), $this->_uploaded_file['mime_type']);
            $this->_setFileinfo('errstr', $error);
        }
        elseif (!$this->_checkSize()) {//文件大小检测
            $error = sprintf($this->_error['UPLOAD_ERR_SIZE_LIMIT'], $this->_formatSize($this->_size_limit), $this->_formatSize($this->_uploaded_file['size']));
            $this->_setFileinfo('errstr', $error);
        }
        elseif (!is_uploaded_file($this->_uploaded_file['tmp_name'])) {//非上传文件
            $this->_setFileinfo('errstr', $this->_error['UPLOAD_ERR_NOT_UPLOADED_FILE']);
        }
        else {

            $filename = $this->_resetName();//文件重命名
            $pathname = $upload_dir . $filename;//文件完整路径名

            $this->_setFileinfo('pathname', $pathname)->_setFileinfo('filename', $filename);

            if (!move_uploaded_file($this->_uploaded_file['tmp_name'], $pathname)) {//移动文件失败
                $this->_setFileinfo('errstr', $this->_error['UPLOAD_ERR_MOVED_FAILED']);
            }
            elseif (!empty($this->_uploaded_check_mime_type) && !$this->_checkMimeType()) {
                $error = sprintf($this->_error['UPLOAD_ERR_MIME_TYPE_NOT_ALLOW'], join('、', $this->_allow_mime_types), $this->_uploaded_file['mime_type']);
                $this->_setFileinfo('errstr', $error);
            }
            elseif ($this->_set_water && (true !== ($result = $this->water($pathname)))) {//添加水印失败
                $this->_setFileinfo('errstr', $this->_error[$result]);
            }
            elseif ($this->_max_width && $this->_max_height) {//等比例缩放图片
                $this->resize($pathname, $this->_max_width, $this->_max_height);
            }
        }

        return $this->_uploaded_file;
    }//end execute
}