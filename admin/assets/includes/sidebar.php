<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <?php
            $isStaff = (($_SESSION['loggedInUser']['role'] ?? '') === 'staff');
            $homeLink = $isStaff ? 'orders-create.php' : 'dashboard.php';
            $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
            $username = $_SESSION['loggedInUser']['username'] ?? 'User';

            $initialsSource = preg_replace('/[^a-zA-Z0-9 ]/', '', $username);
            $initialWords = preg_split('/\s+/', trim($initialsSource));
            $initials = '';
            foreach ($initialWords as $w) {
                if ($w !== '') {
                    $initials .= strtoupper(substr($w, 0, 1));
                }
                if (strlen($initials) >= 2) {
                    break;
                }
            }
            if ($initials === '') {
                $initials = strtoupper(substr($username, 0, 1));
            }

            $viewOrdersPages = ['orders.php', 'orders-view.php', 'orders-edit.php'];
            $categoryPages = ['categories-create.php', 'categories.php', 'categories-edit.php'];
            $productPages = ['products-create.php', 'products.php', 'products-edit.php'];
            $cashierPages = ['admins.php', 'admins-edit.php'];
            $salesPages = ['sales-report.php'];

            $isCategoryActive = in_array($currentPage, $categoryPages, true);
            $isProductActive = in_array($currentPage, $productPages, true);
            $isCashierActive = in_array($currentPage, $cashierPages, true);
            $isSalesActive = in_array($currentPage, $salesPages, true);
            $isDashboardActive = ($currentPage === 'dashboard.php');
            $quotaCurrent = getTodaySoldCups();
            $quotaTarget = getTodayQuotaTarget();
            $quotaPercent = $quotaTarget > 0
                ? max(0, min(100, (int) round(($quotaCurrent / $quotaTarget) * 100)))
                : 0;
            $quotaValueText = $quotaTarget > 0
                ? ((int) $quotaCurrent . '/' . (int) $quotaTarget)
                : ((int) $quotaCurrent . '/0');
        ?>

        <div class="hc-sidebar-brand">
            <div class="hc-brand-row">
                <a href="<?= $homeLink ?>" class="hc-brand-link">
                    <span class="hc-brand-logo">
                        <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Cafe logo" />
                    </span>
                    <span class="hc-brand-text">
                        <span class="hc-brand-title">POS Coffee</span>
                        <span class="hc-brand-subtitle">Hidden Core Cafe</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav hc-nav">
                <?php if ($isStaff): ?>
                    <div class="sb-sidenav-menu-heading">Main</div>
                    <a class="nav-link <?= $currentPage === 'orders-create.php' ? 'active' : '' ?>" href="orders-create.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-bag-shopping"></i></div>
                        Create Order
                    </a>
                    <a class="nav-link <?= in_array($currentPage, $viewOrdersPages, true) ? 'active' : '' ?>" href="orders.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        View Orders
                    </a>
                    <a class="nav-link <?= $isSalesActive ? 'active' : '' ?>" href="sales-report.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Sales
                    </a>
                <?php else: ?>
                    <div class="sb-sidenav-menu-heading">Main</div>
                    <a class="nav-link <?= $isDashboardActive ? 'active' : '' ?>" href="dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-gauge-high"></i></div>
                        Dashboard
                    </a>

                    <a class="nav-link <?= $currentPage === 'orders-create.php' ? 'active' : '' ?>" href="orders-create.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-bag-shopping"></i></div>
                        Create Order
                    </a>
                    <a class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="orders.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        View Orders
                    </a>

                    <a class="nav-link <?= $isCategoryActive ? 'active' : '' ?>" href="categories.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                        Categories
                    </a>

                    <a class="nav-link <?= $isProductActive ? 'active' : '' ?>" href="products.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-mug-hot"></i></div>
                        Products
                    </a>

                    <a class="nav-link <?= $isCashierActive ? 'active' : '' ?>" href="admins.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        Users
                    </a>

                    <a class="nav-link <?= $isSalesActive ? 'active' : '' ?>" href="sales-report.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Sales
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="hc-sidebar-status" style="--quotaPercent: <?= $quotaPercent ?>;">
            <div class="hc-status-label">Quota for today</div>
            <div class="hc-quota-ring">
                <div class="hc-quota-inner">
                    <div class="hc-quota-value"><?= htmlspecialchars($quotaValueText) ?></div>
                    <div class="hc-quota-unit">CUPS</div>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="hc-account-card">
                <div class="hc-account-avatar"><?= htmlspecialchars($initials) ?></div>
                <div class="hc-account-meta">
                    <div class="hc-account-name"><?= htmlspecialchars($username) ?></div>
                </div>
            </div>
            <a href="../../logout.php" class="hc-logout-link">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Log out</span>
            </a>
        </div>
    </nav>
</div>
