<?php
include('includes/header.php');
include('../../config/dbcon.php');

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

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
$order_items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

// Get all products for adding new items
$products_query = "SELECT id, name, price, price_12oz, price_16oz, quantity, category_id FROM products WHERE deleted_at IS NULL AND (status = 0 OR status IS NULL) ORDER BY name";
$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// Get categories for display
$categories_query = "SELECT id, name FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
$categoryNames = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categoryNames[$cat['id']] = $cat['name'];
}

if (isset($_POST['update_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode']);
    $gcash_reference = isset($_POST['gcash_reference']) ? mysqli_real_escape_string($conn, $_POST['gcash_reference']) : '';
    $discount_type = isset($_POST['discount_type']) ? mysqli_real_escape_string($conn, $_POST['discount_type']) : '';
    $discount_rate = isset($_POST['discount_rate']) ? floatval($_POST['discount_rate']) : 0;
    $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
    
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
        $prod_id = intval($p['id']);
        $qty = max(1, intval($p['quantity']));
        $size = isset($p['size']) && $p['size'] === '16oz' ? '16oz' : '12oz';
        
        $prod_result = mysqli_query($conn, "SELECT name, price_12oz, price_16oz, category_id FROM products WHERE id = $prod_id LIMIT 1");
        if (!$prod_result || mysqli_num_rows($prod_result) === 0) continue;
        
        $prod_row = mysqli_fetch_assoc($prod_result);
        $price12 = isset($prod_row['price_12oz']) ? (float)$prod_row['price_12oz'] : (float)$prod_row['price'];
        $price16 = isset($prod_row['price_16oz']) ? (float)$prod_row['price_16oz'] : (float)$prod_row['price'];
        $unitPrice = $size === '16oz' ? $price16 : $price12;
        $product_name = mysqli_real_escape_string($conn, $prod_row['name']);
        $category = isset($categoryNames[$prod_row['category_id']]) ? mysqli_real_escape_string($conn, $categoryNames[$prod_row['category_id']]) : '';
        
        // Insert new item
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_name, category, size, quantity, price) 
                            VALUES ($order_id, '$product_name', '$category', '$size', $qty, $unitPrice)");
        
        // Deduct quantity
        mysqli_query($conn, "UPDATE products SET quantity = quantity - $qty WHERE id = $prod_id");
        
        $total += $qty * $unitPrice;
    }
    
    // Calculate discount
    $subtotal = $total;
    if ($discount_type === 'PWD' || $discount_type === 'Senior') {
        $discount_rate = 0.20;
        $discount_amount = $subtotal * $discount_rate;
        $total = $subtotal - $discount_amount;
    }
    $final_total = $total;
    
    // Update order
    if ($payment_mode === 'Cash') {
        $cash_received = isset($_POST['cash_received']) ? floatval($_POST['cash_received']) : 0;
        $change_due = $cash_received - $final_total;
        mysqli_query($conn, "UPDATE orders SET 
            customer_name = '$customer_name',
            payment_mode = '$payment_mode',
            discount_type = " . ($discount_type ? "'$discount_type'" : "NULL") . ",
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
            discount_type = " . ($discount_type ? "'$discount_type'" : "NULL") . ",
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

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-edit"></i> Edit Order #<?= $order_id ?>
                <a href="orders.php" class="btn btn-secondary float-end">Back to Orders</a>
            </h4>
        </div>
        <div class="card-body">
            <form id="orderForm" method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <h5>Select Products</h5>
                        <div class="hc-pos-catalog mb-4">
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
                                             data-quantity="<?= (int) $item['quantity'] ?>"
                                             <?= (int) $item['quantity'] === 0 ? 'data-disabled="true"' : '' ?>>
                                            <div class="hc-product-media">
                                                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                                <div class="hc-stock-pill product-quantity"><strong><?= (int) $item['quantity'] ?></strong> Stocks</div>
                                            </div>
                                            <div class="product-info">
                                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="hc-product-card-footer">
                                                    <div class="hc-product-cat"><?= htmlspecialchars($categoryNames[$item['category_id']] ?? 'Uncategorized') ?> | 12oz &#8369;<?= number_format($price12, 2) ?> | 16oz &#8369;<?= number_format($price16, 2) ?></div>
                                                </div>
                                                <div class="d-flex gap-2 mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-add-size="12oz">Add 12oz</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-add-size="16oz">Add 16oz</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="hc-checkout-card hc-order-panel">
                            <div class="hc-order-panel-section">
                                <label for="customerName" class="hc-order-label">Customer Name</label>
                                <input type="text" name="customer_name" id="customerName" required class="form-control" 
                                       value="<?= htmlspecialchars($order['customer_name']) ?>" placeholder="Enter customer name">
                            </div>

                            <div class="hc-order-panel-section">
                                <label for="discountType" class="hc-order-label">Discount Type</label>
                                <select name="discount_type" class="form-select" id="discountType">
                                    <option value="" <?= $order['discount_type'] === '' ? 'selected' : '' ?>>No Discount</option>
                                    <option value="PWD" <?= $order['discount_type'] === 'PWD' ? 'selected' : '' ?>>PWD (20%)</option>
                                    <option value="Senior" <?= $order['discount_type'] === 'Senior' ? 'selected' : '' ?>>Senior Citizen (20%)</option>
                                </select>
                                <input type="hidden" name="discount_rate" id="discountRate" value="<?= $order['discount_rate'] ?? 0 ?>">
                                <input type="hidden" name="discount_amount" id="discountAmountInput" value="<?= $order['discount_amount'] ?? 0 ?>">
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

                            <div class="hc-checkout-footer">
                                <button type="submit" name="update_order" id="placeOrderBtn" class="btn btn-success w-100 hc-order-submit">Update Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Pre-load existing order items
    let selectedProducts = {};
    const lineKeyOf = (id, size) => `${id}__${size}`;

    <?php foreach ($order_items as $item): ?>
        (function() {
            const lineKey = lineKeyOf('<?= $item['product_id'] ?? 0 ?>', '<?= $item['size'] ?? '12oz' ?>');
            selectedProducts[lineKey] = {
                id: '<?= $item['product_id'] ?? 0 ?>',
                name: '<?= htmlspecialchars(addslashes($item['product_name'])) ?>',
                size: '<?= $item['size'] ?? '12oz' ?>',
                price12: <?= (float)($item['price'] ?? 0) ?>,
                price16: <?= (float)($item['price'] ?? 0) ?>,
                unitPrice: <?= (float)($item['price'] ?? 0) ?>,
                quantity: <?= (int)($item['quantity'] ?? 1) ?>,
                maxQuantity: 999
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
                    <input type="number" min="1" value="${prod.quantity}" data-id="${lineKey}" class="form-control text-center" style="width:60px;" readonly>
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
        
        // Calculate discount
        const discountType = document.getElementById('discountType').value;
        let discountRate = 0;
        if (discountType === 'PWD' || discountType === 'Senior') {
            discountRate = 0.20;
        }
        const discountAmount = total * discountRate;
        const finalTotal = total - discountAmount;
        
        document.getElementById('orderDiscount').textContent = discountAmount.toFixed(2);
        document.getElementById('grandTotal').textContent = finalTotal.toFixed(2);
        document.getElementById('discountRate').value = discountRate;
        document.getElementById('discountAmountInput').value = discountAmount.toFixed(2);
        
        attachIncDecListeners();
        attachRemoveListeners();
        attachSizeListeners();
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
        const lineKey = lineKeyOf(id, chosenSize);

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

    const discountType = document.getElementById('discountType');
    discountType.addEventListener('change', function() {
        renderSelectedProducts();
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
