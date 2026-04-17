<?php
/**
 * ============================================
 * CUSTOMER - SHOPPING CART PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all items added to the cart.
 * Customers can:
 * - View cart items
 * - Update quantity
 * - Remove items
 * - Place order
 * Cart is stored in PHP SESSION array.
 * ============================================
 */

// Auth check — only customers can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Initialize messages
$success = '';
$error = '';

/**
 * ----------------------------------------
 * HANDLE: UPDATE QUANTITY
 * ----------------------------------------
 * When customer changes item quantity
 * using + or - buttons (form submit).
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {

    $food_id = intval($_POST['food_id']);
    $action = $_POST['action']; // 'increase' or 'decrease'

    foreach ($_SESSION['cart'] as $key => &$cart_item) {
        if ($cart_item['food_id'] == $food_id) {
            if ($action === 'increase') {
                $cart_item['quantity'] += 1;
                $success = htmlspecialchars($cart_item['name']) . " quantity increased.";
            } elseif ($action === 'decrease') {
                $cart_item['quantity'] -= 1;
                // Remove item if quantity reaches 0
                if ($cart_item['quantity'] <= 0) {
                    $removed_name = $cart_item['name'];
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
                    $success = htmlspecialchars($removed_name) . " removed from cart.";
                } else {
                    $success = htmlspecialchars($cart_item['name']) . " quantity decreased.";
                }
            }
            break;
        }
    }
    unset($cart_item); // Break reference
}

/**
 * ----------------------------------------
 * HANDLE: REMOVE ITEM
 * ----------------------------------------
 * Removes a specific item from the cart.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {

    $food_id = intval($_POST['food_id']);

    foreach ($_SESSION['cart'] as $key => $cart_item) {
        if ($cart_item['food_id'] == $food_id) {
            $removed_name = $cart_item['name'];
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
            $success = htmlspecialchars($removed_name) . " has been removed from your cart.";
            break;
        }
    }
}

/**
 * ----------------------------------------
 * HANDLE: CLEAR ENTIRE CART
 * ----------------------------------------
 * Removes all items from the cart.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = array();
    $success = "Your cart has been cleared.";
}

/**
 * ----------------------------------------
 * HANDLE: PLACE ORDER
 * ----------------------------------------
 * Creates a new order in the database with
 * all cart items as order_items.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    // Check if cart is not empty
    if (count($_SESSION['cart']) === 0) {
        $error = "Your cart is empty. Please add items before placing an order.";
    } else {

        $customer_id = $_SESSION['user_id'];

        // Calculate total price
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        // Verify all items still exist and are available
        $all_available = true;
        foreach ($_SESSION['cart'] as $item) {
            $check_query = "SELECT id FROM food WHERE id = ? AND status = 'available'";
            $stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($stmt, "i", $item['food_id']);
            mysqli_stmt_execute($stmt);
            $check_result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($check_result) === 0) {
                $all_available = false;
                $error = htmlspecialchars($item['name']) . " is no longer available. Please remove it from your cart.";
                break;
            }
            mysqli_stmt_close($stmt);
        }

        if ($all_available) {

            // Insert order into orders table
            $order_query = "INSERT INTO orders (customer_id, status, total_price) VALUES (?, 'Pending', ?)";
            $stmt = mysqli_prepare($conn, $order_query);
            mysqli_stmt_bind_param($stmt, "id", $customer_id, $total_price);

            if (mysqli_stmt_execute($stmt)) {

                // Get the new order ID
                $order_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                // Insert each cart item into order_items table
                $item_query = "INSERT INTO order_items (order_id, food_id, quantity) VALUES (?, ?, ?)";

                foreach ($_SESSION['cart'] as $item) {
                    $stmt = mysqli_prepare($conn, $item_query);
                    mysqli_stmt_bind_param($stmt, "iii", $order_id, $item['food_id'], $item['quantity']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }

                // Clear the cart after successful order
                $_SESSION['cart'] = array();

                // Redirect to payment page
                header("Location: /online-food-delivery/customer/payment.php?order_id=" . $order_id);
                exit();

            } else {
                $error = "Failed to place order. Please try again.";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

/**
 * ----------------------------------------
 * CALCULATE CART TOTALS
 * ----------------------------------------
 */
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Page Title ====== -->
    <div class="flex-between">
        <h2 class="page-title">&#128722; Shopping Cart</h2>
        <a href="/online-food-delivery/customer/menu.php" class="btn btn-info btn-sm">
            &#128213; Continue Shopping
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

    <?php if (count($_SESSION['cart']) > 0): ?>

        <!-- ====== Cart Container ====== -->
        <div class="cart-container">

            <!-- Cart Header -->
            <div class="flex-between mb-20">
                <p style="font-size: 0.9rem; color: #636e72;">
                    &#128230; <?php echo $cart_count; ?> item(s) in your cart
                </p>
                <form method="POST" action="">
                    <button type="submit" name="clear_cart" class="btn btn-danger btn-sm">
                        &#128465; Clear Cart
                    </button>
                </form>
            </div>

            <!-- Cart Items List -->
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <?php $item_total = $item['price'] * $item['quantity']; ?>

                <div class="cart-item">
                    <!-- Item Info -->
                    <div class="item-info" style="flex: 1;">
                        <h4>&#127860; <?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Unit Price: &#2547; <?php echo number_format($item['price'], 2); ?></p>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="qty-control">
                        <!-- Decrease Button -->
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="food_id" value="<?php echo $item['food_id']; ?>">
                            <input type="hidden" name="action" value="decrease">
                            <button type="submit" name="update_qty" class="qty-btn">&#8722;</button>
                        </form>

                        <!-- Quantity Display -->
                        <span class="qty-value"><?php echo $item['quantity']; ?></span>

                        <!-- Increase Button -->
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="food_id" value="<?php echo $item['food_id']; ?>">
                            <input type="hidden" name="action" value="increase">
                            <button type="submit" name="update_qty" class="qty-btn">&#43;</button>
                        </form>
                    </div>

                    <!-- Item Total Price -->
                    <div class="item-price" style="min-width: 120px; text-align: right;">
                        &#2547; <?php echo number_format($item_total, 2); ?>
                    </div>

                    <!-- Remove Button -->
                    <div class="item-actions">
                        <form method="POST" action="">
                            <input type="hidden" name="food_id" value="<?php echo $item['food_id']; ?>">
                            <button type="submit" name="remove_item" class="btn btn-danger btn-sm">
                                &#128465;
                            </button>
                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

            <!-- ====== Cart Summary ====== -->
            <div class="payment-summary" style="margin-top: 25px;">
                <h3 style="font-size: 1.1rem; color: #2d3436; margin-bottom: 15px;">
                    &#128203; Order Summary
                </h3>

                <!-- Item Breakdown -->
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="summary-row">
                        <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                        <span>&#2547; <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <!-- Subtotal -->
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>&#2547; <?php echo number_format($cart_total, 2); ?></span>
                </div>

                <!-- Delivery Fee -->
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span style="color: #00b894; font-weight: 600;">FREE</span>
                </div>

                <!-- Total -->
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>&#2547; <?php echo number_format($cart_total, 2); ?></span>
                </div>
            </div>

            <!-- ====== Place Order Button ====== -->
            <div style="margin-top: 20px;">
                <form method="POST" action="">
                    <button type="submit" name="place_order" class="btn btn-success btn-block" style="padding: 16px; font-size: 1.1rem;">
                        &#128179; Place Order — &#2547; <?php echo number_format($cart_total, 2); ?>
                    </button>
                </form>
                <p style="text-align: center; margin-top: 10px; font-size: 0.82rem; color: #b2bec3;">
                    You will be redirected to the payment page after placing your order.
                </p>
            </div>

        </div>

    <?php else: ?>

        <!-- ====== Empty Cart State ====== -->
        <div class="empty-state">
            <span class="empty-icon">&#128722;</span>
            <p>Your cart is empty!</p>
            <p style="font-size: 0.9rem; color: #b2bec3; margin-top: 5px;">
                Browse our menu and add some delicious food to your cart.
            </p>
            <a href="/online-food-delivery/customer/menu.php" class="btn btn-primary mt-20">
                &#128213; Browse Menu
            </a>
        </div>

    <?php endif; ?>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
