<?php if ($expired_result->num_rows > 0): ?>
    <?php while ($expired_item = $expired_result->fetch_assoc()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"> <!--DISPLAYS WARNINGS-->
            <strong>Warning!</strong> <?= htmlspecialchars($expired_item['name']) ?> expired on <?= htmlspecialchars($expired_item['expiry_date']) ?>. Please dispose or use immediately.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<!--Box Part-->
<div class="box">
    <div class="box-part">
        <div class="inner-box">
            <div class="circle">
            </div>
            <div class="text">
                <p>Low Stock</p>
                <strong><?php echo $low_stock_count; ?> </strong><span>items</span> <br>
                <a href="../inventory.php" class="btn btn-warning">View Details</a>
            </div>
        </div>

        <div class="next-box">
            <div class="next-circle">
            </div>
            <div class="text">
                <p>Expiring Soon </p>
                <strong><?php echo $expiring_soon_count; ?> </strong><span>items</span> <br>
                <a href="../inventory.php" class="btn btn-warning">View Details</a>
            </div>
        </div>
    </div>

    <div class="banner"></div>
</div>