<?php

/**
 * File collection persistence class.
 */
class FilesDAO {

    /**
    * Encapsulates connection data to database.
    */
    private $dbConnect;

    /**
    * Collection name for entity.
    */
    private static $COLLECTION_NAME = 'files';

    /**
     * constructor.
     */
    public function __construct() {
        $this->dbConnect = new dbConnection();
    }


    /*****************FUNCTIONS ********************/

    /**
     * selects entitites in database.
     * @return array of entity objects.
     */
    public static function selectFiles($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection->find($filters, $options);
        return $cursor->toArray();
    
    }

    /**
     * inserts a new entity in database.
     */
    public static function insertFile($attributes){
        try {
            $c = new dbConnection();
            $connection = $c->getConnection(self::$COLLECTION_NAME); 
            $insertResult = $connection->insertOne($attributes);
            return $insertResult ;
            
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $_SESSION['errorData']['Error'][]="Error inserting file object";
        }
    }

    /**
     * updates entitiy in database.
     */
    public static function updateFile ($filters, $updateCommand) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> updateMany($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }










}
