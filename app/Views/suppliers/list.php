<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'suppliers'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="/suppliers/add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Supplier</a>
</div>

<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-truck me-2"></i>Supplier List (<?= count($suppliers) ?>)</span>
        <input type="text" id="searchBox" class="form-control form-control-sm w-auto" placeholder="Search suppliers…" style="min-width:200px">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="suppTable">
            <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($suppliers as $i => $s): ?>
                <tr>
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td class="fw-semibold"><?= esc($s['name']) ?></td>
                    <td class="small"><?= esc($s['phone'] ?? '—') ?></td>
                    <td class="small"><?= esc($s['email'] ?? '—') ?></td>
                    <td class="small text-muted"><?= esc($s['address'] ?? '—') ?></td>
                    <td>
                        <a href="/suppliers/edit/<?= esc($s['id']) ?>" class="btn btn-sm btn-outline-warning py-0"><i class="bi bi-pencil"></i></a>
                        <a href="/suppliers/destroy/<?= esc($s['id']) ?>" class="btn btn-sm btn-outline-danger py-0"
                           onclick="return confirm('Delete this supplier?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No suppliers found.</td></tr>
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
    document.querySelectorAll('#suppTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
