<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

/* --- Original Logic: Approval Handling --- */
if (isset($_GET['approve']) && isset($_GET['room_id'])) {
    $request_id = (int)$_GET['approve'];
    $room_id = (int)$_GET['room_id'];

    $request_query = mysqli_query($conn, "SELECT * FROM room_requests WHERE id = $request_id");
    $request_data = mysqli_fetch_assoc($request_query);
    $old_room_id = $request_data['room_id'];

    $room_query = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $room_id");
    $room = mysqli_fetch_assoc($room_query);

    if ($room && $room['occupied'] < $room['capacity']) {
        if ($old_room_id) {
            $update_old_room = "UPDATE rooms SET occupied = occupied - 1, status = IF(occupied - 1 < capacity, 'Available', status) WHERE id = $old_room_id";
            mysqli_query($conn, $update_old_room);
        }
        mysqli_query($conn, "UPDATE room_requests SET status = 'Approved', room_id = $room_id, response_date = NOW() WHERE id = $request_id");
        mysqli_query($conn, "UPDATE rooms SET occupied = occupied + 1, status = IF(occupied + 1 >= capacity, 'Full', 'Available') WHERE id = $room_id");
        $success = $old_room_id ? 'Room reassigned successfully.' : 'Request approved successfully.';
    } else { $error = 'Target room reached maximum capacity.'; }
}

/* --- Original Logic: Rejection Handling --- */
if (isset($_GET['reject'])) {
    $request_id = (int)$_GET['reject'];
    mysqli_query($conn, "UPDATE room_requests SET status = 'Rejected', response_date = NOW() WHERE id = $request_id");
    $success = 'Room request rejected.';
}

/* --- Logic: Statistics Counting --- */
$total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests"))['count'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests WHERE status = 'Pending'"))['count'];
$approved_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests WHERE status = 'Approved'"))['count'];
$rejected_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests WHERE status = 'Rejected'"))['count'];
$pending_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM room_requests WHERE status = 'Approved' AND payment_status = 'Pending'"))['count'];

/* --- Logic: Data Fetching --- */
$requests_query = "SELECT rr.*, s.name as student_name, s.email, s.phone, r.room_number, p.transaction_id, p.payment_method FROM room_requests rr JOIN students s ON rr.student_id = s.id LEFT JOIN rooms r ON rr.room_id = r.id LEFT JOIN payments p ON rr.id = p.room_request_id ORDER BY rr.request_date DESC";
$requests_result = mysqli_query($conn, $requests_query);
$available_rooms_query = "SELECT * FROM rooms WHERE occupied < capacity ORDER BY room_number";
$available_rooms = mysqli_query($conn, $available_rooms_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Hub | GUARDIAN HOSTEL Admin</title>
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
        .page-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }

        /* Multi-Stat Header */
        .stats-matrix { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 3rem; }
        .stat-pill { background: var(--glass); border: 1px solid var(--border); border-radius: 20px; padding: 1.2rem; text-align: center; }
        .stat-pill h3 { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .stat-pill small { color: var(--text-dim); text-transform: uppercase; font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; }

        /* Desktop Table View */
        .panel-container { background: var(--glass); border: 1px solid var(--border); border-radius: 32px; overflow: hidden; margin-bottom: 5rem; backdrop-filter: blur(15px); }
        .panel-head { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.01); }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 1rem; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 1.2rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        /* Component Styles */
        .student-cell { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 35px; height: 35px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 0.65rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .badge-approved { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .badge-rejected { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .badge-paid { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }

        .control-group { display: flex; flex-direction: column; gap: 8px; }
        .admin-select { background: #0f172a; color: var(--primary); border: 1.5px solid var(--primary); padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; width: 100%; }
        .btn-reject { color: #f87171; text-decoration: none; font-size: 0.75rem; font-weight: 800; text-align: center; border: 1px solid rgba(248, 113, 113, 0.2); padding: 4px; border-radius: 6px; }
        .btn-reject:hover { background: #ef4444; color: white; }

        @media (max-width: 1200px) {
            th:nth-child(2), td:nth-child(2), th:nth-child(3), td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Request Processing Hub<span>.</span></h1>
            <p style="color: var(--text-dim);">Verify student applications and initialize room allocations</p>
        </header>

        <div class="stats-matrix">
            <div class="stat-pill"><small>Volume</small><h3><?php echo $total_requests; ?></h3></div>
            <div class="stat-pill" style="border-color: var(--primary);"><small>Awaiting</small><h3><?php echo $pending_requests; ?></h3></div>
            <div class="stat-pill"><small>Success</small><h3><?php echo $approved_requests; ?></h3></div>
            <div class="stat-pill"><small>Declined</small><h3><?php echo $rejected_requests; ?></h3></div>
            <div class="stat-pill"><small>Unpaid Bills</small><h3 style="color: #ef4444;"><?php echo $pending_payments; ?></h3></div>
        </div>

        <?php if($success): ?><div class="alert alert-success" style="margin-bottom:2rem;"><i class="fas fa-check-double"></i> <?php echo $success; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger" style="margin-bottom:2rem;"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

        <section class="panel-container">
            <div class="panel-head">
                <h3 style="font-weight: 800;"><i class="fas fa-list-check" style="color: var(--primary); margin-right: 10px;"></i> Student Entry Pipeline</h3>
                <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Real-time queue</span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Student Resident</th>
                            <th>Contact Info</th>
                            <th>Selection</th>
                            <th>Bill Total</th>
                            <th>Payment Status</th>
                            <th>Application Date</th>
                            <th>Processing Status</th>
                            <th>Action & Assign</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($requests_result)): ?>
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <div class="avatar"><?php echo strtoupper(substr($req['student_name'], 0, 1)); ?></div>
                                    <strong style="color: white;"><?php echo htmlspecialchars($req['student_name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span style="display: block; font-size: 0.8rem;"><?php echo $req['email']; ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><?php echo $req['phone']; ?></span>
                            </td>
                            <td><span style="font-weight: 700; color: #cbd5e1;"><?php echo $req['room_type']; ?></span></td>
                            <td style="font-weight: 800; color: var(--primary);">₹<?php echo number_format($req['total_amount']); ?></td>
                            <td>
                                <?php if($req['payment_status'] == 'Paid'): ?>
                                    <span class="badge badge-paid"><i class="fas fa-check-circle"></i> PAID</span>
                                <?php else: ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i> PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-dim); font-size: 0.8rem;"><?php echo date('d M, Y', strtotime($req['request_date'])); ?></td>
                            <td>
                                <?php 
                                    $st = strtolower($req['status']);
                                    $icon = ($st == 'pending') ? 'clock' : (($st == 'approved') ? 'check-double' : 'times');
                                ?>
                                <span class="badge badge-<?php echo $st; ?>">
                                    <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo strtoupper($req['status']); ?>
                                    <?php if($req['room_number']): ?><br><small>(R: <?php echo $req['room_number']; ?>)</small><?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($req['status'] == 'Pending'): ?>
                                    <div class="control-group">
                                        <select class="admin-select" onchange="if(this.value) window.location.href='room_requests.php?approve=<?php echo $req['id']; ?>&room_id='+this.value">
                                            <option value="">Choose Room</option>
                                            <?php 
                                                mysqli_data_seek($available_rooms, 0);
                                                while ($room = mysqli_fetch_assoc($available_rooms)): 
                                                    if ($room['room_type'] == $req['room_type']):
                                            ?>
                                                <option value="<?php echo $room['id']; ?>"><?php echo $room['room_number']; ?> (<?php echo $room['occupied']; ?>/<?php echo $room['capacity']; ?>)</option>
                                            <?php endif; endwhile; ?>
                                        </select>
                                        <?php if ($req['payment_status'] != 'Paid'): ?>
                                            <a href="room_requests.php?reject=<?php echo $req['id']; ?>" class="btn-reject" onclick="return confirm('Reject request?')">DECLINE</a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--text-dim); font-style: italic; font-size: 0.75rem;">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>