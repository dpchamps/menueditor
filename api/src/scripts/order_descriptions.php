
<?php
require_once(__DIR__ . '/../Models/Database.class.php');

$database = Database::get_instance();
$query = "
    SELECT id, item_id, list_order FROM menu_descriptions
";

$descriptions = $database->fetch_all_query($query);

usort($descriptions, function($a, $b)
{
    return strcmp($a['id'], $b['id']);
});

$current_id = 0;
$current_count = 0;
foreach($descriptions as $desc_key => $description){
    if($description['item_id'] === $current_id){
        $current_count++;
    }else{
        $current_count = 0;
        $current_id = $description['item_id'];
    }
    
    $descriptions[$desc_key]['list_order'] = $current_count;

    $database->update('menu_descriptions', $description['id'], [
        'list_order' => $current_count
    ]);
}
