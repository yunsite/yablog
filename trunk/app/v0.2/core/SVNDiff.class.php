<?php
/**
 * 仿svn比较字符内容差异类
 *
 * @file            SVNDiff.class.php
 * @package         Yab\Core
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-11 17:39:50
 * @lastmodify      $Date$ $Author$
 * @example
 *    svn_diff = new SVNDiff();
 *    $old  = array(
 *            'a' => 'a',
 *            'b' => 'b
 *                   c'
 *    );
 *    $new  = array(
 *            'a' => 'b',
 *            'b' => 'c'
 *    );
 *
 *    svn_diff->get(svn_diff->set($old, $new));
 *    输出:
      a:
 *    0001---- a
 *    0001++++ b
 *
 *    b:
 *    0001---- b
 *    0001++++ c
 *    0002---- c
 */

class SVNDiff {
    /**
     * @var string $_eol 换行符,默认\n
     */
    private $_eol = "\n";

    /**
     * @var array $_ignore_fields 忽略字段
     */
    private $_ignore_fields = array();

    /**
     * @var array $_always_fields 总是需要比较差异字段
     */
    private $_always_fields = array('title', 'content');

    /**
     * 比较两字符串
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-11 14:44:08
     * @example
     *    $text1  = 'b
     *                   c';
     *    $text2  = 'c'
     *    _diff($text1, $text2));
     *    输出:
     *    0001---- a
     *    0001++++ b
     *    0002---- b
     *
     * @param string $text1 字符串1
     * @param string $text2 字符串2
     *
     * @return array 比较后字符
     */
     private function _diff($text1, $text2) {
        $text1      = str_replace('&nbsp;', '', trim($text1));
        $text2      = str_replace('&nbsp;', '', trim($text2));

        $arr1       = explode($this->_eol, $text1);
        $arr2       = explode($this->_eol, $text2);

        $diff1_2    = array_diff_assoc($arr1, $arr2);//在$arr1,不在$arr2
        $diff2_1    = array_diff_assoc($arr2, $arr1);//在$arr2,不在$arr1

        $diff1      = array();
        $diff2      = array();

        $sprintf    = '%04d';

        foreach($diff1_2 as $k => $v) {//在$arr1,不在$arr2 ----
            $diff1[sprintf($sprintf, $k) . '_1'] = sprintf($sprintf, $k + 1) . '---- <del>' . trim($v) . '</del><br />';
        }

        foreach($diff2_1 as $k => $v) {//在$arr2,不在$arr1 ++++
            $diff2[sprintf($sprintf, $k) . '_2'] = sprintf($sprintf, $k + 1) . '++++ <ins>' . trim($v) . '</ins><br />';
        }

        $diff = array_merge($diff1, $diff2);
        ksort($diff);//按key排序,保证0001,0002...

        return join($this->_eol, $diff);
    }//end _diff

    /**
     * 构造方法
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-11 17:45:10
     *
     * @param array $always_fields 总是需要比较差异字段,默认null
     * @param array $ignore_fields 忽略字段,默认null
     *
     * @return void 无返回值
     */
    public function __construct($always_fields = null, $ignore_fields = null) {

        if ($always_fields) {
            $this->_always_fields = is_array($always_fields) ? $always_fields : explode(',', $always_fields);
        }

        if ($ignore_fields) {
            $this->_ignore_fields = is_array($ignore_fields) ? $ignore_fields : explode(',', $ignore_fields);
        }
    }

    /**
     * 设置属性
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-11 16:59:41
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void 无返回值
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }


    /**
     * 获取差异
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-11 16:49:23
     * @example
     *    svn_diff = new SVNDiff();
     *    $old  = array(
     *            'a' => 'a',
     *            'b' => 'b
     *                   c'
     *    );
     *    $new  = array(
     *            'a' => 'b',
     *            'b' => 'c'
     *    );
     *
     *    svn_diff->get(svn_diff->set($old, $new));
     *    返回:
array (
  0 =>
  array (
    'field_name' => 'a',
    'old' => 'a',
    'new' => 'b',
    'diff' => '0001---- <del>a</del><br />
0001++++ <ins>b</ins><br />
',
  ),
  1 =>
  array (
    'field_name' => 'b',
    'old' => 'b
                   c',
    'new' => 'c',
    'diff' => '0001---- <del>b</del><br />
0001++++ <ins>c</ins><br />
0002---- <del>c</del><br />
',
  ),
)
     *
     * @param array $changes 差异数组
     *
     * @return string 差异字符串
     */
    public function get($changes) {

        if (2 == func_num_args()) {//两个参数,老数据,新数据,自动生成$changes
            $args       = func_get_args();
            $changes    = $this->set($args[0], $args[1]);
        }

        if (empty($changes)) {
            return;
        }

        //显示名称最大长度基准,方便查看,如
        //修改了 系统发生错误是否入库 ...
        //修改了 网站名称             ...
        $max_field_name_length  = 0;
        $changes_with_diff      = array();//有比较差异的数组
        $changes_without_diff   = array();//无比较差异的数组

        foreach($changes as $change) {
            $field_name = $change['field_name'];

            if(($length = strlen($field_name)) > $max_field_name_length) {
                $max_field_name_length = $length;
            }

            if ($change['diff']) {
                $changes_with_diff[] = $change;
            }
            else {
                $changes_without_diff[] = $change;
            }
        }

        $changes  = array_merge($changes_without_diff, $changes_with_diff);//有比较差异的数组 最后显示
        $return     = '';

        foreach($changes as $change) {
            $field_name = str_pad($field_name, $max_field_name_length, ' ');

            if ($change['diff']) {
                $change['diff'] = strip_tags($change['diff'], '<ins><del>');
                $change['diff'] = nl2br($change['diff']);

                $return .= $change['diff'];
            }
            else {
                $return .= $change['new'] . ' [to] ' . $change['old'];
            }
        }

        return $return;
    }//end get

    /**
     * 生成两数组内容差异
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-10-11 14:45:53
     * @example
     *    svn_diff = new SVNDiff();
     *    $old  = array(
     *            'a' => 'a',
     *            'b' => 'b
     *                   c'
     *    );
     *    $new  = array(
     *            'a' => 'b',
     *            'b' => 'c'
     *    );
     *
     *    svn_diff->get(svn_diff->set($old, $new));
     *    返回:
array (
  0 =>
  array (
    'field_name' => 'a',
    'old' => 'a',
    'new' => 'b',
    'diff' => '0001---- <del>a</del><br />
0001++++ <ins>b</ins><br />
',
  ),
  1 =>
  array (
    'field_name' => 'b',
    'old' => 'b
                   c',
    'new' => 'c',
    'diff' => '0001---- <del>b</del><br />
0001++++ <ins>c</ins><br />
0002---- <del>c</del><br />
',
  ),
)
     *
     * @param array $old 老数组
     * @param array $new 新数组
     *
     * @return array 内容差异
     */
    public function set($old, $new) {
        $changes        = array();

        if (get_magic_quotes_gpc()) {
            array_walk($old, 'stripslashes');
            array_walk($new, 'stripslashes');
        }

        foreach($new as $k => $v) {
            $k  = strtolower($k);

            if (in_array($k, $this->_ignore_fields) || !isset($old[$k])) {
                continue;
            }

            $v_old  = $old[$k];

            if($v != $v_old) {
                $diff = '';
                //三行以上或总是比较差异
                if (substr_count($v, $this->_eol) > 1 || substr_count($v_old, $this->_eol) > 1 || in_array($k, $this->_always_fields)) {
                    $diff = $this->_diff($v_old, $v);
                }

                $changes[] = array('field_name' => $k, 'old' => $v_old, 'new' => $v, 'diff' => $diff . $this->_eol);
            }
        }

        return $changes;
    }//end set
}
/*
header('content-type: text/html; charset=utf-8');

$svn_diff = new SVNDiff();
$data = include('diff_data.php');
echo $svn_diff->get($svn_diff->set($data[0], $data[1]));

$svn_diff->_always_fields = array('a', 'b');
$old  = array(
        'a' => 'a',
        'b' => 'b
               c'
);
$new  = array(
        'a' => 'b',
        'b' => 'c'
);

echo $svn_diff->get($svn_diff->set($old, $new));
$style = <<<EOT
<style type="text/css">
    body { font-size: 12px; line-height: 150%; }
    ins { background: #cfc; text-decoration: none; }
    del { background: #fcc; }
</style>
EOT;
echo $style;
*/