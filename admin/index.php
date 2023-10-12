<?php
 require_once '../shared/header.php';


// Check if the username is not logged in or does not have 'admin' role redirect to login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
?>

<!-- Bakery Owner Dashboard HTML content -->
<div class="container">
    <h1>Welcome, Bakery Owner!</h1>
    <!-- Display relevant information and links to other management pages -->
    <p>Manage your bakery's menu, orders, deliveries, prices, discounts, and inventory.</p>
    <ul>
        <li><a href="manage_menu.php">Menu Management</a></li>
        <li><a href="manage_orders.php">Order Management</a></li>
        <li><a href="manage_deliveries.php">Delivery Management</a></li>
        <li><a href="manage_prices.php">Price Management</a></li>
        <li><a href="manage_discounts.php">Discount Management</a></li>
        <li><a href="manage_inventory.php">Inventory Management</a></li>
    </ul>
</div>

<?php
// Include the footer template
require_once('..\shared\footer.php');
?>
