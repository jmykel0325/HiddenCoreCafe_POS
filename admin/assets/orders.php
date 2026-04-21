<?php
include('includes/header.php');
include('../../config/dbcon.php');
$isStaff = (($_SESSION['loggedInUser']['role'] ?? '') === 'staff');

$statusColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusColumnCheck && mysqli_num_rows($statusColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_mode");
}
?>

<style>
    .hc-orders-page.hc-orders-ref {
        max-width: 1520px;
        margin: 0 auto;
    }

    .hc-orders-ref .card {
        border-radius: 24px !important;
        overflow: hidden;
    }

    .hc-orders-ref .card-header h4 {
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .hc-orders-ref .card-header .btn {
        min-height: 42px;
        padding: .5rem 1rem !important;
    }

    .hc-orders-ref .hc-orders-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .hc-orders-ref .hc-order-col {
        min-width: 0;
    }

    .hc-orders-ref .order-card {
        height: 100%;
        border-radius: 22px !important;
        border: 1px solid #e9e3dc !important;
        box-shadow: 0 10px 22px rgba(47, 42, 37, .06) !important;
    }

    .hc-orders-ref .order-info {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: .95rem !important;
        gap: .75rem;
    }

    .hc-orders-ref .hc-order-head {
        margin-bottom: 0;
        padding-bottom: .65rem;
        border-bottom: 1px solid #eee7df;
    }

    .hc-orders-ref .order-title {
        font-size: 1.08rem;
        font-weight: 800;
        color: #2f2a25;
    }

    .hc-orders-ref .hc-order-sub {
        font-size: .84rem;
        color: #7a726b !important;
    }

    .hc-orders-ref .hc-order-total {
        color: #c9682e !important;
        font-size: 1.2rem !important;
        font-weight: 900;
    }

    .hc-orders-ref .hc-order-meta {
        gap: .35rem;
        padding: 0;
    }

    .hc-orders-ref .hc-order-meta-row {
        align-items: baseline;
    }

    .hc-orders-ref .hc-order-meta-label {
        color: #7a726b !important;
        font-weight: 700;
    }

    .hc-orders-ref .hc-order-meta-value {
        color: #2f2a25 !important;
        font-weight: 800;
    }

    .hc-orders-ref .hc-order-items {
        border-top: 1px solid #eee7df;
        margin-top: .1rem;
        padding-top: .65rem;
    }

    .hc-orders-ref .hc-order-items-title {
        margin-bottom: .35rem;
        color: #7a726b;
    }

    .hc-orders-ref .hc-order-items-list {
        margin: 0;
        padding-left: 1.15rem;
    }

    .hc-orders-ref .hc-order-items-list li {
        margin-bottom: .25rem;
        color: #48413a;
    }

    .hc-orders-ref .hc-order-footer {
        margin-top: auto;
        padding-top: .65rem;
        border-top: 1px solid #eee7df;
    }

    .hc-orders-ref .hc-order-actions {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center;
        gap: .42rem !important;
    }

    .hc-orders-ref .hc-order-actions .btn {
        min-height: 34px !important;
        padding: .3rem .68rem !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
        flex: 0 1 auto;
    }

    .hc-orders-ref .hc-order-actions .btn-success {
        background: #f4a06b !important;
        border-color: #f4a06b !important;
        color: #fff !important;
    }

    .hc-orders-ref .hc-order-actions .btn-warning {
        background: #fff3e8 !important;
        border: 1px solid #ffd8bf !important;
        color: #a85f31 !important;
    }

    .hc-orders-ref .hc-order-actions .btn-secondary {
        background: #f4eee8 !important;
        border-color: #e9e3dc !important;
        color: #6f655c !important;
    }

    .hc-orders-ref .hc-order-actions .btn-cancel {
        background: #fff !important;
        color: #ef4444 !important;
        border: 1px solid #fecaca !important;
    }

    .hc-orders-ref .hc-order-actions .btn-cancel:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fca5a5 !important;
    }

    .hc-orders-ref .order-card.cancelled {
        opacity: 0.7;
        border: 1px dashed #fca5a5 !important;
    }

    @media (max-width: 1399px) {
        .hc-orders-ref .hc-orders-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .hc-orders-ref .hc-orders-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-4 hc-admin-page hc-orders-page hc-orders-ref">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-clipboard-list"></i> Orders Placed
                <a href="orders-create.php" class="btn btn-primary float-end"> Place New Order </a>
            </h4>
        </div>
        <div class="card-body">
            <?php
            $orders_query = "SELECT * FROM orders ORDER BY id DESC";
            $orders_result = mysqli_query($conn, $orders_query);
            $orders = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);
            ?>

            <div class="hc-orders-grid">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $status = strtolower((string)($order['order_status'] ?? 'pending'));
                            $isCompleted = ($status === 'completed');
                            $isCancelled = ($status === 'cancelled');
                        ?>
                        <div class="hc-order-col">
                            <div class="order-card <?= $isCancelled ? 'cancelled status-cancelled' : ($isCompleted ? 'status-completed' : 'status-pending') ?>">
                                <div class="order-info">
                                    <div class="hc-order-head">
                                        <div>
                                            <div class="order-title">Order #<?= $order['id'] ?></div>
                                            <div class="hc-order-sub text-muted"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></div>
                                            <div class="mt-1">
                                                <?php if ($isCancelled): ?>
                                                    <span class="status-badge status-cancelled">Cancelled</span>
                                                <?php else: ?>
                                                    <span class="status-badge <?= $isCompleted ? 'status-completed' : 'status-pending' ?>">
                                                        <?= $isCompleted ? 'Completed' : 'Pending' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="hc-order-total">&#8369;<?= number_format($order['total'], 2) ?></div>
                                    </div>

                                    <div class="hc-order-meta">
                                        <div class="hc-order-meta-row">
                                            <span class="hc-order-meta-label">Customer</span>
                                            <span class="hc-order-meta-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                                        </div>
                                        <div class="hc-order-meta-row">
                                            <span class="hc-order-meta-label">Payment</span>
                                            <span class="hc-order-meta-value"><?= ucfirst($order['payment_mode']) ?></span>
                                        </div>
                                        <?php if ($order['payment_mode'] === 'GCash' && isset($order['gcash_reference']) && trim((string)$order['gcash_reference']) !== ''): ?>
                                            <div class="hc-order-meta-row">
                                                <span class="hc-order-meta-label">Reference #</span>
                                                <span class="hc-order-meta-value"><?= htmlspecialchars($order['gcash_reference']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($order['payment_mode'] === 'Cash'): ?>
                                            <div class="hc-order-meta-row">
                                                <span class="hc-order-meta-label">Cash</span>
                                                <span class="hc-order-meta-value">&#8369;<?= number_format((float)($order['cash_received'] ?? 0), 2) ?></span>
                                            </div>
                                            <div class="hc-order-meta-row">
                                                <span class="hc-order-meta-label">Change</span>
                                                <span class="hc-order-meta-value">&#8369;<?= number_format((float)($order['change_due'] ?? 0), 2) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="hc-order-items">
                                        <div class="hc-order-items-title">Items</div>
                                        <ul class="hc-order-items-list">
                                            <?php
                                            $order_id = (int)$order['id'];
                                            $order_items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
                                            $order_items_result = mysqli_query($conn, $order_items_query);
                                            $order_items = mysqli_fetch_all($order_items_result, MYSQLI_ASSOC);

                                            if (!empty($order_items)):
                                                foreach ($order_items as $item):
                                            ?>
                                                <li>
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                    <?= htmlspecialchars($item['category']) ?> -
                                                    <?= (int)$item['quantity'] ?> x &#8369;<?= number_format((float)$item['price'], 2) ?>
                                                </li>
                                            <?php
                                                endforeach;
                                            else:
                                            ?>
                                                <li>No items found for this order.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>

                                    <div class="hc-order-footer">
                                        <div class="hc-order-actions">
                                            <a href="orders-view.php?id=<?= $order['id'] ?>" class="btn btn-action btn-sm">View</a>
                                            <?php if ($isStaff): ?>
                                                <?php if ($isCompleted || $isCancelled): ?>
                                                    <button type="button" class="btn btn-success btn-sm" disabled><?= $isCancelled ? 'Cancelled' : 'Completed' ?></button>
                                                    <button type="button" class="btn btn-warning btn-sm" disabled title="This order cannot be edited">Edit</button>
                                                    <button type="button" class="btn btn-cancel btn-sm" disabled title="This order cannot be cancelled">Cancel</button>
                                                <?php else: ?>
                                                    <a href="orders-complete.php?id=<?= $order['id'] ?>"
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Mark this order as completed? This action will lock editing.')">
                                                        Complete
                                                    </a>
                                                    <a href="orders-edit.php?id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="orders-cancel.php?id=<?= $order['id'] ?>"
                                                       class="btn btn-cancel btn-sm"
                                                       onclick="return cancelOrder(<?= (int)$order['id'] ?>);">
                                                        Cancel
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($isCompleted || $isCancelled): ?>
                                                    <button type="button" class="btn btn-success btn-sm" disabled><?= $isCancelled ? 'Cancelled' : 'Completed' ?></button>
                                                    <button type="button" class="btn btn-warning btn-sm" disabled title="This order cannot be edited">Edit</button>
                                                    <button type="button" class="btn btn-cancel btn-sm" disabled title="This order cannot be cancelled">Cancel</button>
                                                    <button type="button" class="btn btn-danger btn-sm" disabled title="This order cannot be deleted">Delete</button>
                                                <?php else: ?>
                                                    <a href="orders-complete.php?id=<?= $order['id'] ?>"
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Mark this order as completed? This action will lock edit/delete/cancel.')">
                                                        Complete
                                                    </a>
                                                    <a href="orders-edit.php?id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="orders-cancel.php?id=<?= $order['id'] ?>"
                                                       class="btn btn-cancel btn-sm"
                                                       onclick="return cancelOrder(<?= (int)$order['id'] ?>);">
                                                        Cancel
                                                    </a>
                                                    <a href="orders-delete.php?id=<?= $order['id'] ?>"
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to delete this order?')">
                                                        Delete
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">No Orders Found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
<script>
function cancelOrder(orderId) {
    if (confirm("Are you sure you want to cancel this order?")) {
        window.location.href = "orders-cancel.php?id=" + encodeURIComponent(orderId);
    }
    return false;
}
</script>

