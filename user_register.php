<?php

include './include/db.php';

session_start();

if (isset($_POST['submits'])) {
    $name = $_POST['name'];
    $name = htmlspecialchars(trim($_POST['name']));
    $email = $_POST['email'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];
    $pass = htmlspecialchars(trim($_POST['pass']));
    $cpass = $_POST['cpass'];
    $cpass = htmlspecialchars(trim($_POST['cpass']));

    if ($pass != $cpass) {
        echo "<script>alert('Passwords do not match!');
        </script>";
    } else {
        // Check if email already exists
        $query = "SELECT * FROM `users` WHERE email='$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            echo "<script>alert('Email already exists.');</script>";
        } else {
            // Insert new user into the database
            $query = "INSERT INTO `users` (name, email, password,confirm_password) VALUES ('$name', '$email', '$pass','$cpass')";
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Registered successfully.');</script>";
                header('Location: user_login.php'); // Redirect to login page after registration
                exit;
            } else {
                echo "<script>alert('Error occurred while registering.');</script>";
            }
        }
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

    <title>Smart-Pantry: Register Here</title>
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
            <div class="row align-items-center">
                <div class="col-md-5">
                    <span class="d-block text-center my-4 text-muted"> or sign in with</span>
                    <div class="social-login text-center">
                        <a href="#" class="facebook btn btn-block">
                            <span class="icon-facebook mr-3"></span>
                        </a>
                        <a href="#" class="twitter btn btn-block">
                            <span class="icon-twitter mr-3"></span>
                        </a>
                        <a href="#" class="google btn btn-block">
                            <span class="icon-google mr-3"></span>
                        </a>
                    </div>

                </div>
                <div class="col-md-2 text-center">
                    &mdash; or &mdash;
                </div>
                <div class="col-md-5 contents">
                    <div class="form-block">
                        <div class="mb-4">
                            <h3>Sign In To <strong>Smart-Pantry</strong></h3>
                            <p class="mb-4">A web-based application designed to help users manage their pantry inventory efficiently</p>
                        </div>
                        <form action="#" method="post">
                            <div class="form-group first">
                                <label for="username"></label>
                                <input type="text" class="form-control" id="username" placeholder="Username" name="name">
                            </div>
                            <div class="form-group last mb-4">
                                <label for="password"></label>
                                <input type="email" class="form-control" id="email" placeholder="Email" name="email">
                            </div>
                            <div class="form-group last mb-4">
                                <label for="password"></label>
                                <input type="password" class="form-control" placeholder="Password" id="password" name="pass">
                            </div>
                            <div class="form-group last mb-4">
                                <label for="password"></label>
                                <input type="password" class="form-control" placeholder="Confirm Password" id="password" name="cpass">
                            </div>

                            <div class="d-flex mb-5 align-items-center">
                                <label class="control control--checkbox mb-0"><span class="caption">Remember me</span>
                                    <input type="checkbox" checked="checked" />
                                    <div class="control__indicator"></div>
                                </label>
                                <span class="ml-auto"><a href="user_login.php" class="forgot-pass">Login Here</a></span>
                            </div>

                            <input type="submit" value="Sign Up Here" class="btn btn-pill text-white btn-block btn-primary" name="submits">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>