<?php
require_once '../shared/header.php';
global $conn;
$targetFolder = '../shared/assets/';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    dislplayInitForm();
    $result = queryMysql("SELECT * FROM menu_items");
    if ($result->num_rows > 0) {
        echo '<div class="card-container">';

        while ($row = $result->fetch_assoc()) {
            $item_id = $row['item_id'];
            $name = reverseSanitizeString($row['name']);
            $description = reverseSanitizeString($row['description']);

            echo '<div class="card">';
            echo '<div class="card-image"><img src="' . $row["image"] . '" alt="Item Image"></div>';
            echo '<div class="card-details">';
            echo '<h2>' . $name . '</h2>';
            echo '<p class="desc">' . $description . '</p>';
            echo '<p>Price: R' . $row["price"] . '</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="item_id" value="' . $item_id . '"/>';
            echo '<button type="submit" name="edit-item">Edit</button>';
            echo '<button type="submit" name="delete-item">Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="card-container">';
        echo "No data found.";
        echo '</div>';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['item_id']))$item_id = $_POST['item_id'];

    if (isset($_POST['add-item'])) {
        addMenuItem($targetFolder, $conn);
    } elseif (isset($_POST['delete-item'])) {
        deleteMenuItem($item_id, $conn);
    } elseif (isset($_POST['edit-item'])) {
        editMenuItem($item_id, $conn);
    }elseif (isset($_POST['update-item'])) {
        updateMenuItem($item_id, $conn);
    }
}

function dislplayInitForm()
{
    echo '<div class="center">';
    echo '<form data-ajax="false" method="post" action="manage_menu.php" enctype="multipart/form-data">';
    echo '<label for="name">Item Name:</label>';
    echo '<input type="text" name="name" required><br>';

    echo '<label for="price">Price:</label>';
    echo '<input type="number" step="1" name="price" required><br>';

    echo '<label for="description">Description:</label>';
    echo '<textarea name="description" rows="4" required></textarea><br>';

    echo '<label for="image_upload">Upload Image:</label>';
    echo '<input type="file" name="file" accept="image/*" required><br>';

    echo '<button type="submit" name="add-item">Add Item</button>';
    echo '</form>';
    echo '</div>';
}

function addMenuItem($targetFolder, $conn)
{
    if (isset($_POST['name'], $_POST['price'], $_POST['description'])) {
        $name = sanitizeString($_POST['name']);
        $price = $_POST['price'];
        $description = sanitizeString($_POST['description']);
        // Handle image upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $image_tmp_path = $_FILES['file']['tmp_name'];

            // Generate a unique filename for the image
            $image_filename = uniqid() . '_' . $_FILES['file']['name'];
            $image_path = $targetFolder . $image_filename;
            echo $image_path;

            // Move the uploaded image to the target folder
            if (move_uploaded_file($image_tmp_path, $image_path)) {
                // Prepare the SQL query with error handling
                $stmt = $conn->prepare("INSERT INTO Menu_Items (name, description, price, image) VALUES (?, ?, ?, ?)");

                if (!$stmt) {
                    $message = "Error preparing SQL statement: " . $conn->error;
                } else {

                    $stmt->bind_param("ssds", $name, $description, $price, $image_path);

                    if ($stmt->execute()) {
                        $message = "Item added successfully!";
                    } else {
                        $message = "Error adding item: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                header("Location: ../errors/500.php");
            }
        } else {
            header("Location: ../errors/500.php");
        }
    }
}

function deleteMenuItem($item_id, $conn)
{
    $stmt = $conn->prepare('DELETE FROM menu_items WHERE item_id = ?');
    if (!$stmt) {
        header("Location: ../errors/500.php");
    } else {
        $stmt->bind_param('i', $item_id);
        if ($stmt->execute()) {
            echo '<script> alert(" Item has been Deleted 😫")</script>';
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        } else {
            echo 'Error: ' . $stmt->error;
        }
    }

}

function editMenuItem($item_id, $conn)
{
    $stmt = $conn->prepare('SELECT * FROM menu_items WHERE item_id = ?');
    if (!$stmt) {
        header("Location: ../errors/500.php");
    } else {
        $stmt->bind_param('i', $item_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                // print_r($row);
                displayEditform($row); // Pass the result row to the displayEditform function
            } else {
                echo 'Item not found.';
            }
        } else {
            echo 'Error: ' . $stmt->error;
        }
    }
}

function displayEditform($row)
{
    // Create an HTML form and populate it with the item data
    ?>
    <form method="post">
        <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
        <label for="name">Item Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $row['name']; ?>"><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description"><?php echo $row['description']; ?></textarea><br>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="<?php echo $row['price']; ?>"><br>

        <label for="image">Image:</label>
        <button type="submit" name='update-item'>Update</button>
    </form>
    <?php
}

function updateMenuItem($item_id, $conn)
{
    // Check if the form was submitted for updating an item
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        // Create an SQL UPDATE statement to update the item
        $stmt = $conn->prepare('UPDATE menu_items SET name = ?, description = ?, price = ? WHERE item_id = ?');
        if (!$stmt) {
            header("Location: ../errors/500.php");
            exit; // Exit the script after redirection
        } else {
            $stmt->bind_param('ssdi', $name, $description, $price, $item_id);
            if ($stmt->execute()) {
                // Redirect to a success page or perform other actions
                echo '<script> alert(" Item has been UPDATED 😁")</script>';
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                
                exit; // Exit the script after redirection
            } else {
                echo 'Error: ' . $stmt->error;
            }
        }
    }


require_once '../shared/footer.php' ?>