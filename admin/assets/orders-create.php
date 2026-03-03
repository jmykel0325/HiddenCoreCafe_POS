<?php


include('includes/header.php'); ?>

<style>
    .product-card {
        background-color: #f8f1e3;
        border: 1px solid #d9c8b4;
        border-radius: 10px;
        overflow: hidden;
        transition: 0.3s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        cursor: pointer;
        position: relative;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }
    .product-card.out-of-stock {
        filter: grayscale(100%);
        cursor: not-allowed;
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
        color: #5a4a42;
        margin-bottom: 5px;
    }
    .product-category {
        font-size: 0.9rem;
        color: #8c7355;
        margin-bottom: 10px;
    }
    .card-header {
        background-color: #d9c8b4 !important;
        color: #3b2f2f;
    }
    .incdec-btn {
        background: linear-gradient(135deg, #f8e4c5 0%, #e2c7a7 100%);
        border: none;
        color: #8c7355;
        border-radius: 8px;
        width: 38px;
        height: 38px;
        font-size: 1.5rem;
        box-shadow: 0 2px 8px rgba(140,115,85,0.10);
        transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.1s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        outline: none;
        font-weight: bold;
        margin: 0 2px;
        user-select: none;
        border: 2px solid #e2c7a7;
        position: relative;
        overflow: hidden;
    }
    .incdec-btn:active {
        transform: scale(0.95);
    }
    .incdec-btn:hover, .incdec-btn:focus {
        background: linear-gradient(135deg, #8c7355 0%, #a97d5d 100%);
        color: #fff;
        box-shadow: 0 4px 16px rgba(140,115,85,0.18);
        border-color: #a97d5d;
    }
    .incdec-btn svg {
        width: 20px;
        height: 20px;
        pointer-events: none;
    }
    .btn-primary {
        background-color: #8c7355;
        border-color: #a97d5d;
    }
    .btn-primary:hover {    
        background-color:rgb(255, 255, 255);
        color: #8c7355;
    }
    .btn-secondary {
        background-color: #8c7355;
        border-color: #a97d5d;
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
    }
    /* --- Cart alignment styles --- */
    .selected-product-row {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .selected-product-row .product-title {
        flex: 1 1 180px;
        min-width: 120px;
        max-width: 220px;
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .selected-product-row .qty-controls {
        display: flex;
        align-items: center;
        min-width: 130px;
        justify-content: center;
        gap: 4px;
    }
    .selected-product-row .product-total {
        min-width: 90px;
        text-align: right;
        margin-left: 12px;
    }
    .selected-product-row .remove-btn {
        margin-left: 12px;
    }
    .order-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 32px;
        align-items: flex-start;
        margin-top: 24px;
    }
    .order-products-col {
        flex: 2 1 0;
        min-width: 340px;
        max-width: 60%;
        padding: 0 0 0 0;
    }
    .order-cart-col {
        flex: 1 1 320px;
        min-width: 320px;
        max-width: 420px;
        padding: 0;
    }
    .order-products-col .card,
    .order-cart-col .card {
        margin-bottom: 0;
    }
    .order-products-col .card-body,
    .order-cart-col .card-body {
        padding: 1.25rem;
    }
    .order-products-col .card-header,
    .order-cart-col .card-header {
        padding: 0.75rem 1.25rem;
    }
    .order-products-col .row.g-4 {
        margin-left: 0;
        margin-right: 0;
    }
    .order-products-col .col-md-6,
    .order-products-col .col-lg-4,
    .order-products-col .col-xl-3 {
        padding-left: 8px;
        padding-right: 8px;
        margin-bottom: 16px;
    }
    @media (max-width: 991px) {
        .order-layout {
            flex-direction: column;
        }
        .order-products-col, .order-cart-col {
            max-width: 100%;
            padding: 0;
        }
    }
    .order-products-col {
        padding-left: 0 !important;
    }
    .order-cart-col {
        padding-right: 0 !important;
    }
    /* Toast notification styles */
    .notif-toast {
    position: fixed;
    top: 30px;
    left: 50%;
    right: auto;
    transform: translateX(-50%);
    background: #8c7355;
    color: #fff;
    padding: 16px 28px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    font-size: 1.1rem;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
    .notif-toast.show {
        opacity: 1;
        pointer-events: auto;
    }

    .gcash-container {
        background-color: #f0f2f5;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        border: 1px solid #ddd;
    }
    .gcash-header img {
        width: 80px;
        margin-bottom: 10px;
    }
    .gcash-details p {
        margin-bottom: 5px;
    }
    .gcash-details .amount {
        font-size: 1.5rem;
        font-weight: bold;
        color: #005cff;
    }
</style>

<h4>Create New Order</h4>

<form action="place_order.php" method="POST" id="orderForm">
    <div class="mb-4">
        <h4 class="mb-3">☕ Choose Your Flavor:</h4>
        <div class="d-flex flex-wrap gap-3">
            <a href="orders-create.php" class="btn btn-light border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-mug-hot"></i> All
            </a>
            <?php 
                $categories = getAll('categories');
                if ($categories && mysqli_num_rows($categories) > 0):
                    while ($cat = mysqli_fetch_assoc($categories)):
            ?>
                <a href="orders-create.php?category=<?= htmlspecialchars($cat['id']) ?>" class="btn btn-light border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-mug-hot"></i> <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php 
                    endwhile;
                else:
            ?>
                <p class="text-muted">No categories found.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="order-layout">
        <!-- Products Table/Section -->
        <div class="order-products-col">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-coffee"></i> Products
                        <a href="products-create.php" class="btn btn-primary float-end"> Add Products </a>
                    </h4>
                </div>
                <div class="card-body">
                    <?php 
                        alertMessage();
                        $products = [];
                        if (isset($_GET['category']) && is_numeric($_GET['category'])) {
                            $categoryId = intval($_GET['category']);
                            $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = $categoryId");
                        } else {
                            $products = getAll('products');
                        }

                        if ($products && mysqli_num_rows($products) > 0):
                    ?>
                        <div class="row g-4">
                            <?php foreach ($products as $item): ?>
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="product-card <?= $item['quantity'] == 0 ? 'out-of-stock' : '' ?>" 
                                     data-id="<?= $item['id'] ?>" 
                                     data-name="<?= htmlspecialchars($item['name']) ?>" 
                                     data-price="<?= $item['price'] ?>"
                                     data-quantity="<?= $item['quantity'] ?>"
                                     <?= $item['quantity'] == 0 ? 'data-disabled="true"' : '' ?>>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                    <?php if ($item['quantity'] == 0): ?>
                                        <div class="out-of-stock-overlay">Out of Stock</div>
                                    <?php endif; ?>
                                    <div class="product-info">
                                        <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="product-category">
                                            <strong>
                                                <?php 
                                                    $category = getById('categories', $item['category_id']);
                                                    echo htmlspecialchars($category['name'] ?? '');
                                                ?>
                                            </strong>       
                                        </div>
                                        <div class="product-quantity">
                                            Quantity: <strong><?= htmlspecialchars($item['quantity']) ?></strong>
                                        </div>
                                        <div class="product-price">
                                            Price: <strong>₱<?= number_format($item['price'], 2) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Cart, Customer, Payment, etc. -->
        <div class="order-cart-col">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Selected Products</h5>
                </div>
                <div class="card-body" id="selectedProducts">
                    <p class="text-muted">No products selected.</p>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" required class="form-control mb-3">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select mb-3" id="paymentMode" required>
                        <option value="Cash">Cash</option>
                        <option value="PayMongo">GCash</option>
                    </select>
                    <div id="cashGivenContainer" style="display:none;">
                        <label class="form-label">Cash Received</label>
                        <input type="number" min="0" step="0.01" name="cash_received" id="cashGiven" class="form-control mb-3">
                        <input type="hidden" name="change_due" id="changeDueInput" value="0.00">
                        <div id="changeDisplay" class="mb-2 text-success" style="display:none;">
                            Change Due: ₱<span id="changeAmount">0.00</span>
                        </div>
                    </div>
                    <div id="paymongoContainer" style="display:none;">
                        <div class="gcash-container">
                            <div class="gcash-header">
                                <img src="https://1000logos.net/wp-content/uploads/2023/05/GCash-Logo.png" alt="GCash Logo">
                                <h6>You are paying</h6>
                            </div>
                            <div class="gcash-details">
                                <p class="amount">₱<span id="gcashTotal">0.00</span></p>
                                <p>Hidden Core Cafe</p>
                            </div>
                            <div class="mb-3">
                                <label for="gcash-number" class="form-label small text-muted">Your GCash Number</label>
                                <input type="text" class="form-control text-center" id="gcash-number" value="09534921530" readonly>
                            </div>
                            <p class="small text-muted">You will be redirected to complete your payment securely.</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total: ₱<span id="grandTotal">0.00</span></h5>
                    <button type="submit" name="place_order" id="placeOrderBtn" class="btn btn-success px-4">Place Order</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Toast Notification -->
<div id="notifToast" class="notif-toast"></div>

<script>
    // Toast notification function
    function showNotif(message, color = '#8c7355') {
        const toast = document.getElementById('notifToast');
        toast.textContent = message;
        toast.style.background = color;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    // Store selected products as {id: {id, name, price, quantity, maxQuantity}}
    let selectedProducts = {};

    // Update product card quantities and out-of-stock state
    function updateProductCardQuantities() {
        document.querySelectorAll('.product-card').forEach(function(card) {
            const id = card.dataset.id;
            const maxQty = parseInt(card.dataset.quantity);
            let selectedQty = selectedProducts[id] ? selectedProducts[id].quantity : 0;
            let displayQty = maxQty - selectedQty;
            // Update the quantity display in the card
            const qtyElem = card.querySelector('.product-quantity strong');
            if (qtyElem) qtyElem.textContent = displayQty;
            // Handle out-of-stock state
            if (displayQty <= 0) {
                card.classList.add('out-of-stock');
                card.setAttribute('data-disabled', 'true');
                if (!card.querySelector('.out-of-stock-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'out-of-stock-overlay';
                    overlay.textContent = 'Out of Stock';
                    card.appendChild(overlay);
                }
            } else {
                card.classList.remove('out-of-stock');
                card.removeAttribute('data-disabled');
                // Remove overlay if present
                const overlay = card.querySelector('.out-of-stock-overlay');
                if (overlay) overlay.remove();
            }
        });
    }

    function renderSelectedProducts() {
        const container = document.getElementById('selectedProducts');
        container.innerHTML = '';
        let total = 0;
        let hasProducts = false;
        for (const id in selectedProducts) {
            hasProducts = true;
            const prod = selectedProducts[id];
            total += prod.price * prod.quantity;
            const row = document.createElement('div');
            row.className = 'selected-product-row';
            row.innerHTML = `
                <div class="product-title">${prod.name}</div>
                <div class="qty-controls">
                    <button type="button" class="incdec-btn" data-decrement="${prod.id}" title="Decrease">
                        <svg viewBox="0 0 20 20" fill="none"><rect x="4" y="9" width="12" height="2" rx="1" fill="currentColor"/></svg>
                    </button>
                    <input type="number" min="1" max="${prod.maxQuantity}" value="${prod.quantity}" data-id="${prod.id}" class="form-control text-center" style="width:60px;" readonly>
                    <button type="button" class="incdec-btn" data-increment="${prod.id}" title="Increase">
                        <svg viewBox="0 0 20 20" fill="none"><rect x="9" y="4" width="2" height="12" rx="1" fill="currentColor"/><rect x="4" y="9" width="12" height="2" rx="1" fill="currentColor"/></svg>
                    </button>
                </div>
                <div class="product-total">₱${(prod.price * prod.quantity).toFixed(2)}</div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-btn" data-remove="${prod.id}">&times;</button>
                <input type="hidden" name="products[${prod.id}][id]" value="${prod.id}">
                <input type="hidden" name="products[${prod.id}][quantity]" value="${prod.quantity}" id="hidden-qty-${prod.id}">
                <input type="hidden" name="products[${prod.id}][price]" value="${prod.price}">
                <input type="hidden" name="products[${prod.id}][name]" value="${prod.name}">
            `;
            container.appendChild(row);
        }
        if (!hasProducts) {
            container.innerHTML = '<p class="text-muted">No products selected.</p>';
        }
        document.getElementById('grandTotal').textContent = total.toFixed(2);
        attachQtyListeners();
        attachRemoveListeners();
        attachIncDecListeners();
        updateChangeDue();
        updateProductCardQuantities();
    }

    function attachQtyListeners() {
        // No direct input, so nothing here
    }

    function attachIncDecListeners() {
        document.querySelectorAll('#selectedProducts [data-increment]').forEach(function(btn){
            btn.addEventListener('click', function(){
                const id = this.getAttribute('data-increment');
                if (selectedProducts[id].quantity < selectedProducts[id].maxQuantity) {
                    selectedProducts[id].quantity += 1;
                    document.getElementById('hidden-qty-' + id).value = selectedProducts[id].quantity;
                    renderSelectedProducts();
                }
            });
        });
        document.querySelectorAll('#selectedProducts [data-decrement]').forEach(function(btn){
            btn.addEventListener('click', function(){
                const id = this.getAttribute('data-decrement');
                if (selectedProducts[id].quantity > 1) {
                    selectedProducts[id].quantity -= 1;
                    document.getElementById('hidden-qty-' + id).value = selectedProducts[id].quantity;
                    renderSelectedProducts();
                }
            });
        });
    }

    function attachRemoveListeners() {
        document.querySelectorAll('#selectedProducts [data-remove]').forEach(function(btn){
            btn.addEventListener('click', function(){
                const id = this.getAttribute('data-remove');
                delete selectedProducts[id];
                renderSelectedProducts();
            });
        });
    }

    document.querySelectorAll('.product-card').forEach(function(card){
        card.addEventListener('click', function(e){
            if (card.dataset.disabled === "true") {
                showNotif("Sorry, this product is currently out of stock.", "#d9534f");
                return;
            }
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const maxQuantity = parseInt(card.dataset.quantity);
            let selectedQty = selectedProducts[id] ? selectedProducts[id].quantity : 0;
            let availableQty = maxQuantity - selectedQty;
            if (availableQty <= 0) {
                card.classList.add('out-of-stock');
                card.setAttribute('data-disabled', 'true');
                showNotif("Sorry, this product is currently out of stock.", "#d9534f");
                return;
            }
            if (selectedProducts[id]) {
                if (selectedProducts[id].quantity + 1 > maxQuantity) {
                    showNotif("Cannot add more than available quantity for this product.", "#d9534f");
                    return;
                }
                selectedProducts[id].quantity += 1;
            } else {
                selectedProducts[id] = {
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    maxQuantity: maxQuantity
                };
            }
            renderSelectedProducts();
        });
    });

    // Payment mode and cash logic
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

    function updatePaymentMethodView() {
        const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;

        // Hide all payment-specific containers by default
        cashGivenContainer.style.display = 'none';
        paymongoContainer.style.display = 'none';

        if (paymentMode.value === 'Cash') {
            cashGivenContainer.style.display = '';
        } else if (paymentMode.value === 'PayMongo') {
            paymongoContainer.style.display = '';
        }
        
        if (paymentMode.value === 'PayMongo') {
            placeOrderBtn.textContent = 'Pay with GCash';
            gcashTotalSpan.textContent = total.toFixed(2);
        } else {
            placeOrderBtn.textContent = 'Place Order';
        }

        // Reset cash inputs if not cash payment
        if (paymentMode.value !== 'Cash') {
            changeDisplay.style.display = 'none';
            cashGivenInput.value = '';
            changeDueInput.value = "0.00";
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
            changeDueInput.value = "0.00";
        }
    }

    paymentMode.addEventListener('change', function() {
        updatePaymentMethodView();
        updateChangeDue();
    });

    cashGivenInput.addEventListener('input', function() {
        updateChangeDue();
    });

    // Show cash input if Cash is default
    updatePaymentMethodView();

    // Prevent form submit if cash is not enough
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (paymentMode.value === 'PayMongo') {
            e.preventDefault();
            this.action = 'paymongo-create-source.php';
            this.submit();
            return;
        }

        if (paymentMode.value === 'Cash') {
            const total = parseFloat(grandTotalSpan.textContent.replace(/,/g, '')) || 0;
            const cash = parseFloat(cashGivenInput.value) || 0;
            if (cash < total) {
                showNotif('Cash received is less than the total amount.', "#d9534f");
                cashGivenInput.focus();
                e.preventDefault();
            }
        }
        // Prevent submit if any selected product exceeds available quantity
        for (const id in selectedProducts) {
            if (selectedProducts[id].quantity > selectedProducts[id].maxQuantity) {
                showNotif('Selected quantity for ' + selectedProducts[id].name + ' exceeds available stock.', "#d9534f");
                e.preventDefault();
                return false;
            }
        }
    });

    // Initial render
    renderSelectedProducts();
</script>

<?php include('includes/footer.php'); ?>