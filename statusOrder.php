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
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="statusOrder.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }
    $query = "select * from orders where user_id ='".$user_id."'";
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
        </tr>
        <?php
        $count = 1;
        $displayForm = true;
        while($row = pg_fetch_assoc($order_items)) {
            $items = json_decode($row['items'],true);
            echo "<tr id='row_$count'>";
            echo "<td id='order_no_$count'>$row[order_no]</td>";
            echo "<td id='timestamp_$count'>$row[timestamp]</td>";
            
            if($row['rating'] != NULL) {
                $displayForm = false;
            }

            if($row['feedback'] != NULL) {
                $displayForm = false;
            }

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
            echo "</tr>";
            echo "<tr>";

            if($displayForm ) {
            echo "<td colspan='7'>
            <form action='statusOrder.php' method='post'>
                <input type='radio' name='rating' value='1' id='rating'>
                <label for='rating' style='margin-right: 10px;'>1</label>

                <input type='radio' name='rating' value='2' id='rating'>
                <label for='rating' style='margin-right: 10px;'>2</label>

                <input type='radio' name='rating' value='3' id='rating'>
                <label for='rating' style='margin-right: 10px;'>3</label>

                <input type='radio' name='rating' value='4' id='rating'>
                <label for='rating' style='margin-right: 10px;'>4</label>

                <input type='radio' name='rating' value='5' id='rating'>
                <label for='rating' style='margin-right: 10px;'>5</label><br>

                <input type='text' name='feedback' placeholder='Please provide feedback' style='float: left; width: 400px;'><br><br>
                <input type='text' style='display:none;' name='order_no' value='$row[order_no]'>
                <input type='submit' value='Submit'>
            </form>
            </td>";
            }
            echo "</tr>";
            $count++;
        }
        ?>
        
    </table>

    <?php
    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $order_no = $_POST['order_no'];

        $whereCondition = ["order_no" => $order_no];

        unset($_POST['order_no']);

        $update_result = pg_update($db_conn, 'orders', $_POST, $whereCondition);
            if(!$update_result) {
                    var_dump(db_conn);
                }
        
    }
    ?>


</body>
</html>