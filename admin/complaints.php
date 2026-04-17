<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';

// Logic: Status Update
if (isset($_GET['update']) && isset($_GET['status'])) {
    $complaint_id = (int)$_GET['update'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $update = "UPDATE complaints SET status = '$status' WHERE id = $complaint_id";
    if (mysqli_query($conn, $update)) {
        $success = 'Complaint status updated successfully';
    }
}

// Logic: Statistics
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints"))['count'];
$pending_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'"))['count'];
$in_progress_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status = 'In Progress'"))['count'];
$resolved_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status = 'Resolved'"))['count'];
$total_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages"))['count'];

// Logic: Queries
$complaints_query = "SELECT c.*, s.name as student_name, s.email, s.phone FROM complaints c JOIN students s ON c.student_id = s.id ORDER BY c.created_at DESC";
$complaints_result = mysqli_query($conn, $complaints_query);

$messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolution Center | GUARDIAN HOSTEL Admin</title>
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
            background: radial-gradient(at 0% 100%, #1e1b4b 0%, #020617 50%);
        }

        .page-header {
            text-align: center;
            padding: 4rem 0 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* Modern Pill Tab Switcher */
        .tab-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .pill-tabs {
            background: var(--glass);
            padding: 6px;
            border-radius: 20px;
            border: 1px solid var(--border);
            display: inline-flex;
            gap: 5px;
        }

        .tab-btn {
            padding: 12px 25px;
            border-radius: 15px;
            border: none;
            background: none;
            color: var(--text-dim);
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .tab-btn.active {
            background: var(--primary);
            color: #000;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .tab-btn .count-badge {
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            position: absolute;
            top: -5px;
            right: -5px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        /* High Density Table */
        .glass-panel {
            background: var(--glass);
            border-radius: 28px;
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 5rem;
        }

        .panel-head {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.01);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1.2rem 2rem;
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 1.2rem 2rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            vertical-align: top;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Status Colors */
        .status-tag {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .tag-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        .tag-progress {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }

        .tag-resolved {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .admin-select {
            background: #0f172a;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Resolution Center<span>.</span></h1>
            <p style="color: var(--text-dim);">Monitor student feedback and manage external inquiries</p>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;"><i class="fas fa-check-double"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="tab-wrapper">
            <div class="pill-tabs">
                <button class="tab-btn active" onclick="switchTab(event, 'complaints')">
                    <i class="fas fa-hand-holding-heart"></i> Student Complaints
                    <?php if ($pending_complaints > 0): ?><span class="count-badge"><?php echo $pending_complaints; ?></span><?php endif; ?>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'messages')">
                    <i class="fas fa-paper-plane"></i> Contact Inquiries
                </button>
            </div>
        </div>

        <div id="complaints" class="tab-content active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;"><i class="fas fa-list-check"></i></div>
                    <div><small>TOTAL</small>
                        <h3><?php echo $total_complaints; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24;"><i class="fas fa-clock"></i></div>
                    <div><small>PENDING</small>
                        <h3><?php echo $pending_complaints; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa;"><i class="fas fa-spinner"></i></div>
                    <div><small>ACTIVE</small>
                        <h3><?php echo $in_progress_complaints; ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #34d399;"><i class="fas fa-check-circle"></i></div>
                    <div><small>RESOLVED</small>
                        <h3><?php echo $resolved_complaints; ?></h3>
                    </div>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-head">
                    <h3>Live Complaint Feed</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject / Description</th>
                                <th>Current Status</th>
                                <th>Received On</th>
                                <th>Resolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($c = mysqli_fetch_assoc($complaints_result)): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar"><?php echo substr($c['student_name'], 0, 1); ?></div>
                                            <div style="font-size: 0.8rem;">
                                                <strong style="display: block; color: white;"><?php echo htmlspecialchars($c['student_name']); ?></strong>
                                                <span style="color: var(--text-dim);"><?php echo $c['phone']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: white; font-size: 0.85rem;"><?php echo htmlspecialchars($c['subject']); ?></strong>
                                        <p style="color: var(--text-dim); font-size: 0.8rem; margin-top: 5px;"><?php echo htmlspecialchars(substr($c['description'], 0, 70)); ?>...</p>
                                    </td>
                                    <td>
                                        <?php
                                        $cls = $c['status'] == 'Pending' ? 'tag-pending' : ($c['status'] == 'In Progress' ? 'tag-progress' : 'tag-resolved');
                                        $icon = $c['status'] == 'Pending' ? 'clock' : ($c['status'] == 'In Progress' ? 'spinner' : 'check');
                                        ?>
                                        <span class="status-tag <?php echo $cls; ?>"><i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $c['status']; ?></span>
                                    </td>
                                    <td style="color: var(--text-dim); font-size: 0.8rem;"><?php echo date('d M, Y', strtotime($c['created_at'])); ?></td>
                                    <td>
                                        <select class="admin-select" onchange="window.location.href='complaints.php?update=<?php echo $c['id']; ?>&status='+this.value">
                                            <option value="">Actions</option>
                                            <option value="Pending" <?php echo $c['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="In Progress" <?php echo $c['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="Resolved" <?php echo $c['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="messages" class="tab-content">
            <div style="max-width: 300px; margin: 0 auto 3rem;">
                <div class="stat-card" style="justify-content: center;">
                    <div class="stat-icon" style="background: rgba(251, 191, 36, 0.1); color: var(--primary);"><i class="fas fa-envelope-open-text"></i></div>
                    <div><small>CONTACT MESSAGES</small>
                        <h3><?php echo $total_messages; ?></h3>
                    </div>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-head">
                    <h3>External Inquiries</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Sender Details</th>
                                <th>Message</th>
                                <th>Received On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($m = mysqli_fetch_assoc($messages_result)): ?>
                                <tr>
                                    <td style="width: 250px;">
                                        <strong style="display: block; color: white;"><?php echo htmlspecialchars($m['name']); ?></strong>
                                        <span style="color: var(--primary); font-size: 0.8rem;"><?php echo $m['email']; ?></span><br>
                                        <span style="color: var(--text-dim); font-size: 0.75rem;"><?php echo $m['phone']; ?></span>
                                    </td>
                                    <td>
                                        <p style="color: #cbd5e1; line-height: 1.6; font-size: 0.85rem;"><?php echo htmlspecialchars($m['message']); ?></p>
                                    </td>
                                    <td style="color: var(--text-dim); font-size: 0.8rem; text-align: right; white-space: nowrap;">
                                        <?php echo date('d M Y', strtotime($m['created_at'])); ?><br>
                                        <?php echo date('h:i A', strtotime($m['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function switchTab(evt, tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</body>

</html>