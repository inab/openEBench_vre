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
function oeb_publish_file_eudat($fn, $metadata, $userEudatToken){
    
	$response_json = new JsonResponse();

    //create data form json in temp folder
    // build temporal directories
    $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."/eudat_form";
        if (!is_dir($wd)){
            mkdir($wd);
    }
    $random_id = strval(round(microtime(true) * 1000));
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
    $cmd = $GLOBALS['B2SHARE_submission_repository']."/.py3env/bin/python3 ".$GLOBALS['B2SHARE_submission_repository']."/upload.py -i ".$eudat_form." -cr ".$GLOBALS['B2SHARE_submission_repository']."/api_endpoints.json -tk '".$userEudatToken."' -f ".$file_path;

    $retvalue = my_exec($cmd);
    if ($retvalue['return'] != 0){
       
        $response_json->setCode(400);
        $response_json->setMessage("<b>ERROR</b> pushing datasets to B2SHARE.".$retvalue['stderr']);
        return $response_json->getResponse();    
    }
    $doi = explode("DOI: ",$retvalue['stdout'])[1];
    //register doi to VRE
    if (!registerDOIToVRE($fn, $doi)){
        $response_json->setCode(400);
        $response_json->setMessage("Error register DOI "+$doi+" to VRE database");
        return $response_json->getResponse();    
    }
    /*
    //register doi to OEB
    //get oeb dataset id
    $oeb_id = getAttr_fromGSFileId($fn, "oeb_id");
   
    if (!registerDOIToOEB($doi, $oeb_id)){
        $response_json->setCode(400);
        $response_json->setMessage("Error register DOI "+doi+" to OpenEBench database");
        return $response_json->getResponse();    
    } else {
        //TODO --> remove file from NC and destroy link
    }
*/
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

/**
 * Registers an eudatDOI to OEB db
 * @param DOI - to register
 * @param OEBDataset_id - Dataset to add DOI
 * @return true if correctly done, false otherwise
 */
function registerDOIToOEB ($DOI, $OEBDataset_id) {
    $registered = false;
    $url = $GLOBALS['OEB_scirestapi'].'/Dataset/'.$OEBDataset_id.'/datalinks/-';
    $headers= array('Content-Type: application/json');
    $data = '{
            "uri" : "'.$DOI.'",
            "attrs" : [ 
                "archive"
            ],
            "kind" : "pid",
            "status" : "ok"
            }';
    //get credentials
    $confFile = $GLOBALS['OEBapi_credentials'];

    // fetch API credentials
    $credentials = array();
    if (($F = fopen($confFile, "r")) !== FALSE) {
        while (($d = fgetcsv($F, 1000, ";")) !== FALSE) {
            foreach ($d as $a){
                //$r = explode(":",$a);
                $r = preg_replace('/^.:/', "", $a);
                if (isset($r)){array_push($credentials,$r);}
            }
        }
        fclose($F);
    }
    $username = $credentials[0];
    $password = $credentials[1];

    $auth_basic["user"] = $username;
    $auth_basic["pass"] = $password;

    $r = patch($data, $url, $headers, $auth_basic);
    if ($r[1]['http_code'] != 200){
        $_SESSION['errorData']['Warning'][]="Error register DOI to OpenEBench datanase. Http code= ".$r[1]['http_code'];
        return $registered;
    } else {
        $registered = true;
    }

    return $registered;

}




