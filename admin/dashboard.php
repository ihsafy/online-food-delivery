<?php
/**
 * ============================================
 * ADMIN (OWNER) DASHBOARD
 * Online Food Delivery System
 * ============================================
 * This is the main dashboard for the admin/owner.
 * It shows:
 * - Welcome message
 * - Quick action buttons
 * - System-wide statistics
 * - Recent orders
 * - Revenue overview
 * ============================================
 */

// Auth check — only owner can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get admin info from session
$admin_name = $_SESSION['user_name'];

/**
 * ----------------------------------------
 * FETCH SYSTEM-WIDE STATISTICS
 * ----------------------------------------
 */

// Total customers
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$result = mysqli_query($conn, $query);
$total_customers = mysqli_fetch_assoc($result)['total'];

// Total riders
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'rider'";
$result = mysqli_query($conn, $query);
$total_riders = mysqli_fetch_assoc($result)['total'];

// Total food items
$query = "SELECT COUNT(*) as total FROM food";
$result = mysqli_query($conn, $query);
$total_food = mysqli_fetch_assoc($result)['total'];

// Available food items
$query = "SELECT COUNT(*) as total FROM food WHERE status = 'available'";
$result = mysqli_query($conn, $query);
$available_food = mysqli_fetch_assoc($result)['total'];

// Total orders
$query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $query);
$total_orders = mysqli_fetch_assoc($result)['total'];

// Pending orders
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'";
$result = mysqli_query($conn, $query);
$pending_orders = mysqli_fetch_assoc($result)['total'];

// Paid orders (waiting for rider assignment)
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Paid' AND rider_id IS NULL";
$result = mysqli_query($conn, $query);
$unassigned_orders = mysqli_fetch_assoc($result)['total'];

// Active orders (Paid, Accepted, Picked Up, On the Way)
$query = "SELECT COUNT(*) as total FROM orders WHERE status IN ('Paid', 'Accepted', 'Picked Up', 'On the Way')";
$result = mysqli_query($conn, $query);
$active_orders = mysqli_fetch_assoc($result)['total'];

// Delivered orders
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Delivered'";
$result = mysqli_query($conn, $query);
$delivered_orders = mysqli_fetch_assoc($result)['total'];

// Total revenue (from delivered orders)
$query = "SELECT COALESCE(SUM(total_price), 0) as revenue FROM orders WHERE status = 'Delivered'";
$result = mysqli_query($conn, $query);
$total_revenue = mysqli_fetch_assoc($result)['revenue'];

// Total payments received
$query = "SELECT COUNT(*) as total FROM payments WHERE payment_status = 'Completed'";
$result = mysqli_query($conn, $query);
$total_payments = mysqli_fetch_assoc($result)['total'];

/**
 * ----------------------------------------
 * FETCH RECENT ORDERS (Last 10)
 * ----------------------------------------
 */
$query_recent = "SELECT o.id, o.status, o.total_price, o.created_at, o.rider_id,
                        c.name as customer_name,
                        r.name as rider_name
                 FROM orders o
                 JOIN users c ON o.customer_id = c.id
                 LEFT JOIN users r ON o.rider_id = r.id
                 ORDER BY o.created_at DESC
                 LIMIT 10";
$recent_orders = mysqli_query($conn, $query_recent);

/**
 * ----------------------------------------
 * FETCH ORDER STATUS DISTRIBUTION
 * ----------------------------------------
 */
$query_dist = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$dist_result = mysqli_query($conn, $query_dist);
$status_distribution = array();
while ($row = mysqli_fetch_assoc($dist_result)) {
    $status_distribution[$row['status']] = $row['count'];
}

/**
 * ----------------------------------------
 * FETCH PAYMENT METHOD DISTRIBUTION
 * ----------------------------------------
 */
$query_payment = "SELECT payment_method, COUNT(*) as count FROM payments GROUP BY payment_method";
$payment_result = mysqli_query($conn, $query_payment);
$payment_distribution = array();
while ($row = mysqli_fetch_assoc($payment_result)) {
    $payment_distribution[$row['payment_method']] = $row['count'];
}

/**
 * ----------------------------------------
 * HELPER: Get badge class
 * ----------------------------------------
 */
function getAdminBadgeClass($status) {
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
        <span class="welcome-icon">&#128081;</span>
        <h2>Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</h2>
        <p>Here's an overview of your food delivery system. Manage food, orders, and riders from here.</p>
    </div>

    <!-- ====== Quick Actions ====== -->
    <div class="quick-actions">
        <a href="/online-food-delivery/admin/manage_food.php" class="quick-action-btn">
            <span class="action-icon">&#127860;</span> Manage Food
        </a>
        <a href="/online-food-delivery/admin/add_food.php" class="quick-action-btn">
            <span class="action-icon">&#10010;</span> Add New Food
        </a>
        <a href="/online-food-delivery/admin/orders.php" class="quick-action-btn">
            <span class="action-icon">&#128230;</span> Manage Orders
            <?php if ($unassigned_orders > 0): ?>
                (<?php echo $unassigned_orders; ?> unassigned)
            <?php endif; ?>
        </a>
    </div>

    <!-- ====== Unassigned Orders Alert ====== -->
    <?php if ($unassigned_orders > 0): ?>
        <div class="alert alert-warning" style="animation: fadeIn 0.6s ease-out;">
            &#128276; <strong>You have <?php echo $unassigned_orders; ?> paid order(s) without a rider!</strong> 
            Go to 
            <a href="/online-food-delivery/admin/orders.php" style="color: #856404; font-weight: 700; text-decoration: underline;">
                Manage Orders
            </a> 
            to assign riders.
        </div>
    <?php endif; ?>

    <!-- ====== Main Statistics ====== -->
    <h3 class="section-title">&#128202; <span>System</span> Overview</h3>
    <div class="stats-grid">

        <!-- Total Revenue -->
        <div class="stat-card" style="border-top: 3px solid #e74c3c;">
            <span class="stat-icon">&#128176;</span>
            <div class="stat-number">&#2547; <?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>

        <!-- Total Orders -->
        <div class="stat-card" style="border-top: 3px solid #0984e3;">
            <span class="stat-icon">&#128230;</span>
            <div class="stat-number"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>

        <!-- Active Orders -->
        <div class="stat-card" style="border-top: 3px solid #a29bfe;">
            <span class="stat-icon">&#128666;</span>
            <div class="stat-number"><?php echo $active_orders; ?></div>
            <div class="stat-label">Active Orders</div>
        </div>

        <!-- Delivered -->
        <div class="stat-card" style="border-top: 3px solid #00b894;">
            <span class="stat-icon">&#9989;</span>
            <div class="stat-number"><?php echo $delivered_orders; ?></div>
            <div class="stat-label">Delivered</div>
        </div>

        <!-- Total Customers -->
        <div class="stat-card" style="border-top: 3px solid #fdcb6e;">
            <span class="stat-icon">&#128100;</span>
            <div class="stat-number"><?php echo $total_customers; ?></div>
            <div class="stat-label">Customers</div>
        </div>

        <!-- Total Riders -->
        <div class="stat-card" style="border-top: 3px solid #fd79a8;">
            <span class="stat-icon">&#128666;</span>
            <div class="stat-number"><?php echo $total_riders; ?></div>
            <div class="stat-label">Riders</div>
        </div>

        <!-- Food Items -->
        <div class="stat-card" style="border-top: 3px solid #fab1a0;">
            <span class="stat-icon">&#127860;</span>
            <div class="stat-number"><?php echo $total_food; ?></div>
            <div class="stat-label">Food Items (<?php echo $available_food; ?> available)</div>
        </div>

        <!-- Total Payments -->
        <div class="stat-card" style="border-top: 3px solid #74b9ff;">
            <span class="stat-icon">&#128179;</span>
            <div class="stat-number"><?php echo $total_payments; ?></div>
            <div class="stat-label">Payments Received</div>
        </div>

    </div>

    <!-- ====== Distribution Section ====== -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px; animation: fadeIn 0.7s ease-out;">

        <!-- Order Status Distribution -->
        <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3 class="section-title">&#128203; <span>Order</span> Status Distribution</h3>

            <?php if (count($status_distribution) > 0): ?>
                <?php
                $all_statuses = array('Pending', 'Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');
                foreach ($all_statuses as $status):
                    $count = isset($status_distribution[$status]) ? $status_distribution[$status] : 0;
                    $percentage = ($total_orders > 0) ? round(($count / $total_orders) * 100) : 0;

                    // Bar color
                    $bar_color = '#dfe6e9';
                    switch ($status) {
                        case 'Pending':    $bar_color = '#ffeaa7'; break;
                        case 'Paid':       $bar_color = '#74b9ff'; break;
                        case 'Accepted':   $bar_color = '#a29bfe'; break;
                        case 'Picked Up':  $bar_color = '#fd79a8'; break;
                        case 'On the Way': $bar_color = '#fab1a0'; break;
                        case 'Delivered':  $bar_color = '#55efc4'; break;
                    }
                ?>
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #2d3436;">
                                <?php echo $status; ?>
                            </span>
                            <span style="font-size: 0.82rem; color: #636e72;">
                                <?php echo $count; ?> (<?php echo $percentage; ?>%)
                            </span>
                        </div>
                        <div style="background: #f0f0f0; border-radius: 10px; height: 10px; overflow: hidden;">
                            <div style="background: <?php echo $bar_color; ?>; height: 100%; width: <?php echo $percentage; ?>%; border-radius: 10px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #b2bec3; font-size: 0.9rem;">No orders yet.</p>
            <?php endif; ?>
        </div>

        <!-- Payment Method Distribution -->
        <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3 class="section-title">&#128179; <span>Payment</span> Methods</h3>

            <?php if (count($payment_distribution) > 0): ?>
                <?php
                $total_pay = array_sum($payment_distribution);
                $pay_colors = array(
                    'Visa Card' => '#0984e3',
                    'bKash'     => '#e84393',
                    'Nagad'     => '#e17055'
                );

                foreach ($payment_distribution as $method => $count):
                    $percentage = ($total_pay > 0) ? round(($count / $total_pay) * 100) : 0;
                    $color = isset($pay_colors[$method]) ? $pay_colors[$method] : '#636e72';
                ?>
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #2d3436;">
                                <?php echo htmlspecialchars($method); ?>
                            </span>
                            <span style="font-size: 0.82rem; color: #636e72;">
                                <?php echo $count; ?> (<?php echo $percentage; ?>%)
                            </span>
                        </div>
                        <div style="background: #f0f0f0; border-radius: 10px; height: 10px; overflow: hidden;">
                            <div style="background: <?php echo $color; ?>; height: 100%; width: <?php echo $percentage; ?>%; border-radius: 10px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #b2bec3; font-size: 0.9rem;">No payments yet.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- ====== Recent Orders Table ====== -->
    <div style="margin-top: 30px; animation: fadeIn 0.8s ease-out;">
        <div class="flex-between">
            <h3 class="section-title">&#128203; <span>Recent</span> Orders</h3>
            <a href="/online-food-delivery/admin/orders.php" class="btn btn-primary btn-sm">
                View All Orders &#10132;
            </a>
        </div>

        <?php if (mysqli_num_rows($recent_orders) > 0): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Rider</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td style="font-weight: 700;">#<?php echo $order['id']; ?></td>
                                <td>&#128100; <?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td style="font-weight: 700; color: #e74c3c;">
                                    &#2547; <?php echo number_format($order['total_price'], 2); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getAdminBadgeClass($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($order['rider_name'])): ?>
                                        &#128666; <?php echo htmlspecialchars($order['rider_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #e74c3c; font-size: 0.82rem; font-weight: 600;">
                                            &#9888; Unassigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date("M d, Y", strtotime($order['created_at'])); ?>
                                    <br>
                                    <span style="font-size: 0.78rem; color: #b2bec3;">
                                        <?php echo date("h:i A", strtotime($order['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/online-food-delivery/admin/orders.php" class="btn btn-info btn-sm">
                                        &#128269; Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">&#128230;</span>
                <p>No orders have been placed yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ====== System Info ====== -->
    <div style="margin-top: 30px; background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); animation: fadeIn 0.9s ease-out;">
        <h3 class="section-title">&#9881; <span>System</span> Information</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #e74c3c;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128187; Platform</p>
                <p style="font-size: 0.85rem; color: #636e72;">Online Food Delivery System v1.0</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #0984e3;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128296; Technology</p>
                <p style="font-size: 0.85rem; color: #636e72;">PHP <?php echo phpversion(); ?> + MySQL</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #00b894;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128100; Admin</p>
                <p style="font-size: 0.85rem; color: #636e72;"><?php echo htmlspecialchars($admin_name); ?> (<?php echo htmlspecialchars($_SESSION['user_email']); ?>)</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #fdcb6e;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128197; Server Time</p>
                <p style="font-size: 0.85rem; color: #636e72;"><?php echo date("F d, Y - h:i:s A"); ?></p>
            </div>

        </div>
    </div>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
