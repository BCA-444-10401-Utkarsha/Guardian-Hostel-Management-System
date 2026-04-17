<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Logic: Handle Announcement Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_announcement'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $target_student_id = ($type == 'personal' && !empty($_POST['target_student_id'])) ? (int)$_POST['target_student_id'] : NULL;
    $admin_id = $_SESSION['admin_id'];

    if (!empty($title) && !empty($message)) {
        if ($type == 'personal' && !$target_student_id) {
            $error = 'Please select a student for personal announcement.';
        } else {
            $query = "INSERT INTO announcements (title, message, type, target_student_id, created_by) 
                      VALUES ('$title', '$message', '$type', " . ($target_student_id ? $target_student_id : 'NULL') . ", $admin_id)";
            if (mysqli_query($conn, $query)) {
                $success = 'Announcement broadcasted successfully!';
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    } else {
        $error = 'Title and Message are required.';
    }
}

// Logic: Data Fetching
$students_query = "SELECT id, name, email FROM students ORDER BY name";
$students_result = mysqli_query($conn, $students_query);

$pending_payment_students = "SELECT s.id, s.name, s.email, rr.total_amount 
                             FROM students s 
                             JOIN room_requests rr ON s.id = rr.student_id 
                             WHERE rr.status = 'Approved' AND rr.payment_status = 'Pending'
                             ORDER BY s.name";
$pending_students_result = mysqli_query($conn, $pending_payment_students);

$announcements_query = "SELECT a.*, s.name as student_name, s.email as student_email 
                        FROM announcements a 
                        LEFT JOIN students s ON a.target_student_id = s.id 
                        ORDER BY a.created_at DESC LIMIT 20";
$announcements_result = mysqli_query($conn, $announcements_query);

$total_announcements = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements"))['count'];
$general_announcements = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE type = 'general'"))['count'];
$personal_announcements = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE type = 'personal'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Center | GUARDIAN HOSTEL Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        :root {
            --admin-gold: #fbbf24;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: #020617;
            color: white;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: radial-gradient(at 0% 0%, #1e1b4b 0%, #020617 50%);
        }

        .page-header {
            padding: 4rem 0 2rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-widget {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Main Workspace */
        .broadcast-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
            align-items: start;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 2.5rem;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        input,
        select,
        textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            color: white;
            margin-bottom: 1.2rem;
            outline: none;
            transition: 0.3s;
        }

        input:focus,
        textarea:focus {
            border-color: var(--admin-gold);
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.1);
        }

        .btn-broadcast {
            width: 100%;
            background: var(--admin-gold);
            color: #000;
            border: none;
            padding: 18px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
        }

        .btn-broadcast:hover {
            transform: translateY(-3px);
            background: #fff;
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        /* Reminder List */
        .reminder-list {
            max-height: 450px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .reminder-item {
            background: rgba(251, 191, 36, 0.03);
            border: 1px solid rgba(251, 191, 36, 0.1);
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            transition: 0.3s;
        }

        .reminder-item:hover {
            border-color: var(--admin-gold);
            background: rgba(251, 191, 36, 0.08);
        }

        /* History Table */
        .history-card {
            background: var(--glass);
            border-radius: 28px;
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1.2rem 2rem;
            background: rgba(255, 255, 255, 0.02);
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
        }

        td {
            padding: 1.2rem 2rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            vertical-align: top;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .badge-general {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
        }

        .badge-personal {
            background: rgba(244, 114, 182, 0.1);
            color: #f472b6;
        }

        @media (max-width: 1000px) {
            .broadcast-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Broadcast Command Center<span>.</span></h1>
            <p style="color: #94a3b8;">Manage global announcements and targeted student communications</p>
        </header>

        <div class="stats-grid">
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;"><i class="fas fa-tower-broadcast"></i></div>
                <div><small style="color: #94a3b8; font-weight: 700;">TOTAL POSTS</small>
                    <h3><?php echo $total_announcements; ?></h3>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><i class="fas fa-users"></i></div>
                <div><small style="color: #94a3b8; font-weight: 700;">GENERAL</small>
                    <h3><?php echo $general_announcements; ?></h3>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(244, 114, 182, 0.1); color: #f472b6;"><i class="fas fa-user-tag"></i></div>
                <div><small style="color: #94a3b8; font-weight: 700;">TARGETED</small>
                    <h3><?php echo $personal_announcements; ?></h3>
                </div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success" style="margin-bottom: 2rem;"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger" style="margin-bottom: 2rem;"><?php echo $error; ?></div><?php endif; ?>

        <div class="broadcast-grid">
            <section class="glass-card">
                <h3 style="margin-bottom: 2rem; font-weight: 800;"><i class="fas fa-pen-nib" style="color: var(--admin-gold); margin-right: 10px;"></i> Compose Announcement</h3>

                <form method="POST" id="announcementForm">
                    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Distribution Type</label>
                            <select name="type" id="announcementType" required onchange="toggleStudentSelect()">
                                <option value="general">Global (All Students)</option>
                                <option value="personal">Personal (Targeted)</option>
                            </select>
                        </div>
                        <div class="form-group" id="studentSelectDiv" style="display: none;">
                            <label>Target Student</label>
                            <select name="target_student_id" id="targetStudent">
                                <option value="">Select recipient...</option>
                                <?php mysqli_data_seek($students_result, 0);
                                while ($s = mysqli_fetch_assoc($students_result)): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?> (<?php echo $s['email']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Message Subject</label>
                        <input type="text" name="title" placeholder="e.g., Scheduled Power Outage" required>
                    </div>

                    <div class="form-group">
                        <label>Message Content</label>
                        <textarea name="message" rows="6" placeholder="Provide clear and concise details..." required></textarea>
                    </div>

                    <button type="submit" name="send_announcement" class="btn-broadcast">
                        Initialize Broadcast <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </section>

            <section class="glass-card" style="padding: 2rem;">
                <h3 style="margin-bottom: 1.5rem; font-weight: 800;"><i class="fas fa-clock-rotate-left" style="color: var(--admin-gold); margin-right: 10px;"></i> Unpaid Balances</h3>

                <div class="reminder-list">
                    <?php if (mysqli_num_rows($pending_students_result) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($pending_students_result)): ?>
                            <div class="reminder-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <span style="color: var(--admin-gold); font-weight: 800;">₹<?php echo number_format($p['total_amount']); ?></span>
                                </div>
                                <button onclick="sendPaymentReminder(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['total_amount']; ?>)"
                                    class="btn-text" style="color: var(--admin-gold); background: none; border: 1px solid rgba(251,191,36,0.3); padding: 5px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; width: 100%;">
                                    <i class="fas fa-bell"></i> Send Direct Reminder
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.85rem;">All payments are currently up to date.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <section class="history-card">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-weight: 800;">Transmission History</h3>
                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">SHOWING LAST 20</span>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Subject / Message</th>
                            <th>Scope</th>
                            <th>Target Info</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($a = mysqli_fetch_assoc($announcements_result)): ?>
                            <tr>
                                <td>
                                    <strong style="display: block; color: white;"><?php echo htmlspecialchars($a['title']); ?></strong>
                                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 5px;"><?php echo htmlspecialchars(substr($a['message'], 0, 80)); ?>...</p>
                                </td>
                                <td>
                                    <span class="badge <?php echo $a['type'] == 'general' ? 'badge-general' : 'badge-personal'; ?>">
                                        <?php echo strtoupper($a['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: #cbd5e1;">
                                        <?php echo $a['type'] == 'general' ? 'All Students' : htmlspecialchars($a['student_name']); ?>
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;">
                                    <?php echo date('d M, Y', strtotime($a['created_at'])); ?><br>
                                    <?php echo date('h:i A', strtotime($a['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        function toggleStudentSelect() {
            const type = document.getElementById('announcementType').value;
            document.getElementById('studentSelectDiv').style.display = (type === 'personal') ? 'block' : 'none';
        }

        function sendPaymentReminder(id, name, amount) {
            document.getElementById('announcementType').value = 'personal';
            toggleStudentSelect();
            document.getElementById('targetStudent').value = id;
            document.querySelector('input[name="title"]').value = 'Payment Notice: Room Booking Balance';
            document.querySelector('textarea[name="message"]').value = `Dear ${name},\n\nOur records indicate a pending balance of ₹${amount.toLocaleString()} for your room allocation. Please finalize your payment through the portal to avoid booking cancellation.\n\nRegards,\nGUARDIAN HOSTEL Hostel Management`;
            document.getElementById('announcementForm').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</body>

</html>