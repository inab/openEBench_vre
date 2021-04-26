<?php 
//functions to manage nextcloud 


/*************** SABREDAV *************** */

use Sabre\DAV\Client;
/*
Per defecte ja tindre un comportamnet, pillant tot d globals, pero les propietats nomes serveixen per modificar el comportament
per defecte.
//username
//server 
//password

*/

/**
 * Constructs the HTTP client
 * @param
 * @return 
 */
function constructClient($nc_server){

    if (!isset($GLOBALS['repositories']['nc'][$nc_server])){
        $_SESSION['errorData']['Error'][]="Nextcloud storage '$nc_server' not declared on the VRE. Please, contact with the administrators";
        return false;
    }
    // Query Nextcloud API to get file-path of the given NC file Id
    $nc_username = 0;
    $nc_password = 0;
    if (!isset($GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file']) || !is_file($GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file'])){
        $_SESSION['errorData']['Error'][]="Credentials for VRE repository '$nc_server' not found or invalid. Please, contact with the administrators.";
        return false;
    }
    $confFile = $GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file'];

    // fetch nextcloud API credentials
    $credentials = array();
    if (($F = fopen($confFile, "r")) !== FALSE) {
        while (($data = fgetcsv($F, 1000, ";")) !== FALSE) {
            foreach ($data as $a){
                //$r = explode(":",$a);
                $r = preg_replace('/^.:/', "", $a);
                if (isset($r)){array_push($credentials,$r);}
            }
        }
        fclose($F);
    }
    if ($credentials[2] != $nc_server){
        $_SESSION['errorData']['Error'][]="Credentials for VRE nextcloud storage '$nc_server' are invalid. Please, contact with the administrators";
        return false;
    }
    $username = $credentials[0];
    $password = $credentials[1];
    $baseUrl = "$nc_server/remote.php/dav/files/$username/";

    $settings = array('baseUri' => $baseUrl , 'userName' => $username , 'password' => $password);

    /*
    $settings = array(
        'baseUri' => 'https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/oeb-9540f6e8-7abc-4a0e-8de4-402c1d0eadb8/',
        'userName' => 'oeb-9540f6e8-7abc-4a0e-8de4-402c1d0eadb8',
        'password' => 'oeb-vredev2021'
    );
    */

    $client = new Client($settings);
    
    return $client;
}


/**
 * Deletes a given file on Nextcloudv
 * @param fileName - the file to remove
 * @return true if correctly done, false otherwise
 */
//var_dump(ncDeleteFile("https://dev-openebench.bsc.es/nextcloud/", "test.md"));
function ncDeleteFile($nc_server, $fileName){
    $client = constructClient($nc_server);

    //check if file exists
    $response = $client->request('GET', $fileName);
    if ($response['statusCode'] == 200) {
        //delete
        $client->request('DELETE', $fileName);
        return true;
    } else return false;
   
}


/**
 * Uploads a file to nextcloud
 * @param fileId- file to upload
 * @param targetDir
 * @return true if correctly done, false otherwise
 */
//var_dump(ncUploadFile("https://dev-openebench.bsc.es/nextcloud/","OpEBUSER5e301d61da6f8_5e5fc0fa342003.92608382", "uploads"));

function ncUploadFile($nc_server, $fileId, $targetDir){
    $client = constructClient($nc_server);

    //1.Check if exitsts community folder 
    if (!checkFileExists("https://dev-openebench.bsc.es/nextcloud/", explode("/",$targetDir))[0]) {
        ncCreateFolder("https://dev-openebench.bsc.es/nextcloud/", explode("/",$targetDir)[0]);
    }

    //2. Create benchmarking folder in case not created
    if (!checkFileExists("https://dev-openebench.bsc.es/nextcloud/", $targetDir)) {
        ncCreateFolder("https://dev-openebench.bsc.es/nextcloud/", $targetDir);
    }

    
    //3. Upload File: consolidated/participant + tar
    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fileId,'path');
    $file_name  = basename($file_path);
    
    if (file_get_contents($file_path)) {
        $response = $client->request('PUT', $targetDir."/".$file_name, file_get_contents($file_path));
        if ($response['statusCode'] == 201) {
            //4. Get link of tar file(s) and return it
            $url = getPublicLinkFile($targetDir."/".$file_name);
            return $url;

        }
    }
}




/**
 * Downloads a file from nextcloud
 * @param fileName - the file to download
 * @param targetName - path where to save file
 * @return true if correctly done, false otherwise
 */
//var_dump(ncDownlowFile("https://dev-openebench.bsc.es/nextcloud/", "uploads/test.md", "./kk.txt"));
//falla el file_put_content
function ncDownlowFile($nc_server, $fileName, $targetName){
    $client = constructClient($nc_server);

    //check if file exists
    $response = $client->request('GET', $fileName);
    if ($response['statusCode'] == 200) {
        if (file_put_contents($targetName, $response['body'])){
            return true;
        }else return false;
    }
    else return false;
}



/**
 * Creates a new folder in nextcloud
 * @param folderName - name/path of the folder to create
 * @return true if correctly done, false otherwise
 */
//var_dump(ncCreateFolder("https://dev-openebench.bsc.es/nextcloud/", "caca"));
function ncCreateFolder($nc_server, $folderName) {
    $client = constructClient($nc_server);
    $response = $client->request('MKCOL',$folderName);
    if ($response['statusCode'] == 201) {
        return true;
    } else return false;
}

/*
* An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
* The ETag is an arbitrary string, but MUST be surrounded by double-quotes.
*/

/**
 * Get the properties of a file
 * @param
 * @param properties - array of properties: https://docs.nextcloud.com/server/12.0/developer_manual/client_apis/WebDAV/index.html
 * @return
 */
//var_dump(getProperties("https://dev-openebench.bsc.es/nextcloud/", "test.md", array("{http://owncloud.org/ns}vre_ids")));
function getProperties($nc_server, $filePath, $properties){
    //per defecte que retorni totes, si hi ha algo (array) que retorni les del parametre

    $client = constructClient($nc_server);
    $response = $client->propfind($filePath, $properties);
    return $response;
}
/**
 * Checks if a file or folders exists
 * @param path - path of file/folder to check
 * @return true if exists, false otherwise
 */
//var_dump(checkFileExists("https://dev-openebench.bsc.es/nextcloud/", "uploads"));
function checkFileExists($nc_server, $path) {

    $client = constructClient($nc_server);
    $response = $client->request('HEAD', $path);

    if ($response['statusCode'] == 200){
       return true;
    } else return false;
}





/**
 * Adds properties to a file
 * @param propertiesToAdd - associative array with property-value: https://docs.nextcloud.com/server/12.0/developer_manual/client_apis/WebDAV/index.html
 * @return true if correctly done, false otherwise
 */
//var_dump(addProperties("https://dev-openebench.bsc.es/nextcloud/", "test.md", array('{http://owncloud.org/ns}vre_id' => "testId")));
function addProperties($nc_server, $filePath, $propertiesToAdd ) {
    //validar que el namescpace sigui correcte - TODO

    $client = constructClient($nc_server);
    if ($client->proppatch($filePath, $propertiesToAdd)) {
        return true;
    }else return false;
}

/**
 * Shares a file
 * @param pathFile - path of the file to share
 * @return url of the shared file.
 */
function getPublicLinkFile ($pathFile){  

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/apps/files_sharing/api/v1/shares',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => array('path' => $pathFile,'shareType' => '3'),
    CURLOPT_HTTPHEADER => array(
        'OCS-APIRequest: true',
        'Authorization: Basic b2ViLTk1NDBmNmU4LTdhYmMtNGEwZS04ZGU0LTQwMmMxZDBlYWRiODpvZWItdnJlZGV2MjAyMQ==',
        'Cookie: cookie_test=test; nc_sameSiteCookielax=true; nc_sameSiteCookiestrict=true; oc_sessionPassphrase=ATHSgWgeiN4RyUQ9Da%2Fh58WyuNYYX8syQanY3z2MzDTIaGDttBZpnwwuTOLz0WKkzuwYTJwt8gHI%2BSfoEeoXmNsZZLGPNZWZ2JsuS3dHgvV0IjiHE8QIf2E2K0qhf2u6; ocb24avbi3lw=a1b64e69874ec93a121379d5842413ac'
    ),
    ));

    $response = curl_exec($curl);
    $result = new SimpleXMLElement($response);

    if ($result->meta->statuscode == 100){
        return $result->data->url->__toString();
    } else {
        return false;
    }
   
    curl_close($curl);

}

/*

********** MANAGE GROUPS ***********
//create a new group in nextcloud whith name newgroup
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/cloud/groups -d groupid="newgroup" -H "OCS-APIRequest: true"

//Share a file/folder with a group.
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/apps/files_sharing/api/v1/shares -d path=foldername 
-d shareType=1 -d shareWith=newgroupp -H "OCS-APIRequest: true"

//Share a file/folder with a user: https://docs.nextcloud.com/server/12/developer_manual/core/ocs-share-api.html#create-a-new-share
curl -X POST -u oeb-9540f6e8-7abc-4a0e-8de4-402c1d0eadb8:oeb-vredev2021 https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/apps/files_sharing/api/v1/shares
-d path=text.md -d shareType=3 -d password=hola "OCS-APIRequest: true"

********** MANAGE TAGS ***********
//create a true tag 
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags/ -d '{"userVisible":true,"userAssignable":true,"canAssign":true,"name":"Teeeeeeest"}' -H 'Content-Type: application/json' 

//Add a tag to a file/folder
curl -X PUT -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags-relations/files/1060/1 -H 'Content-Type: application/json'

//Know the tag properties https://doc.owncloud.com/server/developer_manual/webdav_api/tags.html
curl --silent -u root:***REMOVED*** -X PROPFIND -H "Content-Type: text/xml" --data-binary '<?xml version="1.0" encoding="utf-8"?><a:propfind xmlns:a="DAV:" xmlns:oc="http://owncloud.org/ns"><a:prop><oc:display-name/><oc:user-visible/><oc:user-assignable/><oc:id/></a:prop></a:propfind>' https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags | xmllint --format -


//TODO: move file/folder?


$r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
        '{http://owncloud.org/ns}favorite' => 1,
    ));











$client = constructClient($username);
$r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
    '{http://owncloud.org/ns}oeb_id' => "bla",
));
var_dump(getProperties("mozilla.pdf", ""));




  /*
    

      /*
    $r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
        '{http://owncloud.org/ns}oeb_id' => 1,
    ));



/*
$client = constructClient($username);
 
$r = $client->propfind("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array('{http://owncloud.org/ns}tags'
    ));
    */


  /*
    $r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
        '{http://owncloud.org/ns}favorite' => 1,
    ));
    
    $r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
        '{http://owncloud.org/ns}tags' => "test",
    ));
     */ 


// Will do a GET request on the base uri
/*
$client = constructClient($username);
$response = $client->request('GET', 'robots.txt');
echo('<pre>');
var_dump($response);
echo('</pre>');

/* 
$fp = fopen("prova2.txt", w);
fwrite($fp, $response);
fclose($fp);
echo('<pre>');
var_dump($response);
echo('</pre>');

// Will do a HEAD request relative to the base uri
//$response = $client->request('HEAD', 'stuff');

// Will do a PUT request with a request body

$client = constructClient($username);
//$file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId("OpEBUSER5e301d61da6f8_5ea062738ec357.92182104",'path');
$response = $client->request('GET', "test.txt");
var_dump($response);
/*
// Will do a DELETE request with a condition --> GOOD
//$response = $client->request('DELEfTE', 'robots.txt');


function ncListAll(){

}



function ncGetFile(){

}

*/




//0. explore if an alterinative way to log in with token
//1. Install libraries to conect to VRE
//2. URLs webdav to connect
//3. Create and test functions

/*
function nc_getFile($nc_server="", $file_id_TOBEDEFINED=""){

	use Sabre\DAV\Client;

	if (!isset($GLOBALS['repositories']['nc'][$nc_server])){
                $_SESSION['errorData']['Error'][]="Nextcloud storage '$nc_server' not declared on the VRE. Please, contact with the administrators";
                return $file_url;
    }
    // Query Nextcloud API to get file-path of the given NC file Id
    $nc_username = 0;
    $nc_password = 0;
    if (!isset($GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file']) || !is_file($GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file'])){
            $_SESSION['errorData']['Error'][]="Credentials for VRE repository '$nc_server' not found or invalid. Please, contact with the administrators.";
            return $file_url;
    }
    $confFile = $GLOBALS['repositories']['nc'][$nc_server]['credentials']['conf_file'];

    // fetch nextcloud API credentials
    $credentials = array();
    if (($F = fopen($confFile, "r")) !== FALSE) {
        while (($data = fgetcsv($F, 1000, ";")) !== FALSE) {
            foreach ($data as $a){
                $r = explode(":",$a);
                if (isset($r[1])){array_push($credentials,$r[1]);}
            }
        }
        fclose($F);
    }
    if ($credentials[2] != $nc_server){
            $_SESSION['errorData']['Error'][]="Credentials for VRE nextcloud storage '$nc_server' are invalid. Please, contact with the administrators";
            return $file_url;
    }
	$username = $credentials[0];
	$password = $credentials[1];
	$baseUrl = "https://$nc_server/remote.php/dav/$username/";

	$setting = {'baseUri' => $baseUrl , 'userName' => $username , 'password' => $password}
}

//nc_getFile();
*/
