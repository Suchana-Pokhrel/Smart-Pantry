<?php
session_start();
include '../include/db.php';

// Check if user or admin is logged in
$is_user_logged_in = isset($_SESSION['user_id']);
$is_admin_logged_in = isset($_SESSION['admin_id']);

// Handle favorite action (regular users only)
if ($is_user_logged_in && isset($_GET['action']) && $_GET['action'] === 'favorite' && isset($_GET['recipe_id'])) {
    $recipe_id = (int)$_GET['recipe_id'];
    $user_id = $_SESSION['user_id'];
    $query = "INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $recipe_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipe saved as favorite!";
    } else {
        $_SESSION['error'] = "Could not save favorite.";
    }
    $stmt->close();
    header("Location: recipes.php");
    exit;
}

// Fetch recipes from database
$query = "SELECT id, name, ingredients, instructions FROM recipes";
$recipes_result = $conn->query($query);

// Check if login modal should be shown
$show_login_modal = isset($_GET['show_login']) && $_GET['show_login'] === 'true';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry - Recipes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icons/recipe.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
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

    .active {
        font-weight: bold;
        color: orange;
    }

    .active:hover {
        color: #ff6200;
    }

    i {
        color: black;
        margin-left: 10px;
    }

    table:hover {
        cursor: pointer;
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

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>

<body>
    <!-- Navbar -->
    <div class="container">
        <div class="row justify-content-center hide-overflow">
            <div class="col-2">
                <img src="../assets/img/icons/logo.png" alt="" height="100px">
            </div>
            <div class="col-4">
                <ul class="nav justify-content-center flex-wrap mt-4">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../inventory.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="recipes.php">Recipes</a>
                    </li>
                </ul>
            </div>
            <div class="col-3 m-4 px-5">
                <?php if ($is_user_logged_in || $is_admin_logged_in): ?>
                    <a href="../user_login.php"><i class="fa-solid fa-user"></i></a>
                    <a href="../add_to_cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                    <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                    <button type="button" class="btn"><a class="nav-link text-black" style="background-color: #ff6200;" href="../user_logout.php">Logout</a></button>
                <?php else: ?>
                    <a href="../user_login.php"><i class="fa-solid fa-user"></i></a>

                    <a href="../order_supplies.php"><i class="fa-solid fa-cart-shopping"></i></a>
                    <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="container my-5">
        <h4 class="dashboard-title text-center pb-3">Recipes</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Recipes Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Recipe Name</th>
                        <th>Ingredients</th>
                        <th>Instructions</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recipes_result->num_rows > 0): ?>
                        <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recipe['name']); ?></td>
                                <td><?php echo htmlspecialchars($recipe['ingredients']); ?></td>
                                <td><?php echo htmlspecialchars($recipe['instructions']); ?></td>
                                <td>
                                    <?php if ($is_user_logged_in): ?>
                                        <a href="recipes.php?action=favorite&recipe_id=<?php echo $recipe['id']; ?>" class="btn btn-warning btn-sm">Save Favorite</a>
                                    <?php else: ?>
                                        <a href="recipes.php?show_login=true" class="btn btn-warning btn-sm">Save Favorite</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No recipes available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Login Modal -->
        <div class="modal fade <?php echo $show_login_modal ? 'show' : ''; ?>" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptModalLabel" aria-hidden="<?php echo $show_login_modal ? 'false' : 'true'; ?>" style="<?php echo $show_login_modal ? 'display: block;' : ''; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginPromptModalLabel">Login Required</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please log in to save recipes.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="../user_login.php" class="btn btn-warning">Log In</a>
                        <a href="recipes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../include/footer.php'; ?>
</body>

</html>