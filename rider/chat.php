<?php
/**
 * ============================================
 * RIDER - CHAT WITH CUSTOMER PAGE
 * Online Food Delivery System
 * ============================================
 * Chat system between rider and customer.
 * Features:
 * - Based on order_id
 * - Messages stored in database
 * - Page refresh required (no real-time)
 * - Rider messages on RIGHT (sent)
 * - Customer messages on LEFT (received)
 * - Auto-refresh using meta tag
 * ============================================
 */

// Auth check — only riders can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Get rider ID from session
$rider_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Initialize
$error = '';
$success = '';

/**
 * ----------------------------------------
 * FETCH ORDER & VERIFY ACCESS
 * ----------------------------------------
 * Verify order exists, is assigned to this
 * rider, and is in an active status.
 * ----------------------------------------
 */
$order = null;
$customer = null;

if ($order_id > 0) {
    $query = "SELECT o.*, c.name as customer_name, c.id as customer_user_id, c.email as customer_email
              FROM orders o
              JOIN users c ON o.customer_id = c.id
              WHERE o.id = ? AND o.rider_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $rider_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $order = mysqli_fetch_assoc($result);

        // Check if order is in a chattable status
        if (!in_array($order['status'], ['Accepted', 'Picked Up', 'On the Way', 'Delivered'])) {
            $error = "Chat is only available for orders that have been accepted. Please accept the order first.";
            $order = null;
        } else {
            $customer = array(
                'id' => $order['customer_user_id'],
                'name' => $order['customer_name'],
                'email' => $order['customer_email']
            );
        }
    } else {
        $error = "Order not found or not assigned to you.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Invalid order ID.";
}

/**
 * ----------------------------------------
 * HANDLE SEND MESSAGE
 * ----------------------------------------
 * Rider sends a message to the customer.
 * Message is stored in the messages table.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && $order && $customer) {

    $message = trim($_POST['message']);

    if (empty($message)) {
        $error = "Please type a message before sending.";
    } elseif (strlen($message) > 500) {
        $error = "Message is too long. Maximum 500 characters allowed.";
    } else {
        $insert_query = "INSERT INTO messages (sender_id, receiver_id, order_id, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiis", $rider_id, $customer['id'], $order_id, $message);

        if (mysqli_stmt_execute($stmt)) {
            // Redirect to prevent form resubmission on refresh
            header("Location: /online-food-delivery/rider/chat.php?order_id=" . $order_id . "&sent=1");
            exit();
        } else {
            $error = "Failed to send message. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Check for sent message confirmation
if (isset($_GET['sent']) && $_GET['sent'] == 1) {
    $success = "Message sent successfully!";
}

/**
 * ----------------------------------------
 * FETCH ALL MESSAGES FOR THIS ORDER
 * ----------------------------------------
 * Get all messages between rider and customer
 * for this specific order, ordered by time.
 * ----------------------------------------
 */
$messages = array();

if ($order && $customer) {
    $msg_query = "SELECT m.*, u.name as sender_name, u.role as sender_role
                  FROM messages m
                  JOIN users u ON m.sender_id = u.id
                  WHERE m.order_id = ?
                  AND (
                      (m.sender_id = ? AND m.receiver_id = ?)
                      OR
                      (m.sender_id = ? AND m.receiver_id = ?)
                  )
                  ORDER BY m.created_at ASC";
    $stmt = mysqli_prepare($conn, $msg_query);
    mysqli_stmt_bind_param($stmt, "iiiii", $order_id, $rider_id, $customer['id'], $customer['id'], $rider_id);
    mysqli_stmt_execute($stmt);
    $msg_result = mysqli_stmt_get_result($stmt);

    while ($msg = mysqli_fetch_assoc($msg_result)) {
        $messages[] = $msg;
    }
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * FETCH ORDER ITEMS (for reference)
 * ----------------------------------------
 */
$order_items = array();

if ($order) {
    $items_query = "SELECT oi.quantity, f.name as food_name
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

// Include header
include '../includes/header.php';
?>

<!-- Auto-refresh page every 15 seconds for new messages -->
<?php if ($order && $customer): ?>
    <meta http-equiv="refresh" content="15;url=/online-food-delivery/rider/chat.php?order_id=<?php echo $order_id; ?>">
<?php endif; ?>

<div class="container">

    <!-- ====== Back Button ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.4s ease-out;">
        <a href="/online-food-delivery/rider/deliveries.php" class="btn btn-info btn-sm">
            &#8592; Back to Deliveries
        </a>
    </div>

    <!-- ====== Page Title ====== -->
    <h2 class="page-title">&#128172; Chat with Customer</h2>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- ====== Success Message ====== -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            &#9989; <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($order && $customer): ?>

        <!-- ====== Chat Container ====== -->
        <div class="chat-container">

            <!-- Chat Header -->
            <div class="chat-header">
                <div>
                    &#128172; Chat with <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                    <span style="font-size: 0.85rem; opacity: 0.8;">
                        (<?php echo htmlspecialchars($customer['email']); ?>)
                    </span>
                </div>
                <div class="chat-order-id">
                    Order #<?php echo $order_id; ?> &bull; 
                    Status: <?php echo htmlspecialchars($order['status']); ?> &bull;
                    &#128260; Auto-refreshes every 15 seconds
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-messages" id="chatMessages">

                <?php if (count($messages) > 0): ?>

                    <?php foreach ($messages as $msg): ?>
                        <?php
                        // Determine if message is sent (rider) or received (customer)
                        $is_sent = ($msg['sender_id'] == $rider_id);
                        $msg_class = $is_sent ? 'sent' : 'received';
                        $sender_label = $is_sent ? 'You' : htmlspecialchars($msg['sender_name']) . ' (Customer)';
                        ?>

                        <div class="chat-message <?php echo $msg_class; ?>">
                            <div class="chat-bubble">
                                <span class="chat-sender"><?php echo $sender_label; ?></span>
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <span class="chat-time">
                                    <?php echo date("M d, h:i A", strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- No Messages Yet -->
                    <div style="text-align: center; padding: 60px 20px; color: #b2bec3;">
                        <span style="font-size: 3rem; display: block; margin-bottom: 10px;">&#128172;</span>
                        <p style="font-size: 1rem;">No messages yet.</p>
                        <p style="font-size: 0.85rem; margin-top: 5px;">
                            Start the conversation with the customer!
                        </p>
                    </div>

                <?php endif; ?>

            </div>

            <!-- Chat Input Form -->
            <?php if (in_array($order['status'], ['Accepted', 'Picked Up', 'On the Way'])): ?>
                <form method="POST" action="" class="chat-input">
                    <textarea 
                        name="message" 
                        placeholder="Type your message here... (max 500 characters)"
                        maxlength="500"
                        required
                    ></textarea>
                    <button type="submit" name="send_message" class="btn-send">
                        &#10148; Send
                    </button>
                </form>
            <?php else: ?>
                <!-- Chat Closed -->
                <div style="padding: 15px 25px; background: #f8f9fa; text-align: center; color: #636e72; font-size: 0.9rem; border-top: 1px solid #f0f0f0;">
                    &#128274; This chat is closed. Order has been <strong><?php echo htmlspecialchars($order['status']); ?></strong>.
                </div>
            <?php endif; ?>

        </div>

        <!-- ====== Chat Info ====== -->
        <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; animation: fadeIn 0.7s ease-out;">

            <!-- Order Info -->
            <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 12px;">
                    &#128230; Order Information
                </h4>
                <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 6px;">
                    <strong>Order ID:</strong> #<?php echo $order['id']; ?>
                </p>
                <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 6px;">
                    <strong>Customer:</strong> <?php echo htmlspecialchars($customer['name']); ?>
                </p>
                <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 6px;">
                    <strong>Status:</strong> 
                    <?php
                    $badge_class = '';
                    switch ($order['status']) {
                        case 'Accepted':   $badge_class = 'badge-accepted'; break;
                        case 'Picked Up':  $badge_class = 'badge-picked'; break;
                        case 'On the Way': $badge_class = 'badge-onway'; break;
                        case 'Delivered':  $badge_class = 'badge-delivered'; break;
                    }
                    ?>
                    <span class="badge <?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </p>
                <p style="font-size: 0.85rem; color: #636e72;">
                    <strong>Total:</strong> 
                    <span style="color: #e74c3c; font-weight: 700;">
                        &#2547; <?php echo number_format($order['total_price'], 2); ?>
                    </span>
                </p>

                <!-- Order Items -->
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f0f0;">
                    <p style="font-weight: 600; font-size: 0.85rem; color: #2d3436; margin-bottom: 6px;">
                        &#127860; Items:
                    </p>
                    <?php foreach ($order_items as $item): ?>
                        <p style="font-size: 0.82rem; color: #636e72; margin-bottom: 2px;">
                            &bull; <?php echo htmlspecialchars($item['food_name']); ?> x <?php echo $item['quantity']; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Replies & Tips -->
            <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 12px;">
                    &#9889; Quick Reply Suggestions
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">

                    <form method="POST" action="">
                        <input type="hidden" name="message" value="I have accepted your order. Will pick it up soon!">
                        <button type="submit" name="send_message" style="width: 100%; text-align: left; padding: 10px 14px; background: #f8f9fa; border: 1px solid #dfe6e9; border-radius: 8px; cursor: pointer; font-size: 0.82rem; color: #2d3436; font-family: inherit; transition: all 0.2s ease;">
                            &#128172; "I have accepted your order. Will pick it up soon!"
                        </button>
                    </form>

                    <form method="POST" action="">
                        <input type="hidden" name="message" value="I have picked up your order. On my way now!">
                        <button type="submit" name="send_message" style="width: 100%; text-align: left; padding: 10px 14px; background: #f8f9fa; border: 1px solid #dfe6e9; border-radius: 8px; cursor: pointer; font-size: 0.82rem; color: #2d3436; font-family: inherit; transition: all 0.2s ease;">
                            &#128666; "I have picked up your order. On my way now!"
                        </button>
                    </form>

                    <form method="POST" action="">
                        <input type="hidden" name="message" value="I am near your location. Please be ready!">
                        <button type="submit" name="send_message" style="width: 100%; text-align: left; padding: 10px 14px; background: #f8f9fa; border: 1px solid #dfe6e9; border-radius: 8px; cursor: pointer; font-size: 0.82rem; color: #2d3436; font-family: inherit; transition: all 0.2s ease;">
                            &#128205; "I am near your location. Please be ready!"
                        </button>
                    </form>

                    <form method="POST" action="">
                        <input type="hidden" name="message" value="Your order has been delivered. Enjoy your meal!">
                        <button type="submit" name="send_message" style="width: 100%; text-align: left; padding: 10px 14px; background: #f8f9fa; border: 1px solid #dfe6e9; border-radius: 8px; cursor: pointer; font-size: 0.82rem; color: #2d3436; font-family: inherit; transition: all 0.2s ease;">
                            &#127881; "Your order has been delivered. Enjoy your meal!"
                        </button>
                    </form>

                </div>

                <!-- Chat Tips -->
                <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #f0f0f0;">
                    <h4 style="font-size: 0.88rem; color: #2d3436; margin-bottom: 8px;">
                        &#128161; Tips
                    </h4>
                    <div style="font-size: 0.82rem; color: #636e72; line-height: 1.8;">
                        <p>&#9989; Page auto-refreshes every 15 seconds</p>
                        <p>&#9989; Use quick replies for faster communication</p>
                        <p>&#9989; Keep the customer updated about delivery</p>
                        <p>&#9888; Chat closes after order is delivered</p>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
