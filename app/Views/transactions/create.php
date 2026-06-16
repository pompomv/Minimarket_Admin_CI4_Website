<?= $this->extend('base') ?>
<?= $this->section('content') ?>
<?php $activePage = 'pos'; ?>

<div class="mb-3"><a href="/transactions" class="btn btn-sm btn-outline-secondary"><i
            class="bi bi-arrow-left me-1"></i>Back</a></div>

<form action="/transactions/store" method="POST" id="posForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <!-- Left: Product picker -->
        <div class="col-lg-7">
            <div class="table-card">
                <div class="card-header d-flex gap-2 align-items-center">
                    <i class="bi bi-cart3"></i><span class="fw-semibold">Select Product</span>
                    <input type="text" id="prodSearch" class="form-control form-control-sm ms-auto"
                        placeholder="Search products…" style="max-width:200px">
                </div>
                <div class="table-responsive" style="max-height:450px; overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="prodTable">
                        <thead class="sticky-top">
                            <tr>
                                <th>Product</th>

                                <th>Price</th>
                                <th>Stock</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr class="<?= $p['stock'] == 0 ? 'table-light text-muted' : '' ?>">
                                    <td>
                                        <div class="fw-semibold">
                                            <?= esc($p['name']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= esc($p['category'] ?? '') ?>
                                        </div>
                                        <input type="hidden" name="product_id[]" value="<?= $p['id'] ?>">
                                    </td>

                                    <td class="small">Rp
                                        <?= number_format($p['price'], 0, ',', '.') ?>
                                    </td>
                                    <td><span class="badge <?= $p['stock'] <= 10 ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $p['stock'] ?>
                                        </span></td>
                                    <td style="width:100px">
                                        <input type="number" name="quantity[]"
                                            class="form-control form-control-sm qty-input" value="0" min="0"
                                            max="<?= $p['stock'] ?>" data-price="<?= $p['price'] ?>"
                                            data-name="<?= esc($p['name']) ?>" <?= $p['stock'] == 0 ? 'disabled' : '' ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Order summary -->
        <div class="col-lg-5">
            <div class="form-card mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">— Walk-in / General —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= esc($c['id']) ?>">
                                <?= esc($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional…">
                </div>
                <hr>
                <div id="orderItems" class="mb-2 small text-muted">No items selected yet.</div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="grandTotal">Rp 0</span>
                </div>
                <button type="submit" class="btn btn-success w-100 mt-3 py-2 fw-semibold">
                    <i class="bi bi-check-circle me-2"></i>Complete Transaction
                </button>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('extra-js') ?>
<script>
    function rupiah(n) {
        return 'Rp ' + parseInt(n).toLocaleString('id-ID');
    }

    function updateSummary() {
        let total = 0;
        let html = '';
        document.querySelectorAll('.qty-input').forEach(inp => {
            const qty = parseInt(inp.value) || 0;
            if (qty > 0) {
                const price = parseFloat(inp.dataset.price);
                const sub = qty * price;
                total += sub;
                html += `<div class="d-flex justify-content-between mb-1">
                <span>${inp.dataset.name} ×${qty}</span>
                <span class="fw-semibold">${rupiah(sub)}</span>
            </div>`;
            }
        });
        document.getElementById('orderItems').innerHTML = html || '<span class="text-muted">No items selected yet.</span>';
        document.getElementById('grandTotal').textContent = rupiah(total);
    }

    document.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('change', updateSummary);
        inp.addEventListener('input', updateSummary);
    });

    document.getElementById('prodSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#prodTable tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    document.getElementById('posForm').addEventListener('submit', function (e) {
        const anyQty = [...document.querySelectorAll('.qty-input')].some(i => parseInt(i.value) > 0);
        if (!anyQty) { e.preventDefault(); alert('Select at least one product with qty > 0!'); }
    });
</script>
<?= $this->endSection() ?>