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

// Logic: Handle Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    if (!empty($subject) && !empty($description)) {
        $query = "INSERT INTO complaints (student_id, subject, description) VALUES ($student_id, '$subject', '$description')";
        if (mysqli_query($conn, $query)) {
            $success = 'Complaint submitted! Our team will review it shortly.';
        } else {
            $error = 'Submission failed. Please try again.';
        }
    } else {
        $error = 'Please fill all fields.';
    }
}

// Logic: Fetch History
$complaints_query = "SELECT * FROM complaints WHERE student_id = $student_id ORDER BY created_at DESC";
$complaints_result = mysqli_query($conn, $complaints_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support & Complaints | GUARDIAN HOSTEL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body { background: #0f172a; color: white; min-height: 100vh; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(circle at 0% 0%, #1e1b4b 0%, #0f172a 50%); }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .support-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; margin-bottom: 4rem; }

        /* Form Styling */
        .glass-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 24px; padding: 2.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        
        input, textarea { 
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 12px; padding: 14px; color: white; outline: none; transition: 0.3s;
        }
        input:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }

        .btn-submit { 
            width: 100%; background: var(--primary); color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; gap: 10px;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }

        /* Info Panel */
        .info-panel { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 24px; padding: 2.5rem; border: 1px solid var(--border); }
        .step { display: flex; gap: 15px; margin-bottom: 2rem; }
        .step-num { width: 35px; height: 35px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
        
        /* History Table */
        .history-section { background: var(--glass); border-radius: 24px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 4rem; }
        .table-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 2rem; background: rgba(255,255,255,0.02); font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 1.2rem 2rem; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        
        /* Status Badges */
        .badge { padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .badge-progress { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .badge-resolved { background: rgba(16, 185, 129, 0.1); color: #34d399; }

        @media (max-width: 900px) { .support-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Help Center<span>.</span></h1>
            <p>Report issues or track your support history</p>
        </header>

        <div class="support-grid">
            <div class="glass-card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-pen-to-square" style="color: var(--primary);"></i> Raise a Concern</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="margin-bottom: 20px;"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Issue Subject</label>
                        <input type="text" name="subject" placeholder="Summary of the problem" required>
                    </div>
                    <div class="form-group">
                        <label>Detailed Description</label>
                        <textarea name="description" rows="5" placeholder="Include room number or specific details..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">
                        Submit Complaint <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

            <div class="info-panel">
                <h3 style="margin-bottom: 25px;"><i class="fas fa-circle-info" style="color: var(--primary);"></i> Resolution Process</h3>
                
                <div class="step">
                    <div class="step-num">1</div>
                    <div>
                        <strong>Submission</strong>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin-top: 5px;">Submit your request with clear details.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div>
                        <strong>Review</strong>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin-top: 5px;">Warden/Admin assigns it to the staff.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div>
                        <strong>Action</strong>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin-top: 5px;">Issue is resolved and marked as closed.</p>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 16px; border-left: 4px solid #fbbf24;">
                    <p style="font-size: 0.85rem; line-height: 1.6;">
                        <i class="fas fa-lightbulb"></i> <strong>Pro Tip:</strong> Be specific about maintenance issues (e.g., "Left tap leaking") for faster resolution.
                    </p>
                </div>
            </div>
        </div>

        <div class="history-section">
            <div class="table-header">
                <h3>Support History</h3>
                <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 700;">TOTAL: <?php echo mysqli_num_rows($complaints_result); ?></span>
            </div>
            
            <div style="overflow-x: auto;">
                <?php if (mysqli_num_rows($complaints_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($complaint = mysqli_fetch_assoc($complaints_result)): ?>
                        <tr>
                            <td style="font-weight: 600;">
                                <?php echo htmlspecialchars($complaint['subject']); ?>
                                <p style="font-weight: 400; font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">
                                    <?php echo htmlspecialchars(substr($complaint['description'], 0, 50)) . '...'; ?>
                                </p>
                            </td>
                            <td>
                                <?php if ($complaint['status'] == 'Pending'): ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                <?php elseif ($complaint['status'] == 'In Progress'): ?>
                                    <span class="badge badge-progress"><i class="fas fa-spinner"></i> Working</span>
                                <?php else: ?>
                                    <span class="badge badge-resolved"><i class="fas fa-check-circle"></i> Resolved</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M, Y', strtotime($complaint['created_at'])); ?></td>
                            <td><?php echo date('d M, Y', strtotime($complaint['updated_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; color: #94a3b8;">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No complaints filed yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../js/script.js"></script>
</body>
</html>