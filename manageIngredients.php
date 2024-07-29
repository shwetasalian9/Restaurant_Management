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
    ob_start();
    session_start();

    if(isset($_SESSION['user'])) {
        $user_id = $_SESSION['user']['user_id'];
        $username = $_SESSION['user']['firstname'];

        $ingredients_data = pg_query($db_conn, "select * from ingredients");
        if(!$ingredients_data) {
                echo pg_last_error($db_conn);
                exit;
            }

        echo
        '<div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar" style="display: inline-block;">
            <a style="text-decoration: none; float: left; padding: 20px" href="home.php">Home</a>
            <a id="view_ing" style="text-decoration: none; float: left; padding: 20px" href="manageIngredients.php">View All</a>
            <a id="add_ing" style="text-decoration: none; float: left; padding: 20px" href="manageIngredients.php">Add Ingredient</a>
            <a id="check_ing" onclick="showAlert()" style="text-decoration: none; float: left; padding: 20px" href="manageIngredients.php">Check Inventory</a>
            <a id="view_ing" style="text-decoration: none; float: left; padding: 20px" href="manageIngredients.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div>';
        
    }
    ob_end_flush();
    ?>

    <div class="add_ing_form" id="add_ing_form">
    <form action="manageIngredients.php" method="POST">
        Name: <input type="text" name="ingredient_name" placeholder="Ingredient Name"><br><br>
        Minimum Qty: <input type="text" name="min_level" placeholder="Minimum Quantity"><br><br>
        Current Qty: <input type="text" name="level" placeholder="Current Quantity"><br><br>
        <button type="submit">Add</button>
    </form>
    </div><br><br>

    <table class="table table-striped" style="width:80%">
        <tr>
            <th>Ingredient No.</th>
            <th>Ingredient Name</th>
            <th>Minimum Quantity</th>
            <th>Current Quantity</th>
            <th>Action</th>
        </tr>
        <?php
            $count = 1;
            while($row = pg_fetch_assoc($ingredients_data)) {
                echo "
                    <tr >
                        <td id=\"ingredient_no_$count\">$row[ingredient_no]</td>
                        <td id=\"ingredient_name_$count\">$row[ingredient_name]</td>
                        <td id=\"min_level_$count\">$row[min_level]</td>
                        <td id=\"level_$count\">$row[level]</td>
                        <td >
                            <button onclick='editIngredient($count)' style='background: lightgrey'>Edit</button>
                            <button onclick='deleteIngredient($count)' style='background: red'>Delete</button>
                            <button onclick='saveIngredient($count)'style='background: lightgreen'>Save</button>
                        </td>
                    </tr>
                ";
                $count++;
            }
        ?>
    </table>

    <script>
        document.getElementById("add_ing").addEventListener("click", function(event){
            event.preventDefault();
            document.getElementById("add_ing_form").style.display = "block";
        });
        function showAlert() {
            //alert("This is an alert box!");
            fetch('checkInventory.php')
            .then(response => {
                if (!response.ok) {
                throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                item_list = '';
                data.forEach(item => {
                    let no = item.ingredient_no.toString();
                    let min = item.min_level.toString();
                    let level = item.level.toString();
                    //console.log('')
                    item_list = item_list + item.ingredient_name + '(No.:' + no + ', Min Level:' +min + ', Current Level:'+level+ ')';
                    item_list = item_list + '\n';
                });
                alert('Low inventory alert: Some items are running low!\n\n' + item_list);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });    
        }
        function deleteIngredient(count) {
            var td_no = document.getElementById("ingredient_no_"+count);
            ingredient_no = td_no.textContent
            var data = {'delete':true,'ingredient_no':ingredient_no}

            fetch('manageIngredients.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                body: JSON.stringify(data),
                }
            })
            .then(response => {
                console.log('Res===>' , response);
            if (response.ok) {

                return response.text(); 
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
        function editIngredient(count) {
            var td_name = document.getElementById("ingredient_name_"+count);
            var td_min = document.getElementById("min_level_"+count);
            var td_level = document.getElementById("level_"+count);

            td_name.contentEditable = "true";
            td_min.contentEditable = "true";
            td_level.contentEditable = "true";
        }
        function saveIngredient(count) {
                var td_no = document.getElementById("ingredient_no_"+count);
                var td_name = document.getElementById("ingredient_name_"+count);
                var td_min = document.getElementById("min_level_"+count);
                var td_level = document.getElementById("level_"+count);

                td_name.contentEditable = "false";
                td_min.contentEditable = "false";
                td_level.contentEditable = "false";

                ingredient_no = td_no.textContent
                ingredient_name = td_name.textContent
                min_level = td_min.textContent
                level = td_level.textContent

                var data = {'ingredient_no':ingredient_no, 'ingredient_name':ingredient_name, 'min_level':min_level, 'level':level};

                fetch('manageIngredients.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                body: JSON.stringify(data),
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

    <?php
    ob_start();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            $data = json_decode($_SERVER['HTTP_BODY']);

            $ingredient_no = (int)$data->ingredient_no;

            if($data->delete  and $data->delete== true) {
                $conditions = array("ingredient_no" => $ingredient_no);
                $delete_result = pg_delete($db_conn, 'ingredients', $conditions);
            } 
            else {
            
            $whereCondition = ["ingredient_no" => $ingredient_no];
            
            if ($data != null) {
                $update_result = pg_update($db_conn, 'ingredients', get_object_vars($data), $whereCondition);
            if(!$update_result) {
                    var_dump(db_conn);
                }
            }
        }
        } 
        else {
        $ins_result = pg_insert($db_conn, 'ingredients', $_POST);
        if(!$ins_result) {
                var_dump(db_conn);
            }
      }  
    }
    ob_end_flush(); 
    ?>


</body>
</html>