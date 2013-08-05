<?php
require(dirname(__DIR__) . '/common.php');
require(INCLUDE_PATH . 'Tree.class.php');
$data = $db->select(array('table' => TB_MENU, 'field' => array('*,`menu_id` AS id,`menu_name` AS text')));
echo json_encode(Tree::array2tree($data));