<?php
/**
 * 日志处理类。摘自{@link http://www.thinkphp.cn thinkphp}，已对源码进行修改
 *
 * @file            Logger.class.php
 * @package         Yab\Log
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          liu21st <liu21st@gmail.com>
 * @date            2013-01-17 09:20:14
 * @lastmodify      $Date$ $Author$
 */

class Logger {

    /**
     * 系统
     */
    const SYSTEM = 0;
    /**
     * 邮件
     */
    const MAIL   = 1;
    /**
     * 文件，默认
     */
    const FILE   = 3;
    /**
     * SAPI
     */
    const SAPI   = 4;

    /**
     * @var array $log 日志信息
     */
    static $log    = array();
    /**
     * @var string $date_format 日期格式。默认[ Y-m-d H:i:s ]
     */
    static $date_format = '[ Y-m-d H:i:s ]';

    /**
     * 记录日志
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-17 09:24:57 by mrmsl
     *
     * @param string $message  日志内容
     * @param string $filename 日志文件名，如果提供，将写当前日志内容至该文件。默认''
     *
     * @return void 无返回值
     */
    static public function record($message, $filename = '') {

        if ($filename) {
            self::write($message, $filename);
        }
        else {
            self::$log[] = $message;
        }
    }

    /**
     * 保存记录日志
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-17 09:26:25 by mrmsl
     *
     * @return void 无返回值
     */
    static public function save() {

        if (self::$log) {
            self::write();
            self::$log = array();
        }
    }

    /**
     * 写日志
     *
     * @author          liu21st <liu21st@gmail.com>
     * @lastmodify      2013-01-17 09:26:44 by mrmsl
     *
     * @param string $message       日志信息。默认''，取已保存日志
     * @param string $destination   写入目标。默认''，日志路径+Y/md/+文件名
     * @param int    $type          日志记录方式。默认''，取C('LOG_TYPE')
     * @param string $extra         额外信息。默认''
     *
     * @return void 无返回值
     */
    static public function write($message = '', $destination = '', $type = '', $extra = '') {
        $log  = $message ? $message : join(EOL_LF, self::$log);

        if (!$log) {
            return;
        }

        $log      .= EOL_LF;
        $now      = new_date(self::$date_format);
        $type     = $type ? $type : C('LOG_TYPE');


        if (!$type || self::FILE == $type) {//文件方式记录日志

            if (defined('TODAY_LOG_PATH')) {
                $log_path = TODAY_LOG_PATH;
            }
            else {
                new_mkdir($log_path = LOG_PATH . new_date('Y/md/'));
                define('TODAY_LOG_PATH', $log_path);
            }

            if ('' === $destination) {
                $filename = 'php' . C('LOG_FILE_SUFFIX');
            }
            elseif ($destination) {

                if (false === strpos($destination, C('LOG_FILE_SUFFIX'))) {//sql.log, slowquery.log, errorsql.log...
                    $filename = $destination . C('LOG_FILE_SUFFIX');
                }
            }

            $destination = empty($filename) ? $destination : $log_path . $filename;

            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if (is_file($destination) && ($filesize = sys_config('sys_log_filesize')) && $filesize * 1024 <= filesize($destination)) {
                rename($destination, $log_path . basename($filename, C('LOG_FILE_SUFFIX')) . new_date('_His') . C('LOG_FILE_SUFFIX'));
            }
        }
        else {
            $destination = $destination ? $destination : C('LOG_DEST');
            $extra = $extra ? $extra : C('LOG_EXTRA');
        }

        if (APP_DEBUG && strpos($log, '] PHP ')) {//调试模式，输出php错误
            send_http_status(HTTP_STATUS_SERVER_ERROR);
            echo nl2br($log);
        }

        error_log($log, $type, $destination, $extra);
    }//end write
}