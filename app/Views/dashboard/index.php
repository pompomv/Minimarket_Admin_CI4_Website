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
                    <div class="text-muted" style="font-size:.8rem">Penjualan Hari Ini</div>
                    <div class="fw-bold fs-5">Rp <?= number_format($todaySales, 0, ',', '.') ?></div>
                    <div class="text-muted" style="font-size:.78rem"><?= $todayCount ?> transaksi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Total Produk</div>
                    <div class="fw-bold fs-5"><?= $totalProducts ?></div>
                    <div class="text-muted" style="font-size:.78rem">item terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Total Pelanggan</div>
                    <div class="fw-bold fs-5"><?= $totalCustomers ?></div>
                    <div class="text-muted" style="font-size:.78rem">pelanggan terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted" style="font-size:.8rem">Transaksi Pending</div>
                    <div class="fw-bold fs-5"><?= $pendingCount ?></div>
                    <div class="text-muted" style="font-size:.78rem">belum selesai</div>
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
                <span><i class="bi bi-receipt me-2"></i>Transaksi Terbaru</span>
                <a href="/transactions" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tanggal</th>
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
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td></tr>
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
                <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Stok Rendah</span>
                <a href="/products" class="btn btn-sm btn-outline-warning">Kelola</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Produk</th><th>Tipe</th><th>Stok</th></tr></thead>
                    <tbody>
                        <?php foreach ($lowStock as $p): ?>
                        <tr>
                            <td><?= esc($p['name']) ?></td>
                            <td><span class="badge badge-<?= strtolower($p['product_type']) ?>"><?= $p['product_type'] ?></span></td>
                            <td>
                                <span class="fw-bold <?= $p['stock'] == 0 ? 'text-danger' : 'text-warning' ?>"><?= $p['stock'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lowStock)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Semua stok aman ✓</td></tr>
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
                <a href="/transactions/create" class="btn btn-primary"><i class="bi bi-cart-plus me-2"></i>Transaksi Baru</a>
                <a href="/products/add" class="btn btn-outline-success"><i class="bi bi-plus-circle me-2"></i>Tambah Produk</a>
                <a href="/customers/add" class="btn btn-outline-info"><i class="bi bi-person-plus me-2"></i>Tambah Pelanggan</a>
                <a href="/reports" class="btn btn-outline-secondary"><i class="bi bi-bar-chart me-2"></i>Lihat Laporan</a>
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
                    <div class="text-muted" style="font-size:.85rem">Penjualan Saya Hari Ini</div>
                    <div class="fw-bold" style="font-size:1.6rem">Rp <?= number_format($todaySales, 0, ',', '.') ?></div>
                    <div class="text-muted" style="font-size:.82rem"><?= $todayCount ?> transaksi selesai</div>
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
                    <div class="text-muted" style="font-size:.85rem">Jumlah Transaksi</div>
                    <div class="fw-bold" style="font-size:1.6rem"><?= $todayCount ?></div>
                    <div class="text-muted" style="font-size:.82rem">hari ini</div>
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
            <span>Selamat datang, <strong><?= esc(session('username')) ?></strong>! Gunakan menu <strong>Kasir / POS</strong> untuk memulai transaksi baru.</span>
        </div>
    </div>
</div>

<!-- Quick Actions (Kasir) -->
<div class="row g-3">
    <div class="col-12">
        <div class="card stat-card">
            <div class="card-body d-flex gap-2 flex-wrap">
                <a href="/transactions/create" class="btn btn-primary btn-lg"><i class="bi bi-cart-plus me-2"></i>Transaksi Baru</a>
                <a href="/transactions" class="btn btn-outline-secondary"><i class="bi bi-receipt me-2"></i>Lihat Transaksi</a>
                <a href="/customers/add" class="btn btn-outline-info"><i class="bi bi-person-plus me-2"></i>Daftarkan Pelanggan</a>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>
