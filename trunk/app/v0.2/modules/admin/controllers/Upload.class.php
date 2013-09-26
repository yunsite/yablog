<?php
/**
 * 上传文件控制器
 *
 * @file            Upload.class.php
 * @package         Yab\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-12 22:21:14
 * @lastmodify      $Date $ $Author$
 */

class UploadController extends CommonController {
    /**
     * @var bool $_auto_check_priv true自动检测权限。默认false
     */
    protected $_auto_check_priv = false;

    /**
     * ueditor在线图片管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-16 21:56:18
     *
     * @return void 无返回值
     */
    public function ueditorImageManagerAction() {
        require(CORE_PATH . 'functions/dir.php');

        $file_arr   = list_dir(UPLOAD_PATH);
        $str        = '';

        if ($file_arr) {
            rsort($file_arr, SORT_STRING);

            foreach ( $file_arr as $file ) {

                if (preg_match('/\.(gif|jpeg|jpg|png|bmp)$/i', $file)) {
                    $str .= $file . 'ue_separate_ue';
                }
            }
        }

        exit(str_replace(dir_path(UPLOAD_PATH), '', $str));
    }

    /**
     * ueditor上传图片操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-12 22:22:13
     *
     * @return void 无返回值
     */
    public function ueditorUploadImageAction() {
        $config = array(
            '_upload_dir'   => UPLOAD_PATH,
        );
        $upload = new Image_Upload();
        $date   = date('Ymd/');
        $result = $upload->execute('upfile', UPLOAD_PATH . $date);

        if (isset($result['errstr'])) {//出错
            $this->triggerError(var_export($result, true));
            $result = array('state' => $result['errstr']);
        }
        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )

         array (size=9)
          'name' => string '未命名.jpg' (length=13)
          'type' => string 'application/octet-stream' (length=24)
          'tmp_name' => string 'F:\msl\web\wamp\tmp\php5BD1.tmp' (length=31)
          'error' => int 0
          'size' => int 20592
          'extension' => string 'jpg' (length=3)
          'mime_type' => string 'application/octet-stream' (length=24)
          'pathname' => string 'F:\msl\web\htdocs\yablog/imgcache/v0.2/upload/20130713/20130713231150.jpg' (length=73)
          'filename' => string '20130713231150.jpg' (length=18)
         */
        else {
            $result = array(
                'state'         => 'SUCCESS',
                'originalName'  => $result['name'],
                'name'          => $result['filename'],
                'url'           => $date . $result['filename'],
                'size'          => $result['size'],
                'type'          => $result['type'],
            );
        }

        exit(json_encode($result));
    }//ueditorUploadImageAction
}