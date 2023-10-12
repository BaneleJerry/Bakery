<?php
global $conn;
// Check if the user is logged in as a bakery owner, otherwise redirect to the login page
// if (!is_bakery_owner_logged_in()) {
//     header('Location: auth/login.php');
//     exit;
// }

// Include the header template
include('../shared/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo '<div class="card-container">';
    $menu_items = get_menu_items(); // Fetch menu items from the database

    foreach ($menu_items as $row) {
        $item_id = $row['item_id'];
        $name = reverseSanitizeString($row['name']);
        $stock = getStockQuantity($item_id);

        echo '<div class="card">';
        echo '<div class="card-image"><img src="' . $row["image"] . '" alt="Item Image"></div>';
        echo '<div class="card-details">';
        echo '<h2>' . $name . '</h2>';
        echo '<form method="POST" onsubmit="return checkStock();">';
        echo '<label for="stock">Stock Quantity</label>';
        echo '<input type="hidden" name="item_id" value="' . $item_id . '" />';
        if ($stock) {
            echo '<input type="number" id="stock" name="stock" step=1 value="' . $stock . '">';
            echo '<button type="submit" name="update-stock">Save</button>';
        } else {
            echo '<input type="number" id="stock" name="stock" step= 1 value=0 >';
            echo '<button type="submit" name="insert-stock">Save</button>';
        }

        echo '</form>'; // Close the form tag
        echo '</div></div>';
    }

    echo '</div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $stock = $_POST['stock'];

    // Check if $stock is a valid number greater than or equal to 0
    if (!is_numeric($stock) || $stock < 0) {
        echo '<script>alert("Stock must be a number equal to 0 or greater.");</script>';
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    } else {
        if (isset($_POST['update-stock']))
            handleStockUdate($item_id, $stock, $conn);
        elseif (isset($_POST['insert-stock']))
            handleStockInsert($item_id,$conn);
    }
}


function get_menu_items()
{
    $result = queryMysql("SELECT * FROM menu_items");
    $menu_items = array();

    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }

    return $menu_items;
}

function getStockQuantity($item_id)
{
    // Assuming queryMysql is a function to execute SQL queries securely
    $result = queryMysql("SELECT stock_quantity FROM Inventory WHERE item_id = $item_id");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['stock_quantity'];
    } else {
        return 0; // Return 0 if no stock quantity is found (you can adjust this as needed)
    }
}

function handleStockInsert($item_id, $conn) {
    $stock = $_POST['stock'];
    
    // Check if a record with the same item_id already exists
    $existingStock = getStockQuantity($item_id);
    
    if ($existingStock !== false) {
        handleStockUdate($item_id,$stock,$conn);
    } else {
        // Insert a new record
        $stmt = $conn->prepare("INSERT INTO inventory (item_id, stock_quantity) VALUES (?, ?)");
        $stmt->bind_param('ii', $item_id, $stock);

        if ($stmt->execute()) {
            echo '<script> alert("Stock added successfully 😁")</script>';
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        } else {
            echo 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}



function handleStockUdate($item_id,$stock,$conn){
    $stmt = $conn->prepare("UPDATE inventory SET stock_quantity= ? WHERE item_id = ?");
    $stmt->bind_param('ii',$stock, $item_id);

    if ($stmt->execute()) {
        echo '<script> alert("Stock Update successfully 😁")</script>';
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

// Include the footer template
include('../shared/footer.php');


?>