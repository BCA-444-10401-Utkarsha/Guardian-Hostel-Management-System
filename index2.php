<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($message)) {
        $query = "INSERT INTO contact_messages (name, email, phone, message) VALUES ('$name', '$email', '$phone', '$message')";
        if (mysqli_query($conn, $query)) {
            $success = "Thank you for contacting us! We'll get back to you soon.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "Please fill all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarnath Hostel Management System</title>
    <link rel="stylesheet" href="css/landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-building"></i>
                <span>Sarnath Hostel</span>
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#facilities">Facilities</a></li>
                <li><a href="#rooms">Rooms</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="student/login.php" class="btn-login">Student Login</a>
                <a href="admin/login.php" class="btn-admin">Admin</a>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Sarnath Hostel</h1>
            <p class="hero-tagline">Safe • Comfortable • Affordable Living for Students</p>
            <div class="hero-buttons">
                <a href="student/register.php" class="btn-hero-primary">Apply Now</a>
                <a href="#rooms" class="btn-hero-secondary">View Rooms</a>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About Our Hostel</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Welcome to Sarnath Hostel, your home away from home. Since our establishment, we've been committed to providing a safe, comfortable, and conducive environment for students pursuing their academic dreams.</p>
                    <p>Our hostel features modern amenities, 24/7 security, nutritious meals, and a supportive community that helps students focus on their studies while enjoying their college life to the fullest.</p>
                    <div class="about-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>100+ Comfortable Rooms</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 Security & CCTV</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Nutritious Mess Facility</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>High-Speed WiFi</span>
                        </div>
                    </div>
                </div>
                <div class="about-stats">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>500+</h3>
                        <p>Happy Students</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-door-open"></i>
                        <h3>100+</h3>
                        <p>Quality Rooms</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-award"></i>
                        <h3>15+</h3>
                        <p>Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
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

    <!-- Room Types Section -->
    <section id="rooms" class="rooms">
        <div class="container">
            <h2 class="section-title">Our Rooms & Pricing</h2>
            <p class="section-subtitle">Choose the Perfect Room for Your Needs</p>
            <div class="rooms-grid">
                <div class="room-card">
                    <div class="room-badge">Popular</div>
                    <div class="room-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h3>Single Sharing</h3>
                    <p>Perfect for students who prefer privacy and personal space</p>
                    <div class="room-amenities">
                        <span><i class="fas fa-wifi"></i> WiFi</span>
                        <span><i class="fas fa-snowflake"></i> AC</span>
                        <span><i class="fas fa-bath"></i> Attached Bath</span>
                        <span><i class="fas fa-user"></i> 1 Person</span>
                    </div>
                    <div class="room-footer">
                        <span class="price">₹5,000<small>/month</small></span>
                        <a href="student/register.php" class="btn-book">Apply Now</a>
                    </div>
                </div>
                <div class="room-card featured">
                    <div class="room-badge recommended">Recommended</div>
                    <div class="room-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3>Double Sharing</h3>
                    <p>Great option for making friends and sharing experiences</p>
                    <div class="room-amenities">
                        <span><i class="fas fa-wifi"></i> WiFi</span>
                        <span><i class="fas fa-fan"></i> Fan</span>
                        <span><i class="fas fa-bath"></i> Attached Bath</span>
                        <span><i class="fas fa-users"></i> 2 Persons</span>
                    </div>
                    <div class="room-footer">
                        <span class="price">₹3,500<small>/month</small></span>
                        <a href="student/register.php" class="btn-book">Apply Now</a>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-badge">Budget</div>
                    <div class="room-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Triple Sharing</h3>
                    <p>Most economical option for budget-conscious students</p>
                    <div class="room-amenities">
                        <span><i class="fas fa-wifi"></i> WiFi</span>
                        <span><i class="fas fa-fan"></i> Fan</span>
                        <span><i class="fas fa-restroom"></i> Common Bath</span>
                        <span><i class="fas fa-users"></i> 3 Persons</span>
                    </div>
                    <div class="room-footer">
                        <span class="price">₹2,500<small>/month</small></span>
                        <a href="student/register.php" class="btn-book">Apply Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <p class="section-subtitle">Get In Touch With Us</p>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="contact-content">
                <div class="contact-left">
                    <form class="contact-form" method="POST" onsubmit="return validateContactForm()">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="name" name="name" placeholder="Your Name *" required>
                            </div>
                            <div class="form-group">
                                <input type="email" id="email" name="email" placeholder="Your Email *" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="Your Phone">
                        </div>
                        <br>
                        <div class="form-group">
                            <textarea id="message" name="message" rows="5" placeholder="Your Message *" required></textarea>
                        </div>
                        <br>
                        <button type="submit" name="contact_submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>

                <div class="contact-right">
                    <div class="contact-info-card">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Address</h4>
                                <p>Sarnath Hostel, Campus Road<br>City - 123456</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+91 1234567890</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>hostel@college.edu.in</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-building"></i> Sarnath Hostel</h3>
                    <p>Your home away from home. We provide a safe, comfortable, and supportive environment for students to thrive academically and personally.</p>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://linkedin.com" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section map-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d230153.45256272587!2d84.98291794999999!3d25.594095499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f29937c52d5b7d%3A0x831a0e05f607b270!2sPatna%2C%20Bihar!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin" 
                                width="100%" 
                                height="200" 
                                style="border:0; border-radius: 8px;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Sarnath Hostel Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        .footer-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 3rem 0 2rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #e67e22;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.4);
        }

        .map-section {
            display: flex;
            flex-direction: column;
        }

        .map-section h3 {
            margin-bottom: 1rem;
        }

        .map-container {
            flex: 1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>

    <script src="js/script.js"></script>
</body>
</html>
