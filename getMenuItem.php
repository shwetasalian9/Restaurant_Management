<?php
require('includes/dbh.inc.php');

session_start();

try {
    $data = json_decode($_SERVER['HTTP_BODY']);
    $user_id = $_SESSION['user']['user_id'];
    $item_name = $data->selected_value;
    $query = "select item_no from menu where item_name ='".$item_name."'";
    $selected_item_no = pg_fetch_all(pg_query($db_conn, $query))[0]['item_no'];

    $menu = pg_query($db_conn, "select * from menu where item_no=$selected_item_no");
    if(!$menu) {
        echo pg_last_error($db_conn);
        exit;
    }
    $row = pg_fetch_assoc($menu);

    $query = "select ingredients,cooking_preference,special_instruction from meal_preference where item_no ='".$selected_item_no."' and user_id ='".$user_id."'";    
    $temp = pg_fetch_all(pg_query($db_conn, $query));

    $ing_no = "";
    $cooking_pref = "None";
    $spl_inst = "None";
    $ing_list_to_use = "";
    $db_task = "";

    if (!empty($temp)) {
        $ing_no = pg_fetch_all(pg_query($db_conn, $query))[0]['ingredients'];
        $ing_list_to_use = $ing_no;
        $cooking_pref = pg_fetch_all(pg_query($db_conn, $query))[0]['cooking_preference'];
        $spl_inst = pg_fetch_all(pg_query($db_conn, $query))[0]['special_instruction'];
        $db_task = "update";
    } else {
        $ing_list_to_use = $row['ingredients'];
        $db_task = "insert";
    }
   
    $ingredientsString = str_replace('{', '(', $ing_list_to_use);
    $ingredientsString = str_replace('}', ')', $ingredientsString);
    $query = "select ingredient_name from ingredients where ingredient_no in $ingredientsString";
    $ings_list = pg_fetch_all(pg_query($db_conn, $query));

    $display_ing = "";
    foreach ($ings_list as $item) {
        $display_ing .= $item["ingredient_name"] . ",";
    }
    $display_ing = rtrim($display_ing, ",");
    
    $return_data = [$row['item_no'], $row['item_name'], $row['description'], $row['price'], $display_ing, $cooking_pref, $spl_inst, $db_task];
    echo json_encode($return_data);

} catch(PDOException $e) {
    // Print error message if there is any issue with the database connection or query
    echo "Connection failed: " . $e->getMessage();
}
?>
