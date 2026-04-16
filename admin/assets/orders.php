<?php
include('includes/header.php');
include('../../config/dbcon.php');
$isStaff = (($_SESSION['loggedInUser']['role'] ?? '') === 'staff');

$statusColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusColumnCheck && mysqli_num_rows($statusColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_mode");
}
?>

<div class="container-fluid px-4">
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

            <div class="row g-4">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php $isCompleted = strtolower((string)($order['order_status'] ?? 'pending')) === 'completed'; ?>
                        <div class="col-md-4">
                            <div class="order-card">
                                <div class="order-info">
                                    <div class="hc-order-head">
                                        <div>
                                            <div class="order-title">Order #<?= $order['id'] ?></div>
                                            <div class="hc-order-sub text-muted"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></div>
                                            <div class="mt-1">
                                                <span class="badge <?= $isCompleted ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $isCompleted ? 'Completed' : 'Pending' ?>
                                                </span>
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

                                    <div class="hc-order-actions">
                                        <a href="orders-view.php?id=<?= $order['id'] ?>" class="btn btn-action btn-sm">View</a>
                                        <?php if ($isStaff): ?>
                                            <?php if ($isCompleted): ?>
                                                <button type="button" class="btn btn-success btn-sm" disabled>Completed</button>
                                                <button type="button" class="btn btn-warning btn-sm" disabled title="Completed orders cannot be edited">Edit</button>
                                            <?php else: ?>
                                                <a href="orders-complete.php?id=<?= $order['id'] ?>"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Mark this order as completed? This action will lock editing.')">
                                                    Complete
                                                </a>
                                                <a href="orders-edit.php?id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($isCompleted): ?>
                                                <button type="button" class="btn btn-success btn-sm" disabled>Completed</button>
                                                <button type="button" class="btn btn-warning btn-sm" disabled title="Completed orders cannot be edited">Edit</button>
                                                <button type="button" class="btn btn-secondary btn-sm" disabled title="Completed orders cannot be cancelled">Cancel</button>
                                                <button type="button" class="btn btn-danger btn-sm" disabled title="Completed orders cannot be deleted">Delete</button>
                                            <?php else: ?>
                                                <a href="orders-complete.php?id=<?= $order['id'] ?>"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Mark this order as completed? This action will lock edit/delete/cancel.')">
                                                    Complete
                                                </a>
                                                <a href="orders-edit.php?id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="orders-cancel.php?id=<?= $order['id'] ?>"
                                                   class="btn btn-secondary btn-sm"
                                                   onclick="return confirm('Are you sure you want to cancel this order? This will restore the product quantities.')">
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
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">No Orders Found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
