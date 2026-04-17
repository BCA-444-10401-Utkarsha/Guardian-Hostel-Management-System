<?php
require_once '../config.php';

// Logic: Password Hashing
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

$update_status = false;
$update_error = '';

// Logic: Database Update
$query = "UPDATE admin SET password = '$hashed' WHERE username = 'admin'";
if (mysqli_query($conn, $query)) {
    $update_status = true;
} else {
    $update_error = mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Recovery | GUARDIAN HOSTEL Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #fbbf24;
            --success: #10b981;
            --danger: #ef4444;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body { 
            background: #020617; 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 20px;
            margin: 0;
        }

        /* Mesh Background */
        .mesh-bg { 
            position: fixed; inset: 0; z-index: -1; 
            background: radial-gradient(at 0% 0%, #1e1b4b 0%, #020617 50%); 
        }

        .recovery-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 32px;
            padding: 3rem;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            text-align: center;
        }

        .icon-box {
            width: 80px; height: 80px; background: rgba(251, 191, 36, 0.1);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 2rem; color: var(--primary); font-size: 2.5rem;
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        h1 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 10px; }
        p { color: #94a3b8; font-size: 0.95rem; margin-bottom: 2rem; }

        /* Status Styling */
        .status-box {
            padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;
            font-weight: 600; font-size: 0.9rem; line-height: 1.6;
        }
        .status-success { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-error { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Credentials Box */
        .creds-box {
            background: rgba(0,0,0,0.3); border: 1px solid var(--border);
            border-radius: 16px; padding: 1.5rem; text-align: left; margin-bottom: 2rem;
        }
        .cred-item { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .cred-item:last-child { margin-bottom: 0; }
        .cred-item span { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; }
        .cred-item strong { color: white; font-family: monospace; font-size: 1rem; }

        /* Security Warning */
        .security-alert {
            background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger);
            color: white; padding: 1rem; border-radius: 12px; margin-top: 1rem;
            font-size: 0.85rem; display: flex; align-items: center; gap: 10px;
        }

        .btn-login {
            display: inline-flex; align-items: center; gap: 10px;
            background: var(--primary); color: #000; text-decoration: none;
            padding: 14px 30px; border-radius: 12px; font-weight: 800;
            transition: 0.3s; margin-top: 1rem;
        }
        .btn-login:hover { background: #fff; transform: translateY(-3px); }

        .hash-preview {
            font-size: 0.7rem; color: #475569; margin-top: 2rem;
            word-break: break-all; font-family: monospace;
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <main class="recovery-card">
        <div class="icon-box">
            <i class="fas fa-microchip"></i>
        </div>

        <h1>Access Recovery<span>.</span></h1>
        <p>Administrative credential override tool</p>

        <?php if ($update_status): ?>
            <div class="status-box status-success">
                <i class="fas fa-circle-check"></i> Admin password updated successfully!
            </div>

            <div class="creds-box">
                <div class="cred-item"><span>Username</span><strong>admin</strong></div>
                <div class="cred-item"><span>New Password</span><strong>admin123</strong></div>
            </div>

            <a href="login.php" class="btn-login">
                CONTINUE TO LOGIN <i class="fas fa-arrow-right"></i>
            </a>
        <?php else: ?>
            <div class="status-box status-error">
                <i class="fas fa-circle-xmark"></i> Update Failed: <?php echo $update_error; ?>
            </div>
        <?php endif; ?>

        <div class="security-alert">
            <i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i>
            <div>
                <strong>CRITICAL:</strong> Delete <code>fix_password.php</code> immediately after use to prevent unauthorized access.
            </div>
        </div>

        <div class="hash-preview">
            Generated System Hash: <?php echo $hashed; ?>
        </div>
    </main>

</body>
</html>