<?php
/**
 * ============================================
 * CUSTOMER - ALL ORDERS PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all orders placed by the customer.
 * Features:
 * - Filter orders by status
 * - View order details
 * - Pay for pending orders
 * - Chat with rider for active orders
 * - Color-coded status badges
 * ============================================
 */

// Auth check — only customers can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get customer ID from session
$customer_id = $_SESSION['user_id'];

/**
 * ----------------------------------------
 * HANDLE STATUS FILTER
 * ----------------------------------------
 * Customer can filter orders by status
 * using GET parameter.
 * ----------------------------------------
 */
$filter_status = '';
$valid_statuses = array('Pending', 'Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');

if (isset($_GET['status']) && in_array($_GET['status'], $valid_statuses)) {
    $filter_status = $_GET['status'];
}

/**
 * ----------------------------------------
 * FETCH ALL ORDERS FOR THIS CUSTOMER
 * ----------------------------------------
 * Join with users table to get rider name.
 * Apply status filter if selected.
 * ----------------------------------------
 */
if (!empty($filter_status)) {
    $query = "SELECT o.*, u.name as rider_name
              FROM orders o
              LEFT JOIN users u ON o.rider_id = u.id
              WHERE o.customer_id = ? AND o.status = ?
              ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $customer_id, $filter_status);
} else {
    $query = "SELECT o.*, u.name as rider_name
              FROM orders o
              LEFT JOIN users u ON o.rider_id = u.id
              WHERE o.customer_id = ?
              ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
}

mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
$total_orders = mysqli_num_rows($orders);

/**
 * ----------------------------------------
 * FETCH ORDER COUNTS BY STATUS
 * ----------------------------------------
 * For the filter buttons to show counts.
 * ----------------------------------------
 */
$count_query = "SELECT status, COUNT(*) as count FROM orders WHERE customer_id = ? GROUP BY status";
$stmt_count = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($stmt_count, "i", $customer_id);
mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);

$status_counts = array();
$total_all = 0;
while ($row = mysqli_fetch_assoc($count_result)) {
    $status_counts[$row['status']] = $row['count'];
    $total_all += $row['count'];
}
mysqli_stmt_close($stmt_count);

/**
 * ----------------------------------------
 * HELPER: Get badge class for status
 * ----------------------------------------
 */
function getBadgeClass($status) {
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

    <!-- ====== Page Title ====== -->
    <div class="flex-between">
        <h2 class="page-title">&#128230; My Orders</h2>
        <a href="/online-food-delivery/customer/menu.php" class="btn btn-primary btn-sm">
            &#128213; Order More Food
        </a>
    </div>

    <!-- ====== Status Filter Buttons ====== -->
    <div class="menu-filter">
        <!-- All Orders -->
        <a href="/online-food-delivery/customer/orders.php" 
           class="filter-btn <?php echo (empty($filter_status)) ? 'active' : ''; ?>">
            All (<?php echo $total_all; ?>)
        </a>

        <?php foreach ($valid_statuses as $status): ?>
            <?php $count = isset($status_counts[$status]) ? $status_counts[$status] : 0; ?>
            <a href="/online-food-delivery/customer/orders.php?status=<?php echo urlencode($status); ?>" 
               class="filter-btn <?php echo ($filter_status === $status) ? 'active' : ''; ?>">
                <?php echo $status; ?> (<?php echo $count; ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ====== Filter Info ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.5s ease-out;">
        <p style="font-size: 0.9rem; color: #636e72;">
            <?php if (!empty($filter_status)): ?>
                &#128269; Showing <strong><?php echo htmlspecialchars($filter_status); ?></strong> orders 
                — <?php echo $total_orders; ?> order(s) found
            <?php else: ?>
                &#128230; Showing all orders — <?php echo $total_orders; ?> order(s)
            <?php endif; ?>
        </p>
    </div>

    <!-- ====== Orders List ====== -->
    <?php if ($total_orders > 0): ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Rider</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <?php
                        // Fetch item count for this order
                        $item_query = "SELECT COUNT(*) as item_count, SUM(quantity) as total_qty 
                                       FROM order_items WHERE order_id = ?";
                        $stmt_items = mysqli_prepare($conn, $item_query);
                        mysqli_stmt_bind_param($stmt_items, "i", $order['id']);
                        mysqli_stmt_execute($stmt_items);
                        $item_result = mysqli_stmt_get_result($stmt_items);
                        $item_info = mysqli_fetch_assoc($item_result);
                        mysqli_stmt_close($stmt_items);
                        ?>
                        <tr>
                            <!-- Order ID -->
                            <td style="font-weight: 700;">
                                #<?php echo $order['id']; ?>
                            </td>

                            <!-- Date -->
                            <td>
                                <?php echo date("M d, Y", strtotime($order['created_at'])); ?>
                                <br>
                                <span style="font-size: 0.78rem; color: #b2bec3;">
                                    <?php echo date("h:i A", strtotime($order['created_at'])); ?>
                                </span>
                            </td>

                            <!-- Items Count -->
                            <td>
                                <?php echo $item_info['item_count']; ?> item(s)
                                <br>
                                <span style="font-size: 0.78rem; color: #b2bec3;">
                                    Qty: <?php echo $item_info['total_qty']; ?>
                                </span>
                            </td>

                            <!-- Total Price -->
                            <td style="font-weight: 700; color: #e74c3c;">
                                &#2547; <?php echo number_format($order['total_price'], 2); ?>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <span class="badge <?php echo getBadgeClass($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>

                            <!-- Rider -->
                            <td>
                                <?php if (!empty($order['rider_name'])): ?>
                                    &#128666; <?php echo htmlspecialchars($order['rider_name']); ?>
                                <?php else: ?>
                                    <span style="color: #b2bec3; font-size: 0.85rem;">Not assigned</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    <!-- View Details -->
                                    <a href="/online-food-delivery/customer/order.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-info btn-sm">
                                        &#128269; View
                                    </a>

                                    <!-- Pay Now (if Pending) -->
                                    <?php if ($order['status'] === 'Pending'): ?>
                                        <a href="/online-food-delivery/customer/payment.php?order_id=<?php echo $order['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            &#128179; Pay
                                        </a>
                                    <?php endif; ?>

                                    <!-- Chat with Rider (if active and rider assigned) -->
                                    <?php if (!empty($order['rider_name']) && in_array($order['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                                        <a href="/online-food-delivery/customer/chat.php?order_id=<?php echo $order['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            &#128172; Chat
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <!-- ====== Empty State ====== -->
        <div class="empty-state">
            <?php if (!empty($filter_status)): ?>
                <span class="empty-icon">&#128269;</span>
                <p>No <strong><?php echo htmlspecialchars($filter_status); ?></strong> orders found.</p>
                <a href="/online-food-delivery/customer/orders.php" class="btn btn-info mt-20">
                    &#128230; View All Orders
                </a>
            <?php else: ?>
                <span class="empty-icon">&#128722;</span>
                <p>You haven't placed any orders yet.</p>
                <p style="font-size: 0.9rem; color: #b2bec3; margin-top: 5px;">
                    Browse our menu and start ordering delicious food!
                </p>
                <a href="/online-food-delivery/customer/menu.php" class="btn btn-primary mt-20">
                    &#128213; Browse Menu
                </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <!-- ====== Order Summary Cards ====== -->
    <?php if ($total_all > 0): ?>
        <div style="margin-top: 30px; animation: fadeIn 0.8s ease-out;">
            <h3 class="section-title">&#128202; <span>Order</span> Overview</h3>
            <div class="stats-grid">

                <!-- Total Orders -->
                <div class="stat-card">
                    <span class="stat-icon">&#128230;</span>
                    <div class="stat-number"><?php echo $total_all; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>

                <!-- Pending -->
                <div class="stat-card" style="border-top: 3px solid #ffeaa7;">
                    <span class="stat-icon">&#9203;</span>
                    <div class="stat-number"><?php echo isset($status_counts['Pending']) ? $status_counts['Pending'] : 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>

                <!-- Active -->
                <?php
                $active_count = 0;
                $active_statuses = array('Paid', 'Accepted', 'Picked Up', 'On the Way');
                foreach ($active_statuses as $as) {
                    $active_count += isset($status_counts[$as]) ? $status_counts[$as] : 0;
                }
                ?>
                <div class="stat-card" style="border-top: 3px solid #74b9ff;">
                    <span class="stat-icon">&#128666;</span>
                    <div class="stat-number"><?php echo $active_count; ?></div>
                    <div class="stat-label">Active</div>
                </div>

                <!-- Delivered -->
                <div class="stat-card" style="border-top: 3px solid #55efc4;">
                    <span class="stat-icon">&#9989;</span>
                    <div class="stat-number"><?php echo isset($status_counts['Delivered']) ? $status_counts['Delivered'] : 0; ?></div>
                    <div class="stat-label">Delivered</div>
                </div>

            </div>
        </div>
    <?php endif; ?>

</div>

<?php
// Close statement
mysqli_stmt_close($stmt);

// Include footer
include '../includes/footer.php';
?>
