<?php
header('Content-Type: application/json');

require __DIR__."/../../config/bootstrap.php";
if(!checkLoggedIn()){
    return '{}';
}
if($_REQUEST) {
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?list=true
    if (isset($_REQUEST['list'])){
        echo getProcesses();
        exit;
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?process=validation4&status=Private
    }elseif(isset($_REQUEST['process'], $_REQUEST['status'])){
        echo updateStatusProcess($_REQUEST['process'], $_REQUEST['status']);
        exit;
    }
}else{
    echo '{}';
    exit;
}
