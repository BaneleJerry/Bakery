<?php
require_once '../shared/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['complete-order'])) {
        $delivery_address = $_POST['delivery_address'];
        if (empty($delivery_address)) {
            echo "Please enter a delivery address.";
        } else {
            $order_id = completeOrder($delivery_address);
            $deliveryStatus = getDeliveryStatus($order_id);
            echo "Delivery Status: $deliveryStatus";
            echo "You Be Redirected in 5 seconds";

            // JavaScript countdown and redirection
            echo '<script>
                    var countdown = 5; // Set the countdown time in seconds
                    var timer = setInterval(function() {
                        countdown--;
                        if (countdown <= 0) {
                            clearInterval(timer);
                            window.location.href = "catalog.php"; // Redirect to catalog.php
                        }
                    }, 1000); // 1000 milliseconds = 1 second
                </script>';

        }
    } else {
        initPage();
    }
}

// Display the form to get the delivery address
function initPage()
{
    echo <<<HTML
    <div style="display: flex; flex-direction: column">
    <form method="POST" action="">
        <label for="delivery_address">Delivery Address:</label>
        <textarea type="text" name="delivery_address" id="delivery_address" required></textarea>
HTML;
    foreach ($_SESSION['cart'] as $cartItem) {
        $item_id = $cartItem['item_id'];
        // Get the quantity for this item from the cart
        $quantity = $_POST['quantity'][$item_id] ?? 0; // Use a default of 0 if not set
        // Create hidden input fields for quantities
        echo <<<HTML
        <input type="hidden" name="quantity[{$item_id}]" value="{$quantity}">
HTML;
    }
    echo <<<HTML
        <input type="submit" name="complete-order" value="Complete Order">
    </form>
HTML;


    //   print_r($_SESSION['cart']);
// Display the table of items
    if (!empty($_SESSION['cart'])) {
        echo '<div class="table-container">';
        echo '<table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Image</th>
            </tr>';

        foreach ($_SESSION['cart'] as $cartItem) {
            $item_id = $cartItem['item_id'];
            $quantity = $_POST['quantity'][$item_id];

            // Check stock level before displaying
            $stock_query = "SELECT stock_quantity FROM inventory WHERE item_id = $item_id";
            $stock_result = queryMysql($stock_query);
            if ($stock_result && $stock_result->num_rows > 0) {
                $stock_row = $stock_result->fetch_assoc();
                $stock_quantity = $stock_row['stock_quantity'];

                // Get item details from menu_items table
                $item_query = "SELECT name, description, image FROM menu_items WHERE item_id = $item_id";
                $item_result = queryMysql($item_query);
                if ($item_result && $item_result->num_rows > 0) {
                    $item_row = $item_result->fetch_assoc();
                    $item_name = $item_row['name'];
                    $item_description = $item_row['description'];
                    $item_image = $item_row['image'];

                    // Display the item in the table
                    echo '<tr>
                        <td>' . $item_name . ' - ' . $item_description . '</td>
                        <td>' . $quantity . '</td>
                        <td><img src="' . $item_image . '" alt="' . $item_name . '"></td>
                      </tr>';
                } else {
                    // Handle error if the item is not found in the menu_items table
                    echo '<tr>
                        <td colspan="3">Item not found</td>
                      </tr>';
                }
            } else {
                // Handle error if the item is not found in the inventory
                echo '<tr>
                    <td colspan="3">Item not found</td>
                  </tr>';
            }
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
}

function completeOrder($delivery_address)
{
    $username = $_SESSION['username'];
    $result = queryMysql("SELECT customer_id FROM Customers WHERE username='$username'");
    $row = $result->fetch_row();
    $customer_id = $row[0];
    $delivery_date = date('Y-m-d H:i:s', strtotime('+10 days'));
    // print_r(gettype($customer_id));
    $order_id = createOrder($customer_id, $delivery_date);
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cartItem) {
            $item_id = $cartItem['item_id'];
            $quantity = $_POST['quantity'][$item_id];
            addOrderItem($order_id, $item_id, $quantity);
            updateStockQuantity($item_id, $quantity);
            createDelivery($order_id, $delivery_address);

        }
    }
    return $order_id;
}
function createOrder($customer_id, $delivery_date)
{
    $query = "INSERT INTO orders (customer_id, delivery_date, status) VALUES ('$customer_id', '$delivery_date', 'placed')";
    queryMysql($query);
    return db_connection()->insert_id; // Return the ID of the newly created order
}

function addOrderItem($order_id, $item_id, $quantity)
{
    $query = "INSERT INTO order_items (order_id, item_id, quantity) VALUES ('$order_id', '$item_id', '$quantity')";
    queryMysql($query);
}

function updateStockQuantity($item_id, $quantity)
{
    $query = "UPDATE inventory SET stock_quantity = stock_quantity - $quantity WHERE item_id = $item_id";
    queryMysql($query);
}

function createDelivery($order_id, $delivery_address)
{
    $query = "INSERT INTO deliveries (order_id, delivery_address, status) VALUES ('$order_id', '$delivery_address', 'scheduled')";
    queryMysql($query);
}
function getDeliveryStatus($order_id)
{
    $query = "SELECT status FROM deliveries WHERE order_id = '$order_id'";
    $result = queryMysql($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'];
    }

    return "Status not available"; // Return a default message if no status is found
}

?>