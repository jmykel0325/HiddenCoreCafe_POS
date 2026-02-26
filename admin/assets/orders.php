    <?php
    include('includes/header.php');
    include('../../config/dbcon.php');
    ?>

    <style>
        .order-card {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .order-info {
            padding: 15px;
        }
        .order-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }

        .order-status {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .btn-action {
            background-color: #000000;
            color: #FFFFFF;
            border: 1px solid #000000;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        .btn-action:hover {
            background-color: #1A1A1A;
        }
        .btn-primary {
            background-color: #000000;
            border-color: #000000;
        }
        .btn-primary:hover {
            background-color:#1A1A1A;
            color: #FFFFFF;
        }
        .btn-secondary {
            background-color: #1A1A1A;
            border-color: #1A1A1A;
        }
    </style>

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
                    <div class="col-md-4">
                        <div class="order-card">
                            <div class="order-info">
                                <div class="order-title">Order ID #<?= $order['id'] ?></div>
                               <div class="mb-2">
                                    Customer: <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                                    Total: <strong>₱<?= number_format($order['total'], 2) ?></strong><br>
                                    Payment: <strong><?= ucfirst($order['payment_mode']) ?></strong><br>

                                    <?php if ($order['payment_mode'] === 'Cash'): ?>
                                        Cash Received: <strong>₱<?= number_format($order['cash_received'], 2) ?></strong><br>
                                        Change Due: <strong>₱<?= number_format($order['change_due'], 2) ?></strong><br>
                                    <?php endif; ?>

                                    Created At: <strong><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></strong>
                                </div>

                                
                                <div class="mb-2">
                                    <strong>Order Items:</strong>
                                    <ul>
                                        <?php
                                        $order_id = $order['id'];
                                        $order_items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
                                        $order_items_result = mysqli_query($conn, $order_items_query);
                                        $order_items = mysqli_fetch_all($order_items_result, MYSQLI_ASSOC);

                                        if (!empty($order_items)):
                                            foreach ($order_items as $item):
                                        ?>
                                            <li>
                                                <?= htmlspecialchars($item['product_name']) ?> 
                                                <?= htmlspecialchars($item['category']) ?> - 
                                                <?= $item['quantity'] ?> x ₱<?= number_format($item['price'], 2) ?>
                                            </li>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                            <li>No items found for this order.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <a href="orders-view.php?id=<?= $order['id'] ?>" class="btn btn-action btn-sm me-2">View</a>
                                <a href="orders-delete.php?id=<?= $order['id'] ?>" 
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this order?')">
                                Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning mt-3">No Orders Found</div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>