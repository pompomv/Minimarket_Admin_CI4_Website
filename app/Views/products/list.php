<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'products'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="/products/add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Product</a>
</div>

<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2"></i>Product List (<?= count($products) ?>)</span>
        <input type="text" id="searchBox" class="form-control form-control-sm w-auto" placeholder="Search products…" style="min-width:200px">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="productTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Supplier</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $i => $p): ?>
                <tr>
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td class="fw-semibold"><?= esc($p['name']) ?></td>
                    <td class="small"><?= esc($p['category'] ?? '—') ?></td>
                    <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge <?= $p['stock'] <= 10 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td class="small"><?= esc($p['supplier_name'] ?? '—') ?></td>
                    <td class="small"><?= $p['expiry_date'] ? date('d/m/Y', strtotime($p['expiry_date'])) : '—' ?></td>
                    <td>
                        <a href="/products/edit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning py-0"><i class="bi bi-pencil"></i></a>
                        <a href="/products/destroy/<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger py-0"
                           onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">No products found. <a href="/products/add">Add one now</a></td></tr>
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
    document.querySelectorAll('#productTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
