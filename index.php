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

    <form action="index.php" method="POST">
    Email Id: <input type="text" name="email_id" placeholder="Email Id"><br><br>
    Password: <input type="password" name="password" placeholder="Password"><br><br>
    <button type="submit">Login</button>
    </form>
    <div style='text-align: center;'><a href='register.php'>New User ? Register Here</a></div>
    <?php
    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        $search_user = pg_fetch_assoc(pg_query($db_conn, "select * from users where email_id='".$_POST['email_id']."' and password='".$_POST['password']."'"));
        if($search_user) {
            session_start();
            $user_details = array(
                'user_id' => $search_user['user_id'],
                'firstname' => $search_user['first_name'],
                'email' => $search_user['email_id'],
            );
            $_SESSION['user'] = $user_details;
            header("Location: home.php");
            exit();
        }
        else {
            echo "<div style='text-align: center;'><a class='error' href='index.php'>Incorrect Email Id or Password. Login Again.</a></div>";
        }
    }
    ?>
</body>
</html>