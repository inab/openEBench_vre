<?php


require __DIR__."/../../config/bootstrap.php";

//redirectAdminOutside();
redirectToolDevOutside();
var_dump(checkAdmin());
if (checkAdmin() || in_array($_REQUEST["process"],$_SESSION['User']['ToolsDev']) ){
    $GLOBALS['processCol']->update(array('_id' => $_REQUEST["process"]),
                                 array('$set'   => array('status' => intval($_REQUEST["status"])))
                             );
}
logger("Updating process status | USER: ".$_SESSION['User']["_id"].", ID:".$_SESSION['User']["id"].", PROCESS:".$_REQUEST['process'].", STATUS:".$_REQUEST["status"]);

redirect($GLOBALS['BASEURL'].'management/process/validation.php');
