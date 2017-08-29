<?php

require_once __DIR__.'./../config.php';
abstract class API {
    protected $method = '';

    protected $endpoint = '';

    protected $args = Array();

    protected $file = Null;

    public function __construct($request){
        //check the config file to see if CORS is enabled
        if(ALLOW_CORS){
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS, HEAD, DELETE");
            header("Access-Control-Allow-Headers: Authorization, Content-Type");
        }
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE'){
                $this->method = 'DELETE';
            } else if($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT'){
                $this->method = 'PUT';
            } else {
                throw new Exception(406);
            }
        }

        switch($this->method) {
            case 'OPTIONS':
                //does anything need to go here?
                break;
            case 'HEAD':
                //??
                break;
            case 'DELETE':
            case 'PATCH':
            case 'PUT':
            case 'POST':
                if(isset($_SERVER['CONTENT_TYPE'])){
                    $data = $this->_contentType($_SERVER['CONTENT_TYPE']);
                }else{
                    $data = $_POST;
                }
                $this->request = $this->_cleanInputs($data);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            /*
            case 'PUT';
                $this->request = $this->_cleanInputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            */
            default:
                throw new Exception(405);
                break;
        }
    }

    public function processAPI(){
        if($this->endpoint === ''){
            $this->endpoint = 'API';
        }

        $reflection = new ReflectionMethod($this, $this->endpoint);
        if((int)method_exists($this, $this->endpoint) > 0 &&
            !$reflection->isPrivate()){
            return $this->_response($this->{$this->endpoint}($this->args));
        }else{
            throw new Exception(404);
        }
    }
    private function _contentType($header){
        $header = explode(';', $header);
        $header = $header[0];
        $response = "";
        switch($header){
            case "multipart/form-data":
            case "application/x-www-form-urlencoded":
                $response = $_POST;
                break;
            case "text/plain":
            case "application/json":
                $response = json_decode(file_get_contents("php://input"), true);
                break;

            default:
                throw new Exception("Unsupported mime type: $header");
                break;
        }

        return $response;
    }

    private function _response($data, $status = 200){
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data){
        $clean_input = Array();
        if( is_array($data) ){
            foreach($data as $key => $value){
                $clean_input[$key] = $this->_cleanInputs($value);
            }
        } else{
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code){
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }
}
