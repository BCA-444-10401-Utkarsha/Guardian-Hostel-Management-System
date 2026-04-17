<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Logic: Data Summary
$total_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages"))['count'];

// Logic: Fetch Messages
$messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox | GUARDIAN HOSTEL Admin</title>
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
        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: radial-gradient(at 100% 0%, #1e1b4b 0%, #020617 50%); }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        /* Stats Section */
        .stats-wrapper { max-width: 400px; margin: 0 auto 3rem; }
        .stat-card {
            background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border);
            border-radius: 24px; padding: 1.5rem 2rem; display: flex; align-items: center; gap: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .stat-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; background: rgba(251, 191, 36, 0.1); color: var(--primary); }

        /* Inbox Panel */
        .glass-panel { background: var(--glass); border-radius: 32px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 5rem; }
        .panel-head { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.01); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 2rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: top; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .sender-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 35px; height: 35px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        
        .msg-body { color: #cbd5e1; line-height: 1.6; max-width: 400px; font-size: 0.85rem; }
        .time-tag { white-space: nowrap; text-align: right; color: var(--text-dim); font-size: 0.8rem; }

        .btn-reply { color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; }
        .btn-reply:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            th:nth-child(3), td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Visitor Inbox<span>.</span></h1>
            <p style="color: var(--text-dim);">Review and manage inquiries from the public website</p>
        </header>

        <div class="stats-wrapper">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                <div>
                    <small style="color: var(--text-dim); font-weight: 700; text-transform: uppercase;">Total Received</small>
                    <h3 style="font-size: 1.8rem;"><?php echo $total_messages; ?></h3>
                </div>
            </div>
        </div>

        <section class="glass-panel">
            <div class="panel-head">
                <h3 style="font-weight: 800;">Recent Conversations</h3>
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700;">LIVE FEED</span>
            </div>

            <div style="overflow-x: auto;">
                <?php if (mysqli_num_rows($messages_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Contact Detail</th>
                            <th>Inquiry Message</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($m = mysqli_fetch_assoc($messages_result)): ?>
                        <tr>
                            <td>
                                <div class="sender-info">
                                    <div class="avatar"><?php echo strtoupper(substr($m['name'], 0, 1)); ?></div>
                                    <strong style="color: white;"><?php echo htmlspecialchars($m['name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo $m['email']; ?>" class="btn-reply">
                                    <i class="fas fa-envelope-open"></i> <?php echo htmlspecialchars($m['email']); ?>
                                </a>
                                <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 4px;">
                                    <i class="fas fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($m['phone']); ?>
                                </p>
                            </td>
                            <td>
                                <div class="msg-body"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                            </td>
                            <td class="time-tag">
                                <span style="display: block; color: white;"><?php echo date('d M, Y', strtotime($m['created_at'])); ?></span>
                                <span><?php echo date('h:i A', strtotime($m['created_at'])); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 5rem 0; color: var(--text-dim);">
                        <i class="fas fa-comment-slash" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Your inbox is currently empty.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer style="text-align: center; padding-bottom: 3rem; color: var(--text-dim); font-size: 0.8rem;">
        &copy; 2026 GUARDIAN HOSTEL Admin Terminal. All Communication Logged.
    </footer>

</body>
</html>