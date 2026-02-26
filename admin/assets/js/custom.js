let selectedProducts = {};

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

function renderSelectedProducts() {
    const container = document.getElementById('selectedProducts');
    container.innerHTML = '';

    if (Object.keys(selectedProducts).length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No products selected.</p>';
        document.getElementById('grandTotal').textContent = '0.00';
        return;
    }

    let total = 0;

    for (const id in selectedProducts) {
        const item = selectedProducts[id];
        const subtotal = item.price * item.quantity;
        total += subtotal;

        const productCard = document.createElement('div');
        productCard.className = 'card mb-3 border-0 shadow-sm';

        productCard.innerHTML = `
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-bold">${item.name}</h6>
                    <small class="text-muted">₱${formatCurrency(item.price)} x 
                        <span id="qty-${id}" class="fw-semibold">${item.quantity}</span> = 
                        ₱<span id="subtotal-${id}" class="fw-bold">${formatCurrency(subtotal)}</span>
                    </small>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateQty(${id}, -1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="updateQty(${id}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="products[${id}][id]" value="${id}">
            <input type="hidden" name="products[${id}][name]" value="${item.name}">
            <input type="hidden" name="products[${id}][price]" value="${item.price}">
            <input type="hidden" name="products[${id}][quantity]" id="input-qty-${id}" value="${item.quantity}">
        `;
        container.appendChild(productCard);
    }

    document.getElementById('grandTotal').textContent = formatCurrency(total);
}

function updateQty(id, change) {
    if (!selectedProducts[id]) return;

    selectedProducts[id].quantity += change;
    if (selectedProducts[id].quantity <= 0) {
        delete selectedProducts[id];
    }
    renderSelectedProducts();
}

document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
        const id = card.getAttribute('data-id');
        const name = card.getAttribute('data-name');
        const price = parseFloat(card.getAttribute('data-price'));

        if (!selectedProducts[id]) {
            selectedProducts[id] = {
                name: name,
                price: price,
                quantity: 1
            };
        } else {
            selectedProducts[id].quantity += 1;
        }

        renderSelectedProducts();
    });
});
