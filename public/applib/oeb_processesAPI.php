<?php
header('Content-Type: application/json');

require __DIR__."/../../config/bootstrap.php";
if(!checkLoggedIn()){
    return '{}';
}
if($_REQUEST) {
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?action=getProcesses
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getProcesses"){
        echo getProcesses();
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getProcessSelect") {
        echo getProcessSelect();
        exit;
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getWorkflows") {
        echo getWorkflows();
        exit;
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?action=updateStatus&process=validation4&status=Private
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "updateStatus") {
        if (isset($_REQUEST['process'], $_REQUEST['status'])) {
            echo updateStatusProcess($_REQUEST['process'], $_REQUEST['status']);
            exit;
        } else {
            echo "{}";
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?action=getForm&urlOntology=https://w3id.org/oebDataFormats&ancestors=https://w3id.org/oebDataFormats/FormatDatasets
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getForm") {
        if (isset($_REQUEST['urlOntology'], $_REQUEST['ancestors'])) {
            echo getListOntologyForForm($_REQUEST['urlOntology'], $_REQUEST['ancestors']);
            exit;
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?action=getDefaultValues&owner&_id&_schema
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getDefaultValues") {
        if(isset($_REQUEST['owner'])) {
            echo getDefaultValues();
            exit;
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?processForm=...
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "setProcess" ) {
        echo setProcess($_REQUEST['processForm']);
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "createTool_fromWFs" ) {
        echo createTool_fromWFs($_REQUEST['id']);
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "reject_workflow" ) {
        echo reject_workflow($_REQUEST['id']);
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getUser") {
        echo getUser($_REQUEST['id']);
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "deleteProcess") {
        echo deleteProcess($_REQUEST['id']);
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "setWorkflow") {
        echo setWorkflow($_REQUEST['nameWF'], $_REQUEST['validation'], $_REQUEST['metrics'], $_REQUEST['consolidation']);
    } else {
        echo "IN";
        var_dump($_REQUEST);
    }
} else {
    echo '{}';
    exit;
}
