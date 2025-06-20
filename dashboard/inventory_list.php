<div class="radio" style="background-color: white;">
    <h4 class="dashboard-title">Inventory List</h4>
    <div class="row justify-content-center g-4">
        <?php
        if ($inventory_result->num_rows > 0) {
            while ($row = $inventory_result->fetch_assoc()) {
                  $image = $row['image'] ? '../assets/img/products/' . htmlspecialchars($row['image']) : '../assets/img/icons/default.png';
                echo '<div class="col-12 col-sm-6 col-lg-4 mb-4 d-flex justify-content-center">
                    <div class="inventory-card">
                        <img src="' . $image . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>
                            <p class="card-text"><strong>Quantity:</strong> ' . htmlspecialchars($row['quantity']) . '</p>
                            <p class="card-text"><strong>Expiry Date:</strong> ' . ($row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : 'N/A') . '</p>
                            <p class="card-text"><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</p>  
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-center text-muted">No items found.</p></div>';
        }
        ?>
    </div>
    <div class=" text-center">
        <a href="../inventory.php" class="btn btn-warning btn-view-all">View All Inventory</a>
    </div>

    <div class="banners"></div>
</div>