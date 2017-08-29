<?php
namespace REST;
require_once 'API.class.php';
require_once __DIR__.'./../Models/User.class.php';
require_once __DIR__.'./../Models/Database.class.php';


class REST_API extends \REST\API {
    protected $User;
    protected $Unverified_Endpoints = Array(
        "login",
        "get_content",
        "get_menu",
        "get_press",
        "get_page",
        "get_merch"
    );
    private function verify_user() {

        $User = new \Models\User();

        /**
         * In order for it to be a valid request, the user must have passed a unique token
         * and username pair to the server with the request
         */
        if (array_key_exists('token', $this->request) &&
            array_key_exists('username', $this->request) &&
            !$User->valid_token($this->request['token'], $this->request['username'])
        ) {
            throw new \Exception('Invalid User Token');
        }elseif (!array_key_exists('token', $this->request) ||
                 !array_key_exists('username', $this->request)){
            throw new \Exception('Please log in to complete this action');
        }

        $this->User = $User;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    private function is_method($type = ""){
        $type = strtoupper($type);
        if ( $this->method != $type ){
            throw new \Exception('Method only accepts ' . $type . " requests.");
        }
    }

    /**
     * @param $s
     *
     * instead of throwing an error all invalid keyvalue pairs are ignored
     */
    private function parse_url_key_value($s){
        $keyvalue_strings = explode('|', $s);
        $assoc_array = Array();
        foreach($keyvalue_strings as $pair){
            $pair = explode(':', $pair);
            if(sizeof($pair) > 1){
                $assoc_array[$pair[0]] = $pair[1];
            }
        }

        return $assoc_array;
    }
    private function parse_url_array($s){
        return explode('|', $s);
    }

    public function __construct($request) {
        parent::__construct($request);
        //first check the endpoint method name, read is allowed without an api key or token
        if(in_array($this->endpoint, $this->Unverified_Endpoints)){
            //can add security measures here to ensure someone isn't spamming the system
        }else{
            $this->verify_user();
        }
    }
    /*
     * A general gdet method for querying a database and getting content back.
     *
     *  Write an endpoint method that makes use of this
     *
     */
    private function get($table, $cols, $vals){
        //$this->is_method('GET');

        return \Models\Database::get_instance()->select($cols, $table, $vals);
    }
    /**
     * Endpoint methods
     */
    
    protected function login(){
        $this->is_method("POST");
        if(
            !array_key_exists('username', $this->request) ||
            !array_key_exists('password', $this->request)
        ){
            throw new \Exception('Please provide username and password');
        }
        $this->User = new \Models\User();
        $this->User->login($this->request['username'], $this->request['password']);

        return $this->User->get(Array('username', 'token'));

    }
    protected function logout(){
        $this->is_method('POST');
        $this->User->logout($this->request['username'], $this->request['token']);

        return $this->User->get(Array('username', 'token'));
    }

    protected function get_content(){
        $table = "";
        $cols  = "";
        $vals  = NULL;
        if(isset($this->args[0])){
            $table = $this->args[0];
        }else{
            throw new \Exception('No content specified');
        }
        if(isset($this->args[1])){
            $cols = $this->parse_url_array($this->args[1]);
        }
        if(isset($this->args[2])){
            $vals = $this->parse_url_key_value($this->args[2]);
        }

        //return $this->get($table, $cols, $vals)->fetch_all(MYSQLI_ASSOC);
        $result = $this->get($table, $cols, $vals);
        if($result === false){
            throw new \Exception("Content Not Found");
        }else{
            return $result->fetch_all(MYSQLI_ASSOC);
        }

    }

    protected function get_menu(){

        if(!isset($this->args[0])){
            throw new \Exception('Please specify content');
        }
        $menu_type = \Models\Database::get_instance()->select('id', 'menu_type', Array(
            'type' => $this->args[0]
        ));
        if($menu_type !== false){
            $menu_type = $menu_type->fetch_assoc()['id'];
        }

        $sql_query =
            "select
                menu_items.id, menu_headers.header as header, menu_items.title, menu_descriptions.description, menu_descriptions.id as desc_id, menu_prices.price as price, menu_subprices.sub_price as subprice
                from menu_items
                left outer
                	join menu_headers
                    on menu_items.header_id = menu_headers.id
                left outer
                    join menu_descriptions
                    on menu_descriptions.item_id = menu_items.id
                left outer
                    join menu_prices
                    on menu_prices.item_id = menu_items.id
                left outer
                    join menu_subprices
                    on menu_subprices.desc_id = menu_descriptions.id
                where
                    menu_items.menu_type_id = $menu_type
                order by
                    menu_items.id
                 ";

        $raw_array = \Models\Database::get_instance()->query($sql_query)->fetch_all(MYSQLI_ASSOC);

        $pruned_array = Array();
        $return_array = Array();

        $header = $raw_array[0]['header'];

        foreach($raw_array as $item){
            $zero_idx = $item['id']-1;

            if( array_key_exists($zero_idx, $pruned_array)){
                $desc = Array(
                    'text' => $item['description'],
                    'id' => $item['desc_id'],
                    'price' => $item['subprice']
                );

                array_push($pruned_array[ $zero_idx ]['descriptions'], $desc);
            }else{

                if($header != $item['header']){
                    $return_array[$header] = $pruned_array;
                    $header = $item['header'];
                    $pruned_array = Array();
                }

                $pruned_array[ $zero_idx ] = Array(
                    'title' => $item['title'],
                    'descriptions' => Array(
                        Array(
                        'text' => $item['description'],
                        'id' => $item['desc_id'],
                        'price' => $item['subprice']
                        )
                    ),
                    'price' => $item['price']
                );
            }

        }
        if(!isset($return_array[$header])){
            $return_array[$header] = $pruned_array;
        }
        return $return_array;
    }

    protected function get_page(){
        if(!isset($this->args[0])){
            throw new \Exception('Please Specify page data');
        }else{
            $page = $this->args[0];
        }

        $page_id = \Models\Database::get_instance()->select('id', 'page_data', Array(
            'title' => $page
        ))->fetch_all(MYSQLI_ASSOC);
        if(!$page_id){
            throw new \Exception("Page Not Found");
        }else{
            $page_id = $page_id[0]['id'];
        }
        $page_query = "
            select
                title,
                template,
                image_path as imagePath,
                default_image as defaultImage,
                default_background_image as defaultBackgroundImage,
                default_header as default_header,
                default_description as defaultDescription
            from page_data
            where id = $page_id";

        $page_data = \Models\Database::get_instance()->query($page_query)->fetch_all(MYSQLI_ASSOC);

        if(!$page_data){
            throw new \Exception('Page Not Found');
        }else{
            return $page_data[0];
        }
    }

    protected function get_press(){
        $clause = false;
        $response = Array();
        $query = "";
        if(isset($this->args[0])){
            $clause = $this->args[0];
        }

        if($clause){
            $item_id = \Models\Database::get_instance()->select('id', 'press_items', Array(
                'title' => $clause
            ))->fetch_all(MYSQLI_ASSOC);
            if($item_id){
                $item_id = $item_id[0]['id'];
                $text_query  = "
                    select

                        press_headers.content as header,
                        press_descriptions.content as description
                    from press_items
                    left outer join press_headers
                        on press_headers.item_id = press_items.id
                    left outer join press_descriptions
                        on press_descriptions.item_id = press_items.id
                    where press_items.id = $item_id";
                $image_query = "
                    select
                        press_images.path
                    from press_items
                    left outer join press_images
                        on press_images.item_id = press_items.id
                    where press_items.id = $item_id
                ";
                $response = \Models\Database::get_instance()->query($text_query)->fetch_all(MYSQLI_ASSOC)[0];
                $response['images'] = \Models\Database::get_instance()->query($image_query)->fetch_all(MYSQLI_ASSOC);

            }
        } else {
            $query = "
                SELECT
                     press_items.link,press_items.id, press_type.type as type, press_items.title
                FROM press_items
                LEFT OUTER
                    JOIN press_type
                    ON press_items.press_type_id = press_type.id
            ";
            $raw_array = \Models\Database::get_instance()->query($query)->fetch_all(MYSQLI_ASSOC);
            foreach($raw_array as $key){

                $type = $key['type'];
                if(!isset($response[$type])){
                    $response[$type] = Array();
                }
                array_push($response[$type], $key);
            }
        }
        return $response;
    }

    function get_merch(){
        $clause = false;
        $response = Array();
        if(isset($this->args[0])){
            $clause = $this->args[0];
        }

        if($clause){
            $item_id = \Models\Database::get_instance()->select('id', 'merch_items', Array('title' => $clause));
            if($item_id){
                $item_id = $item_id->fetch_all(MYSQLI_ASSOC)[0]['id'];
                $text_query = "
                    select
                        merch_headers.content as header,
                        merch_descriptions.content as description
                    from merch_items
                    left outer join merch_headers
                        on merch_headers.item_id = merch_items.id
                    left outer join merch_descriptions
                        on merch_descriptions.item_id = merch_items.id
                    where merch_items.id = $item_id
                    ";
                $image_query = "
                    select merch_images.path
                    from merch_images
                    where merch_images.item_id = $item_id";
                $response = \Models\Database::get_instance()->query($text_query)->fetch_all(MYSQLI_ASSOC)[0];
                $response['images'] = \Models\Database::get_instance()->query($image_query)->fetch_all(MYSQLI_ASSOC);
            }
        }else{
            $response = \Models\Database::get_instance()->select('title', 'merch_items', Array())->fetch_all(MYSQLI_ASSOC);
        }

        return $response;



    }


} 