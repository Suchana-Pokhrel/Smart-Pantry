<?php
include 'include/db.php';
session_start();

if (isset($_POST['checkout'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    if (!empty($name) && !empty($address) && !empty($phone)) {
        $order_items = '';
        if (isset($_SESSION['cart'])) {
            $first = true;
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?"); // Changed to products table
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                $stmt->close();
                if ($item) {
                    if (!$first) $order_items .= ', ';
                    $order_items .= htmlspecialchars($item['name']) . ' (x' . $quantity . ')';
                    $first = false;
                }
            }
        }
        $status = 'Pending';
        $stmt = $conn->prepare("INSERT INTO orders (user_name, address, phone, items, status, order_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $name, $address, $phone, $order_items, $status);
        if ($stmt->execute()) {
            unset($_SESSION['cart']);
            header("Location: orders.php?success=1");
        } else {
            $error = "Error placing order: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}

$cart_items = [];
$cart_total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE id = ?"); // Changed to products table
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        if ($item) {
            $item['cart_quantity'] = $quantity;
            $cart_items[] = $item;
            $cart_total += $quantity * $item['price']; // Use actual price from products
        } else {
            echo "<!-- Warning: Product ID $product_id not found in products table -->"; // Debug
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="shortcut icon" href="./assets/img/icons/cart.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    h2 {
        text-align: center;
        margin-bottom: 40px;
        font-weight: 700;
        color: #343a40;
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

    /* Overall container */
    .container {
        background-color: #fff;
        padding: 2rem 2.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        font-family: 'Segoe UI', sans-serif;
        margin-bottom: 3rem;
    }

    /* Headings */
    .container h2,
    .container h3 {
        font-weight: 600;
        color: #222;
        margin-bottom: 1.5rem;
        font-size: 24px;
    }

    /* Error Message */
    .alert-danger {
        background-color: #ffeaea;
        color: #d60000;
        padding: 12px 20px;
        border-left: 4px solid #d60000;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* Table */
    .table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fcfcfc;
        margin-bottom: 2rem;
        border: 1px solid #eee;
    }

    .table th,
    .table td {
        padding: 14px 18px;
        border: 1px solid #e4e4e4;
        text-align: left;
        font-size: 15px;
        color: #333;
    }

    .table thead th {
        background-color: #f5f5f5;
        font-weight: 600;
        font-size: 15.5px;
    }

    .table tfoot td {
        background-color: #fafafa;
        font-weight: bold;
        font-size: 16px;
        color: #111;
    }

    /* Container wrapper for cart + checkout */
    .cart-checkout-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        margin-top: 2rem;
    }

    /* Cart Section */
    .cart-section {
        flex: 1 1 60%;
        min-width: 300px;
    }

    /* Checkout Section */
    .checkout-form {
        flex: 1 1 35%;
        min-width: 280px;
        background-color: #fff9f4;
        padding: 2rem;
        border-radius: 12px;
        border: 1px solid #ffe0cc;
        box-shadow: 0 4px 10px rgba(255, 106, 0, 0.1);
    }

    .checkout-form h3 {
        margin-bottom: 1.5rem;
        font-size: 22px;
        color: #333;
        font-weight: 600;
    }

    /* Form Inputs */
    .checkout-form .form-label {
        font-weight: 500;
        color: #444;
        font-size: 14.5px;
        margin-bottom: 0.4rem;
    }

    .checkout-form .form-control {
        padding: 10px 14px;
        font-size: 14.5px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #fff;
        transition: border-color 0.3s;
    }

    .checkout-form .form-control:focus {
        border-color: #ff6a00;
        box-shadow: 0 0 0 2px rgba(255, 106, 0, 0.15);
        outline: none;
    }

    /* Button */
    .checkout-form .btn-success {
        background-color: #ff6a00;
        border: none;
        padding: 12px;
        font-size: 16px;
        width: 100%;
        margin-top: 10px;
        color: #fff;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.3s;
    }

    .checkout-form .btn-success:hover {
        background-color: #e85c00;
    }
</style>

<body>
    <div class="containers">
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
                        <a class="nav-link text-white" href="/dashboard/recipes.php">Recipes</a>
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
        <h2>Your Cart</h2>

        <?php if (isset($error)) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-checkout-wrapper">
                <!-- Cart Table -->
                <div class="cart-section">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['cart_quantity']); ?></td>
                                    <td>Rs. <?php echo number_format($item['cart_quantity'] * $item['price'], 2); ?></td> <!-- Use price from products -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>Rs. <?php echo number_format($cart_total, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Checkout Form -->
                <div class="checkout-form">
                    <h3>Checkout</h3>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-success">Place Order</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p>Your cart is empty. <a href="add_to_cart.php">Continue shopping</a></p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>