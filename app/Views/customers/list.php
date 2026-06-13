<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'customers'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="/customers/add" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Tambah Pelanggan</a>
</div>

<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Daftar Pelanggan (<?= count($customers) ?>)</span>
        <input type="text" id="searchBox" class="form-control form-control-sm w-auto" placeholder="Cari pelanggan…" style="min-width:200px">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="custTable">
            <thead><tr><th>#</th><th>Nama</th><th>No. HP</th><th>Email</th><th>Alamat</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($customers as $i => $c): ?>
                <tr>
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td class="fw-semibold"><?= esc($c['name']) ?></td>
                    <td class="small"><?= esc($c['phone'] ?? '—') ?></td>
                    <td class="small"><?= esc($c['email'] ?? '—') ?></td>
                    <td class="small text-muted"><?= esc($c['address'] ?? '—') ?></td>
                    <td>
                        <a href="/customers/edit/<?= esc($c['id']) ?>" class="btn btn-sm btn-outline-warning py-0"><i class="bi bi-pencil"></i></a>
                        <a href="/customers/destroy/<?= esc($c['id']) ?>" class="btn btn-sm btn-outline-danger py-0"
                           onclick="return confirm('Hapus pelanggan ini?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pelanggan.</td></tr>
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
    document.querySelectorAll('#custTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
