<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'transactions'; ?>

<div class="mb-3 d-flex gap-2">
    <a href="/transactions" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-primary"><i
            class="bi bi-printer me-1"></i>Print</button>
</div>

<div class="row g-3">
    <!-- Transaction Info -->
    <div class="col-md-4">
        <div class="form-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Transaction Info</h6>
            <table class="table table-sm mb-0">
                <tr>
                    <th class="pe-3 text-muted small fw-normal">ID</th>
                    <td class="font-monospace small">
                        <?= esc($transaction['id']) ?>
                    </td>
                </tr>
                <tr>
                    <th class="pe-3 text-muted small fw-normal">Invoice No</th>
                    <td class="font-monospace small text-primary fw-bold">
                        <?= esc($transaction['invoice_no']) ?>
                    </td>
                </tr>
                <tr>
                    <th class="pe-3 text-muted small fw-normal">Date</th>
                    <td>
                        <?= date('d/m/Y H:i', strtotime($transaction['transaction_date'])) ?>
                    </td>
                </tr>
                <tr>
                    <th class="pe-3 text-muted small fw-normal">Customer</th>
                    <td>
                        <?= esc($transaction['customer_name'] ?? '— Walk-in —') ?>
                    </td>
                </tr>
                <?php if ($transaction['customer_phone']): ?>
                    <tr>
                        <th class="pe-3 text-muted small fw-normal">HP</th>
                        <td>
                            <?= esc($transaction['customer_phone']) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th class="pe-3 text-muted small fw-normal">Status</th>
                    <td><span class="badge badge-<?= strtolower($transaction['status']) ?>">
                            <?= $transaction['status'] ?>
                        </span></td>
                </tr>
                <?php if ($transaction['notes']): ?>
                    <tr>
                        <th class="pe-3 text-muted small fw-normal">Notes</th>
                        <td class="small">
                            <?= esc($transaction['notes']) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Detail Items -->
    <div class="col-md-8">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-list-ul me-2"></i>Purchase Items</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>

                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $i => $d): ?>
                            <tr>
                                <td class="text-muted small">
                                    <?= $i + 1 ?>
                                </td>
                                <td class="fw-semibold">
                                    <?= esc($d['product_name']) ?>
                                </td>

                                <td>
                                    <?= $d['quantity'] ?>
                                </td>
                                <td>Rp
                                    <?= number_format($d['price'], 0, ',', '.') ?>
                                </td>
                                <td class="fw-semibold">Rp
                                    <?= number_format($d['subtotal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <td colspan="5" class="fw-bold text-end">TOTAL</td>
                            <td class="fw-bold fs-5">Rp
                                <?= number_format($transaction['total_amount'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>