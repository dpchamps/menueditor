<?php
/**
 * Created by PhpStorm.
 * User: Dave
 * Date: 3/24/2015
 * Time: 6:40 PM
 */
require_once(__DIR__ . './../Models/Database.class.php');
class List_functions {
    private $db;


    public function swap_menu_items($table_name, $id_1, $id_2){
        $lo_1 = $this->db->select_single_item('list_order', $table_name, Array('id' => $id_1));
        $lo_2 = $this->db->select_single_item('list_order', $table_name, Array('id' => $id_2));

        $this->db->update($table_name, $id_1, Array('list_order' => $lo_2));
        $this->db->update($table_name, $id_2, Array('list_order' => $lo_1));
    }
    /*
     * function majke_sequential_arr
     *
     * strips first level keys, returns an array
     */
    public function make_sequential_arr($arr){
        $s_arr = Array();
        foreach($arr as $val){
            array_push($s_arr, $val);
        }
        return $s_arr;
    }
    private function remove_deleted_items($arr){
        $pruned = Array();
        foreach($arr as $val){
            if((int)$val['list_order'] === 0){
                continue;
            }
            array_push($pruned, $val);
        }
        return $pruned;
    }
    private function show_deleted_items($arr){
        $pruned = Array();
        foreach($arr as $val){
            if((int)$val['list_order'] === 0){
                array_push($pruned, $val);
            }
        }
        return $pruned;
    }

    public function collapse($arr){
        $unique_array = Array();

        foreach($arr as $idx => $item){
            $id = (int)$item['id'];
            if (array_key_exists($id, $unique_array) ){
                array_push($unique_array[$id]['descriptions'], Array(
                    'text' => $item['description'],
                    'id' => $item['desc_id'],
                    'price' => $item['subprice']
                ));
            }else{
                $unique_array[(int)$id] = Array(
                    'title' => $item['title'],
                    'descriptions' => Array(
                        Array(
                            'text' => $item['description'],
                            'id' => $item['desc_id'],
                            'price' => $item['subprice']
                        )
                    ),
                    'price' => $item['price'],
                    'header' => $item['header'],
                    'list_order' => $item['list_order'],
                    'id' => $item['id']
                );
            }
        }

        return $this->make_sequential_arr($unique_array);
    }

    public function build_response($arr){
        return $this->remove_deleted_items( $this->collapse($arr) );
    }

    public function show_removed($arr){
        return $this->show_deleted_items( $this->collapse($arr) );
    }

    public function order_menu_cms($raw_array){
        $return_array = Array();
        $group_array = Array();
        foreach($raw_array as $key => $value){
            $title = $value['title'];
            $content = Array(
                'id' => $value['id'],
                'header' => $value['header'],
                'list_order' => $value['list_order'],
                'title' => $value['title'],
                'description' => $value['description'],
                'desc_id' => $value['desc_id'],
                'price' => $value['price'],
                'subprice' => $value['subprice'],
                'subprice_desc_id' => $value['subprice_id']
            );
            if((int)$value['list_order'] === 0){
                continue;
            }

            if(array_key_exists($title, $group_array)){
                array_push($group_array[$title], $content);
            }else{
                $group_array[$title] = Array($content);
            }

        }

        foreach($group_array as $item){
            $header = $item[0];
            $header = $header['header'];
            $title = $item[0];
            $title = $title['title'];


            if(!array_key_exists($header, $return_array)){
                $return_array[$header] = Array();
            }
            $key = &$return_array[$header];
            $key[$title] = $item;


        }
        return $return_array;

    }

    public function __construct(){
        $this->db = Database::get_instance();
    }
} 