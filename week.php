<?php
include "/srv/carddav_sogo.dev/libs/CarddavImages.php";
include "/srv/carddav_sogo.dev/libs/CarddavSogo.php";

ini_set('display_errors', '1');
error_reporting(E_ALL);
$config = include('config.php');
new CarddavImages($config);
new CarddavSogo($config);