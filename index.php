<?php
/*
function __autoload($class)
{
    require_once "/home/eakhmetov/sogo/carddav_sogo.dev/libs/$class.php";
}
*/
/*
spl_autoload_register(function ($class_name) {
    include "libs/".$class_name . '.php';
});
*/
include "/home/eakhmetov/sogo/carddav_sogo.dev/libs/CarddavImages.php";
include "/home/eakhmetov/sogo/carddav_sogo.dev/libs/CarddavSogo.php";

ini_set('display_errors', '1');
error_reporting(E_ALL);
new CarddavImages();
new CarddavSogo();