<?php
session_start();
include '../include/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = floatval($_POST['price']);
    $image = ''; // Default to empty, will be set based on existing or new image

    if (empty($name) || $quantity < 0 || $price < 0) {
        $error = "Please fill in all required fields correctly.";
        echo "<script>alert('$error');</script>";
    } else {
        $product_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : null;
        $existing_image = $product_id ? $conn->query("SELECT image FROM products WHERE id = $product_id")->fetch_assoc()['image'] : null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = dirname(__FILE__) . '/assets/img/products/'; // Absolute path from script location
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $original_image_name = basename($_FILES['image']['name']);
            // Sanitize filename without timestamp
            $image_name = preg_replace('/[^A-Za-z0-9.-]/', '_', $original_image_name);
            $base_name = pathinfo($image_name, PATHINFO_FILENAME);
            $extension = pathinfo($image_name, PATHINFO_EXTENSION);
            $counter = 1;
            while (file_exists($target_dir . $image_name)) {
                $image_name = $base_name . '_' . $counter . '.' . $extension;
                $counter++;
            }
            $target_file = $target_dir . $image_name;
            $image_type = strtolower(pathinfo($original_image_name, PATHINFO_EXTENSION));

            if (!in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Only JPG, JPEG, PNG or GIF are allowed";
                echo "<script>alert('$error');</script>";
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "Image size must be less than 5MB.";
                echo "<script>alert('$error');</script>";
            } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $image_name;
                echo "<!-- Image uploaded to: $target_file -->"; // Debug output
                // Delete old image if it exists and a new one is uploaded
                if ($product_id && $existing_image && $existing_image != $image && file_exists($target_dir . $existing_image)) {
                    unlink($target_dir . $existing_image);
                }
            } else {
                $error = "Failed to upload image. Check directory permissions or path: $target_dir";
                echo "<script>alert('$error');</script>";
            }
        } else {
            // No new image uploaded, reuse existing image if it exists
            $image = $existing_image ?: '';
        }

        if (empty($error)) {
            if ($product_id) {
                $stmt = $conn->prepare("UPDATE products SET name = ?, quantity = ?, price = ?, image = ? WHERE id = ?");
                $stmt->bind_param("sidsi", $name, $quantity, $price, $image, $product_id);
                $message = "Product updated successfully";
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, quantity, price, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sids", $name, $quantity, $price, $image);
                $message = "Product added successfully";
            }

            if ($stmt->execute()) {
                echo "<script>alert('$message'); window.location.href='../add_to_cart.php';</script>";
                exit;
            } else {
                $error = "Error: " . $conn->error;
                echo "<script>alert('$error');</script>";
            }
            $stmt->close();
        }
    }

    if ($error) {
        $_SESSION['error'] = $error;
        header('Location: add_item.php' . ($product_id ? '?id=' . $product_id : ''));
        exit;
    } else {
        $_SESSION['error'] = "Invalid Request";
        header('Location: add_item.php' . ($product_id ? '?id=' . $product_id : ''));
        exit;
    }
}

// Fetch product for editing if id is provided
$product = null;
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin-Add Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icons/add_item.png" type="image/x-icon">
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

    /* Dark Container */
    .container {
        background-color: #1e1e2f;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        max-width: 700px;
        margin: 2rem auto;
        font-family: 'Segoe UI', sans-serif;
        color: #f0f0f0;
    }

    /* Header */
    .container h2 {
        font-size: 26px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 1.8rem;
    }

    /* Error Alert */
    .alert-danger {
        background-color: #3c1c1c;
        color: #ff6b6b;
        border-left: 4px solid #ff4d4d;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 14.5px;
    }

    /* Form Spacing */
    form .mb-3 {
        margin-bottom: 1.5rem;
    }

    /* Labels */
    form .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #ccc;
        font-size: 14px;
    }

    /* Inputs */
    form .form-control {
        background-color: #2b2b3c;
        color: #f5f5f5;
        border: 1px solid #444;
        padding: 11px 14px;
        font-size: 14.5px;
        border-radius: 8px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    form .form-control:focus {
        border-color: #5a8dee;
        box-shadow: 0 0 0 2px rgba(90, 141, 238, 0.25);
        outline: none;
    }

    /* File input padding fix */
    input[type="file"].form-control {
        padding: 9px;
    }

    /* Dropdown */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,<svg fill='white' viewBox='0 0 20 20'><path d='M7 7l3-3 3 3m0 6l-3 3-3-3'/></svg>");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
    }

    /* Custom Category (hidden by default) */
    #customCategory {
        transition: all 0.3s ease;
    }

    /* Button */
    .btn-primary {
        background-color: #5a8dee;
        border: none;
        padding: 12px 20px;
        font-size: 15px;
        color: white;
        font-weight: 500;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #417de0;
    }
</style>

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
        <h2><?php echo $product ? 'Edit Product' : 'Add New Product'; ?></h2>
        <?php if (isset($_SESSION['error'])) echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']); ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="item_id" value="<?php echo $product ? $product['id'] : ''; ?>">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" class="form-control" name="item_name" value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="quantity" value="<?php echo $product ? htmlspecialchars($product['quantity']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $product ? htmlspecialchars($product['price']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                <?php if ($product && $product['image']): ?>
                    <img src="assets/img/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $product ? 'Update Product' : 'Add Product'; ?></button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('select[name="category"]').addEventListener('change', function() {
            document.getElementById('customCategory').style.display = this.value === 'Other' ? 'block' : 'none';
        });
    </script>
</body>

</html>