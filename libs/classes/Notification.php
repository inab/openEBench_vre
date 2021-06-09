<?php
class Notification {
    
    private $id;
    private $receiver;
    private $content;
    private $created_at;
    private $is_seen;

    public function __construct($id, $receiver, $content){
        
        $this->id = createLabel('Not', 'notificationsCol');
        $this->receiver = $receiver;
        $this->content = $content;
        $this->created_at = new MongoDB\BSON\UTCDateTime;
        $this->is_seen = 0;

        //clean old notifications
        $now = new DateTime();
        //substract 60 days from now
        $now->sub(new DateInterval('P60D'));
        $now=$now->getTimestamp();
        $limitToClean = new MongoDB\BSON\UTCDateTime($now*1000);
        self::cleanNotifications(array('created_at' => array('$lt' => $limitToClean)));
        

        
    }

    /*******GETTERS AND SETTERS *******/
    /**
     * Get the value of id
     */ 
    public function getId(){
        return $this->id;
    }

    /**
     * Set the value of id
     * @return  self
     */ 
    public function setId($id){
        $this->id = $id;
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
     * @return  self
     */ 
    public function setIs_seen(int $is_seen){
        $this->is_seen = $is_seen;
        return $this;
    }
    /**
     * Converts obj properties to array
     */
    public function toArray() {
        return array(
            '_id' => $this->id,
            'receiver' => $this->receiver,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'is_seen' => $this->is_seen
        );
    }

    /*****************FUNCTIONS ********************/

    /**
    * Saves notification object on mongodb
    */
    public function saveNotification() {
        $save = false;
	    $collection = $GLOBALS['notificationsCol'];
	    $insertResult = $collection->insertOne($this->toArray());
	    return $insertResult ;
    }

    /**
     * Selects notifications
     * @param filters - filter notifications
     * @param options 
     * @return array of notifications
     */
    public static function selectAllNotifications($filters, $options = []) {
        $collection = $GLOBALS['notificationsCol'];
        $cursor = $collection->find($filters, $options);
        return $cursor->toArray();
    }
    
    /**
     * Updates notifications
     * @param filters - filter notifications
     * @param updateCommand 
     * @return int of registers modified
     */
    public static function updateNotifications($filters, $updateCommand) {
        $collection = $GLOBALS['notificationsCol'];
        $cursor = $collection-> updateMany($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }

    public static function cleanNotifications($filters, $options =[]) {
        $collection = $GLOBALS['notificationsCol'];
        $cursor = $collection-> deleteMany($filters, $options);
        //return $cursor->getModifiedCount();
    }


   
}
?>