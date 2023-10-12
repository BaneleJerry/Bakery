<?php
require_once '../shared/header.php';

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = queryMysql("SELECT * FROM menu_items");

    if ($result->num_rows > 0) {
        echo '<div class="card-container">';
        
        while ($row = $result->fetch_assoc()) {
            $item_id = $row['item_id'];
            $hasDiscount = hasDiscount($item_id);
            $name = reverseSanitizeString($row['name']);
            $description = reverseSanitizeString($row['description']);

            echo '<div class="card">';
            echo '<div class="card-image"><img src="' . $row["image"] . '" alt="Item Image"></div>';
            echo '<div class="card-details">';
            echo '<h2>' . $name . '</h2>';
            echo '<p class="desc">' . $description . '</p>';
            echo '<p>Price: R' . $row["price"] . '</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="item_id" value="' . $item_id . '" />';
            if ($hasDiscount) {
                echo '<button type="submit" name="edit-discount">Edit Discount</button>';
                echo '<button type="submit" name="delete-discount">Delete Discount</button>';
            } else {
                echo '<button type="submit" name="add-discount">Add Discount</button>';
            }
            echo '</form>';
            echo '</div></div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="card-container">No data found.</div>';
    }
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    if (isset($_POST['add-discount'])) {
        // Display a form to add a discount
        displayAddDiscountForm($item_id);
    } elseif (isset($_POST['edit-discount'])) {
        // Display the existing discount information and form to edit discount
        displayEditDiscountForm($item_id);
    } elseif (isset($_POST['update-discount'])) {
        // Handle the discount update
        handleDiscountUpdate($item_id);
    } elseif (isset($_POST['save-discount'])) {
        // Save the new discount
        saveNewDiscount($item_id);
    } elseif(isset($_POST['delete-discount'])) {
        handleDiscountDelete($item_id);
    }
}

require_once '../shared/footer.php';

// Helper functions
function hasDiscount($item_id) {
    $result = queryMysql("SELECT * FROM discounts WHERE item_id = $item_id");
    return $result->num_rows > 0;
}

function displayEditDiscountForm($item_id) {
    $row = queryMysql("SELECT * FROM discounts WHERE item_id = $item_id")->fetch_assoc();
    echo '<div class="discount-info">';
    echo '<h3>Discount Information for Item ID: ' . $item_id . '</h3>';
    echo '<p>Discount ID: ' . $row['discount_id'] . '</p>';
    echo '<p>Discount Amount: ' . $row['discount'] . '</p>';
    echo '<form method="post" onsubmit="return checkDiscount();">';
    echo '<label for="discount-percent">Enter Discount Percentage (1-100)</label>';
    echo '<input type="hidden" name="item_id" value="' . $item_id . '">';
    echo '<input id="discount-percent" type="number" name="discount-percent" value="' . $row['discount'] . '">';
    echo '<button type="submit" name="update-discount">Save</button>';
    echo '</form></div>';
}

function displayAddDiscountForm($item_id) {
    echo '<div class="container">';
    echo '<form method="post" onsubmit="return checkDiscount();">';
    echo '<label for="discount-percent">Enter Discount Percentage (1-100)</label>';
    echo '<input id="discount-percent" type="number" name="discount-percent">';
    echo '<input type="hidden" name="item_id" value="' . $item_id . '">';
    echo '<button type="submit" name="save-discount">Save</button>';
    echo '</form></div>';
}

function handleDiscountUpdate($item_id) {
    $discount = $_POST['discount-percent'];
    global $conn;
    $stmt = $conn->prepare('UPDATE discounts SET discount = ? WHERE item_id = ?');
    $stmt->bind_param('ii', $discount, $item_id);

    if ($stmt->execute()) {
        echo '<script> alert("Discount updated successfully 😁")</script>';
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    } else {
        echo 'Error: ' . $stmt->error;
    }
}

function saveNewDiscount($item_id) {
    $discount = $_POST['discount-percent'];
    global $conn;
    $stmt = $conn->prepare('INSERT INTO discounts (item_id, discount) VALUES (?, ?)');
    $stmt->bind_param('ii', $item_id, $discount);

    if ($stmt->execute()) {
        echo '<script> alert("Discount added successfully 😁")</script>';
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    } else {
        echo 'Error: ' . $stmt->error;
    }
}

function handleDiscountDelete($item_id){
    global $conn;
    $stmt = $conn->prepare('DELETE FROM discounts WHERE item_id=?');
    $stmt->bind_param('i', $item_id);

    if ($stmt->execute()) {
        // Success message
        echo '<script>alert("Discount Delete successfully 😫");</script>';
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    } else {
        // Error handling
        echo 'Error: ' . $stmt->error;
    }
}

?>
