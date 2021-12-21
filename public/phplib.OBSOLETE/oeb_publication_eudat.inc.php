<?php

/**
 * Called when user clicks on button to publish to EUDAT 
 * @param fn id of file in VRE
 * @param metadata a json doc with required fields filled
 * @return the DOI from EUDAT
 */
function oeb_publish_file_eudat($fn,$metadata){
    $eudat_doi=null;
    // fetch file path
    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fn,'path');

    //publication subprocess
    //add token parametre!
    $eudat_doi = system("python ../../scripts/populate/publish.py "."'".$metadata."' '".$file_path."'", $retvalue);

    return $eudat_doi;
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
