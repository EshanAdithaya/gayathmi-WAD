<?php
session_start();

// Check if user is logged in and is admin
include_once 'adminSession.php';

require_once "../../asset/php/config.php";

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_reservation':
                $sql = "INSERT INTO reservations (name, email, phone, reservation_date, reservation_time, guests, notes, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
                
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssssss", 
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['reservation_date'],
                        $_POST['reservation_time'],
                        $_POST['guests'],
                        $_POST['notes']
                    );
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Reservation added successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error adding reservation";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'update_reservation':
                $sql = "UPDATE reservations SET 
                        name = ?, 
                        email = ?, 
                        phone = ?,
                        reservation_date = ?,
                        reservation_time = ?,
                        guests = ?,
                        notes = ?,
                        status = ?
                        WHERE id = ?";
                
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssssssssi", 
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['reservation_date'],
                        $_POST['reservation_time'],
                        $_POST['guests'],
                        $_POST['notes'],
                        $_POST['status'],
                        $_POST['reservation_id']
                    );
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Reservation updated successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error updating reservation";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'update_status':
                $sql = "UPDATE reservations SET status = ? WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $_POST['status'], $_POST['reservation_id']);
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Status updated successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error updating status";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'delete_reservation':
                $sql = "UPDATE reservations SET status = 'cancelled' WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_POST['reservation_id']);
                    if(mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_msg'] = "Reservation cancelled successfully";
                    } else {
                        $_SESSION['error_msg'] = "Error cancelling reservation";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch reservations with search and filter functionality
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM reservations WHERE 1=1";

if($status_filter != 'all') {
    $sql .= " AND status = '$status_filter'";
}

if(!empty($date_filter)) {
    $sql .= " AND DATE(reservation_date) = '$date_filter'";
}

if(!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}

$sql .= " ORDER BY reservation_date DESC, reservation_time DESC";
$reservations = mysqli_query($conn, $sql);

include_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Admin Dashboard</title>
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

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Raleway', sans-serif;
        }

        .reservations-grid {
            display: grid;
            gap: 20px;
        }

        .reservation-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .reservation-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-group {
            margin-bottom: 10px;
        }

        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .reservation-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
            transition: background 0.3s;
        }

        .confirm-btn {
            background: #28a745;
            color: white;
        }

        .cancel-btn {
            background: #dc3545;
            color: white;
        }

        .edit-btn {
            background: #007bff;
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
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #444;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Raleway', sans-serif;
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }

            .reservation-header {
                flex-direction: column;
                gap: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Reservations</h1>
            <button class="action-btn confirm-btn" onclick="showAddModal()">Add New Reservation</button>
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

        <!-- Filters -->
        <form class="filters" method="GET">
            <input type="text" name="search" class="filter-input" 
                   placeholder="Search name, email or phone"
                   value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="status" class="filter-input">
                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>

            <input type="date" name="date" class="filter-input" 
                   value="<?php echo htmlspecialchars($date_filter); ?>">

            <button type="submit" class="action-btn confirm-btn">Filter</button>
        </form>

        <!-- Reservations Grid -->
        <div class="reservations-grid">
            <?php if(mysqli_num_rows($reservations) > 0): ?>
                <?php while($reservation = mysqli_fetch_assoc($reservations)): ?>
                    <div class="reservation-card">
                        <div class="reservation-header">
                            <h3><?php echo htmlspecialchars($reservation['name']); ?></h3>
                            <span class="reservation-status status-<?php echo $reservation['status']; ?>">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </div>

                        <div class="reservation-info">
                            <div class="info-group">
                                <div class="info-label">Contact Information</div>
                                <div class="info-value">
                                    Email: <?php echo htmlspecialchars($reservation['email']); ?><br>
                                    Phone: <?php echo htmlspecialchars($reservation['phone']); ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Reservation Details</div>
                                <div class="info-value">
                                    Date: <?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?><br>
                                    Time: <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?><br>
                                    Guests: <?php echo htmlspecialchars($reservation['guests']); ?>
                                </div>
                            </div>

                            <?php if($reservation['notes']): ?>
                                <div class="info-group">
                                    <div class="info-label">Special Notes</div>
                                    <div class="info-value"><?php echo htmlspecialchars($reservation['notes']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <?php if($reservation['status'] == 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="action-btn confirm-btn">Confirm</button>
                                </form>
                            <?php endif; ?>

                            <button class="action-btn edit-btn" 
                                    onclick="showEditModal(<?php echo htmlspecialchars(json_encode($reservation)); ?>)">
                                Edit
                            </button>

                            <?php if($reservation['status'] != 'cancelled'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_reservation">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="action-btn cancel-btn" 
                                            onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="reservation-card">
                    <p>No reservations found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Reservation Modal -->
    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add Reservation Details</h2>
            <form id="reservationForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add_reservation">
                <input type="hidden" name="reservation_id" id="reservationId">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="reservation_date">Reservation Date</label>
                    <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="reservation_time">Reservation Time</label>
                    <input type="time" id="reservation_time" name="reservation_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="guests">Number of Guests</label>
                    <input type="number" id="guests" name="guests" min="1" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="notes">Special Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group" id="statusGroup" style="display: none;">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="action-btn confirm-btn">Save Reservation</button>
                    <button type="button" class="action-btn cancel-btn" onclick="hideModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show/Hide Modal Functions
        function showModal() {
            document.getElementById('reservationModal').style.display = 'flex';
        }

        function hideModal() {
            document.getElementById('reservationModal').style.display = 'none';
        }

        // Add New Reservation
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Reservation';
            document.getElementById('formAction').value = 'add_reservation';
            document.getElementById('reservationId').value = '';
            document.getElementById('statusGroup').style.display = 'none';
            document.getElementById('reservationForm').reset();

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('reservation_date').min = today;

            showModal();
        }

        // Edit Existing Reservation
        function showEditModal(reservation) {
            document.getElementById('modalTitle').textContent = 'Edit Reservation';
            document.getElementById('formAction').value = 'update_reservation';
            document.getElementById('reservationId').value = reservation.id;
            document.getElementById('statusGroup').style.display = 'block';

            // Populate form fields
            document.getElementById('name').value = reservation.name;
            document.getElementById('email').value = reservation.email;
            document.getElementById('phone').value = reservation.phone;
            document.getElementById('reservation_date').value = reservation.reservation_date.split(' ')[0];
            document.getElementById('reservation_time').value = reservation.reservation_time.split(' ')[0];
            document.getElementById('guests').value = reservation.guests;
            document.getElementById('notes').value = reservation.notes;
            document.getElementById('status').value = reservation.status;

            showModal();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reservationModal');
            if (event.target === modal) {
                hideModal();
            }
        }

        // Form Validation
        document.getElementById('reservationForm').addEventListener('submit', function(event) {
            const reservationDate = new Date(document.getElementById('reservation_date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (reservationDate < today) {
                alert('Please select a future date for the reservation.');
                event.preventDefault();
                return false;
            }

            return true;
        });
    </script>
</body>
</html>