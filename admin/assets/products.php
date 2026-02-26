<?php

 include ('includes/header.php'); ?>

    <style>
        .product-card {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .product-card.out-of-stock {
            filter: grayscale(100%);
            opacity: 0.7;
        }
        
        .product-card.out-of-stock .btn-success,
        .product-card.out-of-stock .btn-danger {
            filter: none !important;
            opacity: 1 !important;
            background-color: #1A1A1A;
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }
        .product-category {
            font-size: 0.9rem;
            color: #555555;
            margin-bottom: 10px;
        }

        .badge-status {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .card-header {
            background-color: #FFFFFF !important;
            color: #000000;
        }
        .btn-primary {
            background-color: #000000;
            border-color:#000000;
        }
        .btn-primary:hover {
            background-color: #1A1A1A;
            color: #FFFFFF;
        }
        .btn-success, .btn-danger {
            background-color: #000000;
            border-color: #000000;
            color: #FFFFFF;
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .btn-success:hover, .btn-danger:hover {
            background-color: #1A1A1A;
            border-color: #1A1A1A;
            color: #FFFFFF;
        }
    
        .out-of-stock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 10px;
            z-index: 2;
            pointer-events: none;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <div class="mb-5">
        <h4 class="mb-3">☕ Choose Your Flavor:</h4>
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
                    <div class="col-md-3">
                        <div class="product-card<?= $item['quantity'] == 0 ? ' out-of-stock' : '' ?>">
                            <img src="../<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="product-image">

                            <?php if($item['quantity'] == 0): ?>
                                <div class="out-of-stock-overlay">Out of Stock</div>
                            <?php endif; ?>

                            <div class="product-info">
                                <div class="product-name"><?= $item['name'] ?></div>
                                <div class="product-category">
                                    <strong>
                                        <?php 
                                            $category = getById('categories', $item['category_id']);
                                        ?>
                                    </strong>
                                </div>
                                <div class="product-quantity">
                                    Quantity: <strong><?= $item['quantity'] ?></strong>
                                </div>
                                <div class="product-price">
                                    Price: <strong>₱<?= number_format($item['price'], 2) ?></strong>
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
                    <div class="alert alert-warning mt-3">No Products Found
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>