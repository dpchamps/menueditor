<?php

require_once __DIR__.'./../Models/User.class.php';
require_once __DIR__.'./../Models/Database.class.php';
require_once __DIR__.'./../Models/SQL_Statements.class.php';
require_once __DIR__.'./../Models/Cms.class.php';
require_once __DIR__.'./../Models/Utilities.class.php';
class Pages {
    private $db;
    private $lists;
    private $sql;
    private $util;
    private $args;
    private $method;
    private $item;
    private $cms;

    public function __construct($args, $method){
        $this->args = $args;
        $this->method = $method;

        $this->db = Database::get_instance();
        $this->lists = new List_functions();
        $this->sql = new SQL_Statements();
        $this->util = new Utilities($this->method);
        $this->cms = new Cms();
    }
    /*
     * case 'menu'
     */
    private function menu_item_edit(){
        $id = (is_array($this->item)) ? $this->item['id'] : $this->item;
        $id = $this->util->check($id);

        if(!$id){
            //throw new Exception(400);
        }
        $response = $this->item();
        switch($this->method){
            case('PUT'):
                $this->cms->item_edit($this->item);
                break;
            case('POST'):
                $this->item['menu_type'] = $this->args[0];
                $this->cms->add_item($this->item);
                break;
            case('DELETE'):
                //change list_order to zero
                $this->cms->remove_item('menu_items', $id);
                break;
        }
        return $response;
    }
    private function item(){
        $menu = $this->menu();
        $item = NULL;
        $search = array_key_exists('id', $this->item) ? $this->item['id'] : $this->item;
        foreach($menu as $val){
            if($search === $val['id']){
                $item = $val;
                break 1;
            }
        }
        return $item;
    }
    private function menu_item(){
        if($this->method === 'GET'){
            //$item = $menu[(int)($this->item)-1];
            $item = $this->item();
            if($item){
                return $item;
            }else{
                throw new Exception(404);
            }
        }else{
            return $this->menu_item_edit();
        }

    }

    private function show_types(){
        $type = $this->sql->get('menu_type', $this->args[0]);
        $type = $this->db->fetch_all_query($type);
        if (count($type) > 0) {
            $type = $type[0];
            $type = $type['id'];
            $sql = "SELECT header FROM menu_headers WHERE menu_type=$type";
            $response = $this->db->fetch_all_query($sql);
        }else{
            throw new Exception(404);
        }
        return $response;
    }

    private function restore_item($id){
        $this->cms->undelete('menu_items', $id);
    }

    public function show_removed($menu_type=NULL){
        $restore_id = FALSE;
        if($menu_type === NULL){
            $menu_type = $this->args[0];
            $menu_type = $this->sql->get('menu_type', $menu_type);
            $menu_type = $this->db->fetch_all_query($menu_type);
            $menu_type = $menu_type[0];
            $menu_type = $menu_type['id'];
            $restore_id = $this->util->check($this->args[2]);
        }else{
            $restore_id = $this->util->check($this->args[1]);
        }

        $response = NULL;

        $removed_items = $this->sql->get('removed_items', $menu_type);
        $removed_items = $this->db->fetch_all_query($removed_items);
        $removed_ids = Array();
        foreach($removed_items as $val){
            if(array_key_exists('id', $val)){
                array_push($removed_ids, $val['id']);
            }
        }
       if(count($removed_ids) > 0){
           $full_menu = $this->sql->get('menu', $menu_type);
           $full_menu = $this->db->fetch_all_query($full_menu);
           $full_menu =  $this->lists->show_removed($full_menu);
           if($this->method === 'PUT' && $restore_id){
               $match = Array();
               $ids = Array();
               foreach($full_menu as $val){
                   if((int)$val['id'] === (int)$restore_id || $restore_id === 'all'){
                       $this->restore_item($val['id']);
                       array_push($match, $val);
                       array_push($ids, $val['id']);
                   }
               }
               if(count($match) > 0){
                   $ids = array_unique($ids);
                   foreach($ids as $val){
                       $header_id = $this->cms->get_header_id('menu_items', $val);
                       $this->cms->reorder_section($header_id);
                   }
                   $response = $match;
               }else{
                   throw new Exception(404);
               }
           }else{
               $response = $full_menu;
           }
       }else{
           $response = NULL;
       }
        return $response;
    }
    public function permanent_delete(){
        if(!$this->util->check($this->args[1]) || $this->method !== 'DELETE'){
            throw new Exception(400);
        }
        $id = $this->args[1];
        $check = $this->db->fetch_all_query("SELECT id FROM menu_items WHERE id='$id'");
        if(count($check) === 0){
            throw new Exception(404);
        }
        $query = $this->sql->get('permanent_delete', $id);
        $response = $this->db->query($query);
        return Array(
            'id' => $id
        );
    }
    private function available_menus(){
        $item = $this->item;
        $sql =  $this->sql->get('available_menus');
        $available_menus = $this->db->fetch_all_query($sql);
        if($item){
            if($this->util->check($available_menus[$item-1])){
                return $available_menus[$item-1];
            }else{
                throw new Exception(404);
            }

        }else{
            return $available_menus;
        }
    }
    private function menu(){
        $type = $this->sql->get('menu_type', $this->args[0]);
        $type = $this->db->fetch_all_query($type);
        if ($type !== false) {
            $type = $type[0];
            $type = $type['id'];
            $sql_query = $this->sql->get('menu', $type);
            $raw_array = $this->db->fetch_all_query($sql_query);
            $raw_array = $this->lists->build_menu($raw_array);
            ;        }else{
            throw new Exception(404);
        }
        return $raw_array;
        //return $this->lists->order_menu_array($raw_array);

    }
    public function get_menu($item)
    {
        array_shift($this->args);
        // api/pages/menus/food/arg[1]
        if($this->args[1] === 'restore'){
            return $this->show_removed();
        }
        if($this->args[0] === 'remove'){
            return $this->permanent_delete();
        }
        if($this->args[1] === 'types'){
            return $this->show_types();
        }
        $this->item = $item ? $item : $this->args[1];
        if($this->item){
            return $this->menu_item();
        }
        if (!isset($this->args[0])) {
            return $this->available_menus();
        }
        return $this->menu();
    }


}
