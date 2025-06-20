<?php
session_start();
include '../include/db.php';

// Check if admin is logged in
$is_admin_logged_in = isset($_SESSION['admin_id']);

if (!$is_admin_logged_in) {
    $_SESSION['error'] = "You must be an admin to add recipes.";
    header("Location:admin_login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $ingredients = !empty($_POST['ingredients']) ? implode(',', $_POST['ingredients']) : '';
    $instructions = trim($_POST['instructions']);

    // Validate inputs
    if (empty($name) || empty($ingredients) || empty($instructions)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Insert recipe
        $query = "INSERT INTO recipes (name, ingredients, instructions) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $name, $ingredients, $instructions);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Recipe added successfully!";
            header("Location: ../dashboard/recipes.php");
            exit;
        } else {
            $_SESSION['error'] = "Could not add recipe.";
        }
        $stmt->close();
    }
}

// Fetch available ingredients from items table
$query = "SELECT name FROM items";
$items_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry - Add Recipe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/icons/admin_recipe.jpg" type="image/x-icon">
</head>

<style>
    .nav-link {
        color: black;
        border-radius: 10px;
        font-weight: 500;
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
    <!-- Navbar -->
    <div class="container">
        <div class="row justify-content-center hide-overflow ">
            <div class="col-2">
                <img src="../assets/img/icons/logo.png" alt="" height="100px">
            </div>
            <div class="col-4">
                <ul class="nav justify-content-center flex-wrap mt-4">
                    <li class="nav-item">
                        <a class="nav-link text-white " href="admin_dashboard.php">Admin Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  active" href="add_recipes.php">Add Recipes</a>
                    </li>
                </ul>
            </div>
            <div class="col-3 m-4 px-5">
                <!-- <a href="admin_login.php"><i class="fa-solid fa-user"></i></a> -->
                <!-- <a href="add_item.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a> -->
                <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;" href="../user_logout.php">Logout</a></button>
            </div>
        </div>
    </div>

    <div class="container my-2">
        <h4 class="dashboard-title text-center pb-3 text-white">Add Recipe</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Add Recipe Form -->
        <div class="card p-4">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Recipe Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ingredients (select from pantry)</label>
                    <?php if ($items_result->num_rows > 0): ?>
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="ingredients[]" value="<?php echo htmlspecialchars($item['name']); ?>">
                                <label class="form-check-label"><?php echo htmlspecialchars($item['name']); ?></label>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No ingredients available in pantry.</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="instructions" class="form-label">Instructions</label>
                    <textarea class="form-control" id="instructions" name="instructions" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Add Recipe</button>
                <a href="../dashboard/recipes.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>