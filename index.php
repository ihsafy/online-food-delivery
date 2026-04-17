<?php
/**
 * ============================================
 * INDEX PAGE (Landing Page)
 * Online Food Delivery System
 * ============================================
 * This is the first page users see when they
 * visit the site. It serves as the homepage
 * and entry point to the application.
 * 
 * - If logged in → redirect to dashboard
 * - If not logged in → show landing page
 * ============================================
 */

// Start session
session_start();

// If already logged in, redirect to role-based dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'customer':
            header("Location: /online-food-delivery/customer/dashboard.php");
            break;
        case 'rider':
            header("Location: /online-food-delivery/rider/dashboard.php");
            break;
        case 'owner':
            header("Location: /online-food-delivery/admin/dashboard.php");
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodExpress - Online Food Delivery</title>
    <link rel="stylesheet" href="/online-food-delivery/assets/css/style.css">
    <link rel="stylesheet" href="/online-food-delivery/assets/css/auth.css">

    <style>
        /* ====== Landing Page Specific Styles ====== */

        .landing-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b, #2d3436, #e74c3c);
            display: flex;
            flex-direction: column;
        }

        /* ---- Landing Navbar ---- */
        .landing-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            animation: slideDown 0.5s ease-out;
        }

        .landing-nav .brand {
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
        }

        .landing-nav .brand span {
            color: #ffeaa7;
        }

        .landing-nav .nav-btns {
            display: flex;
            gap: 12px;
        }

        .landing-nav .nav-btns a {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-login {
            border: 2px solid #fff;
            color: #fff;
            background: transparent;
        }

        .btn-login:hover {
            background: #fff;
            color: #e74c3c;
            transform: translateY(-2px);
        }

        .btn-register {
            background: #e74c3c;
            color: #fff;
            border: 2px solid #e74c3c;
        }

        .btn-register:hover {
            background: #c0392b;
            border-color: #c0392b;
            transform: translateY(-2px);
        }

        /* ---- Hero Section ---- */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .hero .hero-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            display: block;
            animation: heroFloat 3s ease-in-out infinite;
        }

        @keyframes heroFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .hero h1 {
            font-size: 3rem;
            color: #fff;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .hero h1 span {
            color: #ffeaa7;
        }

        .hero p {
            font-size: 1.15rem;
            color: #dfe6e9;
            max-width: 600px;
            margin-bottom: 35px;
            line-height: 1.7;
        }

        .hero-btns {
            display: flex;
            gap: 15px;
        }

        .hero-btns a {
            padding: 14px 35px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .hero-btn-primary {
            background: #e74c3c;
            color: #fff;
            border: 2px solid #e74c3c;
        }

        .hero-btn-primary:hover {
            background: #c0392b;
            border-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .hero-btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
        }

        .hero-btn-secondary:hover {
            background: #fff;
            color: #2d3436;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }

        /* ---- Features Section ---- */
        .features {
            padding: 60px 40px;
            background: #f8f9fa;
        }

        .features h2 {
            text-align: center;
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .features h2 span {
            color: #e74c3c;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .feature-card {
            background: #fff;
            border-radius: 14px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            animation: fadeIn 0.7s ease-out;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .feature-card .feature-icon {
            font-size: 2.8rem;
            margin-bottom: 15px;
            display: block;
        }

        .feature-card h3 {
            font-size: 1.15rem;
            color: #2d3436;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .feature-card p {
            font-size: 0.9rem;
            color: #636e72;
            line-height: 1.6;
        }

        /* ---- How It Works Section ---- */
        .how-it-works {
            padding: 60px 40px;
            background: #fff;
        }

        .how-it-works h2 {
            text-align: center;
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .how-it-works h2 span {
            color: #e74c3c;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .step-card {
            text-align: center;
            padding: 25px 20px;
            position: relative;
            animation: fadeIn 0.7s ease-out;
        }

        .step-card .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .step-card .step-icon {
            font-size: 2.2rem;
            margin-bottom: 10px;
            display: block;
        }

        .step-card h3 {
            font-size: 1.05rem;
            color: #2d3436;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .step-card p {
            font-size: 0.85rem;
            color: #636e72;
            line-height: 1.5;
        }

        /* ---- CTA Section ---- */
        .cta-section {
            padding: 60px 40px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .cta-section p {
            font-size: 1.05rem;
            color: #ffeef0;
            margin-bottom: 25px;
        }

        .cta-section a {
            display: inline-block;
            padding: 14px 40px;
            background: #fff;
            color: #e74c3c;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .cta-section a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* ---- Landing Footer ---- */
        .landing-footer {
            background: #1e293b;
            color: #94a3b8;
            text-align: center;
            padding: 25px;
            font-size: 0.85rem;
        }

        .landing-footer span {
            color: #e74c3c;
            font-weight: 600;
        }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-btns {
                flex-direction: column;
                gap: 10px;
            }

            .landing-nav {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
            }

            .features, .how-it-works, .cta-section {
                padding: 40px 20px;
            }

            .features h2, .how-it-works h2, .cta-section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="landing-page">

    <!-- ====== Landing Navigation ====== -->
    <nav class="landing-nav">
        <div class="brand">
            &#127829; Food<span>Express</span>
        </div>
        <div class="nav-btns">
            <a href="/online-food-delivery/auth/login.php" class="btn-login">&#128275; Login</a>
            <a href="/online-food-delivery/auth/register.php" class="btn-register">&#128640; Register</a>
        </div>
    </nav>

    <!-- ====== Hero Section ====== -->
    <section class="hero">
        <span class="hero-icon">&#127829;</span>
        <h1>Delicious Food,<br><span>Delivered Fast</span></h1>
        <p>
            Order your favorite meals from the best restaurants in town. 
            Fast delivery, easy payment, and real-time order tracking — 
            all in one place.
        </p>
        <div class="hero-btns">
            <a href="/online-food-delivery/auth/register.php" class="hero-btn-primary">
                &#128722; Order Now
            </a>
            <a href="/online-food-delivery/auth/login.php" class="hero-btn-secondary">
                &#128275; Sign In
            </a>
        </div>
    </section>
</div>

<!-- ====== Features Section ====== -->
<section class="features fade-in">
    <h2>Why Choose <span>FoodExpress</span>?</h2>
    <div class="features-grid">

        <div class="feature-card">
            <span class="feature-icon">&#127860;</span>
            <h3>Wide Menu Selection</h3>
            <p>Browse through a variety of delicious dishes from burgers to desserts. Something for everyone!</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">&#128666;</span>
            <h3>Fast Delivery</h3>
            <p>Our dedicated riders ensure your food arrives hot and fresh at your doorstep in no time.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">&#128179;</span>
            <h3>Easy Payment</h3>
            <p>Pay with Visa Card, bKash, or Nagad. Multiple payment options for your convenience.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">&#128172;</span>
            <h3>Live Chat</h3>
            <p>Chat directly with your delivery rider to coordinate your order delivery in real-time.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">&#128230;</span>
            <h3>Order Tracking</h3>
            <p>Track your order status from preparation to delivery. Know exactly where your food is.</p>
        </div>

        <div class="feature-card">
            <span class="feature-icon">&#128274;</span>
            <h3>Secure & Safe</h3>
            <p>Your data is protected with secure authentication and encrypted password storage.</p>
        </div>

    </div>
</section>

<!-- ====== How It Works Section ====== -->
<section class="how-it-works fade-in">
    <h2>How It <span>Works</span></h2>
    <div class="steps-grid">

        <div class="step-card">
            <div class="step-number">1</div>
            <span class="step-icon">&#128221;</span>
            <h3>Create Account</h3>
            <p>Sign up for free in just a few seconds with your email.</p>
        </div>

        <div class="step-card">
            <div class="step-number">2</div>
            <span class="step-icon">&#128213;</span>
            <h3>Browse Menu</h3>
            <p>Explore our wide selection of delicious food items.</p>
        </div>

        <div class="step-card">
            <div class="step-number">3</div>
            <span class="step-icon">&#128722;</span>
            <h3>Add to Cart</h3>
            <p>Select your favorite items and add them to your cart.</p>
        </div>

        <div class="step-card">
            <div class="step-number">4</div>
            <span class="step-icon">&#128179;</span>
            <h3>Pay & Enjoy</h3>
            <p>Complete payment and wait for your food to arrive!</p>
        </div>

    </div>
</section>

<!-- ====== CTA Section ====== -->
<section class="cta-section fade-in">
    <h2>&#127881; Ready to Order?</h2>
    <p>Join thousands of happy customers. Sign up now and get your first meal delivered!</p>
    <a href="/online-food-delivery/auth/register.php">
        &#128640; Get Started Free
    </a>
</section>

<!-- ====== Landing Footer ====== -->
<footer class="landing-footer">
    <p>
        &#127829; <span>FoodExpress</span> &mdash; Online Food Delivery System
    </p>
    <p>
        &copy; <?php echo date("Y"); ?> All Rights Reserved. 
        Built with &#10084; using PHP &amp; MySQL
    </p>
</footer>

</body>
</html>
