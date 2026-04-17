<?php
require_once '../config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: dashboard.php');
                exit();
            } else { $error = 'Access Denied: Invalid Credentials'; }
        } else { $error = 'Access Denied: Invalid Credentials'; }
    } else { $error = 'Security Check: Fields cannot be empty'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUARDIAN HOSTEL | Admin Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary: #fbbf24; /* Admin Gold */
            --bg-dark: #020617;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-dark); color: white; min-height: 100vh; overflow: hidden; }

        .login-grid { display: grid; grid-template-columns: 1.2fr 1fr; min-height: 100vh; }

        /* Left Side: Brand & Visuals */
        .visual-panel {
            background: radial-gradient(circle at top left, #1e1b4b 0%, #020617 70%);
            display: flex; flex-direction: column; justify-content: center; padding: 10%;
            position: relative; border-right: 1px solid var(--border);
        }
        .visual-panel::after {
            content: ''; position: absolute; inset: 0;
            background: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            opacity: 0.05; pointer-events: none;
        }
        .brand-header { margin-bottom: 3rem; }
        .brand-logo { 
            width: 60px; height: 60px; background: var(--primary); 
            border-radius: 18px; display: flex; align-items: center; 
            justify-content: center; color: #000; font-size: 1.8rem; margin-bottom: 20px;
        }
        .brand-header h1 { font-size: 3rem; font-weight: 800; letter-spacing: -2px; }
        .brand-header h1 span { color: var(--primary); }
        .visual-panel p { font-size: 1.1rem; color: var(--text-dim); line-height: 1.6; max-width: 450px; }

        /* Right Side: Form Panel */
        .form-panel { display: flex; align-items: center; justify-content: center; padding: 40px; position: relative; }
        .glass-card { 
            width: 100%; max-width: 440px; background: var(--glass); 
            backdrop-filter: blur(25px); border: 1px solid var(--border); 
            border-radius: 32px; padding: 3rem; position: relative; z-index: 10;
        }

        .header { margin-bottom: 2.5rem; }
        .header h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 8px; }
        .header p { color: var(--text-dim); font-size: 0.95rem; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 10px; letter-spacing: 1px; }
        
        .field-wrapper { position: relative; }
        .field-wrapper i:not(.toggle-eye) { position: absolute; left: 16px; top: 16px; color: var(--text-dim); font-size: 1rem; }
        .field-wrapper input { 
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border); 
            border-radius: 14px; padding: 14px 14px 14px 45px; color: white; outline: none; transition: 0.3s;
        }
        .field-wrapper input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(251, 191, 36, 0.1); }
        
        .toggle-eye { position: absolute; right: 16px; top: 16px; cursor: pointer; color: var(--text-dim); transition: 0.3s; }
        .toggle-eye:hover { color: var(--primary); }

        .btn-auth { 
            width: 100%; background: var(--primary); color: #000; border: none; padding: 18px; 
            border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s;
            display: flex; justify-content: center; gap: 12px; text-transform: uppercase; margin-top: 1rem;
        }
        .btn-auth:hover { transform: translateY(-3px); background: #fff; box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3); }

        .security-alert {
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 1rem; border-radius: 12px; color: #fca5a5; font-size: 0.85rem; margin-bottom: 2rem;
            display: flex; align-items: center; gap: 10px;
        }

        .back-link { margin-top: 2rem; text-align: center; }
        .back-link a { color: var(--text-dim); text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.3s; }
        .back-link a:hover { color: white; }

        @media (max-width: 1000px) {
            .login-grid { grid-template-columns: 1fr; }
            .visual-panel { display: none; }
        }
    </style>
</head>
<body>

    <main class="login-grid">
        <section class="visual-panel">
            <div class="animate__animated animate__fadeInLeft">
                <div class="brand-header">
                    <div class="brand-logo"><i class="fas fa-shield-halved"></i></div>
                    <h1>GUARDIAN HOSTEL<span>.</span></h1>
                </div>
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Central Management Terminal</h2>
                <p>Welcome to the administrative interface. Access the core system to manage residency allocations, student support tickets, and logistics.</p>
            </div>
        </section>

        <section class="form-panel">
            <div class="glass-card animate__animated animate__zoomIn">
                <div class="header">
                    <h2>Admin Authentication</h2>
                    <p>Enter your credentials to proceed</p>
                </div>

                <?php if ($error): ?>
                    <div class="security-alert animate__animated animate__shakeX">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="input-group">
                        <label>Administrative ID</label>
                        <div class="field-wrapper">
                            <i class="fas fa-user-shield"></i>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Security Key</label>
                        <div class="field-wrapper">
                            <i class="fas fa-key"></i>
                            <input type="password" name="password" id="passInput" placeholder="••••••••" required>
                            <i class="fas fa-eye-slash toggle-eye" onclick="toggleVisibility()"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth">
                        <span>Authorize Access</span>
                        <i class="fas fa-fingerprint"></i>
                    </button>
                </form>

                <div class="back-link">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Return to Main Portal</a>
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleVisibility() {
            const pass = document.getElementById('passInput');
            const icon = document.querySelector('.toggle-eye');
            if (pass.type === 'password') {
                pass.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                pass.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
    </script>
</body>
</html>