<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'transactions'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="/transactions/create" class="btn btn-primary"><i class="bi bi-cart-plus me-2"></i>Transaksi Baru</a>
</div>

<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt me-2"></i>Daftar Transaksi (<?= count($transactions) ?>)</span>
        <input type="text" id="searchBox" class="form-control form-control-sm w-auto" placeholder="Cari transaksi…" style="min-width:200px">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="txTable">
            <thead>
                <tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><span class="font-monospace small text-muted"><?= esc(substr($tx['id'], 0, 8)) ?>…</span></td>
                    <td class="small"><?= date('d/m/Y H:i', strtotime($tx['transaction_date'])) ?></td>
                    <td><?= $tx['customer_name'] ? esc($tx['customer_name']) : '<span class="text-muted">Walk-in</span>' ?></td>
                    <td class="fw-semibold">Rp <?= number_format($tx['total_amount'], 0, ',', '.') ?></td>
                    <td><span class="badge badge-<?= strtolower($tx['status']) ?>"><?= $tx['status'] ?></span></td>
                    <td class="d-flex gap-1">
                        <a href="/transactions/detail/<?= esc($tx['id']) ?>" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-eye"></i></a>
                        <?php if ($tx['status'] === 'PENDING' && session('role') === 'admin'): ?>
                            <a href="/transactions/cancel/<?= esc($tx['id']) ?>" class="btn btn-sm btn-outline-danger py-0"
                               onclick="return confirm('Batalkan transaksi ini?')"><i class="bi bi-x-circle"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('extra-js') ?>
<script>
document.getElementById('searchBox').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#txTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
