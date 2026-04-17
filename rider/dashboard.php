<?php
/**
 * ============================================
 * RIDER DASHBOARD
 * Online Food Delivery System
 * ============================================
 * This is the main dashboard for riders.
 * It shows:
 * - Welcome message
 * - Quick action buttons
 * - Delivery statistics
 * - Recent assigned deliveries
 * ============================================
 */

// Auth check — only riders can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get rider ID from session
$rider_id = $_SESSION['user_id'];
$rider_name = $_SESSION['user_name'];

/**
 * ----------------------------------------
 * FETCH DASHBOARD STATISTICS
 * ----------------------------------------
 */

// Total assigned deliveries
$query_total = "SELECT COUNT(*) as total FROM orders WHERE rider_id = ?";
$stmt = mysqli_prepare($conn, $query_total);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$result_total = mysqli_stmt_get_result($stmt);
$total_deliveries = mysqli_fetch_assoc($result_total)['total'];
mysqli_stmt_close($stmt);

// New deliveries (Paid — waiting to be accepted)
$query_new = "SELECT COUNT(*) as total FROM orders WHERE rider_id = ? AND status = 'Paid'";
$stmt = mysqli_prepare($conn, $query_new);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$result_new = mysqli_stmt_get_result($stmt);
$new_deliveries = mysqli_fetch_assoc($result_new)['total'];
mysqli_stmt_close($stmt);

// Active deliveries (Accepted, Picked Up, On the Way)
$query_active = "SELECT COUNT(*) as total FROM orders WHERE rider_id = ? AND status IN ('Accepted', 'Picked Up', 'On the Way')";
$stmt = mysqli_prepare($conn, $query_active);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$result_active = mysqli_stmt_get_result($stmt);
$active_deliveries = mysqli_fetch_assoc($result_active)['total'];
mysqli_stmt_close($stmt);

// Completed deliveries
$query_completed = "SELECT COUNT(*) as total FROM orders WHERE rider_id = ? AND status = 'Delivered'";
$stmt = mysqli_prepare($conn, $query_completed);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$result_completed = mysqli_stmt_get_result($stmt);
$completed_deliveries = mysqli_fetch_assoc($result_completed)['total'];
mysqli_stmt_close($stmt);

// Total earnings (sum of delivered orders)
$query_earnings = "SELECT COALESCE(SUM(total_price), 0) as total_earnings FROM orders WHERE rider_id = ? AND status = 'Delivered'";
$stmt = mysqli_prepare($conn, $query_earnings);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$result_earnings = mysqli_stmt_get_result($stmt);
$total_earnings = mysqli_fetch_assoc($result_earnings)['total_earnings'];
mysqli_stmt_close($stmt);

/**
 * ----------------------------------------
 * FETCH RECENT DELIVERIES (Last 5)
 * ----------------------------------------
 */
$query_recent = "SELECT o.id, o.status, o.total_price, o.created_at,
                        c.name as customer_name
                 FROM orders o
                 JOIN users c ON o.customer_id = c.id
                 WHERE o.rider_id = ?
                 ORDER BY o.created_at DESC
                 LIMIT 5";
$stmt = mysqli_prepare($conn, $query_recent);
mysqli_stmt_bind_param($stmt, "i", $rider_id);
mysqli_stmt_execute($stmt);
$recent_deliveries = mysqli_stmt_get_result($stmt);

/**
 * ----------------------------------------
 * HELPER: Get badge class for status
 * ----------------------------------------
 */
function getRiderBadgeClass($status) {
    switch ($status) {
        case 'Pending':    return 'badge-pending';
        case 'Paid':       return 'badge-paid';
        case 'Accepted':   return 'badge-accepted';
        case 'Picked Up':  return 'badge-picked';
        case 'On the Way': return 'badge-onway';
        case 'Delivered':  return 'badge-delivered';
        default:           return '';
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Welcome Section ====== -->
    <div class="dashboard-welcome">
        <span class="welcome-icon">&#128666;</span>
        <h2>Welcome back, <?php echo htmlspecialchars($rider_name); ?>!</h2>
        <p>Check your assigned deliveries and keep your customers happy with fast delivery.</p>
    </div>

    <!-- ====== Quick Actions ====== -->
    <div class="quick-actions">
        <a href="/online-food-delivery/rider/deliveries.php" class="quick-action-btn">
            <span class="action-icon">&#128230;</span> All Deliveries
        </a>
        <a href="/online-food-delivery/rider/deliveries.php?status=Paid" class="quick-action-btn">
            <span class="action-icon">&#128276;</span> New Orders
            <?php if ($new_deliveries > 0): ?>
                (<?php echo $new_deliveries; ?>)
            <?php endif; ?>
        </a>
        <a href="/online-food-delivery/rider/deliveries.php?status=Accepted" class="quick-action-btn">
            <span class="action-icon">&#128666;</span> Active Deliveries
            <?php if ($active_deliveries > 0): ?>
                (<?php echo $active_deliveries; ?>)
            <?php endif; ?>
        </a>
    </div>

    <!-- ====== Statistics Cards ====== -->
    <h3 class="section-title">&#128202; <span>Delivery</span> Statistics</h3>
    <div class="stats-grid">

        <!-- Total Deliveries -->
        <div class="stat-card">
            <span class="stat-icon">&#128230;</span>
            <div class="stat-number"><?php echo $total_deliveries; ?></div>
            <div class="stat-label">Total Assigned</div>
        </div>

        <!-- New Orders -->
        <div class="stat-card" style="border-top: 3px solid #74b9ff;">
            <span class="stat-icon">&#128276;</span>
            <div class="stat-number"><?php echo $new_deliveries; ?></div>
            <div class="stat-label">New Orders</div>
        </div>

        <!-- Active Deliveries -->
        <div class="stat-card" style="border-top: 3px solid #a29bfe;">
            <span class="stat-icon">&#128666;</span>
            <div class="stat-number"><?php echo $active_deliveries; ?></div>
            <div class="stat-label">Active</div>
        </div>

        <!-- Completed -->
        <div class="stat-card" style="border-top: 3px solid #55efc4;">
            <span class="stat-icon">&#9989;</span>
            <div class="stat-number"><?php echo $completed_deliveries; ?></div>
            <div class="stat-label">Completed</div>
        </div>

        <!-- Total Earnings -->
        <div class="stat-card" style="border-top: 3px solid #ffeaa7;">
            <span class="stat-icon">&#128176;</span>
            <div class="stat-number">&#2547; <?php echo number_format($total_earnings, 2); ?></div>
            <div class="stat-label">Total Earnings</div>
        </div>

    </div>

    <!-- ====== New Orders Alert ====== -->
    <?php if ($new_deliveries > 0): ?>
        <div class="alert alert-warning" style="animation: fadeIn 0.6s ease-out;">
            &#128276; <strong>You have <?php echo $new_deliveries; ?> new order(s) waiting!</strong> 
            Go to 
            <a href="/online-food-delivery/rider/deliveries.php?status=Paid" style="color: #856404; font-weight: 700; text-decoration: underline;">
                Deliveries
            </a> 
            to accept them.
        </div>
    <?php endif; ?>

    <!-- ====== Recent Deliveries ====== -->
    <div class="recent-orders">
        <h3 class="section-title">&#128203; <span>Recent</span> Deliveries</h3>

        <?php if (mysqli_num_rows($recent_deliveries) > 0): ?>

            <?php while ($delivery = mysqli_fetch_assoc($recent_deliveries)): ?>

                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">&#128230; Order #<?php echo $delivery['id']; ?></span>
                        <span class="order-date">
                            <?php echo date("M d, Y - h:i A", strtotime($delivery['created_at'])); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <div>
                            <span class="badge <?php echo getRiderBadgeClass($delivery['status']); ?>">
                                <?php echo htmlspecialchars($delivery['status']); ?>
                            </span>
                            <span style="margin-left: 10px; font-size: 0.85rem; color: #636e72;">
                                &#128100; <?php echo htmlspecialchars($delivery['customer_name']); ?>
                            </span>
                        </div>
                        <span class="order-total">
                            &#2547; <?php echo number_format($delivery['total_price'], 2); ?>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <!-- View in Deliveries -->
                        <a href="/online-food-delivery/rider/deliveries.php" 
                           class="btn btn-info btn-sm">
                            &#128269; View Details
                        </a>

                        <!-- Accept (if Paid) -->
                        <?php if ($delivery['status'] === 'Paid'): ?>
                            <a href="/online-food-delivery/rider/deliveries.php?status=Paid" 
                               class="btn btn-success btn-sm">
                                &#9989; Accept Order
                            </a>
                        <?php endif; ?>

                        <!-- Chat with Customer (if active) -->
                        <?php if (in_array($delivery['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                            <a href="/online-food-delivery/rider/chat.php?order_id=<?php echo $delivery['id']; ?>" 
                               class="btn btn-primary btn-sm">
                                &#128172; Chat with Customer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>

            <!-- View All Deliveries Link -->
            <div class="text-center mt-20">
                <a href="/online-food-delivery/rider/deliveries.php" class="btn btn-primary">
                    &#128666; View All Deliveries
                </a>
            </div>

        <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state">
                <span class="empty-icon">&#128666;</span>
                <p>No deliveries assigned to you yet.</p>
                <p style="font-size: 0.9rem; color: #b2bec3; margin-top: 5px;">
                    New orders will appear here once the admin assigns them to you.
                </p>
            </div>

        <?php endif; ?>

    </div>

    <!-- ====== Rider Tips Section ====== -->
    <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-top: 25px; animation: fadeIn 0.9s ease-out;">
        <h3 class="section-title">&#128161; <span>Rider</span> Tips</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #00b894;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#9989; Accept Quickly</p>
                <p style="font-size: 0.85rem; color: #636e72;">Accept new orders as soon as possible to keep customers happy.</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #0984e3;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128172; Communicate</p>
                <p style="font-size: 0.85rem; color: #636e72;">Use the chat feature to update customers about their delivery.</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #e74c3c;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128260; Update Status</p>
                <p style="font-size: 0.85rem; color: #636e72;">Keep the delivery status updated at each step of the process.</p>
            </div>

        </div>
    </div>

</div>

<?php
// Close statement
mysqli_stmt_close($stmt);

// Include footer
include '../includes/footer.php';
?>
