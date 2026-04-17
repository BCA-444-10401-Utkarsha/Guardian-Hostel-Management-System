<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

/* --- Your Data Queries (Logic Unchanged) --- */
$request_query = "SELECT rr.*, r.room_number, r.room_type, r.rent 
                  FROM room_requests rr 
                  LEFT JOIN rooms r ON rr.room_id = r.id 
                  WHERE rr.student_id = $student_id 
                  ORDER BY rr.request_date DESC 
                  LIMIT 1";
$request_result = mysqli_query($conn, $request_query);
$current_request = mysqli_fetch_assoc($request_result);

$complaints_query = "SELECT * FROM complaints WHERE student_id = $student_id ORDER BY created_at DESC";
$complaints_result = mysqli_query($conn, $complaints_query);
$complaints_count = mysqli_num_rows($complaints_result);
$pending_complaints = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM complaints WHERE student_id = $student_id AND status = 'Pending'"));

$today_day = date('l');
$today_menu_query = "SELECT * FROM mess_menu WHERE day_of_week = '$today_day'";
$today_menu_result = mysqli_query($conn, $today_menu_query);
$today_menu = mysqli_fetch_assoc($today_menu_result);

$rent_status = null;
$rent_overdue = false;
if ($current_request && $current_request['status'] == 'Approved') {
    $last_rent_query = mysqli_query($conn, "SELECT MAX(payment_month) as last_paid FROM monthly_rent_payments WHERE student_id = $student_id AND payment_status = 'Success'");
    if ($last_rent_query && mysqli_num_rows($last_rent_query) > 0) {
        $last_rent = mysqli_fetch_assoc($last_rent_query);
        $last_paid_month = $last_rent['last_paid'];
        $current_month = date('Y-m-01');
        $rent_due_day = 5; 
        $current_due_date = date('Y-m-' . str_pad($rent_due_day, 2, '0', STR_PAD_LEFT));

        if (!$last_paid_month || $last_paid_month < $current_month) {
            if (strtotime($current_due_date) < time()) { $rent_overdue = true; }
            $rent_status = 'Due for ' . date('F Y', strtotime($current_month));
        } else {
            $rent_status = 'Paid till ' . date('M Y', strtotime($last_paid_month));
        }
    } else {
        $rent_status = 'Not Paid Yet';
        $rent_overdue = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GUARDIAN HOSTEL Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { 
            background-color: #0f172a; 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
        }

        /* The 2026 "Perfect" Mesh Background */
        .mesh-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                        radial-gradient(at 100% 0%, hsla(225,39%,30%,1) 0, transparent 50%);
        }

        .dashboard-main { padding: 3rem 0; }
        
        .welcome-section { margin-bottom: 3rem; }
        .welcome-section h2 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }
        .welcome-section p { color: var(--text-dim); margin-top: 5px; }

        /* Rent Alert - High Vibrancy */
        .rent-alert {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
            border-radius: 20px; padding: 1.5rem 2rem; margin-bottom: 2.5rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card {
            background: var(--glass-bg); backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border); border-radius: 24px;
            padding: 1.8rem; display: flex; align-items: center; gap: 1.5rem;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--primary); background: rgba(255,255,255,0.05); }
        .stat-icon { width: 55px; height: 55px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        
        /* Widget Sections */
        .widget-row { display: grid; grid-template-columns: 1.6fr 1fr; gap: 2rem; }
        .glass-widget { background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 28px; padding: 2.2rem; backdrop-filter: blur(15px); }
        
        .meal-pill {
            padding: 1.2rem; border-radius: 18px; border: 1px solid var(--glass-border);
            display: flex; flex-direction: column; gap: 8px; transition: 0.3s;
        }
        .meal-pill:hover { background: rgba(255,255,255,0.03); }
        .meal-tag { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

        .btn-action {
            background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border);
            padding: 1.2rem; border-radius: 18px; color: white; text-decoration: none;
            display: flex; align-items: center; gap: 1rem; font-weight: 600; transition: 0.3s;
        }
        .btn-action:hover { background: var(--primary); border-color: var(--primary); transform: translateX(10px); }
        .btn-action i { color: var(--primary); transition: 0.3s; }
        .btn-action:hover i { color: white; }

        @media (max-width: 1024px) { .widget-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <?php include 'includes/nav.php'; ?>

    <main class="container dashboard-main">
        
        <?php if ($rent_overdue): ?>
        <div class="rent-alert animate__animated animate__pulse animate__infinite">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <i class="fas fa-receipt" style="font-size: 2rem;"></i>
                <div>
                    <h3 style="font-weight: 800; margin-bottom: 2px;">Overdue Payment</h3>
                    <p style="opacity: 0.8; font-size: 0.9rem;"><?php echo $rent_status; ?>. Please clear your dues.</p>
                </div>
            </div>
            <a href="pay_rent.php" style="background: white; color: #ef4444; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.85rem;">PAY NOW</a>
        </div>
        <?php endif; ?>

        <header class="welcome-section">
            <h2>Welcome Back, <?php echo htmlspecialchars($student_name); ?><span>.</span></h2>
            <p><?php echo date('l, F jS'); ?></p>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;"><i class="fas fa-door-open"></i></div>
                <div>
                    <span style="color: var(--text-dim); font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Allocation</span>
                    <h3 style="font-size: 1.4rem;"><?php echo $current_request['room_number'] ?? 'Reviewing'; ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24;"><i class="fas fa-headset"></i></div>
                <div>
                    <span style="color: var(--text-dim); font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Complaints</span>
                    <h3 style="font-size: 1.4rem;"><?php echo $pending_complaints; ?> Pending</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #34d399;"><i class="fas fa-utensils"></i></div>
                <div>
                    <span style="color: var(--text-dim); font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Mess Gate</span>
                    <h3 style="font-size: 1.4rem;"><?php echo $today_menu ? 'Open' : 'Closed'; ?></h3>
                </div>
            </div>
        </div>

        <div class="widget-row">
            <div class="glass-widget">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="font-weight: 800; font-size: 1.2rem;">Today's Meals</h3>
                    <a href="mess.php" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.85rem;">View Full Schedule <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></a>
                </div>

                <?php if ($today_menu): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div class="meal-pill">
                        <span class="meal-tag" style="color: #fbbf24;">Breakfast</span>
                        <p style="font-weight: 600; font-size: 0.95rem;"><?php echo htmlspecialchars($today_menu['breakfast']); ?></p>
                    </div>
                    <div class="meal-pill">
                        <span class="meal-tag" style="color: #4ade80;">Lunch</span>
                        <p style="font-weight: 600; font-size: 0.95rem;"><?php echo htmlspecialchars($today_menu['lunch']); ?></p>
                    </div>
                    <div class="meal-pill">
                        <span class="meal-tag" style="color: #60a5fa;">Dinner</span>
                        <p style="font-weight: 600; font-size: 0.95rem;"><?php echo htmlspecialchars($today_menu['dinner']); ?></p>
                    </div>
                </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-dim);">
                        <i class="fas fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Menu is being updated by admin.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="glass-widget">
                <h3 style="font-weight: 800; font-size: 1.2rem; margin-bottom: 2rem;">Quick Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="complaint.php" class="btn-action">
                        <i class="fas fa-bug"></i> Report Maintenance
                    </a>
                    <a href="apply_room.php" class="btn-action">
                        <i class="fas fa-shuffle"></i> Request Room Change
                    </a>
                    <a href="profile.php" class="btn-action">
                        <i class="fas fa-fingerprint"></i> Identity & Settings
                    </a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>