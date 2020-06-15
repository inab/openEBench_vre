<?php

class JsonResponse {
    public  $code;  
    public  $message;
    public  $date;

    static $http_codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        422 => 'Unprocessable entity',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    );


    public function __construct($code=200, $message='OK'){
	$this->code    = (in_array($code,array_keys(self::$http_codes))?$code:200);
        $this->message = $message;
        $this->date    = date('m/d/Y h:i:s a', time());
    }

    public function setCode($code){
	$this->code  = (in_array($code,array_keys(self::$http_codes))?$code:200);
    }
    public function setMessage($message){
        $this->message = $message;
    }

    public function getResponse(){
	// update time
        $this->date    = date('m/d/Y h:i:s a', time());

	// set headers
	header_remove();                                                       // clear the old headers
	header("Access-Control-Allow-Origin: *");                              // CORS
    	header("Cache-Control: no-transform,public,max-age=300,s-maxage=900"); // make sure cache is forced
    	header('Content-Type: application/json; charset=UTF-8');               // treat this as json

	// set headers (HTTP code)
	http_response_code($this->code);                                       // set the actual code
	//$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	//header($protocol . ' ' . $this->code . ' ' . $this->http_codes[$this->code]);

	// set content: JSON
	$res = (array)get_object_vars($this);           // convert PHP-obj   to PHP-array
	return json_encode($res, JSON_PRETTY_PRINT);    // convert PHP-array to JSON
    }
} ?>
