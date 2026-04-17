<?php
require_once '../config.php';

if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $guardian_name = mysqli_real_escape_string($conn, trim($_POST['guardian_name']));
    $guardian_phone = mysqli_real_escape_string($conn, trim($_POST['guardian_phone']));

    // --- Profile Image Upload Logic ---
    $photo_name = 'default_user.png'; // Default if no upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('student_', true) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $new_filename)) {
                $photo_name = $new_filename;
            } else {
                $error = 'Failed to upload profile photo.';
            }
        } else {
            $error = 'Invalid image format. Only JPG, JPEG, and PNG allowed.';
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($gender) || empty($address) || empty($guardian_name) || empty($guardian_phone)) {
            $error = 'Please fill all fields';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $check_query = "SELECT * FROM students WHERE email = '$email'";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                $error = 'Email already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Added 'photo' to the INSERT query
                $query = "INSERT INTO students (name, email, password, phone, gender, address, guardian_name, guardian_phone, photo) 
                          VALUES ('$name', '$email', '$hashed_password', '$phone', '$gender', '$address', '$guardian_name', '$guardian_phone', '$photo_name')";

                if (mysqli_query($conn, $query)) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GUARDIAN HOSTEL | Student Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --accent: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.4);
            --glass: rgba(255, 255, 255, 0.05);
            --text-white: #ffffff;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        .mesh-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: 
                radial-gradient(at 0% 100%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(225,39%,30%,1) 0, transparent 50%);
        }

        .back-home { position: fixed; top: 20px; left: 20px; z-index: 100; }
        .back-home a { 
            color: var(--text-white); text-decoration: none; font-weight: 600;
            padding: 10px 20px; background: rgba(255,255,255,0.1); border-radius: 12px;
            backdrop-filter: blur(5px); display: block; border: 1px solid rgba(255,255,255,0.1);
        }

        .reg-container { width: 100%; max-width: 900px; }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 50px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .form-header { text-align: center; margin-bottom: 40px; }
        .icon-circle {
            width: 70px; height: 70px; background: var(--accent); color: white;
            font-size: 1.8rem; border-radius: 20px; display: flex;
            align-items: center; justify-content: center; margin: 0 auto 15px;
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .form-header h2 { color: white; font-size: 2rem; font-weight: 800; }
        .form-header p { color: var(--text-dim); }

        /* --- New Image Preview Styling --- */
        .photo-upload-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .image-preview-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            overflow: hidden;
            margin-bottom: 15px;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px var(--accent-glow);
        }
        .image-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-preview-container i {
            font-size: 3rem;
            color: var(--text-dim);
        }
        .upload-label {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .upload-label:hover { background: var(--accent); }
        input[type="file"] { display: none; }

        .input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .form-section label {
            display: block; color: var(--accent); font-weight: 700;
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 15px;
        }

        input, select, textarea {
            width: 100%; background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 14px 18px; color: white;
            margin-bottom: 15px; outline: none; transition: 0.3s;
        }

        input:focus, textarea:focus { border-color: var(--accent); background: rgba(0,0,0,0.4); }

        .split-input { display: grid; grid-template-columns: 1.5fr 1fr; gap: 10px; }

        .pass-box { position: relative; }
        .toggle-eye {
            position: absolute; right: 15px; top: 18px; color: var(--text-dim);
            cursor: pointer;
        }

        .alert { 
            padding: 15px; border-radius: 12px; margin-bottom: 30px; 
            display: flex; align-items: center; gap: 10px; font-size: 0.9rem;
        }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.2); }

        .submit-btn {
            width: 100%; padding: 18px; background: var(--accent);
            border: none; border-radius: 16px; color: white;
            font-weight: 800; text-transform: uppercase; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 12px;
            transition: 0.3s;
        }
        .submit-btn:hover { background: #4f46e5; transform: translateY(-3px); box-shadow: 0 10px 20px var(--accent-glow); }

        .form-footer { text-align: center; margin-top: 25px; color: var(--text-dim); }
        .form-footer a { color: var(--accent); text-decoration: none; font-weight: 700; }

        @media (max-width: 850px) {
            .input-grid { grid-template-columns: 1fr; gap: 0; }
            .glass-card { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <div class="back-home animate__animated animate__fadeIn">
        <a href="../index.php"><i class="fas fa-chevron-left"></i> Home</a>
    </div>

    <div class="reg-container animate__animated animate__zoomIn">
        <div class="glass-card">
            <div class="form-header">
                <div class="icon-circle">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Account</h2>
                <p>Join the GUARDIAN HOSTEL student community</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger animate__animated animate__headShake">
                    <i class="fas fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success animate__animated animate__fadeIn">
                    <i class="fas fa-circle-check"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateRegistration()">
                
                <div class="photo-upload-section">
                    <div class="image-preview-container" id="imagePreview">
                        <i class="fas fa-user"></i>
                    </div>
                    <label for="profile_photo" class="upload-label">
                        <i class="fas fa-camera"></i> Select Profile Photo
                    </label>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*" onchange="previewImage(this)">
                    <small style="color: var(--text-dim); margin-top: 8px;">JPG, PNG or JPEG (Max 2MB)</small>
                </div>

                <div class="input-grid">
                    <div class="form-section">
                        <label><i class="fas fa-id-card"></i> Personal Details</label>
                        <input type="text" name="name" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email Address" required>
                        
                        <div class="split-input">
                            <input type="tel" name="phone" id="phone" placeholder="Phone Number" required>
                            <select name="gender" required>
                                <option value="" disabled selected>Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <textarea name="address" rows="3" placeholder="Permanent Address" required></textarea>
                    </div>

                    <div class="form-section">
                        <label><i class="fas fa-shield-heart"></i> Emergency & Security</label>
                        <input type="text" name="guardian_name" placeholder="Guardian Name" required>
                        <input type="tel" name="guardian_phone" id="guardian_phone" placeholder="Guardian Phone" required>
                        
                        <div class="pass-box">
                            <input type="password" name="password" id="password" placeholder="Create Password" required>
                            <i class="fas fa-eye toggle-eye" onclick="togglePass('password')"></i>
                        </div>
                        <div class="pass-box">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                            <i class="fas fa-eye toggle-eye" onclick="togglePass('confirm_password')"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span>Finalize Registration</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

            <div class="form-footer">
                Already registered? <a href="login.php">Log in here</a>
            </div>
        </div>
    </div>

    <script>
        // Image Preview Logic
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function togglePass(id) {
            const el = document.getElementById(id);
            el.type = el.type === 'password' ? 'text' : 'password';
        }

        function validateRegistration() {
            const pwd = document.getElementById('password').value;
            const cpwd = document.getElementById('confirm_password').value;
            const phone = document.getElementById('phone').value;

            if (pwd.length < 6) { alert('Password must be 6+ characters'); return false; }
            if (pwd !== cpwd) { alert('Passwords do not match'); return false; }
            if (phone.length < 10) { alert('Invalid Phone Number'); return false; }
            return true;
        }
    </script>
</body>
</html>