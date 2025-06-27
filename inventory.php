<?php
session_start();
include 'include/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Handle delete action (logged-in users only)
if ($is_logged_in && isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting item: " . $conn->error;
    }
    $stmt->close();
    header("Location: inventory.php");
    exit;
}

// Query parameters for search, filter, sort, and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['name', 'quantity', 'expiry_date']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build query
$query = "SELECT id, name, quantity, expiry_date, category, image FROM items WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}
if ($status === 'low_stock') {
    $query .= " AND quantity < 5";
} elseif ($status === 'expired') {
    $query .= " AND expiry_date IS NOT NULL AND expiry_date < CURDATE()";
} elseif ($status === 'expiring_soon') {
    $query .= " AND expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
}

$query .= " ORDER BY $sort $order LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total items for pagination
$count_query = "SELECT COUNT(*) as total FROM items WHERE 1=1";
$count_params = [];
$count_types = "";
if ($search) {
    $count_query .= " AND name LIKE ?";
    $count_params[] = "%$search%";
    $count_types .= "s";
}
if ($category) {
    $count_query .= " AND category = ?";
    $count_params[] = $category;
    $count_types .= "s";
}
if ($status === 'low_stock') {
    $count_query .= " AND quantity < 5";
} elseif ($status === 'expired') {
    $count_query .= " AND expiry_date IS NOT NULL AND expiry_date < CURDATE()";
} elseif ($status === 'expiring_soon') {
    $count_query .= " AND expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
}
$count_stmt = $conn->prepare($count_query);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch categories for filter
$category_query = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != '' ORDER BY category";
$category_result = $conn->query($category_query) or die("Error fetching categories: " . $conn->error);

// Check for expired items alert
$expired_query = "SELECT name, expiry_date FROM items WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()";
$expired_result = $conn->query($expired_query) or die("Error in expired items query: " . $conn->error);

// Check if login modal should be shown
$show_login_modal = isset($_GET['show_login']) && $_GET['show_login'] === 'true';
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry - Inventory</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/icons/inventory.png" type="image/x-icon">
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
</style>

<body>
    <!-- Navbar -->
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
                        <a class="nav-link  active" href="./inventory.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./dashboard/recipes.php">Recipes</a>
                    </li>
                </ul>
            </div>
             <div class="col-3 m-4 px-5">
                <a href="../user_login.php"><i class="fa-solid fa-user"></i></a>
                <a href="./add_to_cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;color:black;border-radius:10px;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container">
        <h4 class="dashboard-title" style="color: white; text-align:center; margin:2rem; font-size:2rem;">Inventory Management</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if ($expired_result->num_rows > 0): ?>
            <?php while ($expired_item = $expired_result->fetch_assoc()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Warning!</strong> <?= htmlspecialchars($expired_item['name']) ?> expired on <?= htmlspecialchars($expired_item['expiry_date']) ?>. Please dispose or use immediately.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by item name" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="category">
                            <option value="">All Categories</option>
                            <?php while ($cat = $category_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="status">
                            <option value="" <?= $status === '' ? 'selected' : '' ?>>All Status</option>
                            <option value="low_stock" <?= $status === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                            <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                            <option value="expiring_soon" <?= $status === 'expiring_soon' ? 'selected' : '' ?>>Expiring Soon</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="table-responsive">
            <table class="table table-bordered" style="border-color: white; color:white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <thead class="table-dark">
                    <tr>
                        <th><a href="?sort=name&order=<?= $sort === 'name' && $order === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" class="text-white">Name</a></th>
                        <th><a href="?sort=quantity&order=<?= $sort === 'quantity' && $order === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" class="text-white">Quantity</a></th>
                        <th><a href="?sort=expiry_date&order=<?= $sort === 'expiry_date' && $order === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" class="text-white">Expiry Date</a></th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($item = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>
                                    <?= $item['quantity'] ?>
                                    <?php if ($item['quantity'] < 5): ?>
                                        <span class="badge bg-warning ms-2" style="color: black;">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $item['expiry_date'] ? htmlspecialchars($item['expiry_date']) : 'N/A' ?>
                                    <?php
                                    if ($item['expiry_date'] && strtotime($item['expiry_date']) < strtotime(date('Y-m-d'))):
                                    ?>
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    <?php elseif ($item['expiry_date'] && strtotime($item['expiry_date']) <= strtotime('+7 days')): ?>
                                        <span class="badge bg-warning ms-2" style="color: black;">Expiring Soon</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['category']) ?: 'N/A' ?></td>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars('assets/img/products/' . $item['image']) ?>" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_logged_in): ?>
                                        <a href="edit_inventory.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm me-1">Edit</a>
                                        <a href="inventory.php?delete_id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                    <?php else: ?>
                                        <a href="inventory.php?show_login=true" class="btn btn-warning btn-sm me-1">Edit</a>
                                        <a href="inventory.php?show_login=true" class="btn btn-danger btn-sm">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" style="cursor:pointer;">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages . '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">Next</a>
                </li>
            </ul>
        </nav>

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
                        <a href="user_login.php" class="btn btn-warning">Log In</a>
                        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Footer Part-->
    <div class="container">
        <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
            <div class="col-md-4 d-flex align-items-center"> <a href="/" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1" aria-label="Bootstrap"> <svg class="bi" width="30" height="24" aria-hidden="true">
                        <use xlink:href="#bootstrap"></use>
                    </svg> </a> <span class="mb-3 mb-md-0 text-body-secondary text-white">© Smart Pantry, 2025</span> </div>
            <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
                <li class="ms-3">
                    <a class="text-body-secondary" href="#" aria-label="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                </li>
                <li class="ms-3">
                    <a class="text-body-secondary" href="#" aria-label="Facebook">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                </li>
            </ul>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>