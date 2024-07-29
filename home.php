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
        </div>';
    }
    
    if($username == 'admin') {
    echo '<button onclick="redirectManageIngredient()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Manage Ingredients</button>';
    echo '<button onclick="redirectManageMenu()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Manage Menu</button>';
    echo '<button onclick="redirectManageOrders()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Manage Orders</button>';
    echo '<button onclick="redirectAnalytics()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Analytics</button>';
    } 
    else {
    echo '<button onclick="redirectMealPref()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Meal Preference</button>';
    echo '<button onclick="redirectOrder()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Place Order</button>';
    echo '<button onclick="redirectViewCart()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">View Cart</button>';
    echo '<button onclick="redirectOrderStatus()" style="height: 300px; width: 300px; background: lightblue; font-size: large; text-align: center; margin-top: 20px; margin-left: 20px;">Order Status</button>';
    }
    ?>

    <script>
        function redirectManageIngredient() {
            window.location.href = 'manageIngredients.php';
        }

        function redirectManageMenu() {
            window.location.href = 'manageMenu.php';
        }

        function redirectMealPref() {
            window.location.href = 'mealPreference.php';
        }

        function redirectOrder() {
            window.location.href = 'placeOrder.php';
        }

        function redirectOrderStatus() {
            window.location.href = 'statusOrder.php';
        }

        function redirectManageOrders() {
            window.location.href = 'manageOrders.php';
        }

        function redirectViewCart() {
            window.location.href = 'viewCart.php';
        }

        function redirectAnalytics() {
            window.location.href = 'analytics.php';
        }
    </script>

</body>
</html>