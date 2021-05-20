<?php

/**
 * Functions to push and manage data to b2share
 */

/**
 * Push data to b2share
 * @param fn id of file in VRE to add in register
 * @param metadata - string with data form
 * @param userEudatToken-  the token of the user of EUDAT
 * @return json response object
 */
function oeb_publish_file_eudat($fn,$metadata, $userEudatToken){
    
    $_GLOBALS['B2SHARE_submission_repository'] = "/home/user/b2share";
	$response_json = new JsonResponse();

    //create data form json in temp folder
    // build temporal directories
    $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."/eudat_form";
        if (!is_dir($wd)){
            mkdir($wd);
    }
    $random_id = "test"; //TO random num
    $r = file_put_contents($wd."/".$random_id.".json", $metadata);
    if (!$r) {
        // return error msg via BlockResponse
        $response_json->setCode(404);
        $response_json->setMessage("Cannnot access temporary dir or write content");

        return $response_json->getResponse();    
    }
    $eudat_form = $wd."/".$random_id.".json";

    // fetch file path
    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fn,'path');

    //Execute script to push to b2share
    $cmd = $_GLOBALS['B2SHARE_submission_repository']."/.py3env/bin/python3 ".$_GLOBALS['B2SHARE_submission_repository']."/upload.py -i ".$eudat_form." -cr ".$_GLOBALS['B2SHARE_submission_repository']."/api_endpoints.json -tk '".$userEudatToken."' -f ".$file_path;

    $retvalue = my_exec($cmd);
    if ($retvalue['return'] != 0){
       
        $response_json->setCode(400);
        $response_json->setMessage("<b>ERROR</b> pushing datasets to B2SHARE.".$retvalue['stderr']);
        return $response_json->getResponse();    
    }
    $doi = explode("DOI:",$retvalue['stdout'])[1];
    //register doi to VRE
    if (!registerDOIToVRE($fn, "00.0000/b2share.".$doi)){
        $response_json->setCode(400);
        $response_json->setMessage("Error register DOI "+doi+" to VRE database");
        return $response_json->getResponse();    
    }
    $response_json->setCode(200);
	$response_json->setMessage($doi);
    return $response_json->getResponse();
    
   
}
/**
 * Registers an eudatDOI to VRE file
 * @param fn id of the file in VRE
 * @param DOi to write
 * @return true if correctly done, false otherwise
 */
function registerDOIToVRE ($fn, $DOI) {
    $registered = false;
    if ($DOI != null) {
        $newMetadata = array("oeb_eudatDOI" => $DOI);
        if(addMetadataBNS($fn,$newMetadata)){
            $registered = true;
        }
    }
    return $registered;
}

function addB2SHAREtokenToVRE($user, $token){
    //TODO
}


