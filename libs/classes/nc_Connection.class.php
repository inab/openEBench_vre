<?php

class nc_Connection {

    //attributes
    private $username;
    private $password;
    private $server;
    private $client;

    function __construct($username, $password, $server) {
        $settings = array(
            'baseUri' => 'https://dev-openebench.bsc.es/nextcloud/remote.php/dav/files/root/',
            'userName' => 'root',
            'password' => '***REMOVED***'
        );
    
        $this->client = new Client($settings);

        return $this->client;
    }


    public function ncUpload () {
        

    }










}