<?php
/**
 * ============================================
 * ADMIN - EDIT FOOD ITEM PAGE
 * Online Food Delivery System
 * ============================================
 * Allows admin to edit an existing food item.
 * Fields:
 * - Food Name
 * - Price
 * - Description
 * - Status (Available / Unavailable)
 * 
 * Food ID is passed via GET parameter.
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
 * FETCH EXISTING FOOD ITEM
 * ----------------------------------------
 * Load the food item data from the database
 * to pre-fill the edit form.
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

// Set form values from existing data
$name = $food ? $food['name'] : '';
$price = $food ? $food['price'] : '';
$description = $food ? $food['description'] : '';
$status = $food ? $food['status'] : 'available';

/**
 * ----------------------------------------
 * HANDLE EDIT FOOD FORM SUBMISSION
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_food']) && $food) {

    // Get and sanitize form inputs
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $price = trim($_POST['price']);
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $status = trim(mysqli_real_escape_string($conn, $_POST['status']));

    // ---- Validation ----

    // Check empty fields
    if (empty($name) || empty($price) || empty($description)) {
        $error = "All fields are required. Please fill in every field.";
    }
    // Validate name length
    elseif (strlen($name) < 3) {
        $error = "Food name must be at least 3 characters long.";
    }
    elseif (strlen($name) > 150) {
        $error = "Food name cannot exceed 150 characters.";
    }
    // Validate price
    elseif (!is_numeric($price) || floatval($price) <= 0) {
        $error = "Please enter a valid price greater than 0.";
    }
    elseif (floatval($price) > 99999.99) {
        $error = "Price cannot exceed 99,999.99.";
    }
    // Validate description length
    elseif (strlen($description) < 10) {
        $error = "Description must be at least 10 characters long.";
    }
    elseif (strlen($description) > 500) {
        $error = "Description cannot exceed 500 characters.";
    }
    // Validate status
    elseif (!in_array($status, ['available', 'unavailable'])) {
        $error = "Invalid status selected.";
    }
    else {
        // Convert price to float
        $price = floatval($price);

        // Check if another food item with the same name exists (exclude current item)
        $check_query = "SELECT id FROM food WHERE name = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "si", $name, $food_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Another food item with this name already exists. Please use a different name.";
        } else {
            // Update food item in database
            $update_query = "UPDATE food SET name = ?, price = ?, description = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sdssi", $name, $price, $description, $status, $food_id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Food item \"" . htmlspecialchars($name) . "\" has been updated successfully!";

                // Refresh food data
                $food['name'] = $name;
                $food['price'] = $price;
                $food['description'] = $description;
                $food['status'] = $status;
            } else {
                $error = "Failed to update food item. Please try again.";
            }
        }
        mysqli_stmt_close($stmt);
    }
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
    <h2 class="page-title">&#9998; Edit Food Item</h2>

    <!-- ====== Success Message ====== -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            &#9989; <?php echo $success; ?>
            <div style="margin-top: 10px;">
                <a href="/online-food-delivery/admin/manage_food.php" style="color: #065f46; font-weight: 600;">
                    &#127860; View All Food Items
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ====== Error Message ====== -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            &#9888; <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($food): ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">

            <!-- ====== LEFT: Edit Form ====== -->
            <div class="food-form" style="max-width: 100%;">
                <h3>&#127860; Update Food Details</h3>

                <form method="POST" action="">

                    <!-- Food Name -->
                    <div class="form-group">
                        <label for="name">Food Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Enter food name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            required
                            minlength="3"
                            maxlength="150"
                        >
                        <p class="password-hint">Minimum 3 characters, maximum 150 characters</p>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label for="price">Price (&#2547;) *</label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            placeholder="Enter price"
                            value="<?php echo htmlspecialchars($price); ?>"
                            required
                            min="1"
                            max="99999.99"
                            step="0.01"
                        >
                        <p class="password-hint">Enter price in Taka (&#2547;). Must be greater than 0.</p>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            placeholder="Enter food description..."
                            required
                            minlength="10"
                            maxlength="500"
                            rows="4"
                        ><?php echo htmlspecialchars($description); ?></textarea>
                        <p class="password-hint">Minimum 10 characters, maximum 500 characters.</p>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label>Availability Status *</label>
                        <div style="display: flex; gap: 15px; margin-top: 5px;">

                            <!-- Available -->
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 20px; border: 2px solid #dfe6e9; border-radius: 10px; transition: all 0.3s ease; <?php echo ($status === 'available') ? 'border-color: #00b894; background: #d1fae5;' : ''; ?>">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="available"
                                    <?php echo ($status === 'available') ? 'checked' : ''; ?>
                                >
                                <span style="font-weight: 600; color: <?php echo ($status === 'available') ? '#00b894' : '#636e72'; ?>;">
                                    &#9989; Available
                                </span>
                            </label>

                            <!-- Unavailable -->
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 20px; border: 2px solid #dfe6e9; border-radius: 10px; transition: all 0.3s ease; <?php echo ($status === 'unavailable') ? 'border-color: #fdcb6e; background: #ffeaa7;' : ''; ?>">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="unavailable"
                                    <?php echo ($status === 'unavailable') ? 'checked' : ''; ?>
                                >
                                <span style="font-weight: 600; color: <?php echo ($status === 'unavailable') ? '#856404' : '#636e72'; ?>;">
                                    &#10060; Unavailable
                                </span>
                            </label>

                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div style="display: flex; gap: 12px;">
                        <button type="submit" name="update_food" class="btn btn-success" style="flex: 1; padding: 14px;">
                            &#9989; Update Food Item
                        </button>
                        <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-danger" style="padding: 14px 24px;">
                            &#10005; Cancel
                        </a>
                    </div>

                </form>
            </div>

            <!-- ====== RIGHT: Preview & Info ====== -->
            <div style="animation: fadeIn 0.7s ease-out;">

                <!-- Current Preview -->
                <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px;">
                    <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #f0f0f0;">
                        &#128065; Live Preview
                    </h4>
                    <div class="card" style="margin: 0; animation: none;">
                        <div style="margin-bottom: 10px;">
                            <span style="font-size: 2.2rem;">&#127860;</span>
                        </div>
                        <h3><?php echo htmlspecialchars($name); ?></h3>
                        <p class="description">
                            <?php echo htmlspecialchars($description); ?>
                        </p>
                        <div class="price">
                            &#2547; <?php echo number_format(floatval($price), 2); ?>
                        </div>
                        <span class="badge <?php echo ($status === 'available') ? 'badge-delivered' : 'badge-pending'; ?>">
                            <?php echo ($status === 'available') ? '&#9989; Available' : '&#10060; Unavailable'; ?>
                        </span>
                    </div>
                </div>

                <!-- Food Item Info -->
                <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px;">
                    <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 12px;">
                        &#128203; Item Information
                    </h4>
                    <div style="font-size: 0.85rem; color: #636e72; line-height: 2;">
                        <p><strong>Food ID:</strong> #<?php echo $food['id']; ?></p>
                        <p><strong>Created:</strong> <?php echo date("F d, Y - h:i A", strtotime($food['created_at'])); ?></p>
                        <p><strong>Current Status:</strong> 
                            <span class="badge <?php echo ($food['status'] === 'available') ? 'badge-delivered' : 'badge-pending'; ?>">
                                <?php echo htmlspecialchars($food['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div style="background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                    <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 12px;">
                        &#9889; Quick Actions
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">

                        <!-- Toggle Status -->
                        <form method="POST" action="/online-food-delivery/admin/manage_food.php" style="display: inline;">
                            <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                            <?php if ($food['status'] === 'available'): ?>
                                <input type="hidden" name="new_status" value="unavailable">
                                <button type="submit" name="toggle_status" class="btn btn-warning btn-block btn-sm">
                                    &#10060; Mark as Unavailable
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="new_status" value="available">
                                <button type="submit" name="toggle_status" class="btn btn-success btn-block btn-sm">
                                    &#9989; Mark as Available
                                </button>
                            <?php endif; ?>
                        </form>

                        <!-- Delete -->
                        <a href="/online-food-delivery/admin/delete_food.php?id=<?php echo $food['id']; ?>" 
                           class="btn btn-danger btn-block btn-sm">
                            &#128465; Delete This Item
                        </a>

                        <!-- View All -->
                        <a href="/online-food-delivery/admin/manage_food.php" 
                           class="btn btn-info btn-block btn-sm">
                            &#127860; View All Food Items
                        </a>

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
