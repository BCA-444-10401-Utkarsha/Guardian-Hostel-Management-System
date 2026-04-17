<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$message = '';
$error = '';

/* --- NEW: Profile Photo Update Logic --- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_photo'])) {
    if ($_FILES['new_photo']['error'] == 0) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        $file_ext = strtolower(pathinfo($_FILES['new_photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('student_', true) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['new_photo']['tmp_name'], $upload_dir . $new_filename)) {
                
                // Update Database
                $update_photo = "UPDATE students SET photo = '$new_filename' WHERE id = $student_id";
                if (mysqli_query($conn, $update_photo)) {
                    $message = 'Profile picture updated successfully!';
                } else { $error = 'Database update failed.'; }
            } else { $error = 'Failed to move uploaded file.'; }
        } else { $error = 'Only JPG, JPEG & PNG files are allowed.'; }
    } else { $error = 'Error uploading file.'; }
}

/* --- Original PHP Logic: Profile Update --- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $guardian_name = mysqli_real_escape_string($conn, trim($_POST['guardian_name']));
    $guardian_phone = mysqli_real_escape_string($conn, trim($_POST['guardian_phone']));

    if (!empty($phone) && !empty($address) && !empty($guardian_name) && !empty($guardian_phone)) {
        if (preg_match('/^[0-9]{10}$/', $phone) && preg_match('/^[0-9]{10}$/', $guardian_phone)) {
            $update_query = "UPDATE students SET phone = '$phone', address = '$address', guardian_name = '$guardian_name', guardian_phone = '$guardian_phone' WHERE id = $student_id";
            if (mysqli_query($conn, $update_query)) {
                $message = 'Profile updated successfully!';
            } else { $error = 'Failed to update profile.'; }
        } else { $error = 'Phone numbers must be 10 digits.'; }
    } else { $error = 'Please fill all fields.'; }
}

/* --- Original PHP Logic: Password Change --- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $query = "SELECT password FROM students WHERE id = $student_id";
        $result = mysqli_query($conn, $query);
        $student_data = mysqli_fetch_assoc($result);

        if (password_verify($current_password, $student_data['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    if (mysqli_query($conn, "UPDATE students SET password = '$hashed_password' WHERE id = $student_id")) {
                        $message = 'Password changed successfully!';
                    } else { $error = 'Update failed.'; }
                } else { $error = 'Minimum 6 characters required.'; }
            } else { $error = 'Passwords do not match.'; }
        } else { $error = 'Current password incorrect.'; }
    } else { $error = 'Fill all password fields.'; }
}

/* --- Original PHP Logic: Data Fetching --- */
$query = "SELECT * FROM students WHERE id = $student_id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

$room_query = "SELECT rr.*, r.room_number, r.room_type, r.rent FROM room_requests rr LEFT JOIN rooms r ON rr.room_id = r.id WHERE rr.student_id = $student_id AND rr.status = 'Approved' ORDER BY rr.request_date DESC LIMIT 1";
$room_result = mysqli_query($conn, $room_query);
$room_info = mysqli_fetch_assoc($room_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | GURDIAN HOSTEL Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #0f172a; color: white; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #0f172a 50%); }

        .profile-wrapper { max-width: 1100px; margin: 3rem auto; padding: 0 20px 5rem; }

        /* Header Card */
        .profile-hero {
            background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--glass-border);
            border-radius: 32px; padding: 3rem; display: flex; align-items: center; gap: 3rem; margin-bottom: 2rem;
        }

        /* --- UPDATED: Editable Avatar Box --- */
        .avatar-box {
            width: 140px; height: 140px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 3.5rem; font-weight: 800; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            overflow: hidden; position: relative;
        }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }

        /* Edit Overlay */
        .photo-edit-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: 0.3s; cursor: pointer;
        }
        .avatar-box:hover .photo-edit-overlay { opacity: 1; }
        .photo-edit-overlay i { font-size: 1.5rem; color: white; }

        .hero-info h2 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .hero-info p { color: var(--text-dim); font-weight: 500; }

        .action-bar { display: flex; gap: 10px; margin-bottom: 2rem; }
        .btn-tab {
            background: var(--glass); border: 1px solid var(--glass-border); color: white;
            padding: 12px 24px; border-radius: 14px; cursor: pointer; font-weight: 700;
            transition: 0.3s; display: flex; align-items: center; gap: 10px;
        }
        .btn-tab:hover { background: rgba(255,255,255,0.08); border-color: var(--primary); }

        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .info-widget { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; padding: 2rem; }
        .widget-head { display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem; color: var(--primary); }
        .widget-head h3 { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: white; }

        .data-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .data-row label { color: var(--text-dim); font-size: 0.85rem; font-weight: 600; }
        .data-row span { font-weight: 700; font-size: 0.95rem; }

        .modal { 
            display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.8);
            backdrop-filter: blur(10px); z-index: 3000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #1e293b; border: 1px solid var(--glass-border); width: 100%; max-width: 500px;
            border-radius: 28px; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        input, textarea { 
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 12px; padding: 14px; color: white; margin-bottom: 1.2rem; outline: none;
        }

        @media (max-width: 768px) {
            .profile-hero { flex-direction: column; text-align: center; padding: 2rem; }
            .action-bar { flex-direction: column; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="profile-wrapper">
        <?php if($message): ?><div class="alert alert-success" style="margin-bottom:2rem;"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger" style="margin-bottom:2rem;"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

        <header class="profile-hero">
            <form id="photoForm" method="POST" enctype="multipart/form-data">
                <div class="avatar-box" onclick="document.getElementById('new_photo').click()">
                    <?php if (!empty($student['photo']) && $student['photo'] != 'default_user.png'): ?>
                        <img src="../uploads/profiles/<?php echo $student['photo']; ?>" alt="Profile Photo">
                    <?php else: ?>
                        <?php echo substr($student['name'], 0, 1); ?>
                    <?php endif; ?>
                    <div class="photo-edit-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <input type="file" name="new_photo" id="new_photo" style="display:none" accept="image/*" onchange="document.getElementById('photoForm').submit()">
            </form>

            <div class="hero-info">
                <h2><?php echo htmlspecialchars($student['name']); ?><span>.</span></h2>
                <p><i class="far fa-envelope" style="margin-right:8px;"></i> <?php echo htmlspecialchars($student['email']); ?></p>
            </div>
        </header>

        <div class="action-bar">
            <button class="btn-tab" onclick="toggleModal('editModal', true)"><i class="fas fa-user-pen"></i> Edit Personal Info</button>
            <button class="btn-tab" onclick="toggleModal('passModal', true)"><i class="fas fa-shield-halved"></i> Security Settings</button>
        </div>

        <div class="info-grid">
            <div class="info-widget">
                <div class="widget-head"><i class="fas fa-id-card-clip"></i> <h3>Student Profile</h3></div>
                <div class="data-row"><label>Mobile</label><span><?php echo htmlspecialchars($student['phone']); ?></span></div>
                <div class="data-row"><label>Gender</label><span><?php echo htmlspecialchars($student['gender']); ?></span></div>
                <div class="data-row"><label>Joined</label><span><?php echo date('M Y', strtotime($student['created_at'])); ?></span></div>
                <div class="data-row" style="border:none;"><label>Address</label><span style="text-align:right; font-size:0.85rem; color:var(--text-dim);"><?php echo htmlspecialchars($student['address']); ?></span></div>
            </div>

            <div class="info-widget">
                <div class="widget-head"><i class="fas fa-user-shield"></i> <h3>Emergency Contacts</h3></div>
                <div class="data-row"><label>Guardian</label><span><?php echo htmlspecialchars($student['guardian_name']); ?></span></div>
                <div class="data-row"><label>Contact No.</label><span><?php echo htmlspecialchars($student['guardian_phone']); ?></span></div>
                <p style="font-size:0.75rem; color:var(--text-dim); margin-top:2rem; line-height:1.4;">
                    <i class="fas fa-info-circle"></i> Emergency contacts are used for critical notifications and verification.
                </p>
            </div>

            <div class="info-widget">
                <div class="widget-head"><i class="fas fa-house-chimney"></i> <h3>Residency Status</h3></div>
                <?php if ($room_info): ?>
                    <div class="data-row"><label>Room No.</label><span><?php echo $room_info['room_number']; ?></span></div>
                    <div class="data-row"><label>Type</label><span><?php echo $room_info['room_type']; ?> Sharing</span></div>
                    <div class="data-row"><label>Monthly</label><span style="color:#10b981;">₹<?php echo number_format($room_info['rent']); ?></span></div>
                    <div style="margin-top:1.5rem;"><span class="status-chip">ASSIGNED</span></div>
                <?php else: ?>
                    <p style="color:var(--text-dim); font-size:0.9rem; text-align:center; padding: 2rem 0;">
                        No active room allocation found.<br>
                        <a href="apply_room.php" style="color:var(--primary); text-decoration:none; font-weight:800;">Apply for Room →</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Update Profile</h3><button class="close-btn" onclick="toggleModal('editModal', false)">&times;</button></div>
            <form method="POST">
                <label style="font-size:0.7rem; font-weight:800; color:var(--text-dim); display:block; margin-bottom:5px;">PHONE NUMBER</label>
                <input type="text" name="phone" value="<?php echo $student['phone']; ?>" required maxlength="10">
                <label style="font-size:0.7rem; font-weight:800; color:var(--text-dim); display:block; margin-bottom:5px;">PERMANENT ADDRESS</label>
                <textarea name="address" rows="3" required><?php echo $student['address']; ?></textarea>
                <label style="font-size:0.7rem; font-weight:800; color:var(--text-dim); display:block; margin-bottom:5px;">GUARDIAN NAME</label>
                <input type="text" name="guardian_name" value="<?php echo $student['guardian_name']; ?>" required>
                <label style="font-size:0.7rem; font-weight:800; color:var(--text-dim); display:block; margin-bottom:5px;">GUARDIAN PHONE</label>
                <input type="text" name="guardian_phone" value="<?php echo $student['guardian_phone']; ?>" required maxlength="10">
                <button type="submit" name="update_profile" class="btn-primary" style="width:100%; padding:15px; border-radius:12px; font-weight:800; border:none; cursor:pointer;">SAVE CHANGES</button>
            </form>
        </div>
    </div>

    <div id="passModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Change Password</h3><button class="close-btn" onclick="toggleModal('passModal', false)">&times;</button></div>
            <form method="POST">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password (6+ chars)" required minlength="6">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" name="change_password" class="btn-primary" style="width:100%; padding:15px; border-radius:12px; font-weight:800; border:none; cursor:pointer;">UPDATE KEY</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id, show) { document.getElementById(id).style.display = show ? 'flex' : 'none'; }
        window.onclick = function(e) { if (e.target.className === 'modal') e.target.style.display = 'none'; }
    </script>
</body>
</html>