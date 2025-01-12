<?php
session_start();

// Check if user is logged in and is admin
include_once 'adminSession.php';

require_once "../../asset/php/config.php";

// Process user actions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        
        switch($_POST['action']) {
            case 'verify':
                $sql = "UPDATE users SET is_verified = 1 WHERE id = ?";
                $message = "User verified successfully";
                break;
                
            case 'unverify':
                $sql = "UPDATE users SET is_verified = 0 WHERE id = ?";
                $message = "User verification removed";
                break;
                
            case 'make_admin':
                $sql = "UPDATE users SET is_admin = 1 WHERE id = ?";
                $message = "User promoted to admin";
                break;
                
            case 'remove_admin':
                // Prevent removing admin status from self
                if($user_id == $_SESSION['id']) {
                    $_SESSION['error_msg'] = "Cannot remove your own admin status";
                    header("Location: admin_users.php");
                    exit;
                }
                $sql = "UPDATE users SET is_admin = 0 WHERE id = ?";
                $message = "Admin status removed";
                break;
                
            case 'delete':
                // Prevent deleting self
                if($user_id == $_SESSION['id']) {
                    $_SESSION['error_msg'] = "Cannot delete your own account";
                    header("Location: admin_users.php");
                    exit;
                }
                $sql = "DELETE FROM users WHERE id = ?";
                $message = "User deleted successfully";
                break;
        }
        
        if(isset($sql)) {
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                if(mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_msg'] = $message;
                } else {
                    $_SESSION['error_msg'] = "Error performing action";
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        header("Location: admin_users.php");
        exit;
    }
}

// Fetch users with search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT * FROM users WHERE 1=1";

if(!empty($search)) {
    $sql .= " AND (email LIKE ? OR username LIKE ?)";
}

switch($filter) {
    case 'admin':
        $sql .= " AND is_admin = 1";
        break;
    case 'verified':
        $sql .= " AND is_verified = 1";
        break;
    case 'unverified':
        $sql .= " AND is_verified = 0";
        break;
}

$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if(!empty($search)) {
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-title {
            font-size: 24px;
            color: #333;
        }

        .search-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
            min-width: 200px;
            font-family: 'Raleway', sans-serif;
        }

        .filter-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Raleway', sans-serif;
        }

        .search-btn {
            padding: 8px 20px;
            background: #de6900;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .user-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-admin {
            background: #cce5ff;
            color: #004085;
        }

        .status-verified {
            background: #d4edda;
            color: #155724;
        }

        .status-unverified {
            background: #fff3cd;
            color: #856404;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            font-family: 'Raleway', sans-serif;
        }

        .verify-btn {
            background: #28a745;
            color: white;
        }

        .unverify-btn {
            background: #ffc107;
            color: #333;
        }

        .admin-btn {
            background: #007bff;
            color: white;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .search-filters {
                flex-direction: column;
            }

            .users-table {
                display: block;
                overflow-x: auto;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Manage Users</h1>
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

        <form method="GET" class="search-filters">
            <input type="text" name="search" class="search-input" 
                   placeholder="Search by email or username" 
                   value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="filter" class="filter-select">
                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Users</option>
                <option value="admin" <?php echo $filter == 'admin' ? 'selected' : ''; ?>>Admins</option>
                <option value="verified" <?php echo $filter == 'verified' ? 'selected' : ''; ?>>Verified</option>
                <option value="unverified" <?php echo $filter == 'unverified' ? 'selected' : ''; ?>>Unverified</option>
            </select>

            <button type="submit" class="search-btn">Search</button>
        </form>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if($user['is_admin']): ?>
                                    <span class="user-status status-admin">Admin</span>
                                <?php endif; ?>
                                
                                <?php if($user['is_verified']): ?>
                                    <span class="user-status status-verified">Verified</span>
                                <?php else: ?>
                                    <span class="user-status status-unverified">Unverified</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    
                                    <?php if(!$user['is_verified']): ?>
                                        <button type="submit" name="action" value="verify" 
                                                class="action-btn verify-btn">Verify</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="unverify" 
                                                class="action-btn unverify-btn">Unverify</button>
                                    <?php endif; ?>

                                    <?php if(!$user['is_admin']): ?>
                                        <button type="submit" name="action" value="make_admin" 
                                                class="action-btn admin-btn">Make Admin</button>
                                    <?php else: ?>
                                        <?php if($user['id'] != $_SESSION['id']): ?>
                                            <button type="submit" name="action" value="remove_admin" 
                                                    class="action-btn admin-btn">Remove Admin</button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if($user['id'] != $_SESSION['id']): ?>
                                        <button type="submit" name="action" value="delete" 
                                                class="action-btn delete-btn" 
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>