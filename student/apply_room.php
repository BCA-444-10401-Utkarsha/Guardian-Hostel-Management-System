<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$error = '';
$success = '';

// Logic: Fetch Mess Fee from settings
$mess_fee_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'mess_fee_monthly'");
$mess_fee = 2000; // default
if ($mess_fee_query && mysqli_num_rows($mess_fee_query) > 0) {
    $mess_fee = (float)mysqli_fetch_assoc($mess_fee_query)['setting_value'];
}

// Logic: Rent Definitions
$room_rents = [
    'Single' => 5000,
    'Double' => 3500,
    'Triple' => 2500
];

// Logic: Check for active requests
$check_query = "SELECT * FROM room_requests WHERE student_id = $student_id AND status IN ('Pending', 'Approved')";
$check_result = mysqli_query($conn, $check_query);
$existing_request = null;
$has_existing = false;

if (mysqli_num_rows($check_result) > 0) {
    $existing_request = mysqli_fetch_assoc($check_result);
    $has_existing = true;
}

// Logic: Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_type = mysqli_real_escape_string($conn, $_POST['room_type']);
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $mess_included = isset($_POST['mess_included']) ? 1 : 0;

    if (!empty($room_type)) {
        $room_rent = $room_rents[$room_type];
        $total_amount = $room_rent + ($mess_included ? $mess_fee : 0);

        if (!$has_existing) {
            $query = "INSERT INTO room_requests (student_id, room_type, message, mess_included, total_amount) VALUES ($student_id, '$room_type', '$message', $mess_included, $total_amount)";
            if (mysqli_query($conn, $query)) {
                $request_id = mysqli_insert_id($conn);
                header("Location: payment.php?request_id=$request_id");
                exit();
            } else {
                $error = 'Failed to submit request. Please try again.';
            }
        } else {
            $error = 'You already have an active request.';
        }
    } else {
        $error = 'Please select a room type.';
    }
}

// Logic: Fetch available rooms summary
$rooms_query = "SELECT room_type, COUNT(*) as total, SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available, MIN(rent) as rent 
                FROM rooms 
                GROUP BY room_type";
$rooms_result = mysqli_query($conn, $rooms_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Room | GUARDIAN HOSTEL Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body { background: #0f172a; color: white; }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .booking-grid { display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start; }

        /* Available Room Cards */
        .inventory-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .room-stat-card { background: var(--glass); border: 1px solid var(--border); padding: 1.5rem; border-radius: 20px; transition: 0.3s; }
        .room-stat-card:hover { border-color: var(--primary); }
        .room-stat-card h3 { font-size: 1rem; margin-bottom: 10px; color: var(--primary); }
        .room-stat-card .price { font-size: 1.5rem; font-weight: 800; display: block; }
        .room-stat-card .stock { font-size: 0.8rem; color: #94a3b8; margin-top: 5px; }

        /* Existing Request Card */
        .status-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 24px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
        .status-item span { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; display: block; }
        .status-item strong { font-size: 1.1rem; }

        /* Application Form */
        .glass-form { background: var(--glass); backdrop-filter: blur(10px); border: 1px solid var(--border); border-radius: 24px; padding: 2.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 700; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }

        select, textarea {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border);
            border-radius: 12px; padding: 14px; color: white; outline: none; transition: 0.3s;
        }
        select:focus, textarea:focus { border-color: var(--primary); }

        /* Price Sidebar */
        .price-sticky { position: sticky; top: 100px; background: white; color: #0f172a; border-radius: 24px; padding: 2rem; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.95rem; }
        .price-total { border-top: 2px solid #f1f5f9; padding-top: 1rem; margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .price-total strong { font-size: 1.8rem; color: var(--primary); }

        .btn-submit {
            width: 100%; background: var(--primary); color: white; border: none; padding: 16px;
            border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 1.5rem;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }

        .btn-pay-action {
            display: block; width: 100%; text-align: center; background: white; color: var(--primary);
            text-decoration: none; padding: 14px; border-radius: 12px; font-weight: 800; margin-top: 1.5rem;
        }

        @media (max-width: 900px) { .booking-grid { grid-template-columns: 1fr; } .price-sticky { position: static; } }
    </style>
</head>
<body>

    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Reserve Your Space<span>.</span></h1>
            <p style="color: #94a3b8;">Select your preferred comfort level and join the community</p>
        </header>

        <div class="inventory-section">
            <?php while ($room = mysqli_fetch_assoc($rooms_result)): ?>
            <div class="room-stat-card">
                <h3><?php echo $room['room_type']; ?> Sharing</h3>
                <span class="price">₹<?php echo number_format($room['rent'], 0); ?></span>
                <p class="stock">
                    <i class="fas fa-check-circle"></i> <?php echo $room['available']; ?> Available
                </p>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="booking-grid">
            <div class="form-side">
                
                <?php if ($has_existing): ?>
                <div class="status-banner">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-receipt" style="font-size: 2rem;"></i>
                        <div>
                            <h2 style="font-weight: 800;">Active Application</h2>
                            <p style="opacity: 0.8; font-size: 0.9rem;">Reference ID: #<?php echo $existing_request['id']; ?></p>
                        </div>
                    </div>
                    
                    <div class="status-grid">
                        <div class="status-item"><span>Type</span><strong><?php echo $existing_request['room_type']; ?> Sharing</strong></div>
                        <div class="status-item"><span>Status</span><strong><?php echo $existing_request['status']; ?></strong></div>
                        <div class="status-item"><span>Total Amount</span><strong>₹<?php echo number_format($existing_request['total_amount']); ?></strong></div>
                        <div class="status-item"><span>Payment</span><strong><?php echo $existing_request['payment_status']; ?></strong></div>
                    </div>

                    <?php if ($existing_request['payment_status'] != 'Paid'): ?>
                        <a href="payment.php?request_id=<?php echo $existing_request['id']; ?>" class="btn-pay-action">
                            Complete Payment <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!$has_existing): ?>
                <div class="glass-form">
                    <?php if ($error): ?><div class="alert alert-danger" style="margin-bottom: 20px;"><?php echo $error; ?></div><?php endif; ?>
                    
                    <form id="roomForm" method="POST">
                        <div class="form-group">
                            <label>Preferred Room Type</label>
                            <select id="room_type" name="room_type" required onchange="updatePrice()">
                                <option value="">Select Accommodation</option>
                                <option value="Single" data-rent="5000">Single Sharing (Private)</option>
                                <option value="Double" data-rent="3500">Double Sharing (Balanced)</option>
                                <option value="Triple" data-rent="2500">Triple Sharing (Economy)</option>
                            </select>
                        </div>

                        <div style="background: rgba(99, 102, 241, 0.1); border: 1px dashed var(--primary); padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="fas fa-utensils"></i> Monthly Mess Plan</span>
                                <strong>₹<?php echo number_format($mess_fee); ?></strong>
                            </div>
                            <input type="hidden" name="mess_included" value="1">
                            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 5px;">Mandatory full-board meal plan included.</p>
                        </div>

                        <div class="form-group">
                            <label>Special Requests</label>
                            <textarea name="message" rows="4" placeholder="Roommate preferences, floor level, etc."></textarea>
                        </div>
                        
                        <div class="mobile-only-price">
                             </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="price-side">
                <div class="price-sticky">
                    <h3 style="margin-bottom: 1.5rem; font-weight: 800; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Cost Summary</h3>
                    
                    <div class="price-row">
                        <span>Room Rent</span>
                        <span id="display-rent">₹0</span>
                    </div>
                    <div class="price-row">
                        <span>Mess Charges</span>
                        <span>₹<?php echo number_format($mess_fee); ?></span>
                    </div>
                    <div class="price-row" style="color: #10b981; font-weight: 600;">
                        <span>Security Deposit</span>
                        <span>₹0.00</span>
                    </div>

                    <div class="price-total">
                        <span>Total Monthly</span>
                        <strong id="display-total">₹0</strong>
                    </div>

                    <?php if (!$has_existing): ?>
                        <button type="submit" form="roomForm" class="btn-submit">
                            Confirm Application
                        </button>
                    <?php else: ?>
                        <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 15px;">
                            You have an active request being processed.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        const messFee = <?php echo $mess_fee; ?>;
        
        function updatePrice() {
            const select = document.getElementById('room_type');
            const selected = select.options[select.selectedIndex];
            const rent = parseInt(selected.getAttribute('data-rent')) || 0;
            
            document.getElementById('display-rent').innerText = '₹' + rent.toLocaleString();
            document.getElementById('display-total').innerText = '₹' + (rent + messFee).toLocaleString();
        }
    </script>
</body>
</html>