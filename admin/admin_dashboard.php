<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry - Add Recipe</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/icons/admin_recipe.jpg" type="image/x-icon">
</head>

<style>
    .card {
        width: 300px;
        height: 260px;
        border-radius: 20px;
        background: #f5f5f5;
        position: relative;
        padding: 1.8rem;
        border: 2px solid #c3c6ce;
        transition: 0.5s ease-out;
        overflow: visible;
        cursor: pointer;
    }

    .card-details {
        color: black;
        height: 100%;
        gap: .5em;
        display: grid;
        place-content: center;
    }

    .card-button {
        transform: translate(-50%, 125%);
        width: 60%;
        border-radius: 1rem;
        border: none;
        background-color: #008bf8;
        color: #fff;
        font-size: 1rem;
        padding: .5rem 1rem;
        position: absolute;
        left: 50%;
        bottom: 0;
        opacity: 0;
        transition: 0.3s ease-out;
    }

    .text-body {
        color: rgb(134, 134, 134);
    }

    .heading {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 40px;
    }

    .text-title {
        font-size: 1.5em;
        font-weight: bold;
    }

    .card:hover {
        border-color: #008bf8;
        box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
    }

    .card:hover .card-button {
        transform: translate(-50%, 50%);
        opacity: 1;
    }

    .panel-icon {
        font-size: 100px;
        color: #007bff;
        text-align: center;
    }

    .text-title {
        text-align: center;
    }

    .card-button a {
        text-decoration: none;
        color: #fff;
    }

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

</head>

<body>
    <div class="container">
        <div class="row justify-content-center hide-overflow ">
            <div class="col-2">
                <img src="../assets/img/icons/logo.png" alt="" height="100px">
            </div>
            <div class="col-4">
                <ul class="nav justify-content-center flex-wrap mt-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_dashboard.php">Admin Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="add_recipes.php">Add Recipes</a>
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

    <div class="heading">

        <div class="heading">
            <div class="card">
                <div class="card-details">
                    <div class="panel-icon">📦</div>
                    <p class="text-title">Order Panel</p>
                </div>
                <button class="card-button"><a href="order.php">More info</a> <i class="fa-solid fa-arrow-right"></i></button>

            </div>
        </div>

        <div class="heading">
            <div class="card">
                <div class="card-details">
                    <div class="panel-icon">🛒</div>
                    <p class="text-title">Add Products</p>
                </div>
                <button class="card-button"><a href="products.php">More info</a> <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

        <div class="heading">
            <div class="card">
                <div class="card-details">
                    <div class="panel-icon">🛠️</div>
                    <p class="text-title">Admin Panel</p>
                </div>
                <button class="card-button"><a href="admin_account.php">More info</a> <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>


</body>

</html>