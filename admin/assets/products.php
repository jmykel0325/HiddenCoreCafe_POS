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
                $products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = $categoryId");
            } else {
                $products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
            } ?>

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
                            $imagePath = ltrim((string)($item['image'] ?? ''), '/');
                            $imageUrl = '/HiddenCoreCafe_POS/admin/' . $imagePath;
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
                                <a href="products-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                <button type="button" class="btn btn-info btn-sm" onclick="printProductQR('<?= htmlspecialchars($item['name']) ?>', '<?= $price12 ?>', '<?= $price16 ?>', '<?= (int)$item['quantity'] ?>')">Print QR</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if(mysqli_num_rows($products) == 0): ?>
                <div class="alert alert-warning mt-3">No Products Found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function printProductQR(name, price12, price16, qty) {
    const qrData = `Hidden Core Cafe\\nProduct: ${name}\\n12oz: ₱${price12}\\n16oz: ₱${price16}\\nStock: ${qty}`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrData)}`;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print QR Code - ${name}</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                .qr-container { display: inline-block; border: 2px solid #333; padding: 20px; border-radius: 10px; }
                .product-name { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .product-details { font-size: 14px; color: #666; margin-top: 10px; }
                img { max-width: 200px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <div class="product-name">${name}</div>
                <img src="${qrUrl}" alt="QR Code">
                <div class="product-details">
                    12oz: ₱${price12} | 16oz: ₱${price16}<br>
                    Stock: ${qty}
                </div>
            </div>
            <script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); };<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<?php include('includes/footer.php'); ?>
