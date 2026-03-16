<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div class="mb-5">
    <h4 class="mb-3">Choose Your Flavor:</h4>
    <div class="d-flex flex-wrap gap-3">
        <a href="products.php" class="btn btn-light border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
            <i class="fas fa-mug-hot"></i> All
        </a>
        <?php
            $categories = getAll('categories');
            if($categories && mysqli_num_rows($categories) > 0):
                foreach($categories as $cat):
        ?>
            <a href="products.php?category=<?= $cat['id']; ?>" class="btn btn-light border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-mug-hot"></i> <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php
                endforeach;
            endif;
        ?>
    </div>
</div>


<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-coffee"></i> Products
                <a href="products-create.php" class="btn btn-primary float-end"> Add Products </a>
            </h4>
        </div>
        <div class="card-body">
            <?php alertMessage();

                if(isset($_GET['category']) && is_numeric($_GET['category'])) {
                $categoryId = $_GET['category'];
                $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = $categoryId");
            } else {
                $products = getAll('products');
            } ?>

            <div class="row g-4">
                <?php foreach($products as $item) : ?>
                <?php
                    $imagePath = ltrim((string)($item['image'] ?? ''), '/');
                    $imageUrl = '/HiddenCoreCafe_POS/admin/' . $imagePath;
                    $price12 = (float)($item['price_12oz'] ?? $item['price']);
                    $price16 = (float)($item['price_16oz'] ?? $item['price']);
                ?>
                <div class="col-md-3">
                    <div class="product-card<?= $item['quantity'] == 0 ? ' out-of-stock' : '' ?>">
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">

                        <?php if($item['quantity'] == 0): ?>
                            <div class="out-of-stock-overlay">Out of Stock</div>
                        <?php endif; ?>

                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="product-category"><strong>12oz:</strong> &#8369;<?= number_format($price12, 2) ?></div>
                            <div class="product-category"><strong>16oz:</strong> &#8369;<?= number_format($price16, 2) ?></div>
                            <div class="product-quantity">
                                Quantity: <strong><?= (int)$item['quantity'] ?></strong>
                            </div>

                            <div class="mb-2">
                                <?php if(isset($item['status']) && $item['status'] == 1): ?>
                                    <span class="badge bg-danger badge-status">Not Available</span>
                                <?php else: ?>
                                    <span class="badge bg-success badge-status">Available</span>
                                <?php endif; ?>
                            </div>

                            <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-success btn-sm me-2">Edit</a>
                            <a href="products-delete.php?id=<?= $item['id']; ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Are you sure you want to delete this product?')">
                            Delete
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if(mysqli_num_rows($products) == 0): ?>
                <div class="alert alert-warning mt-3">No Products Found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
