<?php
/**
 * 重命名文件
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-24 17:45:02
 * @lastmodify      $Date$ $Author$
 */

require('dir.php');

$path  = 'C:\wamp\www\yablog\imgcache\v0.2\admin\app';
$files = list_dir($path, true);

foreach($files as $file) {

    if (is_file($file) && strpos($file, $v = 'Yap.')) {
        rename($file, $v = str_replace($v, 'Yab.', $file));
        var_dump($v, is_file($v));
    }
}