<?php
/**
 * ============================================
 * USER LOGIN PAGE
 * Online Food Delivery System
 * ============================================
 * Allows existing users to login.
 * Verifies credentials against the database.
 * Redirects to role-based dashboard on success.
 * ============================================
 */

// Start session
session_start();

// If already logged in, redirect to dashboard
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

// Include database connection
include '../config/db.php';

// Initialize variables
$email = '';
$error = '';

// Check for session timeout message
$timeout_msg = '';
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $timeout_msg = "Your session has expired. Please login again.";
}

// Check for registration success message
$register_msg = '';
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $register_msg = "Registration successful! Please login with your credentials.";
}

/**
 * ----------------------------------------
 * HANDLE LOGIN FORM SUBMISSION
 * ----------------------------------------
 * When user submits the form, inputs are
 * verified against the credentials stored
 * in the database. If they match, the user
 * is authorized and granted access.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize form inputs
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);

    // ---- Validation ----

    // Check empty fields
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    else {
        /**
         * Query the database for the user
         * Using prepared statements to prevent SQL injection
         */
        $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            /**
             * Verify the password against the hash
             * password_verify() safely compares the
             * plain text password with the stored hash
             */
            if (password_verify($password, $user['password'])) {

                // ---- Login Successful ----

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Redirect based on role
                switch ($user['role']) {
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
                        header("Location: /online-food-delivery/auth/login.php");
                        break;
                }
                exit();

            } else {
                // Password does not match
                $error = "Incorrect password. Please try again.";
            }
        } else {
            // No user found with that email
            $error = "No account found with this email address.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodExpress</title>
    <link rel="stylesheet" href="/online-food-delivery/assets/css/style.css">
    <link rel="stylesheet" href="/online-food-delivery/assets/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">

        <!-- ====== Auth Header ====== -->
        <div class="auth-header">
            <span class="auth-icon">&#127829;</span>
            <h2>Welcome Back!</h2>
            <p>Login to your FoodExpress account</p>
        </div>

        <!-- ====== Timeout Message ====== -->
        <?php if (!empty($timeout_msg)): ?>
            <div class="auth-alert auth-alert-error">
                &#9200; <?php echo htmlspecialchars($timeout_msg); ?>
            </div>
        <?php endif; ?>

        <!-- ====== Registration Success Message ====== -->
        <?php if (!empty($register_msg)): ?>
            <div class="auth-alert auth-alert-success">
                &#9989; <?php echo htmlspecialchars($register_msg); ?>
            </div>
        <?php endif; ?>

        <!-- ====== Error Message ====== -->
        <?php if (!empty($error)): ?>
            <div class="auth-alert auth-alert-error">
                &#9888; <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- ====== Login Form ====== -->
        <form class="auth-form" method="POST" action="">

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#9993;</span>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128274;</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-auth">
                &#128275; Login
            </button>
        </form>

        <!-- ====== Divider ====== -->
        <div class="auth-divider">
            <span>OR</span>
        </div>

        <!-- ====== Register Link ====== -->
        <div class="auth-footer">
            Don't have an account? 
            <a href="/online-food-delivery/auth/register.php">Register here</a>
        </div>

        <!-- ====== Demo Credentials ====== -->
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 0.82rem; color: #636e72;">
            <p style="font-weight: 700; margin-bottom: 8px; color: #2d3436;">&#128161; Demo Credentials:</p>
            <p><strong>Admin:</strong> admin@food.com / admin123</p>
            <p><strong>Rider:</strong> rider@food.com / rider123</p>
            <p><strong>Customer:</strong> Register a new account</p>
        </div>

    </div>
</div>

</body>
</html>
