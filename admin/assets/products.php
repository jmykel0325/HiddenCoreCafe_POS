<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?php $selectedCategoryId = (isset($_GET['category']) && is_numeric($_GET['category'])) ? (int)$_GET['category'] : null; ?>

<style>
    .hc-products-page.hc-products-ref {
        max-width: 1520px;
        margin: 0 auto;
    }

    .hc-products-ref .hc-products-filter {
        margin: 1rem 0 1.2rem;
        padding: 1rem 1.1rem;
        border-radius: 22px;
        border: 1px solid #e9e3dc;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(47, 42, 37, .05);
    }

    .hc-products-ref .hc-products-filter-title {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin: 0 0 .7rem;
        padding: .3rem .62rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        color: #b8612b;
        background: #fff1e7;
        border: 1px solid #ffd8bf;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .hc-products-ref .hc-products-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: .52rem;
    }

    .hc-products-ref .hc-filter-chip {
        min-height: 40px;
        border-radius: 999px !important;
        border: 1px solid #e9e3dc !important;
        background: #ffffff !important;
        color: #4f4740 !important;
        font-weight: 700 !important;
        box-shadow: none !important;
        padding: .45rem .9rem !important;
    }

    .hc-products-ref .hc-filter-chip:hover {
        background: #fff4eb !important;
        border-color: #ffd8bf !important;
        color: #b8612b !important;
    }

    .hc-products-ref .hc-filter-chip.active {
        background: var(--primary, #FF7A1A) !important;
        border-color: var(--primary, #FF7A1A) !important;
        color: #ffffff !important;
    }

    .hc-products-ref .card {
        border-radius: 24px !important;
        overflow: hidden;
    }

    .hc-products-ref .card-header h4 {
        margin: 0;
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .hc-products-ref .card-header .btn {
        min-height: 42px;
        padding: .5rem 1rem !important;
    }

    .hc-products-ref .table-responsive {
        border-radius: 16px;
        overflow: hidden;
    }

    .hc-products-ref table.table thead th {
        background: #fdf8f4 !important;
        color: #423b34 !important;
        font-weight: 800 !important;
        font-size: .92rem;
        vertical-align: middle !important;
    }

    .hc-products-ref table.table tbody td {
        padding: .9rem .85rem !important;
        vertical-align: middle !important;
    }

    .hc-products-ref td:nth-child(2) {
        color: #2f2a25 !important;
        font-weight: 800;
    }

    .hc-products-ref td:nth-child(4),
    .hc-products-ref td:nth-child(5),
    .hc-products-ref td:nth-child(6) {
        font-weight: 700;
        color: #3f3831 !important;
    }

    .hc-products-ref .img-thumbnail {
        width: 52px !important;
        height: 52px !important;
        border-radius: 12px !important;
        border: 1px solid #e9e3dc !important;
        background: #fff;
        object-fit: cover !important;
        padding: 0 !important;
    }

    .hc-products-ref .badge {
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px !important;
        padding: .34rem .66rem !important;
        font-weight: 700 !important;
    }

    .hc-products-ref .btn.btn-sm {
        min-height: 30px !important;
        padding: .22rem .62rem !important;
        font-size: .76rem !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
    }

    .hc-products-ref .btn.btn-success.btn-sm {
        background: var(--primary, #FF7A1A) !important;
        border-color: var(--primary, #FF7A1A) !important;
        color: #fff !important;
    }

    .hc-products-ref .btn.btn-success.btn-sm:hover {
        background: var(--primary-hover, #E96A0C) !important;
        border-color: var(--primary-hover, #E96A0C) !important;
    }

    .hc-products-ref .hc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .34rem;
        align-items: center;
        min-width: max-content;
    }

    .hc-products-ref .hc-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: auto;
        min-width: 52px;
        white-space: nowrap;
    }

    .hc-products-ref .hc-deleted-head {
        margin: 1.2rem 0 .8rem;
        padding-top: 1rem;
        border-top: 1px dashed #eadfd4;
        display: flex;
        align-items: center;
        gap: .5rem;
        color: #3f3831;
        font-size: 1.02rem;
        font-weight: 800;
    }

    .hc-products-ref .hc-empty-deleted {
        display: grid;
        place-items: center;
        gap: .35rem;
        padding: 1.35rem 1rem;
        border-radius: 14px;
        border: 1px dashed #e9e3dc;
        background: #fffdfa;
        color: #7a726b;
        font-weight: 600;
        text-align: center;
    }

    .hc-products-ref .hc-empty-deleted i {
        color: #d3c4b6;
        font-size: 1.2rem;
    }
</style>

<div class="container-fluid px-4 hc-admin-page hc-products-page hc-products-ref">
<div class="hc-products-filter">
    <div class="hc-products-filter-title"><i class="fas fa-mug-hot"></i> Choose Your Flavor</div>
    <div class="hc-products-filter-row">
        <a href="products.php" class="btn hc-filter-chip d-flex align-items-center gap-2 <?= $selectedCategoryId === null ? 'active' : '' ?>">
            <i class="fas fa-mug-hot"></i> All
        </a>
        <?php
            $categories = getAll('categories');
            if($categories && mysqli_num_rows($categories) > 0):
                foreach($categories as $cat):
        ?>
            <a href="products.php?category=<?= $cat['id']; ?>" class="btn hc-filter-chip d-flex align-items-center gap-2 <?= $selectedCategoryId === (int)$cat['id'] ? 'active' : '' ?>">
                <i class="fas fa-mug-hot"></i> <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php
                endforeach;
            endif;
        ?>
    </div>
</div>
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
                            <td>
                                <?php if(isset($item['status']) && $item['status'] == 1): ?>
                                    <span class="badge bg-danger">Not Available</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="hc-actions">
                                    <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                    <a href="products-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Move this product to deleted items? You can restore it later.')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if(mysqli_num_rows($products) == 0): ?>
                <div class="alert alert-warning mt-3">No Products Found</div>
            <?php endif; ?>

            <div class="hc-deleted-head"><i class="fas fa-trash-restore"></i> Deleted Products</div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th width="80">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>12oz Price</th>
                            <th>16oz Price</th>
                            <th>Status</th>
                            <th>Deleted At</th>
                            <th width="200">Actions</th>
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
                                <td>
                                    <?php if(isset($item['status']) && $item['status'] == 1): ?>
                                        <span class="badge bg-danger">Not Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($item['deleted_at']) ? date('M d, Y h:i A', strtotime($item['deleted_at'])) : '-' ?></td>
                                <td>
                                    <div class="hc-actions">
                                        <a href="products-restore.php?id=<?= $item['id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Restore this product?')">Restore</a>
                                        <a href="products-force-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this product? This cannot be undone.')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="hc-empty-deleted">
                                        <i class="fas fa-box-open"></i>
                                        <span>No deleted products found.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

