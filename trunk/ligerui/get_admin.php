<?php
require(__DIR__ . '/common.php');
$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$page   = $page > 0 ? $page : 1;
$size   = isset($_POST['page_size']) ? intval($_POST['page_size']) : 10;
$size   = $size > 0 ? $size : 10;
$sort   = isset($_POST['sort']) ? $_POST['sort'] : 'admin_id';
$order  = isset($_POST['order']) ? $_POST['order'] : 'DESC';
$where  = isset($_POST['keyword']) ? array('username' => array('like', '%' . $_POST['keyword'] . '%')) : '';
$data   = $db->select(array('table' => TB_ADMIN, 'limit' => $page . '.' . $size, 'where' => $where, 'order' => $sort . ' ' . $order));
$count  = $db->select(array('table' => TB_ADMIN, 'field' => 'COUNT(*)', 'where' => $where));
$count  = array_pop($count[0]);
ajax_return(true, '', $data, $count);