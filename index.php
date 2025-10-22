<?php
// Landing page for WIET College Library Management System
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WIET College Library - Digital Library Management System</title>
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
        scroll-padding-top: 70px; /* Offset for sticky navbar */
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
        /* Thin gold line at bottom for visual accent */
        border-bottom: 3px solid var(--yellow);
    }

    .navbar.scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        /* Keep the gold bottom line when scrolled */
        border-bottom: 3px solid var(--yellow);
    }

    .navbar .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 10px;
        /* reduced padding for more left alignment */
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 50px;
    }

    /* Layout helpers for left/right nav grouping */
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

    .navbar-brand {
        font-size: 24px;
        font-weight: 700;
        color: var(--blue);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .navbar-brand:hover {
        color: var(--yellow);
    }

    .navbar-nav {
        display: flex;
        list-style: none;
        gap: 40px;
        align-items: center;
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

    .nav-link:hover::after {
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

    /* Banner Section */
    .banner {
        height: 80vh;
        background: linear-gradient(rgba(38, 60, 121, 0.7), rgba(207, 172, 105, 0.7)), url('./images/Watumull Building.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
    }

    /* About Hero (image with overlay title)*/
    .about-hero {
        max-width: 1200px;
        margin: 20px auto;
        /* spacing between banner and this image */
        height: 220px;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        background: url('./images/interior2.jpg');
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 25px var(--shadow);
    }

    .about-hero .overlay-title {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: var(--white);
        font-size: 3rem;
        font-weight: 800;
        text-align: center;
        z-index: 2;
        line-height: 1.05;
        text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.6);
    }

    @media (max-width: 768px) {
        .about-hero {
            height: 140px;
            margin: 16px 15px;
        }

        .about-hero .overlay-title {
            font-size: 1.6rem;
            padding: 0 8px;
        }
    }

    .banner-content {
        color: var(--white);
        z-index: 2;
    }

    .banner h1 {
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 20px;
        /* text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5); */
        animation: fadeInUp 1s ease;
    }

    .banner p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        opacity: 0.9;
        animation: fadeInUp 1s ease 0.3s both;
    }

    .banner-cta {
        display: inline-block;
        background: var(--yellow);
        color: var(--white);
        padding: 15px 35px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        animation: fadeInUp 1s ease 0.6s both;
        box-shadow: 0 5px 20px rgba(207, 172, 105, 0.4);
    }

    .banner-cta:hover {
        background: var(--white);
        color: var(--blue);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
    }

    /* Section Styles */
        .section {
            padding: 80px 0;
            scroll-margin-top: 70px; /* Offset for sticky navbar + some extra space */
        }

        /* Mobile Section Adjustments */
        @media (max-width: 768px) {
            .section {
                padding: 60px 0;
            }

            .section-header {
                margin-bottom: 40px;
            }

            .facilities-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .facility-card {
                margin-bottom: 20px;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .about-content {
                flex-direction: column;
                text-align: center;
            }

            .about-text {
                margin-bottom: 30px;
            }

            .about-image {
                margin-bottom: 20px;
            }
        }    .container {
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

    /* About Section */
    .about {
        background: var(--ivory);
    }

    .about-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .about-image {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 15px 35px var(--shadow);
        height: 400px;
    }

    .about-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .about-image:hover img {
        transform: scale(1.05);
    }

    .about-text h3 {
        font-size: 2rem;
        color: var(--blue);
        margin-bottom: 20px;
        font-weight: 600;
    }

    .about-text p {
        color: var(--dark-gray);
        margin-bottom: 20px;
        font-size: 1rem;
        line-height: 1.8;
    }

    .about-stats {
        display: flex;
        gap: 30px;
        margin-top: 30px;
    }

    .stat-item {
        text-align: center;
        flex: 1;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--yellow);
        display: block;
    }

    .stat-label {
        color: var(--dark-gray);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 5px;
    }

    /* Facilities Section */
    .facilities {
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
        border-radius: 15px;
        box-shadow: 0 15px 35px var(--shadow);
        height: 350px;
    }

    .facility-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .facility-image:hover img {
        transform: scale(1.05);
    }

    .facility-text h4 {
        font-size: 1.8rem;
        color: var(--blue);
        margin-bottom: 20px;
        font-weight: 600;
    }

    .facility-text p {
        color: var(--dark-gray);
        line-height: 1.8;
        margin-bottom: 25px;
    }

    .facility-features {
        list-style: none;
    }

    .facility-features li {
        padding: 8px 0;
        color: var(--dark-gray);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .facility-features li::before {
        content: '✓';
        color: var(--yellow);
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Administration Section */
    .administration {
        background: var(--ivory);
    }

    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-top: 50px;
    }

    .staff-card {
        background: var(--white);
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 10px 30px var(--shadow);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .staff-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--yellow), var(--blue));
    }

    .staff-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px var(--shadow);
    }

    .staff-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 20px;
        overflow: hidden;
        border: 4px solid var(--yellow);
        box-shadow: 0 5px 15px var(--shadow);
    }

    .staff-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .staff-name {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--blue);
        margin-bottom: 8px;
    }

    .staff-role {
        color: var(--yellow);
        font-weight: 500;
        margin-bottom: 15px;
    }

    .staff-description {
        color: var(--dark-gray);
        font-size: 0.9rem;
        line-height: 1.6;
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
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {

        .about-content,
        .facility-item {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .facility-item:nth-child(even) .facility-image,
        .facility-item:nth-child(even) .facility-text {
            order: initial;
        }

        .about-stats {
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .navbar .container {
            padding: 0 15px;
            height: 60px;
        }

        .navbar {
            position: sticky;
            top: 0;
        }

        .navbar-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            flex-direction: column;
            padding: 15px;
            box-shadow: 0 5px 15px var(--shadow);
            gap: 15px;
        }

        .navbar-nav.active {
            display: flex;
        }

        .mobile-menu-toggle {
            display: block;
        }

        /* Hide left links on mobile until toggle is pressed */
        .nav-left {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            flex-direction: column;
            padding: 15px;
            gap: 10px;
            box-shadow: 0 5px 15px var(--shadow);
            z-index: 1001;
            border-radius: 0 0 10px 10px;
        }

        .nav-left.active {
            display: flex;
        }

        .banner h1 {
            font-size: 2.5rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .section {
            padding: 60px 0;
        }

        .staff-grid {
            grid-template-columns: 1fr;
        }

        .footer-content {
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .map-container {
            height: 120px;
        }

        .college-header {
            height: 80px;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 15px;
        }

        .banner {
            min-height: 60vh;
            padding: 40px 0;
        }

        .banner h1 {
            font-size: 2rem;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .banner p {
            font-size: 1rem;
            margin-bottom: 25px;
        }

        .banner-cta {
            padding: 12px 25px;
            font-size: 1rem;
        }

        .section {
            padding: 50px 0;
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .section-subtitle {
            font-size: 1rem;
            line-height: 1.5;
        }

        .navbar-brand {
            font-size: 18px;
        }

        .college-header {
            height: 60px;
        }

        .about-stats {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .stat-item {
            padding: 15px;
        }

        .stat-number {
            font-size: 1.8rem;
        }

        .staff-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .staff-card {
            text-align: center;
            padding: 20px 15px;
        }

        .footer {
            padding: 40px 0;
        }

        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .footer-section {
            margin-bottom: 20px;
        }
    }

    /* Extra Small Devices */
    @media (max-width: 360px) {
        .container {
            padding: 0 10px;
        }

        .banner h1 {
            font-size: 1.6rem;
        }

        .banner p {
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 1.5rem;
        }

        .navbar .container {
            padding: 0 10px;
        }

        .banner-cta {
            padding: 10px 20px;
            font-size: 0.9rem;
        }

        .college-header {
            height: 70px;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--light-gray);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--yellow);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--blue);
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
                <a href="#about" class="nav-link">About</a>
                <a href="#facilities" class="nav-link">Facilities</a>
                <a href="#contact" class="nav-link">Contact Us</a>
                <a href="opac.php" class="nav-link">OPAC Search</a>
            </div>

            <div class="nav-right">
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="student/student_login.php" class="nav-link nav-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Banner Section -->
    <section class="banner">
        <div class="banner-content">
            <h1>LIBRARY</h1>
            <p>Your Gateway to Knowledge and Academic Excellence</p>
            <a href="#about" class="banner-cta">Explore Our Resources</a>
        </div>
    </section>

    <!-- About Section -->
    <!-- <section id="about-section" class="section about">
        <div class="container">
            <div class="section-header fade-in">
                <!-- About Hero (image with overlay) -->
    <!-- <div id="about" class="about-hero" role="img" aria-label="About hero image">
                    <div class="overlay-title">About Our Library</div>

                </div>
                <p class="section-subtitle">
                    Discover a world-class library facility designed to support your academic journey
                    with extensive resources, modern technology, and dedicated services.
                </p>
            </div> -->

    <!-- About Section -->
    <section id="about" class="section about">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">About Our Library</h2>
                <p class="section-subtitle">
                    Discover a world-class library facility designed to support your academic journey
                    with extensive resources, modern technology, and dedicated services.
                </p>
            </div>

            <div class="about-content">
                <div class="about-image fade-in">
                    <img src="./images/entry2.jpg" alt="WIET College Library Interior">
                </div>
                <div class="about-text fade-in">
                    <h3>Welcome to WIET College Library</h3>
                    <p>
                        Our state-of-the-art library serves as the intellectual heart of WIET College,
                        providing students, faculty, and researchers with access to an extensive collection
                        of books, journals, digital resources, and cutting-edge technology.
                    </p>
                    <p>
                        With over 15,000 volumes, spacious study spaces, and advanced digital infrastructure,
                        we are committed to supporting academic excellence and fostering a culture of
                        lifelong learning within our community.
                    </p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <span class="stat-number">15,000+</span>
                            <span class="stat-label">Books</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">1,500+</span>
                            <span class="stat-label">Members</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Study Seats</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section id="facilities" class="section facilities">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Facilities</h2>
                <p class="section-subtitle">
                    Experience modern library amenities designed to enhance your learning
                    and research capabilities in a comfortable environment.
                </p>
            </div>

            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/brown.jpeg" alt="Digital Learning Center">
                </div>
                <div class="facility-text">
                    <h4>Digital Learning Center</h4>
                    <p>
                        Our cutting-edge digital learning center features high-speed internet,
                        multimedia workstations, and access to online databases and e-resources.
                        Students can access digital books, research papers, and educational content
                        from leading publishers worldwide.
                    </p>
                    <ul class="facility-features">
                        <li>High-speed Wi-Fi throughout the library</li>
                        <li>Access to premium online databases</li>
                        <li>Digital book collection and e-journals</li>
                        <li>Multimedia presentation facilities</li>
                    </ul>
                </div>
            </div>

            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/bench2.jpg" alt="Study Spaces">
                </div>
                <div class="facility-text">
                    <h4>Spacious Study Area</h4>
                    <p>
                        Thoughtfully designed study areas cater to different learning preferences,
                        from quiet individual study zones to collaborative group spaces. Our ergonomic
                        furniture and optimal lighting create the perfect environment for focused learning.
                    </p>
                    <ul class="facility-features">
                        <li>Silent study zones for focused research</li>
                        <li>Group discussion rooms</li>
                        <li>Comfortable seating with power outlets</li>
                        <li>Climate-controlled environment</li>
                    </ul>
                </div>
            </div>

            <div class="facility-item fade-in">
                <div class="facility-image">
                    <img src="./images/journal.jpeg" alt="Research Resources">
                </div>
                <div class="facility-text">
                    <h4>Comprehensive Research Resources</h4>
                    <p>
                        Our extensive collection spans multiple disciplines with the latest editions
                        of textbooks, reference materials, journals, and research publications.
                        We continuously update our collection to ensure access to current knowledge.
                    </p>
                    <ul class="facility-features">
                        <li>Latest textbooks and reference materials</li>
                        <li>Academic journals and research papers</li>
                        <li>Thesis and dissertation collection</li>
                        <li>Specialized subject databases</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Administration Section -->
    <section class="section administration">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Administration / Staff</h2>
                <p class="section-subtitle">
                    Meet our dedicated team of library professionals committed to providing
                    exceptional service and support to our academic community.
                </p>
            </div>

            <div class="staff-grid">
                <div class="staff-card fade-in">
                    <div class="staff-image">
                        <img src="./images/placeholder.jpg" alt="Chief Librarian">
                    </div>
                    <h4 class="staff-name">Dr. Rajesh Kumar</h4>
                    <p class="staff-role">Chief Librarian</p>
                    <p class="staff-description">
                        Leading our library with over 15 years of experience in academic library management,
                        digital resource development, and information systems.
                    </p>
                </div>

                <div class="staff-card fade-in">
                    <div class="staff-image">
                        <img src="./images/placeholder.jpg" alt="Assistant Librarian">
                    </div>
                    <h4 class="staff-name">Ms. Priya Patel</h4>
                    <p class="staff-role">Assistant Librarian</p>
                    <p class="staff-description">
                        Specializing in circulation services, member support, and digital cataloging
                        to ensure seamless access to library resources.
                    </p>
                </div>

                <div class="staff-card fade-in">
                    <div class="staff-image">
                        <img src="./images/placeholder.jpg" alt="Systems Administrator">
                    </div>
                    <h4 class="staff-name">Mr. Amit Sharma</h4>
                    <p class="staff-role">Systems Administrator</p>
                    <p class="staff-description">
                        Managing our digital infrastructure, database systems, and ensuring
                        optimal performance of our library management system.
                    </p>
                </div>
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
                <p>&copy; 2025 WIET College Library.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle: show/hide left links on small screens
        function toggleMobileMenu() {
            const navLeft = document.querySelector('.nav-left');
            if (navLeft) navLeft.classList.toggle('active');
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 53; // Just enough for navbar height (50px) + minimal buffer
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    const navLeft = document.querySelector('.nav-left');
                    if (navLeft) navLeft.classList.remove('active');
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all fade-in elements
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach(el => observer.observe(el));

            // Animate statistics numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            const animateNumbers = (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const finalNumber = parseInt(target.textContent.replace(/[^0-9]/g, ''));
                        let current = 0;
                        const increment = finalNumber / 50;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= finalNumber) {
                                target.textContent = target.textContent.replace(/[0-9,]+/, finalNumber.toLocaleString());
                                clearInterval(timer);
                            } else {
                                target.textContent = target.textContent.replace(/[0-9,]+/, Math.floor(current).toLocaleString());
                            }
                        }, 30);
                        numberObserver.unobserve(target);
                    }
                });
            };

            const numberObserver = new IntersectionObserver(animateNumbers, observerOptions);
            statNumbers.forEach(stat => numberObserver.observe(stat));
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const navbar = document.querySelector('.navbar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const navLeft = document.querySelector('.nav-left');

            if (!navbar.contains(e.target) && navLeft) {
                navLeft.classList.remove('active');
            }
        });
    </script>
</body>

</html>