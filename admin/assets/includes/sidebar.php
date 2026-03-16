<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
        <?php
            $isStaff = (($_SESSION['loggedInUser']['role'] ?? '') === 'staff');
            $homeLink = $isStaff ? 'orders-create.php' : 'dashboard.php';
        ?>
        <div class="hc-sidebar-brand">
            <div class="hc-sidebar-brand">
                <a href="<?= $homeLink ?>" class="hc-brand-link">
                    <span class="hc-brand-logo">
                        <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Cafe logo" />
                    </span>
                    <span class="hc-brand-text">
                        <span class="hc-brand-title">Hidden</span>
                        <span class="hc-brand-title">Core</span>
                        <span class="hc-brand-subtitle">Cafe</span>
                    </span>
                </a>
            </div>
        </div>
        <div class="sb-sidenav-menu">
            <div class="nav hc-nav">
                <?php if ($isStaff): ?>
                <div class="sb-sidenav-menu-heading">Cashiering</div>
                <a class="nav-link" href="orders-create.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                    Cashiering Order
                </a>
                <a class="nav-link" href="orders.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                    View Orders
                </a>
                <?php else: ?>
                <div class="sb-sidenav-menu-heading">Main</div>
                <a class="nav-link" href="dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-dashboard"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Manage Orders and Categories</div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseOrders" aria-expanded="false" aria-controls="collapsePages">
                    <div class="sb-nav-link-icon"><i class="fas fa-coffee"></i></div>
                    Orders
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseOrders" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="orders-create.php">Create Order</a>
                        <a class="nav-link" href="orders.php">View Orders</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="false" aria-controls="collapsePages">
                    <div class="sb-nav-link-icon"><i class="fas fa-coffee"></i></div>
                    Categories
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCategories" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="categories-create.php">Create Category</a>
                        <a class="nav-link" href="categories.php">View Categories</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProduct" aria-expanded="false" aria-controls="collapseProduct">
                    <div class="sb-nav-link-icon"><i class="fas fa-coffee"></i></div>
                    Products
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseProduct" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="products-create.php">Create Products</a>
                        <a class="nav-link" href="products.php">View Products</a>
                    </nav>
                </div>

                <div class="sb-sidenav-menu-heading">Manage Cashiers</div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCashiers" aria-expanded="false" aria-controls="collapseCashiers">
                    <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                    Cashiers/Staff
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCashiers" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admins-create.php">Add Cashier</a>
                        <a class="nav-link" href="admins.php">View Cashiers</a>
                    </nav>
                </div>

                <div class="sb-sidenav-menu-heading">Manage Sales</div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSales" aria-expanded="false" aria-controls="collapseSales">
                    <div class="sb-nav-link-icon"><i class="fas fa-bar-chart"></i></div>
                    Sales
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseSales" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="sales-report.php">View Sales</a>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Account section at bottom -->
        <div class="sb-sidenav-footer">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Account</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAccount" aria-expanded="false" aria-controls="collapseAccount">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-circle"></i></div>
                    <?= $_SESSION['loggedInUser']['username']; ?>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseAccount" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </nav>
                </div>
            </div>
        </div>
    </nav>
</div>
