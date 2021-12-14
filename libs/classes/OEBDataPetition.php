<?php
class OEBDataPetition {

    private $_id;
    private $filesIds;
    private $requester;
    private $approvers;
    private $current_status;
    private $history_actions;
    private $oeb_metadata;
    private $visualitzation_url;
    private $dataset_OEBid;

    //connection collections attributes
    private static $COLLECTION_NAME = 'oeb_publication_registers';
    private $dbConnect;

    
    public function __construct(array $filesIds, $requester, $approvers, 
        $oeb_metadata, $visualitzation_ur = null, $dataset_OEBid = null){
        
        $this->dbConnect = new dbConnection();
        
        $this->_id = createLabel2('vre-oebreq', $this->dbConnect->
            getConnection(self::$COLLECTION_NAME));
        $this->filesIds = $filesIds;
        $this->requester = $requester;
        $this->approvers = $approvers;
        $this->current_status = "pending approval";
        $this->history_actions = array(new historyActions("request", $requester));
        $this->oeb_metadata = $oeb_metadata;
        $this->visualitzation_url = $visualitzation_url;
        $this->dataset_OEBid = $dataset_OEBid;


    }

    /************GETTERS AND SETTERS ****************/

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
     * Get the value of filesIds
     */
    public function getFilesIds(){
        return $this->filesIds;
    }

    /**
     * Set the value of filesIds
     * @return  self
     */
    public function setFilesIds($filesIds){
        $this->filesIds = $filesIds;
        return $this;
    }

    /**
     * Get the value of requester
     */
    public function getRequester(){
        return $this->requester;
    }

    /**
     * Set the value of requester
     * @return  self
     */
    public function setRequester($requester){
        $this->requester = $requester;
        return $this;
    }

    /**
     * Get the value of approvers
     */
    public function getApprovers(){
        return $this->approvers;
    }

    /**
     * Set the value of approvers
     * @return  self
     */
    public function setApprovers($approvers){
        $this->approvers = $approvers;
        return $this;
    }

    /**
     * Get the value of current_status
     */
    public function getCurrentStatus(){
        return $this->current_status;
    }

    /**
     * Set the value of current_status
     * @return  self
     */
    public function setCurrentStatus($current_status){
        $this->current_status = $current_status;
        return $this;
    }

    /**
     * Get the value of history_actions
     */
    public function getHistoryActions() :array{
        return $this->history_actions;
    }

    /**
     * Set the value of history_actions
     * @return  self
     */
    public function setHistoryActions($history_actions){
        $this->history_actions = $history_actions;
    }

    /**
     * Get the value of oeb_metadata
     */
    public function getOebMetadata(){
        return $this->oeb_metadata;
    }

    /**
     * Set the value of oeb_metadata
     * @return  self
     */
    public function setOebMetadata($oeb_metadata){
        $this->oeb_metadata = $oeb_metadata;
        return $this;
    }

    /**
     * Get the value of visualitzation_url
     */
    public function getVisualitzationUrl(){
        return $this->visualitzation_url;
    }

    /**
     * Set the value of visualitzation_url
     * @return  self
     */
    public function setVisualitzationUrl($visualitzation_url){
        $this->visualitzation_url = $visualitzation_url;
        return $this;
    }

    /**
     * Get the value of dataset_OEBid
     */
    public function getDatasetOEBid(){
        return $this->dataset_OEBid;
    }

    /**
     * Set the value of dataset_OEBid
     * @return  self
     */
    public function setDatasetOEBid($dataset_OEBid){
        $this->dataset_OEBid = $dataset_OEBid;
        return $this;
    }


    /**
     * Converts obj properties to array
     */
    public function toArray() {
        
        return array(
            '_id' => $this->_id,
            'filesIds' => $this->filesIds,
            'requester' => $this->requester,
            'approvers' => $this->approvers,
            'current_status' => $this->current_status,
            'history_actions' => array($this->history_actions[0]->toArray()),
            'oeb_metadata' => $this->oeb_metadata,
            'visualitzation_url' => $this->visualitzation_url,
            'dataset_OEBid' => $this->dataset_OEBid

        );
    }

    /*****************FUNCTIONS ********************/

    /**
    * Saves OEBDataPetition object on mongodb
    */
    public function saveOEBPetition() {
        try {
            $connection = $this->dbConnect->getConnection(self::$COLLECTION_NAME); 
            $insertResult = $connection->insertOne($this->toArray());
            return $insertResult ;
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $_SESSION['errorData']['Error'][]="Error inserting OEB petition";
        }
    }

    /**
     * Selects OEBDataPetitions
     * @param filters - filter petitions
     * @param options 
     * @return array of OEB petitions
     */
    public static function selectAllOEBPetitions($filters, $options = []) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection->find($filters, $options);
        return $cursor->toArray();
    }
    
    /**
     * Updates OEBDataPetitions
     * @param filters - filter petitions
     * @param updateCommand 
     * @return int of registers modified
     */
    public static function updateOEBPetitions($filters, $updateCommand) {
        $c = new dbConnection();
        $connection = $c->getConnection(self::$COLLECTION_NAME); 
        $cursor = $connection-> updateMany($filters, $updateCommand);
        return $cursor->getModifiedCount();
    }
}

class historyActions {
    private $action;
    private $user;
    private $timestamp;
    private $log;

    public function __construct($action, $user, $log = null){
        
        $this->action = $action;
        $this->user = $user;
        $this->timestamp = new \MongoDB\BSON\UTCDateTime();
        $this->log = $log;
    }

    /**
     * Get the value of action
     */
    public function getAction(){
        return $this->action;
    }

    /**
     * Set the value of action
     * @return  self
     */
    public function setAction($action){
        $this->action = $action;
        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * Set the value of user
     * @return  self
     */
    public function setUser($user){
        $this->user = $user;
        return $this;
    }

    /**
     * Get the value of timestamp
     */
    public function getTimestamp(){
        return $this->timestamp;
    }

    /**
     * Set the value of timestamp
     * @return  self
     */
    public function setTimestamp($timestamp){
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * Get the value of log
     */
    public function getLog(){
        return $this->log;
    }

    /**
     * Set the value of log
     * @return  self
     */
    public function setLog($log){
        $this->log = $log;
    }

    /**
     * Converts obj properties to array
     */
    public function toArray() {
        return array(
            'action' => $this->action,
            'user' => $this->user,
            'timestamp' => $this->timestamp,
            'log' => $this->log
        );
    }


}