<?php
require(dirname(__DIR__) . '/common.php');
$controller = $_GET['controller'];
$action     = $_GET['action'];

if (is_file($file = __DIR__ . '/' . $controller . '.php')) {
    require($file);

    $class = new $controller;

    if (method_exists($class, $method = $action . 'Action')) {
        call_user_func(array($class, $method));
        exit();
    }
}

echo '<div id="' . $controller . $action . '">';
var_dump($_GET);
echo '</div>';