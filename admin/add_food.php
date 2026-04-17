<?php
/**
 * ============================================
 * ADMIN - ADD NEW FOOD ITEM PAGE
 * Online Food Delivery System
 * ============================================
 * Allows admin to add a new food item to the
 * menu with the following fields:
 * - Food Name
 * - Price
 * - Description
 * - Status (Available / Unavailable)
 * ============================================
 */

// Auth check — only owner can access
include '../includes/auth_check.php';

// Database connection
include '../config/db.php';

// Initialize variables
$name = '';
$price = '';
$description = '';
$status = 'available';
$error = '';
$success = '';

/**
 * ----------------------------------------
 * HANDLE ADD FOOD FORM SUBMISSION
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_food'])) {

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

        // Check if food with same name already exists
        $check_query = "SELECT id FROM food WHERE name = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "A food item with this name already exists. Please use a different name.";
        } else {
            // Insert new food item
            $insert_query = "INSERT INTO food (name, price, description, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "sdss", $name, $price, $description, $status);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Food item \"" . htmlspecialchars($name) . "\" has been added successfully!";
                // Clear form fields
                $name = '';
                $price = '';
                $description = '';
                $status = 'available';
            } else {
                $error = "Failed to add food item. Please try again.";
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
    <h2 class="page-title">&#10010; Add New Food Item</h2>

    <!-- ====== Success Message ====== -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            &#9989; <?php echo $success; ?>
            <div style="margin-top: 10px;">
                <a href="/online-food-delivery/admin/manage_food.php" style="color: #065f46; font-weight: 600;">
                    &#127860; View All Food Items
                </a>
                &nbsp; | &nbsp;
                <a href="/online-food-delivery/admin/add_food.php" style="color: #065f46; font-weight: 600;">
                    &#10010; Add Another Item
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

    <!-- ====== Add Food Form ====== -->
    <div class="food-form">
        <h3>&#127860; Food Item Details</h3>

        <form method="POST" action="">

            <!-- Food Name -->
            <div class="form-group">
                <label for="name">Food Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter food name (e.g., Chicken Burger)"
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
                    placeholder="Enter price (e.g., 250.00)"
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
                    placeholder="Enter a detailed description of the food item..."
                    required
                    minlength="10"
                    maxlength="500"
                    rows="4"
                ><?php echo htmlspecialchars($description); ?></textarea>
                <p class="password-hint">Minimum 10 characters, maximum 500 characters. Describe the food item clearly.</p>
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

            <!-- Preview Card -->
            <?php if (!empty($name) || !empty($price) || !empty($description)): ?>
                <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #e74c3c;">
                    <h4 style="font-size: 0.95rem; color: #2d3436; margin-bottom: 10px;">&#128065; Preview</h4>
                    <div class="card" style="margin: 0; animation: none;">
                        <div style="margin-bottom: 10px;">
                            <span style="font-size: 2.2rem;">&#127860;</span>
                        </div>
                        <h3><?php echo !empty($name) ? htmlspecialchars($name) : 'Food Name'; ?></h3>
                        <p class="description">
                            <?php echo !empty($description) ? htmlspecialchars($description) : 'Food description will appear here...'; ?>
                        </p>
                        <div class="price">
                            &#2547; <?php echo !empty($price) ? number_format(floatval($price), 2) : '0.00'; ?>
                        </div>
                        <span class="badge <?php echo ($status === 'available') ? 'badge-delivered' : 'badge-pending'; ?>">
                            <?php echo ($status === 'available') ? '&#9989; Available' : '&#10060; Unavailable'; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 12px;">
                <button type="submit" name="add_food" class="btn btn-success" style="flex: 1; padding: 14px;">
                    &#10010; Add Food Item
                </button>
                <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-danger" style="padding: 14px 24px;">
                    &#10005; Cancel
                </a>
            </div>

        </form>
    </div>

    <!-- ====== Tips Section ====== -->
    <div style="margin-top: 25px; background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); animation: fadeIn 0.8s ease-out;">
        <h3 class="section-title">&#128161; <span>Tips</span> for Adding Food</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #00b894;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#9989; Clear Name</p>
                <p style="font-size: 0.85rem; color: #636e72;">Use a clear, descriptive name that customers can easily understand.</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #0984e3;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128176; Fair Price</p>
                <p style="font-size: 0.85rem; color: #636e72;">Set a competitive price. Check similar items before pricing.</p>
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid #e74c3c;">
                <p style="font-weight: 600; color: #2d3436; margin-bottom: 4px;">&#128221; Good Description</p>
                <p style="font-size: 0.85rem; color: #636e72;">Write a detailed description including ingredients and serving size.</p>
            </div>

        </div>
    </div>

</div>

<?php
// Include footer
include '../includes/footer.php';
?>
