<?php
use Sabre\DAV\Client;

class nc_Connection extends Client{

    //attributes
    private $username;
    private $password;
    private $server;

    function __construct($username = null, $password = null, $server = null) {

        //default parameters
        if (is_null($username) && is_null($password) && is_null($server)){
            if (!isset($GLOBALS['repositories']['nc'])){
                $_SESSION['errorData']['Error'][]="Nextcloud storage '$server' 
                    not declared on the VRE. Please, contact with the administrators";
                return false;
            }
            $server = array_keys($GLOBALS['repositories']['nc'])[0];
            // Query Nextcloud API to get file-path of the given NC file Id
            $nc_username = 0;
            $nc_password = 0;
            if (!isset($GLOBALS['repositories']['nc'][$server]['credentials']['conf_file']) || 
                !is_file($GLOBALS['repositories']['nc'][$server]['credentials']['conf_file'])){
                $_SESSION['errorData']['Error'][]="Credentials for VRE repository 
                    '$server' not found or invalid. Please, contact with the administrators.";
                return false;
            }
            $confFile = $GLOBALS['repositories']['nc'][$server]['credentials']['conf_file'];
        
            // fetch nextcloud API credentials
            $credentials = array();
            if (($F = fopen($confFile, "r")) !== FALSE) {
                while (($data = fgetcsv($F, 1000, ";")) !== FALSE) {
                    foreach ($data as $a){
                        $r = preg_replace('/^.:/', "", $a);
                        if (isset($r)){array_push($credentials,$r);}
                    }
                }
                fclose($F);
            }
            if ($credentials[2] != $server){
                $_SESSION['errorData']['Error'][]="Credentials for VRE nextcloud 
                    storage '$server' are invalid. Please, contact with the administrators";
                return false;
            }
            
            $this->username = $credentials[0];
            $this->password = $credentials[1];
            $this->server = $credentials[2];
            
        } else {
            $this->username = $username;
            $this->password = $password;
            $this->server = $server;
        }
        $this->baseUri = $this->server."/remote.php/dav/files/".$this->username."/";
        $settings = array('baseUri' => $this->baseUri, 'userName' => 
            $this->username , 'password' => $this->password);

        parent::__construct($settings);

        
    }

    /**
     * Deletes a given file on Nextcloudv
     * @param fileName - the file to remove
     * @return true if correctly done, false otherwise
     */
    function ncDeleteFile($fileName){
        //check if file exists
        $response = $this->request('GET', $fileName);
        if ($response['statusCode'] == 200) {
            //delete
            $this->request('DELETE', $fileName);
            return true;
        } else return false;
    
    }

    /**
     * Creates a new folder in nextcloud
     * @param folderName - name/path of the folder to create
     * @return true if correctly done, false otherwise
     */
    function ncCreateFolder($folderName) {
        $response = $this->request('MKCOL',$folderName);
        if ($response['statusCode'] == 201) {
            return true;
        } else return false;
    }

    /**
     * Checks if a file or folders exists
     * @param path - path of file/folder to check
     * @return true if exists, false otherwise
     */
    function checkFileExists($path) {

        $response = $this->request('HEAD', $path);
        if ($response['statusCode'] == 200){
        return true;
        } else return false;
    }

    /**
     * Shares a file protected by password
     * @param pathFile - path of the file to share
     * @return url of the shared file or false otherwise
     */
    function createPublicLinkFile ($pathFile){  
        //create password from now
        $date   = new DateTime(); //this returns the current date time
        $result = $date->format('Y-m-d-H-i-s');
        $password = hash("md5", $result);
        
        $data = array('path' => $pathFile,'shareType' => '3', "password" => $password);
        $url = $this->server."ocs/v1.php/apps/files_sharing/api/v1/shares";
        $auth_basic["user"] = $this->username;
        $auth_basic["pass"] = $this->password;
        $headers= array("OCS-APIRequest: true");

        $r = post($data, $url, $headers, $auth_basic);
        $result = new SimpleXMLElement($r[0]);

        if ($result->meta->statuscode == 100){
            $res = $result->data->url->__toString();
            $urlUser = end(explode("/", $res));
            return "https://".$urlUser.":".$password."@".end(explode("//",$this->server))."public.php/webdav/";

        } else {
            return false;
        }
    }

    /**
     * Get Shares from a specific file or folder
     * @param pathFile
     * @return url link or false otherwise
     */
    function getLinkFile($pathFile){ 

        $url = $this->server."ocs/v1.php/apps/files_sharing/api/v1/shares?path=".$pathFile;
        $auth_basic["user"] = $this->username;
        $auth_basic["pass"] = $this->password;
        $headers= array("OCS-APIRequest: true");

        $r = get($url, $headers, $auth_basic);
        $result = new SimpleXMLElement($r[0]);
    
        if ($result->meta->statuscode == 100){
            return $result->data->element->url->__toString();
        } else {
            return false;
        }
    }


    /**
     * Uploads a file to nextcloud
     * @param fileId- file to upload
     * @param targetDir
     * @return url of uploaded file or false otherwise
     */
    function ncUploadFile($fileId, $targetDir){
        $url = false;

        $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($fileId,'path');
        $file_name  = basename($file_path);
        
        //check file does not exists
        if (!$this->checkFileExists($targetDir."/".$file_name)){
            //Check folders are created or create them
            $foldersPath = explode("/", $targetDir);
            for ($i=count($foldersPath)-1; $i > 0; $i--) { 
                if (!$this->checkFileExists(dirname($targetDir,$i))) {
                    $this->ncCreateFolder(dirname($targetDir, $i));
                }
            }
            if (!$this->checkFileExists($targetDir)) {
                $this->ncCreateFolder($targetDir);
            }
            //Upload File
            if (file_get_contents($file_path)) {
                $response = $this->request('PUT', $targetDir."/".$file_name, file_get_contents($file_path));
                if ($response['statusCode'] == 201) {
                    //Get public link of file and return it
                    $url = $this->createPublicLinkFile($targetDir."/".$file_name);
                }
            }
        } else {
            //GET existing public link 
            $url = $this->getLinkFile($targetDir."/".$file_name);
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
        }
        return $url;
    }

    /**
     * Get the properties of a file
     * @param
     * @param properties - array of properties: 
     * https://docs.nextcloud.com/server/12.0/developer_manual/client_apis/WebDAV/index.html
     * @return
     */
    //var_dump(getProperties("test.md", array("{http://owncloud.org/ns}vre_ids")));
    function getProperties($filePath, $properties){
        //per defecte que retorni totes, si hi ha algo (array) que retorni les del parametre

        $response = $this->propfind($filePath, $properties);
        return $response;
    }

    /**
     * Adds properties to a file
     * @param propertiesToAdd - associative array with property-value: 
     * https://docs.nextcloud.com/server/12.0/developer_manual/client_apis/WebDAV/index.html
     * @return true if correctly done, false otherwise
     */
    //var_dump(addProperties("test.md", array('{http://owncloud.org/ns}vre_id' => "testId")));
    function addProperties($filePath, $propertiesToAdd ) {
        //validar que el namescpace sigui correcte - TODO

        if ($this->proppatch($filePath, $propertiesToAdd)) {
            return true;
        }else return false;
    }


    /**
     * Downloads a file from nextcloud
     * @param fileName - the file to download
     * @param targetName - path where to save file
     * @return true if correctly done, false otherwise
     */
    //var_dump(ncDownlowFile("uploads/test.md", "./kk.txt"));
    //falla el file_put_content
    function ncDownlowFile($fileName, $targetName){
        //check if file exists
        $response = $this->request('GET', $fileName);
        if ($response['statusCode'] == 200) {
            if (file_put_contents($targetName, $response['body'])){
                return true;
            }else return false;
        }
        else return false;
    }

}
