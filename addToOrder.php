<?php
require('includes/dbh.inc.php');

session_start();
$user_id = $_SESSION['user']['user_id'];

$query = "select items from cart where user_id ='".$user_id."'";
$cart_items = pg_query($db_conn, $query);

$user_id = (int)$user_id;

$price = 0;
$status = "order_placed";
$item_array = array();

while($row = pg_fetch_assoc($cart_items)) {
    
    var_dump($row['items']);
    $items = json_decode($row['items'], true);
    $price = $price + $items['total_price'];
    
    $temp = array($items['item_no'], $items['item_name'], $items['cook_pref'], $items['spl_inst'], $items['ingredients'], $items['quantity']);
    array_push($item_array, $temp);
}

$jsonData = json_encode($item_array);

$query = "INSERT INTO orders (user_id, items, price, status) VALUES ($user_id, '$jsonData', $price, '$status')";
    try {
    $ins_result = pg_query($db_conn, $query);
    if(!$ins_result) {
            echo pg_last_error($db_conn);
    }  else {echo "success";}
    } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    }

$delete_cart_query = "DELETE FROM cart";
try {
    $delete_result = pg_query($db_conn, $delete_cart_query);
    if(!$delete_result) {
            echo pg_last_error($db_conn);
    }  else {echo "success";}
    } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    }
?>
