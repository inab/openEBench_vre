<?php
class Notification {
    
    private $_id;
    private $receiver;
    private $content;
    private $created_at;
    private $is_seen;
    private $redirectOnClick;
    
    //connection collections attributes
    private static $COLLECTION_NAME = 'notifications';
    private $dbConnect;

    public function __construct($receiver, $content, $redirectOnClick){ 
        $this->dbConnect = new dbConnection();

        $this->_id = createLabel2('Not', $this->dbConnect->getConnection(self::$COLLECTION_NAME));
        $this->receiver = $receiver;
        $this->content = $content;
        $this->created_at = new MongoDB\BSON\UTCDateTime;
        $this->is_seen = 0;
        $this->redirectOnClick = $redirectOnClick;

        //clean old notifications
        if($GLOBALS['notifications_clean_period'] !=-1){
            self::cleanNotifications($GLOBALS['notifications_clean_period']);
        }
        
    }

    /*******GETTERS AND SETTERS *******/
    /**
     * Get the value of id
     */ 
    public function getId(){
        return $this->_id;
    }

    /**
     * Set the value of id
     * @return  self
     */ 
    public function setId($id){
        $this->_id = $id;
        return $this;
    }

    /**
     * Get the value of receiver
     */ 
    public function getReceiver(){
        return $this->receiver;
    }

    /**
     * Set the value of receiver
     * @return  self
     */ 
    public function setReceiver($receiver){
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * Get the value of content
     */ 
    public function getContent(){
        return $this->content;
    }

    /**
     * Set the value of content
     * @return  self
     */ 
    public function setContent($content){
        $this->content = $content;
        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreated_at(){
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     * @return  self
     */ 
    public function setCreated_at($created_at){
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * Get the value of is_seen
     */ 
    public function getIs_seen(){
        return $this->is_seen;
    }

    /**
     * Set the value of is_seen
     */ 
    public function setIs_seen(int $is_seen){
        $this->is_seen = $is_seen;
    }

    /**
     * Get the value of redirectOnClick
     */
    public function getRedirectOnClick(){
        return $this->redirectOnClick;
    }

    /**
     * Set the value of redirectOnClick
     */
    public function setRedirectOnClick($redirectOnClick){
        $this->redirectOnClick = $redirectOnClick;
    }


    /*****************FUNCTIONS ********************/

    /**
    * Saves notification object on mongodb
    */
    public function saveNotification() {
        try {
            $connection = $this->dbConnect->getConnection(self::$COLLECTION_NAME); 
            unset($this->dbConnect);
            $insertResult = $connection->insertOne(get_object_vars($this));
            return $insertResult ;
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $_SESSION['errorData']['Error'][]="Error inserting notifications";
        }
    }

    /**
     * Selects notifications
     * @param filters - filter notifications
     * @param options 
     * @return array of notifications
     */
    public static function selectAllNotifications($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection->find($filters, $options);
        return $cursor->toArray();
    }
    
    /**
     * Updates notifications
     * @param filters - filter notifications
     * @param updateCommand 
     * @return int of registers modified
     */
    public static function updateNotifications($filters, $updateCommand) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> updateMany($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }

    /**
     * Deletes old notifications
     * @param days from current day from which to delete notifications
     */
    public static function cleanNotifications($periodDays) {
        $now = new DateTime();
        $now->sub(new DateInterval('P'.$periodDays.'D'));
        $now = $now->getTimestamp();
        $limitToClean = new MongoDB\BSON\UTCDateTime($now*1000);

        $filters = array('created_at' => array('$lt' => $limitToClean));
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> deleteMany($filters);
        //return $cursor->getModifiedCount();
    }

    
}
?>