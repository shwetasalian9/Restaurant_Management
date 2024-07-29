<?php
require('includes/dbh.inc.php');

session_start();
$user_id = $_SESSION['user']['user_id'];

$postData = file_get_contents("php://input");
$data = json_decode($postData, true);
$data['user_id'] = (int)$user_id;

$insertData = [
    "items" => [
        "item_no" => $data['items']['item_no'],
        "item_name" => $data['items']['item_name'],
        "description" => $data['items']['description'],
        "price" => $data['items']['price'],
        "total_price" => $data['items']['total_price'],
        "cook_pref" => $data['items']['cook_pref'],
        "spl_inst" => $data['items']['spl_inst'],
        "ingredients" => $data['items']['ingredients'],
        "quantity" => $data['items']['quantity']
    ],
    "user_id" => $data['user_id']
];
$jsonData = json_encode($insertData['items']);

$query = "INSERT INTO cart (user_id, items) VALUES ('$insertData[user_id]', '$jsonData')";
var_dump($query);
try {
    
    $ins_result = pg_query($db_conn, $query);
    if(!$ins_result) {
            echo pg_last_error($db_conn);
    }  else {echo "success";}


} catch(PDOException $e) {
    // Print error message if there is any issue with the database connection or query
    echo "Connection failed: " . $e->getMessage();
}
?>
