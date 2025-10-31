<?php
// Developer page for WIET College Library Management System
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Developers | WIET Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-padding-top: 70px;
        scroll-behavior: smooth;
    }

    :root {
        --ivory: #f3ebdc;
        --yellow: #cfac69;
        --blue: #263c79;
        --white: #ffffff;
        --light-gray: #f8f9fa;
        --dark-gray: #6c757d;
        --shadow: rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: "Inter", sans-serif;
        line-height: 1.6;
        color: #333;
        overflow-x: hidden;
    }

    /* Header Styles */
    .college-header {
        width: 100%;
        height: 120px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 2px 10px var(--shadow);
    }

    .college-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* Navigation Styles */
    .navbar {
        background: var(--white);
        box-shadow: 0 2px 15px var(--shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
        border-bottom: 3px solid var(--yellow);
    }

    .navbar.scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 3px solid var(--yellow);
    }

    .navbar .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 50px;
    }

    .nav-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-link {
        color: var(--blue);
        text-decoration: none;
        font-weight: 500;
        font-size: 16px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link:hover {
        color: var(--yellow);
    }

    .nav-link.active {
        color: var(--yellow);
        font-weight: 600;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -5px;
        left: 50%;
        background: var(--yellow);
        transition: all 0.3s ease;
    }

    .nav-link:hover::after,
    .nav-link.active::after {
        width: 100%;
        left: 0;
    }

    .nav-login {
        background: var(--yellow);
        color: var(--white);
        padding: 6px 24px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(207, 172, 105, 0.3);
    }

    .nav-login:hover {
        background: var(--blue);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(38, 60, 121, 0.3);
    }

    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--blue);
        font-size: 24px;
        cursor: pointer;
    }

    /* Section Styles */
    .section {
        padding: 40px 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 10px;
    }

    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--blue);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 4px;
        background: var(--yellow);
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: var(--dark-gray);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.8;
    }

    /* Developers Section - Using Facility Item Structure */
    .developers {
        background: var(--white);
    }

    .facility-item {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        margin-bottom: 80px;
    }

    .facility-item:nth-child(even) .facility-image {
        order: 2;
    }

    .facility-item:nth-child(even) .facility-text {
        order: 1;
    }

    .facility-image {
        position: relative;
        overflow: hidden;
        border-radius: 50%;
        box-shadow: 0 15px 35px var(--shadow);
        width: 350px;
        height: 350px;
        margin: 0 auto;
        border: 5px solid var(--yellow);
    }

    .facility-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        transition: transform 0.5s ease;
    }

    .facility-image:hover img {
        transform: scale(1.1);
    }

    /* Placeholder for missing images */
    .facility-image.placeholder {
        background: linear-gradient(135deg, var(--blue), var(--yellow));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .facility-image.placeholder span {
        color: var(--white);
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
        padding: 20px;
    }

    .facility-text h4 {
        font-size: 1.8rem;
        color: var(--blue);
        margin-bottom: 10px;
        font-weight: 600;
    }

    .facility-text .developer-role {
        font-size: 1.1rem;
        color: var(--yellow);
        font-style: italic;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .facility-text p {
        color: var(--dark-gray);
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .linkedin-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--blue);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: 15px;
    }

    .linkedin-link:hover {
        color: var(--yellow);
        transform: translateX(5px);
    }

    .linkedin-link i {
        font-size: 1.2rem;
    }

    /* Team Note */
    .team-note {
        text-align: center;
        font-size: 1.15rem;
        color: var(--blue);
        max-width: 800px;
        margin: 60px auto 40px;
        padding: 30px;
        background: var(--ivory);
        border-radius: 10px;
        border-left: 5px solid var(--yellow);
        font-style: italic;
        line-height: 1.8;
        font-weight: 500;
    }

    /* Footer Section */
    .footer {
        background: var(--blue);
        color: var(--white);
        padding: 40px 0 20px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1.2fr;
        gap: 30px;
        margin-bottom: 25px;
        align-items: start;
    }

    .footer-column h3 {
        color: var(--white);
        font-size: 1.1rem;
        margin-bottom: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
    }

    .footer-column li {
        margin-bottom: 8px;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.4;
    }

    .contact-item {
        margin-bottom: 8px;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.4;
    }

    .contact-icon {
        display: none;
    }

    .contact-details {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
    }

    .contact-details strong {
        color: var(--white);
        display: block;
        margin-bottom: 5px;
    }

    .map-container {
        background: var(--light-gray);
        height: 160px;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 30px;
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        opacity: 0;
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in.visible {
        opacity: 1;
        animation: fadeInUp 0.8s ease;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .facility-item {
            gap: 40px;
        }

        .facility-image {
            width: 300px;
            height: 300px;
        }

        .section-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .nav-left {
            position: fixed;
            top: 50px;
            left: -100%;
            width: 100%;
            background: var(--white);
            flex-direction: column;
            padding: 20px;
            box-shadow: 0 5px 15px var(--shadow);
            transition: left 0.3s ease;
            gap: 15px;
            z-index: 999;
        }

        .nav-left.active {
            left: 0;
        }

        .mobile-menu-toggle {
            display: block;
        }

        .section {
            padding: 60px 0;
        }

        .facility-item {
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }

        .facility-item:nth-child(even) .facility-image,
        .facility-item:nth-child(even) .facility-text {
            order: unset;
        }

        .facility-image {
            width: 250px;
            height: 250px;
        }

        .facility-text h4 {
            font-size: 1.5rem;
        }

        .section-title {
            font-size: 1.8rem;
        }

        .section-subtitle {
            font-size: 1rem;
        }

        .team-note {
            font-size: 1rem;
            padding: 25px;
        }

        .footer-content {
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .map-container {
            height: 120px;
        }
    }

    @media (max-width: 480px) {
        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .footer-section {
            margin-bottom: 20px;
        }

        .college-header {
            height: 80px;
        }

        .section {
            padding: 40px 0;
        }

        .facility-image {
            width: 200px;
            height: 200px;
        }

        .section-title {
            font-size: 1.5rem;
        }

        .facility-text h4 {
            font-size: 1.3rem;
        }

        .team-note {
            font-size: 0.95rem;
            padding: 20px;
        }
    }
</style>

<body>
    <!-- College Header -->
    <header class="college-header">
        <img src="./images/Colg_banner.png" alt="WIET College Header Banner">
    </header>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="index.php#about" class="nav-link">About</a>
                <a href="index.php#facilities" class="nav-link">Facilities</a>
                <a href="index.php#contact" class="nav-link">Contact Us</a>
                <a href="../opac.php" class="nav-link">OPAC Search</a>
            </div>

            <div class="nav-right">
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="../student_login.php" class="nav-link nav-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Developers Section -->
    <section id="developers" class="section developers">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Meet the Developers</h2>
                <p class="section-subtitle">
                    The team behind the WIET Library Management System project.
                </p>
            </div>

            <!-- Developer 1: Aditi Godse -->
            <div class="facility-item fade-in">
                <div class="facility-image placeholder">
                    <img src="./images/Aditi.jpg" alt="Aditi Godse">
                </div>
                <div class="facility-text">
                    <h4>Aditi Godse</h4>
                    <p class="developer-role">Frontend Development & Design</p>
                    <p>
                        Developed and styled the user interface, ensuring responsive layouts
                        and visual consistency across all pages.
                    </p>
                    <a href="https://www.linkedin.com/in/aditi-godse-5a308928b/" target="_blank" class="linkedin-link">
                        <i class="fab fa-linkedin"></i> Connect on LinkedIn
                    </a>
                </div>
            </div>

            <!-- Developer 2: Esha Gond -->
            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/Esha.jpg" alt="Esha Gond">
                </div>
                <div class="facility-text">
                    <h4>Esha Gond</h4>
                    <p class="developer-role">Frontend Development & Design</p>
                    <p>
                        Designed and implemented the full frontend interface with a focus on
                        smooth navigation, accessibility, and balanced visual design.
                    </p>
                    <a href="https://www.linkedin.com/in/eshagond17/" target="_blank" class="linkedin-link">
                        <i class="fab fa-linkedin"></i> Connect on LinkedIn
                    </a>
                </div>
            </div>

            <!-- Developer 3: Aditya Jadhav -->
            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/Aditya.jpg" alt="Aditya Jadhav" style="object-position: center 20%;">
                </div>
                <div class="facility-text">
                    <h4>Aditya Jadhav</h4>
                    <p class="developer-role">Testing & Documentation</p>
                    <p>
                        Handled project testing and documentation, ensuring all modules functioned
                        smoothly and system workflows were clearly documented.
                    </p>
                    <a href="https://www.linkedin.com/in/aditya-jadhav-404109275" target="_blank" class="linkedin-link">
                        <i class="fab fa-linkedin"></i> Connect on LinkedIn
                    </a>
                </div>
            </div>

            <!-- Developer 4: Rudra Malvankar -->
            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/Rudra.jpg" alt="Rudra Malvankar" style="object-position: center 20%;">
                </div>
                <div class="facility-text">
                    <h4>Rudra Malvankar</h4>
                    <p class="developer-role">Backend Development & Database Management</p>
                    <p>
                        Built and managed the backend logic, designed and maintained the database
                        schema, and ensured secure, efficient data handling.
                    </p>
                    <a href="https://linkedin.com/in/rudra-malvankar" target="_blank" class="linkedin-link">
                        <i class="fab fa-linkedin"></i> Connect on LinkedIn
                    </a>
                </div>
            </div>

            <!-- Team Note -->
            <div class="team-note fade-in">
                This project was collaboratively planned and developed by all four members
                of the WIET Library Management System team.
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>ADDRESS</h3>
                    <div class="contact-item">
                        Watumull Institute of Engineering & Technology<br>
                        Ulhasnagar, Mumbai<br>
                        Maharashtra - 421003<br>
                        India
                    </div>
                </div>

                <div class="footer-column">
                    <h3>TIMING</h3>
                    <div class="contact-item">
                        Monday - Friday<br>
                        9:00 AM - 6:00 PM<br><br>
                        Saturday<br>
                        9:00 AM - 5:00 PM<br>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>email</h3>
                    <div class="contact-item">
                        library@wiet.edu<br>
                        info@wiet.edu
                    </div>
                </div>

                <div class="footer-column">
                    <h3>phone</h3>
                    <div class="contact-item">
                        +91-22-2493-4787<br>
                        +91-22-2493-8108
                    </div>
                </div>

                <div class="footer-column">
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3767.4242702056495!2d73.16045207466863!3d19.220332747426585!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7ce9b0092e491%3A0x925bbfe8e99a6a63!2sWatumull%20Institute%20Of%20Engineering%20And%20Technology!5e0!3m2!1sen!2sin!4v1758878115946!5m2!1sen!2sin"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 WIET College Library | Developed and Managed by <span style="color: var(--yellow); font-weight: 600;">WIET Library Team</span></p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const navLeft = document.querySelector('.nav-left');
            if (navLeft) navLeft.classList.toggle('active');
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Fade-in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(element => {
            observer.observe(element);
        });
    </script>
</body>

</html>