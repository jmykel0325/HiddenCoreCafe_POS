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
            <?php
                alertMessage();
                ensureProductsDeletedAtColumn();

                $activeWhere = "p.deleted_at IS NULL";
                $deletedWhere = "p.deleted_at IS NOT NULL";
                if(isset($_GET['category']) && is_numeric($_GET['category'])) {
                    $categoryId = validate($_GET['category']);
                    $activeWhere .= " AND p.category_id = '$categoryId'";
                    $deletedWhere .= " AND p.category_id = '$categoryId'";
                }

                $products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $activeWhere ORDER BY p.id DESC");
                $deletedProducts = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $deletedWhere ORDER BY p.deleted_at DESC, p.id DESC");
            ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="80">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>12oz Price</th>
                            <th>16oz Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = mysqli_fetch_assoc($products)) : ?>
                        <?php
                            $imagePath = $item['image'] ?? '';
                            $imageUrl = $imagePath ? str_replace('assets/', '', $imagePath) : '';
                            $price12 = (float)($item['price_12oz'] ?? $item['price']);
                            $price16 = (float)($item['price_16oz'] ?? $item['price']);
                        ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                            <td>&#8369;<?= number_format($price12, 2) ?></td>
                            <td>&#8369;<?= number_format($price16, 2) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td>
                                <?php if(isset($item['status']) && $item['status'] == 1): ?>
                                    <span class="badge bg-danger">Not Available</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                <a href="products-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Move this product to deleted items? You can restore it later.')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if(mysqli_num_rows($products) == 0): ?>
                <div class="alert alert-warning mt-3">No Products Found</div>
            <?php endif; ?>

            <hr class="my-4">
            <h5 class="mb-3"><i class="fas fa-trash-restore"></i> Deleted Products</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th width="80">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>12oz Price</th>
                            <th>16oz Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Deleted At</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($deletedProducts && mysqli_num_rows($deletedProducts) > 0): ?>
                            <?php while($item = mysqli_fetch_assoc($deletedProducts)) : ?>
                            <?php
                                $imagePath = $item['image'] ?? '';
                                $imageUrl = $imagePath ? str_replace('assets/', '', $imagePath) : '';
                                $price12 = (float)($item['price_12oz'] ?? $item['price']);
                                $price16 = (float)($item['price_16oz'] ?? $item['price']);
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                                <td>&#8369;<?= number_format($price12, 2) ?></td>
                                <td>&#8369;<?= number_format($price16, 2) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td>
                                    <?php if(isset($item['status']) && $item['status'] == 1): ?>
                                        <span class="badge bg-danger">Not Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($item['deleted_at']) ? date('M d, Y h:i A', strtotime($item['deleted_at'])) : '-' ?></td>
                                <td>
                                    <a href="products-restore.php?id=<?= $item['id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Restore this product?')">Restore</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No deleted products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
