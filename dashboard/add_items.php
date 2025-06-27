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
        // Image uploads
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../assets/img/products/";

            // Create the directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $original_image_name = basename($_FILES['image']['name']); // Original filename without path
            $image_name = time() . '_' . $original_image_name; // Default new name with timestamp
            $target_file = $target_dir . $image_name;
            $image_type = strtolower(pathinfo($original_image_name, PATHINFO_EXTENSION));

            // Validate image
            if (!in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Only JPG, JPEG, PNG or GIF are allowed";
                echo "<script>alert('$error');</script>";
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "Image size must be less than 5MB.";
                echo "<script>alert('$error');</script>";
            } else {
                // Check for existing image without timestamp
                $existing_files = glob($target_dir . preg_replace('/^\d+_/', '', $original_image_name));
                if (!empty($existing_files)) {
                    $image_name = basename($existing_files[0]); // Reuse existing filename
                } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Successfully uploaded new image
                } else {
                    $error = "Failed to upload image";
                    echo "<script>alert('$error');</script>";
                }
                $image = $image_name; // Store only the filename
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO items(name, quantity, expiry_date, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $name, $quantity, $expiry_date, $category, $image);

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