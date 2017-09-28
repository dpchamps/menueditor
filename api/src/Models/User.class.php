<?php
/**
 * Created by PhpStorm.
 * User: Dave
 * Date: 1/15/2015
 * Time: 4:28 PM
 */


require_once 'Auth.class.php';

class User extends Auth{

    private $_token;
    public $token;
    public $username;
    private $_username;
    private $_logged_in = false;

    public function get_user_hash(){
        return $this->_db->select_single_item(
            'password',
            $this->login_table,
            ["username" => $this->username]
        );
    }
    public function get_user_id(){
        $this->_id = $this->_db->select_single_item(
            'id',
            $this->login_table,
            ["username" => $this->username]
        );
    }
    public function check_password($password){
        $hash = $this->get_user_hash($password);

        if(password_verify($password, $hash)){
            return true;
        }else{
            throw new Exception(400);            
        }
    }
    public function login($username, $password){
        //wrapped in try/catch because check_password return a 400 error, 
        //  but if a user fails a login that is explicitly an unauthorized error
        try{
            $this->username = $username;
            $this->check_password($password);
            $this->get_user_id();
            //create a new token, and timestamp
            $pair = $this->get_token_timestamp_pair();
            //insert token and timestamp pair into user table
            $this->_db->update(
                $this->login_table,
                $this->_id,
                $pair
            );
            $this->token = (string)$pair['token'];
            $this->_logged_in = true;
        }catch(Exception $e){
            throw new Exception(401);
        }
    }
    public function __construct(){
        parent::__construct();
    }
    public function valid_token($token, $username){
        $valid_token = false;
        $uid = $this->_db->select(
            'id',
            'users',
            Array('token' => $token, 'username' => $username)
        );

        $this->_id = $uid->fetch_assoc();
        $this->_id = $this->_id['id'];
        //make sure the query returned one result and the token is still valid
        //  if it is valid set class variables
        if($uid->num_rows == 1 &&
            !$this->is_inactive( $this->_id ) ){

            $valid_token = true;

            $this->username = $username;
            $this->token = $token;
        }
        //upon successful request, update the timestamp
        if($valid_token){
            $this->update_timestamp();
            return $valid_token;
        }else{
            throw new Exception(401);
        }
    }
    /*
     * logout()
     * this method should be called only after verifying a
     * valid un/token pair, so the internal variables will be set.
     * error otherwise
     */
    public function logout(){

        $this->_db->update(
            $this->login_table,
            $this->_id,
            Array('token' => NULL, 'token_timestamp' => NULL)
        );

        $this->token = NULL;
    }
    /*
     * create a user
     */
    public function create_user($username, $password){
        $hash = password_hash( $password, PASSWORD_DEFAULT );
        //check that the username doesn't exist
        $username_check = $this->_db->fetch_all_query("
            SELECT * FROM `users` WHERE users.username='$username'
        ");
        if(!empty($username_check)){
            throw new Exception(409);
        }
        $this->_db->query(" 
            INSERT INTO `users`(`id`, `username`, `password`)
            VALUES (NULL, '$username', '$hash')
        ");
        return true;
    }
    /*
     * change user password
     */
    public function change_password($new_pw){
        //insert new password
        $hash = password_hash($new_pw, PASSWORD_DEFAULT);
        $this->_db->update(
            $this->login_table,
            $this->_id,
            Array('password' => $hash )
        );
        var_dump( $this->_db->get_connection()->error );
        //update timestamp
        $this->update_timestamp();
    }
    /*
     * change username
     */
    public function change_username($new_username, $password){
        //check db to make sure username doesnt exist
        $exists = $this->_db->select_single_item('id',
            $this->login_table,
            Array('username'=> $new_username)
        );
        echo $exists;
        if(!$exists){
            $this->check_password($password);
            $this->_db->update(
                $this->login_table,
                $this->_id,
                Array('username'=>$new_username)
            );
            $this->login($new_username, $password);
            $this->username = $new_username;
            $this->update_timestamp();
        }else{
            throw new Exception(409);
        }
    }
}
