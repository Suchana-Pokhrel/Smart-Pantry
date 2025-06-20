<?php
include '../include/db.php';

// Total items
$total_items_query = "SELECT COUNT(*) as count FROM items";
$total_items_result = $conn->query($total_items_query) or die("Error fetching total items: " . $conn->error);
$total_items = $total_items_result->fetch_assoc()['count'];

// Total categories
$total_categories_query = "SELECT COUNT(DISTINCT category) as count FROM items WHERE category IS NOT NULL AND category != ''";
$total_categories_result = $conn->query($total_categories_query) or die("Error fetching categories: " . $conn->error);
$total_categories = $total_categories_result->fetch_assoc()['count'];

// Waste prevention tip (prioritize expired, then expiring soon)
$tip_query = "SELECT name, expiry_date FROM items WHERE expiry_date IS NOT NULL AND (expiry_date < CURDATE() OR expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) ORDER BY CASE WHEN expiry_date < CURDATE() THEN 0 ELSE 1 END, expiry_date ASC LIMIT 1";
$tip_result = $conn->query($tip_query) or die("Error fetching tip item: " . $conn->error);
$tip_item = $tip_result->fetch_assoc();
$tip = $tip_item ? (
    $tip_item['expiry_date'] < date('Y-m-d')
    ? "Dispose or use " . htmlspecialchars($tip_item['name']) . " immediately (expired on " . htmlspecialchars($tip_item['expiry_date']) . ")!"
    : "Use " . htmlspecialchars($tip_item['name']) . " soon (expires on " . htmlspecialchars($tip_item['expiry_date']) . ") to avoid waste!"
) : "No items expiring soon.";

// Urgent items for progress bar (expired + expiring soon)
$expired_query = "SELECT COUNT(*) as count FROM items WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()";
$expired_result = $conn->query($expired_query) or die("Error fetching expired items: " . $conn->error);
$expired_count = $expired_result->fetch_assoc()['count'];

$expiring_soon_query = "SELECT COUNT(*) as count FROM items WHERE expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
$expiring_soon_result = $conn->query($expiring_soon_query) or die("Error fetching expiring soon items: " . $conn->error);
$expiring_soon_count = $expiring_soon_result->fetch_assoc()['count'];

?>

<div class="contain bg-white py-4 ">
    <h4 class="dashboard-title text-center mb-4">Pantry Analytics</h4>

    <div class="container-part">
        <div class="row justify-content-center">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Items</h5>
                        <p class="card-text"><strong><?= $total_items ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Categories</h5>
                        <p class="card-text"><strong><?= $total_categories ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-10 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Waste Prevention Tip</h5>
                        <p class="card-text"><?= $tip ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card-title {
            color: #ff6200;
            font-size: 1.2rem;
        }

        .card-text {
            font-size: 1rem;
            color: #333;
        }

        .progress-bar {
            background-color: #ff6200 !important;
            color: #fff;
            font-weight: bold;
        }

        .progress {
            background-color: #f0f0f0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>