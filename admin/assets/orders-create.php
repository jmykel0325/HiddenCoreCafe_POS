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

$categoriesResult = getAll('categories');
if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0) {
    while ($categoryRow = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $categoryRow;
        $categoryNames[(int) $categoryRow['id']] = $categoryRow['name'];
    }
}

$categoryCountResult = mysqli_query($conn, "SELECT category_id, COUNT(*) AS total FROM products GROUP BY category_id");
if ($categoryCountResult) {
    while ($countRow = mysqli_fetch_assoc($categoryCountResult)) {
        $categoryCounts[(int) $countRow['category_id']] = (int) $countRow['total'];
    }
}

$totalCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($totalCountResult) {
    $totalCountRow = mysqli_fetch_assoc($totalCountResult);
    $totalProductCount = (int) ($totalCountRow['total'] ?? 0);
}

if ($selectedCategoryId !== null) {
    $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$selectedCategoryId}");
} else {
    $products = getAll('products');
}

$paymongoSecretKey = defined('PAYMONGO_SECRET_KEY') ? trim((string) PAYMONGO_SECRET_KEY) : trim((string) getenv('PAYMONGO_SECRET_KEY'));
$paymongoEnabled = $paymongoSecretKey !== ''
    && !str_contains($paymongoSecretKey, 'replace_with')
    && !str_contains($paymongoSecretKey, 'actual_paymongo_secret_key');
?>

<div class="hc-pos hc-order-screen">
    <div class="hc-order-hero">
        <div class="hc-order-hero-copy">
            <h1 class="hc-order-hero-title"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($currentAdminName) ?></h1>
            <p class="hc-order-hero-date"><?= htmlspecialchars(date('l, F j, Y')) ?></p>
        </div>
        <div class="hc-order-hero-tools">
            <a href="products-create.php" class="hc-order-action-btn">
                <i class="fas fa-plus"></i>
                <span>Add Products</span>
            </a>
        </div>
    </div>

    <?php alertMessage(); ?>

    <form action="place_order.php" method="POST" id="orderForm">
        <div class="hc-order-board">
            <div class="hc-order-main">
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
                    <?php if ($products && mysqli_num_rows($products) > 0): ?>
                        <div class="hc-product-grid hc-order-grid">
                            <?php while ($item = mysqli_fetch_assoc($products)): ?>
                                <?php $productCategoryName = $categoryNames[(int) $item['category_id']] ?? 'Uncategorized'; ?>
                                <?php
                                    $imagePath = ltrim((string)($item['image'] ?? ''), '/');
                                    $imageUrl = '/HiddenCoreCafe_POS/admin/' . $imagePath;
                                    $price12 = (float)($item['price_12oz'] ?? $item['price']);
                                    $price16 = (float)($item['price_16oz'] ?? $item['price']);
                                ?>
                                <div class="hc-product-cell">
                                    <div class="product-card <?= (int) $item['quantity'] === 0 ? 'out-of-stock' : '' ?>"
                                         data-id="<?= (int) $item['id'] ?>"
                                         data-name="<?= htmlspecialchars($item['name']) ?>"
                                         data-price12="<?= htmlspecialchars((string)$price12) ?>"
                                         data-price16="<?= htmlspecialchars((string)$price16) ?>"
                                         data-quantity="<?= (int) $item['quantity'] ?>"
                                         <?= (int) $item['quantity'] === 0 ? 'data-disabled="true"' : '' ?>>
                                        <div class="hc-product-media">
                                            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                            <div class="hc-stock-pill product-quantity"><strong><?= (int) $item['quantity'] ?></strong> Stocks</div>
                                            <?php if ((int) $item['quantity'] === 0): ?>
                                                <div class="out-of-stock-overlay">Out of Stock</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="hc-product-card-footer">
                                                <div class="hc-product-cat"><?= htmlspecialchars($productCategoryName) ?> | 12oz &#8369;<?= number_format($price12, 2) ?> | 16oz &#8369;<?= number_format($price16, 2) ?></div>
                                            </div>
                                            <div class="d-flex gap-2 mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-add-size="12oz">Add 12oz</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-add-size="16oz">Add 16oz</button>
                                            </div>
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
                        <div class="hc-order-panel-section">
                            <label for="customerName" class="hc-order-label">Customer Name</label>
                            <input type="text" name="customer_name" id="customerName" required class="form-control" placeholder="Enter customer name">
                        </div>

                        <div class="hc-order-panel-section">
                            <label for="paymentMode" class="hc-order-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select" id="paymentMode" required>
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

                        <div class="hc-order-panel-section" id="paymongoContainer" style="display:none;">
                            <div class="gcash-container">
                                <div class="gcash-header">
                                    <img src="https://1000logos.net/wp-content/uploads/2023/05/GCash-Logo.png" alt="GCash Logo">
                                    <h6>You are paying</h6>
                                </div>
                                <div class="gcash-details">
                                    <p class="amount">&#8369;<span id="gcashTotal">0.00</span></p>
                                    <p>Hidden Core Cafe</p>
                                </div>
                                <div class="text-center mb-3">
                                    <img src="img/gcash-qr.jpg" alt="GCash QR Code" style="max-width: 200px; border-radius: 8px; border: 1px solid #ddd;">
                                    <p class="mt-2 mb-0 fw-bold text-primary">09497836057</p>
                                    <p class="small text-muted">Scan to pay with GCash</p>
                                </div>
                                <p class="small text-muted mb-0">You will be redirected to complete your payment securely.</p>
                            </div>
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
                            <div class="hc-order-summary-row">
                                <span>Subtotal</span>
                                <strong>&#8369;<span id="orderSubtotal">0.00</span></strong>
                            </div>
                            <div class="hc-order-summary-row">
                                <span>Discount</span>
                                <strong>-&#8369;<span id="orderDiscount">0.00</span></strong>
                            </div>
                            <div class="hc-order-summary-row hc-order-summary-total">
                                <span>Total</span>
                                <strong>&#8369;<span id="grandTotal">0.00</span></strong>
                            </div>
                        </div>

                        <div class="hc-order-panel-section hc-order-panel-section-compact">
                            <input type="text" class="form-control hc-order-promo-input" placeholder="Voucher / Promo code" disabled>
                        </div>

                        <div class="hc-checkout-footer">
                            <button type="submit" name="place_order" id="placeOrderBtn" class="btn btn-success w-100 hc-order-submit">Place Order</button>
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
            row.className = 'selected-product-row';
            row.innerHTML = `
                <div class="product-title">
                    <div class="cart-item-name">${prod.name}</div>
                    <div class="mt-1">
                        <select class="form-select form-select-sm product-size-select cart-item-size-select" data-line-key="${lineKey}">
                            <option value="12oz" ${prod.size === '12oz' ? 'selected' : ''}>12oz - PHP ${prod.price12.toFixed(2)}</option>
                            <option value="16oz" ${prod.size === '16oz' ? 'selected' : ''}>16oz - PHP ${prod.price16.toFixed(2)}</option>
                        </select>
                    </div>
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
                <div class="product-total">&#8369;${(prod.unitPrice * prod.quantity).toFixed(2)}</div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-btn" data-remove="${lineKey}">&times;</button>
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
        document.getElementById('orderSubtotal').textContent = total.toFixed(2);
        document.getElementById('gcashTotal').textContent = total.toFixed(2);
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

    function addProductToOrder(card, chosenSize = '12oz') {
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
            const lineKey = lineKeyOf(id, chosenSize);
            let selectedQty = selectedQtyForProduct(id);
            let availableQty = maxQuantity - selectedQty;
            if (availableQty <= 0) {
                card.classList.add('out-of-stock');
                card.setAttribute('data-disabled', 'true');
                showNotif('Sorry, this product is currently out of stock.', '#d9534f');
                return;
            }
            if (selectedProducts[lineKey]) {
                selectedProducts[lineKey].quantity += 1;
            } else {
                selectedProducts[lineKey] = {
                    id: id,
                    name: name,
                    size: chosenSize,
                    price12: price12,
                    price16: price16,
                    unitPrice: chosenSize === '16oz' ? price16 : price12,
                    quantity: 1,
                    maxQuantity: maxQuantity
                };
            }
            renderSelectedProducts();
    }

    document.querySelectorAll('.product-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('[data-add-size]')) {
                return;
            }
            addProductToOrder(card, '12oz');
        });

        card.querySelectorAll('[data-add-size]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const chosenSize = this.getAttribute('data-add-size') || '12oz';
                addProductToOrder(card, chosenSize);
            });
        });
    });

    const paymentMode = document.getElementById('paymentMode');
    const cashGivenContainer = document.getElementById('cashGivenContainer');
    const paymongoContainer = document.getElementById('paymongoContainer');
    const cashGivenInput = document.getElementById('cashGiven');
    const grandTotalSpan = document.getElementById('grandTotal');
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    const changeDueInput = document.getElementById('changeDueInput');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const gcashTotalSpan = document.getElementById('gcashTotal');
    const paymongoEnabled = <?= $paymongoEnabled ? 'true' : 'false' ?>;

    function updatePaymentMethodView() {
        const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;

        cashGivenContainer.style.display = 'none';
        paymongoContainer.style.display = 'none';

        if (paymentMode.value === 'Cash') {
            cashGivenContainer.style.display = '';
        } else if (paymentMode.value === 'GCash') {
            paymongoContainer.style.display = '';
        }

        if (paymentMode.value === 'GCash') {
            placeOrderBtn.textContent = 'Pay with GCash';
            gcashTotalSpan.textContent = total.toFixed(2);
        } else {
            placeOrderBtn.textContent = 'Place Order';
        }

        if (paymentMode.value !== 'Cash') {
            changeDisplay.style.display = 'none';
            cashGivenInput.value = '';
            changeDueInput.value = '0.00';
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
        if (paymentMode.value === 'Cash') {
            const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;
            const cash = parseFloat(cashGivenInput.value) || 0;
            if (cash < total) {
                showNotif('Cash received is less than the total amount.', '#d9534f');
                cashGivenInput.focus();
                e.preventDefault();
            }
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
