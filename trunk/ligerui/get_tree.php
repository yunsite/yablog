<?php
require(__DIR__ . '/common.php');
$data   = $db->select(array('table' => TB_MENU, 'field' => array('*')));
echo json_encode($data);