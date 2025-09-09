<?php

define('ROOT', $_SERVER['DOCUMENT_ROOT']);

require ROOT . '/Classes/App.php';

App::getApp();

use Classes\Views\Router;

$router = new Router();
$router->execute();
?>