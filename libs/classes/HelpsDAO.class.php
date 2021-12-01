<?php

/**
 * Helps collection persistence class.
 */
class HelpsDAO {

    /**
    * Encapsulates connection data to database.
    */
    private $dbConnect;

    /**
    * Collection name for entity.
    */
    private static $COLLECTION_NAME = 'helps';

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
    public static function selectHelps($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection->find($filters, $options);
        return $cursor->toArray();
    
    }

    /**
     * inserts a new entity in database.
     */
    public static function insertHelp ($attributes){
        try {
            $c = new dbConnection();
            $connection = $c->getConnection(self::$COLLECTION_NAME); 
            $insertResult = $connection->insertOne($attributes);
            return $insertResult ;
            
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $_SESSION['errorData']['Error'][]="Error inserting help object";
        }
    }

    /**
     * updates entitiy in database.
     */
    public static function updateHelp ($filters, $updateCommand) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> updateMany($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }


}
