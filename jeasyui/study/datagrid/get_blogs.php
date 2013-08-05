<?php
require(dirname(__DIR__) . '/common.php');
header('content-type: application/json; charset=utf-8');
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$page = $page > 0 ? $page : 1;
$size = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$size = $size > 0 ? $size : 10;
$data = $db->select(array('table' => TB_BLOG, 'limit' => $page . '.' . $size));
echo json_encode($data);