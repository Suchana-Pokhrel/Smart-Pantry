<?php

include '../include/db.php';

// Fetch distinct categories from items table
$category_query = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != '' ORDER BY category";
$category_result = $conn->query($category_query) or die("Error fetching categories: " . $conn->error);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Check if login modal should be shown (passed from dashboard.php)
$show_login_modal = isset($_GET['show_login']) && $_GET['show_login'] === 'true';

?>

<div class="modals" style="background-color: white;">
    <h4 class="dashboard-title">Quick Actions</h4>
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    <div class="action-buttons">
        <?php if ($is_logged_in): ?>
            <button type="button" class="btn btn-warning action-btn" data-bs-toggle="modal" data-bs-target="#addItemModal" aria-label="Add new inventory item">Add Item</button>
            <a href="recipes.php?suggest=true" class="btn btn-warning action-btn" aria-label="Suggest a recipe">Suggest Recipe</a>
            <a href="../add_to_cart.php" class="btn btn-warning action-btn" aria-label="Order supplies">Order Supplies</a>
        <?php else: ?>
            <a href="dashboard.php?show_login=true" class="btn btn-warning action-btn" aria-label="Add new inventory item">Add Item</a>
            <a href="dashboard.php?show_login=true" class="btn btn-warning action-btn" aria-label="Suggest a recipe">Suggest Recipe</a>
            <a href="dashboard.php?show_login=true" class="btn btn-warning action-btn" aria-label="Order supplies">Order Supplies</a>
        <?php endif; ?>
    </div>

    <!-- Login Prompt Modal -->
    <div class="modal fade <?= $show_login_modal ? 'show' : '' ?>" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptModalLabel" aria-hidden="<?= $show_login_modal ? 'false' : 'true' ?>" style="<?= $show_login_modal ? 'display: block;' : '' ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginPromptModalLabel">Login Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please Log In First.</p>
                </div>
                <div class="modal-footer">
                    <a href="../user_login.php" class="btn btn-warning">Log In</a>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal (only accessible if logged in) -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_items.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="itemName" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="expiry" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry" name="expiry_date">
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required onchange="toggleCustomCategory()">
                                <option value="">Select Category</option>
                                <?php while ($cat = $category_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                                <?php endwhile; ?>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="customCategory" name="custom_category" placeholder="Enter new category" style="display: none;">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Item Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-warning modal-submit-btn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomCategory() {
        const categorySelect = document.getElementById('category');
        const customCategoryInput = document.getElementById('customCategory');
        customCategoryInput.style.display = categorySelect.value === 'Other' ? 'block' : 'none';
        customCategoryInput.required = categorySelect.value === 'Other';
    }
</script>

<style>
    .modal-submit-btn {
        margin: 0;
        border-radius: 0;
    }

    .btn-warning {
        background-color: #ff6200;
        border-color: #ff6200;
    }

    .btn-warning:hover {
        background-color: #e65b00;
        border-color: #e65b00;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .alert {
        margin-bottom: 20px;
    }

    .modal.show {
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>