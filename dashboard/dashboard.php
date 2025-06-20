<?php

session_start();
include '../include/db.php';

//checks stock 
$low_stock_query = "SELECT COUNT(*) as count FROM items WHERE quantity< 5 ";
$low_stock_result = $conn->query($low_stock_query) or die("Error in low stock query: " . $conn->error);
$low_stock_count = $low_stock_result->fetch_assoc()['count'];

//check expiry date and its logics
$expiring_soon_query = "SELECT COUNT(*) as count FROM items WHERE expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
$expiring_soon_result = $conn->query($expiring_soon_query) or die("Error in expiring soon query: " . $conn->error);
$expiring_soon_count = $expiring_soon_result->fetch_assoc()['count'];

//shows alert when reaches 7 days.
$expired_query = "SELECT name, expiry_date FROM items WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()";
$expired_result = $conn->query($expired_query) or die("Error in expired items query: " . $conn->error);

// Query for recent/low stock items (limit to 5)
$inventory_query = "SELECT name, quantity, expiry_date, category, image FROM items WHERE quantity <= 10 ORDER BY expiry_date ASC LIMIT 5";
$inventory_result = $conn->query($inventory_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pantry</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="shortcut icon" href="../assets/img/icons/FMS.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

    .btn {
        border-radius: 14px;
        margin-left: 20px;
    }

    .wrapper {
        display: flex;
        align-items: center;
        justify-content: space-evenly;
    }

    .image img {
        max-width: 100%;
        height: 100%;
    }

    img {
        filter: contrast(1.2) saturate(1.2) blur(0.7px) brightness(0.9);
    }

    .text-part {
        text-align: left;
        padding: 4rem;

        h3 {
            color: white;
            font-size: 4rem;
            font-family: Comic Sans MS, Comic Sans, cursive;
            font-weight: bold;
        }

        span {
            font-size: 1.5em;
            color: rgba(188, 186, 186, 0.74);
            font-family: New Century Schoolbook, TeX Gyre Schola, serif;
        }

        p {
            color: rgb(255, 255, 255);
            font-family: New Century Schoolbook, TeX Gyre Schola, serif;
        }

        .btn {
            width: 200px;
            margin: 0px;
        }
    }

    .box-part {
        height: auto;
        padding: 5rem;
        display: flex;
        background-color: white;
        justify-content: space-evenly;
    }

    .box {
        height: auto;
        background-color: white;
    }

    .inner-box,
    .next-box {
        background: linear-gradient(90deg,
                hsla(0, 0%, 0%, 1) 43%,
                hsla(193, 9%, 19%, 1) 100%);
        width: 450px;
        display: flex;
        align-items: center;
    }

    .circle {
        border: 3px solid white;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        margin: 1rem;
        background-image: url(../assets/img/banner/burger.jpg);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: 1s ease;

    }

    .circle:hover {
        transform: scale(1.2);
    }

    .next-circle {
        border: 3px solid white;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        margin: 1rem;
        background-image: url(../assets/img/banner/roll.webp);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: 1s ease;
    }

    .next-circle:hover {
        transform: scale(1.2);
    }

    .text {
        color: white;
        font-family: Comic Sans MS, Comic Sans, cursive;
        font-size: 1.4rem;
        padding: 0px;
        margin: 16px;

        a {
            margin-left: 0px;
            border-radius: 0px;
            margin-top: 15px;
        }
    }

    strong {
        font-size: 2rem;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 4rem;
        font-family: Comic Sans MS, Comic Sans, cursive;
        color: black;
        text-align: center;
        padding-top: 3rem;
    }

    .inventory-card {
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        width: 300px;
    }

    .inventory-card .card-img-top {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        display: block;
        margin-top: 2rem;
        margin-left: auto;
        margin-right: auto;
        transition: 1s ease;
    }

    .inventory-card .card-img-top:hover {
        transform: scale(0.8);
    }

    .inventory-card .card-body {
        padding: 1.5rem;
    }

    .inventory-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        text-align: center;
    }

    .inventory-card .card-text {
        color: #666;
        margin-bottom: 0.5rem;
    }

    .inventory-card .card-text strong {
        color: #ff6200;
        font-weight: 200;
        font-size: 1rem;
    }

    .inventory-card .btn-card {
        border-radius: 8px;
        background-color: #ff6200;
        border: none;
        margin: 0px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        width: 100%;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .inventory-card .btn-card:hover {
        background-color: #e55a00;
    }

    .btn-view-all {
        border-radius: 8px;
        background-color: #ff6200;
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .btn-view-all:hover {
        background-color: #e55a00;
    }

    .radio {
        overflow: hidden;
    }

    .banner {
        height: 200px;
        background-image: url(../assets/img/banner/food-banner.jpg);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .banners {
        height: 200px;
        background-image: url(../assets/img/banner/vector-banner.jpg);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .text-center {
        padding: 2rem;
    }

    .dashboard-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
        font-family: Comic Sans MS, Comic Sans, cursive;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .action-btn {
        border-radius: 8px;
        background-color: #ff6200;
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .action-btn:hover {
        background-color: #e55a00;
    }
</style>

<body>
    <?php include '../include/header.php'; ?>
    <div class="container-fluid">
        <?php include 'metrics_dashboard.php'; ?>
        <?php include 'inventory_list.php'; ?>
        <?php include 'quick_actions.php'; ?>
        <?php include 'about-us.php'; ?>
        <?php include 'pantry_analytics.php'; ?>
        <?php include '../include/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>