<?php
include 'include/db.php';
session_start();

// Add to cart (insert into carts table with debug)
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    echo "Received product_id: $product_id<br>"; // Debug
    $quantity = 1; // Default increment, can adjust based on stock later

    // Check if product exists and has stock
    $check_query = "SELECT stock FROM products WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $product = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($product && $product['stock'] > 0) {
        // Check if item already exists in cart
        $cart_check_query = "SELECT quantity FROM carts WHERE product_id = ?";
        $cart_check_stmt = $conn->prepare($cart_check_query);
        $cart_check_stmt->bind_param("i", $product_id);
        $cart_check_stmt->execute();
        $cart_check_result = $cart_check_stmt->get_result();
        $existing_cart_item = $cart_check_result->fetch_assoc();
        $cart_check_stmt->close();

        if ($existing_cart_item) {
            // Update existing cart item
            $new_quantity = $existing_cart_item['quantity'] + $quantity;
            $update_query = "UPDATE carts SET quantity = ? WHERE product_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ii", $new_quantity, $product_id);
            if ($update_stmt->execute()) {
                echo "Updated cart: product_id $product_id, quantity $new_quantity<br>";
            } else {
                echo "Update failed: " . $conn->error . "<br>";
            }
            $update_stmt->close();
        } else {
            // Insert new cart item
            $insert_query = "INSERT INTO carts (product_id, quantity) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ii", $product_id, $quantity);
            if ($insert_stmt->execute()) {
                echo "Inserted into cart: product_id $product_id, quantity $quantity<br>";
            } else {
                echo "Insert failed: " . $conn->error . "<br>";
            }
            $insert_stmt->close();
        }
    } else {
        echo "Product $product_id not found or out of stock.<br>";
    }
    header("Location: order_supplies.php");
    exit;
}

// Checkout
if (isset($_POST['checkout'])) {
    $cart_query = "SELECT c.product_id, c.quantity, p.name, p.price 
                   FROM carts c 
                   JOIN products p ON c.product_id = p.id";
    $cart_result = $conn->query($cart_query);

    if ($cart_result && $cart_result->num_rows > 0) {
        while ($cart_item = $cart_result->fetch_assoc()) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];

            $stock_query = "SELECT stock FROM products WHERE id = ?";
            $stock_stmt = $conn->prepare($stock_query);
            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();
            $product = $stock_result->fetch_assoc();
            $stock_stmt->close();

            if ($product && $product['stock'] >= $quantity) {
                $order_query = "INSERT INTO orders (item_id, quantity, order_date, status) VALUES (?, ?, NOW(), 'Pending')";
                $order_stmt = $conn->prepare($order_query);
                $order_stmt->bind_param("ii", $product_id, $quantity);
                if ($order_stmt->execute()) {
                    $update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_stock_query);
                    $update_stmt->bind_param("ii", $quantity, $product_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    echo "Error inserting order for item_id $product_id: " . $conn->error . "<br>";
                }
                $order_stmt->close();
            } else {
                echo "Insufficient stock for item_id $product_id.<br>";
            }
        }

        // Clear cart
        $clear_cart_query = "DELETE FROM carts";
        $conn->query($clear_cart_query);

        header("Location: order_supplies.php?success=1");
        exit;
    }
}

// Fetch products
$products_query = "SELECT * FROM products";
$products_result = $conn->query($products_query);

// Fetch cart items from database
$cart_items = [];
$cart_total = 0;
$cart_query = "SELECT c.product_id, c.quantity, p.name, p.price 
               FROM carts c 
               JOIN products p ON c.product_id = p.id";
$cart_result = $conn->query($cart_query);
if ($cart_result && $cart_result->num_rows > 0) {
    while ($cart_item = $cart_result->fetch_assoc()) {
        $cart_item['total'] = $cart_item['quantity'] * $cart_item['price'];
        $cart_items[] = $cart_item;
        $cart_total += $cart_item['total'];
    }
}
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

    tr,th{
        color: white;
    }

    h2{
        color: white;
        text-align: center;
        margin-bottom: 2rem;
    }

    .cart{
        color: white;
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
                        <a class="nav-link text-white" href="./dashboard/orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./dashboard/recipes.php">Recipes</a>
                    </li>
                </ul>
            </div>
            <div class="col-3 m-4 px-5">
                <a href="./user_login.php"><i class="fa-solid fa-user"></i></a>
                <a href="add_item.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <h2>Available Supplies</h2>
        <div class="row">
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">Price: <?php echo number_format($product['price'], 2); ?></p>
                            <p class="card-text">Stock: <?php echo $product['stock']; ?></p>
                            <a href="?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <h2 class="mt-5">Your Cart</h2>
        <?php if (!empty($cart_items)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rs.<?php echo number_format($item['price'], 2); ?></td>
                            <td>Rs.<?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>Rs.<?php echo number_format($cart_total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <form method="post">
                <button type="submit" name="checkout" class="btn btn-success">Checkout</button>
            </form>
        <?php else: ?>
            <p class="cart"> Your cart is empty.</p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])) echo '<div class="alert alert-success">Order placed successfully!</div>'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>