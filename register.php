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
    <div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar"></div>
    </div>

    <form action="register.php" method="POST">
    
    First Name: <input type="text" name="first_name"><br><br>
    Last Name: <input type="text" name="last_name"><br><br>
    Email Id: <input type="text" name="email_id"><br><br>
    Password: <input type="password" name="password"><br><br>
    <input type="submit">
    </form>
    <div style='text-align: center;'><a href='index.php'>Login</a></div>
    <?php
    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

        $search_user = pg_fetch_assoc(pg_query($db_conn, "select * from users where email_id='".$_POST['email_id']."'"));
        
        if($search_user) {
            echo "<div style='text-align: center;'><a href='index.php'>You are already registered ! Login Here</a></div>";
        }
        else {
            $ins_result = pg_insert($db_conn, 'users', $_POST);
            if(!$ins_result) {
                    echo pg_last_error($db_conn);
                    exit;
                }
            else {
                echo "<div style='text-align: center;'><a href='index.php'>Congratulations ! You are registered ! Login Here</a></div>";
            }
        }
    }
    ?>
</body>
</html>