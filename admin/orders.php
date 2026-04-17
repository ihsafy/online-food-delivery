<?php
/**
 * ============================================
 * ADMIN - MANAGE ORDERS PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all orders in the system.
 * Admin can:
 * - View all orders with details
 * - Filter orders by status
 * - Assign riders to orders
 * - View order items
 * - View payment info
 * - Track order status
 * ============================================
 */

// Auth check — only owner can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Initialize messages
$success = '';
$error = '';

/**
 * ----------------------------------------
 * HANDLE: ASSIGN RIDER TO ORDER
 * ----------------------------------------
 * Admin selects a rider from dropdown and
 * assigns them to a specific order.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_rider'])) {

    $order_id = intval($_POST['order_id']);
    $rider_id = intval($_POST['rider_id']);

    // Validate rider exists and has role 'rider'
    $check_rider = "SELECT id, name FROM users WHERE id = ? AND role = 'rider'";
    $stmt = mysqli_prepare($conn, $check_rider);
    mysqli_stmt_bind_param($stmt, "i", $rider_id);
    mysqli_stmt_execute($stmt);
    $rider_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($rider_result) === 0) {
        $error = "Invalid rider selected. Please choose a valid rider.";
    } else {
        $rider = mysqli_fetch_assoc($rider_result);

        // Verify order exists
        $check_order = "SELECT id, status FROM orders WHERE id = ?";
        $stmt_order = mysqli_prepare($conn, $check_order);
        mysqli_stmt_bind_param($stmt_order, "i", $order_id);
        mysqli_stmt_execute($stmt_order);
        $order_result = mysqli_stmt_get_result($stmt_order);

        if (mysqli_num_rows($order_result) === 0) {
            $error = "Order not found.";
        } else {
            $order_data = mysqli_fetch_assoc($order_result);

            // Only assign rider to Paid orders or update existing assignment
            if (in_array($order_data['status'], ['Paid', 'Accepted', 'Picked Up', 'On the Way'])) {

                $update_query = "UPDATE orders SET rider_id = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt_update, "ii", $rider_id, $order_id);

                if (mysqli_stmt_execute($stmt_update)) {
                    $success = "Rider \"" . htmlspecialchars($rider['name']) . "\" has been assigned to Order #" . $order_id . " successfully!";
                } else {
                    $error = "Failed to assign rider. Please try again.";
                }
                mysqli_stmt_close($stmt_update);

            } else {
                $error = "Cannot assign rider to an order with status \"" . htmlspecialchars($order_data['status']) . "\". Order must be Paid first.";
            }
        }
        mysqli_stmt_close($stmt_order);
    }
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * HANDLE STATUS FILTER
 * ----------------------------------------
 */
$filter_status = '';
$valid_statuses = array('Pending', 'Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');

if (isset($_GET['status']) && in_array($_GET['status'], $valid_statuses)) {
    $filter_status = $_GET['status'];
}

/**
 * ----------------------------------------
 * FETCH ALL RIDERS (for assignment dropdown)
 * ----------------------------------------
 */
$riders_query = "SELECT id, name, email FROM users WHERE role = 'rider' ORDER BY name ASC";
$riders_result = mysqli_query($conn, $riders_query);
$riders = array();
while ($r = mysqli_fetch_assoc($riders_result)) {
    $riders[] = $r;
}

/**
 * ----------------------------------------
 * FETCH ALL ORDERS
 * ----------------------------------------
 */
if (!empty($filter_status)) {
    $query = "SELECT o.*, 
                     c.name as customer_name, c.email as customer_email,
                     r.name as rider_name, r.email as rider_email
              FROM orders o
              JOIN users c ON o.customer_id = c.id
              LEFT JOIN users r ON o.rider_id = r.id
              WHERE o.status = ?
              ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $filter_status);
} else {
    $query = "SELECT o.*, 
                     c.name as customer_name, c.email as customer_email,
                     r.name as rider_name, r.email as rider_email
              FROM orders o
              JOIN users c ON o.customer_id = c.id
              LEFT JOIN users r ON o.rider_id = r.id
              ORDER BY 
                  CASE o.status
                      WHEN 'Pending' THEN 1
                      WHEN 'Paid' THEN 2
                      WHEN 'Accepted' THEN 3
                      WHEN 'Picked Up' THEN 4
                      WHEN 'On the Way' THEN 5
                      WHEN 'Delivered' THEN 6
                  END ASC,
                  o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
}

mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
$total_orders = mysqli_num_rows($orders);

/**
 * ----------------------------------------
 * FETCH STATUS COUNTS
 * ----------------------------------------
 */
$count_query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$count_result = mysqli_query($conn, $count_query);
$status_counts = array();
$total_all = 0;
while ($row = mysqli_fetch_assoc($count_result)) {
    $status_counts[$row['status']] = $row['count'];
    $total_all += $row['count'];
}

/**
 * ----------------------------------------
 * HELPER: Get badge class
 * ----------------------------------------
 */
function getOrderBadgeClass($status) {
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
        <h2 class="page-title">&#128230; Manage Orders</h2>
        <a href="/online-food-delivery/admin/dashboard.php" class="btn btn-info btn-sm">
            &#127968; Dashboard
        </a>
    </div>

    <!-- ====== Success Message ====== -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            &#9989; <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- ====== Deleted Food Success (from manage_food redirect) ====== -->
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div class="alert alert-success">
            &#9989; Food item has been deleted successfully!
        </div>
    <?php endif; ?>

    <!-- ====== Status Filter Buttons ====== -->
    <div class="menu-filter">
        <a href="/online-food-delivery/admin/orders.php" 
           class="filter-btn <?php echo (empty($filter_status)) ? 'active' : ''; ?>">
            All (<?php echo $total_all; ?>)
        </a>

        <?php foreach ($valid_statuses as $status): ?>
            <?php $count = isset($status_counts[$status]) ? $status_counts[$status] : 0; ?>
            <a href="/online-food-delivery/admin/orders.php?status=<?php echo urlencode($status); ?>" 
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
                <span style="font-size: 0.82rem; color: #b2bec3;">(sorted by priority)</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- ====== Orders List ====== -->
    <?php if ($total_orders > 0): ?>

        <?php while ($order = mysqli_fetch_assoc($orders)): ?>

            <?php
            // Fetch order items
            $items_query = "SELECT oi.quantity, f.name as food_name, f.price as food_price
                            FROM order_items oi
                            JOIN food f ON oi.food_id = f.id
                            WHERE oi.order_id = ?";
            $stmt_items = mysqli_prepare($conn, $items_query);
            mysqli_stmt_bind_param($stmt_items, "i", $order['id']);
            mysqli_stmt_execute($stmt_items);
            $items_result = mysqli_stmt_get_result($stmt_items);

            $order_items = array();
            while ($item = mysqli_fetch_assoc($items_result)) {
                $order_items[] = $item;
            }
            mysqli_stmt_close($stmt_items);

            // Fetch payment info
            $pay_query = "SELECT * FROM payments WHERE order_id = ? LIMIT 1";
            $stmt_pay = mysqli_prepare($conn, $pay_query);
            mysqli_stmt_bind_param($stmt_pay, "i", $order['id']);
            mysqli_stmt_execute($stmt_pay);
            $pay_result = mysqli_stmt_get_result($stmt_pay);
            $payment = (mysqli_num_rows($pay_result) === 1) ? mysqli_fetch_assoc($pay_result) : null;
            mysqli_stmt_close($stmt_pay);
            ?>

            <div class="delivery-card" style="border-left-color: 
                <?php 
                switch ($order['status']) {
                    case 'Pending':    echo '#ffeaa7'; break;
                    case 'Paid':       echo '#74b9ff'; break;
                    case 'Accepted':   echo '#a29bfe'; break;
                    case 'Picked Up':  echo '#fd79a8'; break;
                    case 'On the Way': echo '#fab1a0'; break;
                    case 'Delivered':  echo '#55efc4'; break;
                    default:           echo '#e74c3c'; break;
                }
                ?>;">

                <!-- Order Header -->
                <div class="delivery-header">
                    <span style="font-size: 1.1rem; font-weight: 700; color: #2d3436;">
                        &#128230; Order #<?php echo $order['id']; ?>
                    </span>
                    <span class="badge <?php echo getOrderBadgeClass($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>

                <!-- Order Info Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">

                    <!-- Customer Info -->
                    <div>
                        <p style="font-size: 0.78rem; color: #b2bec3; margin-bottom: 3px;">CUSTOMER</p>
                        <p style="font-weight: 600; color: #2d3436; font-size: 0.9rem;">
                            &#128100; <?php echo htmlspecialchars($order['customer_name']); ?>
                        </p>
                        <p style="font-size: 0.78rem; color: #636e72;">
                            <?php echo htmlspecialchars($order['customer_email']); ?>
                        </p>
                    </div>

                    <!-- Order Details -->
                    <div>
                        <p style="font-size: 0.78rem; color: #b2bec3; margin-bottom: 3px;">ORDER DETAILS</p>
                        <p style="font-weight: 700; color: #e74c3c; font-size: 1.1rem;">
                            &#2547; <?php echo number_format($order['total_price'], 2); ?>
                        </p>
                        <p style="font-size: 0.78rem; color: #636e72;">
                            <?php echo date("M d, Y - h:i A", strtotime($order['created_at'])); ?>
                        </p>
                    </div>

                    <!-- Rider Info -->
                    <div>
                        <p style="font-size: 0.78rem; color: #b2bec3; margin-bottom: 3px;">RIDER</p>
                        <?php if (!empty($order['rider_name'])): ?>
                            <p style="font-weight: 600; color: #2d3436; font-size: 0.9rem;">
                                &#128666; <?php echo htmlspecialchars($order['rider_name']); ?>
                            </p>
                            <p style="font-size: 0.78rem; color: #636e72;">
                                <?php echo htmlspecialchars($order['rider_email']); ?>
                            </p>
                        <?php else: ?>
                            <p style="color: #e74c3c; font-weight: 600; font-size: 0.88rem;">
                                &#9888; Not Assigned
                            </p>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Order Items -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 12px 16px; margin-bottom: 15px;">
                    <p style="font-weight: 600; font-size: 0.85rem; color: #2d3436; margin-bottom: 8px;">
                        &#127860; Order Items (<?php echo count($order_items); ?>):
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($order_items as $item): ?>
                            <span style="padding: 4px 10px; background: #fff; border: 1px solid #dfe6e9; border-radius: 6px; font-size: 0.82rem; color: #2d3436;">
                                <?php echo htmlspecialchars($item['food_name']); ?> x<?php echo $item['quantity']; ?>
                                <span style="color: #e74c3c; font-weight: 600;">
                                    (&#2547;<?php echo number_format($item['food_price'] * $item['quantity'], 2); ?>)
                                </span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Payment Info -->
                <?php if ($payment): ?>
                    <div style="background: #d1fae5; border-radius: 8px; padding: 10px 16px; margin-bottom: 15px; font-size: 0.85rem;">
                        <span style="font-weight: 600; color: #065f46;">&#128179; Payment:</span>
                        <span style="color: #065f46;">
                            <?php echo htmlspecialchars($payment['payment_method']); ?> &bull;
                            TxID: <?php echo htmlspecialchars($payment['transaction_id']); ?> &bull;
                            <?php echo htmlspecialchars($payment['payment_status']); ?> &bull;
                            <?php echo date("M d, Y h:i A", strtotime($payment['created_at'])); ?>
                        </span>
                    </div>
                <?php elseif ($order['status'] === 'Pending'): ?>
                    <div style="background: #fff3cd; border-radius: 8px; padding: 10px 16px; margin-bottom: 15px; font-size: 0.85rem;">
                        <span style="font-weight: 600; color: #856404;">&#9888; Payment:</span>
                        <span style="color: #856404;">Awaiting payment from customer</span>
                    </div>
                <?php endif; ?>

                <!-- Mini Status Tracker -->
                <div style="display: flex; gap: 6px; margin-bottom: 15px; flex-wrap: wrap;">
                    <?php
                    $all_statuses = array('Pending', 'Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');
                    $current_index = array_search($order['status'], $all_statuses);

                    foreach ($all_statuses as $index => $s):
                        $step_style = '';
                        if ($index < $current_index) {
                            $step_style = 'background: #00b894; color: #fff;';
                        } elseif ($index == $current_index) {
                            $step_style = 'background: #e74c3c; color: #fff;';
                        } else {
                            $step_style = 'background: #dfe6e9; color: #b2bec3;';
                        }
                    ?>
                        <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; <?php echo $step_style; ?>">
                            <?php echo $s; ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <!-- Actions Row -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">

                    <!-- Assign Rider (if Paid and no rider or reassign) -->
                    <?php if (in_array($order['status'], ['Paid', 'Accepted', 'Picked Up', 'On the Way'])): ?>
                        <form method="POST" action="" class="assign-rider-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="rider_id" required>
                                <option value="">-- Select Rider --</option>
                                <?php foreach ($riders as $r): ?>
                                    <option value="<?php echo $r['id']; ?>"
                                        <?php echo ($order['rider_id'] == $r['id']) ? 'selected' : ''; ?>>
                                        &#128666; <?php echo htmlspecialchars($r['name']); ?> (<?php echo htmlspecialchars($r['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_rider" class="btn btn-success btn-sm">
                                &#9989; Assign
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Delivered Badge -->
                    <?php if ($order['status'] === 'Delivered'): ?>
                        <span style="padding: 8px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            &#127881; Order Completed
                        </span>
                    <?php endif; ?>

                    <!-- Pending Badge -->
                    <?php if ($order['status'] === 'Pending'): ?>
                        <span style="padding: 8px 16px; background: #fff3cd; color: #856404; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            &#9203; Waiting for Payment
                        </span>
                    <?php endif; ?>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <!-- ====== Empty State ====== -->
        <div class="empty-state">
            <?php if (!empty($filter_status)): ?>
                <span class="empty-icon">&#128269;</span>
                <p>No <strong><?php echo htmlspecialchars($filter_status); ?></strong> orders found.</p>
                <a href="/online-food-delivery/admin/orders.php" class="btn btn-info mt-20">
                    &#128230; View All Orders
                </a>
            <?php else: ?>
                <span class="empty-icon">&#128230;</span>
                <p>No orders have been placed yet.</p>
                <p style="font-size: 0.9rem; color: #b2bec3; margin-top: 5px;">
                    Orders will appear here once customers start placing them.
                </p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <!-- ====== Order Statistics ====== -->
    <?php if ($total_all > 0): ?>
        <div style="margin-top: 30px; animation: fadeIn 0.8s ease-out;">
            <h3 class="section-title">&#128202; <span>Order</span> Statistics</h3>
            <div class="stats-grid">

                <!-- Total Orders -->
                <div class="stat-card" style="border-top: 3px solid #e74c3c;">
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

                <!-- Paid -->
                <div class="stat-card" style="border-top: 3px solid #74b9ff;">
                    <span class="stat-icon">&#128179;</span>
                    <div class="stat-number"><?php echo isset($status_counts['Paid']) ? $status_counts['Paid'] : 0; ?></div>
                    <div class="stat-label">Paid</div>
                </div>

                <!-- Active -->
                <?php
                $active_count = 0;
                $active_statuses = array('Accepted', 'Picked Up', 'On the Way');
                foreach ($active_statuses as $as) {
                    $active_count += isset($status_counts[$as]) ? $status_counts[$as] : 0;
                }
                ?>
                <div class="stat-card" style="border-top: 3px solid #a29bfe;">
                    <span class="stat-icon">&#128666;</span>
                    <div class="stat-number"><?php echo $active_count; ?></div>
                    <div class="stat-label">In Delivery</div>
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
