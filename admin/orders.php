<?php
include '../include/db.php';
session_start();

// Temporary test for admin access
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = true; // Enable this line for testing, remove after setting up login
    echo "<!-- Admin session enabled for testing -->";
} elseif (!$_SESSION['admin']) {
    die("Access denied. Please log in as admin.");
} else {
    echo "<!-- Admin session is true -->";
}

if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $cancel_reason = ($status === 'Cancelled') ? mysqli_real_escape_string($conn, $_POST['cancel_reason'] ?? '') : '';
    $stmt = $conn->prepare("UPDATE orders SET status = ?, cancel_reason = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $cancel_reason, $order_id);
    if ($stmt->execute()) {
        // Success, page reloads
    } else {
        $error = "Error updating status: " . $conn->error;
    }
    $stmt->close();
}

$orders_query = "SELECT * FROM orders ORDER BY order_date DESC";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin-Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icons/orders.png" type="image/x-icon">
</head>

<style>
    .nav-link {
        color: black;
        border-radius: 10px;
        font-weight: 500;
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

    /* Success Alert */
    .alert-success {
        background-color: #223322;
        color: #6ee67e;
        border-left: 4px solid #2ecc71;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14.5px;
        margin-bottom: 1.5rem;
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: collapse;
        background-color: #2a2a3c;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #2e2e42;
        color: #cfcfcf;
        font-weight: 600;
        font-size: 14px;
        padding: 14px 18px;
        text-align: left;
        border-bottom: 1px solid #3e3e58;
    }

    .table tbody td {
        color: black;
        font-size: 14px;
        padding: 13px 18px;
        border-bottom: 1px solid #3e3e58;
        vertical-align: middle;
    }

    /* Hover effect */
    .table tbody tr:hover {
        background-color: #33334d;
        transition: background-color 0.3s ease;
    }

    /* Dropdown styling */
    select.form-select {
        background-color: #1f1f2e;
        border: 1px solid #555;
        color: #f0f0f0;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    select.form-select:focus {
        border-color: #5a8dee;
        box-shadow: 0 0 0 2px rgba(90, 141, 238, 0.25);
        outline: none;
    }

    /* Button styling */
    .btn-warning {
        background-color: #ff6b00;
        border-color: #ff6b00;
        color: #fff;
    }

    .btn-warning:hover {
        background-color: #e65b00;
        border-color: #e65b00;
    }

    /* Status Badge (Optional - if you want color-coded status text) */
    .status-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
    }

    .status-pending {
        background-color: #444;
        color: #ffca28;
    }

    .status-processing {
        background-color: #1e88e5;
        color: #fff;
    }

    .status-delivered {
        background-color: #43a047;
        color: #fff;
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
                        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                            <th>Action</th>
                        <?php endif; ?>
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
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <input type="hidden" name="update_status">
                                    </form>
                                </td>
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