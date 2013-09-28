<?php
/**
 * 图片操作类
 *
 * @file            Image.class.php
 * @package         Yab\Image
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-02-25 16:29:24
 * @lastmodify      $Date$ $Author$
 */

class Image {

    /**
     * @var string $_position 水印位置，如果不为下面九种，则为随机。默认br
     * +---+---+---+
     * |tl |tc |tr |
     * +---+---+---+
     * |cl |cc |cr |
     * +---+---+---+
     * |bl |bc |br |
     */
    protected $_position     = 'cc';
    /**
     * @var int $_font_size 水印字体大小。默认30
     */
    protected $_font_size    = 30;
    /**
     * @var string $_font 水印字体。默认./simsun.ttc
     */
    protected $_font  = './simsun.ttc';
    /**
     * @var string $_font_color 水印字体颜色，七位，否则将随机颜色。默认#000000
     */
    protected $_font_color   = '#000000';
    /**
     * @var string $_background 水印图片或文字。默认null
     */
    protected $_background = null;
    /**
     * @var int $_angle 水印文字倾斜角度，逆时针放置。默认30
     */
    protected $_angle     = 30;
    /**
     * @var int $_offset_x 水印x偏移量。默认0
     */
    protected $_offset_x     = 0;
    /**
     * @var int $_offset_y 水印y偏移量。默认0
     */
    protected $_offset_y     = 0;
    /**
     * @var int $_trans 水印图片透明度。默认20
     */
    protected $_trans = 20;
    /**
     * @var bool $_unlink_src true加水印后删除原图。默认false
     */
    protected $_unlink_src = false;
    /**
     * @var string $_thumb_suffix 缩放图后缀标识。默认_thumb
     */
    protected $_thumb_suffix = '_thumb';

    /**
     * 创建图像资源
     *
     * @param array  $info     图像信息
     * @param string $filename 文件名
     *
     * @return resource 图像资源
     */
    private function _createImage($info, $filename) {
        $image_fun = str_replace('/', 'createfrom', $info['mime']);

        return $image_fun($filename);
    }

    /**
     * 输出图像
     *
     * @param resource $im   图像资源
     * @param string   $type 图像类型，默认png
     *
     * @return void 无返回值
     */
    protected function _output($im, $type = 'png') {
        header('Content-type: image/' . $type);
        $image_fun = 'image' . $type;
        $image_fun($im);
        imagedestroy($im);
        exit();
    }

    /**
     * 获取图片信息
     *
     * @param string $img 图片路径
     *
     * @return mixed 如果正确读取，返回图片信息，否则返回false
     */
    public function getImageInfo($img) {
        $image_info = getimagesize($img);

        if (false === $image_info) {
            return false;
        }

        if (function_exists('image_type_to_extension')) {
            $image_type = image_type_to_extension($image_info[2], false);
        }
        else {
            $image_type = substr($img, strrpos($img, '.') + 1);
        }

        return array(
            'width'  => $image_info[0],
            'height' => $image_info[1],
            'type'   => strtolower($image_type),
            'size'   => filesize($img),
            'mime'   => $image_info['mime']
        );
    }

    /**
     * 等比例缩放图片
     *
     * @param string $src_file   源图片
     * @param int    $to_w       缩放至宽度
     * @param int    $to_h       缩放至高度
     * @param string $to_file    缩放至图片路径，默认''，直接缩放原图

     * @todo 缩放后，不覆盖原图，比如，文件名+_thumb标识或拷贝到其它目录
     *
     * @return mixed 缩放成功，返回true，否则返回false
     */
    public function resize($src_file, $to_w, $to_h, $to_file = '') {
        $src_info   = $this->getImageInfo($src_file);

        if (false === $src_info) {
            return false;
        }

        $image_type = $src_info['type'];

        $to_file    = $to_file ? $to_file : $src_file;//substr($src_file, 0, strrpos($src_file, '.')) . $this->_thumb_suffix . '.' . $image_type;
        $image      = $this->_createImage($src_info, $src_file);
        $src_w      = imagesx($image);
        $src_h      = imagesy($image);
        $src_wh     = $src_w / $src_h;
        $to_wh      = $to_w / $to_h;

        if ($src_wh >= $to_wh) {
            $to_h = $src_w > $to_w ? $to_w * $src_h / $src_w : $to_h;
        }
        else {
            $to_w = $src_h > $to_h ? $to_h * $src_w / $src_h : $to_w;
        }

        $image_p    = imagecreatetruecolor($to_w, $to_h);
        $image_func = 'image' . $image_type;
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $to_w, $to_h, $src_w, $src_h);
        $image_type == 'jpeg' ? imagejpeg($image_p, $to_file, 100) : $image_func($image_p, $to_file);
        imagedestroy($image_p);
        $this->_unlink_src && unlink($src_file);

        return $to_file;
    }//end resize

    /**
     * 显示服务器图像文件，如果图片不存在，则创建图像文件
     *
     * @param string $img_file 图片路径，
     * @param string $text     图片文字，当图片存在服务器上时有效。默认''
     * @param int    $width    图片宽度，当图片不存在时有效。默认80
     * @param int    $height   图片高度，当图片不存在时有效。默认30
     *
     * @return bool true
     */
    public function showImg($img_file, $text = '', $width = 80, $height = 30) {
        $info = $this->getImageInfo($img_file); //获取图像文件信息

        if (false !== $info) {
            $im = $this->_createImage($info, $img_file);

            if ($im) {
                $image_fun = str_replace('/', '', $info['mime']);

                if ($text) {
                    $tc = imagecolorallocate($im, 0, 0, 0);
                    imagestring($im, 3, 5, 5, $text, $tc);
                }

                if ($info['type'] == 'png' || $info['type'] == 'gif') {
                    imagealphablending($im, false); //取消默认的混色模式
                    imagesavealpha($im, true);      //设定保存完整的 alpha 通道信息
                }

                header('content-type: ' . $info['mime']);
                $image_fun($im);
                imagedestroy($im);

                return true;
            }
        }

        $im  = imagecreatetruecolor($width, $height);//获取或者创建图像文件失败则生成空白PNG图片
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 4, 5, 5, 'NO PIC', $tc);
        $this->_output($im);

        return true;
    }//end showImg

    /**
     * 添加水印
     *
     * @param string         $image      待加水印图片
     * @param string         $to_file    输出图像名称。默认''，输出至原图

     * @todo 添加水印后，不覆盖原图，比如拷贝到其它目录
     *
     * @return bool true成功添加水印，否则错误信息
     */
    public function water($image, $to_file = '') {

        if (!is_file($image)) {
            return 'WATER_ERR_FILE_NOT_EXIST';
        }

        $image_info = $this->getImageInfo($image);//图片大小
        $image_w    = $image_info['width'];      //图片宽
        $image_h    = $image_info['height'];     //图片高
        $image_type = $image_info['type'];
        $src_image  = $this->_createImage($image_info, $image);

        if (is_file($this->_background)) {    //水印为图片
            $water_info  = $this->getImageInfo($this->_background);
            $width       = $water_w = $water_info['width'];    //水印宽
            $height      = $water_h = $water_info['height'];   //水印高
            $water_image = $this->_createImage($water_info, $this->_background);
        }
        else {//水印字体

            if (!is_file($this->_font)) {
                return 'WATER_ERR_FONT_NOT_EXIST';
            }

            $font_info = imagettfbbox($this->_font_size, 0, $this->_font, $this->_background);
            $width  = $font_info[2] - $font_info[6];
            $height = $font_info[3] - $font_info[7];
            unset($font_info);
        }

        if ($image_w < $width || $image_h < $height) {
            return 'WATER_ERR_IMAGE_TOO_SMALL';
        }

        $position_w   = $image_w - $width;
        $position_h   = $image_h - $height;
        $position_x_c = $position_w / 2;
        $position_y_c = $position_h / 2;

        switch ($this->_position) {
            case 'tl':    //顶部居左
                $position_x = 0;
                $position_y = 0;
                break;

            case 'tc':    //顶部居中
                $position_x = $position_x_c;
                $position_y = 0;
                break;

            case 'tr':    //顶部居右
                $position_x = $position_w;
                $position_y = 0;
                break;

            case 'cl':    //中部居左
                $position_x = 0;
                $position_y = $position_y_c;
                break;

            case 'cc':    //中部居中
                $position_x = $position_x_c;
                $position_y = $position_y_c;
                break;

            case 'cr':    //中部居右
                $position_x = $position_w;
                $position_y = $position_y_c;
                break;

            case 'bl':    //底部居左
                $position_x = 0;
                $position_y = $position_h;
                break;

            case 'bc':    //底部居中
                $position_x = $position_x_c;
                $position_y = $position_h;
                break;

            case 'br':    //底部居右
                $position_x = $position_w;
                $position_y = $position_h;
                break;

            default:
                $position_x = rand(0, $position_w);
                $position_y = rand(0, $position_h);
                break;
        }

        $position_x += $this->_offset_x;
        $position_y += $this->_offset_y;

        imagealphablending($src_image, true);

        if (isset($water_image)) {    //水印图片
            imagecopymerge($src_image, $water_image, $position_x, $position_y, 0, 0, $water_w, $water_h, $this->_trans);
        }
        else {

            if (7 == strlen($this->_font_color)) {//#000000
                $red   = hexdec(substr($this->_font_color, 1, 2));
                $green = hexdec(substr($this->_font_color, 3, 2));
                $blue  = hexdec(substr($this->_font_color, 5));
            }
            else {
                $red   = rand(0, 255);
                $green = rand(0, 255);
                $blue  = rand(0, 255);
            }

            imagettftext($src_image, $this->_font_size, $this->_angle, $position_x, $position_y + $height, imagecolorallocatealpha($src_image, $red, $green, $blue, 80), $this->_font, $this->_background);
        }

        $this->_unlink_src && unlink($image);

        $to_file = $to_file ? $to_file : $image;//substr($image, 0, strrpos($image, '.')) '.' . $image_type;

        switch ($image_info['mime']) {
            case 'image/gif':
                imagegif($src_image, $to_file);
                break;

            case 'image/jpeg':
                imagejpeg($src_image, $to_file, 100);
                break;

            default:
                imagepng($src_image, $to_file);
                break;
        }

        if (isset($water_image)) {
            unset($water_image);
        }

        if (isset($water_image)) {
            imagedestroy($water_image);
        }

        unset($image_info);

        imagedestroy($src_image);

        return true;
    }//end water
}