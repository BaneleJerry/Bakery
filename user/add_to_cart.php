<?php
session_start();

// Check if the item_id, item_name, and item_price are provided via POST
if (isset($_POST['item_id'], $_POST['item_name'], $_POST['item_price'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    // Add the item to the cart
    $_SESSION['cart'][] = array(
        'item_id' => $item_id,
        'item_name' => $item_name,
        'item_price' => $item_price
    );

    // Return a success message (you can customize this as needed)
    echo json_encode(array('success' => true, 'message' => 'Item added to cart successfully.'));
} else {
    // Return an error message if the required data is not provided
    echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
}
?>
