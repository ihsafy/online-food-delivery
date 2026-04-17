<?php
/**
 * ============================================
 * CUSTOMER - FOOD MENU PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all available food items as cards.
 * Customers can browse and add items to cart.
 * No images used — text-based card layout.
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
 * HANDLE ADD TO CART
 * ----------------------------------------
 * When customer clicks "Add to Cart" button,
 * the food item is added to the session cart.
 * If item already exists, quantity is increased.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $food_id = intval($_POST['food_id']);

    // Verify food item exists and is available
    $query = "SELECT id, name, price FROM food WHERE id = ? AND status = 'available'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $food_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $food = mysqli_fetch_assoc($result);

        // Check if item already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['food_id'] == $food_id) {
                $cart_item['quantity'] += 1;
                $found = true;
                break;
            }
        }
        unset($cart_item); // Break reference

        // If not found, add new item to cart
        if (!$found) {
            $_SESSION['cart'][] = array(
                'food_id' => $food['id'],
                'name' => $food['name'],
                'price' => $food['price'],
                'quantity' => 1
            );
        }

        $success = htmlspecialchars($food['name']) . " has been added to your cart!";
    } else {
        $error = "Sorry, this food item is not available.";
    }

    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * HANDLE SEARCH / FILTER
 * ----------------------------------------
 * Customers can search food items by name.
 * ----------------------------------------
 */
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim(mysqli_real_escape_string($conn, $_GET['search']));
}

/**
 * ----------------------------------------
 * FETCH ALL AVAILABLE FOOD ITEMS
 * ----------------------------------------
 */
if (!empty($search)) {
    $query_food = "SELECT * FROM food WHERE status = 'available' AND name LIKE ? ORDER BY name ASC";
    $stmt = mysqli_prepare($conn, $query_food);
    $search_param = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "s", $search_param);
} else {
    $query_food = "SELECT * FROM food WHERE status = 'available' ORDER BY name ASC";
    $stmt = mysqli_prepare($conn, $query_food);
}

mysqli_stmt_execute($stmt);
$food_items = mysqli_stmt_get_result($stmt);
$total_items = mysqli_num_rows($food_items);

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Page Title ====== -->
    <h2 class="page-title">&#128213; Food Menu</h2>

    <!-- ====== Success Message ====== -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            &#9989; <?php echo $success; ?>
            <a href="/online-food-delivery/customer/cart.php" style="float: right; font-weight: 600; color: #065f46;">
                View Cart &#128722;
            </a>
        </div>
    <?php endif; ?>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- ====== Search Bar ====== -->
    <div style="margin-bottom: 25px; animation: fadeIn 0.5s ease-out;">
        <form method="GET" action="" style="display: flex; gap: 10px; max-width: 500px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <div class="input-wrapper">
                    <span class="input-icon">&#128269;</span>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search for food..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        style="padding-left: 42px;"
                    >
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="/online-food-delivery/customer/menu.php" class="btn btn-danger" style="height: 48px; line-height: 28px;">
                    &#10005; Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ====== Results Info ====== -->
    <div class="flex-between mb-20" style="animation: fadeIn 0.5s ease-out;">
        <p style="color: #636e72; font-size: 0.9rem;">
            <?php if (!empty($search)): ?>
                &#128269; Showing results for "<strong><?php echo htmlspecialchars($search); ?></strong>" 
                — <?php echo $total_items; ?> item(s) found
            <?php else: ?>
                &#127860; Showing all available items — <?php echo $total_items; ?> item(s)
            <?php endif; ?>
        </p>
        <a href="/online-food-delivery/customer/cart.php" class="btn btn-success btn-sm">
            &#128722; Cart (<?php echo count($_SESSION['cart']); ?>)
        </a>
    </div>

    <!-- ====== Food Items Grid ====== -->
    <?php if ($total_items > 0): ?>

        <div class="card-grid">

            <?php 
            $delay = 0;
            while ($food = mysqli_fetch_assoc($food_items)): 
                $delay += 0.1;

                // Check if item is already in cart
                $in_cart = false;
                $cart_qty = 0;
                foreach ($_SESSION['cart'] as $cart_item) {
                    if ($cart_item['food_id'] == $food['id']) {
                        $in_cart = true;
                        $cart_qty = $cart_item['quantity'];
                        break;
                    }
                }
            ?>

            <div class="card" style="animation-delay: <?php echo $delay; ?>s;">

                <!-- Food Category Icon -->
                <div style="margin-bottom: 10px;">
                    <span style="font-size: 2.2rem;">&#127860;</span>
                </div>

                <!-- Food Name -->
                <h3><?php echo htmlspecialchars($food['name']); ?></h3>

                <!-- Food Description -->
                <p class="description">
                    <?php echo htmlspecialchars($food['description']); ?>
                </p>

                <!-- Food Price -->
                <div class="price">
                    &#2547; <?php echo number_format($food['price'], 2); ?>
                </div>

                <!-- Food Status -->
                <div style="margin-bottom: 15px;">
                    <span class="badge badge-delivered">&#9989; Available</span>
                    <?php if ($in_cart): ?>
                        <span class="badge badge-paid" style="margin-left: 5px;">
                            &#128722; In Cart (<?php echo $cart_qty; ?>)
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Button -->
                <form method="POST" action="">
                    <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-block">
                        <?php if ($in_cart): ?>
                            &#10010; Add More
                        <?php else: ?>
                            &#128722; Add to Cart
                        <?php endif; ?>
                    </button>
                </form>
            </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <!-- ====== Empty State ====== -->
        <div class="empty-state">
            <span class="empty-icon">&#128533;</span>
            <?php if (!empty($search)): ?>
                <p>No food items found matching "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                <a href="/online-food-delivery/customer/menu.php" class="btn btn-primary mt-20">
                    &#128213; View All Menu
                </a>
            <?php else: ?>
                <p>No food items are available right now. Please check back later!</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <!-- ====== Cart Summary Bar ====== -->
    <?php if (count($_SESSION['cart']) > 0): ?>
        <?php
        // Calculate cart total
        $cart_total = 0;
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
            $cart_count += $item['quantity'];
        }
        ?>
        <div style="
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #2d3436, #1e293b);
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.15);
            z-index: 999;
            animation: slideUp 0.4s ease-out;
        ">
            <div>
                <span style="font-size: 1.1rem; font-weight: 600;">
                    &#128722; <?php echo $cart_count; ?> item(s) in cart
                </span>
                <span style="margin-left: 15px; font-size: 1.2rem; font-weight: 700; color: #ffeaa7;">
                    &#2547; <?php echo number_format($cart_total, 2); ?>
                </span>
            </div>
            <a href="/online-food-delivery/customer/cart.php" class="btn btn-primary">
                View Cart &#10132;
            </a>
        </div>

        <style>
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    <?php endif; ?>

</div>

<?php
// Close statement
mysqli_stmt_close($stmt);

// Include footer
include '../includes/footer.php';
?>
