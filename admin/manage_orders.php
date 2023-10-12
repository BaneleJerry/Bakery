f<?php
require_once '../shared/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['view-order'])) {
        // getDeliveredOrders();
        getOrders();
        echo '<form method="POST">';
        echo '<input type="submit" name= "view-delivered-orders" value="View Delivered Orders">';
        echo '</form>';
    } elseif (isset($_GET['view-order'])) {
        getOrderItems();
    } 
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // handleStatusUpdate();
    if (isset($_POST['view-delivered-orders'])) {
        getDeliveredOrders();

}
}

function get_orders()
{
    $sql = "SELECT
                C.username AS Customer_Username,
                O.order_id,
                MAX(MI.name) AS Menu_Item_Name,
                MAX(MI.image) AS Menu_Item_Image,
                MAX(OI.quantity) AS quantity,
                MAX(O.order_date) AS order_date,
                MAX(O.status) AS status
            FROM
                customers AS C
                JOIN orders AS O ON C.customer_id = O.customer_id
                JOIN order_items AS OI ON O.order_id = OI.order_id
                JOIN menu_items AS MI ON OI.item_id = MI.item_id
            GROUP BY O.order_id";
    
    $result = queryMysql($sql);
    $menu_items = array();
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
    return $menu_items;
}

function getOrders()
{
    $result = queryMysql("SELECT * FROM menu_items");
    if ($result->num_rows > 0) {
        echo '<h2>Order Management</h2>';
        echo '<div class="container">';
        echo '<div class="card-container">';
        $orders = get_orders(); // You'll need a function to fetch orders from the database
        foreach ($orders as $order) {
            if ($order['status'] !== 'delivered') {
                echo "<div class='card'>";
                echo "<div class='card-header'>Order ID: " . $order['order_id'] . "</div>";
                echo "<div class='card-body'>";
                echo "<p>Customer Name: " . $order['Customer_Username'] . "</p>";
                echo "<p>Order Date: " . $order['order_date'] . "</p>";
                echo "<p>Status: " . $order['status'] . "</p>";
                echo "</div>";
                echo "<div class='card-footer'>";
                // Add a "View Details" button that links to view_order.php
                echo "<form method='GET'>";
                echo "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
                echo "<input type='submit' name='view-order' value='View Details'>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "</div>";
    }
}

function getOrderItems()
{
    if (isset($_GET['order_id'])) {
        $order_id = $_GET['order_id'];
        // Get the current order status from the database
        $status_query = queryMysql("SELECT status FROM orders WHERE order_id = $order_id");
        if ($status_query->num_rows === 1) {
            $current_status = $status_query->fetch_assoc()['status'];
        } else {
            echo "Invalid order ID.";
            return;
        }

        // Display order details
        echo '<div class="flex-container">';
        echo '<h2>Order Details</h2>';
        echo "<p>Order ID: $order_id</p>";
        $sql = "SELECT MI.name AS Menu_Item_Name, MI.image AS Menu_Item_Image, OI.quantity
                FROM order_items AS OI
                JOIN menu_items AS MI ON OI.item_id = MI.item_id
                WHERE OI.order_id = $order_id";
        $result = queryMysql($sql);
        if ($result->num_rows > 0) {
            echo '<h3>Order Items</h3>';
            echo '<table data-role="table" id="order-items-table" data-mode="reflow" class="ui-responsive table-stroke">';
            echo '<thead>';
            echo '<tr>';
            echo '<th data-priority="1">Item Name</th>';
            echo '<th data-priority="3">Quantity</th>';
            echo '<th data-priority="2">Item Image</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            while ($item = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $item['Menu_Item_Name'] . '</td>';
                echo '<td>' . $item['quantity'] . '</td>';
                echo '<td><img src="' . $item['Menu_Item_Image'] . '" alt="Item Image" style="max-width: 100px;"></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            
            // Display the form to change the order status
            echo '<form method="POST">';
            echo '<input type="hidden" name="order_id" value="' . $order_id . '">';
            echo '<label for="new_status">Change Order Status:</label>';
            echo '<select name="new_status" id="new_status">';
            echo '<option value="placed" ' . ($current_status === 'placed' ? 'selected' : '') . '>Placed</option>';
            echo '<option value="prepared" ' . ($current_status === 'prepared' ? 'selected' : '') . '>Prepared</option>';
            echo '<option value="out_for_delivery" ' . ($current_status === 'out_for_delivery' ? 'selected' : '') . '>Out for Delivery</option>';
            echo '<option value="delivered" ' . ($current_status === 'delivered' ? 'selected' : '') . '>Delivered</option>';
            echo '</select>';
            echo '<input type="submit" name="submit" value="Update Status">';
            echo '</form>';
        } else {
            echo '<p>No items found for this order.</p>';
        }
        echo '</div>';
    } else {
        echo "Invalid request.";
    }
}

function getDeliveredOrders()
{
    $result = queryMysql("SELECT * FROM menu_items");
    if ($result->num_rows > 0) {
        
        echo '<div class="container">';
        echo '<h2>Delivered Orders</h2>';
        echo '<div class="card-container">';
        $orders = get_orders(); // You'll need a function to fetch orders from the database
        foreach ($orders as $order) {
            if ($order['status'] === 'delivered') {
                echo "<div class='card'>";
                echo "<div class='card-header'>Order ID: " . $order['order_id'] . "</div>";
                echo "<div class='card-body'>";
                echo "<p>Customer Name: " . $order['Customer_Username'] . "</p>";
                echo "<p>Order Date: " . $order['order_date'] . "</p>";
                echo "<p>Status: " . $order['status'] . "</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "</div>";
        echo "</div>";
    }
}

function handleStatusUpdate()
{
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        // Update the order status in the database
        $sql = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
        queryMysql($sql);

        // Redirect back to the order details page
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit;
    } else {
        echo "Invalid request.";
    }
}

require_once '../shared/footer.php';
?>
