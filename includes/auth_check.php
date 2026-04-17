<?php
/**
 * ============================================
 * AUTHENTICATION CHECK FILE
 * Online Food Delivery System
 * ============================================
 * This file is included at the top of every
 * protected page to verify:
 * 1. User is logged in (valid session exists)
 * 2. User has the correct role for the page
 * 
 * Usage in any protected page:
 *   $_SESSION check + role verification
 *   Include this file BEFORE any output.
 * ============================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ----------------------------------------
 * CHECK 1: Is the user logged in?
 * ----------------------------------------
 * If no valid session exists, redirect
 * the user back to the login page.
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Destroy any partial session data
    session_unset();
    session_destroy();

    // Redirect to login page
    header("Location: /online-food-delivery/auth/login.php");
    exit();
}

/**
 * ----------------------------------------
 * CHECK 2: Does the user have the right role?
 * ----------------------------------------
 * Determine the required role based on the
 * folder the current page is in.
 * 
 * Example:
 *   /customer/dashboard.php → requires 'customer'
 *   /rider/deliveries.php   → requires 'rider'
 *   /admin/manage_food.php  → requires 'owner'
 */

// Get the current script path
$current_path = $_SERVER['PHP_SELF'];

// Determine required role based on folder
$required_role = '';

if (strpos($current_path, '/customer/') !== false) {
    $required_role = 'customer';
} elseif (strpos($current_path, '/rider/') !== false) {
    $required_role = 'rider';
} elseif (strpos($current_path, '/admin/') !== false) {
    $required_role = 'owner';
}

/**
 * ----------------------------------------
 * CHECK 3: Role mismatch → Redirect
 * ----------------------------------------
 * If the user's role doesn't match the
 * required role for this page, redirect
 * them to their own dashboard.
 */
if ($required_role !== '' && $_SESSION['role'] !== $required_role) {

    // Redirect user to their correct dashboard
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
        default:
            // Unknown role — send to login
            session_unset();
            session_destroy();
            header("Location: /online-food-delivery/auth/login.php");
            break;
    }
    exit();
}

/**
 * ----------------------------------------
 * SESSION TIMEOUT (Optional Security)
 * ----------------------------------------
 * Auto-logout user after 30 minutes of
 * inactivity for security purposes.
 */
$session_timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];

    if ($elapsed_time > $session_timeout) {
        // Session expired — destroy and redirect
        session_unset();
        session_destroy();
        header("Location: /online-food-delivery/auth/login.php?timeout=1");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
