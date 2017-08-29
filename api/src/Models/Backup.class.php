<?php
/**
 * Created by PhpStorm.
 * User: dave
 * Date: 2/12/16
 * Time: 2:22 PM
 */
require_once __DIR__.'./../Models/Database.class.php';

class Backup {
    private $db;
    private $ignore_tables = Array('backups', 'users');
    private $max_backups = 3;

    public function __construct(){
        $this->db = Database::get_instance();
    }
    private function check_db(){
        $result = $this->db->fetch_all_query("SELECT * FROM backups ORDER BY 'id'");
        if(count($result)> $this->max_backups){
            $destroy = $result[0]['id'];
            $file = $result[0]['file'];
            unlink($file);
            $this->db->query("DELETE FROM backups where id='$destroy'");
        }
    }
    private function buildSQL(){
        $tables = array();
        $result = $this->db->query("SHOW TABLES");
        while($row = $result->fetch_row())
        {
            if(in_array($row[0], $this->ignore_tables)){
                continue;
            }
            $tables[] = $row[0];
        }
        $return = "SET FOREIGN_KEY_CHECKS=0;\n\n";
        //cycle through
        foreach($tables as $table)
        {
            $result = $this->db->query('SELECT * FROM '.$table);
            $num_fields = $result->field_count;

            $return.= 'DROP TABLE '.$table.';';
            $row2 = $this->db->query('SHOW CREATE TABLE '.$table)->fetch_row();
            $return.= "\n\n".$row2[1].";\n\n";

            for ($i = 0; $i < $num_fields; $i++)
            {
                while($row = $result->fetch_row())
                {
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                    for($j=0; $j < $num_fields; $j++)
                    {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = preg_replace("/\n/","/\\n/",$row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j < ($num_fields-1)) { $return.= ','; }
                    }
                    $return.= ");\n";
                }
            }
            $return.="\n\n\n ";
        }
        $return .= "\n\nSET FOREIGN_KEY_CHECKS=1;";
        return Array(
            'file' => $return,
            'tables' => $tables
        );
    }
    private function write_file($file, $tables){
        $file = addslashes($file);
        $file_hash = md5($file);
        if($this->db->fetch_all_query("SELECT * FROM backups WHERE hash='$file_hash'")){
            $this->db->query("UPDATE backups SET date=CURRENT_TIMESTAMP WHERE hash'$file_hash'");
        }else{
            $sql = "INSERT into backups (file, hash)
              VALUES('$file', '$file_hash');
            ";
            $success = $this->db->query($sql);
        }
        if($success){
            $this->check_db();
        }
    }
    public function backup(){
        $file = $this->buildSQL();
        $write = $this->write_file($file['file'], $file['tables']);
    }
    public function restore($id){
        $backup = $this->db->fetch_all_query("SELECT file FROM backups WHERE id='$id'");
        if(count($backup) === 0){
            throw new Exception(404);
        }
        $backup = $backup[0]['file'];
        if($this->db->get_connection()->multi_query($backup)){
            while($this->db->get_connection()->next_result()) {}
        }
        if($this->db->get_connection()->error){
            throw new Exception(500);
        }
    }
    public function show_backups(){
        $backups = $this->db->fetch_all_query("SELECT id, date, hash  FROM backups ORDER BY 'id'");
        return($backups);
    }
}