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

        $pref_data = pg_query($db_conn, "SELECT m.item_no, ARRAY(SELECT i.ingredient_name FROM ingredients i WHERE i.ingredient_no = ANY(m.ingredients)) 
        AS ingredients, m.cooking_preference, m.special_instruction FROM meal_preference m where user_id = $user_id");
        if(!$pref_data) {
                echo pg_last_error($db_conn);
                exit;
            }

        echo
        '<div class="title_bar">
        <div class="app_name">SubWhich</div>
        <div class="page_bar" style="display: inline-block;">
            <a style="text-decoration: none; float: left; padding: 20px" href="home.php">Home</a>
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="mealPreference.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large; margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }

    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            $data = json_decode($_SERVER['HTTP_BODY']);

            $item_no = (int)$data->item_no;

            if($data->delete and $data->delete== true) {
                $conditions = array("item_no" => $item_no);
                $delete_result = pg_delete($db_conn, 'meal_preference', $conditions);
            }
            else {
                $item_no = (int)$data->item_no;                

                if($data->ingredients) {
                    $display_ing = "";
                    foreach ($data->ingredients as $item) {
                        $display_ing .= "'".$item . "',";
                    }
                    $display_ing = rtrim($display_ing, ',');

                    $query = 'select ingredient_no from ingredients where ingredient_name in ('.$display_ing.')';
                    $ings_list = pg_fetch_all(pg_query($db_conn, $query));

                    $listing = [];
                    foreach($ings_list as $item) {
                        $listing[] = $item['ingredient_no'];
                    }

                    $data->ingredients = '{' . implode(',', $listing) . '}';
                } else {
                    unset($data->ingredients);
                }

                $data->item_no = $item_no;
                
                $whereCondition = ["item_no" => $item_no, "user_id" => $user_id];
                unset($_POST['item_no']);
                
                var_dump($data);
                var_dump($whereCondition);

                $update_result = pg_update($db_conn, 'meal_preference', get_object_vars($data), $whereCondition);
                if(!$update_result) {
                        echo "update database failed";
                    }
                }
        } 
    }
    
    ?>

    <script>
        function deletePref(count) {
            var td_no = document.getElementById("item_no_"+count);
            item_no = td_no.textContent
            var data = {'delete':true,'item_no':item_no}

            fetch('mealPreference.php', {
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

        function editPref(count) {
            var cooking_pref = document.getElementById("cooking_"+count);

            var spl_instn = document.getElementById("special_"+count);

            var td_ing = document.getElementById("ingredients_"+count);
            td_ing.innerHTML = ""
            
            cooking_pref.contentEditable = "true";
            spl_instn.contentEditable = "true";

            fetch('getIngredients.php')
            .then(response => {
                if (!response.ok) {
                throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                for (var i = 0; i < data.length; i++) {
                    var item = data[i];

                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = "ing_"+item.ingredient_name;
                    checkbox.name = 'ingredients[]'; 
                    checkbox.value = item.ingredient_name; 

                    var label = document.createElement('label');
                    label.appendChild(checkbox); 
                    label.appendChild(document.createTextNode(item.ingredient_name)); 

                    td_ing.appendChild(label);
                    td_ing.appendChild(document.createElement('br')); 
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });            
        }

        function savePref(count) {
                var td_no = document.getElementById("item_no_"+count);
                var td_ingredients = document.getElementById("ingredients_"+count);
                var td_cooking_pref = document.getElementById("cooking_"+count);
                var td_spl_instn = document.getElementById("special_"+count);
                
                td_cooking_pref.contentEditable = "false";
                td_spl_instn.contentEditable = "false";

                td_no = td_no.textContent;
                td_cooking_pref = td_cooking_pref.textContent;
                td_spl_instn = td_spl_instn.textContent;

                var inputs = document.querySelectorAll('input[id*="ing"]:checked');

                console.log(inputs);

                var ingredient_values = [];
                inputs.forEach(function(input) {
                    // Push the value of the checked input element to the values array
                    ingredient_values.push(input.value);
                });

                var data = {'item_no':td_no, 'cooking_preference':td_cooking_pref, 'special_instruction':td_spl_instn, 'ingredients':ingredient_values};

                fetch('mealPreference.php', {
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

    <table class="table table-striped" style="width:80%">
        <tr>
            <th>Item No.</th>
            <th>Ingredients</th>
            <th>Cooking Preference</th>
            <th>Special Instruction</th>
            <th>Action</th>
        </tr>
        <?php
            $count = 1;
            while($row = pg_fetch_assoc($pref_data)) {
                $display_ing = str_replace('{', '', $row['ingredients']);
                $display_ing = str_replace('}', '', $display_ing);

                echo "
                    <tr >
                        <td id=\"item_no_$count\">$row[item_no]</td>
                        <td id=\"ingredients_$count\">$display_ing</td>
                        <td id=\"cooking_$count\">$row[cooking_preference]</td>
                        <td id=\"special_$count\">$row[special_instruction]</td>
                        <td >
                            <button onclick='editPref($count)' style='background: lightgrey'>Edit</button>
                            <button onclick='deletePref($count)' style='background: red'>Delete</button>
                            <button onclick='savePref($count)'style='background: lightgreen'>Save</button>
                        </td>
                    </tr>
                ";
                $count++;
            }
        ?>
    </table>
    <?php
    ?>


</body>
</html>