<?php
include '../include/db.php';
session_start();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        header("Location: admin_products.php");
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
}

// Fetch products
$products_query = "SELECT * FROM products";
$products_result = $conn->query($products_query);

if (!$products_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin- Show Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/icons/products.webp" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .nav-link {
        color: black;
        border-radius: 10px;
        font-weight: 500;
    }

    th,td{
        color: white;
        text-align: center;
    }

    .nav-link:hover {
        color: black;
    }

    .nav-item a {
        margin-top: 10px;
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

    /* Dark Panel Container */
    .container {
        padding: 2.5rem;
        background-color: #1c1c2b;
        border-radius: 16px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
        color: #f2f2f2;
        font-family: 'Segoe UI', sans-serif;
    }

    /* Heading */
    .container h2 {
        font-size: 28px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 2rem;
    }

</style>

</head>

<body>
    <div class="containers">
        <div class="row justify-content-center hide-overflow ">
            <div class="col-2">
                <img src="../assets/img/icons/logo.png" alt="" height="100px">
            </div>
            <div class="col-4">
                <ul class="nav justify-content-center flex-wrap mt-4">
                    <li class="nav-item">
                        <a class="nav-link" href="./admin_dashboard.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="./add_recipes.php">Add Recipes</a>
                    </li>
                </ul>
            </div>
            <div class="col-3 m-4 px-5">
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;padding:10px;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2>Manage Products</h2>
        <?php if (isset($error)) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td>Rs. <?php echo number_format($product['price'], 2); ?></td>
                        <td><img src="../assets/img/products/<?php echo htmlspecialchars($product['image'] ?: 'default.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-height: 50px;"></td>
                        <td>
                            <a href="./edit_products.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="./admin_products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="add_item.php" class="btn btn-primary">Add New Product</a>
    </div>
</body>
</html>