<?php
/**
 * ============================================
 * CUSTOMER DASHBOARD
 * Online Food Delivery System
 * ============================================
 * This is the main dashboard for customers.
 * It shows:
 * - Welcome message
 * - Quick action buttons
 * - Order statistics
 * - Recent orders
 * ============================================
 */

// Auth check — only customers can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get customer ID from session
$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];

/**
 * ----------------------------------------
 * FETCH DASHBOARD STATISTICS
 * ----------------------------------------
 */

// Total orders by this customer
$query_total = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $query_total);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result_total = mysqli_stmt_get_result($stmt);
$total_orders = mysqli_fetch_assoc($result_total)['total'];
mysqli_stmt_close($stmt);

// Pending orders
$query_pending = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ? AND status = 'Pending'";
$stmt = mysqli_prepare($conn, $query_pending);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result_pending = mysqli_stmt_get_result($stmt);
$pending_orders = mysqli_fetch_assoc($result_pending)['total'];
mysqli_stmt_close($stmt);

// Active orders (Paid, Accepted, Picked Up, On the Way)
$query_active = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ? AND status IN ('Paid', 'Accepted', 'Picked Up', 'On the Way')";
$stmt = mysqli_prepare($conn, $query_active);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result_active = mysqli_stmt_get_result($stmt);
$active_orders = mysqli_fetch_assoc($result_active)['total'];
mysqli_stmt_close($stmt);

// Delivered orders
$query_delivered = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ? AND status = 'Delivered'";
$stmt = mysqli_prepare($conn, $query_delivered);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result_delivered = mysqli_stmt_get_result($stmt);
$delivered_orders = mysqli_fetch_assoc($result_delivered)['total'];
mysqli_stmt_close($stmt);

// Total amount spent
$query_spent = "SELECT COALESCE(SUM(total_price), 0) as total_spent FROM orders WHERE customer_id = ? AND status = 'Delivered'";
$stmt = mysqli_prepare($conn, $query_spent);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result_spent = mysqli_stmt_get_result($stmt);
$total_spent = mysqli_fetch_assoc($result_spent)['total_spent'];
mysqli_stmt_close($stmt);

/**
 * ----------------------------------------
 * FETCH RECENT ORDERS (Last 5)
 * ----------------------------------------
 */
$query_recent = "SELECT o.id, o.status, o.total_price, o.created_at, 
                        u.name as rider_name
                 FROM orders o 
                 LEFT JOIN users u ON o.rider_id = u.id 
                 WHERE o.customer_id = ? 
                 ORDER BY o.created_at DESC 
                 LIMIT 5";
$stmt = mysqli_prepare($conn, $query_recent);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$recent_orders = mysqli_stmt_get_result($stmt);

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Welcome Section ====== -->
    <div class="dashboard-welcome">
        <span class="welcome-icon">&#128075;</span>
        <h2>Welcome back, <?php echo htmlspecialchars($customer_name); ?>!</h2>
        <p>Ready to order something delicious? Browse our menu and enjoy fast delivery.</p>
    </div>

    <!-- ====== Quick Actions ====== -->
    <div class="quick-actions">
        <a href="/online-food-delivery/customer/menu.php" class="quick-action-btn">
            <span class="action-icon">&#128213;</span> Browse Menu
        </a>
        <a href="/online-food-delivery/customer/cart.php" class="quick-action-btn">
            <span class="action-icon">&#128722;</span> View Cart
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                (<?php echo count($_SESSION['cart']); ?>)
            <?php endif; ?>
        </a>
        <a href="/online-food-delivery/customer/orders.php" class="quick-action-btn">
            <span class="action-icon">&#128230;</span> My Orders
        </a>
    </div>

    <!-- ====== Statistics Cards ====== -->
    <h3 class="section-title">&#128202; <span>Order</span> Statistics</h3>
    <div class="stats-grid">

        <!-- Total Orders -->
        <div class="stat-card">
            <span class="stat-icon">&#128230;</span>
            <div class="stat-number"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>

        <!-- Pending Orders -->
        <div class="stat-card">
            <span class="stat-icon">&#9203;</span>
            <div class="stat-number"><?php echo $pending_orders; ?></div>
            <div class="stat-label">Pending</div>
        </div>

        <!-- Active Orders -->
        <div class="stat-card">
            <span class="stat-icon">&#128666;</span>
            <div class="stat-number"><?php echo $active_orders; ?></div>
            <div class="stat-label">Active</div>
        </div>

        <!-- Delivered Orders -->
        <div class="stat-card">
            <span class="stat-icon">&#9989;</span>
            <div class="stat-number"><?php echo $delivered_orders; ?></div>
            <div class="stat-label">Delivered</div>
        </div>

        <!-- Total Spent -->
        <div class="stat-card">
            <span class="stat-icon">&#128176;</span>
            <div class="stat-number">&#2547; <?php echo number_format($total_spent, 2); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>

    </div>

    <!-- ====== Recent Orders ====== -->
    <div class="recent-orders">
        <h3 class="section-title">&#128203; <span>Recent</span> Orders</h3>

        <?php if (mysqli_num_rows($recent_orders) > 0): ?>

            <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>

                <?php
                // Determine badge class based on status
                $badge_class = '';
                switch ($order['status']) {
                    case 'Pending':
                        $badge_class = 'badge-pending';
                        break;
                    case 'Paid':
                        $badge_class = 'badge-paid';
                        break;
                    case 'Accepted':
                        $badge_class = 'badge-accepted';
                        break;
                    case 'Picked Up':
                        $badge_class = 'badge-picked';
                        break;
                    case 'On the Way':
                        $badge_class = 'badge-onway';
                        break;
                    case 'Delivered':
                        $badge_class = 'badge-delivered';
                        break;
                }
                ?>

                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">&#128230; Order #<?php echo $order['id']; ?></span>
                        <span class="order-date">
                            <?php echo date("M d, Y - h:i A", strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <div>
                            <span class="badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                            <?php if (!empty($order['rider_name'])): ?>
                                <span style="margin-left: 10px; font-size: 0.85rem; color: #636e72;">
                                    &#128666; <?php echo htmlspecialchars($order['rider_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="order-total">
                            &#2547; <?php echo number_format($order['total_price'], 2); ?>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div style="margin-top: 12px; display: flex; gap: 8px;">
                        <a href="/online-food-delivery/customer/order.php?id=<?php echo $order['id']; ?>" 
                           class="btn btn-info btn-sm">
                            &#128269; View Details
                        </a>

                        <?php if ($order['status'] === 'Pending'): ?>
                            <a href="/online-food-delivery/customer/payment.php?order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-success btn-sm">
                                &#128179; Pay Now
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($order['rider_name']) && in_array($order['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                            <a href="/online-food-delivery/customer/chat.php?order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-primary btn-sm">
                                &#128172; Chat with Rider
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>

            <!-- View All Orders Link -->
            <div class="text-center mt-20">
                <a href="/online-food-delivery/customer/orders.php" class="btn btn-primary">
                    &#128230; View All Orders
                </a>
            </div>

        <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state">
                <span class="empty-icon">&#127829;</span>
                <p>You haven't placed any orders yet.</p>
                <a href="/online-food-delivery/customer/menu.php" class="btn btn-primary mt-20">
                    &#128213; Browse Menu & Order Now
                </a>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php
// Close statement
mysqli_stmt_close($stmt);

// Include footer
include '../includes/footer.php';
?>
