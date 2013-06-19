<?php
/**
 * 目录、文件处理函数库。摘自{@link http://www.phpcms.cn phpcms}，已对源码进行修改
 *
 * @file            dir.php
 * @package         Yab
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @date            2013-01-24 17:38:54
 * @lastmodify      $Date$ $Author$
 */

/**
 * 转化路径中 “\”为“ /”
 *
 * @param string $path 路径
 *
 * @return string 转换后的路径
 */
function dir_path($path) {
    $path  = str_replace('\\', '/', $path);
    $path .= '/' != substr($path, -1) ? '/' : '';

    return $path;
}

/**
 * 创建目录，可创建多级
 *
 * @param string $path 路径
 * @param string $mode 权限。默认0755
 *
 * @return bool true创建成功，否则false
 */
function create_dir($path, $mode = 0755) {
    $path = dir_path($path);

    if(is_dir($path)) {
        return true;
    }

    return mkdir($path, $mode, true);
}

/**
 * 拷贝目录及下面所有文件
 *
 * @param string $from 原路径
 * @param string $to   目标路径
 *
 * @return bool true拷贝成功，否则false
 */
function copy_dir($from, $to) {
    $from = dir_path($from);
    $to   = dir_path($to);

    if (!is_dir($from)) {
        return false;
    }

    if ($from == $to) {
        return true;
    }

    !is_dir($to) && create_dir($to);

    $list = glob($from . '*');

    if (!empty($list)) {

        foreach($list as $v) {
            $path = $to . basename($v);

            if(is_dir($v)) {
                copy_dir($v, $path);
            }
            else {
                copy($v, $path);
                chmod($path, 0755);
            }
        }
    }

    return true;
}//end copy_dir

/**
 * 删除目录及目录下面的所有文件
 *
 * @param string $dir 路径
 */
function delete_dir($dir) {

    if (!is_dir($dir)) {
        return false;
    }

    $dir  = dir_path($dir);
    $list = glob($dir . '*');

    foreach($list as $v) {
        is_dir($v) ? delete_dir($v) : unlink($v);
    }

    return rmdir($dir);
}

/**
 * 列出目录下所有文件
 *
 * @param string $path      路径
 * @param bool   $recursive true递归。默认true
 * @param string $pattern   匹配模式。默认*
 * @param array  $list      增加的文件列表。默认array()
 *
 * @return array 文件列表
 */
function list_dir($path, $recursive = true, $pattern = '*', $list= array()) {
    $path  = dir_path($path);

    if (!is_dir($path)) {
        return false;
    }

    $files = glob($path . $pattern);

    foreach($files as $v) {
        $list[] = $v;
        is_dir($v) && $recursive && ($list = list_dir($v, $recursive, $pattern, $list));
    }

    return $list;
}
/**
 * 遍历目录及其子目录
 *
 * @param string $path    路径
 * @param bool   $recursive true递归。默认true
 * @param string $pattern 匹配模式。默认*
 *
 * @return array 文件列表
 */
function scand_dir($path, $recursive = true, $pattern = '*') {

    if (!is_dir($path)) {
        return false;
    }

    $path  = dir_path($path);
    $list  = array();
    $files = glob($path . $pattern);

    foreach ($files as $v) {

        if (is_dir($v) && $recursive) {
            $v = dir_path($v);
            $k = basename($v);
            $list[$k] = scand_dir($v, $recursive, $pattern);
        }
        else {
            $list[] = $v;
        }
    }

    return $list;
}

/**
 * 目录列表
 *
 * @param string $dir       路径
 * @param bool   $recursive 递归。默认true
 * @param int    $parent_id 父id。默认0
 * @param array  $dirs      传入的目录。默认array()
 *
 * @param array 目录列表
 */
function dir_tree($dir, $recursive = true, $parent_id = 0, $dirs = array()) {
    global $id;

    $parent_id == 0 && ($id = 0);
    $list = glob($dir . '*');

    foreach($list as $v) {

        if (is_dir($v) && $recursive) {
            $id++;
            $dirs[$id] = array('id' => $id, 'parentid' => $parent_id, 'name' => basename($v), 'dir' => $v . '/');
            $dirs      = dir_tree($v . '/', $recursive, $id, $dirs);
        }
    }

    return $dirs;
}

/**
 * 设置目录下面的所有文件的访问和修改时间
 *
 * @param string $path  路径
 * @param int    $mtime 修改时间
 * @param int    $atime 访问时间
 *
 * @return bool
 */
function touch_dir($path, $mtime = START_TIME, $atime = START_TIME) {
    $path = dir_path($path);

    if (!is_dir($path)) {
        return false;
    }

    !is_dir($path) && touch($path, $mtime, $atime);
    $files = glob($path . '*');

    foreach($files as $v) {
        is_dir($v) ? touch_dir($v, $mtime, $atime) : touch($v, $mtime, $atime);
    }

    return true;
}

/**
 * 转换目录下面的所有文件编码格式
 *
 * @param string $in_charset  原字符集
 * @param string $out_charset 目标字符集
 * @param string $dir         目录地址
 * @param string $extention   转换的文件格式
 *
 * @param string bool
 */
function dir_iconv($in_charset, $out_charset, $dir, $extention = 'php|html|htm|shtml|shtm|js|txt|xml') {
    if($in_charset == $out_charset){

        return false;
    }

    $list = list_dir($dir);

    foreach($list as $v) {
        if (preg_match("/\.({$extention})/i", $v) && is_file($v)){
            file_put_contents($v, iconv($in_charset, $out_charset, file_get_contents($v)));
        }
    }

    return true;
}