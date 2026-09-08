<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo "APP_ENV: " . getenv("APP_ENV") . "<br>";
echo "ROOT: ";
define("DS", DIRECTORY_SEPARATOR);
echo DS . "var" . DS . "www" . "<br>";
echo "bootstrap exists: " . (file_exists("/var/www/data/lib/Cake/bootstrap.php") ? "YES" : "NO") . "<br>";
