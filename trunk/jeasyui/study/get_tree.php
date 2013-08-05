<?php
require(__DIR__ . '/common.php');
require(INCLUDE_PATH . 'Tree.class.php');
$data   = $db->select(array('table' => TB_MENU, 'field' => array('*')));
$return = array();

foreach($data as $v) {
    $v['id'] = $v['menu_id'];
    $v['text'] = $v['menu_name'];
    $v['attributes'] = $v;
    $return[] = $v;
}

echo json_encode(Tree::array2tree($return));