<?php
/**
 * ============================================
 * USER REGISTRATION PAGE
 * Online Food Delivery System
 * ============================================
 * Allows new users to create an account.
 * Roles available: Customer, Rider
 * (Owner/Admin account is pre-created in DB)
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
$name = '';
$email = '';
$role = 'customer';
$error = '';
$success = '';

/**
 * ----------------------------------------
 * HANDLE REGISTRATION FORM SUBMISSION
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize form inputs
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim(mysqli_real_escape_string($conn, $_POST['role']));

    // ---- Validation ----

    // Check empty fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required. Please fill in every field.";
    }
    // Validate name length
    elseif (strlen($name) < 3) {
        $error = "Name must be at least 3 characters long.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    // Validate password length
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    }
    // Check password match
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please try again.";
    }
    // Validate role
    elseif (!in_array($role, ['customer', 'rider'])) {
        $error = "Invalid role selected.";
    }
    else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "This email is already registered. Please use a different email or login.";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database
            $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! You can now login.";
                // Clear form fields
                $name = '';
                $email = '';
                $role = 'customer';
            } else {
                $error = "Something went wrong. Please try again later.";
            }
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
    <title>Register - FoodExpress</title>
    <link rel="stylesheet" href="/online-food-delivery/assets/css/style.css">
    <link rel="stylesheet" href="/online-food-delivery/assets/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">

        <!-- ====== Auth Header ====== -->
        <div class="auth-header">
            <span class="auth-icon">&#128221;</span>
            <h2>Create Account</h2>
            <p>Join FoodExpress and order your favorite food</p>
        </div>

        <!-- ====== Error Message ====== -->
        <?php if (!empty($error)): ?>
            <div class="auth-alert auth-alert-error">
                &#9888; <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- ====== Success Message ====== -->
        <?php if (!empty($success)): ?>
            <div class="auth-alert auth-alert-success">
                &#9989; <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- ====== Registration Form ====== -->
        <form class="auth-form" method="POST" action="">

            <!-- Full Name -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128100;</span>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >
                </div>
            </div>

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
                        placeholder="Create a password"
                        required
                    >
                </div>
                <p class="password-hint">Minimum 6 characters</p>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128274;</span>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                    >
                </div>
            </div>

            <!-- Role Selection -->
            <div class="form-group">
                <label>Register As</label>
                <div class="role-selector">
                    <!-- Customer Role -->
                    <div class="role-option">
                        <input 
                            type="radio" 
                            id="role_customer" 
                            name="role" 
                            value="customer"
                            <?php echo ($role === 'customer') ? 'checked' : ''; ?>
                        >
                        <label for="role_customer">
                            <span class="role-icon">&#128722;</span>
                            Customer
                        </label>
                    </div>

                    <!-- Rider Role -->
                    <div class="role-option">
                        <input 
                            type="radio" 
                            id="role_rider" 
                            name="role" 
                            value="rider"
                            <?php echo ($role === 'rider') ? 'checked' : ''; ?>
                        >
                        <label for="role_rider">
                            <span class="role-icon">&#128666;</span>
                            Rider
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-auth">
                &#128640; Create Account
            </button>
        </form>

        <!-- ====== Divider ====== -->
        <div class="auth-divider">
            <span>OR</span>
        </div>

        <!-- ====== Login Link ====== -->
        <div class="auth-footer">
            Already have an account? 
            <a href="/online-food-delivery/auth/login.php">Login here</a>
        </div>

    </div>
</div>

</body>
</html>
