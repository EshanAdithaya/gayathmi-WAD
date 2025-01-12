<?php
session_start();

// Check if user is logged in and is admin
include_once 'adminSession.php';

require_once "../../asset/php/config.php";

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            // Category Actions
            case 'add_category':
                $sql = "INSERT INTO menu_categories (name, display_order, status) VALUES (?, ?, 'active')";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $_POST['category_name'], $_POST['display_order']);
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Category added successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error adding category";
                    }
                }
                break;

            case 'edit_category':
                $sql = "UPDATE menu_categories SET name = ?, display_order = ? WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sii", $_POST['category_name'], $_POST['display_order'], $_POST['category_id']);
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Category updated successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error updating category";
                    }
                }
                break;

            case 'delete_category':
                // First check if category has items
                $sql = "SELECT COUNT(*) FROM menu_items WHERE category_id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_POST['category_id']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $item_count);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    if($item_count > 0) {
                        $_SESSION['error_msg'] = "Cannot delete category with existing items";
                    } else {
                        $sql = "UPDATE menu_categories SET status = 'inactive' WHERE id = ?";
                        if($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $_POST['category_id']);
                            if(mysqli_stmt_execute($stmt)) {
                                $_SESSION['success_msg'] = "Category deleted successfully";
                            } else {
                                $_SESSION['error_msg'] = "Error deleting category";
                            }
                        }
                    }
                }
                break;

            // Menu Item Actions
            case 'add_item':
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
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Menu item added successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error adding menu item";
                    }
                }
                break;

            case 'edit_item':
                $image_path = $_POST['current_image'];
                if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "uploads/menu/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image_path = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
                    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
                }

                $sql = "UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "issdsi", 
                        $_POST['category_id'],
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['price'],
                        $image_path,
                        $_POST['item_id']
                    );
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Menu item updated successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error updating menu item";
                    }
                }
                break;

            case 'delete_item':
                $sql = "UPDATE menu_items SET status = 'inactive' WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_POST['item_id']);
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Menu item deleted successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error deleting menu item";
                    }
                }
                break;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch categories
$sql_categories = "SELECT * FROM menu_categories WHERE status = 'active' ORDER BY display_order";
$categories = mysqli_query($conn, $sql_categories);

// Fetch menu items
$sql_items = "SELECT i.*, c.name as category_name 
              FROM menu_items i 
              LEFT JOIN menu_categories c ON i.category_id = c.id 
              WHERE i.status = 'active'
              ORDER BY c.display_order, i.name";
$items = mysqli_query($conn, $sql_items);

include_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
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

        .header-buttons {
            display: flex;
            gap: 15px;
        }

        .add-btn {
            background: #de6900;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .add-btn:hover {
            background: #c25900;
        }

        .categories-section {
            margin-bottom: 40px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-info h3 {
            margin: 0;
            color: #333;
        }

        .category-actions {
            display: flex;
            gap: 10px;
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
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #444;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Raleway', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #de6900;
            outline: none;
        }

        .success-msg, .error-msg {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .header-buttons {
                flex-direction: column;
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }

            .menu-items-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Menu Management</h1>
            <div class="header-buttons">
                <button class="add-btn" onclick="showCategoryModal()">Add Category</button>
                <button class="add-btn" onclick="showItemModal()">Add Menu Item</button>
            </div>
        </div>

        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="success-msg">
                <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="error-msg">
                <?php 
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Categories Section -->
        <div class="categories-section">
            <h2>Categories</h2>
            <div class="categories-grid">
                <?php 
                mysqli_data_seek($categories, 0);
                while($category = mysqli_fetch_assoc($categories)): 
                ?>
                <div class="category-card">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <small>Order: <?php echo $category['display_order']; ?></small>
                    </div>
                    <div class="category-actions">
                        <button class="edit-btn" onclick="showEditCategoryModal(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $category['id'],
                                'name' => $category['name'],
                                'display_order' => $category['display_order']
                            ])); 
                        ?>)">Edit</button>
                        <button class="delete-btn" onclick="deleteCategory(<?php echo $category['id']; ?>)">Delete</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Menu Items Section -->
        <h2>Menu Items</h2>
        <div class="menu-items-grid">
            <?php while($item = mysqli_fetch_assoc($items)): ?>
            <div class="menu-item-card">
                <?php if($item['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                <?php else: ?>
                    <img src="../../image/default-dish.jpg" alt="Default" class="item-image">
                <?php endif; ?>
                <div class="item-details">
                    <h3><?php echo htmlspecialchars($item['name']); ?> </h3>
                    <p class="category-name"><?php echo htmlspecialchars($item['category_name']); ?></p>
                    <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                    <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                    <div class="item-actions">
                        <button class="edit-btn" onclick="showEditItemModal(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $item['id'],
                                'name' => $item['name'],
                                'category_id' => $item['category_id'],
                                'description' => $item['description'],
                                'price' => $item['price'],
                                'image_url' => $item['image_url']
                            ])); 
                        ?>)">Edit</button>
                        <button class="delete-btn" onclick="deleteItem(<?php echo $item['id']; ?>)">Delete</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="hideCategoryModal()">&times;</span>
            <h2 id="categoryModalTitle">Add Category</h2>
            <form id="categoryForm" method="POST">
                <input type="hidden" name="action" id="categoryFormAction" value="add_category">
                <input type="hidden" name="category_id" id="categoryId" value="">

                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>

                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" min="0" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="add-btn">Save Category</button>
                    <button type="button" onclick="hideCategoryModal()" style="margin-left: 10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Menu Item Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="hideItemModal()">&times;</span>
            <h2 id="itemModalTitle">Add Menu Item</h2>
            <form id="itemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="itemFormAction" value="add_item">
                <input type="hidden" name="item_id" id="itemId" value="">
                <input type="hidden" name="current_image" id="currentImage" value="">

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
                            <?php echo htmlspecialchars($category['name']); ?>
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
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit" class="add-btn">Save Item</button>
                    <button type="button" onclick="hideItemModal()" style="margin-left: 10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Category Modal Functions
        function showCategoryModal() {
            document.getElementById('categoryModalTitle').textContent = 'Add Category';
            document.getElementById('categoryFormAction').value = 'add_category';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').style.display = 'flex';
        }

        function showEditCategoryModal(category) {
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categoryFormAction').value = 'edit_category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('category_name').value = category.name;
            document.getElementById('display_order').value = category.display_order;
            document.getElementById('categoryModal').style.display = 'flex';
        }

        function hideCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function deleteCategory(categoryId) {
            if(confirm('Are you sure you want to delete this category? This cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="${categoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Menu Item Modal Functions
        function showItemModal() {
            document.getElementById('itemModalTitle').textContent = 'Add Menu Item';
            document.getElementById('itemFormAction').value = 'add_item';
            document.getElementById('itemId').value = '';
            document.getElementById('currentImage').value = '';
            document.getElementById('itemForm').reset();
            document.getElementById('itemModal').style.display = 'flex';
        }

        function showEditItemModal(item) {
            document.getElementById('itemModalTitle').textContent = 'Edit Menu Item';
            document.getElementById('itemFormAction').value = 'edit_item';
            document.getElementById('itemId').value = item.id;
            document.getElementById('name').value = item.name;
            document.getElementById('category_id').value = item.category_id;
            document.getElementById('description').value = item.description;
            document.getElementById('price').value = item.price;
            document.getElementById('currentImage').value = item.image_url;
            document.getElementById('itemModal').style.display = 'flex';
        }

        function hideItemModal() {
            document.getElementById('itemModal').style.display = 'none';
        }

        function deleteItem(itemId) {
            if(confirm('Are you sure you want to delete this menu item?')) {
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

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target === document.getElementById('categoryModal')) {
                hideCategoryModal();
            }
            if (event.target === document.getElementById('itemModal')) {
                hideItemModal();
            }
        }
    </script>
</body>
</html>