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
    $menu = pg_query($db_conn, "SELECT m.item_no, m.item_name, m.description, m.price, ARRAY(SELECT i.ingredient_name FROM ingredients i 
    WHERE i.ingredient_no = ANY(m.ingredients)) AS ingredients FROM menu m;");
    if(!$ingredients and !$menu) {
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
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="manageMenu.php">View All</a>
            <a id="add_item" style="text-decoration: none; float: left; padding: 20px" href="manageMenu.php">Add Item</a>
            <a id="view_item" style="text-decoration: none; float: left; padding: 20px" href="manageMenu.php">Refresh</a>
            <a href="logout.php" style="float: right; padding: 2px; font-size: large;">Logout</a>
            <p style="float: right; padding: 2px; font-size: large;margin-right:10px;">User: '.$username.'</p>
        </div>
        </div>';
    }
    ?>

    <script>
        document.getElementById("add_item").addEventListener("click", function(event){
            event.preventDefault(); // Prevent the default behavior of the link
            document.getElementById("add_item_form").style.display = "block"; // Show the form
        });

        function deleteMenu(count) {
            var td_no = document.getElementById("item_no_"+count);
            item_no = td_no.textContent
            var data = {'delete':true,'item_no':item_no}

            fetch('manageMenu.php', {
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

        function editMenu(count) {
            var td_name = document.getElementById("item_name_"+count);

            var td_desc = document.getElementById("description_"+count);

            var td_price = document.getElementById("price_"+count);

            var td_ing = document.getElementById("ingredients_"+count);
            td_ing.innerHTML = ""
            
            td_name.contentEditable = "true";
            td_desc.contentEditable = "true";
            td_price.contentEditable = "true";

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
        }

        function saveMenu(count) {
                var td_no = document.getElementById("item_no_"+count);
                var td_name = document.getElementById("item_name_"+count);
                var td_desc = document.getElementById("description_"+count);
                var td_price = document.getElementById("price_"+count);
                var td_ingredients = document.getElementById("ingredients_"+count);

                //console.log(td_ingredients);

                td_name.contentEditable = "false";
                td_desc.contentEditable = "false";
                td_price.contentEditable = "false";
                td_ingredients.contentEditable = "false";

                item_no = td_no.textContent;
                item_name = td_name.textContent;
                td_desc = td_desc.textContent;
                td_price = td_price.textContent;

                var inputs = document.querySelectorAll('input[id*="ing"]:checked');

                console.log(inputs);

                var ingredient_values = [];
                inputs.forEach(function(input) {
                    // Push the value of the checked input element to the values array
                    ingredient_values.push(input.value);
                });

                var data = {'item_no':item_no, 'item_name':item_name, 'description':td_desc, 'price':td_price, 'ingredients':ingredient_values};

                console.log(data);

                fetch('manageMenu.php', {
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

    <div class="add_item_form" id="add_item_form">
        <form action="manageMenu.php" method="POST">
            
            Name: <input type="text" name="item_name" placeholder="Item Name"><br><br>
            Description: <input type="text" name="description" placeholder="Description"><br><br>
            Price: <input type="text" name="price" placeholder="Price"><br><br>
            Ingredients: 
            <?php
            echo "<label style='float: right;'>";
            while ($row = pg_fetch_assoc($ingredients)) {
                echo "<input type='checkbox' name='ingredients[]' value='" . $row['ingredient_no'] . "'>" . $row['ingredient_name'] . "</input>";
            }
            echo "</label>";
            ?><br><br>
            <button type="submit">Add</button>
    </form>
    </div><br><br>

    <table class="table table-striped" style="width:80%">
        <tr>
            <th>Item No.</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Ingredients</th>
            <th>Action</th>
        </tr>
        <?php
            $count = 1;
            while($row = pg_fetch_assoc($menu)) {

                $display_ing = str_replace('{', '', $row['ingredients']);
                $display_ing = str_replace('}', '', $display_ing);
                echo "
                    <tr id=\"row_$count\">
                        <td id=\"item_no_$count\">$row[item_no]</td>
                        <td id=\"item_name_$count\">$row[item_name]</td>
                        <td id=\"description_$count\">$row[description]</td>
                        <td id=\"price_$count\">$row[price]</td>                        
                        <td id=\"ingredients_$count\">$display_ing</td>
                        <td >
                            <button id='editMenu' onclick='editMenu($count)' style='background: lightgrey'>Edit</button>
                            <button onclick='deleteMenu($count)' style='background: red'>Delete</button>
                            <button onclick='saveMenu($count)'style='background: lightgreen'>Save</button>
                        </td>
                    </tr>
                ";
                $count++;
            }
        ?>
    </table>

    <?php
    require('includes/dbh.inc.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            $data = json_decode($_SERVER['HTTP_BODY']);

            $item_no = (int)$data->item_no;

            if($data->delete and $data->delete== true) {
                $conditions = array("item_no" => $item_no);
                $delete_result = pg_delete($db_conn, 'menu', $conditions);
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
                
                $whereCondition = ["item_no" => $item_no];
                unset($_POST['item_no']);
                
                var_dump($data);
                var_dump($whereCondition);

                $update_result = pg_update($db_conn, 'menu', get_object_vars($data), $whereCondition);
                if(!$update_result) {
                        var_dump(db_conn);
                    }
                }
        } 
        else {
            var_dump($_POST);
            $_POST['ingredients'] = '{' . implode(',', $_POST['ingredients']) . '}';

            $ins_result = pg_insert($db_conn, 'menu', $_POST);
            if(!$ins_result) {
                    echo pg_last_error($db_conn);
            }  
        }
    }
    ?>

</body>
</html>