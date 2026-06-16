<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
    <title>Login — Minimarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: #2563eb;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            margin: 0 auto 1rem;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .2);
        }

        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="brand-icon"><i class="bi bi-shop"></i></div>
        <h5 class="text-center fw-bold mb-1">Minimarket</h5>
        <p class="text-center text-muted mb-4" style="font-size:.9rem">Admin access only — sign in to continue</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2"><i
                    class="bi bi-exclamation-triangle me-1"></i><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="/login/auth" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required
                        autofocus value="<?= esc(old('username')) ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required
                        id="passInput">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePass()"><i class="bi bi-eye"
                            id="eyeIcon"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>
        <p class="text-center mt-3 text-muted" style="font-size:.8rem">
        </p>
        <hr class="my-3">
        <div class="alert alert-info py-2 mb-0" style="font-size:.82rem">
            <i class="bi bi-phone me-1"></i> Cashier? Use the <strong>mobile app</strong> to log in.
        </div>
    </div>
    <script>
        function togglePass() {
            const el = document.getElementById('passInput');
            const ic = document.getElementById('eyeIcon');
            if (el.type === 'password') { el.type = 'text'; ic.className = 'bi bi-eye-slash'; }
            else { el.type = 'password'; ic.className = 'bi bi-eye'; }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>