<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'dashboard'; ?>

<?php if (($userRole ?? '') === 'admin'): ?>

<!-- ══════════════════════════════════════════
     ADMIN VIEW: Statistik Lengkap
══════════════════════════════════════════ -->

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dbeafe; color:#2563eb;"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Today's Sales</div>
                    <div class="fw-bold fs-5">Rp <?= number_format($todaySales, 0, ',', '.') ?></div>
                    <div class="text-muted" style="font-size:.78rem"><?= $todayCount ?> transactions</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Total Products</div>
                    <div class="fw-bold fs-5"><?= $totalProducts ?></div>
                    <div class="text-muted" style="font-size:.78rem">registered items</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Total Customers</div>
                    <div class="fw-bold fs-5"><?= $totalCustomers ?></div>
                    <div class="text-muted" style="font-size:.78rem">registered customers</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Pending Transactions</div>
                    <div class="fw-bold fs-5"><?= $pendingCount ?></div>
                    <div class="text-muted" style="font-size:.78rem">not yet completed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent Transactions -->
    <div class="col-lg-7">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>Recent Transactions</span>
                <a href="/transactions" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTx as $tx): ?>
                        <tr>
                            <td><a href="/transactions/detail/<?= esc($tx['id']) ?>" class="text-primary text-decoration-none small"><?= esc(substr($tx['id'], 0, 8)) ?>…</a></td>
                            <td><?= esc($tx['customer_name'] ?? '— Walk-in —') ?></td>
                            <td class="fw-semibold">Rp <?= number_format($tx['total_amount'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($tx['status']) ?>"><?= $tx['status'] ?></span>
                            </td>
                            <td class="text-muted small"><?= date('d/m H:i', strtotime($tx['transaction_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentTx)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="col-lg-5">
        <div class="table-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock</span>
                <a href="/products" class="btn btn-sm btn-outline-warning">Manage</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Product</th><th>Type</th><th>Stock</th></tr></thead>
                    <tbody>
                        <?php foreach ($lowStock as $p): ?>
                        <tr>
                            <td><?= esc($p['name']) ?></td>
                            <td>
                                <span class="fw-bold <?= $p['stock'] == 0 ? 'text-danger' : 'text-warning' ?>"><?= $p['stock'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lowStock)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">All stock levels are safe ✓</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions (Admin) -->
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card stat-card">
            <div class="card-body d-flex gap-2 flex-wrap">
                <a href="/transactions/create" class="btn btn-primary"><i class="bi bi-cart-plus me-2"></i>New Transaction</a>
                <a href="/products/add" class="btn btn-outline-success"><i class="bi bi-plus-circle me-2"></i>Add Product</a>
                <a href="/customers/add" class="btn btn-outline-info"><i class="bi bi-person-plus me-2"></i>Add Customer</a>
                <a href="/reports" class="btn btn-outline-secondary"><i class="bi bi-bar-chart me-2"></i>View Reports</a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<!-- ══════════════════════════════════════════
     KASIR VIEW: Penjualan Saya Hari Ini
══════════════════════════════════════════ -->

<div class="row g-3 mb-4">
    <div class="col-sm-8 col-lg-5">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dbeafe; color:#2563eb; width:60px; height:60px; font-size:1.8rem;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.85rem">My Sales Today</div>
                    <div class="fw-bold" style="font-size:1.6rem">Rp <?= number_format($todaySales, 0, ',', '.') ?></div>
                    <div class="text-muted" style="font-size:.82rem"><?= $todayCount ?> completed transactions</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7; color:#16a34a; width:60px; height:60px; font-size:1.8rem;">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.85rem">Total Transactions</div>
                    <div class="fw-bold" style="font-size:1.6rem"><?= $todayCount ?></div>
                    <div class="text-muted" style="font-size:.82rem">today</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info panel kasir -->
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <span>Welcome, <strong><?= esc(session('username')) ?></strong>! Use the <strong>Cashier / POS</strong> menu to start a new transaction.</span>
        </div>
    </div>
</div>

<!-- Quick Actions (Kasir) -->
<div class="row g-3">
    <div class="col-12">
        <div class="card stat-card">
            <div class="card-body d-flex gap-2 flex-wrap">
                <a href="/transactions/create" class="btn btn-primary btn-lg"><i class="bi bi-cart-plus me-2"></i>New Transaction</a>
                <a href="/transactions" class="btn btn-outline-secondary"><i class="bi bi-receipt me-2"></i>View Transactions</a>
                <a href="/customers/add" class="btn btn-outline-info"><i class="bi bi-person-plus me-2"></i>Register Customer</a>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>
