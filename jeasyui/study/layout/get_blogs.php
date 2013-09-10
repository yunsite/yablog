<?php
require(dirname(__DIR__) . '/common.php');
$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$page   = $page > 0 ? $page : 1;
$size   = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$size   = $size > 0 ? $size : 10;
$sort   = isset($_POST['sort']) ? $_POST['sort'] : 'blog_id';
$order  = isset($_POST['order']) ? $_POST['order'] : 'order';
$where  = isset($_POST['keyword']) ? array('title' => array('like', '%' . $_POST['keyword'] . '%')) : '';
$data   = $db->select(array('table' => TB_BLOG, 'limit' => $page . '.' . $size, 'where' => $where, 'order' => $sort . ' ' . $order));
$count  = $db->select(array('table' => TB_BLOG, 'field' => 'COUNT(*)', 'where' => $where));
$count  = array_pop($count[0]);
ajax_return(true, '', $data, $count);