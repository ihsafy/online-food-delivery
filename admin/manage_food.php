<?php
/**
 * ============================================
 * ADMIN - MANAGE FOOD ITEMS PAGE
 * Online Food Delivery System
 * ============================================
 * Displays all food items in a table.
 * Admin can:
 * - View all food items
 * - Search food items
 * - Toggle availability status
 * - Edit food items
 * - Delete food items
 * - Add new food items
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
 * HANDLE: TOGGLE FOOD STATUS
 * ----------------------------------------
 * Admin can toggle food between
 * 'available' and 'unavailable'.
 * ----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {

    $food_id = intval($_POST['food_id']);
    $new_status = trim($_POST['new_status']);

    // Validate status
    if (!in_array($new_status, ['available', 'unavailable'])) {
        $error = "Invalid status value.";
    } else {
        $update_query = "UPDATE food SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $food_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Food item status updated to \"" . htmlspecialchars($new_status) . "\" successfully!";
        } else {
            $error = "Failed to update food status. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

/**
 * ----------------------------------------
 * HANDLE: SEARCH
 * ----------------------------------------
 */
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim(mysqli_real_escape_string($conn, $_GET['search']));
}

/**
 * ----------------------------------------
 * HANDLE: STATUS FILTER
 * ----------------------------------------
 */
$filter_status = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['available', 'unavailable'])) {
    $filter_status = $_GET['status'];
}

/**
 * ----------------------------------------
 * FETCH FOOD ITEMS
 * ----------------------------------------
 */
$where_clauses = array();
$params = array();
$types = '';

if (!empty($search)) {
    $where_clauses[] = "name LIKE ?";
    $search_param = "%" . $search . "%";
    $params[] = &$search_param;
    $types .= 's';
}

if (!empty($filter_status)) {
    $where_clauses[] = "status = ?";
    $params[] = &$filter_status;
    $types .= 's';
}

$query = "SELECT * FROM food";
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}
$query .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if (!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$food_items = mysqli_stmt_get_result($stmt);
$total_items = mysqli_num_rows($food_items);

/**
 * ----------------------------------------
 * FETCH FOOD COUNTS
 * ----------------------------------------
 */
$query_count = "SELECT status, COUNT(*) as count FROM food GROUP BY status";
$count_result = mysqli_query($conn, $query_count);
$status_counts = array();
$total_all = 0;
while ($row = mysqli_fetch_assoc($count_result)) {
    $status_counts[$row['status']] = $row['count'];
    $total_all += $row['count'];
}

// Include header
include '../includes/header.php';
?>

<div class="container">

    <!-- ====== Page Title ====== -->
    <div class="flex-between">
        <h2 class="page-title">&#127860; Manage Food Items</h2>
        <a href="/online-food-delivery/admin/add_food.php" class="btn btn-success">
            &#10010; Add New Food
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

    <!-- ====== Search & Filter Bar ====== -->
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; animation: fadeIn 0.5s ease-out;">

        <!-- Search Form -->
        <form method="GET" action="" style="display: flex; gap: 10px; flex: 1; min-width: 300px;">
            <?php if (!empty($filter_status)): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
            <?php endif; ?>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <div class="input-wrapper">
                    <span class="input-icon">&#128269;</span>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search food items..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        style="padding-left: 42px;"
                    >
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">Search</button>
            <?php if (!empty($search) || !empty($filter_status)): ?>
                <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-danger" style="height: 48px; line-height: 28px;">
                    &#10005; Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ====== Status Filter Buttons ====== -->
    <div class="menu-filter">
        <a href="/online-food-delivery/admin/manage_food.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
           class="filter-btn <?php echo (empty($filter_status)) ? 'active' : ''; ?>">
            All (<?php echo $total_all; ?>)
        </a>
        <a href="/online-food-delivery/admin/manage_food.php?status=available<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="filter-btn <?php echo ($filter_status === 'available') ? 'active' : ''; ?>">
            &#9989; Available (<?php echo isset($status_counts['available']) ? $status_counts['available'] : 0; ?>)
        </a>
        <a href="/online-food-delivery/admin/manage_food.php?status=unavailable<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="filter-btn <?php echo ($filter_status === 'unavailable') ? 'active' : ''; ?>">
            &#10060; Unavailable (<?php echo isset($status_counts['unavailable']) ? $status_counts['unavailable'] : 0; ?>)
        </a>
    </div>

    <!-- ====== Results Info ====== -->
    <div style="margin-bottom: 20px; animation: fadeIn 0.5s ease-out;">
        <p style="font-size: 0.9rem; color: #636e72;">
            <?php if (!empty($search)): ?>
                &#128269; Search results for "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php endif; ?>
            <?php if (!empty($filter_status)): ?>
                <?php echo !empty($search) ? ' | ' : ''; ?>
                Filter: <strong><?php echo htmlspecialchars($filter_status); ?></strong>
            <?php endif; ?>
            — <?php echo $total_items; ?> item(s) found
        </p>
    </div>

    <!-- ====== Food Items Table ====== -->
    <?php if ($total_items > 0): ?>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Food Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while ($food = mysqli_fetch_assoc($food_items)): 
                    ?>
                        <tr>
                            <!-- Number -->
                            <td style="font-weight: 600;"><?php echo $count++; ?></td>

                            <!-- Food Name -->
                            <td>
                                <span style="font-weight: 700; color: #2d3436;">
                                    &#127860; <?php echo htmlspecialchars($food['name']); ?>
                                </span>
                            </td>

                            <!-- Description -->
                            <td style="max-width: 250px;">
                                <span style="font-size: 0.82rem; color: #636e72;">
                                    <?php 
                                    $desc = htmlspecialchars($food['description']);
                                    echo (strlen($desc) > 80) ? substr($desc, 0, 80) . '...' : $desc;
                                    ?>
                                </span>
                            </td>

                            <!-- Price -->
                            <td style="font-weight: 700; color: #e74c3c;">
                                &#2547; <?php echo number_format($food['price'], 2); ?>
                            </td>

                            <!-- Status -->
                            <td>
                                <?php if ($food['status'] === 'available'): ?>
                                    <span class="badge badge-delivered">&#9989; Available</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">&#10060; Unavailable</span>
                                <?php endif; ?>
                            </td>

                            <!-- Created Date -->
                            <td>
                                <?php echo date("M d, Y", strtotime($food['created_at'])); ?>
                                <br>
                                <span style="font-size: 0.78rem; color: #b2bec3;">
                                    <?php echo date("h:i A", strtotime($food['created_at'])); ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">

                                    <!-- Toggle Status -->
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                        <?php if ($food['status'] === 'available'): ?>
                                            <input type="hidden" name="new_status" value="unavailable">
                                            <button type="submit" name="toggle_status" class="btn btn-warning btn-sm" title="Mark as Unavailable">
                                                &#10060; Disable
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="available">
                                            <button type="submit" name="toggle_status" class="btn btn-success btn-sm" title="Mark as Available">
                                                &#9989; Enable
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <!-- Edit -->
                                    <a href="/online-food-delivery/admin/edit_food.php?id=<?php echo $food['id']; ?>" 
                                       class="btn btn-info btn-sm" title="Edit Food Item">
                                        &#9998; Edit
                                    </a>

                                    <!-- Delete -->
                                    <a href="/online-food-delivery/admin/delete_food.php?id=<?php echo $food['id']; ?>" 
                                       class="btn btn-danger btn-sm" title="Delete Food Item">
                                        &#128465; Delete
                                    </a>

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
            <?php if (!empty($search) || !empty($filter_status)): ?>
                <span class="empty-icon">&#128269;</span>
                <p>No food items found matching your criteria.</p>
                <a href="/online-food-delivery/admin/manage_food.php" class="btn btn-info mt-20">
                    &#127860; View All Food Items
                </a>
            <?php else: ?>
                <span class="empty-icon">&#127860;</span>
                <p>No food items have been added yet.</p>
                <a href="/online-food-delivery/admin/add_food.php" class="btn btn-success mt-20">
                    &#10010; Add First Food Item
                </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <!-- ====== Food Summary ====== -->
    <?php if ($total_all > 0): ?>
        <div style="margin-top: 30px; animation: fadeIn 0.8s ease-out;">
            <h3 class="section-title">&#128202; <span>Food</span> Summary</h3>
            <div class="stats-grid">

                <!-- Total Items -->
                <div class="stat-card" style="border-top: 3px solid #e74c3c;">
                    <span class="stat-icon">&#127860;</span>
                    <div class="stat-number"><?php echo $total_all; ?></div>
                    <div class="stat-label">Total Items</div>
                </div>

                <!-- Available -->
                <div class="stat-card" style="border-top: 3px solid #00b894;">
                    <span class="stat-icon">&#9989;</span>
                    <div class="stat-number"><?php echo isset($status_counts['available']) ? $status_counts['available'] : 0; ?></div>
                    <div class="stat-label">Available</div>
                </div>

                <!-- Unavailable -->
                <div class="stat-card" style="border-top: 3px solid #fdcb6e;">
                    <span class="stat-icon">&#10060;</span>
                    <div class="stat-number"><?php echo isset($status_counts['unavailable']) ? $status_counts['unavailable'] : 0; ?></div>
                    <div class="stat-label">Unavailable</div>
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
