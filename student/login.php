<?php
require_once '../config.php';

// Check if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Logic: Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM students WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['name'];
                $_SESSION['student_email'] = $student['email'];
                header('Location: dashboard.php');
                exit();
            } else { $error = 'Access Denied: Invalid Credentials'; }
        } else { $error = 'Access Denied: Invalid Credentials'; }
    } else { $error = 'Security Check: Please fill all fields'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Access | GUARDIAN HOSTEL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary: #6366f1;
            --bg-dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-dark); color: white; min-height: 100vh; overflow: hidden; }

        /* The 2026 Mesh Background */
        .mesh-bg { 
            position: fixed; inset: 0; z-index: -1; 
            background: radial-gradient(at 0% 0%, #1e1b4b 0%, #0f172a 50%); 
        }

        .login-wrapper { display: grid; grid-template-columns: 1fr 480px; min-height: 100vh; }

        /* Left Side: Branding */
        .brand-panel {
            padding: 10%; display: flex; flex-direction: column; justify-content: center;
            background: linear-gradient(rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.5)), 
            url('https://images.unsplash.com/photo-1523050853051-f75dbba891c2?q=80&w=2070') center/cover;
            border-right: 1px solid var(--border);
        }
        .brand-panel h1 { font-size: 4rem; font-weight: 800; letter-spacing: -2px; line-height: 1; margin-bottom: 1.5rem; }
        .brand-panel h1 span { color: var(--primary); }
        .brand-panel p { font-size: 1.2rem; color: #cbd5e1; max-width: 500px; line-height: 1.6; }

        /* Right Side: Form */
        .form-panel { display: flex; align-items: center; justify-content: center; padding: 40px; background: rgba(0,0,0,0.2); }
        .glass-card { 
            width: 100%; background: var(--glass); backdrop-filter: blur(20px); 
            border: 1px solid var(--border); border-radius: 32px; padding: 3rem; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .header { margin-bottom: 2.5rem; }
        .header h2 { font-size: 2rem; font-weight: 800; margin-bottom: 10px; }
        .header p { color: var(--text-dim); font-size: 0.95rem; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        
        .field-box { position: relative; }
        .field-box i:not(.toggle-btn) { position: absolute; left: 16px; top: 16px; color: var(--text-dim); }
        input { 
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border); 
            border-radius: 14px; padding: 14px 14px 14px 45px; color: white; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(99, 102, 241, 0.1); }

        .toggle-btn { position: absolute; right: 16px; top: 16px; background: none; border: none; color: var(--text-dim); cursor: pointer; }

        .btn-auth { 
            width: 100%; background: var(--primary); color: white; border: none; padding: 18px; 
            border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s;
            display: flex; justify-content: center; gap: 10px; font-size: 1rem; margin-top: 1rem;
        }
        .btn-auth:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3); }

        .alert-error { 
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); 
            padding: 1rem; border-radius: 12px; color: #fca5a5; margin-bottom: 2rem;
            display: flex; align-items: center; gap: 10px; font-size: 0.85rem;
        }

        .footer-links { margin-top: 2.5rem; text-align: center; border-top: 1px solid var(--border); padding-top: 2rem; font-size: 0.9rem; }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 700; }

        .back-home { position: fixed; top: 30px; left: 30px; z-index: 100; text-decoration: none; color: white; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }

        @media (max-width: 1024px) {
            .login-wrapper { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
            .form-panel { background: none; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <a href="../index.php" class="back-home"><i class="fas fa-arrow-left"></i> BACK TO EXPLORE</a>

    <main class="login-wrapper">
        <section class="brand-panel animate__animated animate__fadeIn">
            <div class="animate__animated animate__fadeInUp">
                <h1>GUARDIAN HOSTEL<span>.</span></h1>
                <p>Welcome to your digital residency portal. Access your dashboard to manage room allocations, track financial history, and view the mess schedule.</p>
            </div>
        </section>

        <section class="form-panel">
            <div class="glass-card animate__animated animate__zoomIn">
                <div class="header">
                    <h2>Portal Access</h2>
                    <p>Enter your institutional credentials</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error animate__animated animate__shakeX">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="input-group">
                        <label>Student Email</label>
                        <div class="field-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="name@college.edu" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Security Key</label>
                        <div class="field-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="passInput" name="password" placeholder="••••••••" required>
                            <button type="button" class="toggle-btn" onclick="togglePass()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth">
                        AUTHORIZE LOGIN <i class="fas fa-shield-halved"></i>
                    </button>
                </form>

                <div class="footer-links">
                    <p style="color: var(--text-dim);">New to GUARDIAN HOSTEL? <a href="register.php">Register</a></p>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePass() {
            const input = document.getElementById('passInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>