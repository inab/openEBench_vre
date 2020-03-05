<?php
header('Content-Type: application/json');

require __DIR__."/../../config/bootstrap.php";

if(!checkLoggedIn()){
    echo '{}';
    exit;
}
if($_REQUEST){
    if (isset($_REQUEST['list'])){
        echo getUserProcesses();
        exit;
    }elseif(isset($_REQUEST['user'])){
        #TODO
        exit;
    }
}else{
    echo '{}';
    exit;
}