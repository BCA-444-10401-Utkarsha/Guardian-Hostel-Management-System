<?php
require_once 'config.php';

// Logic: Contact Form Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($message)) {
        $query = "INSERT INTO contact_messages (name, email, phone, message) VALUES ('$name', '$email', '$phone', '$message')";
        if (mysqli_query($conn, $query)) {
            $success = "Message sent successfully! We will contact you shortly.";
        } else {
            $error = "System error. Please try again later.";
        }
    } else {
        $error = "All required fields must be filled.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUARDIAN HOSTEL | Premium Student Residences</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/landing.css">
</head>

<body>

    <div class="mesh-bg"></div>

    <nav class="glass-navbar">
        <div class="container nav-wrapper">
            <a href="index.php" class="brand">
                <div class="brand-logo"><i class="fas fa-hotel"></i></div>
                <span>GUARDIAN HOSTEL</span>
            </a>

            <ul class="main-menu" id="navMenu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#rooms">Residences</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

            <div class="user-hub">
                <a href="student/login.php" class="btn-login">Login</a>
                <a href="student/register.php" class="btn-apply">Register</a>
            </div>

            <div class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <header id="home" class="hero">
        <div class="container">
            <div class="hero-content animate__animated animate__fadeInLeft">
                <span class="hero-tag">A New Standard of Student Living</span>
                <h1>Evolve Your<br>Residency<span>.</span></h1>
                <p>Designed for academic excellence and absolute safety. Experience GUARDIAN HOSTEL—where premium comfort meets a thriving educational community.</p>
                <div style="display: flex; gap: 15px;">
                    <a href="#rooms" class="btn-apply" style="padding: 18px 40px; font-size: 1rem;">View Inventory</a>
                    <a href="student/login.php" class="btn-login" style="padding: 18px; border: 1px solid var(--border); border-radius: 12px; display: inline-flex; align-items: center; gap: 10px;">Portal Access <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </header>

    <section id="about">
        <div class="container about-grid">
            <div class="about-img-wrap animate__animated animate__fadeInLeft">
                <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/615069839.jpg?k=eeaf5d7a045a4364d4569954ed2c8733c475df2ec053ec02de1ca80dbd4e86a9&o=" style="width: 100%; display: block;" alt="Hostel Bed Room">
                <div class="float-badge">
                    <h2 style="font-size: 3rem; font-weight: 800;">15+</h2>
                    <p style="font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px;">Years of Excellence</p>
                </div>
            </div>
            <div class="animate__animated animate__fadeInRight">
                <span class="hero-tag">Established 2011</span>
                <h2 style="margin-bottom: 2rem;">Designed for Success,<br>Built for Comfort.</h2>
                <p style="color: var(--text-dim); font-size: 1.1rem; line-height: 1.7; margin-bottom: 2rem;">GUARDIAN HOSTEL is a high-security residence located in the heart of the educational hub. We provide a sanctuary where academic focus and student well-being are the top priorities.</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div style="background: var(--glass); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                        <i class="fas fa-shield-halved" style="color: var(--primary); font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <h4>Active Security</h4>
                        <p style="font-size: 0.8rem; color: var(--text-dim);">24/7 CCTV & Guards</p>
                    </div>
                    <div style="background: var(--glass); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                        <i class="fas fa-bolt" style="color: var(--primary); font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <h4>Fiber Infrastructure</h4>
                        <p style="font-size: 0.8rem; color: var(--text-dim);">Dedicated High Speed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <div class="section-head">
                <span class="hero-tag">Onboarding</span>
                <h2>How to Join the Community</h2>
            </div>
            <div class="process-row">
                <div class="step-card">
                    <div class="step-icon">01</div>
                    <h3>Inventory</h3>
                    <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 10px;">Select your preferred specification: Single, Double or Triple Sharing.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">02</div>
                    <h3>Application</h3>
                    <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 10px;">Complete your profile and guardian details via our digital portal.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">03</div>
                    <h3>Audit</h3>
                    <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 10px;">Our administration team verifies and approves your allocation.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">04</div>
                    <h3>Checkout</h3>
                    <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 10px;">Finalize your payment securely online and move in the same day.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="rooms">
        <div class="container">
            <div class="section-head">
                <span class="hero-tag">Inventory</span>
                <h2>Available Residences</h2>
            </div>
            <div class="room-grid">
                <div class="room-card">
                    <div class="room-banner" style="background: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsO0sRUTcFgANaGhbLenEI4lerqO7J9vJN3Q&s') center/cover;">
                        <div class="room-price">₹5,000/mo</div>
                    </div>
                    <div class="room-body">
                        <h3>Premium Single-Bed Room</h3>
                        <div class="room-features">
                            <span><i class="fas fa-snowflake"></i> AC</span>
                            <span><i class="fas fa-bath"></i> Private</span>
                            <span><i class="fas fa-wifi"></i> Fiber</span>
                        </div>
                        <a href="student/register.php" class="btn-apply" style="display: block; text-align: center;">Register Interest</a>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-banner" style="background: url('https://rentok-storage-cdn.azureedge.net/rentok-marketplace/MicrositeImages/bf31e70a-2b7e-4bc1-9adf-1ced71babadb/1707463333905.webp') center/cover;">
                        <div class="room-price">₹3,500/mo</div>
                    </div>
                    <div class="room-body">
                        <h3>Classic Double-Bed Room</h3>
                        <div class="room-features">
                            <span><i class="fas fa-fan"></i> Standard</span>
                            <span><i class="fas fa-bath"></i> Attached</span>
                            <span><i class="fas fa-users"></i> 2 Residents</span>
                        </div>
                        <a href="student/register.php" class="btn-apply" style="display: block; text-align: center;">Register Interest</a>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-banner" style="background: url('https://5.imimg.com/data5/ZJ/BL/VW/SELLER-50725794/hostel-room-set.jpg') center/cover;">
                        <div class="room-price">₹2,500/mo</div>
                    </div>
                    <div class="room-body">
                        <h3>Budget Triple-Bed Room</h3>
                        <div class="room-features">
                            <span><i class="fas fa-wifi"></i> Fiber</span>
                            <span><i class="fas fa-restroom"></i> Shared</span>
                            <span><i class="fas fa-users"></i> 3 Residents</span>
                        </div>
                        <a href="student/register.php" class="btn-apply" style="display: block; text-align: center;">Register Interest</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="facilities" class="facilities">
        <div class="container">
            <h2 class="section-title">Our Facilities</h2>
            <p class="section-subtitle">Everything You Need for Comfortable Living</p>
            <div class="facilities-grid">
                <div class="facility-card">
                    <i class="fas fa-wifi"></i>
                    <h3>High-Speed WiFi</h3>
                    <p>24/7 high-speed internet connectivity in all rooms and common areas for seamless online learning</p>
                </div>
                <div class="facility-card">
                    <i class="fas fa-utensils"></i>
                    <h3>Mess Facility</h3>
                    <p>Hygienic and nutritious meals with varied menu prepared by experienced cooks</p>
                </div>
                <div class="facility-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>24/7 Security</h3>
                    <p>Round-the-clock security with CCTV surveillance and trained personnel</p>
                </div>
                <div class="facility-card">
                    <i class="fas fa-book-reader"></i>
                    <h3>Study Rooms</h3>
                    <p>Quiet study areas and library facilities for focused academic work</p>
                </div>
                <div class="facility-card">
                    <i class="fas fa-dumbbell"></i>
                    <h3>Gym & Sports</h3>
                    <p>Well-equipped gym and sports facilities for physical fitness</p>
                </div>
                <div class="facility-card">
                    <i class="fas fa-medkit"></i>
                    <h3>Medical Facility</h3>
                    <p>On-campus medical support and first aid available 24/7</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container">
            <div class="contact-wrap">
                <div class="contact-side">
                    <div>
                        <h2>Let's Talk<span>.</span></h2>
                        <p style="opacity: 0.8; margin-top: 10px;">Questions about admissions or tours?</p>
                    </div>
                    <div>
                        <p><i class="fas fa-phone-alt"></i> +91 123 456 7890</p>
                        <p style="margin-top: 15px;"><i class="fas fa-envelope"></i> admin@GUARDIAN HOSTEL.com</p>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-facebook-messenger"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <?php if (isset($success)): ?><div class="alert alert-success" style="margin-bottom: 2rem;"><?php echo $success; ?></div><?php endif; ?>
                    <?php if (isset($error)): ?><div class="alert alert-danger" style="margin-bottom: 2rem;"><?php echo $error; ?></div><?php endif; ?>

                    <form method="POST">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="John Doe" required>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div><label>Email</label><input type="email" name="email" placeholder="john@email.com" required></div>
                            <div><label>Mobile</label><input type="tel" name="phone" placeholder="+91"></div>
                        </div>
                        <label>Inquiry Details</label>
                        <textarea name="message" rows="4" placeholder="How can we help?" required></textarea>
                        <button type="submit" name="contact_submit" class="btn-apply" style="width: 100%; border: none; cursor: pointer;">SUBMIT MESSAGE</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h2 style="color: white; margin-bottom: 1.5rem;">GUARDIAN HOSTEL<span>.</span></h2>
                    <p>Elevating the student experience with secure, tech-enabled residency solutions since 2011.</p>
                </div>
                <div>
                    <h4 style="color: white; margin-bottom: 1.5rem;">Links</h4>
                    <ul style="list-style: none; color: var(--text-dim);">
                        <li style="margin-bottom: 10px;"><a href="#about" style="color: inherit;">The Vision</a></li>
                        <li style="margin-bottom: 10px;"><a href="#rooms" style="color: inherit;">Residences</a></li>
                        <li style="margin-bottom: 10px;"><a href="admin/login.php" style="color: inherit;">Admin Console</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: white; margin-bottom: 1.5rem;">Support</h4>
                    <ul style="list-style: none; color: var(--text-dim);">
                        <li style="margin-bottom: 10px;">Privacy Rights</li>
                        <li style="margin-bottom: 10px;">Terms of Residency</li>
                        <li style="margin-bottom: 10px;">Help Center</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: white; margin-bottom: 1.5rem;">Location</h4>
                    <div style="border-radius: 15px; overflow: hidden; height: 130px; border: 1px solid var(--border);">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d230153.45256272587!2d84.98291794999999!3d25.594095499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f29937c52d5b7d%3A0x831a0e05f607b270!2sPatna%2C%20Bihar!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" loading="lazy"></iframe>
                    </div>
                    <a href="admin/login.php">
                        <div
                            style="
                                margin-top: 2em;
                                text-align: center;
                                padding: 0.5rem;
                                border-radius: 2em;
                                background: var(--primary);
                                font-weight: 700;
                                transition: background 0.3s ease, transform 0.2s ease;
                                cursor: pointer;
                                text-decoration: none;
                                color: white;
                            ">
                            Admin
                        </div>
                    </a>
                </div>
            </div>
            <div style="text-align: center; padding-top: 4rem; color: var(--text-dim); font-size: 0.8rem;">
                &copy; 2026 GUARDIAN HOSTEL Hostel Management System. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('navToggle').addEventListener('click', function() {
            const menu = document.getElementById('navMenu');
            menu.classList.toggle('active');
            this.innerHTML = menu.classList.contains('active') ?
                '<i class="fas fa-times"></i>' :
                '<i class="fas fa-bars"></i>';
        });
        // Smooth scroll for anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>