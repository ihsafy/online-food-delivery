<?php
/**
 * ============================================
 * LOGOUT PAGE
 * Online Food Delivery System
 * ============================================
 * This file handles user logout by:
 * 1. Starting the session (if not already started)
 * 2. Unsetting all session variables
 * 3. Destroying the session completely
 * 4. Clearing the session cookie
 * 5. Redirecting to the login page
 * ============================================
 */

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ----------------------------------------
 * STEP 1: Clear the cart (if exists)
 * ----------------------------------------
 * Remove any cart data stored in session
 * before destroying the entire session.
 */
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

/**
 * ----------------------------------------
 * STEP 2: Unset all session variables
 * ----------------------------------------
 * This removes all data stored in $_SESSION
 * including user_id, role, user_name, etc.
 */
$_SESSION = array();

/**
 * ----------------------------------------
 * STEP 3: Delete the session cookie
 * ----------------------------------------
 * If the session uses cookies, we need to
 * delete the session cookie as well to
 * fully clean up the session.
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),    // Cookie name
        '',                // Empty value
        time() - 42000,    // Expired time (in the past)
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/**
 * ----------------------------------------
 * STEP 4: Destroy the session
 * ----------------------------------------
 * This destroys all data associated with
 * the current session on the server side.
 */
session_destroy();

/**
 * ----------------------------------------
 * STEP 5: Redirect to login page
 * ----------------------------------------
 * Send the user back to the login page
 * with a logout success indicator.
 */
header("Location: /online-food-delivery/auth/login.php");
exit();
?>
