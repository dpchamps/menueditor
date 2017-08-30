<?php

require_once 'API.class.php';
require_once __DIR__.'./../Models/User.class.php';
require_once __DIR__.'./../Models/Database.class.php';
require_once __DIR__.'./../Models/SQL_Statements.class.php';
require_once __DIR__.'./../Models/Utilities.class.php';
require_once __DIR__.'./../Models/Backup.class.php';
//require endpoint models
require_once __DIR__.'./../Endpoint_Models/Page.class.php';

class REST_API extends API
{
    protected $User;
    private $db;
    private $lists;
    private $sql;
    private $util;
    private $backup;

    private function check_auth_session(){
        $user = $_SERVER['PHP_AUTH_USER'];
        $pw = $_SERVER['PHP_AUTH_PW'];
        $valid = $this->User->valid_token($pw, $user);
        if( !$valid ){
            throw new Exception(401);
        }else{
            return $valid;
        }

    }
    private function authorize_user(){
        $protected_methods = Array(
            'PUT', 'PUSH', 'PATCH', 'DELETE', 'POST'
        );
        //first, enforce login for protected methods,
        //  i.e., the user is altering (or attempting) something...
        if(in_array($this->method, $protected_methods)){
            $this->check_auth_session();
        }
        /*
        Next, the user isn't trying to alter anythign, but is sending auth headers.
          So, if they're wrong, issue a 401.
        Exception: if the user is trying to login, we handle the auth differently;

        Note to self: At the moment, it seems that we should only check the auth session if AUTH_USER and AUTH_PW
              is present
        */
        elseif($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW'] && $this->endpoint !== 'login'){
            $this->check_auth_session();
        }

    }
    private function initialize(){

    }
    private function _escapeInputs($data){
        $clean_input = Array();
        if( is_array($data) ){
            foreach($data as $key => $value){
                $clean_input[$key] = $this->_escapeInputs($value);
            }
        } else{
            $clean_input = $this->db->get_connection()->real_escape_string($data);
        }
        return $clean_input;
    }
    public function __construct($request)
    {
        parent::__construct($request);
        //Database instance
        $this->initialize();
        $this->db = Database::get_instance();

        //models
        $this->lists = new List_functions();
        $this->sql = new SQL_Statements();

        $this->util = new Utilities($this->method);
        $this->User = new User();
        //endpoint models
        $this->backup = new Backup();
        $this->authorize_user();
        $this->request = $this->_escapeInputs($this->request);
    }
    /**
     * Endpoint methods
     */

    protected function API()
    {
        return Array(
            'version' => '',
            'site' => '',
            'links' => Array(
                'pages' => Array(
                    'ref' => SERVER_ROOT.'/pages',
                    'description' => "Retrieve page data."
                )
            )
        );
    }
    protected function login()
    {
        $this->util->allowed_methods('GET');
        if($this->method === 'OPTIONS'){
            return NULL;
        }
        $user = $_SERVER['PHP_AUTH_USER'];
        $pw = $_SERVER['PHP_AUTH_PW'];
        if(!$user || !$pw){
            throw new Exception(401);
        }
        $this->User = new User();
        $this->User->login($user, $pw);
        return Array('username'=>$this->User->username, 'token'=>$this->User->token);
    }

    protected function check_login()
    {
        $response = false;
        $this->util->allowed_methods($this->method, "GET");
        if($this->method === "OPTIONS"){
            return null;
        }
        return $this->check_auth_session();
    }

    protected function logout()
    {
        $this->util->allowed_methods('GET');
        if($this->method === 'OPTIONS'){
            return NULL;
        }
        if($this->check_auth_session()){
            $this->User->logout();
        }else{
            throw new Exception(400);
        }
    }
    /*
     * url structure:
     *
     *  /pages
     *      returns array of pages that exist
     *  _____________________
     *  /pages/menus
     *      returns an array of menus that exist
   */
    protected function pages(){
        //deal with options first
        if($this->method === 'OPTIONS'){
            $this->util->allowed_methods('GET PUT POST PATCH DELETE');
            return null;
        }

        $pageRequest = new Page($this->args, $this->method, $this->request);


        return $pageRequest->response;
    }

    public function user(){
        if($this->method === 'OPTIONS'){
            $this->util->allowed_methods('PUT POST');
            return null;
        }
        $option = $this->args[0];
        switch($option){
            case('change-password'):
                $old_pw = $this->util->check($this->request['password']);
                $new_pw = $this->util->check($this->request['new_password']);
                if(!$old_pw || !$new_pw){
                    //bad request, requires old and new
                    throw new Exception(400);
                }else{
                    $username = $this->User->username;
                    $this->User->login($username, $old_pw);
                    $this->User->change_password($new_pw);
                    return Array(
                        'username' => $this->User->username,
                        'token' => $this->User->token
                    );
                }
                break;
            case('change-username'):
                $pw = $this->util->check($this->request['password']);
                $new_username = $this->util->check($this->request['new_username']);
                if(!$pw || !$new_username){
                    throw new Exception(400);
                }else{
                    $this->User->change_username($new_username, $pw);
                    return Array(
                        'username' => $this->User->username,
                        'token' => $this->User->token
                    );
                }
                break;
            case('check-password'):
                $pw = $this->util->check($this->request['password']);
                if(!$pw){
                    throw new Exception(400);
                }else{
                    return $this->User->check_password($pw);
                }
            default:
                throw new Exception(404);
        }

    }
    public function backup(){
        if($this->method === 'OPTIONS'){
            $this->util->allowed_methods('PATCH GET PUT');
            return null;
        }
        switch($this->method){
            case "PUT":
                $this->backup->backup();
                break;
            case "PATCH":
                $restore_id = $this->util->required($this->args[0], 400);
                $this->backup->restore($restore_id);
                break;
            case "GET":
                return $this->backup->show_backups();
                break;
            default:
                throw new Exception(400);
        }
    }
}
