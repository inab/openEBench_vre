<?php 
//functions to manage nextcloud with webdav

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
function constructClient($nc_username){
    $settings = array(
        'baseUri' => 'https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/',
        'userName' => 'root',
        'password' => '***REMOVED***'
    );

    $client = new Client($settings);
    
    return $client;
}


/**
 * Deletes a given file on Nextcloudv
 * @param fileName - the file to remove
 * @return true if correctly done, false otherwise
 */
function ncDeleteFile($username ="", $fileName){
    $client = constructClient($username);

    //check if file exists
    $response = $client->request('GET', $fileName);
    if ($response['statusCode'] == 200) {
        $client->request('DELETE', $fileName);
        return true;
    } else return false;
   
}


/**
 * Uploads a file to nextcloud
 * @param fileId- file to upload
 * @param 
 * @return davFile array if correctly done, davfile with response, not file part
 */
function ncUploadFile($username ="", $fileId, $targetDir, $additionalProperties){
    $davfile = "";
    $client = constructClient($username);

    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fileId,'path');
    $file_name  = basename($file_path);
    
    if (file_get_contents($file_path)) {
        $response = $client->request('PUT', $targetDir."/".$file_name, file_get_contents($file_path));
        if ($response['statusCode'] == 201) {
            //add property file VRE id
            if (addProperties($targetDir."/".$file_name, $additionalProperties)) {
                //list properties
                $returnedProperties = getProperties($targetDir."/".$file_name, array('{http://owncloud.org/ns}fileid', 
                '{DAV:}getetag','{http://owncloud.org/ns}oeb_id','{http://owncloud.org/ns}owner-id','{DAV:}getlastmodified', 
                '{DAV:}resourcetype', '{http://owncloud.org/ns}checksums','{http://owncloud.org/ns}size'));

                $returnedProperties["local_url"] = 'https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/'.$targetDir."/".$file_name; 

                $davfile = array('response'=>$response, 'file'=>$returnedProperties); //treure els headers del response
                return $davfile;

            }
            
        } else return false;
      
    } else return false; //return jsonresponse -> msg indicant el problem (file (path) not found or empty)- bad request -code
}

//var_dump(ncUploadFile("", "OpEBUSER5e301d61da6f8_5e5fc0faa39716.11893046", "Drop", array('{http://owncloud.org/ns}oeb_id' => "bla")));


/**
 * Downloads a file from nextcloud
 * @param fileName - the file to download
 * @return true if correctly done, false otherwise
 */
function ncDownlowFile($username ="", $fileName, $targetName){
    $client = constructClient($username);

    //check if file exists
    $response = $client->request('GET', $fileName);
    var_dump($response);
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
function ncCreateFolder($folderName) {
    $client = constructClient($username);
    $response = $client->request('MKCOL','https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/'.$folderName);
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
function getProperties($filePath, $properties){
    //per defecte que retorni totes, si hi ha algo (array) que retorni les del parametre

    $client = constructClient($username);
    $response = $client->propfind("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/".$filePath, $properties);
    return $response;


}



/**
 * Adds properties to a file
 * @param propertiesToAdd - associative array with property-value: https://docs.nextcloud.com/server/12.0/developer_manual/client_apis/WebDAV/index.html
 * @return true if correctly done, false otherwise
 */
function addProperties($filePath, $propertiesToAdd  ) {
    //validar que el namescpace sigui correcte

    $client = constructClient($username);
    
    if ($client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/".$filePath, $propertiesToAdd)) {
        return true;
    }else return false;
}


/*

********** MANAGE GROUPS ***********
//create a new group in nextcloud whith name newgroup
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/cloud/groups -d groupid="newgroup" -H "OCS-APIRequest: true"

//Share a file/folder with a group.
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/ocs/v1.php/apps/files_sharing/api/v1/shares -d path=foldername -d shareType=1 -d shareWith=newgroupp -H "OCS-APIRequest: true"

********** MANAGE TAGS ***********
//create a true tag 
curl -X POST -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags/ -d '{"userVisible":true,"userAssignable":true,"canAssign":true,"name":"Teeeeeeest"}' -H 'Content-Type: application/json' 

//Add a tag to a file/folder
curl -X PUT -u root:***REMOVED*** https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags-relations/files/1060/1 -H 'Content-Type: application/json'

//Know the tag properties https://doc.owncloud.com/server/developer_manual/webdav_api/tags.html
curl --silent -u root:***REMOVED*** -X PROPFIND -H "Content-Type: text/xml" --data-binary '<?xml version="1.0" encoding="utf-8"?><a:propfind xmlns:a="DAV:" xmlns:oc="http://owncloud.org/ns"><a:prop><oc:display-name/><oc:user-visible/><oc:user-assignable/><oc:id/></a:prop></a:propfind>' https://dev-openebench.bsc.es/nextcloud/remote.php/dav/systemtags | xmllint --format -


//TODO: move file/folder?





$client = constructClient($username);
$r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
    '{http://owncloud.org/ns}oeb_id' => "bla",
));
var_dump(getProperties("mozilla.pdf", ""));




  /*
    $r = $client->proppatch("https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/mozilla.pdf", array(
        '{http://owncloud.org/ns}favorite' => 1,
    ));

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
