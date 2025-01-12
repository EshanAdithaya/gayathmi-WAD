<?php
session_start();
require_once "asset/php/config.php";

$success_message = $error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $date = trim($_POST["date"]);
    $time = trim($_POST["time"]);
    $guests = trim($_POST["guests"]);
    $notes = trim($_POST["notes"]);

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || empty($guests)) {
        $error_message = "Please fill all required fields.";
    } else {
        // Check if the selected date is not in the past
        $selected_datetime = strtotime($date . ' ' . $time);
        if ($selected_datetime < strtotime('today')) {
            $error_message = "Please select a future date.";
        } else {
            // Insert reservation into database
            $sql = "INSERT INTO reservations (name, email, phone, reservation_date, reservation_time, guests, notes, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $phone, $date, $time, $guests, $notes);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Reservation request submitted successfully! We will confirm shortly.";
                    
                    // Send confirmation email
                    $to = $email;
                    $subject = "Reservation Confirmation - Chan's Food";
                    $message = "
                    <html>
                    <body>
                        <h2>Reservation Details</h2>
                        <p>Thank you for choosing Chan's Food!</p>
                        <p>Your reservation details:</p>
                        <ul>
                            <li>Date: $date</li>
                            <li>Time: $time</li>
                            <li>Number of Guests: $guests</li>
                        </ul>
                        <p>We will confirm your reservation shortly.</p>
                    </body>
                    </html>
                    ";
                    
                    sendEmail($to, $subject, $message);
                } else {
                    $error_message = "Something went wrong. Please try again.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get available time slots
$time_slots = [
    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', 
    '14:00', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation - Chan's Food</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .reservation-container {
            max-width: 800px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .reservation-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reservation-header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #222222;
            margin-bottom: 15px;
        }

        .reservation-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #de6900;
            outline: none;
        }

        .submit-btn {
            background: #de6900;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: #c25900;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .reservation-info {
            background: #fff3cd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .reservation-info h3 {
            color: #856404;
            margin-bottom: 10px;
        }

        .reservation-info ul {
            list-style: none;
            padding: 0;
        }

        .reservation-info li {
            margin-bottom: 8px;
            color: #666;
        }

        .reservation-info i {
            margin-right: 10px;
            color: #de6900;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .reservation-header h1 {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="index.php">home</a>
                <a href="menu.php">menu</a>
                <a href="reservations.php" class="active">reservations</a>
                <a href="contact us.php">contact us</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="profile.php">my account</a>
                    <a href="logout.php">logout</a>
                <?php else: ?>
                    <a href="login.php">login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="reservation-container">
        <div class="reservation-header">
            <h1>Make a Reservation</h1>
            <p>Book your table for an unforgettable dining experience</p>
        </div>

        <div class="reservation-info">
            <h3><i class="fa fa-info-circle"></i> Important Information</h3>
            <ul>
                <li><i class="fa fa-clock-o"></i> Lunch Hours: 11:00 AM - 2:30 PM</li>
                <li><i class="fa fa-clock-o"></i> Dinner Hours: 6:00 PM - 9:30 PM</li>
                <li><i class="fa fa-users"></i> For groups larger than 8, please call us directly</li>
                <li><i class="fa fa-calendar"></i> Reservations can be made up to 30 days in advance</li>
            </ul>
        </div>

        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="reservation-form">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests *</label>
                        <select id="guests" name="guests" required>
                            <?php for($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" 
                               min="<?php echo date('Y-m-d'); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="time">Time *</label>
                        <select id="time" name="time" required>
                            <?php foreach($time_slots as $slot): ?>
                                <option value="<?php echo $slot; ?>"><?php echo $slot; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Special Requests</label>
                        <textarea id="notes" name="notes" rows="4"></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Request Reservation</button>
            </form>
        </div>
    </div>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const date = new Date(document.getElementById('date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (date < today) {
                e.preventDefault();
                alert('Please select a future date.');
            }
        });

        // Disable past dates
        document.getElementById('date').addEventListener('input', function(e) {
            const selected = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selected < today) {
                this.value = today.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>