<?php
/**
 * 生成验证码图片类
 *
 * @file            Verifycode.class.php
 * @package         Yab\Image
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-17 15:23:46
 * @lastmodify      $Date$ $Author$
 */

class Image_Verifycode extends Image_Image {

    /**
     * 产生随机字符串
     *
     * @param ing    $len        长度，默认4
     * @param int    $mode       模式，默认5，去掉了容易混淆的字符oOLl和数字01
     * @param string $add_chars  额外字符，默认''
     *
     * @return string 随机字符串
     */
    private function _rand_string($len = 4, $mode = 5, $add_chars = '') {

        if (function_exists('rand_string')) {
            return rand_string($len, $mode, $add_chars);
        }

        $str = '';

        switch ($mode) {
            case 0://大小写字母a-zA-Z
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $add_chars;
                break;

            case 1://大写字母A-Z
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $add_chars;
                break;

            case 2://小写字母a-z
                $chars = 'abcdefghijklmnopqrstuvwxyz' . $add_chars;
                break;

            case 3://数字0-9
                $chars = str_repeat('0123456789', 3);
                break;

            case 4://字母与数字a-zA-z0-9
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;

            default://默认去掉了容易混淆的字符oOLl和数字01，要添加请使用add_chars参数
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $add_chars;
                break;
        }

        if ($len > 10) { //位数过长重复字符串一定次数
            $chars = 2 == $mode ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }

        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);

        return $str;
    }//end _rand_string

    /**
     * 设置验证码session
     *
     * @param string $string
     * @param int    $offset 验证码开始偏移量，如验证码为abcd，$offset=-1，保持验证码顺序；$offset=0，验证码倒序；$offset = 1, 则session = bcda；$offset = 2，则session = cdab，依此类推...，默认-1
     * @param string $verify_name  验证码session名称
     *
     * @return void 无返回值
     */
    private function _setVerifySession($string, $offset = -1, $verify_name) {

        if (-1 != $offset && isset($string{$offset})) {//顺序与验证码顺序不一致
            $len = strlen($string);

            if ($offset) {
                $string = substr($string, $offset) . substr($string, 0, $offset);
            }
            else {
                $string = strrev($string);
            }
        }

        session($verify_name, $string);
    }

    /**
     * 生成图像验证码
     *
     * @param ing     $length       验证码长度，默认4
     * @param int     $mode         验证码类型，默认5，去掉了容易混淆的字符oOLl和数字01
     * @param int     $width        验证码图片宽度，默认40
     * @param int     $height       验证码图片高度，默认20
     * @param int     $offset       验证码开始偏移量，如验证码为abcd，$offset=-1，保持验证码顺序；$offset=0，验证码倒序；$offset = 1, 则session = bcda；$offset = 2，则session = cdab，依此类推...，默认-1
     * @param string  $type         图片类型，默认png
     * @param string  $verify_name  验证码session名称，默认null，取SESSION_VERIFY_CODE常量值
     *
     * @return void 无回返值
     */
    public function buildVerifyImage($length = 4, $mode = 5, $width = 40, $height = 20, $offset = -1, $type = 'png', $verify_name = null) {
        $verify_name = $verify_name ? $verify_name : SESSION_VERIFY_CODE;
        $rand_string = $this->_rand_string($length, $mode);
        $width       = ($length * 9 + 10) > $width ? $length * 9 + 10 : $width;

        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($width, $height);
        }
        else {
            $im = imagecreate($width, $height);
        }

        $r     = array(225, 255, 255, 223);
        $g     = array(225, 236, 237, 255);
        $b     = array(225, 236, 166, 125);
        $key   = mt_rand(0, 3);

        $back_color   = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]); //背景色（随机）
        $border_color = imagecolorallocate($im, 100, 100, 100); //边框色

        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $back_color);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $border_color);

        for ($i = 0; $i < 50; $i ++) {
            $rand_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand() % 70, rand() % 30, $rand_color);
        }

        for ($i = 0; $i < $length; $i ++) {
            $font_color = imagecolorallocate($im, rand(100, 255), rand(0, 100), rand(100, 255));
            imagestring($im, 6, 5 + $i * 10, 3, $rand_string{$i}, $font_color);
        }

        $this->_setVerifySession($rand_string, $offset, $verify_name);//设置session

        $this->_output($im, $type);//输出图像
    }//end buildVerifyImage
}