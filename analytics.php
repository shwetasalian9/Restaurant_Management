<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="restaurant" content="width=device-width, initial-scale=1.0">
    <title>RestaurantManagement</title>
    <link rel="stylesheet" href="includes/forms.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <?php
    require('includes/dbh.inc.php');   
    ?>    

    <?php
    session_start();

    if(isset($_SESSION['user'])) {
        $user_id = $_SESSION['user']['user_id'];
        $username = $_SESSION['user']['firstname'];
        echo
        '<div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar" style="display: inline-block;">
            <a style="text-decoration: none; float: left; padding: 20px" href="home.php">Home</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large; margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }

    // Get top rated item
    $top_rated_item_query = "Select m.item_no, item_name, description, i.rating from menu as m join 
    (Select CAST(json_array_elements(items)->>0 AS INTEGER) AS item_no, rating FROM ( Select order_no, items, rating 
    from orders order by rating desc limit 1)) as i on m.item_no = i.item_no";

    // Top 3 customers by spending
    $top_customers_query = "SELECT u.user_id,first_name,last_name,total_spending,
    CASE WHEN rank = 1 THEN '1st' WHEN rank = 2 THEN '2nd' WHEN rank = 3 THEN '3rd'
    ELSE NULL END AS ranking FROM users AS u JOIN 
    (SELECT user_id, SUM(price) AS total_spending, ROW_NUMBER() OVER (ORDER BY SUM(price) DESC) AS rank
    FROM orders GROUP BY user_id LIMIT 3) AS s ON u.user_id = s.user_id order by ranking asc";

    //weekly orders
    $weekly_orders_query = "SELECT EXTRACT(DOW FROM timestamp) AS day_of_week,
    COUNT(*) AS total_orders FROM orders WHERE
    timestamp >= CURRENT_DATE - INTERVAL '7 days'
    GROUP BY day_of_week ORDER BY day_of_week;";

    //no of orders in each status
    $order_status_query = "SELECT all_statuses.status, COALESCE(orders_count.order_count, 0) AS order_count
    FROM (SELECT 'order_placed' AS status UNION ALL SELECT 'preparing' UNION ALL SELECT 'picked_up'
        UNION ALL SELECT 'delivered' ) AS all_statuses LEFT JOIN (SELECT status, COUNT(*) AS order_count 
        FROM orders WHERE status IN ('order_placed', 'preparing', 'picked_up', 'delivered')
        GROUP BY status) AS orders_count ON all_statuses.status = orders_count.status
    ORDER BY all_statuses.status;";

    $top_rated_item_result = pg_query($db_conn, $top_rated_item_query);
    $top_customers_result = pg_query($db_conn, $top_customers_query);
    $weekly_orders_result = pg_query($db_conn, $weekly_orders_query);
    $order_status_result = pg_query($db_conn, $order_status_query);

    if(!$top_rated_item_result and !$top_customers_result and !$weekly_orders_result and !$order_status_result) {
        echo pg_last_error($db_conn);
        exit;
    } else {
        $top_rated_item = pg_fetch_assoc($top_rated_item_result);
        $top_three_customers = pg_fetch_all($top_customers_result);
        $weekly_orders_customers = pg_fetch_all($weekly_orders_result);
        $orders_in_status = pg_fetch_all($order_status_result);
    }
    ?>

    <table id="order_table" class="table table-striped" style="width:80%;">
    <tr><th>Top Rated Item</th><th>Top 3 Customers By Spending</th></tr>
    <tr><td>
    <?php
    echo "Item No.: <span style='font-size: 18px;font-weight: bold;'>$top_rated_item[item_no]</span><br>";
    echo "Item Name: <span style='font-size: 18px;font-weight: bold;'>$top_rated_item[item_name]</span><br>";
    echo "Description: <span style='font-size: 18px;font-weight: bold;'>$top_rated_item[description]</span><br>";
    echo "Rating: <span style='font-size: 18px;font-weight: bold;'>$top_rated_item[rating]</span><br>";
    ?>
    </td>
    <td>
    <?php
    foreach ($top_three_customers as $customer) {
        echo "<span style='font-size: 18px;font-weight: bold;'>$customer[ranking]</span><br>";
        echo "User Id : <span style='font-weight: bold;'>$customer[user_id]</span>\t\t";
        echo "Name : <span style='font-weight: bold;'>$customer[first_name] $customer[last_name]</span>\t\t";
        echo "Total Spending : <span style='font-weight: bold;'>$customer[total_spending]</span><br>";
    }    
    ?>
    </td></tr>
    <tr><th>No. of Orders Through The Week</th><th>No. of Orders in Each Status</th></tr>
    <tr><td>
    <?php
    $ordersPerDay = array_fill(0, 7, 0);

    foreach ($weekly_orders_customers as $order) {
        $dayOfWeek = intval($order["day_of_week"]);
        $ordersPerDay[$dayOfWeek] = intval($order["total_orders"]); 
    } 
 
    echo "<div style='display: flex; align-items: flex-end; width: 80%; margin-left:60px;'>";
        foreach ($ordersPerDay as $dayOfWeek => $totalOrders) {
            $dayName = date('l', strtotime("Sunday +$dayOfWeek days"));
            echo "<div style='width: 65px; background-color: #1b174d; margin-right: 5px; text-align: center; color: white; font-size: 12px;'>";
            echo "<div>$dayName</div>"; 
            echo "<div>$totalOrders</div>"; 
            echo "<div style='height: " . ($totalOrders * 20) . "px'></div>";
            echo "</div>";
        }
    echo "</div>";
    ?>
    </td><td>
    <?php

    foreach ($orders_in_status as $order) {
        $status = $order["status"];
        $status_no[$status] = intval($order["order_count"]); 
    } 
 
    echo "<div style='display: flex; align-items: flex-end; margin-left: 60px; width: 80%'>";
        foreach ($status_no as $status => $totalOrders) {
            echo "<div style='width: 75px; background-color: #ba5222; margin-right: 5px; text-align: center; color: white; font-size: 12px;'>";
            echo "<div>$status</div>"; 
            echo "<div>$totalOrders</div>"; 
            echo "<div style='height: " . ($totalOrders * 20) . "px'></div>";
            echo "</div>";
        }
    echo "</div>";
    ?>
    </td></tr>
    </table>

    
</body>
</html>