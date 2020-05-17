<?php
header('Content-Type: application/json');

require __DIR__."/../../config/bootstrap.php";
if(!checkLoggedIn()){
    return '{}';
}
if($_REQUEST) {
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?action=list
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "list"){
        echo getProcesses();
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
        if(isset($_REQUEST['owner'], $_REQUEST['_schema'])) {
            echo getDefaultValues();
            exit;
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?processForm=...
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "setProcess" ) {
        echo setProcess($_REQUEST['processForm']);
        exit;
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "validateGit") {
        if (isset($_REQUEST['gitURL'], $_REQUEST['gitTag'])) {
            echo validateGit($_REQUEST['gitURL'], $_REQUEST['gitTag']);
            exit;
        }
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getUser") {
        echo getUser();
        exit;
    } else {
        echo "IN";
        var_dump($_REQUEST);
    }
} else {
    echo '{}';
    exit;
}
