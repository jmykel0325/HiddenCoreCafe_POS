<?php
include('includes/header.php');

$selectedCategoryId = null;
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $selectedCategoryId = (int) $_GET['category'];
}

$currentAdminName = $_SESSION['loggedInUser']['username'] ?? 'Admin';
$currentUserId = (int)($_SESSION['loggedInUser']['user_id'] ?? 0);
if ($currentUserId > 0) {
    $userResult = mysqli_query($conn, "SELECT first_name, last_name, username FROM cashier_staff WHERE id = {$currentUserId} LIMIT 1");
    if ($userResult && mysqli_num_rows($userResult) === 1) {
        $userRow = mysqli_fetch_assoc($userResult);
        $fullName = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? ''));
        if ($fullName !== '') {
            $currentAdminName = $fullName;
        } elseif (!empty($userRow['username'])) {
            $currentAdminName = $userRow['username'];
        }
    }
}
$currentHour = (int) date('G');
$greeting = 'Good evening';
if ($currentHour < 12) {
    $greeting = 'Good morning';
} elseif ($currentHour < 18) {
    $greeting = 'Good afternoon';
}

$categories = [];
$categoryNames = [];
$categoryCounts = [];
$totalProductCount = 0;
ensureProductsDeletedAtColumn();

$categoriesResult = getAll('categories');
if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0) {
    while ($categoryRow = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $categoryRow;
        $categoryNames[(int) $categoryRow['id']] = $categoryRow['name'];
    }
}

$categoryCountResult = mysqli_query($conn, "SELECT category_id, COUNT(*) AS total FROM products WHERE deleted_at IS NULL GROUP BY category_id");
if ($categoryCountResult) {
    while ($countRow = mysqli_fetch_assoc($categoryCountResult)) {
        $categoryCounts[(int) $countRow['category_id']] = (int) $countRow['total'];
    }
}

$totalCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE deleted_at IS NULL");
if ($totalCountResult) {
    $totalCountRow = mysqli_fetch_assoc($totalCountResult);
    $totalProductCount = (int) ($totalCountRow['total'] ?? 0);
}

if ($selectedCategoryId !== null) {
    $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$selectedCategoryId} AND deleted_at IS NULL");
} else {
    $products = getAll('products');
}

$paymongoSecretKey = defined('PAYMONGO_SECRET_KEY') ? trim((string) PAYMONGO_SECRET_KEY) : trim((string) getenv('PAYMONGO_SECRET_KEY'));
$paymongoEnabled = $paymongoSecretKey !== ''
    && !str_contains($paymongoSecretKey, 'replace_with')
    && !str_contains($paymongoSecretKey, 'actual_paymongo_secret_key');
?>

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

    .hc-order-ref .hc-order-filter-btn {
        min-height: 46px;
        border-radius: 999px;
        border: 1px solid transparent;
        background: var(--ref-primary);
        color: #fff;
        font-weight: 700;
        padding: .55rem 1rem;
    }

    .hc-order-ref .hc-order-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-bottom: .9rem;
    }

    .hc-order-ref .hc-category-chip {
        min-height: 40px;
        border-radius: 999px;
        border: 1px solid var(--ref-border);
        background: #fff;
        color: var(--ref-muted);
        box-shadow: none;
    }

    .hc-order-ref .hc-category-chip.active {
        background: var(--ref-primary);
        color: #fff !important;
        border-color: var(--ref-primary);
    }

    .hc-order-ref .hc-category-chip.active .hc-category-chip-count,
    .hc-order-ref .hc-category-chip.active span {
        color: #fff !important;
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

    .hc-order-ref .hc-product-actions {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: .55rem !important;
        align-items: stretch !important;
    }

    .hc-order-ref .hc-inline-qty {
        justify-content: center;
        width: max-content;
        margin: 0 auto;
    }

    .hc-order-ref .hc-product-head {
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

    .hc-order-ref .hc-size-pill {
        border-radius: 999px;
        background: #fff !important;
        border: 1px solid var(--ref-border) !important;
        color: var(--ref-muted) !important;
    }

    .hc-order-ref .hc-size-pill.active {
        background: var(--ref-primary) !important;
        border-color: var(--ref-primary) !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    .hc-order-ref .hc-inline-qty {
        background: #fff7f0;
        border: 1px solid var(--ref-border);
    }

    .hc-order-ref .hc-inline-qty-btn {
        background: #fff !important;
        border: 1px solid var(--ref-border) !important;
        color: var(--ref-text) !important;
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
        .hc-order-screen.hc-order-ref {
            padding: .2rem;
        }
        .hc-order-ref .hc-order-board {
            grid-template-columns: 1fr;
        }
        .hc-order-ref .hc-order-grid {
            grid-template-columns: 1fr !important;
        }
        .hc-order-ref .hc-order-side .hc-checkout {
            position: static;
            height: auto;
        }
    }
</style>

<div class="hc-pos hc-order-screen hc-order-ref">
    <?php alertMessage(); ?>

    <form action="place_order.php" method="POST" id="orderForm">
        <div class="hc-order-board">
            <div class="hc-order-main">
                <div class="hc-order-topbar">
                    <div class="hc-order-topbar-left">
                        <span class="hc-topbar-dot"><i class="fas fa-location-dot"></i></span>
                        <span class="hc-topbar-text">Hidden Core Cafe POS</span>
                    </div>
                    <div class="hc-order-topbar-right">
                        <span class="hc-topbar-text">Welcome, <strong><?= htmlspecialchars($currentAdminName) ?></strong></span>
                    </div>
                </div>

                <div class="hc-order-search-inline">
                    <input type="text" id="productSearchInput" class="form-control" placeholder="Search product by name...">
                    <button type="button" class="hc-order-filter-btn"><i class="fas fa-sliders-h me-1"></i>Filter</button>
                </div>

                <div class="hc-order-filter-row">
                    <a href="orders-create.php" class="hc-category-chip <?= $selectedCategoryId === null ? 'active' : '' ?>">
                        <span>All</span>
                        <span class="hc-category-chip-count">(<?= $totalProductCount ?>)</span>
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <?php $catId = (int) $cat['id']; ?>
                            <a href="orders-create.php?category=<?= $catId ?>" class="hc-category-chip <?= $selectedCategoryId === $catId ? 'active' : '' ?>">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="hc-category-chip-count">(<?= $categoryCounts[$catId] ?? 0 ?>)</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="hc-pos-catalog">
                    <h2 class="hc-order-section-title">Coffee menu</h2>
                    <?php if ($products && mysqli_num_rows($products) > 0): ?>
                        <div class="hc-product-grid hc-order-grid">
                            <?php while ($item = mysqli_fetch_assoc($products)): ?>
                                <?php $productCategoryName = $categoryNames[(int) $item['category_id']] ?? 'Uncategorized'; ?>
                                <?php
                                    $imagePath = $item['image'] ?? '';
                                    // Database stores: assets/upload/products/filename.jpg
                                    // We're in admin/assets/, so strip 'assets/' to get: upload/products/filename.jpg
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
                                                <?php if ((int) $item['quantity'] === 0): ?>
                                                    <div class="out-of-stock-overlay">Out of Stock</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="product-info">
                                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="hc-product-card-footer">
                                                    <div class="hc-product-cat"><?= htmlspecialchars($productCategoryName) ?> | 12oz &#8369;<?= number_format($price12, 2) ?> | 16oz &#8369;<?= number_format($price16, 2) ?></div>
                                                </div>
                                                <div class="hc-card-size-toggle mt-2">
                                                    <button type="button" class="hc-size-pill active" data-card-size="12oz">12oz</button>
                                                    <button type="button" class="hc-size-pill" data-card-size="16oz">16oz</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hc-product-actions mt-2">
                                            <div class="hc-inline-qty">
                                                <button type="button" class="hc-inline-qty-btn" data-card-dec title="Decrease">-</button>
                                                <input type="text" class="hc-inline-qty-input" data-card-qty value="1" readonly>
                                                <button type="button" class="hc-inline-qty-btn" data-card-inc title="Increase">+</button>
                                            </div>
                                            <button type="button" class="btn hc-add-cart-btn w-100" data-add-to-cart>Add to cart</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="hc-order-empty-state">
                            <div class="hc-order-empty-icon"><i class="fas fa-mug-hot"></i></div>
                            <p class="mb-0">No products found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hc-order-side">
                <div class="hc-checkout">
                    <div class="hc-checkout-card hc-order-panel">
                        <div class="hc-cart-head">
                            <h3>Cart</h3>
                            <p>Order #NEW</p>
                        </div>
                        <div class="hc-order-panel-section hc-order-panel-section-items">
                            <div class="hc-order-items-heading">Your order:</div>
                            <div id="selectedProducts" class="hc-checkout-items">
                                <div class="hc-order-empty-state hc-order-empty-state-inline">
                                    <div class="hc-order-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                                    <p class="mb-0">No items in order</p>
                                    <small>Add drinks from the left to start order.</small>
                                </div>
                            </div>
                        </div>

                        <div id="checkoutDetails" style="display:none;">
                            <div class="hc-order-panel-section">
                                <label for="customerName" class="hc-order-label">Customer Name</label>
                                <input type="text" name="customer_name" id="customerName" class="form-control" placeholder="Enter customer name">
                            </div>

                            <div class="hc-order-panel-section">
                                <label for="paymentMode" class="hc-order-label">Payment Mode</label>
                                <select name="payment_mode" class="form-select" id="paymentMode">
                                    <option value="Cash">Cash</option>
                                    <option value="GCash">GCash</option>
                                </select>
                            </div>

                            <div class="hc-order-panel-section" id="cashGivenContainer" style="display:none;">
                                <label for="cashGiven" class="hc-order-label">Cash Received</label>
                                <input type="number" min="0" step="0.01" name="cash_received" id="cashGiven" class="form-control">
                                <input type="hidden" name="change_due" id="changeDueInput" value="0.00">
                                <div id="changeDisplay" class="hc-order-change" style="display:none;">
                                    Change Due: &#8369;<span id="changeAmount">0.00</span>
                                </div>
                            </div>

                            <div class="hc-order-panel-section" id="gcashReferenceContainer" style="display:none;">
                                <label for="gcashReference" class="hc-order-label">GCash Reference Number</label>
                                <input type="text" name="gcash_reference" id="gcashReference" class="form-control" placeholder="Enter GCash reference number">
                            </div>
                        </div>

                        <div class="hc-order-summary">
                            <div class="hc-order-summary-row hc-order-summary-total">
                                <span>Total</span>
                                <strong>&#8369;<span id="grandTotal">0.00</span></strong>
                            </div>
                        </div>

                        <div class="hc-checkout-footer">
                            <button type="submit" name="place_order" id="placeOrderBtn" class="btn btn-success w-100 hc-order-submit">Checkout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="notifToast" class="notif-toast"></div>

<script>
    function showNotif(message, color = '#ff7a1a') {
        const toast = document.getElementById('notifToast');
        toast.textContent = message;
        toast.style.background = color;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    let selectedProducts = {};
    let isCheckoutStep = false;
    const lineKeyOf = (id, size) => `${id}__${size}`;

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

    function updateProductCardQuantities() {
        document.querySelectorAll('.product-card').forEach(function(card) {
            const id = card.dataset.id;
            const maxQty = parseInt(card.dataset.quantity);
            let selectedQty = selectedQtyForProduct(id);
            let displayQty = maxQty - selectedQty;
            const qtyElem = card.querySelector('.product-quantity strong');
            if (qtyElem) qtyElem.textContent = displayQty;
            card.classList.toggle('is-selected', selectedQty > 0);
            if (displayQty <= 0) {
                card.classList.add('out-of-stock');
                card.setAttribute('data-disabled', 'true');
                if (!card.querySelector('.out-of-stock-overlay')) {
                    const media = card.querySelector('.hc-product-media');
                    const overlay = document.createElement('div');
                    overlay.className = 'out-of-stock-overlay';
                    overlay.textContent = 'Out of Stock';
                    if (media) {
                        media.appendChild(overlay);
                    } else {
                        card.appendChild(overlay);
                    }
                }
            } else {
                card.classList.remove('out-of-stock');
                card.removeAttribute('data-disabled');
                const overlay = card.querySelector('.out-of-stock-overlay');
                if (overlay) overlay.remove();
            }
        });
    }

    function renderSelectedProducts() {
        const container = document.getElementById('selectedProducts');
        const emptyOrderMarkup = `
            <div class="hc-order-empty-state hc-order-empty-state-inline">
                <div class="hc-order-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                <p class="mb-0">No items in order</p>
                <small>Add drinks from the left to start order.</small>
            </div>
        `;
        container.innerHTML = '';
        let total = 0;
        let hasProducts = false;
        for (const lineKey in selectedProducts) {
            hasProducts = true;
            const prod = selectedProducts[lineKey];
            total += prod.unitPrice * prod.quantity;
            const row = document.createElement('div');
            row.className = isCheckoutStep ? 'checkout-summary-item' : 'selected-product-row';
            row.innerHTML = isCheckoutStep ? `
                <li>${prod.name} - ${prod.quantity} x &#8369;${prod.unitPrice.toFixed(2)}</li>
                <input type="hidden" name="products[${lineKey}][id]" value="${prod.id}">
                <input type="hidden" name="products[${lineKey}][quantity]" value="${prod.quantity}" id="hidden-qty-${lineKey}">
                <input type="hidden" name="products[${lineKey}][price]" value="${prod.unitPrice}" id="hidden-price-${lineKey}">
                <input type="hidden" name="products[${lineKey}][name]" value="${prod.name}">
                <input type="hidden" name="products[${lineKey}][size]" value="${prod.size}" id="hidden-size-${lineKey}">
            ` : `
                <div class="cart-item-thumb">${prod.image ? `<img src="${prod.image}" alt="${prod.name}">` : ''}</div>
                <div class="product-title">
                    <div class="cart-item-name">${prod.name}</div>
                    <div class="cart-item-size-text">${prod.size}</div>
                </div>
                <div class="qty-controls">
                    <button type="button" class="incdec-btn" data-decrement="${lineKey}" title="Decrease">
                        <svg viewBox="0 0 20 20" fill="none"><rect x="4" y="9" width="12" height="2" rx="1" fill="currentColor"/></svg>
                    </button>
                    <input type="number" min="1" max="${prod.maxQuantity}" value="${prod.quantity}" data-id="${lineKey}" class="form-control text-center" style="width:60px;" readonly>
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
        } else if (isCheckoutStep) {
            container.classList.add('checkout-summary-list');
        } else {
            container.classList.remove('checkout-summary-list');
        }
        document.getElementById('grandTotal').textContent = total.toFixed(2);
        
        attachQtyListeners();
        attachRemoveListeners();
        attachIncDecListeners();
        attachSizeListeners();
        updateChangeDue();
        updateProductCardQuantities();
    }

    function attachQtyListeners() {
    }

    function attachIncDecListeners() {
        document.querySelectorAll('#selectedProducts [data-increment]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const lineKey = this.getAttribute('data-increment');
                const prod = selectedProducts[lineKey];
                if (!prod) return;
                const totalSelected = selectedQtyForProduct(prod.id);
                if (totalSelected < prod.maxQuantity) {
                    prod.quantity += 1;
                    document.getElementById('hidden-qty-' + lineKey).value = prod.quantity;
                    renderSelectedProducts();
                } else {
                    showNotif('Cannot add more than available quantity for this product.', '#d9534f');
                }
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

    function attachSizeListeners() {
        document.querySelectorAll('#selectedProducts [data-line-key]').forEach(function(select) {
            select.addEventListener('change', function() {
                const oldLineKey = this.getAttribute('data-line-key');
                const prod = selectedProducts[oldLineKey];
                if (!prod) return;
                const newSize = this.value === '16oz' ? '16oz' : '12oz';
                const newLineKey = lineKeyOf(prod.id, newSize);

                if (oldLineKey === newLineKey) {
                    prod.size = newSize;
                    prod.unitPrice = newSize === '16oz' ? prod.price16 : prod.price12;
                    renderSelectedProducts();
                    return;
                }

                if (selectedProducts[newLineKey]) {
                    selectedProducts[newLineKey].quantity += prod.quantity;
                    delete selectedProducts[oldLineKey];
                } else {
                    delete selectedProducts[oldLineKey];
                    prod.size = newSize;
                    prod.unitPrice = newSize === '16oz' ? prod.price16 : prod.price12;
                    selectedProducts[newLineKey] = prod;
                }
                renderSelectedProducts();
            });
        });
    }

    function addProductToOrder(card, chosenSize = '12oz', qtyToAdd = 1) {
            if (card.dataset.disabled === 'true') {
                showNotif('Sorry, this product is currently out of stock.', '#d9534f');
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
            const lineKey = lineKeyOf(id, chosenSize);
            let selectedQty = selectedQtyForProduct(id);
            let availableQty = maxQuantity - selectedQty;
            const requestedQty = Math.max(1, parseInt(qtyToAdd) || 1);
            if (availableQty <= 0 || requestedQty > availableQty) {
                card.classList.add('out-of-stock');
                card.setAttribute('data-disabled', 'true');
                showNotif('Cannot add more than available stock.', '#d9534f');
                return;
            }
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
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const gcashReferenceContainer = document.getElementById('gcashReferenceContainer');
    const gcashReferenceInput = document.getElementById('gcashReference');
    const orderPanel = document.querySelector('.hc-order-panel');
    const checkoutDetails = document.getElementById('checkoutDetails');
    const customerNameInput = document.getElementById('customerName');
    const paymongoEnabled = <?= $paymongoEnabled ? 'true' : 'false' ?>;

    function updatePaymentMethodView() {
        const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;

        cashGivenContainer.style.display = 'none';
        gcashReferenceContainer.style.display = 'none';
        gcashReferenceInput.required = false;

        if (paymentMode.value === 'Cash') {
            cashGivenContainer.style.display = '';
            orderPanel.classList.remove('hc-payment-gcash');
        } else if (paymentMode.value === 'GCash') {
            gcashReferenceContainer.style.display = '';
            gcashReferenceInput.required = true;
            orderPanel.classList.add('hc-payment-gcash');
        }

        if (!isCheckoutStep) return;
        placeOrderBtn.textContent = paymentMode.value === 'GCash' ? 'Pay with GCash' : 'Place Order';

        if (paymentMode.value !== 'Cash') {
            changeDisplay.style.display = 'none';
            cashGivenInput.value = '';
            changeDueInput.value = '0.00';
        }

        if (paymentMode.value !== 'GCash') {
            gcashReferenceInput.value = '';
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

    updatePaymentMethodView();

    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (!isCheckoutStep) {
            if (Object.keys(selectedProducts).length === 0) {
                showNotif('Please add at least one item first.', '#d9534f');
                e.preventDefault();
                return false;
            }
            isCheckoutStep = true;
            checkoutDetails.style.display = '';
            customerNameInput.required = true;
            paymentMode.required = true;
            updatePaymentMethodView();
            placeOrderBtn.textContent = paymentMode.value === 'GCash' ? 'Pay with GCash' : 'Place Order';
            renderSelectedProducts();
            customerNameInput.focus();
            e.preventDefault();
            return false;
        }

        if (paymentMode.value === 'Cash') {
            const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;
            const cash = parseFloat(cashGivenInput.value) || 0;
            if (cash < total) {
                showNotif('Cash received is less than the total amount.', '#d9534f');
                cashGivenInput.focus();
                e.preventDefault();
            }
        }

        if (paymentMode.value === 'GCash' && gcashReferenceInput.value.trim() === '') {
            showNotif('Please enter the GCash reference number.', '#d9534f');
            gcashReferenceInput.focus();
            e.preventDefault();
            return false;
        }

        for (const lineKey in selectedProducts) {
            if (selectedProducts[lineKey].quantity > selectedProducts[lineKey].maxQuantity) {
                showNotif('Selected quantity for ' + selectedProducts[lineKey].name + ' exceeds available stock.', '#d9534f');
                e.preventDefault();
                return false;
            }
        }

        const productQtyMap = {};
        for (const lineKey in selectedProducts) {
            const p = selectedProducts[lineKey];
            if (!productQtyMap[p.id]) productQtyMap[p.id] = 0;
            productQtyMap[p.id] += p.quantity;
        }
        for (const productId in productQtyMap) {
            const card = document.querySelector('.product-card[data-id="' + productId + '"');
            if (!card) continue;
            const maxQty = parseInt(card.dataset.quantity);
            if (productQtyMap[productId] > maxQty) {
                showNotif('Selected quantity exceeds available stock.', '#d9534f');
                e.preventDefault();
                return false;
            }
        }
    });

    renderSelectedProducts();
</script>

<?php include('includes/footer.php'); ?>
