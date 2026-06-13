<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= esc($title ?? '403 — Akses Ditolak') ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc2626;
            font-size: 2.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .error-code {
            font-size: 4rem;
            font-weight: 800;
            color: #dc2626;
            line-height: 1;
            margin-bottom: .5rem;
        }

        .error-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .75rem;
        }

        .error-desc {
            color: #64748b;
            font-size: .95rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Akses Ditolak</div>
        <p class="error-desc">
            Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Halaman ini hanya bisa diakses oleh <strong>Admin</strong>.
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="/dashboard" class="btn btn-primary">
                <i class="bi bi-house me-2"></i>Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
        <?php if (session()->get('role')): ?>
            <p class="mt-3 text-muted" style="font-size:.8rem">
                Anda login sebagai: <strong>
                    <?= esc(session('username')) ?>
                </strong>
                <span class="badge bg-secondary ms-1">
                    <?= esc(session('role')) ?>
                </span>
            </p>
        <?php endif; ?>
    </div>
</body>

</html>