<?php
require_once '../config.php';

$message = '';
$success = false;

// Logic: Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters long!';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match!';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE admin SET password = '$hashed_password' WHERE id = 1";

        if (mysqli_query($conn, $query)) {
            $success = true;
            $message = 'Admin password has been reset successfully! Access restored.';
        } else {
            $message = 'Database Error: ' . mysqli_error($conn);
        }
    }
}

// Logic: Fetch current admin for context
$admin_query = "SELECT * FROM admin WHERE id = 1";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Override | GUARDIAN HOSTEL Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #fbbf24;
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
            overflow-x: hidden;
        }

        .mesh-bg { 
            position: fixed; inset: 0; z-index: -1; 
            background: radial-gradient(at 0% 0%, #1e1b4b 0%, #020617 50%); 
        }

        .recovery-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border: 1px solid var(--border);
            border-radius: 32px;
            padding: 3rem;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            position: relative;
        }

        /* Decorative Header */
        .recovery-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px;
            background: repeating-linear-gradient(90deg, var(--primary), var(--primary) 20px, transparent 20px, transparent 40px);
            border-radius: 32px 32px 0 0;
        }

        .header { text-align: center; margin-bottom: 2rem; }
        .header i { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; }
        .header h2 { font-size: 1.8rem; font-weight: 800; letter-spacing: -1px; }

        /* Security Warning Banner */
        .warning-strip {
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 1.2rem; border-radius: 16px; margin-bottom: 2rem;
            display: flex; align-items: center; gap: 15px; font-size: 0.85rem;
        }

        /* Account Info Box */
        .info-panel {
            background: rgba(0,0,0,0.3); border: 1px solid var(--border);
            border-radius: 16px; padding: 1.2rem; margin-bottom: 2rem;
        }
        .info-panel span { color: #94a3b8; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .info-panel strong { color: white; font-family: monospace; }

        /* Form Controls */
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 14px; top: 14px; color: #475569; }
        input { 
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border); 
            border-radius: 12px; padding: 12px 40px; color: white; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(251, 191, 36, 0.1); }

        .toggle-btn { position: absolute; right: 12px; top: 12px; background: none; border: none; color: #475569; cursor: pointer; }
        .toggle-btn:hover { color: white; }

        .btn-reset { 
            width: 100%; background: var(--primary); color: #000; border: none; padding: 16px; 
            border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s;
            display: flex; justify-content: center; gap: 10px; margin-top: 1rem;
        }
        .btn-reset:hover { background: #fff; transform: translateY(-3px); }

        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <main class="recovery-card">
        <div class="header">
            <i class="fas fa-shield-halved"></i>
            <h2>Credential Override<span>.</span></h2>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas fa-<?php echo $success ? 'circle-check' : 'circle-exclamation'; ?>"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="warning-strip">
            <i class="fas fa-triangle-exclamation" style="color: var(--danger); font-size: 1.5rem;"></i>
            <div>
                <strong>TEMPORARY TOOL:</strong> This file exposes internal database functions. You <u>must</u> delete it from the server immediately after use.
            </div>
        </div>

        <?php if ($admin): ?>
        <div class="info-panel">
            <span>Target Administrative Account</span>
            <strong><?php echo htmlspecialchars($admin['username']); ?></strong> (<?php echo htmlspecialchars($admin['email']); ?>)
        </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label>New Security Key</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters" required minlength="6">
                        <button type="button" class="toggle-btn" onclick="toggleView('new_password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Access Key</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter for verification" required minlength="6">
                        <button type="button" class="toggle-btn" onclick="toggleView('confirm_password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <button type="submit" name="reset_password" class="btn-reset">
                    AUTHORIZE OVERRIDE <i class="fas fa-key"></i>
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="login.php" class="btn-reset" style="background: white; text-decoration: none;">
                    GO TO ADMIN LOGIN <i class="fas fa-arrow-right"></i>
                </a>
                <p style="color: var(--danger); font-size: 0.75rem; margin-top: 2rem; font-weight: 800;">
                    <i class="fas fa-trash"></i> DELETE THIS FILE NOW
                </p>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function toggleView(id) {
            const el = document.getElementById(id);
            const icon = el.nextElementSibling.querySelector('i');
            if (el.type === 'password') {
                el.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                el.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Logic: Success Alert
        <?php if ($success): ?>
            window.onload = function() {
                alert('⚠️ OVERRIDE SUCCESSFUL. Please delete "reset_admin_password.php" from your server immediately to prevent hijacking.');
            }
        <?php endif; ?>
    </script>
</body>
</html>