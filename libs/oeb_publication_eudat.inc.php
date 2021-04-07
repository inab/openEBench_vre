<?php

/**
 * Called when user clicks on button to publish to EUDAT 
 * @param fn id of file in VRE
 * @param metadata a json doc with required fields filled
 * @param userToken the token of the user in EUDAT
 * @return the DOI from EUDAT
 */
function oeb_publish_file_eudat($fn,$metadata, $userEudatToken){
    $eudat_doi=null;
    // fetch file path
    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fn,'path');

    //publication subprocess
    //txell's token-- > to change for user logged token
    $userEudatToken = "ixQFTHFCUIPTjaBRps6ixPjLAo40M8fCE6AR6lEsttokUuS7q8xP2pLnD7Is";
    $eudat_doi = system("python ../../scripts/populate/publish.py "."'".$userEudatToken."' '".$metadata."' '".$file_path."'", $retvalue);

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
