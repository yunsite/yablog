<?php
require(dirname(__DIR__) . '/common.php');
$data = $db->select(array('table' => TB_CATEGORY, 'field' => 'cate_id,cate_name'));
array_unshift($data, array('cate_id' => 0, 'cate_name' => '所属分类'));
ajax_return($data);