<?php
require_once __DIR__.'./../Models/Database.class.php';

/**
 * Class SQL_Statements
 *
 * @method void query() query($name, $args, $item=NULL) execute sql query
 */
class SQL_Statements {

    private $database;

    private function left_outer($join, $on, $from, $firstId, $secondId){

        return "\nleft outer
          join $join
           on $on.$firstId = $from.$secondId
        ";
    }
    private function keyValQueryStrings( $item ){
        $keys = implode(', ', array_keys($item));
        $values = implode("', '", array_values($item) );

        return [
            $keys,
            $values
        ];
    }

    private function removed_items($menu_type_id){
        return "SELECT id FROM menu_items WHERE list_order='0' AND  menu_type_id LIKE '$menu_type_id'";
    }
    private function is_empty($s){
        $b = false;
        if($s === ""){
            $b = true;
        }
        return $b;
    }

    private function subpage_id($args){
        $subpage = $args['subpage'];
        $page = $args['prefix']."type";

        return "SELECT id FROM $page WHERE title='$subpage'";
    }


    private function page_query($args){
        $id = $args['id'];
        $prefix = $args['prefix'];

        $items = $prefix."items";
        $headers = $prefix."headers";
        $descriptions = $prefix."descriptions";
        $prices = $prefix."prices";
        $sub_prices = $prefix."subprices";

        $statement = "select
                $items.list_order, $items.id, $headers.header as header, $items.title, 
                $descriptions.description, $descriptions.id as desc_id, $descriptions.list_order as desc_order, $prices.price as price,
                $sub_prices.sub_price as subprice, $sub_prices.desc_id as subprice_id 
                
                from $items
                ";
        $statement.= $this->left_outer($headers, $items, $headers, "header_id", "id");
        $statement.= $this->left_outer($descriptions, $descriptions, $items, "item_id", "id");
        $statement.= $this->left_outer($prices, $prices, $items, "item_id", "id");
        $statement.= $this->left_outer($sub_prices, $sub_prices, $descriptions, "desc_id", "id");

        $statement.= "
            \nWHERE
                $items.menu_type_id LIKE '$id'
            ORDER BY
                $items.header_id, $items.list_order
        ";

        return $statement;
    }

    private function available_pages($prefix){
        $term = $prefix."type";
        return "SELECT title FROM $term" ;
    }
    private function update_descriptions($args){
        $table = $args['prefix']."descriptions";
        $vals  = $args['vals'];
        $text  = $args['text'];

        return "INSERT INTO $table
                        (id, item_id, description)
                        VALUES
                        $vals
                        ON DUPLICATE KEY UPDATE
                        description = '$text'
                        ";
    }
    private function update_subprices($args){


        $table = $args['prefix']."subprices";
        $vals  = $args['vals'];
        $price = $args['price'];

        return "INSERT INTO $table
                    (id, desc_id, sub_price)
                    VALUES
                    $vals
                    ON DUPLICATE KEY UPDATE
                    sub_price = '$price'
                ";
    }


    /*
     * CRUD Implementations
     */
    //create
    public function create($table, $item){
        $strings = $this->keyValQueryStrings($item);

        return "INSERT INTO $table
                ($strings[0])
                VALUES
                ('$strings[1]')        
        ";
    }
    //read
    public function read($table, $item){
        $match = (isset($item['id'])) ? $item['match'] : 1;
        $term = ( isset($item['term']) ) ? $item['term'] : "1";

        unset($item['match']);
        unset($item['term']);

        $select = $this->keyValQueryStrings($item);

        return "SELECT $select[0]
                FROM $table
                WHERE $term='$match'
        ";
    }
    //update
    public function update($table, $item){
        $set = "";
        $id = (array_key_exists('id', $item)) ? $item['id'] : NULL;
        $id_name = ( isset($item['id_name']) ) ? $item['id_name'] : 'id';

        unset($item['id']);
        unset($item['id_name']);

        foreach($item as $key=>$value){
            $value = urlencode($value);
            $set .= "$key='$value' ";
        }
        $set = implode(', ', array_filter(explode(" ", $set), 'strlen'));
        $set = urldecode($set);

        return "UPDATE $table
                SET $set
                WHERE $table.$id_name=$id        
        ";
    }
    //destroy
    public function destroy($table, $item){
        $id = ( array_key_exists('id', $item) ) ? $item['id'] : NULL;

        return "DELETE FROM $table WHERE $table.id='$id'";
    }

    /*
     * Main Interface, using these functions are encouraged
     */

    public function get($name, $args){
        if( !method_exists($this, $name)){
            Throw new Error('500');
        }

        $query = call_user_func(array($this, $name), $args);

        return $query;
    }

    private function queryWithArrayArgs($name, $args){
        $query = $this->get($name, $args);
        $this->database->query($query);
    }
    private function queryWithTableItemArgs($name, $table, $item){
        if( !method_exists($this, $name)){
            Throw new Error('500');
        }
        $query = call_user_func(array($this, $name), $table, $item);
        $this->database->query($query);
    }
    /*
     * the query wrapper, for get and CRUD calls.
     *
     * A common pattern is
     *
     * $query = get_sql_statement
     * $database->query($query);
     *
     * So this is a wrapper function, providing two seperate calls:
     *
     *      SQL_Statements::query($func_name, $associative_array_of_arguments)
     *      - For the more complex sql statements, pass them an associative array
     *
     *      SQL_Statements::query($func_name, $table, $item)
     *      - For the more specific CRUD functions.
     *
     *  CRUD functions should be favored over the first, but some ops are too complex have specific use cases
     */
    public function __call($name, $args){
        switch($name){
            case 'query':
                if(count($args) === 3){

                    $this->queryWithTableItemArgs($args[0], $args[1], $args[2]);
                }
                if(count($args) == 2){
                    $this->queryWithArrayArgs($args[0], $args[1]);
                }
                break;
            default:
                //this doesn't come from the user, it's internal
                throw new Exception(500);
        }
    }

    public function __construct()
    {
        $this->database = Database::get_instance();
    }
} 