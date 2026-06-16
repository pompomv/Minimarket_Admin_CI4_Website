<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'products'; ?>

<div class="mb-3"><a href="/products" class="btn btn-sm btn-outline-secondary"><i
            class="bi bi-arrow-left me-1"></i>Back</a></div>

<div class="form-card" style="max-width:700px">
    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Product</h6>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
                <div><i class="bi bi-x-circle me-1"></i>
                    <?= esc($e) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/products/store" method="POST">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required value="<?= esc(old('name')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Price (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control" required min="0" step="100"
                    value="<?= esc(old('price')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                <input type="number" name="stock" class="form-control" required min="0"
                    value="<?= esc(old('stock', 0)) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g. Instant Noodles, Snacks…"
                    value="<?= esc(old('category')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= esc($s['id']) ?>" <?= old('supplier_id') === $s['id'] ? 'selected' : '' ?>>
                            <?= esc($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" value="<?= esc(old('expiry_date')) ?>">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="2"><?= esc(old('description')) ?></textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Save</button>
            <a href="/products" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>