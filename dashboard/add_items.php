<?php
session_start();
include '../include/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'] ?: NULL;
    $category = trim($_POST['category'] ?? '');
    $custom_category = trim($_POST['custom_category'] ?? '');
    $image = '';

    // Use custom category if "Other" is selected and custom_category is provided
    if ($category === 'Other') {
        if (empty($custom_category)) {
            $error = "Please enter a custom category.";
            echo "<script>
            alert('$error');
          </script>";
        } elseif (strlen($custom_category) > 100 || !preg_match('/^[a-zA-Z0-9 ]+$/', $custom_category)) {
            $error = "Custom category must be less than 100 characters and contain only letters, numbers, or spaces.";
            echo "<script>
            alert('$error');
          </script>";
        } else {
            $category = $custom_category;
        }
    }

    if (empty($name) || $quantity < 0 || empty($category)) {
        $error = "Please fill in all required fields correctly.";
        echo "<script>
            alert('$error');
          </script>";
    } else {
        //image uploads
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../assets/img/products/";

            // Create the directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $image_name = time() . '_' . basename($_FILES['image']['name']); // rename to prevent conflict
            $target_file = $target_dir . $image_name;
            $image_type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            // Validate image
            if (!in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Only JPG, JPEG, PNG or GIF are allowed";
                echo "<script>alert('$error');</script>";
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "Image size must be less than 5MB.";
                echo "<script>alert('$error');</script>";
            } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error = "Failed to upload image";
                echo "<script>alert('$error');</script>";
            } else {
                $image = $target_file; // Store this in DB
            }
        }


        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO items(name,quantity,expiry_date, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $name, $quantity, $expiry_date, $category, $image); //sisss bhneko string,integer,string,string,string ho

            if ($stmt->execute()) {
                $_SESSION['success'];
                $message = "Item added successfully";
                echo "<script>
        alert('$message');
        window.location.href='dashboard.php';
      </script>";
                exit;
            } else {
                $error = "Error adding item: " . $conn->error;
                echo "<script>
        alert('$error');
      </script>";
            }
            $stmt->close();
        }
    }

    if ($error) {
        $_SESSION['error'] = $error;
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = "Invalid Request";
        header('Location: dashboard.php');
        exit;
    }
}
