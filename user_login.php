<?php
include './include/db.php';
session_start();

// LOGIN LOGIC
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass']; // Retrieve and sanitize password
    $pass = htmlspecialchars(trim($_POST['pass']));

    $query = "SELECT * FROM `users` WHERE email='$email' AND password='$pass'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_logged_in'] = true; // Set login session
        // Redirect based on redirect parameter or default to add_to_cart.php
        $redirect = isset($_GET['redirect']) && !empty($_GET['redirect']) ? $_GET['redirect'] : './add_to_cart.php';
        header("Location: " . $redirect);
        exit;
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/css/owl.carousel.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./assets/css/css/bootstrap.min.css">

    <link rel="stylesheet" href="./assets/fonts/icomoon/style.css">

    <!-- Style -->
    <link rel="stylesheet" href="./assets/css/css/styles.css">

    <link rel="shortcut icon" href="./assets/img/icons/login.png" type="image/x-icon">

    <title>Smart-Pantry: Login Here</title>
</head>

<style>
    .content .contents .form-control,
    .content .bg .form-control {
        font-size: 13px;
    }
</style>

<body>
    <div class="content">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-5 contents">
                    <div class="form-block">
                        <div class="mb-4">
                            <h3>Login To <strong>Smart-Pantry</strong></h3>
                            <p class="mb-4">A web-based application designed to help users manage their pantry inventory efficiently</p>
                        </div>
                        <form action="#" method="post">
                            <div class="form-group last mb-4">
                                <label for="Email"></label>
                                <input type="email" class="form-control" id="email" placeholder="Email" name="email" required>
                            </div>
                            <div class="form-group last mb-4">
                                <label for="password"></label>
                                <input type="password" class="form-control" placeholder="Password" id="password" name="pass" required>
                            </div>

                            <div class="d-flex  mb-5 align-items-center">
                                <span><a href="user_register.php" class="forgot-pass">Sign Up Here</a></span>
                                <span class="ml-auto"><a href="./admin/admin_login.php" class="forgot-pass">Admin Login</a>
                                </span>
                            </div>

                            <input type="submit" value="Log In" class="btn btn-pill text-white btn-block btn-primary" name="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>