<?php
/**
 * ============================================
 * CUSTOMER - PAYMENT PAGE (DUMMY)
 * Online Food Delivery System
 * ============================================
 * Dummy payment system with 3 methods:
 * - Visa Card
 * - bKash
 * - Nagad
 * 
 * Behavior:
 * - User selects payment method
 * - Inputs a fake transaction ID
 * - Payment always succeeds
 * - Payment info stored in DB
 * - Order status updated to "Paid"
 * ============================================
 */

// Auth check — only customers can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get customer ID from session
$customer_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Initialize messages
$error = '';
$success = '';

/**
 * ----------------------------------------
 * FETCH ORDER DETAILS
 * ----------------------------------------
 * Verify order exists, belongs to customer,
 * and is in "Pending" status.
 * ----------------------------------------
 */
$order = null;

if ($order_id > 0) {
    $query = "SELECT * FROM orders WHERE id = ? AND customer_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $order = mysqli_fetch_assoc($result);

        // Check if order is already paid
        if ($order['status'] !== 'Pending') {
            $error = "This order has already been processed. Current status: " . $order['status'];
            $order = null;
        }
    } else {
        $error = "Order not found or you don't have permission to pay for this order.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Invalid order ID.";
}

/**
 * ----------------------------------------
 * FETCH ORDER ITEMS (for summary)
 * ----------------------------------------
 */
$order_items = array();

if ($order) {
    $items_query = "SELECT oi.*, f.name as food_name, f.price as food_price
                    FROM order_items oi
                    JOIN food f ON oi.food_id = f.id
                    WHERE oi.order_id = ?";
    $stmt = mysqli_prepare($conn, $items_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);

    while ($item = mysqli_fetch_assoc($items_result)) {
        $order_items[] = $item;
    }
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * HANDLE PAYMENT FORM SUBMISSION
 * ----------------------------------------
 * Process the dummy payment:
 * 1. Validate inputs
 * 2. Insert payment record into DB
 * 3. Update order status to "Paid"
 * 4. Redirect to order details
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment']) && $order) {

    // Get form inputs
    $payment_method = trim(mysqli_real_escape_string($conn, $_POST['payment_method']));
    $transaction_id = trim(mysqli_real_escape_string($conn, $_POST['transaction_id']));

    // Validate payment method
    $valid_methods = array('Visa Card', 'bKash', 'Nagad');

    if (empty($payment_method) || !in_array($payment_method, $valid_methods)) {
        $error = "Please select a valid payment method.";
    }
    // Validate transaction ID
    elseif (empty($transaction_id)) {
        $error = "Please enter a transaction ID.";
    }
    elseif (strlen($transaction_id) < 5) {
        $error = "Transaction ID must be at least 5 characters long.";
    }
    else {
        /**
         * Check if payment already exists for this order
         * to prevent duplicate payments
         */
        $check_query = "SELECT id FROM payments WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Payment has already been made for this order.";
        } else {

            // Begin transaction for data consistency
            mysqli_begin_transaction($conn);

            try {
                /**
                 * STEP 1: Insert payment record
                 */
                $payment_query = "INSERT INTO payments (order_id, payment_method, transaction_id, payment_status) 
                                  VALUES (?, ?, ?, 'Completed')";
                $stmt = mysqli_prepare($conn, $payment_query);
                mysqli_stmt_bind_param($stmt, "iss", $order_id, $payment_method, $transaction_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to record payment.");
                }
                mysqli_stmt_close($stmt);

                /**
                 * STEP 2: Update order status to "Paid"
                 */
                $update_query = "UPDATE orders SET status = 'Paid' WHERE id = ? AND customer_id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ii", $order_id, $customer_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to update order status.");
                }
                mysqli_stmt_close($stmt);

                // Commit transaction
                mysqli_commit($conn);

                // Redirect to order details with success
                header("Location: /online-food-delivery/customer/order.php?id=" . $order_id . "&payment=success");
                exit();

            } catch (Exception $e) {
                // Rollback on failure
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Back Button ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.4s ease-out;">
        <?php if ($order): ?>
            <a href="/online-food-delivery/customer/order.php?id=<?php echo $order_id; ?>" class="btn btn-info btn-sm">
                &#8592; Back to Order
            </a>
        <?php else: ?>
            <a href="/online-food-delivery/customer/orders.php" class="btn btn-info btn-sm">
                &#8592; Back to My Orders
            </a>
        <?php endif; ?>
    </div>

    <!-- ====== Page Title ====== -->
    <h2 class="page-title">&#128179; Payment</h2>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($order): ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">

            <!-- ====== LEFT: Payment Form ====== -->
            <div class="payment-container" style="max-width: 100%;">

                <h3 style="font-size: 1.2rem; color: #2d3436; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    &#128179; Select Payment Method
                </h3>

                <form method="POST" action="">

                    <!-- Payment Method Selection -->
                    <div class="payment-methods">

                        <!-- Visa Card -->
                        <div class="payment-method">
                            <input type="radio" id="visa" name="payment_method" value="Visa Card" checked>
                            <label for="visa">
                                <span class="method-icon">&#128179;</span>
                                <span class="method-name">Visa Card</span>
                            </label>
                        </div>

                        <!-- bKash -->
                        <div class="payment-method">
                            <input type="radio" id="bkash" name="payment_method" value="bKash">
                            <label for="bkash">
                                <span class="method-icon">&#128241;</span>
                                <span class="method-name">bKash</span>
                            </label>
                        </div>

                        <!-- Nagad -->
                        <div class="payment-method">
                            <input type="radio" id="nagad" name="payment_method" value="Nagad">
                            <label for="nagad">
                                <span class="method-icon">&#128178;</span>
                                <span class="method-name">Nagad</span>
                            </label>
                        </div>

                    </div>

                    <!-- Visa Card Fields -->
                    <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 15px;">
                            &#128196; Payment Details
                        </h4>

                        <!-- Card / Account Number -->
                        <div class="form-group">
                            <label for="card_number">Card / Account Number</label>
                            <input 
                                type="text" 
                                id="card_number" 
                                name="card_number" 
                                placeholder="Enter card or account number"
                                value="4111-1111-1111-1111"
                            >
                        </div>

                        <!-- Account Holder Name -->
                        <div class="form-group">
                            <label for="holder_name">Account Holder Name</label>
                            <input 
                                type="text" 
                                id="holder_name" 
                                name="holder_name" 
                                placeholder="Enter account holder name"
                                value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>"
                            >
                        </div>

                        <!-- Transaction ID -->
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID / Reference</label>
                            <input 
                                type="text" 
                                id="transaction_id" 
                                name="transaction_id" 
                                placeholder="Enter transaction ID (min 5 characters)"
                                required
                            >
                            <p class="password-hint">Enter any reference number (dummy payment — always succeeds)</p>
                        </div>
                    </div>

                    <!-- Dummy Payment Notice -->
                    <div style="background: #d1ecf1; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #0c5460; border-left: 4px solid #17a2b8;">
                        &#128161; <strong>Note:</strong> This is a dummy payment system for demonstration purposes. 
                        All payments will be processed successfully regardless of the details entered.
                    </div>

                    <!-- Submit Payment Button -->
                    <button type="submit" name="process_payment" class="btn btn-success btn-block" style="padding: 16px; font-size: 1.05rem;">
                        &#128274; Pay &#2547; <?php echo number_format($order['total_price'], 2); ?>
                    </button>

                </form>
            </div>

            <!-- ====== RIGHT: Order Summary ====== -->
            <div style="animation: fadeIn 0.7s ease-out;">

                <!-- Order Summary Card -->
                <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px;">
                    <h3 style="font-size: 1.1rem; color: #2d3436; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                        &#128203; Order Summary
                    </h3>

                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 0.85rem; color: #636e72;">Order ID:</span>
                        <span style="font-weight: 700; color: #2d3436;"> #<?php echo $order['id']; ?></span>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <span style="font-size: 0.85rem; color: #636e72;">Date:</span>
                        <span style="font-weight: 600; color: #2d3436;">
                            <?php echo date("M d, Y - h:i A", strtotime($order['created_at'])); ?>
                        </span>
                    </div>

                    <!-- Order Items -->
                    <div class="payment-summary">
                        <?php foreach ($order_items as $item): ?>
                            <div class="summary-row">
                                <span>&#127860; <?php echo htmlspecialchars($item['food_name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span>&#2547; <?php echo number_format($item['food_price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <!-- Subtotal -->
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>&#2547; <?php echo number_format($order['total_price'], 2); ?></span>
                        </div>

                        <!-- Delivery Fee -->
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span style="color: #00b894; font-weight: 600;">FREE</span>
                        </div>

                        <!-- Total -->
                        <div class="summary-row total">
                            <span>Total Amount</span>
                            <span>&#2547; <?php echo number_format($order['total_price'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                    <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 12px;">
                        &#128274; Secure Payment
                    </h4>
                    <div style="font-size: 0.85rem; color: #636e72; line-height: 1.7;">
                        <p style="margin-bottom: 8px;">&#9989; Your payment information is secure</p>
                        <p style="margin-bottom: 8px;">&#9989; Encrypted transaction processing</p>
                        <p style="margin-bottom: 8px;">&#9989; Instant payment confirmation</p>
                        <p>&#9989; Money-back guarantee</p>
                    </div>
                </div>

                <!-- Accepted Methods -->
                <div style="margin-top: 15px; text-align: center; font-size: 0.85rem; color: #b2bec3;">
                    <p>We accept:</p>
                    <p style="font-size: 1.5rem; margin-top: 5px;">
                        &#128179; &#128241; &#128178;
                    </p>
                    <p>Visa Card &bull; bKash &bull; Nagad</p>
                </div>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
