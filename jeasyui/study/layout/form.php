<?php
require(dirname(__DIR__) . '/common.php');
var_dump($_POST);'exit;
ajax_return(array('success' => true, 'messate' => $_POST));