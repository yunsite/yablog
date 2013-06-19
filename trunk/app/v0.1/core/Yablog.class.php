<?php
/**
 * Yablog类
 *
 * @file            Yablog.class.php
 * @package         Yab\Core
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-18 12:52:51
 * @lastmodify      $Date$ $Author$
 */

class Yablog {
    /**
     * @var array $_require_files 加载核心核心文件
     */
    private $_require_files = array();

    /**
     * 创建运行时文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 15:45:33
     * @lastmodify      2013-02-18 17:03:00 by mrmsl
     *
     * @return void 无返回值
     */
    private function _buildRuntimeFile() {
        $filesize = 0;//加载文件大小
        $compile  = "<?php\n!defined('YAB_PATH') && exit('Access Denied');";//编译内容

        //加载核心文件
        foreach ($this->_require_files as $file) {
            require($file);

            if (defined('APP_DEBUG') && !APP_DEBUG) {
                $filesize += filesize($file);
                $compile  .= compile_file($file);
            }
        }

        $require_files = array(
            CORE_PATH . 'Bootstrap.' . APP_EXT,//启动插件类
            CORE_PATH . 'Template.' . APP_EXT,//启动插件类
            CORE_PATH . 'Model.' . APP_EXT,//模型类
            CORE_PATH . 'Logger.' . APP_EXT,//日志类
            CORE_PATH . 'Filter.' . APP_EXT,//参数验证及过滤类
            CORE_PATH . 'Db.' . APP_EXT,//Db类
            CORE_PATH  . 'drivers/db/Db' . ucfirst(DB_TYPE) . '.' . APP_EXT,//数据库驱动类
        );

        if (is_file($filename = LIB_PATH . 'BaseController.' . APP_EXT)) {//底层控制器类
            $require_files[] = $filename;
        }

        if (is_file($filename = APP_PATH . 'controllers/Common.' . APP_EXT)) {//项目底层通用控制器类
            $require_files[] = $filename;
        }

        if (is_file($filename = LIB_PATH . 'BaseModel.' . APP_EXT)) {//底层模型类
            $require_files[] = $filename;
        }

        if (is_file($filename = APP_PATH . 'models/Common.' . APP_EXT)) {//项目底层通用模型类
            $require_files[] = $filename;
        }

        //加载核心文件，用空间换时间
        if (APP_DEBUG) {//调试

             foreach ($require_files as $file) {
                require($file);
            }
        }
        else{

            foreach ($require_files as $file) {
                require($file);
                $filesize += filesize($file);
                $compile  .= compile_file($file);
            }

            file_put_contents(RUNTIME_FILE, $compile);
            $size = filesize(RUNTIME_FILE);//编译后大小
            file_put_contents(LOG_PATH. 'compile_runtime_file.log', new_date() . '(' . format_size($filesize) . ' => ' . format_size($size) . ')' . EOL_LF, FILE_APPEND);

        }

    }//end _buildRuntimeFile

    /**
     * 检查运行环境，必须要满足：1、加载yaf扩展；2、PHP版本大于5.3
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 14:38:13
     *
     * @return void 无返回值
     */
    private function _checkRuntimeRequirements() {
        !extension_loaded('yaf') && exit('yaf extension required!');
        !version_compare(PHP_VERSION, '5.3', '>') && exit('php5.3 or higher required!');
    }

    /**
     * 构造函数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 15:05:51
     *
     * @param array $require_files 预加载核心文件
     *
     * @return void 无返回值
     */
    public function __construct($require_files) {
        $this->_checkRuntimeRequirements();//运行环境检查
        $this->_require_files[] = CORE_PATH . 'functions/functions.php';//函数库

        if ($require_files) {
            $this->_require_files = array_merge($this->_require_files, $require_files);//合并核心文件
        }
    }

    /**
     * 启动程序
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-22 15:06:16
     *
     * @return void 无返回值
     */
    public function bootstrap() {
        ob_get_level() != 0 && ob_end_clean();
        header('content-type: text/html; charset=utf-8');

        if (APP_DEBUG || !is_file(RUNTIME_FILE)) {
            $this->_buildRuntimeFile();

            if (APP_DEBUG && is_file(RUNTIME_FILE)) {
                unlink(RUNTIME_FILE);
            }
        }
        else {
            require(RUNTIME_FILE);
        }

        $app = new Yaf_Application(CONF_FILE);
        $app->getDispatcher()->registerPlugin(new BootstrapPlugin());
        $app->bootstrap()->run();
    }
}