<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
    <title><?= esc($title ?? 'Minimarket') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 240px;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-bg: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-hover: #334155;
            --sidebar-active: #2563eb;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform .25s;
        }

        .sidebar-brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #334155;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .sidebar-brand span {
            color: var(--primary);
        }

        .sidebar-nav {
            flex: 1;
            padding: .75rem 0;
            overflow-y: auto;
        }

        .nav-section {
            color: #64748b;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .75rem 1rem .25rem;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem 1rem;
            color: var(--sidebar-text);
            border-radius: 0;
            font-size: .9rem;
            transition: background .15s, color .15s;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.05rem;
            width: 20px;
        }

        /* ── Main ── */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .topbar-user {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #475569;
            font-size: .9rem;
        }

        .page-content {
            flex: 1;
            padding: 1.5rem;
        }

        /* ── Cards ── */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Tables ── */
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
        }

        .table-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .table thead th {
            background: #f8fafc;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 2px solid #e2e8f0;
        }

        .badge-food {
            background: #dcfce7;
            color: #166534;
        }

        .badge-beverage {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-electronic {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ── Forms ── */
        .form-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            padding: 1.5rem;
        }

        @media (max-width:768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════ -->
    <nav id="sidebar">
        <a href="/dashboard" class="sidebar-brand d-flex align-items-center gap-2">
            <i class="bi bi-shop"></i>
            Mini<span>Market</span>
        </a>
        <div class="sidebar-nav">
            <div class="nav-section">Utama</div>
            <a href="/dashboard" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"><i
                    class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="/transactions" class="nav-link <?= ($activePage ?? '') === 'transactions' ? 'active' : '' ?>"><i
                    class="bi bi-receipt"></i> Transaksi</a>
            <a href="/transactions/create" class="nav-link <?= ($activePage ?? '') === 'pos' ? 'active' : '' ?>">
                <i class="bi bi-cart-plus-fill"></i> Kasir
            </a>

            <div class="nav-section">Master Data</div>
            <?php if (session('role') === 'admin'): ?>
                <a href="/products" class="nav-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>"><i
                        class="bi bi-box-seam"></i> Produk</a>
            <?php endif; ?>
            <a href="/customers" class="nav-link <?= ($activePage ?? '') === 'customers' ? 'active' : '' ?>"><i
                    class="bi bi-people"></i> Pelanggan</a>
            <?php if (session('role') === 'admin'): ?>
                <a href="/suppliers" class="nav-link <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>"><i
                        class="bi bi-truck"></i> Supplier</a>

                <div class="nav-section">Laporan</div>
                <a href="/reports" class="nav-link <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>"><i
                        class="bi bi-bar-chart-line"></i> Laporan</a>
            <?php endif; ?>

            <div class="nav-section">Akun</div>
            <a href="/logout" class="nav-link" style="color:#fca5a5;" onmouseover="this.style.color='#f87171'"
                onmouseout="this.style.color='#fca5a5'"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </nav>


    <!-- ═══════════════════════════════════════════════════════════
     MAIN
═══════════════════════════════════════════════════════════ -->
    <div id="main-content">
        <!-- Topbar -->
        <div id="topbar">
            <button class="btn btn-sm btn-outline-secondary d-md-none"
                onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <span class="topbar-title"><?= esc($title ?? '') ?></span>
            <div class="topbar-user">
                <i class="bi bi-person-circle fs-5"></i>
                <span><?= esc(session('username') ?? 'Guest') ?></span>
                <span class="badge bg-primary ms-1"><?= esc(session('role') ?? '') ?></span>
            </div>
        </div>

        <!-- Alert flashes -->
        <div class="px-4 pt-3">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><strong>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page content -->
        <div class="page-content">
            <?= $this->renderSection('content') ?>
        </div>

        <footer class="text-center py-3 text-muted"
            style="font-size:.8rem; border-top:1px solid #e2e8f0; background:#fff;">
            Minimarket &copy; <?= date('Y') ?> &mdash; CodeIgniter <?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
            &mdash; Rendered in {elapsed_time}s
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('extra-js') ?>
</body>

</html>