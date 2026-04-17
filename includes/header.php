<?php
/**
 * ============================================
 * HEADER INCLUDE FILE
 * Online Food Delivery System
 * ============================================
 * This file is included at the top of every page.
 * It contains the HTML head, CSS links, and navbar.
 * Navigation links change based on user role.
 * ============================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodExpress - Online Food Delivery</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/online-food-delivery/assets/css/style.css">
    <link rel="stylesheet" href="/online-food-delivery/assets/css/auth.css">
    <link rel="stylesheet" href="/online-food-delivery/assets/css/dashboard.css">
</head>
<body>

<?php if ($is_logged_in): ?>
<!-- ============================================
     NAVIGATION BAR (Shown only when logged in)
     ============================================ -->
<nav class="navbar">
    <!-- Brand Name -->
    <div class="brand">
        &#127829; Food<span>Express</span>
    </div>

    <!-- Navigation Links (Role-Based) -->
    <div class="nav-links">

        <?php if ($user_role === 'customer'): ?>
            <!-- ====== CUSTOMER NAV ====== -->
            <a href="/online-food-delivery/customer/dashboard.php"
               class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                &#127968; Dashboard
            </a>
            <a href="/online-food-delivery/customer/menu.php"
               class="<?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>">
                &#128213; Menu
            </a>
            <a href="/online-food-delivery/customer/cart.php"
               class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
                &#128722; Cart
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    (<?php echo count($_SESSION['cart']); ?>)
                <?php endif; ?>
            </a>
            <a href="/online-food-delivery/customer/orders.php"
               class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
                &#128230; My Orders
            </a>

        <?php elseif ($user_role === 'rider'): ?>
            <!-- ====== RIDER NAV ====== -->
            <a href="/online-food-delivery/rider/dashboard.php"
               class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                &#127968; Dashboard
            </a>
            <a href="/online-food-delivery/rider/deliveries.php"
               class="<?php echo ($current_page == 'deliveries.php') ? 'active' : ''; ?>">
                &#128666; Deliveries
            </a>

        <?php elseif ($user_role === 'owner'): ?>
            <!-- ====== ADMIN/OWNER NAV ====== -->
            <a href="/online-food-delivery/admin/dashboard.php"
               class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                &#127968; Dashboard
            </a>
            <a href="/online-food-delivery/admin/manage_food.php"
               class="<?php echo ($current_page == 'manage_food.php') ? 'active' : ''; ?>">
                &#127860; Manage Food
            </a>
            <a href="/online-food-delivery/admin/orders.php"
               class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
                &#128230; Orders
            </a>

        <?php endif; ?>

        <!-- User Name Display -->
        <span style="color: #ffeaa7; font-weight: 600; font-size: 0.9rem;">
            &#128100; <?php echo htmlspecialchars($user_name); ?>
        </span>

        <!-- Logout Button -->
        <a href="/online-food-delivery/logout.php" class="logout-btn">
            &#128682; Logout
        </a>
    </div>
</nav>
<?php endif; ?>

<!-- Main Content Wrapper -->
<main class="fade-in">
