<?php
/**
 * Mongo client mongodb connection
 */
class dbConnection {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $uri;

    public function __construct() { 

        // read credentials from config file
        $conf = array();
        if (($F = fopen($GLOBALS['db_credentials'], "r")) !== FALSE) {
            while (($data = fgetcsv($F, 1000, ";")) !== FALSE) {
            foreach ($data as $a){
                    $r = explode(":",$a);
                        if (isset($r[1])){array_push($conf,$r[1]);}
            }
            }
            fclose($F);
        }  

        //connection data.
        $this->user = $conf[0];
        $this->pass = $conf[1];
        $this->host = $conf[2];
        $this->db = $GLOBALS['dbname_VRE'];
        $this->uri = sprintf("mongodb://%s:%s@%s:27017", $this->user, $this->pass, $this->host);

    }

    public function getConnection($collectionName) {
        try {
            //Client creation
            $VREConn =  new MongoDB\Client($this->uri);
            // create handlers
            $db = $this->db;
            $handler = $VREConn->$db;
            return $handler->$collectionName;

        } catch (MongoDB\Driver\Exception\ConnectionException  $e){
        } catch (MongoDB\Driver\Exception\RuntimeException $e) {
            $_SESSION['errorData']['Error'][]="Cannot connect to database collection";
        }
    }
}

