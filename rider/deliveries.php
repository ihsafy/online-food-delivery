<?php
/**
 * ============================================
 * RIDER - DELIVERIES PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all deliveries assigned to the rider.
 * Features:
 * - Filter by status
 * - Accept orders (Paid → Accepted)
 * - Update delivery status:
 *     Accepted → Picked Up → On the Way → Delivered
 * - Chat with customer
 * - View order items
 * ============================================
 */

// Auth check — only riders can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get rider ID from session
$rider_id = $_SESSION['user_id'];

// Initialize messages
$success = '';
$error = '';

/**
 * ----------------------------------------
 * HANDLE: STATUS UPDATE
 * ----------------------------------------
 * Rider can update order status step by step:
 * Paid → Accepted → Picked Up → On the Way → Delivered
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {

    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['new_status']);

    // Valid status transitions
    $valid_transitions = array(
        'Paid'       => 'Accepted',
        'Accepted'   => 'Picked Up',
        'Picked Up'  => 'On the Way',
        'On the Way' => 'Delivered'
    );

    // Verify order belongs to this rider
    $verify_query = "SELECT id, status FROM orders WHERE id = ? AND rider_id = ?";
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $rider_id);
    mysqli_stmt_execute($stmt);
    $verify_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($verify_result) === 1) {
        $order = mysqli_fetch_assoc($verify_result);
        $current_status = $order['status'];

        // Check if the transition is valid
        if (isset($valid_transitions[$current_status]) && $valid_transitions[$current_status] === $new_status) {

            // Update the order status
            $update_query = "UPDATE orders SET status = ? WHERE id = ? AND rider_id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "sii", $new_status, $order_id, $rider_id);

            if (mysqli_stmt_execute($stmt_update)) {
                $success = "Order #" . $order_id . " status updated to \"" . htmlspecialchars($new_status) . "\" successfully!";
            } else {
                $error = "Failed to update order status. Please try again.";
            }
            mysqli_stmt_close($stmt_update);

        } else {
            $error = "Invalid status transition. Cannot change from \"" . htmlspecialchars($current_status) . "\" to \"" . htmlspecialchars($new_status) . "\".";
        }
    } else {
        $error = "Order not found or not assigned to you.";
    }
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * HANDLE STATUS FILTER
 * ----------------------------------------
 */
$filter_status = '';
$valid_statuses = array('Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');

if (isset($_GET['status']) && in_array($_GET['status'], $valid_statuses)) {
    $filter_status = $_GET['status'];
}

/**
 * ----------------------------------------
 * FETCH ALL DELIVERIES FOR THIS RIDER
 * ----------------------------------------
 */
if (!empty($filter_status)) {
    $query = "SELECT o.*, c.name as customer_name, c.email as customer_email
              FROM orders o
              JOIN users c ON o.customer_id = c.id
              WHERE o.rider_id = ? AND o.status = ?
              ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $rider_id, $filter_status);
} else {
    $query = "SELECT o.*, c.name as customer_name, c.email as customer_email
              FROM orders o
              JOIN users c ON o.customer_id = c.id
              WHERE o.rider_id = ?
              ORDER BY 
                  CASE o.status
                      WHEN 'Paid' THEN 1
                      WHEN 'Accepted' THEN 2
                      WHEN 'Picked Up' THEN 3
                      WHEN 'On the Way' THEN 4
                      WHEN 'Delivered' THEN 5
                  END ASC,
                  o.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $rider_id);
}

mysqli_stmt_execute($stmt);
$deliveries = mysqli_stmt_get_result($stmt);
$total_deliveries = mysqli_num_rows($deliveries);

/**
 * ----------------------------------------
 * FETCH STATUS COUNTS
 * ----------------------------------------
 */
$count_query = "SELECT status, COUNT(*) as count FROM orders WHERE rider_id = ? GROUP BY status";
$stmt_count = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($stmt_count, "i", $rider_id);
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
 * HELPER: Get badge class
 * ----------------------------------------
 */
function getDeliveryBadgeClass($status) {
    switch ($status) {
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
        <h2 class="page-title">&#128666; My Deliveries</h2>
        <a href="/online-food-delivery/rider/dashboard.php" class="btn btn-info btn-sm">
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

    <!-- ====== Status Filter Buttons ====== -->
    <div class="menu-filter">
        <a href="/online-food-delivery/rider/deliveries.php" 
           class="filter-btn <?php echo (empty($filter_status)) ? 'active' : ''; ?>">
            All (<?php echo $total_all; ?>)
        </a>

        <?php foreach ($valid_statuses as $status): ?>
            <?php $count = isset($status_counts[$status]) ? $status_counts[$status] : 0; ?>
            <a href="/online-food-delivery/rider/deliveries.php?status=<?php echo urlencode($status); ?>" 
               class="filter-btn <?php echo ($filter_status === $status) ? 'active' : ''; ?>">
                <?php echo $status; ?> (<?php echo $count; ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ====== Filter Info ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.5s ease-out;">
        <p style="font-size: 0.9rem; color: #636e72;">
            <?php if (!empty($filter_status)): ?>
                &#128269; Showing <strong><?php echo htmlspecialchars($filter_status); ?></strong> deliveries 
                — <?php echo $total_deliveries; ?> order(s) found
            <?php else: ?>
                &#128666; Showing all deliveries — <?php echo $total_deliveries; ?> order(s)
                <span style="font-size: 0.82rem; color: #b2bec3;">(sorted by priority)</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- ====== Deliveries List ====== -->
    <?php if ($total_deliveries > 0): ?>

        <?php while ($delivery = mysqli_fetch_assoc($deliveries)): ?>

            <?php
            // Fetch order items for this delivery
            $items_query = "SELECT oi.quantity, f.name as food_name
                            FROM order_items oi
                            JOIN food f ON oi.food_id = f.id
                            WHERE oi.order_id = ?";
            $stmt_items = mysqli_prepare($conn, $items_query);
            mysqli_stmt_bind_param($stmt_items, "i", $delivery['id']);
            mysqli_stmt_execute($stmt_items);
            $items_result = mysqli_stmt_get_result($stmt_items);

            $order_items = array();
            while ($item = mysqli_fetch_assoc($items_result)) {
                $order_items[] = $item;
            }
            mysqli_stmt_close($stmt_items);

            // Determine next status for this order
            $next_status = '';
            $next_btn_class = '';
            $next_btn_label = '';

            switch ($delivery['status']) {
                case 'Paid':
                    $next_status = 'Accepted';
                    $next_btn_class = 'accept';
                    $next_btn_label = '&#9989; Accept Order';
                    break;
                case 'Accepted':
                    $next_status = 'Picked Up';
                    $next_btn_class = 'pickup';
                    $next_btn_label = '&#128230; Mark as Picked Up';
                    break;
                case 'Picked Up':
                    $next_status = 'On the Way';
                    $next_btn_class = 'onway';
                    $next_btn_label = '&#128666; Mark as On the Way';
                    break;
                case 'On the Way':
                    $next_status = 'Delivered';
                    $next_btn_class = 'deliver';
                    $next_btn_label = '&#127881; Mark as Delivered';
                    break;
            }
            ?>

            <div class="delivery-card">

                <!-- Delivery Header -->
                <div class="delivery-header">
                    <span style="font-size: 1.1rem; font-weight: 700; color: #2d3436;">
                        &#128230; Order #<?php echo $delivery['id']; ?>
                    </span>
                    <span class="badge <?php echo getDeliveryBadgeClass($delivery['status']); ?>">
                        <?php echo htmlspecialchars($delivery['status']); ?>
                    </span>
                </div>

                <!-- Delivery Info -->
                <div class="delivery-info">
                    <p>
                        <strong>&#128100; Customer:</strong> 
                        <?php echo htmlspecialchars($delivery['customer_name']); ?>
                        <span style="color: #b2bec3; font-size: 0.82rem;">
                            (<?php echo htmlspecialchars($delivery['customer_email']); ?>)
                        </span>
                    </p>
                    <p>
                        <strong>&#128197; Date:</strong> 
                        <?php echo date("M d, Y - h:i A", strtotime($delivery['created_at'])); ?>
                    </p>
                    <p>
                        <strong>&#128176; Total:</strong> 
                        <span style="color: #e74c3c; font-weight: 700;">
                            &#2547; <?php echo number_format($delivery['total_price'], 2); ?>
                        </span>
                    </p>
                </div>

                <!-- Order Items -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 12px 16px; margin-bottom: 15px;">
                    <p style="font-weight: 600; font-size: 0.88rem; color: #2d3436; margin-bottom: 8px;">
                        &#127860; Order Items:
                    </p>
                    <?php foreach ($order_items as $item): ?>
                        <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 3px;">
                            &bull; <?php echo htmlspecialchars($item['food_name']); ?> 
                            x <?php echo $item['quantity']; ?>
                        </p>
                    <?php endforeach; ?>
                </div>

                <!-- Delivery Status Tracker (Mini) -->
                <div style="display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap;">
                    <?php
                    $all_statuses = array('Paid', 'Accepted', 'Picked Up', 'On the Way', 'Delivered');
                    $current_index = array_search($delivery['status'], $all_statuses);

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

                <!-- Action Buttons -->
                <div class="delivery-actions">

                    <!-- Status Update Button -->
                    <?php if (!empty($next_status)): ?>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $delivery['id']; ?>">
                            <input type="hidden" name="new_status" value="<?php echo $next_status; ?>">
                            <button type="submit" name="update_status" class="status-btn <?php echo $next_btn_class; ?>">
                                <?php echo $next_btn_label; ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Chat with Customer (if active) -->
                    <?php if (in_array($delivery['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                        <a href="/online-food-delivery/rider/chat.php?order_id=<?php echo $delivery['id']; ?>" 
                           class="btn btn-primary btn-sm">
                            &#128172; Chat with Customer
                        </a>
                    <?php endif; ?>

                    <!-- Delivered Badge -->
                    <?php if ($delivery['status'] === 'Delivered'): ?>
                        <span style="padding: 8px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            &#127881; Delivery Completed
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
                <p>No <strong><?php echo htmlspecialchars($filter_status); ?></strong> deliveries found.</p>
                <a href="/online-food-delivery/rider/deliveries.php" class="btn btn-info mt-20">
                    &#128666; View All Deliveries
                </a>
            <?php else: ?>
                <span class="empty-icon">&#128666;</span>
                <p>No deliveries assigned to you yet.</p>
                <p style="font-size: 0.9rem; color: #b2bec3; margin-top: 5px;">
                    New orders will appear here once the admin assigns them to you.
                </p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<?php
// Close statement
mysqli_stmt_close($stmt);

// Include footer
include '../includes/footer.php';
?>
