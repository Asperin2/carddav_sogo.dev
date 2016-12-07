<?php

function __autoload($class)
{
    require_once "libs/$class.php";
}

ini_set('display_errors', '1');
error_reporting(E_ALL);
//new carddavImages();
new CarddavSogo();