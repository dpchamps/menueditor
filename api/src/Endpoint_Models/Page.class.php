<?php

require_once __DIR__.'./../Models/Database.class.php';
require_once __DIR__.'./../Models/SQLInterface.php';

class Page{

    private $args;
    private $page;
    private $method;
    private $db;
    private $sql;
    private $dbPrefix = "_";
    private $interface;
    public $response = NULL;

    private function toplevelMethods(){
        $data = (isset($this->request['data'])) ? $this->request['data'] : NULL;
        $table = (isset($data['table'])) ? $this->dbPrefix.$data['table'] : NULL;

        switch($this->method){
            case 'GET':
                $this->response = $this->interface->availablePages($this->dbPrefix);
                break;
            case 'PUT':

                if(!$table){
                    throw new Exception(400);
                }

                if($data['table'] == 'headers'){
                    $this->sql->query('update', $table, [
                        'header' => $data['name'],
                        'menu_type' => $data['menu_type_id'],
                        'id' => $data['id']
                    ]);
                }else if($data['table'] == 'type'){
                    $this->sql->query('update', $table, [
                        'title' => $data['title'],
                        'id' => $data['id']
                    ]);
                }

                break;
            case 'POST':

                if(!$table){
                    throw new Exception(400);
                }

                if($data['table'] == 'headers'){

                    $this->sql->query('create', $table, [
                        'header' => $data['name'],
                        'menu_type' => $data['menu_type_id']
                    ]);
                }else if($data['table'] == 'type'){
                    $this->sql->query('create', $table, [
                        'title' => $data['title']
                    ] );
                }

                break;
            case 'DELETE':
                if(!$table){
                    throw new Exception(400);
                }
                $this->sql->query('destroy', $table, ['id' => $data['id']]);
                break;
            default:
                throw new Exception(400);
        }
    }
    private function subpageTopLevel(){
        $action = $this->args[0];
        $object = $this->args[1];
        $subject = (isset($this->args[2])) ? $this->args[2] : NULL;
        $data   = (isset($this->request['data'])) ? $this->request['data'] : NULL;

        switch($this->method){

            case 'GET':
                $this->response = $this->interface->subpage_data($action);
                break;
            case 'POST':
                $this->response = $this->interface->addItem($action, $data);
                break;
            default:
                throw new Exception(400);
        }
    }

    private function subPageVerbGeneric($subpage, $object, $data){
        switch($this->method){
            case 'GET':
                $item = $this->interface->subpage_item($subpage, $object);

                if(!$item){
                    throw new Exception(404);
                }
                $this->response = $item;
                break;
            case 'PUT':
                $this->response = $this->interface->updateItem($subpage, $data, $object);
                break;
            case 'DELETE':
                $this->response = $this->interface->deleteItem($object);
                break;
            default:
                throw new Exception(400);
        }
    }

    private function subPageVerb__Removed($subpage, $id){
        switch($this->method) {
            case 'GET':

                $this->response = $this->interface->subpage_data($subpage, true);
                break;
            case 'PUT':
                if($id){
                    $this->response = $this->interface->restoreItem($id);
                }else{
                    throw new Exception(400);
                }
                break;
            case 'DELETE':
                if($id){
                    $this->response = $this->interface->deletePermanently($id);
                }else{
                    throw new Exception(400);
                }
                break;
            default:
                throw new Exception(400);
        }

    }

    private function subPageSecondLevel(){
        $action = $this->args[0];
        $object = $this->args[1];
        $subject = (isset($this->args[2])) ? $this->args[2] : NULL;
        $data   = (isset($this->request['data'])) ? $this->request['data'] : NULL;

        switch($object){
            case 'removed':
                $this->subPageVerb__Removed($action, $subject);
                break;
            default:
                $this->subPageVerbGeneric($action, $object, $data);
        }
    }

    private function processPageArgs(){
        //default response is default
        $this->response = $this->interface->defaultResponse();

        if($this->page){
            if( empty($this->args) ){
                $this->toplevelMethods();
            }else if(count($this->args) == 1 ){
                $this->subpageTopLevel();
            }else{
                $this->subPageSecondLevel();
            }

        }
    }

    public function __construct($args, $method, $request)
    {
        // api/pages/arg[0]/arg[1]/...arg[n]
        $this->page = array_shift($args);
        $this->dbPrefix = $this->page.$this->dbPrefix;

        //args -> ../$page/arg[n]
        $this->args = $args;
        $this->method = $method;
        $this->request = $request;

        $this->db = Database::get_instance();
        $this->interface = new SQLInterface($this->dbPrefix);
        $this->sql = new SQL_Statements();
        $this->list =  new List_functions();

        //handle request
        $this->processPageArgs();
    }
}