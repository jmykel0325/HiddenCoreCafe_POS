<?php
require '../../config/function.php';
require '../../config/dbcon.php';
require 'authentication.php';

$statusColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusColumnCheck && mysqli_num_rows($statusColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_mode");
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// Get order details
$order_query = "SELECT * FROM orders WHERE id = $order_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: orders.php');
    exit;
}

if (in_array(strtolower((string)($order['order_status'] ?? 'pending')), ['completed', 'cancelled'], true)) {
    header('Location: orders.php?error=completed_locked');
    exit;
}

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
$order_items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

// Get all products for adding new items
$products_query = "SELECT id, name, image, price, price_12oz, price_16oz, quantity, category_id FROM products WHERE deleted_at IS NULL AND (status = 0 OR status IS NULL) ORDER BY name";
$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);
$productById = [];
$productIdByName = [];
$productImageByName = [];
foreach ($products as $prodItem) {
    $pid = (int)($prodItem['id'] ?? 0);
    $pname = (string)($prodItem['name'] ?? '');
    $pImagePath = (string)($prodItem['image'] ?? '');
    $pImageUrl = $pImagePath ? str_replace('assets/', '', $pImagePath) : '';
    if ($pid > 0) {
        $productById[$pid] = $prodItem;
    }
    if ($pname !== '') {
        $productIdByName[strtolower($pname)] = $pid;
        $productImageByName[strtolower($pname)] = $pImageUrl;
    }
}

// Get categories for display
$categories_query = "SELECT id, name FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
$categoryNames = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categoryNames[$cat['id']] = $cat['name'];
}

if (isset($_POST['update_order'])) {
    $lockCheckResult = mysqli_query($conn, "SELECT order_status FROM orders WHERE id = $order_id LIMIT 1");
    $lockCheck = $lockCheckResult ? mysqli_fetch_assoc($lockCheckResult) : null;
    if (in_array(strtolower((string)($lockCheck['order_status'] ?? 'pending')), ['completed', 'cancelled'], true)) {
        header('Location: orders.php?error=completed_locked');
        exit;
    }

    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode']);
    $gcash_reference = isset($_POST['gcash_reference']) ? mysqli_real_escape_string($conn, $_POST['gcash_reference']) : '';
    // Restore original quantities before updating
    foreach ($order_items as $item) {
        $restore_qty = intval($item['quantity']);
        $prod_name = mysqli_real_escape_string($conn, $item['product_name']);
        mysqli_query($conn, "UPDATE products SET quantity = quantity + $restore_qty WHERE name = '$prod_name'");
    }
    
    // Delete old order items
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");
    
    // Process new items
    $total = 0;
    $products_post = $_POST['products'] ?? [];
    
    foreach ($products_post as $p) {
        $prod_id = intval($p['id'] ?? 0);
        $qty = max(1, intval($p['quantity']));
        $size = isset($p['size']) && $p['size'] === '16oz' ? '16oz' : '12oz';

        $postedNameRaw = trim((string)($p['name'] ?? ''));
        if ($prod_id <= 0 && $postedNameRaw !== '') {
            $lookupKey = strtolower($postedNameRaw);
            if (isset($productIdByName[$lookupKey])) {
                $prod_id = (int)$productIdByName[$lookupKey];
            }
        }

        $prod_row = null;
        if ($prod_id > 0) {
            $prod_result = mysqli_query($conn, "SELECT id, name, price, price_12oz, price_16oz, category_id FROM products WHERE id = $prod_id LIMIT 1");
            if ($prod_result && mysqli_num_rows($prod_result) > 0) {
                $prod_row = mysqli_fetch_assoc($prod_result);
            }
        }

        if ($prod_row) {
            $price12 = isset($prod_row['price_12oz']) ? (float)$prod_row['price_12oz'] : (float)$prod_row['price'];
            $price16 = isset($prod_row['price_16oz']) ? (float)$prod_row['price_16oz'] : (float)$prod_row['price'];
            $unitPrice = $size === '16oz' ? $price16 : $price12;
            $product_name = mysqli_real_escape_string($conn, $prod_row['name']);
            $category = isset($categoryNames[$prod_row['category_id']]) ? mysqli_real_escape_string($conn, $categoryNames[$prod_row['category_id']]) : '';
        } else {
            $postedPrice = isset($p['price']) ? (float)$p['price'] : 0;
            if ($postedNameRaw === '' || $postedPrice <= 0) {
                continue;
            }
            $unitPrice = $postedPrice;
            $product_name = mysqli_real_escape_string($conn, $postedNameRaw);
            $category = isset($p['category']) ? mysqli_real_escape_string($conn, (string)$p['category']) : '';
        }

        // Insert new item
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_name, category, size, quantity, price) 
                            VALUES ($order_id, '$product_name', '$category', '$size', $qty, $unitPrice)");

        // Deduct quantity
        if ($prod_id > 0) {
            mysqli_query($conn, "UPDATE products SET quantity = quantity - $qty WHERE id = $prod_id");
        } else {
            mysqli_query($conn, "UPDATE products SET quantity = quantity - $qty WHERE name = '$product_name' LIMIT 1");
        }
        
        $total += $qty * $unitPrice;
    }
    
    $subtotal = $total;
    $discount_type = '';
    $discount_rate = 0;
    $discount_amount = 0;
    $final_total = $subtotal;
    
    // Update order
    if ($payment_mode === 'Cash') {
        $cash_received = isset($_POST['cash_received']) ? floatval($_POST['cash_received']) : 0;
        $change_due = $cash_received - $final_total;
        mysqli_query($conn, "UPDATE orders SET 
            customer_name = '$customer_name',
            payment_mode = '$payment_mode',
            discount_type = NULL,
            discount_rate = $discount_rate,
            discount_amount = $discount_amount,
            final_total = $final_total,
            total = $subtotal,
            cash_received = $cash_received,
            change_due = $change_due,
            gcash_reference = NULL
            WHERE id = $order_id");
    } else {
        mysqli_query($conn, "UPDATE orders SET 
            customer_name = '$customer_name',
            payment_mode = '$payment_mode',
            gcash_reference = '$gcash_reference',
            discount_type = NULL,
            discount_rate = $discount_rate,
            discount_amount = $discount_amount,
            final_total = $final_total,
            total = $subtotal
            WHERE id = $order_id");
    }
    
    header('Location: orders.php');
    exit;
}
?>

<?php include('includes/header.php'); ?>

<style>
    .hc-order-screen.hc-order-ref {
        --ref-bg: #f7f2ec;
        --ref-surface: #ffffff;
        --ref-primary: var(--primary, #FF7A1A);
        --ref-primary-deep: var(--primary-hover, #E96A0C);
        --ref-border: #e9e3dc;
        --ref-text: #3a342e;
        --ref-muted: #8a8178;
        max-width: 1520px;
        margin: 0 auto;
        padding: .5rem;
        color: var(--ref-text);
    }
    .hc-order-ref .hc-order-board {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 390px);
        gap: 1.25rem;
        align-items: start;
    }
    .hc-order-ref .hc-order-main {
        padding: 1.25rem;
        border-radius: 28px;
        border: 1px solid var(--ref-border);
        background: var(--ref-bg);
    }
    .hc-order-ref .hc-order-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: .85rem;
    }
    .hc-order-ref .hc-order-topbar .btn {
        border-radius: 999px;
    }
    .hc-order-ref .hc-order-search-inline {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: .95rem;
    }
    .hc-order-ref .hc-order-search-inline .form-control {
        max-width: 460px;
        min-height: 48px;
        border-radius: 999px !important;
        border: 1px solid var(--ref-border) !important;
        background: #fff !important;
        box-shadow: none !important;
    }
    .hc-order-ref .hc-order-section-title {
        margin: .2rem 0 .9rem;
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--ref-text);
    }
    .hc-order-ref .hc-order-grid {
        grid-template-columns: repeat(2, minmax(260px, 1fr)) !important;
        gap: .95rem;
    }
    .hc-order-ref .product-card {
        border-radius: 22px !important;
        border: 1px solid var(--ref-border) !important;
        background: #fff !important;
        box-shadow: 0 10px 22px rgba(58, 52, 46, .06) !important;
        padding: 1rem;
        min-height: 210px;
    }
    .hc-order-ref .hc-product-head {
        display: grid;
        grid-template-columns: 90px minmax(0, 1fr) !important;
        gap: .8rem;
    }
    .hc-order-ref .hc-product-thumb {
        width: 90px;
        height: 102px;
        border-radius: 14px;
        background: #faf7f3;
    }
    .hc-order-ref .product-name {
        color: var(--ref-text) !important;
        font-size: 1.08rem;
        font-weight: 700;
    }
    .hc-order-ref .hc-product-cat {
        color: var(--ref-muted) !important;
        font-size: .78rem;
        min-height: 2.2rem;
    }
    .hc-order-ref .hc-card-size-toggle {
        display: inline-flex;
        gap: .4rem;
        margin-top: .5rem;
    }
    .hc-order-ref .hc-size-pill {
        min-height: 32px;
        min-width: 68px;
        padding: .25rem .75rem;
        border-radius: 999px;
        border: 1px solid var(--ref-border);
        background: #fff;
        color: var(--ref-muted);
        font-weight: 700;
        font-size: .8rem;
    }
    .hc-order-ref .hc-size-pill.active {
        background: var(--ref-primary);
        border-color: var(--ref-primary);
        color: #fff;
    }
    .hc-order-ref .hc-product-actions {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: .55rem !important;
        align-items: stretch !important;
        margin-top: .65rem;
    }
    .hc-order-ref .hc-inline-qty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        width: max-content;
        margin: 0 auto;
        background: #fff7f0;
        border: 1px solid var(--ref-border);
        border-radius: 999px;
        padding: .1rem .2rem;
    }
    .hc-order-ref .hc-inline-qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        border: 1px solid var(--ref-border);
        background: #fff;
        color: var(--ref-text);
        line-height: 1;
        font-weight: 800;
    }
    .hc-order-ref .hc-inline-qty-input {
        width: 24px;
        border: 0;
        text-align: center;
        background: transparent;
        color: var(--ref-text);
        font-weight: 700;
    }
    .hc-order-ref .hc-add-cart-btn {
        min-height: 40px;
        border-radius: 999px !important;
        background: var(--ref-primary) !important;
        border: 1px solid var(--ref-primary) !important;
        color: #fff !important;
        font-weight: 700;
        width: 100%;
        white-space: nowrap;
    }
    .hc-order-ref .hc-order-side .hc-checkout {
        position: sticky;
        top: 1rem;
        height: calc(100vh - 8rem);
    }
    .hc-order-ref .hc-order-panel {
        height: 100%;
        border-radius: 26px !important;
        border: 1px solid var(--ref-border) !important;
        background: #fff !important;
        box-shadow: 0 12px 26px rgba(58, 52, 46, .08) !important;
        padding: 1rem !important;
    }
    .hc-order-ref .hc-cart-head h3 {
        font-size: 2rem;
        color: var(--ref-text);
    }
    .hc-order-ref .hc-cart-head p,
    .hc-order-ref .hc-order-label,
    .hc-order-ref .hc-order-summary-row {
        color: var(--ref-muted);
    }
    .hc-order-ref .selected-product-row {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto auto;
        gap: .55rem;
        align-items: center;
        padding: .65rem 0;
        border-bottom: 1px solid var(--ref-border);
    }
    .hc-order-ref .cart-item-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--ref-border);
        background: #faf7f3;
    }
    .hc-order-ref .cart-item-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .hc-order-ref .hc-order-summary-total strong {
        color: var(--ref-primary-deep);
        font-size: 1.6rem;
    }
    .hc-order-ref .hc-order-submit {
        min-height: 52px;
        border-radius: 999px !important;
        background: var(--ref-primary) !important;
        border: 1px solid var(--ref-primary) !important;
        box-shadow: none !important;
    }
    @media (max-width: 1199px) {
        .hc-order-ref .hc-order-grid {
            grid-template-columns: repeat(2, minmax(220px, 1fr)) !important;
        }
    }
    @media (max-width: 991px) {
        .hc-order-screen.hc-order-ref { padding: .2rem; }
        .hc-order-ref .hc-order-board { grid-template-columns: 1fr; }
        .hc-order-ref .hc-order-grid { grid-template-columns: 1fr !important; }
        .hc-order-ref .hc-order-side .hc-checkout { position: static; height: auto; }
    }
</style>

<div class="hc-pos hc-order-screen hc-order-ref">
    <form id="orderForm" method="POST">
        <div class="hc-order-board">
            <div class="hc-order-main">
                <div class="hc-order-topbar">
                    <div class="hc-order-topbar-left">
                        <span class="hc-topbar-dot"><i class="fas fa-pen-to-square"></i></span>
                        <span class="hc-topbar-text">Editing existing order #<?= $order_id ?></span>
                    </div>
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>

                <div class="hc-order-search-inline">
                    <input type="text" id="productSearchInput" class="form-control" placeholder="Search product by name...">
                </div>

                <div class="hc-pos-catalog">
                    <h2 class="hc-order-section-title">Coffee menu</h2>
                    <div class="hc-product-grid hc-order-grid">
                        <?php foreach ($products as $item): ?>
                            <?php
                                $imagePath = $item['image'] ?? '';
                                $imageUrl = $imagePath ? str_replace('assets/', '', $imagePath) : '';
                                $price12 = (float)($item['price_12oz'] ?? $item['price']);
                                $price16 = (float)($item['price_16oz'] ?? $item['price']);
                            ?>
                            <div class="hc-product-cell">
                                <div class="product-card <?= (int) $item['quantity'] === 0 ? 'out-of-stock' : '' ?>"
                                     data-id="<?= (int) $item['id'] ?>"
                                     data-name="<?= htmlspecialchars($item['name']) ?>"
                                     data-price12="<?= htmlspecialchars((string)$price12) ?>"
                                     data-price16="<?= htmlspecialchars((string)$price16) ?>"
                                     data-image="<?= htmlspecialchars($imageUrl) ?>"
                                     data-quantity="<?= (int) $item['quantity'] ?>"
                                     <?= (int) $item['quantity'] === 0 ? 'data-disabled="true"' : '' ?>>
                                    <div class="hc-product-head">
                                        <div class="hc-product-media hc-product-thumb">
                                            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                        </div>
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="hc-product-card-footer">
                                                <div class="hc-product-cat"><?= htmlspecialchars($categoryNames[$item['category_id']] ?? 'Uncategorized') ?> | 12oz &#8369;<?= number_format($price12, 2) ?> | 16oz &#8369;<?= number_format($price16, 2) ?></div>
                                            </div>
                                            <div class="hc-card-size-toggle">
                                                <button type="button" class="hc-size-pill active" data-card-size="12oz">12oz</button>
                                                <button type="button" class="hc-size-pill" data-card-size="16oz">16oz</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hc-product-actions">
                                        <div class="hc-inline-qty">
                                            <button type="button" class="hc-inline-qty-btn" data-card-dec>-</button>
                                            <input type="text" class="hc-inline-qty-input" data-card-qty value="1" readonly>
                                            <button type="button" class="hc-inline-qty-btn" data-card-inc>+</button>
                                        </div>
                                        <button type="button" class="btn hc-add-cart-btn w-100" data-add-to-cart>Add to cart</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="hc-order-side">
                <div class="hc-checkout">
                    <div class="hc-checkout-card hc-order-panel">
                        <div class="hc-cart-head">
                            <h3>Cart</h3>
                            <p>Editing Order #<?= $order_id ?></p>
                        </div>

                        <div class="hc-order-panel-section">
                            <label for="customerName" class="hc-order-label">Customer Name</label>
                            <input type="text" name="customer_name" id="customerName" required class="form-control"
                                   value="<?= htmlspecialchars($order['customer_name']) ?>" placeholder="Enter customer name">
                        </div>

                        <div class="hc-order-panel-section">
                            <label for="paymentMode" class="hc-order-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select" id="paymentMode" required>
                                <option value="Cash" <?= $order['payment_mode'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="GCash" <?= $order['payment_mode'] === 'GCash' ? 'selected' : '' ?>>GCash</option>
                            </select>
                        </div>

                        <div class="hc-order-panel-section" id="cashGivenContainer" style="<?= $order['payment_mode'] === 'Cash' ? '' : 'display:none;' ?>">
                            <label for="cashGiven" class="hc-order-label">Cash Received</label>
                            <input type="number" min="0" step="0.01" name="cash_received" id="cashGiven" class="form-control"
                                   value="<?= $order['cash_received'] ?? 0 ?>">
                            <input type="hidden" name="change_due" id="changeDueInput" value="<?= $order['change_due'] ?? 0 ?>">
                            <div id="changeDisplay" class="hc-order-change" style="<?= $order['change_due'] > 0 ? '' : 'display:none;' ?>">
                                Change Due: &#8369;<span id="changeAmount"><?= number_format($order['change_due'] ?? 0, 2) ?></span>
                            </div>
                        </div>

                        <div class="hc-order-panel-section" id="gcashReferenceContainer" style="<?= $order['payment_mode'] === 'GCash' ? '' : 'display:none;' ?>">
                            <label for="gcashReference" class="hc-order-label">GCash Reference Number</label>
                            <input type="text" name="gcash_reference" id="gcashReference" class="form-control"
                                   value="<?= htmlspecialchars($order['gcash_reference'] ?? '') ?>" placeholder="Enter GCash reference number">
                        </div>

                        <div class="hc-order-panel-section hc-order-panel-section-items">
                            <div class="hc-order-items-heading">Your order:</div>
                            <div id="selectedProducts" class="hc-checkout-items">
                                <div class="hc-order-empty-state hc-order-empty-state-inline">
                                    <div class="hc-order-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                                    <p class="mb-0">No items in order</p>
                                </div>
                            </div>
                        </div>

                        <div class="hc-order-summary">
                            <div class="hc-order-summary-row hc-order-summary-total">
                                <span>Total</span>
                                <strong>&#8369;<span id="grandTotal">0.00</span></strong>
                            </div>
                        </div>

                        <div class="hc-checkout-footer">
                            <button type="submit" name="update_order" id="placeOrderBtn" class="btn btn-success w-100 hc-order-submit">Update Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Pre-load existing order items
    let selectedProducts = {};
    const fallbackItemImage = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" rx="12" fill="%23f3ede6"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-size="22" fill="%23c7b9a8">☕</text></svg>';
    const lineKeyOf = (id, size, name = '') => {
        const normalizedName = String(name || '').trim().toLowerCase();
        const base = (String(id || '0') !== '0') ? String(id) : `name:${normalizedName}`;
        return `${base}__${size}`;
    };

    <?php foreach ($order_items as $item): ?>
        (function() {
            const existingName = '<?= htmlspecialchars(addslashes($item['product_name'] ?? '')) ?>';
            const existingId = <?= (int)($productIdByName[strtolower((string)($item['product_name'] ?? ''))] ?? 0) ?>;
            const existingImage = '<?= htmlspecialchars(addslashes((string)($productImageByName[strtolower((string)($item['product_name'] ?? ''))] ?? ''))) ?>';
            const lineKey = lineKeyOf(existingId, '<?= $item['size'] ?? '12oz' ?>', existingName);
            selectedProducts[lineKey] = {
                id: existingId,
                name: existingName,
                size: '<?= $item['size'] ?? '12oz' ?>',
                price12: <?= (float)($item['price'] ?? 0) ?>,
                price16: <?= (float)($item['price'] ?? 0) ?>,
                unitPrice: <?= (float)($item['price'] ?? 0) ?>,
                quantity: <?= (int)($item['quantity'] ?? 1) ?>,
                maxQuantity: 999,
                image: existingImage
            };
        })();
    <?php endforeach; ?>

    function selectedQtyForProduct(productId) {
        let totalQty = 0;
        for (const key in selectedProducts) {
            const p = selectedProducts[key];
            if (String(p.id) === String(productId)) {
                totalQty += p.quantity;
            }
        }
        return totalQty;
    }

    function renderSelectedProducts() {
        const container = document.getElementById('selectedProducts');
        const emptyOrderMarkup = `
            <div class="hc-order-empty-state hc-order-empty-state-inline">
                <div class="hc-order-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                <p class="mb-0">No items in order</p>
            </div>
        `;
        container.innerHTML = '';
        let total = 0;
        let hasProducts = false;
        for (const lineKey in selectedProducts) {
            hasProducts = true;
            const prod = selectedProducts[lineKey];
            total += prod.unitPrice * prod.quantity;
            const thumb = prod.image ? prod.image : fallbackItemImage;
            const row = document.createElement('div');
            row.className = 'selected-product-row';
            row.innerHTML = `
                <div class="cart-item-thumb"><img src="${thumb}" alt="${prod.name}"></div>
                <div class="product-title">
                    <div class="cart-item-name">${prod.name}</div>
                    <div class="cart-item-size-text">${prod.size}</div>
                </div>
                <div class="qty-controls">
                    <button type="button" class="incdec-btn" data-decrement="${lineKey}" title="Decrease">
                        <svg viewBox="0 0 20 20" fill="none"><rect x="4" y="9" width="12" height="2" rx="1" fill="currentColor"/></svg>
                    </button>
                    <input type="number" min="1" value="${prod.quantity}" data-id="${lineKey}" class="form-control text-center" style="width:60px;" readonly>
                    <button type="button" class="incdec-btn" data-increment="${lineKey}" title="Increase">
                        <svg viewBox="0 0 20 20" fill="none"><rect x="9" y="4" width="2" height="12" rx="1" fill="currentColor"/><rect x="4" y="9" width="12" height="2" rx="1" fill="currentColor"/></svg>
                    </button>
                </div>
                <div class="product-line-actions">
                    <div class="product-total">&#8369;${(prod.unitPrice * prod.quantity).toFixed(2)}</div>
                    <button type="button" class="hc-row-delete-btn remove-btn" data-remove="${lineKey}" title="Remove item" aria-label="Remove item">&times;</button>
                </div>
                <input type="hidden" name="products[${lineKey}][id]" value="${prod.id}">
                <input type="hidden" name="products[${lineKey}][quantity]" value="${prod.quantity}" id="hidden-qty-${lineKey}">
                <input type="hidden" name="products[${lineKey}][price]" value="${prod.unitPrice}" id="hidden-price-${lineKey}">
                <input type="hidden" name="products[${lineKey}][name]" value="${prod.name}">
                <input type="hidden" name="products[${lineKey}][size]" value="${prod.size}" id="hidden-size-${lineKey}">
            `;
            container.appendChild(row);
        }
        if (!hasProducts) {
            container.innerHTML = emptyOrderMarkup;
        }
        document.getElementById('grandTotal').textContent = total.toFixed(2);
        document.getElementById('grandTotal').textContent = total.toFixed(2);
        
        attachIncDecListeners();
        attachRemoveListeners();
        updateChangeDue();
    }

    function attachIncDecListeners() {
        document.querySelectorAll('#selectedProducts [data-increment]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const lineKey = this.getAttribute('data-increment');
                const prod = selectedProducts[lineKey];
                if (!prod) return;
                prod.quantity += 1;
                document.getElementById('hidden-qty-' + lineKey).value = prod.quantity;
                renderSelectedProducts();
            });
        });
        document.querySelectorAll('#selectedProducts [data-decrement]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const lineKey = this.getAttribute('data-decrement');
                if (selectedProducts[lineKey] && selectedProducts[lineKey].quantity > 1) {
                    selectedProducts[lineKey].quantity -= 1;
                    document.getElementById('hidden-qty-' + lineKey).value = selectedProducts[lineKey].quantity;
                    renderSelectedProducts();
                }
            });
        });
    }

    function attachRemoveListeners() {
        document.querySelectorAll('#selectedProducts [data-remove]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const lineKey = this.getAttribute('data-remove');
                delete selectedProducts[lineKey];
                renderSelectedProducts();
            });
        });
    }

    function addProductToOrder(card, chosenSize = '12oz', qtyToAdd = 1) {
        if (card.dataset.disabled === 'true') {
            alert('Sorry, this product is currently out of stock.');
            return;
        }
        const id = card.dataset.id;
        const name = card.dataset.name;
        const rawPrice12 = parseFloat(card.dataset.price12);
        const rawPrice16 = parseFloat(card.dataset.price16);
        const price12 = Number.isFinite(rawPrice12) ? rawPrice12 : 0;
        const price16 = Number.isFinite(rawPrice16) ? rawPrice16 : price12;
        const maxQuantity = parseInt(card.dataset.quantity);
        const image = card.dataset.image || '';
        const lineKey = lineKeyOf(id, chosenSize, name);
        const requestedQty = Math.max(1, parseInt(qtyToAdd) || 1);

        if (selectedProducts[lineKey]) {
            selectedProducts[lineKey].quantity += requestedQty;
        } else {
            selectedProducts[lineKey] = {
                id: id,
                name: name,
                size: chosenSize,
                price12: price12,
                price16: price16,
                unitPrice: chosenSize === '16oz' ? price16 : price12,
                quantity: requestedQty,
                maxQuantity: maxQuantity,
                image: image
            };
        }
        renderSelectedProducts();
    }

    document.querySelectorAll('.product-card').forEach(function(card) {
        const qtyInput = card.querySelector('[data-card-qty]');
        const incBtn = card.querySelector('[data-card-inc]');
        const decBtn = card.querySelector('[data-card-dec]');
        const addBtn = card.querySelector('[data-add-to-cart]');
        const sizeBtns = card.querySelectorAll('[data-card-size]');
        let selectedSize = '12oz';

        sizeBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                selectedSize = this.getAttribute('data-card-size') === '16oz' ? '16oz' : '12oz';
                sizeBtns.forEach(function(other) {
                    other.classList.toggle('active', other === btn);
                });
            });
        });

        if (incBtn && qtyInput) {
            incBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const current = parseInt(qtyInput.value) || 1;
                const max = parseInt(card.dataset.quantity) || 1;
                qtyInput.value = String(Math.min(max, current + 1));
            });
        }

        if (decBtn && qtyInput) {
            decBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const current = parseInt(qtyInput.value) || 1;
                qtyInput.value = String(Math.max(1, current - 1));
            });
        }

        if (addBtn && qtyInput) {
            addBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const qty = parseInt(qtyInput.value) || 1;
                addProductToOrder(card, selectedSize, qty);
                qtyInput.value = '1';
            });
        }
    });

    const productSearchInput = document.getElementById('productSearchInput');
    if (productSearchInput) {
        productSearchInput.addEventListener('input', function() {
            const keyword = (this.value || '').trim().toLowerCase();
            document.querySelectorAll('.hc-product-cell .product-card').forEach(function(card) {
                const name = (card.dataset.name || '').toLowerCase();
                const showCard = keyword === '' || name.startsWith(keyword);
                const cell = card.closest('.hc-product-cell');
                if (cell) {
                    cell.style.display = showCard ? '' : 'none';
                }
            });
        });
    }

    const paymentMode = document.getElementById('paymentMode');
    const cashGivenContainer = document.getElementById('cashGivenContainer');
    const cashGivenInput = document.getElementById('cashGiven');
    const grandTotalSpan = document.getElementById('grandTotal');
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    const changeDueInput = document.getElementById('changeDueInput');
    const gcashReferenceContainer = document.getElementById('gcashReferenceContainer');
    const gcashReferenceInput = document.getElementById('gcashReference');

    function updatePaymentMethodView() {
        cashGivenContainer.style.display = 'none';
        gcashReferenceContainer.style.display = 'none';
        gcashReferenceInput.required = false;

        if (paymentMode.value === 'Cash') {
            cashGivenContainer.style.display = '';
        } else if (paymentMode.value === 'GCash') {
            gcashReferenceContainer.style.display = '';
            gcashReferenceInput.required = true;
        }
    }

    function updateChangeDue() {
        const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;
        const cash = parseFloat(cashGivenInput.value) || 0;
        if (paymentMode.value === 'Cash' && cash >= total && total > 0) {
            const change = (cash - total).toFixed(2);
            changeAmount.textContent = change;
            changeDisplay.style.display = '';
            changeDueInput.value = change;
        } else {
            changeDisplay.style.display = 'none';
            changeDueInput.value = '0.00';
        }
    }

    paymentMode.addEventListener('change', function() {
        updatePaymentMethodView();
        updateChangeDue();
    });

    cashGivenInput.addEventListener('input', function() {
        updateChangeDue();
    });

    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (Object.keys(selectedProducts).length === 0) {
            alert('Please add at least one product to the order.');
            e.preventDefault();
            return false;
        }

        if (paymentMode.value === 'Cash') {
            const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;
            const cash = parseFloat(cashGivenInput.value) || 0;
            if (cash < total) {
                alert('Cash received is less than the total amount.');
                cashGivenInput.focus();
                e.preventDefault();
                return false;
            }
        }

        if (paymentMode.value === 'GCash' && gcashReferenceInput.value.trim() === '') {
            alert('Please enter the GCash reference number.');
            gcashReferenceInput.focus();
            e.preventDefault();
            return false;
        }
    });

    updatePaymentMethodView();
    renderSelectedProducts();
</script>

<?php include('includes/footer.php'); ?>
