<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'customers'; ?>

<div class="mb-3"><a href="/customers" class="btn btn-sm btn-outline-secondary"><i
            class="bi bi-arrow-left me-1"></i>Kembali</a></div>
<div class="form-card" style="max-width:560px">
    <h6 class="fw-bold mb-3"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pelanggan</h6>
    <form action="/customers/store" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= esc(old('name')) ?>">
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">No. HP</label>
                <input type="text" name="phone" class="form-control" value="<?= esc(old('phone')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label fw-semibold">Alamat</label>
            <textarea name="address" class="form-control" rows="2"><?= esc(old('address')) ?></textarea>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Simpan</button>
            <a href="/customers" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>