<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Handle Mark as Read Logic
if (isset($_GET['mark_read'])) {
    $announcement_id = (int)$_GET['mark_read'];
    mysqli_query($conn, "UPDATE announcements SET is_read = 1 WHERE id = $announcement_id AND (type = 'general' OR target_student_id = $student_id)");
    header('Location: announcements.php');
    exit();
}

// Data Queries
$personal_query = "SELECT * FROM announcements 
                   WHERE type = 'personal' AND target_student_id = $student_id 
                   ORDER BY created_at DESC";
$personal_result = mysqli_query($conn, $personal_query);

$general_query = "SELECT * FROM announcements 
                  WHERE type = 'general' 
                  ORDER BY created_at DESC";
$general_result = mysqli_query($conn, $general_query);

$unread_personal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE type = 'personal' AND target_student_id = $student_id AND is_read = 0"))['count'];
$unread_general = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE type = 'general' AND is_read = 0"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | GUARDIAN HOSTEL Student Portal</title>
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

        body { background: #0f172a; color: white; min-height: 100vh; }
        
        .page-header { text-align: center; padding: 3rem 0; }
        .page-header h2 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }
        .page-header p { color: var(--text-dim); margin-top: 0.5rem; }

        /* Modern Tab Switcher */
        .tab-container {
            display: flex; justify-content: center; margin-bottom: 3rem;
        }
        .tab-switcher {
            background: rgba(255,255,255,0.05); padding: 6px; border-radius: 20px;
            display: inline-flex; border: 1px solid var(--glass-border);
        }
        .tab-btn {
            padding: 12px 24px; border-radius: 16px; border: none; background: none;
            color: var(--text-dim); font-weight: 700; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 10px; position: relative;
        }
        .tab-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }
        .tab-btn .unread-dot {
            width: 8px; height: 8px; background: #ef4444; border-radius: 50%;
            position: absolute; top: 10px; right: 10px; box-shadow: 0 0 10px #ef4444;
        }

        /* Announcement Cards */
        .announcement-list { max-width: 800px; margin: 0 auto; display: none; }
        .announcement-list.active { display: block; animation: fadeInUp 0.5s ease; }

        .a-card {
            background: var(--glass-bg); backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border); border-radius: 24px;
            padding: 2rem; margin-bottom: 1.5rem; transition: 0.3s;
        }
        .a-card:hover { transform: translateY(-5px); border-color: var(--primary); background: rgba(255,255,255,0.05); }
        .a-card.is-unread { border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.02); }

        .card-meta { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .card-title { font-size: 1.25rem; font-weight: 700; color: white; margin-bottom: 4px; }
        .card-date { font-size: 0.85rem; color: var(--text-dim); display: flex; align-items: center; gap: 6px; }
        
        .card-msg { line-height: 1.7; color: #cbd5e1; font-size: 1rem; white-space: pre-wrap; }

        .mark-btn {
            background: rgba(34, 197, 94, 0.1); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2);
            padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 0.8rem;
            font-weight: 700; transition: 0.3s; white-space: nowrap;
        }
        .mark-btn:hover { background: #22c55e; color: white; }

        .empty-state { text-align: center; padding: 5rem 0; opacity: 0.5; }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h2>Announcements<span>.</span></h2>
            <p>Stay informed with the latest updates from GUARDIAN HOSTEL Management</p>
        </header>

        <div class="tab-container">
            <div class="tab-switcher">
                <button class="tab-btn active" onclick="switchTab(event, 'personal')">
                    <i class="fas fa-user-shield"></i> Personal
                    <?php if ($unread_personal > 0): ?><span class="unread-dot"></span><?php endif; ?>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'general')">
                    <i class="fas fa-globe"></i> General
                    <?php if ($unread_general > 0): ?><span class="unread-dot"></span><?php endif; ?>
                </button>
            </div>
        </div>

        <div id="personal" class="announcement-list active">
            <?php if (mysqli_num_rows($personal_result) > 0): ?>
                <?php while ($announcement = mysqli_fetch_assoc($personal_result)): ?>
                    <div class="a-card <?php echo $announcement['is_read'] == 0 ? 'is-unread' : ''; ?>">
                        <div class="card-meta">
                            <div>
                                <h3 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <div class="card-date">
                                    <i class="far fa-clock"></i> <?php echo date('M j, Y • h:i A', strtotime($announcement['created_at'])); ?>
                                </div>
                            </div>
                            <?php if ($announcement['is_read'] == 0): ?>
                                <a href="announcements.php?mark_read=<?php echo $announcement['id']; ?>" class="mark-btn">
                                    <i class="fas fa-check"></i> Mark as Read
                                </a>
                            <?php endif; ?>
                        </div>
                        <p class="card-msg"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-envelope-open"></i>
                    <h3>No Personal Messages</h3>
                </div>
            <?php endif; ?>
        </div>

        <div id="general" class="announcement-list">
            <?php if (mysqli_num_rows($general_result) > 0): ?>
                <?php while ($announcement = mysqli_fetch_assoc($general_result)): ?>
                    <div class="a-card">
                        <div class="card-meta">
                            <div>
                                <h3 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <div class="card-date">
                                    <i class="far fa-calendar-alt"></i> <?php echo date('M j, Y • h:i A', strtotime($announcement['created_at'])); ?>
                                </div>
                            </div>
                            <span style="background: rgba(99, 102, 241, 0.1); color: #818cf8; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">PUBLIC</span>
                        </div>
                        <p class="card-msg"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bullhorn"></i>
                    <h3>No Public Announcements</h3>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function switchTab(evt, tabName) {
            // Hide all lists
            document.querySelectorAll('.announcement-list').forEach(list => {
                list.classList.remove('active');
            });
            // Remove active from buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Show current
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>