<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'suppliers'; ?>

<div class="mb-3"><a href="/suppliers" class="btn btn-sm btn-outline-secondary"><i
            class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="form-card" style="max-width:560px">
    <h6 class="fw-bold mb-3"><i class="bi bi-pencil me-2 text-warning"></i>Edit Supplier</h6>
    <form action="/suppliers/update/<?= esc($supplier['id']) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                value="<?= esc(old('name', $supplier['name'])) ?>">
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control"
                    value="<?= esc(old('phone', $supplier['phone'])) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= esc(old('email', $supplier['email'])) ?>">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label fw-semibold">Address</label>
            <textarea name="address" class="form-control"
                rows="2"><?= esc(old('address', $supplier['address'])) ?></textarea>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle me-2"></i>Update</button>
            <a href="/suppliers" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>