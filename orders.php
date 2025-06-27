<?php
include 'include/db.php';
session_start();

if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
}

$orders_query = "SELECT * FROM orders ORDER BY order_date DESC";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="shortcut icon" href="./assets/img/icons/orders.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    /* Container styling */
    .container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 100%;
        overflow-x: auto;
    }

    /* Heading */
    .container h2 {
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
    }

    /* Table styling */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        background-color: #fafafa;
    }

    .table th,
    .table td {
        padding: 12px 16px;
        border: 1px solid #dee2e6;
        text-align: left;
        vertical-align: middle;
        font-size: 15px;
    }

    .table thead th {
        background-color: #f0f0f0;
        font-weight: 600;
        color: #444;
    }

    /* Row hover effect */
    .table tbody tr:hover {
        background-color: #f9f9f9;
        transition: background-color 0.2s ease-in-out;
    }

    /* Select dropdown for status */
    form select.form-control {
        padding: 6px 10px;
        font-size: 14px;
        border-radius: 8px;
        border: 1px solid #ccc;
        background-color: #fff;
        transition: border-color 0.2s;
    }

    form select.form-control:hover,
    form select.form-control:focus {
        border-color: #007bff;
        outline: none;
    }

    /* Success alert styling */
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
        font-size: 15px;
    }

    a {
        color: white;
    }

    a:hover {
        color: white;
    }

    .active {
        font-weight: bold;
        color: orange;
    }

    .active:hover {
        color: #ff6200;
    }

    .nav-link {
        font-weight: 600;
    }

    i {
        color: white;
        margin-left: 10px;
    }
</style>

<body>
    <!--NAVBAR PART-->
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
                        <a class="nav-link active" href="./dashboard/orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./dashboard/recipes.php">Recipes</a>
                    </li>
                </ul>
            </div>
            <div class="col-3 m-4 px-5">
                <a href="./user_login.php"><i class="fa-solid fa-user"></i></a>
                <a href="./add_to_cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;color:black;border-radius:10px;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2>Your Orders</h2>
        <?php if (isset($_GET['success'])) echo '<div class="alert alert-success">Order placed successfully!</div>'; ?>
        <?php if ($orders_result && $orders_result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Items</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['address']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo htmlspecialchars($order['items']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>

                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>