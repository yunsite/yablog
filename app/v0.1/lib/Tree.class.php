<?php
/**
 * 无限级分类树处理类
 *
 * @file            Tree.class.php
 * @package         Yab\Tree
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2011-12-20 16:03:08
 * @lastmodify      $Date$ $Author$
 */

class Tree {
    /**
     * @var array $tree tree2array()将树数组转化为普通数组 递归保持数据，每次调Tree::tree2array()前务必Tree->tree = array()
     */
    static public $tree = array();

    /**
     * 将普通数组转化为树数组
     *
     * @example msl.php
     *     $tree = array(
     *         1 = array(
     *             'id'        => 1,
     *             'parent_id' => 0,
     *             'name'      => '系统设置'
     *         ),
     *         2 = array(
     *             'id'        => 2,
     *             'parent_id' => 1,
     *             'name'      => '菜单管理'
     *         ),
     *         3 = array(
     *             'id'        => 3,
     *             'parent_id' => 2,
     *             'name'      => '添加菜单'
     *         ),
     *         4 = array(
     *             'id'        => 4,
     *             'parent_id' => 0,
     *             'name'      => '分类管理'
     *         ),
     *     );
     *     Tree:array2tree($tree) 返回
     *     $tree = array(
     *         0 = array(
     *             'id'        => 1,
     *             'parent_id' => 0,
     *             'name'      => '系统设置',
     *             'data'      => array(
     *                  0 = array(
     *                  'id'        => 2,
     *                  'parent_id' => 1,
     *                  'name'      => '菜单管理'
     *                  'data'      =>
     *                      1 = array(
     *                          'id'        => 3,
     *                          'parent_id' => 2,
     *                          'name'      => '添加菜单'
     *                      ),
     *                  ),
     *              )
     *         ),
     *         1 = array(
     *             'id'        => 4,
     *             'parent_id' => 0,
     *             'name'      => '分类管理'
     *         ),
     *     );
     *
     * @param array  $array    待转化数组
     * @param string $pk       主键，默认id
     * @param string $pid      父id字段，默认parent_id
     * @param string $child    子类键名，默认data
     *
     * @return array 转化后的树数组
     */

    static public function array2tree($array = array(), $pk = 'id', $pid = 'parent_id', $child = 'data') {
        $tree = array();

        if (is_array($array)) {
            $refer = array();//创建基于主键的数组引用

            foreach ($array as $key => $data) {
                $_key = $data[$pk];

                if (!isset($data['leaf'])) {//支持手工控制leaf by mrmsl on 2012-07-19 09:04:17
                    $array[$key]['leaf'] = true;
                }

                if (isset($data['checked']) && !is_bool($data['checked'])) {//支持手工控制checked by mrmsl on 2012-07-19 09:05:33
                    unset($array[$key]['checked']);
                }
                elseif (!isset($data['checked'])) {
                    $array[$key]['checked'] = false;
                }

                $refer[$_key] = &$array[$key];
            }

            foreach ($array as $key => $data) {
                $parent_id = $data[$pid];

                if ($parent_id && isset($refer[$parent_id])) {//存在parent 增加isset判断 by mrmsl on 2012-07-19 11:16:52
                    $refer[$parent_id]['leaf'] = false;
                    $parent = &$refer[$parent_id];
                    $parent[$child][] = &$array[$key];
                }
                else {
                    $tree[] = &$array[$key];
                }

                unset($array[$key]);
            }
        }

        return $tree;
    }//end array2tree

    /**
     * 将树数组转化为普通数组
     *
     * @example
     *     $tree = array(
     *         0 = array(
     *             'id'        => 1,
     *             'parent_id' => 0,
     *             'name'      => '系统设置',
     *             'data'      => array(
     *                  0 = array(
     *                  'id'        => 2,
     *                  'parent_id' => 1,
     *                  'name'      => '菜单管理'
     *                  'data'      =>
     *                      1 = array(
     *                          'id'        => 3,
     *                          'parent_id' => 2,
     *                          'name'      => '添加菜单'
     *                      ),
     *                  ),
     *              )
     *         ),
     *         1 = array(
     *             'id'        => 4,
     *             'parent_id' => 0,
     *             'name'      => '分类管理'
     *         ),
     *     );
     *     Tree:array2tree($tree) 返回
     *     $tree = array(
     *         0 = array(
     *             'id'        => 1,
     *             'parent_id' => 0,
     *             'name'      => '系统设置'
     *             'indent_key'=> '&nbsp;&nbsp;│'
     *         ),
     *         1 = array(
     *             'id'        => 2,
     *             'parent_id' => 1,
     *             'name'      => '菜单管理'
     *             'indent_key'=> '&nbsp;&nbsp;├ '
     *         ),
     *         2 = array(
     *             'id'        => 3,
     *             'parent_id' => 2,
     *             'name'      => '添加菜单'
     *             'indent_key'=> '&nbsp;&nbsp;│&nbsp;&nbsp;├ '
     *         ),
     *         3 = array(
     *             'id'        => 4,
     *             'parent_id' => 0,
     *             'name'      => '分类管理'
     *         ),
     *     );
     *
     * @param array  $tree       待转化树数组
     * @param int    $level      层次，默认0
     * @param string $indent_key 缩进字段，默认false，不缩进
     * @param string $child      子类标识，默认data
     *
     * @return array 转化后的普通数组
     */
    static function tree2array($tree, $level = 0, $indent_key = false, $child = 'data') {
        $xu = 0;

        foreach ($tree as $key => $val) {

            if ($indent_key) {
                $temp_str = ' ';

                if ($level > 0) {

                    for ($xu = 1; $xu < $level; $xu++) {
                        $temp_str .= '&nbsp;&nbsp;│';
                    }

                    $temp_str .= '&nbsp;&nbsp;├ ';
                }

                $val[$indent_key] = $temp_str . $val[$indent_key];
            }

            if (isset($val[$child])) {
                $temp_arr = $val[$child];

                unset($val[$child]);

                self::$tree[$key] = $val;
                self::tree2array($temp_arr, $level + 1, $indent_key, $child);
            }
            else {
                self::$tree[$key] = $val;
            }
        }
    }//end tree2array
}