<?php
require_once __DIR__.'./../Models/Database.class.php';
require_once __DIR__.'./../Models/SQL_Statements.class.php';
require_once __DIR__.'./../Models/SQLInterface.php';
require_once __DIR__.'./../Models/List_functions.class.php';
require_once __DIR__.'./../Models/Utilities.class.php';

class SQLInterface
{
    private $db;
    private $list;
    private $sql;
    private $util;
    private $queryPrefix;
    private function parseItemData($data){
        return [
            'id'            => $this->util->required($data['id'], 400),
            'title'         => $this->util->check($data['title']),
            'header'        => $this->util->check($data['header']),
            'price'         => $this->util->check($data['price']),
            'descriptions'  =>$this->util->check($data['descriptions'])
        ];
    }

    /*
     * sets first item in section to 1, proceeds from there
     */
    public function reset_list_order($header_id){
        $start = 1;
        $items = $this->db->fetch_all_query("SELECT id FROM $this->queryPrefix.items WHERE header_id=$header_id ORDER BY id");
        foreach($items as $val){
            $this->db->update('menu_items', $val['id'], Array('list_order' => $start++));
        }
    }
    /*
     * Reorders a section of the menu given a header id,
     * This method skips over list orders with the value of zero
     */
    public function reorder_section($header_id){

        $list = $this->db->fetch_all_query("SELECT id, list_order FROM ".$this->queryPrefix."items WHERE header_id=$header_id ORDER BY id");
        $new = Array();
        $start = 0;
        foreach($list as $val){
            $order = $val['list_order'];
            if((int)$order !== 0){
                array_push($new, Array(
                    'id' => $val['id'],
                    'list_order' => ++$start
                ));
            }
        }
        foreach($new as $val){
            $id = $val['id'];
            $order = $val['list_order'];
            $this->db->update('menu_items', $id, Array('list_order' => $order));
        }
    }
    /*
     * MENU TYPES
     */

    public function get_menu_type_by_id( $id ){
        return $this->db->select_single_item('title', $this->queryPrefix."type", ['id' => $id]);
    }

    /*
     * HEADERS
     */
    public function get_header_id_by_id($item_id){
        return $this->db->select_single_item('header_id', $this->queryPrefix."items", Array('id' => $item_id));
    }
    public function get_header_info_by_title($header_title){
        $table = $this->queryPrefix."headers";


        return $this->db->fetch_all_query( $this->sql->read($table, [
            'menu_type' => 1,
            'id' => 1,
            'term' => 'header',
            'match' => $header_title
        ]) );
    }
    /*
     * Kind of a heavy lifter...
     *  First, check the headers table to make sure the id exists.
     *   If it doesn't, it probably needs to be created first, which is the job for a POST. So throw a Bad Request
     *
     *   If it does, then the item's `header_id`, and `menu_type` get updated
     *      Menu type should be inaccessable to the user, an item needs to live inside of a header
     */
    public function update_header($header, $id){
        $header_info = $this->get_header_info_by_title($header);

        if(empty($header_info) ){
            throw new Exception(400 );
        }

        $header_info = $header_info[0];
        $table = $this->queryPrefix."items";

        $this->sql->query('update', $table, [
            'header_id' => $header_info['id'],
            'menu_type_id' => $header_info['menu_type'],
            'id' => $id
        ]);


        return $this->get_menu_type_by_id( $header_info['menu_type'] );
    }
    /*
     * DESCRIPTIONS
     */
    private function delete_description($id){

        $table = $this->queryPrefix."descriptions";
        $this->sql->query("destroy", $table, ['id' => $id]);

    }
    private function update_description($description_id, $item_id, $text){

        $vals = "";
        if(!$description_id){
            $vals = "(NULL, '$item_id', '$text')";
        }else{
            $vals = "('$description_id', '$item_id', '$text')";
        }
        $this->sql->query('update_descriptions', [
            'prefix' => $this->queryPrefix,
            'vals'   => $vals,
            'text'   => $text
        ]);
    }
    private function update_descriptions($descriptions, $item_id){
        foreach($descriptions as $desc){
            $id = $this->util->check($desc['id']);
            $text = $this->util->check($desc['text']);
            $price = $this->util->check($desc['price']);

            if(!$text){
                $this->delete_description($id);
                $this->delete_subprice($id);
            }else{
                $this->update_description($id, $item_id, $text);
                $this->update_subprices($id, $price);
            }
        }
    }
    /*
     * PRICES, SUBPRICES
     */
    private function update_price($price, $id){
        $table = $this->queryPrefix."prices";
        $this->sql->query('update', $table, ['price' => $price, 'id_name' => 'item_id', 'id' => $id]);
    }
    private function update_subprices($id, $price){
        if(!$id){
            $id = $this->db->get_connection()->insert_id;
        }
        $sid = $this->db->select_single_item('id', 'menu_subprices', Array('desc_id'=> $id));
        $vals = ($sid) ? "('$sid', '$id', '$price')" : "(NULL, '$id', '$price')";
        $this->sql->query('update_subprices', [
            'prefix' => $this->queryPrefix,
            'vals'   => $vals,
            'price'  => $price
        ]);
    }
    private function delete_subprice($id){
        $table = $this->queryPrefix."subprices";
        $this->sql->query('destroy', ['id' => $table]);
    }
    private function addPriceToDatabase($item_id, $price){
        $table = $this->queryPrefix."prices";
       $this->sql->query('create', $table, [
            'id'        => 0,
            'item_id'   => $item_id,
            'price'     => (string)$price
        ]);


        return $this->db->get_connection()->insert_id;
    }
    /*
     * TITLES
     */
    private function update_title($title, $id){
        $table = $this->queryPrefix."items";
        $this->sql->query('update', $table, ['title' => $title, 'id' => $id]);
    }
    /*
     * ITEMS
     */
    private function addItemToDatabase($title, $header_id, $menu_type_id){
        $table = $this->queryPrefix."items";
        $this->sql->query('create', $table, [
            'id'           => 0,
            'title'        => $title,
            'header_id'    => $header_id,
            'menu_type_id' => $menu_type_id,
            'list_order'   => 1
        ]);

        return $this->db->get_connection()->insert_id;
    }

    /*
     * CRUD
     */

    public function addItem($page, $data){

        $data['id'] = 999;
        $data = $this->parseItemData($data);
        $data['header_id'] = $this->db->select_single_item('id', 'menu_headers', Array('header'=> $data['header']));
        $data['menu_type_id'] = $this->getSubpageType($page);

        $item_id = $this->addItemToDatabase($data['title'], $data['header_id'], $data['menu_type_id']);

        $this->addPriceToDatabase($item_id, $data['price']);

        if(is_array($data['descriptions'])){
            $this->update_descriptions($data['descriptions'], $item_id);
        }

        $this->reorder_section($data['header_id']);
        return $item_id;
    }

    public function updateItem($page, $data, $id){

        $data['id'] = $id;
        $data = $this->parseItemData($data);

        if($data['title']){
            $this->update_title($data['title'], $id);
        }

        if($data['price']){
            $this->update_price($data['price'], $id);
        }

        if($data['header']){
            $page = $this->update_header($data['header'], $id);

        }

        if(is_array($data['descriptions'])){
            $this->update_descriptions($data['descriptions'], $id);
        }

        return $this->subpage_item($page, $id);
    }

    public function deleteItem($id){
        $header_id = $this->get_header_id_by_id($id);
        $table = $this->queryPrefix."items";

        $this->db->update($table, $id, [ 'list_order' => '0' ]);
        $this->reorder_section($header_id);
        return 1;
    }
    public function restoreItem($id){
        $table = $this->queryPrefix."items";

        $this->db->query( $this->sql->update($table, ['id' => $id, 'list_order' => '1']) );
        $this->reorder_section( $this->get_header_id_by_id($id) );
        return 1;
    }
    public function deletePermanently($id){

        $table = $this->queryPrefix."items";
        $this->sql->query( 'destroy', $table, ['id' => $id] );

        return 1;
    }

    /*
     * PAGE DATA
     */
    public function getSubpageType($page){
        $query = $this->sql->get('subpage_id', [ 'prefix' => $this->queryPrefix, 'subpage' => $page ]);
        $response = $this->db->fetch_all_query( $query );
        if(empty($response)){
            $response = NULL;
        }else{
            $response = $response[0]['id'][0];
        }

        return $response;
    }
    public function subpage_data($subpage, $removed=false){
        $id = $this->getSubpageType($subpage);

        $data = $this->db->fetch_all_query( $this->sql->get('page_query', ['id' => $id, 'prefix' => $this->queryPrefix]) );

        return ($removed) ? $this->list->show_removed($data) : $this->list->build_response($data);
    }

    public function subpage_item($subpage, $item_id){
        $items = $this->subpage_data($subpage);

        return  array_filter($items, function($_item) use ($item_id){
            return $_item['id'] == $item_id;
        });
    }

    public function defaultResponse(){
        //default to top-level available pages
        return $this->db->fetch_all_query( $this->sql->get('available_pages', 'page_') );
    }

    public function availablePages( $page_prefix ){
        return $this->db->fetch_all_query( $this->sql->get('available_pages', $page_prefix ) );
    }



    public function __construct($prefix)
    {
        $this->db = Database::get_instance();
        $this->list = new List_functions();
        $this->sql = new SQL_Statements();
        $this->util = new Utilities();
        $this->queryPrefix = $prefix;
    }
}