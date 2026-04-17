<?php
/**
 * ============================================
 * ADMIN - DELETE FOOD ITEM PAGE
 * Online Food Delivery System
 * ============================================
 * Displays a confirmation page before deleting
 * a food item from the database.
 * 
 * Two-step process:
 * 1. Show confirmation page with food details
 * 2. On confirm, delete the food item
 * 
 * Also checks if the food item is part of
 * any existing orders before deleting.
 * ============================================
 */

// Auth check — only owner can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Initialize messages
$error = '';
$success = '';

// Get food ID from URL
$food_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/**
 * ----------------------------------------
 * FETCH FOOD ITEM DETAILS
 * ----------------------------------------
 */
$food = null;

if ($food_id > 0) {
    $query = "SELECT * FROM food WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $food_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $food = mysqli_fetch_assoc($result);
    } else {
        $error = "Food item not found.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Invalid food item ID.";
}

/**
 * ----------------------------------------
 * CHECK IF FOOD IS IN ANY ORDERS
 * ----------------------------------------
 * Count how many orders contain this food item.
 * Warn admin but still allow deletion.
 * ----------------------------------------
 */
$order_count = 0;

if ($food) {
    $count_query = "SELECT COUNT(DISTINCT order_id) as total FROM order_items WHERE food_id = ?";
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $food_id);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
    $order_count = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($stmt);
}

/**
 * ----------------------------------------
 * HANDLE DELETE CONFIRMATION
 * ----------------------------------------
 * When admin confirms deletion:
 * 1. Delete order_items referencing this food
 * 2. Delete the food item itself
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $food) {

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        /**
         * STEP 1: Delete order_items referencing this food
         * This prevents foreign key constraint errors.
         */
        $delete_items_query = "DELETE FROM order_items WHERE food_id = ?";
        $stmt = mysqli_prepare($conn, $delete_items_query);
        mysqli_stmt_bind_param($stmt, "i", $food_id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to remove food from existing orders.");
        }
        mysqli_stmt_close($stmt);

        /**
         * STEP 2: Delete the food item
         */
        $delete_food_query = "DELETE FROM food WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_food_query);
        mysqli_stmt_bind_param($stmt, "i", $food_id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete food item.");
        }
        mysqli_stmt_close($stmt);

        // Commit transaction
        mysqli_commit($conn);

        // Redirect to manage food with success message
        header("Location: /online-food-delivery/admin/manage_food.php?deleted=1");
        exit();

    } catch (Exception $e) {
        // Rollback on failure
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

/**
 * ----------------------------------------
 * HANDLE CANCEL
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_delete'])) {
    header("Location: /online-food-delivery/admin/manage_food.php");
    exit();
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Back Button ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.4s ease-out;">
        <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-info btn-sm">
            &#8592; Back to Manage Food
        </a>
    </div>

    <!-- ====== Page Title ====== -->
    <h2 class="page-title">&#128465; Delete Food Item</h2>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($food): ?>

        <!-- ====== Confirmation Box ====== -->
        <div class="confirm-box" style="max-width: 550px;">

            <!-- Warning Icon -->
            <span class="confirm-icon" style="color: #e74c3c;">&#9888;</span>

            <!-- Title -->
            <h3>Are you sure you want to delete this food item?</h3>

            <!-- Warning Message -->
            <p style="color: #636e72;">
                This action <strong>cannot be undone</strong>. The food item will be permanently removed from the system.
            </p>

            <!-- Order Warning -->
            <?php if ($order_count > 0): ?>
                <div style="background: #fff3cd; border-radius: 8px; padding: 14px 18px; margin: 15px 0; text-align: left; font-size: 0.88rem; color: #856404; border-left: 4px solid #ffc107;">
                    &#9888; <strong>Warning:</strong> This food item is part of 
                    <strong><?php echo $order_count; ?></strong> existing order(s). 
                    Deleting it will remove it from those order records as well.
                </div>
            <?php endif; ?>

            <!-- Food Item Details Card -->
            <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: left; border-left: 4px solid #e74c3c;">

                <!-- Food Icon -->
                <div style="text-align: center; margin-bottom: 10px;">
                    <span style="font-size: 2.5rem;">&#127860;</span>
                </div>

                <!-- Food Name -->
                <div style="margin-bottom: 10px;">
                    <span style="font-size: 0.82rem; color: #636e72;">Food Name:</span>
                    <p style="font-weight: 700; font-size: 1.1rem; color: #2d3436;">
                        <?php echo htmlspecialchars($food['name']); ?>
                    </p>
                </div>

                <!-- Description -->
                <div style="margin-bottom: 10px;">
                    <span style="font-size: 0.82rem; color: #636e72;">Description:</span>
                    <p style="font-size: 0.9rem; color: #2d3436;">
                        <?php echo htmlspecialchars($food['description']); ?>
                    </p>
                </div>

                <!-- Price -->
                <div style="margin-bottom: 10px;">
                    <span style="font-size: 0.82rem; color: #636e72;">Price:</span>
                    <p style="font-weight: 700; font-size: 1.2rem; color: #e74c3c;">
                        &#2547; <?php echo number_format($food['price'], 2); ?>
                    </p>
                </div>

                <!-- Status -->
                <div style="margin-bottom: 10px;">
                    <span style="font-size: 0.82rem; color: #636e72;">Status:</span>
                    <p>
                        <?php if ($food['status'] === 'available'): ?>
                            <span class="badge badge-delivered">&#9989; Available</span>
                        <?php else: ?>
                            <span class="badge badge-pending">&#10060; Unavailable</span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Created Date -->
                <div style="margin-bottom: 5px;">
                    <span style="font-size: 0.82rem; color: #636e72;">Created:</span>
                    <p style="font-size: 0.88rem; color: #2d3436;">
                        <?php echo date("F d, Y - h:i A", strtotime($food['created_at'])); ?>
                    </p>
                </div>

                <!-- Order Count -->
                <div>
                    <span style="font-size: 0.82rem; color: #636e72;">Used in Orders:</span>
                    <p style="font-size: 0.88rem; color: #2d3436; font-weight: 600;">
                        <?php echo $order_count; ?> order(s)
                    </p>
                </div>

            </div>

            <!-- Confirmation Buttons -->
            <div class="confirm-actions">
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="confirm_delete" class="btn btn-danger" style="padding: 12px 30px;">
                        &#128465; Yes, Delete Permanently
                    </button>
                </form>

                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="cancel_delete" class="btn btn-info" style="padding: 12px 30px;">
                        &#10005; No, Cancel
                    </button>
                </form>
            </div>

            <!-- Alternative Action -->
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                <p style="font-size: 0.85rem; color: #636e72; margin-bottom: 10px;">
                    &#128161; Instead of deleting, you can mark this item as unavailable:
                </p>
                <?php if ($food['status'] === 'available'): ?>
                    <form method="POST" action="/online-food-delivery/admin/manage_food.php" style="display: inline;">
                        <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                        <input type="hidden" name="new_status" value="unavailable">
                        <button type="submit" name="toggle_status" class="btn btn-warning btn-sm">
                            &#10060; Mark as Unavailable Instead
                        </button>
                    </form>
                <?php else: ?>
                    <p style="font-size: 0.82rem; color: #b2bec3;">
                        This item is already marked as unavailable.
                    </p>
                <?php endif; ?>
            </div>

        </div>

    <?php else: ?>

        <!-- ====== Food Not Found ====== -->
        <?php if (empty($error)): ?>
            <div class="empty-state">
                <span class="empty-icon">&#128533;</span>
                <p>Food item not found.</p>
                <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-info mt-20">
                    &#127860; Back to Manage Food
                </a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
