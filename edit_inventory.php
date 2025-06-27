<?php
session_start();
include 'include/db.php';

// Redirect non-logged-in users to login.php
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to edit items.";
    header("Location: login.php");
    exit;
}

// Check if item ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid item ID.";
    header("Location: inventory.php");
    exit;
}

$item_id = (int)$_GET['id'];

// Fetch item details
$stmt = $conn->prepare("SELECT name, quantity, expiry_date, category, image FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Item not found.";
    header("Location: inventory.php");
    exit;
}
$item = $result->fetch_assoc();
$stmt->close();

// Fetch distinct categories
$category_query = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != '' ORDER BY category";
$category_result = $conn->query($category_query) or die("Error fetching categories: " . $conn->error);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $category = trim($_POST['category']);
    $custom_category = trim($_POST['custom_category']);
    $image = $item['image']; // Default to existing image

    // Use custom category if "Other" is selected
    if ($category === 'Other' && !empty($custom_category)) {
        $category = $custom_category;
    }

    // Handle image upload only if a new file is provided
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = __DIR__ . '/assets/img/products/'; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); 
        }
        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "Upload directory is not writable. Please check permissions.";
            header("Location: edit_inventory.php?id=$item_id");
            exit;
        }

        $image_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5000000;
        if (in_array($image_type, $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $upload_error = $_FILES['image']['error'];
            if ($upload_error === UPLOAD_ERR_OK) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = $image_name; 
                } else {
                    $_SESSION['error'] = "Error moving uploaded file. Check server logs.";
                    header("Location: edit_inventory.php?id=$item_id");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Upload failed with error code: " . $upload_error;
                header("Location: edit_inventory.php?id=$item_id");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid image format or size (max 5MB, " . implode('/', $allowed_types) . ").";
            header("Location: edit_inventory.php?id=$item_id");
            exit;
        }
    }

    // Update item
    $stmt = $conn->prepare("UPDATE items SET name = ?, quantity = ?, expiry_date = ?, category = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sisssi", $name, $quantity, $expiry_date, $category, $image, $item_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item updated successfully.";
        header("Location: inventory.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating item: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry - Inventory</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
</head>

<style>
    .nav-link {
        color: black;
        border-radius: 10px;
        font-weight: 500;
    }

    .nav-link:hover {
        color: white;
    }

    .active {
        font-weight: bold;
        color: orange;
    }

    .active:hover {
        color: #ff6200;
    }

    i {
        color: white;
        margin-left: 10px;
    }

    table:hover {
        cursor: pointer;
    }

    .card {
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }

    .btn-warning {
        background-color: #ff6200;
        border-color: #ff6200;
    }

    .btn-warning:hover {
        background-color: #e65b00;
        border-color: #e65b00;
    }

    .alert {
        margin-bottom: 20px;
    }
</style>

<body>
    <!--NAVBAR PART-->
    <div class="container">
        <div class="row justify-content-center hide-overflow ">
            <div class="col-2">
                <img src="assets/img/icons/logo.png" alt="" height="100px">
            </div>
            <div class="col-4">
                <ul class="nav justify-content-center flex-wrap mt-4">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./dashboard/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../inventory.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="recipes.php">Recipes</a>
                    </li>
                </ul>
            </div>
             <div class="col-3 m-4 px-5">
                <a href="./user_login.php"><i class="fa-solid fa-user"></i></a>
                <a href="./cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;color:black;border-radius:10px;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container">
        <h4 class="dashboard-title" style="color: white; text-align:center; margin-bottom:2rem;">Edit Item</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="edit_inventory.php?id=<?= $item_id ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="itemName" name="item_name" value="<?= htmlspecialchars($item['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="<?= $item['quantity'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry" name="expiry_date" value="<?= $item['expiry_date'] ?: '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required onchange="toggleCustomCategory()">
                            <option value="">Select Category</option>
                            <?php
                            $category_result->data_seek(0); // Reset pointer
                            while ($cat = $category_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $item['category'] === $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="Other" <?= $item['category'] && !in_array($item['category'], array_column($category_result->fetch_all(MYSQLI_ASSOC), 'category')) ? 'selected' : '' ?>>Other</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="customCategory" name="custom_category" placeholder="Enter new category" style="display: <?= $item['category'] && !in_array($item['category'], array_column($category_result->fetch_all(MYSQLI_ASSOC), 'category')) ? 'block' : 'none' ?>;" value="<?= $item['category'] && !in_array($item['category'], array_column($category_result->fetch_all(MYSQLI_ASSOC), 'category')) ? htmlspecialchars($item['category']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Item Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($item['image']): ?>
                            <div class="mt-2">
                                <small>Current Image: <img src="<?= htmlspecialchars('assets/img/products/' . $item['image']) ?>" alt="Current Image" style="width: 50px; height: 50px; object-fit: cover;"></small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Item</button>
                    <a href="inventory.php" class="btn btn-secondary ms-2">Cancel</a>
                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>