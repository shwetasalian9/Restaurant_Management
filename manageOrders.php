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
    $user_id = $_SESSION['user']['user_id'];
    if(isset($_SESSION['user'])) {
        $user_id = $_SESSION['user']['user_id'];
        $username = $_SESSION['user']['firstname'];
        echo
        '<div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar" style="display: inline-block;">
            <a style="text-decoration: none; float: left; padding: 20px" href="home.php">Home</a>
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="manageOrders.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }
    $query = "select * from orders";
    $order_items = pg_query($db_conn, $query);

    ?>

    <table id="order_table" class="table table-striped" style="width:80%;">
        <tr>
            <th>Order No.</th>
            <th>DateTime</th>
            <th>Items</th>
            <th>Total Price</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Status</th>
            <th>Update Status</th>
        </tr>
        <?php
        $count = 1;
        while($row = pg_fetch_assoc($order_items)) {
            $items = json_decode($row['items'],true);
            echo "<tr id='row_$count'>";
            echo "<td id='order_no_$count'>$row[order_no]</td>";
            echo "<td id='timestamp_$count'>$row[timestamp]</td>";
            
            echo "<td><table>";
            echo "<tr><th>Item</th><th>Cooking Preference</th><th>Special Instruction</th><th>Ingredients</th><th>Quantity</th></tr>";
            foreach ($items as $sub_array) {
                $first_element = true;
                echo "<tr>";
                foreach ($sub_array as $item) {
                    if (is_array($item)) {
                        $comma_separated_string = implode(", ", $item);
                        echo "<td>$comma_separated_string</td>";
                    } else {
                        if (!$first_element) {
                            echo "<td>$item</td>";
                        } else {
                            $first_element = false;
                        }
                    }
                }
                echo "</tr>";
                }
                echo "</table></td>";
            

            echo "<td id='total_price_$count'>$row[price]</td>";
            echo "<td id='rating_$count'>$row[rating]</td>";
            echo "<td id='feedback_$count'>$row[feedback]</td>";
            echo "<td id='status_$count'>$row[status]</td>"; 

            if($row['status'] != 'delivered') {
                echo "
                <td >
                    <select name='status_$row[order_no]' id='status_$row[order_no]'>
                        <option value='preparing'>preparing</option>
                        <option value='picked_up'>picked_up</option>
                        <option value='delivered'>delivered</option>
                    </select><br><br>
                    <button onclick='updateStatus($row[order_no])'style='background: lightgreen'>Update</button>
                </td>
                ";
            } else {
                echo "<td></td>";
            }
                       
            echo "</tr>";
            echo "<tr>";
            echo "</tr>";
            $count++;
        }
        ?>
    </table>

    <script>
        function updateStatus(order_no) {
            var status = document.getElementById("status_"+order_no);
            status_value = status.value;
            var data = {'order_no':order_no, 'status':status_value};
            fetch('manageOrders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                body: JSON.stringify(data),
                }
            })
        }
    </script>

    <?php
    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            $data = json_decode($_SERVER['HTTP_BODY']);
            $order_no = (int)$data->order_no;
            $whereCondition = ["order_no" => $order_no];

            $update_result = pg_update($db_conn, 'orders', get_object_vars($data), $whereCondition);
                if(!$update_result) {
                        var_dump(db_conn);
                    }
                }
        }
    ?>


</body>
</html>