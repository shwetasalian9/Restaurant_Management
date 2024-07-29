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
    $ingredients = pg_query($db_conn, "select ingredient_no, ingredient_name from ingredients");

    $item_names = pg_query($db_conn, "select item_name from menu");
    if(!$ingredients and !$menu and $item_names) {
        echo pg_last_error($db_conn);
        exit;
    }
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
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="placeOrder.php">Refresh</a>
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="viewCart.php">View Cart</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div><br><br>';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            
            $data = json_decode($_SERVER['HTTP_BODY']);

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

            if($data->task == 'update_pref') {
            var_dump("updating the db pref");
            unset($data->task);
            $update_result = pg_update($db_conn, 'meal_preference', get_object_vars($data), $whereCondition);
            if(!$update_result) {
                    echo "update failed";
                }
            }  else if($data->task == 'insert_pref') {
                var_dump("inserting the db pref");
                unset($data->task);
                
                $user_id = (int)$user_id;
                $ingredientsString = $data->ingredients;

                var_dump($ingredients);
                $query = "INSERT INTO meal_preference (user_id, item_no, ingredients, cooking_preference, special_instruction) VALUES ($user_id, $data->item_no, '$ingredientsString', '$data->cooking_preference', '$data->special_instruction')";
                var_dump($query);
                try {
                    
                    $ins_result = pg_query($db_conn, $query);
                    if(!$ins_result) {
                            echo pg_last_error($db_conn);
                    }  else {echo "success";}
                } catch(PDOException $e) {
                    // Print error message if there is any issue with the database connection or query
                    echo "Connection failed: " . $e->getMessage();
                }
            }
        }         
    }
    ?>

    <script>
        function editPref(count) {
            var td_ing = document.getElementById("cell_4");
            td_ing.innerHTML = ""
            
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

                    // Create a checkbox element
                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = "ing_"+item.ingredient_name;
                    checkbox.name = 'ingredients[]'; 
                    checkbox.value = item.ingredient_name; 

                    // Create a label element
                    var label = document.createElement('label');
                    label.appendChild(checkbox); 
                    label.appendChild(document.createTextNode(item.ingredient_name)); 

                    // Append the label to the container
                    td_ing.appendChild(label);
                    td_ing.appendChild(document.createElement('br')); 
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
            
            var cook_pref = document.getElementById("cell_5");
            cook_pref.contentEditable = "true";

            var spl_inst = document.getElementById("cell_6");
            spl_inst.contentEditable = "true";
        }

        function savePref(count) {
                var hiddenInput = document.getElementById("db_task");
                db_task = hiddenInput.value;

                var cook_pref = document.getElementById("cell_5");
                var spl_inst = document.getElementById("cell_6");
                var td_no = document.getElementById("cell_0");
                var td_ingredients = document.getElementById("cell_4");

                item_no = td_no.textContent;
                cook_pref = cook_pref.textContent;
                spl_inst = spl_inst.textContent;

                var ingredient_values = [];

                var inputs = document.querySelectorAll('input[id*="ing"]:checked');

                if(inputs.length == 0) {
                    ingredients = td_ingredients.textContent;
                    ingredient_values = ingredients.split(",");
                } else {
                    inputs.forEach(function(input) {
                    // Push the value of the checked input element to the values array
                    ingredient_values.push(input.value);
                    });
                }

                if(db_task == 'insert') {
                    console.log("it is a insert task");
                    var data = {'task': 'insert_pref','item_no':item_no, 'ingredients':ingredient_values, 'cooking_preference':cook_pref, 'special_instruction':spl_inst};
                } else if(db_task == 'update'){
                    console.log("it is a update task");
                    var data = {'task': 'update_pref','item_no':item_no, 'ingredients':ingredient_values, 'cooking_preference':cook_pref, 'special_instruction':spl_inst};
                }

                console.log(data);
                fetch('placeOrder.php', {
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

        function selectedItem() {
                var dropdown = document.getElementById("selected_item");
                var selectedValue = dropdown.value;
                
                var data = {'selected_value':selectedValue}
                
                fetch('getMenuItem.php', {
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
            .then(data => {
                console.log('Data===>' , data);
                const rowMenu = document.getElementById('row_menu');
                const dataArray = JSON.parse(data);
                const db_task = dataArray[dataArray.length - 1];
                delete dataArray[dataArray.length - 1];

                var hiddenInput = document.getElementById("db_task");
                hiddenInput.value = db_task; 

                //get db_task and remove from array

                var count = 0;
                dataArray.forEach(value => {
                    const cell = document.createElement('td');
                    cell.id = "cell_"+count;
                    cell.textContent = value; // Set the content of the table cell
                    rowMenu.appendChild(cell); // Append the table cell to the table row
                    count++;
                });
                var quantity = document.createElement('input');
                quantity.id = 'qty';
                quantity.type = 'number';
                quantity.size = '4';
                quantity.max = '100';
                quantity.min = '1';
                quantity.value = '1';
                quantity.step = '1';
                const cell = document.createElement('td');
                cell.appendChild(quantity);
                rowMenu.appendChild(cell);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
        }

        function addCart() {
            var item_no = document.getElementById("cell_0");
            var item_name = document.getElementById("cell_1");
            var description = document.getElementById("cell_2");
            var price = document.getElementById("cell_3");
            var cook_pref = document.getElementById("cell_5");
            var spl_inst = document.getElementById("cell_6");

            var inputs = document.querySelectorAll('input[id*="ing"]:checked');
            console.log(inputs);
            var ingredient_values = [];
            if(inputs.length != 0) {      
                console.log("inside if");           
                inputs.forEach(function(input) {
                ingredient_values.push(input.value);
            });
            } else {
                console.log("inside else");
                var ings = document.getElementById("cell_4");
                ingredient_values = ings.textContent.split(",");
            }
            var quantity = document.getElementById("qty");

            var items = {
                'item_no': item_no.textContent,
                'item_name': item_name.textContent,
                'description': description.textContent,
                'price': parseInt(price.textContent),
                'total_price': parseInt(price.textContent) * parseInt(quantity.value),
                'cook_pref': cook_pref.textContent,
                'spl_inst': spl_inst.textContent,
                'ingredients': ingredient_values,
                'quantity': quantity.value
            };
            
            var data = {'items': items}

            fetch('addToCart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data) 
            })
        }
            
    </script>

    <div style='float:center; width:20%; margin-left:20%'>
    Select Item :
    <?php
    echo "<select  id='selected_item' name='selected_item' onchange='selectedItem()'>";
    echo "<option value='' disabled selected>Select an option</option>";
    while ($row = pg_fetch_assoc($item_names)) {
        echo "<option value='" . $row['item_name'] . "'>" . $row['item_name'] . "</option>";
    }
    echo "</select>";
    echo "<input type='hidden' id='db_task' name='db_task'>";
    ?><br><br>
    </div>

    <table id="menu_table" class="table table-striped" style="width:80%;">
        <tr>
            <th>Item No.</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Ingredients</th>
            <th>Cooking Preference</th>
            <th>Special Instruction</th>
            <th>Quantity</th>            
        </tr>
        <tr id="row_menu">        
        </tr>
        <tr ><td colspan='7'>
        <button onclick='addCart()'style='background: lightgreen; float:right;'>Add to cart</button>
        <button id='savePref' onclick='savePref()' style='background: lightgrey; float:right;'>Save Preference</button>
        <button id='editPref' onclick='editPref()' style='background: lightgrey; float:right;'>Edit Preference</button>
        </td></tr>
    </table>

</body>
</html>