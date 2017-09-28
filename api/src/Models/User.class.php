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

    public function check_password($password){
        $id = $this->_db->select_single_item(
            'id',
            $this->login_table,
            Array(
               'username' => $this->username,
               'password' => MD5($password)
            )
        );
        if(!$id){
            throw new Exception(400);
        }else{
            return true;
        }
    }
    public function login($username, $password){

        //select from the users table the id where username and the MD5 hash of the password exist
        $query = $this->_db->select(
            'id',
            'users',
            Array(
                'username' => $username,
                'password' => MD5($password)
            )
        );
        $this->_id = $query->fetch_assoc();
        $this->_id = $this->_id['id'];

        if($query->num_rows == 1){
            //create a new token, and timestamp
            $pair = $this->get_token_timestamp_pair();
            //insert token and timestamp pair into user table
            $this->_db->update(
                $this->login_table,
                $this->_id,
                $pair
            );

            $this->token = (string)$pair['token'];
            //echo $this->_token;
            $this->username = $username;
            $this->_logged_in = true;
        }else{
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
       var_dump($this->_db->get_connection()->error);
        return true;
    }
    /*
     * change user password
     */
    public function change_password($new_pw){
        //insert new password
        $this->_db->update(
            $this->login_table,
            $this->_id,
            Array('password' => MD5($new_pw))
        );
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
