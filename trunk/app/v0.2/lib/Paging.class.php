<?php
/**
 * 分页类
 *
 * @file            Page.class.php
 * @package         Yab\Paging
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-25 08:32:30
 * @lastmodify      $Date$ $Author$
 */

class Paging {
    /**
     * @var int $_total_num 记录总数
     */
    private $_total_num = 0;
    /**
     * @var int $_total_page 总页数
     */
    private $_total_page = 0;
    /**
     * @var int $_now_page 当前页
     */
    private $_now_page = 0;
    /**
     * @var int $_page_size 每页显示数。默认10
     */
    private $_page_size = 10;
    /**
     * @var int $_next_page 下一页
     */
    private $_next_page = 0;
    /**
     * @var int $_next_page 上一页
     */
     private $_prev_page = 0;
     /**
     * @var int $_show_pages_num 显示数字页数。默认7
     */
    private $_show_pages_num = 7;
    /**
     * @var int $_mode 分页模式。
     * 0: 默认模式，前后缩略  1 ... 5 6 7 ... 26 下一页 ...无链接;
     * 1: 首页 上一页 1 ... 5 6 7 ... 26 下一页 尾页 ...加链接
     * 2：总显示$this->_show_pages_num页 上一页 2 3 4 5 6 7 下一页
     */
    private $_mode = 0;
    /**
     * @var string $_next_page_text 下一页文字。默认下一页
     */
    private $_next_page_text = '下一页';
    /**
     * @var string $_next_page_text 上一页文字。默认上一页
     */
    private $_prev_page_text = '上一页';
    /**
     * @var string $_first_page_text 首页文字。默认首页
     */
    private $_first_page_text = '首页';
    /**
     * @var string $_last_page_text 尾页文字。默认尾页
     */
    private $_last_page_text = '尾页';
    /**
     * @var bool $_reverse_order true反向分页。默认false
     */
    private $_reverse_order = false;    //反向分页
    /**
     * @var string $_ajax_func ajax分页函数名。默认null
     */
    private $_ajax_func = null;    //ajax函数名
    /**
     * @var bool $_is_html true静态html分页。默认true
     */
    private $_is_html = true;
    /**
     * @var string $_url_tpl 文件模板。默认?page=\\1
     */
    private $_url_tpl = '/page/\\1.shtml';
    /**
     * @var string $_now_page_tpl 当前页class名。默认<li class="active">%s</li>
     */
    private $_now_page_tpl = '<li class="active"><span>%s</span></li>';
    /**
     * @var string $_href_tpl a标签模板。默认'<li><a href="%s">%s</a></li>
     */
    private $_href_tpl = '<li><a href="%s">%s</a></li>';
    /**
     * @var string $_page_begin_html 分页前html
     */
    private $_page_begin_html = '<div class="pagination pagination-right"><ul>';
    /**
     * @var string $_page_end_html 分页后html
     */
    private $_page_end_html = '</ul></div>';

    /**
     * 返回指定范围内整数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 08:54:39
     *
     * @param int $num  待验证整数
     * @param int $min  最小
     * @param int $max  最大
     * @param int $base 基数
     *
     * @return int 指定范围内的整数
     */
    private function _constrainNumber($num, $min, $max, $base = -1) {
        $num = ($min > $base && $num < $min) ? $min : $num;
        $num = ($max > $base && $num > $max) ? $max : $num;

        return $num;
    }

    /**
     * 获取链接地址
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 08:55:35
     *
     * @param int    $page 页数
     * @param string $text 链接文字
     *
     * @return string 链接地址
     */
    private function _getUrl($page, $text = '', $no_link = false) {
        $page = $this->_reverse_order ? $this->_total_page - $page + 1 : $page;

        return sprintf($no_link ? $this->_now_page_tpl : $this->_href_tpl, $page, $text ? $text : $page);
    }

    /**
     * 获取模式0分页html，前后缩略
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 09:02:15
     *
     * @param int $_mode 模式。0 前后缩略  1 ... 5 6 7 ... 26 下一页 ...无链接0：2：总显示$this->_show_pages_num页总显示$this->_show_pages_num页 上一页 2 3 4 5 6 7 下一页。默认0
     *
     * @return string 分页html
     */
    private function _getHtmlMode0($_mode = 0) {
        $html         = '';
        $is_mode_2    = 2 == $_mode;
        $pages        = $this->_show_pages_num - 2;//中间显示页数
        $pages_side   = floor($pages / 2); //当前页左右两边显示数
        $offset_left  = $pages % 2 == 0 ? $pages_side - 1 : $pages_side;//当前页左侧显示数
        $offset_right = $pages_side;//当前页右侧显示数
        $html        .= $this->_prev_page > 0 ? $this->_getUrl($this->_prev_page, $this->_prev_page_text) : '';

        if (!$is_mode_2) {
            $html .= $this->_getUrl(1, '', 1 == $this->_now_page);
        }

        if ($this->_total_page <= $this->_show_pages_num) {//总页数小于显示页数
            $from = 2;
            $to   = $this->_total_page - 1;
        }
        else {
            $from  = $this->_now_page - $offset_left;//中间开始页数
            $to    = $this->_now_page + $offset_right;//中间结束页数

            if ($to < $pages + 1) {
                $from = 2;
                $to   = $pages + 1;
            }
            else {
                $to   = $to > $this->_total_page - 1 ? $this->_total_page - 1 : $to;
                $from = $this->_total_page - $from < $pages ? $this->_total_page - $pages : $from;
            }

            $html .= $from > 2 && !$is_mode_2 ? $this->_getUrl('...', '', true) : '';
        }

        if ($is_mode_2) {
           $from = $from - 1;
           $to   = $to + 1;
        }

        for ($i = $from; $i <= $to; $i++) {
            $t = $is_mode_2 ? 0 : 1;

            if ($i > $t) {
                $html .= $this->_getUrl($i, '', $i == $this->_now_page);
            }
        }

        $html .= $to > $this->_total_page - 2 || $is_mode_2 ? '' : $this->_getUrl('...', '', true);

        if (!$is_mode_2) {
            $html .= $this->_getUrl($this->_total_page, '', $this->_now_page == $this->_total_page);
        }

        $html .= $this->_next_page <= $this->_total_page ? $this->_getUrl($this->_next_page, $this->_next_page_text) : '';

        return $html;
    }//end _getHtmlMode0

    /**
     * 获取分页html，模式1，指定页数缩略 首页 上一页 1 ... 5 6 7 ... 26 下一页 尾页 ...加链接
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 09:02:15
     *
     * @return string 分页html
     */
    private function _getHtmlMode1() {
        $html  = $this->_prev_page < 1 ? '' : $this->_getUrl(1, $this->_first_page_text) . $this->_getUrl($this->_prev_page, $this->_prev_page_text);
        $temp  = $this->_now_page % $this->_show_pages_num;
        $from  = $temp == 0 ? $this->_now_page - $this->_show_pages_num : $this->_now_page - $temp;
        $from  = $from + 1;
        $to    = $from + $this->_show_pages_num - 1;
        $to    = $to > $this->_total_page ? $this->_total_page : $to;
        $html .= $from > $this->_show_pages_num ? $this->_getUrl($this->_now_page - $this->_show_pages_num, '...') : '';

        for ($i = $from; $i <= $to; $i++) {
            $html .= $this->_getUrl($i, '', $i == $this->_now_page);
        }

        $temp  = $this->_now_page + $this->_show_pages_num;
        $html .= $this->_total_page >= $from + $this->_show_pages_num ? $this->_getUrl($temp > $this->_total_page ? $this->_total_page : $temp, '...') : '';
        $html .= $this->_next_page > $this->_total_page ? '' : $this->_getUrl($this->_next_page, $this->_next_page_text) . $this->_getUrl($this->_total_page, $this->_last_page_text);

        return $html;
    }

    /**
     * 获取模式2分页html，总显示$this->_show_pages_num页 上一页 2 3 4 5 6 7 下一页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 09:02:15
     *
     * @return string 分页html
     */
    private function _getHtmlMode2() {
        return $this->_getHtmlMode0(2);
    }

    /**
     * 构造函数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 08:51:33
     *
     * @param array $properties 属性数组
     *
     * @return void 无返回值
     */
    public function __construct($properties = array()) {
        $this->setProperty($properties);

        if (!$this->_total_page) {//未提供总页数，自动计算
            $this->_total_page  = ceil($this->_total_num / $this->_page_size);
        }

        if (!$this->_now_page) {//未提供当前页，设置默认
            $this->_now_page = $this->_reverse_order ? $this->_total_page : 1;
        }

        $this->_now_page    = $this->_constrainNumber($this->_now_page, 1, $this->_total_page);
        $this->_now_page    = $this->_reverse_order ? $this->_total_page - $this->_now_page + 1 : $this->_now_page;
        $this->resetProperty();
    }

    /**
     * 获取分页html
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 09:01:09
     *
     * @return string 分页html
     */
    public function getHtml() {

        if ($this->_total_page < 2) {
            return '';
        }

        $function  = '_getHtmlMode' . $this->_mode;
        $html      = $this->$function();
        $replace   = $this->_ajax_func ? "javascript:{$this->_ajax_func}(\\1)" : $this->_url_tpl;
        $html      = preg_replace('/href="([^"]+)"/', "href=\"{$replace}\"" , $html);

        if ($this->_is_html) {
            $html = preg_replace('#/page/' . ($this->_reverse_order ? $this->_total_page : 1) . '\.(html|htm|shtml)#', '.\\1', $html);
        }

        return $this->_page_begin_html . $html . $this->_page_end_html;
    }

    /**
     * 设置属性
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 08:51:33
     *
     * @param mixed $key 变量名称或一组变量数组
     * @param mixed $val 变量值。默认null
     *
     * @return object 本类实例
     */
    public function setProperty($key, $value = null) {

        if (is_array($key)) {

            foreach ($key as $k => $v) {
                $this->$k = $v;
            }
        }
        else {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * 重新设置变量，循环时，不用每次都new一次
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-25 08:51:33
     *
     * @return object 本类实例
     */
    public function resetProperty() {
        $this->_prev_page   = $this->_constrainNumber($this->_now_page - 1, 0, $this->_total_page - 1);
        $this->_next_page   = $this->_constrainNumber($this->_now_page + 1, 1, $this->_total_page + 1);

        return $this;
    }
}