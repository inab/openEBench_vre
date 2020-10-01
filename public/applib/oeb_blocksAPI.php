<?php
header('Content-Type: application/json');

require __DIR__."/../../config/bootstrap.php";
if(!checkLoggedIn()){
    return '{}';
}
if($_REQUEST) {
    //https://dev-openebench.bsc.es/vre/applib/oeb_blocksAPI.php?action=getBlocks
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getBlocks"){
        if (isset($_REQUEST['type'])) {
            echo getBlocks($_REQUEST['type']);
            exit;
        }
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getBlockSelect") {
        if (isset($_REQUEST['type'])) {
            echo getBlockSelect($_REQUEST['type']);
            exit;
        }
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getBlock") {
        if (isset($_REQUEST['id'])) {
            echo _getBlock($_REQUEST['id']);
            exit;
        }
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getWorkflows") {
        echo getWorkflows();
        exit;
    //https://dev-openebench.bsc.es/vre/applib/oeb_blocksAPI.php?action=updateStatus&block=validation4&status=Private
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "updateStatus") {
        if (isset($_REQUEST['block'], $_REQUEST['status'])) {
            echo updateStatusBlock($_REQUEST['block'], $_REQUEST['status']);
            exit;
        } else {
            echo "{}";
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_blocksAPI.php?action=getForm&urlOntology=https://w3id.org/oebDataFormats&ancestors=https://w3id.org/oebDataFormats/FormatDatasets
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getForm") {
        if (isset($_REQUEST['urlOntology'], $_REQUEST['ancestors'])) {
            echo loadOntologyToPlainList($_REQUEST['urlOntology'], $_REQUEST['ancestors']);
            exit;
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_blocksAPI.php?action=getDefaultValues&owner&_id&_schema
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getDefaultValues") {
        if(isset($_REQUEST['owner'])) {
            echo getDefaultValues();
            exit;
        }
    //https://dev-openebench.bsc.es/vre/applib/oeb_blocksAPI.php?blockForm=...
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "setBlock" ) {
        if (isset($_REQUEST['blockForm'], $_REQUEST['buttonAction'], $_REQUEST['typeBlock'] ))
        echo setBlock($_REQUEST['blockForm'], $_REQUEST['typeBlock'], $_REQUEST['buttonAction']);
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
    } elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "deleteBlock") {
        echo deleteBlock($_REQUEST['id']);
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "setWorkflow") {
        echo setWorkflow($_REQUEST['json'], $_REQUEST['validation'], $_REQUEST['metrics'], $_REQUEST['consolidation']);
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "showWorkflowJSON") {
        echo showWorkflowJSON($_REQUEST['id']);
    } else {
        echo "IN";
        var_dump($_REQUEST);
    }
} else {
    echo '{}';
    exit;
}
