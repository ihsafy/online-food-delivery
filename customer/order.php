<?php
/**
 * ============================================
 * CUSTOMER - ORDER DETAILS PAGE
 * Online Food Delivery System
 * ============================================
 * Displays full details of a specific order:
 * - Order info (ID, date, status, total)
 * - Order items list
 * - Payment info
 * - Rider info
 * - Delivery status tracker
 * - Link to chat / payment
 * ============================================
 */

// Auth check — only customers can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get customer ID from session
$customer_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize
$error = '';

/**
 * ----------------------------------------
 * FETCH ORDER DETAILS
 * ----------------------------------------
 * Get order info along with rider name.
 * Verify the order belongs to this customer.
 * ----------------------------------------
 */
$order = null;

if ($order_id > 0) {
    $query = "SELECT o.*, u.name as rider_name, u.email as rider_email
              FROM orders o
              LEFT JOIN users u ON o.rider_id = u.id
              WHERE o.id = ? AND o.customer_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $order = mysqli_fetch_assoc($result);
    } else {
        $error = "Order not found or you don't have permission to view this order.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Invalid order ID.";
}

/**
 * ----------------------------------------
 * FETCH ORDER ITEMS
 * ----------------------------------------
 * Get all food items in this order with
 * food name and price from the food table.
 * ----------------------------------------
 */
$order_items = array();

if ($order) {
    $items_query = "SELECT oi.*, f.name as food_name, f.price as food_price, f.description as food_desc
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
 * FETCH PAYMENT INFO
 * ----------------------------------------
 * Get payment details for this order.
 * ----------------------------------------
 */
$payment = null;

if ($order) {
    $payment_query = "SELECT * FROM payments WHERE order_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $payment_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $payment_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($payment_result) === 1) {
        $payment = mysqli_fetch_assoc($payment_result);
    }
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * DETERMINE STATUS TRACKER STEPS
 * ----------------------------------------
 * Map order status to step numbers for
 * the visual delivery tracker.
 * ----------------------------------------
 */
$status_steps = array(
    'Pending'    => 1,
    'Paid'       => 2,
    'Accepted'   => 3,
    'Picked Up'  => 4,
    'On the Way' => 5,
    'Delivered'  => 6
);

$current_step = 0;
if ($order) {
    $current_step = isset($status_steps[$order['status']]) ? $status_steps[$order['status']] : 0;
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Back Button ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.4s ease-out;">
        <a href="/online-food-delivery/customer/orders.php" class="btn btn-info btn-sm">
            &#8592; Back to My Orders
        </a>
    </div>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($order): ?>

        <!-- ====== Page Title ====== -->
        <h2 class="page-title">&#128230; Order #<?php echo $order['id']; ?></h2>

        <!-- ====== Delivery Status Tracker ====== -->
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 25px; animation: fadeIn 0.5s ease-out;">
            <h3 class="section-title">&#128666; <span>Delivery</span> Status</h3>

            <div class="status-tracker">
                <!-- Step 1: Pending -->
                <div class="status-step <?php echo ($current_step >= 1) ? (($current_step == 1) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#9203;</div>
                    <span class="step-label">Pending</span>
                </div>

                <!-- Step 2: Paid -->
                <div class="status-step <?php echo ($current_step >= 2) ? (($current_step == 2) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#128179;</div>
                    <span class="step-label">Paid</span>
                </div>

                <!-- Step 3: Accepted -->
                <div class="status-step <?php echo ($current_step >= 3) ? (($current_step == 3) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#9989;</div>
                    <span class="step-label">Accepted</span>
                </div>

                <!-- Step 4: Picked Up -->
                <div class="status-step <?php echo ($current_step >= 4) ? (($current_step == 4) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#128230;</div>
                    <span class="step-label">Picked Up</span>
                </div>

                <!-- Step 5: On the Way -->
                <div class="status-step <?php echo ($current_step >= 5) ? (($current_step == 5) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#128666;</div>
                    <span class="step-label">On the Way</span>
                </div>

                <!-- Step 6: Delivered -->
                <div class="status-step <?php echo ($current_step >= 6) ? (($current_step == 6) ? 'active' : 'completed') : ''; ?>">
                    <div class="step-circle">&#127881;</div>
                    <span class="step-label">Delivered</span>
                </div>
            </div>
        </div>

        <!-- ====== Order Info & Actions ====== -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">

            <!-- Order Information Card -->
            <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); animation: fadeIn 0.6s ease-out;">
                <h3 class="section-title">&#128203; <span>Order</span> Information</h3>

                <div style="margin-bottom: 12px;">
                    <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Order ID</p>
                    <p style="font-weight: 700; color: #2d3436;">#<?php echo $order['id']; ?></p>
                </div>

                <div style="margin-bottom: 12px;">
                    <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Date Placed</p>
                    <p style="font-weight: 600; color: #2d3436;">
                        <?php echo date("F d, Y - h:i A", strtotime($order['created_at'])); ?>
                    </p>
                </div>

                <div style="margin-bottom: 12px;">
                    <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Status</p>
                    <?php
                    $badge_class = '';
                    switch ($order['status']) {
                        case 'Pending':    $badge_class = 'badge-pending'; break;
                        case 'Paid':       $badge_class = 'badge-paid'; break;
                        case 'Accepted':   $badge_class = 'badge-accepted'; break;
                        case 'Picked Up':  $badge_class = 'badge-picked'; break;
                        case 'On the Way': $badge_class = 'badge-onway'; break;
                        case 'Delivered':  $badge_class = 'badge-delivered'; break;
                    }
                    ?>
                    <span class="badge <?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>

                <div style="margin-bottom: 12px;">
                    <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Total Amount</p>
                    <p style="font-weight: 700; font-size: 1.3rem; color: #e74c3c;">
                        &#2547; <?php echo number_format($order['total_price'], 2); ?>
                    </p>
                </div>
            </div>

            <!-- Rider & Payment Info Card -->
            <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); animation: fadeIn 0.7s ease-out;">

                <!-- Rider Info -->
                <h3 class="section-title">&#128666; <span>Rider</span> Info</h3>

                <?php if (!empty($order['rider_name'])): ?>
                    <div style="margin-bottom: 12px;">
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Rider Name</p>
                        <p style="font-weight: 600; color: #2d3436;">
                            &#128100; <?php echo htmlspecialchars($order['rider_name']); ?>
                        </p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Rider Email</p>
                        <p style="font-weight: 600; color: #2d3436;">
                            &#9993; <?php echo htmlspecialchars($order['rider_email']); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 20px; padding: 12px; background: #ffeaa7; border-radius: 8px; font-size: 0.88rem; color: #856404;">
                        &#9203; No rider assigned yet. A rider will be assigned soon.
                    </div>
                <?php endif; ?>

                <!-- Payment Info -->
                <h3 class="section-title">&#128179; <span>Payment</span> Info</h3>

                <?php if ($payment): ?>
                    <div style="margin-bottom: 8px;">
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Payment Method</p>
                        <p style="font-weight: 600; color: #2d3436;">
                            <?php echo htmlspecialchars($payment['payment_method']); ?>
                        </p>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Transaction ID</p>
                        <p style="font-weight: 600; color: #2d3436;">
                            <?php echo htmlspecialchars($payment['transaction_id']); ?>
                        </p>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Payment Status</p>
                        <span class="badge badge-delivered">
                            &#9989; <?php echo htmlspecialchars($payment['payment_status']); ?>
                        </span>
                    </div>
                    <div>
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 4px;">Payment Date</p>
                        <p style="font-weight: 600; color: #2d3436;">
                            <?php echo date("F d, Y - h:i A", strtotime($payment['created_at'])); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php if ($order['status'] === 'Pending'): ?>
                        <div style="padding: 12px; background: #fee2e2; border-radius: 8px; font-size: 0.88rem; color: #991b1b; margin-bottom: 12px;">
                            &#9888; Payment not completed yet.
                        </div>
                        <a href="/online-food-delivery/customer/payment.php?order_id=<?php echo $order['id']; ?>" 
                           class="btn btn-success">
                            &#128179; Pay Now
                        </a>
                    <?php else: ?>
                        <p style="font-size: 0.88rem; color: #636e72;">No payment information available.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- ====== Order Items Table ====== -->
        <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 25px; animation: fadeIn 0.8s ease-out;">
            <h3 class="section-title">&#127860; <span>Order</span> Items</h3>

            <?php if (count($order_items) > 0): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Food Item</th>
                                <th>Description</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            foreach ($order_items as $item): 
                                $subtotal = $item['food_price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td style="font-weight: 600;">
                                        &#127860; <?php echo htmlspecialchars($item['food_name']); ?>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #636e72;">
                                        <?php echo htmlspecialchars($item['food_desc']); ?>
                                    </td>
                                    <td>&#2547; <?php echo number_format($item['food_price'], 2); ?></td>
                                    <td style="text-align: center; font-weight: 600;">
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td style="font-weight: 700; color: #e74c3c;">
                                        &#2547; <?php echo number_format($subtotal, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8f9fa;">
                                <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1.05rem;">
                                    Total Amount:
                                </td>
                                <td style="font-weight: 700; font-size: 1.15rem; color: #e74c3c;">
                                    &#2547; <?php echo number_format($order['total_price'], 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #636e72;">No items found for this order.</p>
            <?php endif; ?>
        </div>

        <!-- ====== Action Buttons ====== -->
        <div style="display: flex; gap: 12px; flex-wrap: wrap; animation: fadeIn 0.9s ease-out;">

            <!-- Pay Now (if Pending) -->
            <?php if ($order['status'] === 'Pending'): ?>
                <a href="/online-food-delivery/customer/payment.php?order_id=<?php echo $order['id']; ?>" 
                   class="btn btn-success">
                    &#128179; Pay Now
                </a>
            <?php endif; ?>

            <!-- Chat with Rider (if rider assigned and order is active) -->
            <?php if (!empty($order['rider_name']) && in_array($order['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                <a href="/online-food-delivery/customer/chat.php?order_id=<?php echo $order['id']; ?>" 
                   class="btn btn-primary">
                    &#128172; Chat with Rider
                </a>
            <?php endif; ?>

            <!-- Back to Orders -->
            <a href="/online-food-delivery/customer/orders.php" class="btn btn-info">
                &#128230; All Orders
            </a>

            <!-- Back to Menu -->
            <a href="/online-food-delivery/customer/menu.php" class="btn btn-warning">
                &#128213; Order More Food
            </a>

        </div>

    <?php endif; ?>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
