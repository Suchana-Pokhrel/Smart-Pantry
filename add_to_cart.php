<?php
include 'include/db.php';
session_start();

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$product_id] = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + 1 : 1;
    // Debug: Check session content
    echo "<pre>Session Cart: "; print_r($_SESSION['cart']); echo "</pre>";
    header("Location: checkout.php");
    exit;
}

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
    <title>Add to Cart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="shortcut icon" href="./assets/img/icons/cart.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }

    h2 {
        text-align: center;
        margin-bottom: 40px;
        font-weight: 700;
        color: #343a40;
    }

    .card {
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        overflow: hidden;
        width: 300px;
        height: 400px;
    }

    .card:hover {
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
    }

    .card-img-top {
        height: 200px;
        object-fit: cover;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #212529;
    }

    .card-text {
        font-size: 0.95rem;
        color: #555;
    }

    .btn-primary {
        background-color: #0d6efd;
        border: none;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    .btn-primary:hover {
        background-color: #084298;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }

    .btn-primary:active {
        background-color: #062c6b;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .card .btn {
        width: 100%;
        margin-top: 10px;
    }

    a {
        color: white;
    }

    a:hover {
        color: white;
    }

    h2 {
        color: white;
    }

    i {
        color: white;
        margin-left: 10px;
    }

    .active {
        font-weight: bold;
        color: orange;
    }

    .active:hover {
        color: #ff6200;
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
                        <a class="nav-link text-white" href="./inventory.php">Inventory</a>
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
                <a href="./user_login.php"><i class="fa-solid fa-user"></i></a>
                <a href="./add_to_cart.php"><i class="fa-solid fa-cart-shopping active"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;color:black;border-radius:10px;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2>Available Products</h2>
        <div class="row">
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <img src="assets/img/products/<?php echo htmlspecialchars($product['image'] ?: 'default.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-height: 200px; object-fit: cover;" onerror="this.src='assets/img/products/default.png';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                            <p class="card-text">Price: Rs.<?php echo number_format($product['price'], 2); ?></p>
                            <a href="?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>