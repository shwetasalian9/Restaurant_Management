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
    session_start();

    if(isset($_SESSION['user'])) {
        $user_id = $_SESSION['user']['user_id'];
        $username = $_SESSION['user']['firstname'];
        echo
        '<div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar" style="display: inline-block;">
            <a style="text-decoration: none; float: left; padding: 20px" href="home.php">Home</a>
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="viewCart.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }
    $query = "select * from cart where user_id ='".$user_id."'";
    $cart_items = pg_query($db_conn, $query);
    ?>

    <script>
        function placeOrder() {
            //insert data to orders table
            fetch('addToOrder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                // body: JSON.stringify(data),
                }
            })
            .then(response => {
                
            if (response.ok) {
                console.log('Res===>' , response);
            } else {
                throw new Error('Error: ' + response.status);
            }
            })
            .then(result => {
                console.log(result);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });

        }
    </script>

    <table id="cart_table" class="table table-striped" style="width:80%;">
        <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <?php
        $count = 1;
        $total_bill = 0;
        while($row = pg_fetch_assoc($cart_items)) {
            $json_data = json_decode($row['items'], true);
            echo "<tr id='row_$count'>";
            $ingredients = implode(", ", $json_data['ingredients']);
            echo "<td id=\"item_no_$count\">$json_data[item_name]<br>$ingredients<br>$json_data[cook_pref]<br>$json_data[spl_inst]</td>";
            echo "<td id=\"price_$count\">$json_data[price]</td>";
            echo "<td id=\"quantity_$count\">$json_data[quantity]</td>";
            echo "<td id=\"total_price_$count\">$json_data[total_price]</td>";
            echo "</tr>";
            $total_bill = $total_bill + $json_data['total_price'];
            $count++;
        }
        echo "<tr><td colspan='4'><label style='float:right;'>Total: $total_bill</label></td></tr>";
        ?>
        <tr ><td colspan='4'>
        <button onclick='placeOrder()'style='background: lightgreen; float:right;'>Place Order</button>
        </td></tr>
    </table>

</body>
</html>