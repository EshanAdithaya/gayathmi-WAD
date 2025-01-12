<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "asset/php/config.php";

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_item':
                // Handle file upload
                $image_path = '';
                if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "uploads/menu/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image_path = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
                    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
                }

                $sql = "INSERT INTO menu_items (category_id, name, description, price, image_url, status) 
                        VALUES (?, ?, ?, ?, ?, 'active')";
                
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "issds", 
                        $_POST['category_id'],
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['price'],
                        $image_path
                    );
                    
                    mysqli_stmt_execute($stmt);
                }
                break;

            case 'edit_item':
                // Similar logic for editing items
                break;

            case 'delete_item':
                $sql = "UPDATE menu_items SET status = 'inactive' WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_POST['item_id']);
                    mysqli_stmt_execute($stmt);
                }
                break;
        }
    }
}

// Fetch categories
$sql_categories = "SELECT * FROM menu_categories WHERE status = 'active'";
$categories = mysqli_query($conn, $sql_categories);

// Fetch menu items
$sql_items = "SELECT i.*, c.name as category_name 
              FROM menu_items i 
              LEFT JOIN menu_categories c ON i.category_id = c.id 
              WHERE i.status = 'active'";
$items = mysqli_query($conn, $sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 0 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .add-item-btn {
            background: #de6900;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .menu-item-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .item-details {
            padding: 15px;
        }

        .item-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .edit-btn {
            background: #4CAF50;
            color: white;
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Admin Dashboard</a>
            <div class="navbar-menu">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_menu.php" class="active">Menu</a>
                <a href="admin_orders.php">Orders</a>
                <a href="admin_users.php">Users</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Menu Management</h1>
            <button class="add-item-btn" onclick="showAddModal()">Add New Item</button>
        </div>

        <div class="menu-items-grid">
            <?php while($item = mysqli_fetch_assoc($items)): ?>
            <div class="menu-item-card">
                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                <div class="item-details">
                    <h3><?php echo $item['name']; ?></h3>
                    <p><?php echo $item['category_name']; ?></p>
                    <p>$<?php echo number_format($item['price'], 2); ?></p>
                    <div class="item-actions">
                        <button class="edit-btn" onclick="showEditModal(<?php echo $item['id']; ?>)">Edit</button>
                        <button class="delete-btn" onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Item</h2>
            <form id="itemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add_item">
                <input type="hidden" name="item_id" id="itemId" value="">

                <div class="form-group">
                    <label for="name">Item Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while($category = mysqli_fetch_assoc($categories)): 
                        ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo $category['name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit" class="add-item-btn">Save Item</button>
                    <button type="button" onclick="hideModal()" style="margin-left: 10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('formAction').value = 'add_item';
            document.getElementById('itemId').value = '';
            document.getElementById('itemForm').reset();
            document.getElementById('itemModal').style.display = 'flex';
        }

        function showEditModal(itemId) {
            // Fetch item details and populate form
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('formAction').value = 'edit_item';
            document.getElementById('itemId').value = itemId;
            document.getElementById('itemModal').style.display = 'flex';
        }

        function hideModal() {
            document.getElementById('itemModal').style.display = 'none';
        }

        function deleteItem(itemId) {
            if(confirm('Are you sure you want to delete this item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_item">
                    <input type="hidden" name="item_id" value="${itemId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === document.getElementById('itemModal')) {
                hideModal();
            }
        }
    </script>
</body>
</html>