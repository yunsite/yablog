<?php
require(dirname(__DIR__) . '/common.php');
echo json_encode($db->select(array('table' => TB_ADMIN)));