<?php

error_reporting(E_ALL & ~E_NOTICE);


// set up app settings
require dirname(__FILE__)."/../config/globals.inc.php";

// import vendor libs
require dirname(__FILE__)."/../vendor/autoload.php"; 

// initialize session
require dirname(__FILE__)."/../libs/session.inc";

// import local classes
foreach(glob(dirname(__FILE__)."/../libs/classes/*.php") as $lib){
    require $lib;
}
// import local libs
foreach(glob(dirname(__FILE__)."/../libs/*.php") as $lib){
    require $lib;
}

?>
