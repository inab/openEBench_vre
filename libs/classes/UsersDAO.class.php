<?php

/**
 * Users collection persistence class.
 */
class UsersDAO {

    /**
    * Encapsulates connection data to database.
    */
    private $dbConnect;

    /**
    * Collection name for entity.
    */
    private static $COLLECTION_NAME = 'users';

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
    public static function selectUsers($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection->find($filters, $options);
        return $cursor->toArray();
    
    }

    /**
     * inserts a new entity in database.
     */
    public static function insertUser($attributes){
        try {
            $c = new dbConnection();
            $connection = $c->getConnection(self::$COLLECTION_NAME); 
            $insertResult = $connection->insertOne($attributes);
            return $insertResult ;
            
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $_SESSION['errorData']['Error'][]="Error inserting user object";
        }
    }

    /**
     * updates entitiy in database.
     */
    public static function updateUser ($filters, $updateCommand, $options=[]) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> updateMany($filters, $updateCommand, $options);
        return $cursor->getModifiedCount();
    }

    public static function deleteUser ($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> deleteOne($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }










}

