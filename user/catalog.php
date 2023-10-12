<?php
require_once "../shared/header.php";
echo <<<HTML
<script>
    var cart = '<li><a id="openOverlay" href="#">View Cart</a></li>';
    $('#nav-list').prepend(cart);
</script>
HTML;
// Check if the cart is already initialized in the session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array(); // Initialize an empty cart array
}

// Create an array to store item IDs in the cart
$cartItemIds = array();

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cartItem) {
        $cartItemIds[] = $cartItem['item_id'];
    }
}

$query = "SELECT mi.*, d.discount, i.stock_quantity
          FROM menu_items mi
          LEFT JOIN discounts d ON mi.item_id = d.item_id
          LEFT JOIN inventory i ON mi.item_id = i.item_id";

$result = queryMysql($query);
if ($result->num_rows > 0) {
    echo '<div class="card-container">';

    while ($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $name = reverseSanitizeString($row['name']);
        $description = reverseSanitizeString($row['description']);
        $price = $row["price"];
        $discount = $row["discount"]; // Get the discount as a percentage
        $stock_quantity = $row["stock_quantity"]; // Get the stock quantity

        echo '<div class="card">';

        // Conditionally display the discount image
        if ($discount > 0) {
            // Calculate the discounted price based on the percentage discount
            $discountedPrice = $price - ($price * ($discount / 100));
            echo '<img src="../static/images/Discount_logo.png" class="discount-indicator" alt="Discount">';
        } else {
            $discountedPrice = $price;
        }

        if ($stock_quantity <= 0) {
            echo '<img src="../static/images/Out.png" class="out-of-stock" alt="Discount">';
        }

        echo '<div class="card-image"><img src="' . $row["image"] . '" alt="Item Image"></div>';
        echo '<div class="card-details">';
        echo '<h2>' . $name . '</h2>';
        echo '<p class="desc">' . $description . '</p>';
        echo '<p>Price: R' . $price;

        // Check if a discount exists and display the discounted price if it does
        if ($discount > 0) {
            echo '<br>Discounted Price: R' . $discountedPrice;
        }
        echo '</p>';

        // Check if the item is out of stock and disable the "Add to Cart" button
        if ($stock_quantity <= 0) {
            echo '<p>Out of Stock</p>';
            echo '<button type="submit" disabled>Add to Cart</button>';
        } else {
            echo '<form method="post">';
            echo '<input type="hidden" name="item_id" value="' . $item_id . '">';
            echo '<input type="hidden" name="item_name" value="' . $name . '">';
            echo '<input type="hidden" name="item_price" value="' . $discountedPrice . '">';

            // Check if the item is in the cart and disable the button accordingly
            if (in_array($item_id, $cartItemIds)) {
                echo '<button type="button" disabled>Already in Cart</button>';
            } else {
                echo '<button type="submit" name="add-to-cart">Add to Cart</button>';
            }

            echo '</form>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
} else {
    echo "No menu items found.";
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add-to-cart'])) {
        addToCart();
    } elseif (isset($_POST['remove_item'])) {
        removeCartItem();
    }
}


function addToCart()
{
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
        reloadPage();
    }
}

function removeCartItem()
{
    // Get the item ID to remove
    $item_id_to_remove = $_POST['item_id_to_remove'];
    // Find the item in the cart and remove it
    foreach ($_SESSION['cart'] as $key => $cartItem) {
        if ($cartItem['item_id'] == $item_id_to_remove) {
            unset($_SESSION['cart'][$key]);
            reloadPage();
        }
    }
}

function reloadPage()
{
    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
}






echo '<div id="overlay" class="overlay">';
echo '<div class="overlay-content">';
echo '<div id="cart">';
// Display the cart contents here
if (!empty($_SESSION['cart'])) {
    echo '<h3>Your Cart</h3>';
    echo '<form method="post" action="checkout.php">';
    echo '<table>';
    echo '<tr><th>Item Name</th><th>Price</th><th>Quantity</th><th>Remove</th></tr>';
    foreach ($_SESSION['cart'] as $cartItem) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($cartItem['item_name']) . '</td>';
        echo '<td class="item-price">R' . number_format($cartItem['item_price'], 2) . '</td>';
        echo '<td>
        <input type="number" class="quantity-input" name="quantity[' . $cartItem['item_id'] . ']" value="1" min="1">
    </td>';
        echo '<td>
        <form method="post">
            <input type="hidden" name="item_id_to_remove" value="' . $cartItem['item_id'] . '">
            <button type="submit" name="remove_item">Remove</button>
        </form>
    </td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<p id="total-price">Total Price: R0.00</p>'; // Placeholder for total price
    echo '<button type="submit" name="checkout" id="checkout">Checkout</button>';
    echo '</form>';
} else {
    echo '<p>Your cart is empty.</p>';
}
echo '</div>';
echo '<button id="closeOverlay">Close</button>';


require_once '../shared/footer.php';
?>