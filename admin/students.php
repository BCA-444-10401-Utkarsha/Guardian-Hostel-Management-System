<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Logic: Demographic Summary
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
$allocated_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as count FROM room_requests WHERE status = 'Approved'"))['count'];
$male_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE gender = 'Male'"))['count'];
$female_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE gender = 'Female'"))['count'];

// Logic: Master Student Query
$students_query = "SELECT s.*, 
                   (SELECT COUNT(*) FROM room_requests WHERE student_id = s.id AND status = 'Approved') as has_room,
                   (SELECT room_number FROM rooms r JOIN room_requests rr ON r.id = rr.room_id WHERE rr.student_id = s.id AND rr.status = 'Approved' LIMIT 1) as room_number
                   FROM students s 
                   ORDER BY s.created_at DESC";
$students_result = mysqli_query($conn, $students_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Directory | GUARDIAN HOSTEL Admin</title>
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

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }

        /* Stats Strip */
        .stats-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .stat-widget { background: var(--glass); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.2rem; }
        .stat-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

        /* Search Terminal */
        .search-panel { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border); border-radius: 20px; padding: 1rem 2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 15px; }
        .search-panel i { color: var(--primary); }
        .search-panel input { flex: 1; background: none; border: none; color: white; font-size: 1rem; outline: none; padding: 10px 0; }
        .search-panel input::placeholder { color: #475569; }

        /* Directory Panel */
        .glass-panel { background: var(--glass); border-radius: 32px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 5rem; }
        .panel-head { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.01); display: flex; justify-content: space-between; align-items: center; }
        
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 1.5rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        /* Student Components */
        .student-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; border: 2px solid rgba(251, 191, 36, 0.2); }
        
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
        .badge-allocated { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-waiting { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        
        .gender-male { color: #38bdf8; }
        .gender-female { color: #f472b6; }

        @media (max-width: 1000px) {
            th:nth-child(3), td:nth-child(3), th:nth-child(7), td:nth-child(7) { display: none; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Personnel Registry<span>.</span></h1>
            <p style="color: var(--text-dim);">Centralized resident records and demographic distribution</p>
        </header>

        <div class="stats-strip">
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(251, 191, 36, 0.1); color: var(--primary);"><i class="fas fa-users"></i></div>
                <div><small style="color: var(--text-dim); font-weight: 700;">TOTAL</small><h3><?php echo $total_students; ?></h3></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fas fa-house-circle-check"></i></div>
                <div><small style="color: var(--text-dim); font-weight: 700;">ALLOCATED</small><h3><?php echo $allocated_students; ?></h3></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><i class="fas fa-mars"></i></div>
                <div><small style="color: var(--text-dim); font-weight: 700;">MALE</small><h3><?php echo $male_students; ?></h3></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(244, 114, 182, 0.1); color: #f472b6;"><i class="fas fa-venus"></i></div>
                <div><small style="color: var(--text-dim); font-weight: 700;">FEMALE</small><h3><?php echo $female_students; ?></h3></div>
            </div>
        </div>

        <div class="search-panel">
            <i class="fas fa-terminal"></i>
            <input type="text" id="directorySearch" placeholder="Query name, contact, email or room number..." onkeyup="filterDirectory()">
        </div>

        <section class="glass-panel">
            <div class="panel-head">
                <h3 style="font-weight: 800;"><i class="fas fa-id-card" style="color: var(--primary); margin-right: 10px;"></i> Resident Master List</h3>
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700;">RECORDS SECURED</span>
            </div>

            <div class="table-wrapper">
                <table id="directoryTable">
                    <thead>
                        <tr>
                            <th>Ref ID</th>
                            <th>Student Personnel</th>
                            <th>Email Address</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Residency</th>
                            <th>Legal Guardian</th>
                            <th style="text-align: right;">Joined On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = mysqli_fetch_assoc($students_result)): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--primary); font-weight: 700;">#<?php echo $s['id']; ?></td>
                            <td>
                                <div class="student-profile">
                                    <div class="avatar"><?php echo strtoupper(substr($s['name'], 0, 1)); ?></div>
                                    <strong style="color: white;"><?php echo htmlspecialchars($s['name']); ?></strong>
                                </div>
                            </td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars($s['email']); ?></td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars($s['phone']); ?></td>
                            <td>
                                <?php if($s['gender'] == 'Male'): ?>
                                    <span class="gender-male"><i class="fas fa-mars"></i> M</span>
                                <?php else: ?>
                                    <span class="gender-female"><i class="fas fa-venus"></i> F</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($s['has_room']): ?>
                                    <span class="badge badge-allocated"><i class="fas fa-door-open"></i> <?php echo $s['room_number']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-waiting"><i class="fas fa-clock"></i> UNASSIGNED</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="display: block; font-weight: 600; color: white;"><?php echo htmlspecialchars($s['guardian_name']); ?></span>
                                <small style="color: var(--text-dim); font-size: 0.75rem;"><?php echo $s['guardian_phone']; ?></small>
                            </td>
                            <td style="text-align: right; color: var(--text-dim); font-size: 0.8rem;">
                                <?php echo date('d M, Y', strtotime($s['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Logic: Real-time search filter
        function filterDirectory() {
            const query = document.getElementById('directorySearch').value.toUpperCase();
            const rows = document.getElementById('directoryTable').getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].innerText.toUpperCase().includes(query)) {
                        found = true; break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>