<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Logic: Statistics
$total_students_with_rooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests WHERE status = 'Approved' AND payment_status = 'Paid'"))['count'];
$current_month = date('Y-m-01');
$paid_current_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as count FROM monthly_rent_payments WHERE payment_month = '$current_month' AND payment_status = 'Success'"))['count'];
$pending_current_month = $total_students_with_rooms - $paid_current_month;
$total_collected_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM monthly_rent_payments WHERE payment_month = '$current_month' AND payment_status = 'Success'"))['total'];
$total_collected_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM monthly_rent_payments WHERE payment_status = 'Success'"))['total'];

// Logic: Fetch Student Status
$students_query = "SELECT s.id, s.name, s.email, s.phone, s.gender, rr.room_type, rr.total_amount as monthly_rent, rr.room_allocation_date, (SELECT MAX(payment_month) FROM monthly_rent_payments WHERE student_id = s.id AND payment_status = 'Success') as last_paid_month, (SELECT COUNT(*) FROM monthly_rent_payments WHERE student_id = s.id AND payment_status = 'Success') as total_payments, (SELECT SUM(total_amount) FROM monthly_rent_payments WHERE student_id = s.id AND payment_status = 'Success') as total_paid FROM students s JOIN room_requests rr ON s.id = rr.student_id WHERE rr.status = 'Approved' AND rr.payment_status = 'Paid' ORDER BY last_paid_month ASC, s.name ASC";
$students_result = mysqli_query($conn, $students_query);

// Logic: Fetch Recent Payments
$payments_query = "SELECT mrp.*, s.name as student_name, s.email, rr.room_type FROM monthly_rent_payments mrp JOIN students s ON mrp.student_id = s.id JOIN room_requests rr ON mrp.room_request_id = rr.id ORDER BY mrp.payment_date DESC LIMIT 50";
$payments_result = mysqli_query($conn, $payments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Hub | GUARDIAN HOSTEL Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        :root {
            --primary: #fbbf24;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #020617; color: white; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #020617 50%); }

        .page-header { padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }

        /* Stats Grid */
        .financial-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .fin-card { background: var(--glass); border: 1px solid var(--border); border-radius: 24px; padding: 1.5rem; display: flex; align-items: center; gap: 1.2rem; }
        .fin-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

        /* Master Table Panels */
        .glass-panel { background: var(--glass); border-radius: 32px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 3rem; backdrop-filter: blur(15px); }
        .panel-head { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.01); display: flex; justify-content: space-between; align-items: center; }
        
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 1.5rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        /* Status Badges */
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .badge-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-overdue { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .badge-advance { background: rgba(99, 102, 241, 0.1); color: #818cf8; }
        .badge-never { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        .btn-call { width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; }
        .btn-call:hover { background: #10b981; color: white; }

        @media (max-width: 1000px) { th:nth-child(2), td:nth-child(2), th:nth-child(6), td:nth-child(6) { display: none; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Rent & Financial Hub<span>.</span></h1>
            <p style="color: var(--text-dim);">Revenue tracking and resident payment compliance</p>
        </header>

        <div class="financial-stats">
            <div class="fin-card">
                <div class="fin-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><i class="fas fa-users"></i></div>
                <div><small>RESIDENTS</small><h3><?php echo $total_students_with_rooms; ?></h3></div>
            </div>
            <div class="fin-card">
                <div class="fin-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fas fa-calendar-check"></i></div>
                <div><small>PAID (<?php echo date('M'); ?>)</small><h3><?php echo $paid_current_month; ?></h3></div>
            </div>
            <div class="fin-card">
                <div class="fin-icon" style="background: rgba(239, 68, 68, 0.1); color: #f87171;"><i class="fas fa-clock"></i></div>
                <div><small>PENDING</small><h3><?php echo $pending_current_month; ?></h3></div>
            </div>
            <div class="fin-card">
                <div class="fin-icon" style="background: rgba(251, 191, 36, 0.1); color: var(--primary);"><i class="fas fa-vault"></i></div>
                <div><small>MONTHLY REV</small><h3>₹<?php echo number_format($total_collected_month/1000, 1); ?>K</h3></div>
            </div>
        </div>

        <section class="glass-panel">
            <div class="panel-head">
                <h3 style="font-weight: 800;"><i class="fas fa-user-clock" style="color: var(--primary); margin-right: 10px;"></i> Resident Rent Matrix</h3>
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700;">SORTED BY OLDEST DUES</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student Detail</th>
                            <th>Room Type</th>
                            <th>Monthly</th>
                            <th>Last Paid</th>
                            <th>Current Status</th>
                            <th>Total Vol.</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = mysqli_fetch_assoc($students_result)): 
                            $last_paid = $student['last_paid_month'];
                            if (!$last_paid) { $st_cls = 'badge-never'; $st_txt = 'NEVER PAID'; }
                            elseif ($last_paid < $current_month) { $st_cls = 'badge-overdue'; $st_txt = 'OVERDUE'; }
                            elseif ($last_paid == $current_month) { $st_cls = 'badge-success'; $st_txt = 'UP TO DATE'; }
                            else { $st_cls = 'badge-advance'; $st_txt = 'ADVANCE'; }
                        ?>
                        <tr>
                            <td>
                                <strong style="display: block; color: white;"><?php echo htmlspecialchars($student['name']); ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><?php echo $student['email']; ?></span>
                            </td>
                            <td style="color: #cbd5e1;"><?php echo $student['room_type']; ?></td>
                            <td style="font-weight: 700;">₹<?php echo number_format($student['monthly_rent']); ?></td>
                            <td><?php echo $last_paid ? date('M Y', strtotime($last_paid)) : '—'; ?></td>
                            <td><span class="badge <?php echo $st_cls; ?>"><?php echo $st_txt; ?></span></td>
                            <td>
                                <strong style="color: #10b981;">₹<?php echo number_format($student['total_paid']); ?></strong>
                                <small style="display: block; color: var(--text-dim); font-size: 0.7rem;"><?php echo $student['total_payments']; ?> txns</small>
                            </td>
                            <td>
                                <a href="tel:<?php echo $student['phone']; ?>" class="btn-call" title="Call Resident"><i class="fas fa-phone-alt"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="glass-panel" style="margin-bottom: 5rem;">
            <div class="panel-head">
                <h3 style="font-weight: 800;"><i class="fas fa-file-invoice-dollar" style="color: var(--primary); margin-right: 10px;"></i> Master Ledger</h3>
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700;">RECENT 50 INVOICES</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Receipt ID</th>
                            <th>Student</th>
                            <th>Bill Month</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($payments_result)): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--primary); font-weight: 700;"><?php echo $p['receipt_number']; ?></td>
                            <td><?php echo htmlspecialchars($p['student_name']); ?></td>
                            <td><strong><?php echo date('M Y', strtotime($p['payment_month'])); ?></strong></td>
                            <td style="color: #10b981; font-weight: 700;">₹<?php echo number_format($p['total_amount']); ?></td>
                            <td>
                                <?php 
                                    $m = $p['payment_method'];
                                    $icon = $m == 'Card' ? 'credit-card' : ($m == 'UPI' ? 'mobile-screen' : 'wallet');
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>" style="margin-right: 5px; opacity: 0.5;"></i> <?php echo $m; ?>
                            </td>
                            <td style="font-family: monospace; font-size: 0.75rem; color: var(--text-dim);"><?php echo $p['transaction_id']; ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-dim);">
                                <?php echo date('d M, Y', strtotime($p['payment_date'])); ?><br>
                                <?php echo date('h:i A', strtotime($p['payment_date'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer style="text-align: center; padding-bottom: 3rem; color: var(--text-dim); font-size: 0.8rem;">
        &copy; 2026 GUARDIAN HOSTEL Financial Services. Secure Ledger.
    </footer>

</body>
</html>