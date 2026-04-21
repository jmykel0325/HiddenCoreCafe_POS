<div id="layoutSidenav_nav">
    <style>
        #layoutSidenav_nav .sb-sidenav {
            background: #fbf7f2 !important;
            border-right: 1px solid #e9e3dc !important;
            box-shadow: 6px 0 22px rgba(58, 52, 46, 0.05) !important;
        }
        #layoutSidenav_nav .hc-sidebar-brand {
            border-bottom: 1px solid #eee7df !important;
            padding: 1.15rem 1rem .95rem !important;
        }
        #layoutSidenav_nav .hc-brand-link {
            flex-direction: row !important;
            align-items: center !important;
            gap: 0 !important;
        }
        #layoutSidenav_nav .hc-brand-title {
            color: #3a342e !important;
            font-size: 1.45rem !important;
            font-weight: 800 !important;
            text-align: left !important;
            line-height: 1.05 !important;
        }
        #layoutSidenav_nav .hc-brand-subtitle {
            color: #8a8178 !important;
            font-size: .86rem !important;
            text-align: left !important;
            letter-spacing: .08em !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link {
            min-height: 44px !important;
            border-radius: 14px !important;
            padding: .62rem .78rem !important;
            color: #534b43 !important;
            font-weight: 600 !important;
            border: 1px solid transparent !important;
            background: transparent !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link .sb-nav-link-icon {
            width: 24px !important;
            height: 24px !important;
            border-radius: 10px !important;
            background: #f4eee8 !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link .sb-nav-link-icon i {
            color: #8a8178 !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link:hover {
            background: #fff !important;
            border-color: #ebe4dc !important;
            color: #3a342e !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link.active {
            background: linear-gradient(90deg, var(--primary, #FF7A1A) 0%, var(--primary-hover, #E96A0C) 100%) !important;
            border-color: var(--primary, #FF7A1A) !important;
            color: #fff !important;
            box-shadow: 0 10px 18px rgba(255, 122, 26, 0.28) !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link.active .sb-nav-link-icon {
            background: rgba(255, 255, 255, 0.22) !important;
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link.active .sb-nav-link-icon i,
        #layoutSidenav_nav .sb-sidenav .nav-link.active .sb-nav-link-icon svg {
            color: #ffffff !important;
            fill: currentColor !important;
            opacity: 1 !important;
        }
        #layoutSidenav_nav .sb-sidenav .nav-link.active::before {
            display: none !important;
        }
        #layoutSidenav_nav .sb-sidenav-footer {
            background: #fbf7f2 !important;
            border-top: 1px solid #eee7df !important;
        }
        #layoutSidenav_nav .quota-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        #layoutSidenav_nav .quota-input {
            width: 86px;
            min-height: 34px !important;
            border-radius: 8px !important;
            border: 1px solid #ddd !important;
            background: #fff !important;
            color: #4a433d !important;
            text-align: center;
            font-weight: 600;
            font-size: .82rem !important;
            padding: 8px 10px !important;
        }
        #layoutSidenav_nav .quota-save-btn {
            background: var(--primary, #FF7A1A) !important;
            color: #fff !important;
            border: none !important;
            min-height: 34px !important;
            padding: 8px 14px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer;
            transition: 0.2s;
        }
        #layoutSidenav_nav .quota-save-btn:hover {
            background: var(--primary-hover, #E96A0C) !important;
        }
        #layoutSidenav_nav .hc-quota-note {
            margin-top: .4rem;
            color: #8a8178;
            font-size: .72rem;
            line-height: 1.2;
        }
        #layoutSidenav_nav .hc-quota-note.is-ok {
            color: #15803d;
        }
    </style>
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

            $ordersPages = ['orders-create.php', 'orders.php', 'orders-edit.php'];
            $categoryPages = ['categories-create.php', 'categories.php', 'categories-edit.php'];
            $productPages = ['products-create.php', 'products.php', 'products-edit.php'];
            $cashierPages = ['admins.php', 'admins-edit.php'];
            $salesPages = ['sales-report.php'];

            $isOrdersActive = in_array($currentPage, $ordersPages, true);
            $isCreateOrderActive = ($currentPage === 'orders-create.php');
            $isViewOrdersActive = ($currentPage === 'orders.php');
            $isCategoryActive = in_array($currentPage, $categoryPages, true);
            $isProductActive = in_array($currentPage, $productPages, true);
            $isCashierActive = in_array($currentPage, $cashierPages, true);
            $isSalesActive = in_array($currentPage, $salesPages, true);
            $isDashboardActive = ($currentPage === 'dashboard.php');

            $quotaMessage = '';
            $quotaMessageClass = '';
            if(!$isStaff && isset($_POST['save_today_quota'])){
                $postedTarget = isset($_POST['today_target_cups']) ? (int)$_POST['today_target_cups'] : 0;
                $saveOk = saveTodayQuotaTarget($postedTarget, (int)($_SESSION['loggedInUser']['user_id'] ?? 0));
                if($saveOk){
                    $quotaMessage = 'Today quota updated.';
                    $quotaMessageClass = 'is-ok';
                } else {
                    $quotaMessage = 'Unable to update quota.';
                }
            }

            $quotaCurrent = getTodaySoldCups();
            $quotaTarget = getTodayQuotaTarget();
            $quotaPercent = max(0, min(100, (int) round(($quotaCurrent / max(1, $quotaTarget)) * 100)));
        ?>

        <div class="hc-sidebar-brand">
            <div class="hc-brand-row">
                <a href="<?= $homeLink ?>" class="hc-brand-link">
                    <span class="hc-brand-text">
                        <span class="hc-brand-title">POS Coffee</span>
                        <span class="hc-brand-subtitle">Hidden Core Cafe</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav hc-nav hc-nav-main">
                <?php if ($isStaff): ?>
                    <a class="nav-link <?= $isCreateOrderActive ? 'active' : '' ?>" href="orders-create.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                        Create Order
                    </a>
                    <a class="nav-link <?= $isViewOrdersActive ? 'active' : '' ?>" href="orders.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        View Orders
                    </a>
                    <a class="nav-link <?= $isSalesActive ? 'active' : '' ?>" href="sales-report.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Sales Report
                    </a>
                <?php else: ?>
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
                    <div class="hc-quota-value"><?= (int) $quotaCurrent ?>/<?= (int) $quotaTarget ?></div>
                    <div class="hc-quota-unit">CUPS</div>
                </div>
            </div>
            <?php if(!$isStaff): ?>
                <form method="POST" class="quota-input-group">
                    <input
                        type="number"
                        min="0"
                        name="today_target_cups"
                        class="quota-input"
                        value="<?= (int) $quotaTarget ?>"
                        placeholder="Set target cups"
                        aria-label="Set today's quota target in cups"
                    >
                    <button type="submit" name="save_today_quota" class="quota-save-btn">Save</button>
                </form>
                <?php if($quotaMessage !== ''): ?>
                    <div class="hc-quota-note <?= $quotaMessageClass ?>"><?= htmlspecialchars($quotaMessage) ?></div>
                <?php elseif($quotaTarget > 0 && $quotaCurrent >= $quotaTarget): ?>
                    <div class="hc-quota-note is-ok">Quota reached for today.</div>
                <?php endif; ?>
            <?php endif; ?>
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
