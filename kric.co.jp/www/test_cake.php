<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", "/var/www");
define("APPROOT_DIR", "data");
define("APP_DIR", APPROOT_DIR . DS . "app");
define("CAKE_CORE_INCLUDE_PATH", ROOT . DS . APPROOT_DIR . DS . "lib");
define("WEBROOT_DIR", "html");
define("WWW_ROOT", ROOT . DS . WEBROOT_DIR . DS);
echo "Including bootstrap...<br>";
require CAKE_CORE_INCLUDE_PATH . DS . "Cake" . DS . "bootstrap.php";
echo "OK";
