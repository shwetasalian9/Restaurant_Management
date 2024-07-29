<?php
require('includes/dbh.inc.php');

try {
    $result = pg_query($db_conn, "select * from ingredients where level<min_level");
    $ingredients_data = pg_fetch_all($result);
    echo json_encode($ingredients_data);
} catch(PDOException $e) {
    // Print error message if there is any issue with the database connection or query
    echo "Connection failed: " . $e->getMessage();
}
?>
