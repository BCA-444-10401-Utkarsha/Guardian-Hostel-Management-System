<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];

$password_message = '';
$password_error = '';

// Logic: Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_admin_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $query = "SELECT password FROM admin WHERE id = $admin_id";
        $result = mysqli_query($conn, $query);
        $admin = mysqli_fetch_assoc($result);

        if (password_verify($current_password, $admin['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE admin SET password = '$hashed_password' WHERE id = $admin_id";
                    if (mysqli_query($conn, $update_query)) {
                        $password_message = 'Password changed successfully!';
                    } else { $password_error = 'Failed to update password.'; }
                } else { $password_error = 'New password must be 6+ characters.'; }
            } else { $password_error = 'Passwords do not match.'; }
        } else { $password_error = 'Current password is incorrect.'; }
    } else { $password_error = 'Please fill all password fields.'; }
}

// Logic: Data Summary
$students_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM students"));
$rooms_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rooms"));
$available_rooms = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rooms WHERE status = 'Available'"));
$occupied_rooms = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rooms WHERE status = 'Full'"));
$pending_requests = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM room_requests WHERE status = 'Pending'"));
$approved_requests = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM room_requests WHERE status = 'Approved'"));
$pending_complaints = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM complaints WHERE status = 'Pending'"));
$total_complaints = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM complaints"));
$contact_messages = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM contact_messages"));
$today_day = date('l');
$today_menu_exists = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM mess_menu WHERE day_of_week = '$today_day'")) > 0;

$recent_requests_query = "SELECT rr.*, s.name as student_name FROM room_requests rr JOIN students s ON rr.student_id = s.id ORDER BY rr.request_date DESC LIMIT 5";
$recent_requests = mysqli_query($conn, $recent_requests_query);

$recent_complaints_query = "SELECT c.*, s.name as student_name FROM complaints c JOIN students s ON c.student_id = s.id ORDER BY c.created_at DESC LIMIT 5";
$recent_complaints = mysqli_query($conn, $recent_complaints_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center | GUARDIAN HOSTEL Admin</title>
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

        body { background: #020617; color: white; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #020617 50%); }

        .dashboard-header { padding: 3rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; }
        .header-text h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .header-text p { color: var(--text-dim); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .stat-card {
            background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border);
            border-radius: 24px; padding: 1.5rem; display: flex; align-items: center; gap: 1.2rem; transition: 0.3s;
        }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-5px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        /* Main Grid */
        .workspace-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem; }
        .glass-panel { background: var(--glass); border: 1px solid var(--border); border-radius: 28px; padding: 2.2rem; }
        .panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .panel-head h3 { font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }

        /* Command Buttons */
        .cmd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .cmd-btn {
            background: rgba(255,255,255,0.02); border: 1px solid var(--border);
            padding: 1.5rem; border-radius: 20px; color: white; text-decoration: none;
            display: flex; flex-direction: column; align-items: center; gap: 10px; transition: 0.3s;
            font-size: 0.85rem; font-weight: 700; text-align: center;
        }
        .cmd-btn:hover { background: var(--primary); color: #000; border-color: var(--primary); transform: scale(1.05); }
        .cmd-btn.alert { border-color: #ef4444; color: #fca5a5; background: rgba(239, 68, 68, 0.05); }
        .cmd-btn.alert:hover { background: #ef4444; color: white; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 1.2rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .badge-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        /* Modal Styles */
        .modal { 
            display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.8);
            backdrop-filter: blur(10px); z-index: 3000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #1e293b; border: 1px solid var(--border); width: 100%; max-width: 450px;
            border-radius: 28px; padding: 2.5rem; animation: slideIn 0.4s ease;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) { .workspace-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="dashboard-header">
            <div class="header-text">
                <h1>Command Center<span>.</span></h1>
                <p>Welcome back, Administrator • <?php echo date('l, jS F'); ?></p>
            </div>
            <button class="btn-primary" onclick="toggleModal('adminPasswordModal', true)" style="background: var(--primary); color: #000; padding: 12px 24px; border-radius: 14px; border: none; font-weight: 800; cursor: pointer;">
                <i class="fas fa-shield-halved"></i> SECURITY SETTINGS
            </button>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><i class="fas fa-users"></i></div>
                <div><small>STUDENTS</small><h3><?php echo $students_count; ?></h3></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fas fa-door-open"></i></div>
                <div><small>AVAILABLE</small><h3><?php echo $available_rooms; ?> / <?php echo $rooms_count; ?></h3></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24;"><i class="fas fa-clock"></i></div>
                <div><small>PENDING</small><h3><?php echo $pending_requests; ?></h3></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;"><i class="fas fa-envelope"></i></div>
                <div><small>MESSAGES</small><h3><?php echo $contact_messages; ?></h3></div>
            </div>
        </div>

        <div class="workspace-grid">
            <section class="glass-panel">
                <div class="panel-head"><h3><i class="fas fa-bolt" style="color: var(--primary);"></i> Management Hub</h3></div>
                <div class="cmd-grid">
                    <a href="room_requests.php" class="cmd-btn <?php echo $pending_requests > 0 ? 'alert' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Requests (<?php echo $pending_requests; ?>)</span>
                    </a>
                    <a href="complaints.php" class="cmd-btn <?php echo $pending_complaints > 0 ? 'alert' : ''; ?>">
                        <i class="fas fa-headset"></i>
                        <span>Tickets (<?php echo $pending_complaints; ?>)</span>
                    </a>
                    <a href="mess.php" class="cmd-btn <?php echo !$today_menu_exists ? 'alert' : ''; ?>">
                        <i class="fas fa-utensils"></i>
                        <span>Mess Menu</span>
                    </a>
                    <a href="rooms.php" class="cmd-btn">
                        <i class="fas fa-bed"></i>
                        <span>Inventory</span>
                    </a>
                    <a href="students.php" class="cmd-btn">
                        <i class="fas fa-users-viewfinder"></i>
                        <span>Directory</span>
                    </a>
                    <a href="announcements.php" class="cmd-btn">
                        <i class="fas fa-bullhorn"></i>
                        <span>Broadcast</span>
                    </a>
                </div>
            </section>

            <section class="glass-panel">
                <div class="panel-head">
                    <h3><i class="fas fa-list" style="color: var(--primary);"></i> Recent Room Requests</h3>
                    <a href="room_requests.php" style="color: var(--primary); text-decoration: none; font-size: 0.75rem; font-weight: 800;">VIEW ALL</a>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr><th>Student</th><th>Type</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php while($req = mysqli_fetch_assoc($recent_requests)): ?>
                            <tr>
                                <td style="font-weight: 700; color: white;"><?php echo htmlspecialchars($req['student_name']); ?></td>
                                <td style="color: var(--text-dim);"><?php echo $req['room_type']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($req['status']); ?>"><?php echo strtoupper($req['status']); ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="glass-panel">
            <div class="panel-head">
                <h3><i class="fas fa-comment-dots" style="color: #ef4444;"></i> Urgent Complaints</h3>
                <a href="complaints.php" style="color: var(--primary); text-decoration: none; font-size: 0.75rem; font-weight: 800;">HANDLE TICKETS</a>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr><th>Sender</th><th>Issue Subject</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while($c = mysqli_fetch_assoc($recent_complaints)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['student_name']); ?></strong></td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars(substr($c['subject'], 0, 50)); ?>...</td>
                            <td style="color: var(--text-dim);"><?php echo date('M j', strtotime($c['created_at'])); ?></td>
                            <td><a href="complaints.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">RESOLVE</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="adminPasswordModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                <h3 style="font-weight: 800;"><i class="fas fa-lock" style="color: var(--primary);"></i> Change Admin Access</h3>
                <span style="cursor: pointer; color: var(--text-dim);" onclick="toggleModal('adminPasswordModal', false)">&times;</span>
            </div>
            <form method="POST">
                <input type="password" name="current_password" placeholder="Verify Current Password" required>
                <input type="password" name="new_password" placeholder="New Secret Key (6+ chars)" required minlength="6">
                <input type="password" name="confirm_password" placeholder="Confirm New Secret Key" required>
                <button type="submit" name="change_admin_password" style="width: 100%; background: var(--primary); color: #000; padding: 15px; border: none; border-radius: 12px; font-weight: 800; cursor: pointer;">UPDATE SECURITY KEY</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id, show) {
            document.getElementById(id).style.display = show ? 'flex' : 'none';
        }
        
        window.onclick = function(e) {
            if (e.target.className === 'modal') {
                e.target.style.display = 'none';
            }
        }

        <?php if ($password_message || $password_error): ?>
            toggleModal('adminPasswordModal', true);
        <?php endif; ?>
    </script>
</body>
</html>