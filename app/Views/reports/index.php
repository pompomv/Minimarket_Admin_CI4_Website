<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'reports'; ?>

<!-- Date Filter -->
<div class="form-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>">
        </div>
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date"   class="form-control form-control-sm" value="<?= esc($endDate) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="/reports" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Pendapatan</div>
                <div class="fw-bold fs-5 text-success">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small">Jumlah Transaksi</div>
                <div class="fw-bold fs-5"><?= count($rows) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small">Rata-rata / Transaksi</div>
                <div class="fw-bold fs-5">Rp <?= count($rows) > 0 ? number_format($totalRevenue / count($rows), 0, ',', '.') : '0' ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small">Periode</div>
                <div class="fw-semibold small"><?= date('d M Y', strtotime($startDate)) ?> — <?= date('d M Y', strtotime($endDate)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Top Products -->
    <div class="col-lg-5">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Top 5 Produk Terlaris</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>#</th><th>Produk</th><th>Qty</th><th>Pendapatan</th></tr></thead>
                    <tbody>
                        <?php foreach ($topProducts as $i => $p): ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= $i+1 ?></td>
                            <td>
                                <div><?= esc($p['product_name']) ?></div>
                                <span class="badge badge-<?= strtolower($p['product_type']) ?> small"><?= $p['product_type'] ?></span>
                            </td>
                            <td><?= number_format($p['total_qty']) ?></td>
                            <td class="text-success fw-semibold">Rp <?= number_format($p['total_revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown -->
    <div class="col-lg-7">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Penjualan Per Hari</div>
            <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="sticky-top"><tr><th>Tanggal</th><th>Total Penjualan</th></tr></thead>
                    <tbody>
                        <?php foreach ($byDate as $date => $amt): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($date)) ?></td>
                            <td class="fw-semibold text-success">Rp <?= number_format($amt, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($byDate)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-4">Tidak ada data untuk periode ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
