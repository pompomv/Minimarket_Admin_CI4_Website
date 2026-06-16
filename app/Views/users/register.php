<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
    <title>Register Account — Minimarket</title>
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

        .register-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 440px;
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

        .divider {
            position: relative;
            text-align: center;
            margin: 1rem 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            position: relative;
            background: #fff;
            padding: 0 .75rem;
            color: #94a3b8;
            font-size: .85rem;
        }
    </style>
</head>

<body>
    <div class="register-card">
        <div class="brand-icon"><i class="bi bi-shop"></i></div>
        <h5 class="text-center fw-bold mb-1">Create New Account</h5>
        <p class="text-center text-muted mb-4" style="font-size:.9rem">Register to access Minimarket</p>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i><strong>There are errors:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/register/save" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="min. 3 characters" required
                        autofocus value="<?= esc(old('username')) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email <span class="text-muted fw-normal">(optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="example@email.com"
                        value="<?= esc(old('email')) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" id="passInput"
                        placeholder="min. 6 characters" required>
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePass('passInput','eyeIcon1')">
                        <i class="bi bi-eye" id="eyeIcon1"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirm" class="form-control" id="passConfirm"
                        placeholder="Repeat password" required>
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePass('passConfirm','eyeIcon2')">
                        <i class="bi bi-eye" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="divider mt-4"><span>Already have an account?</span></div>
        <a href="/login" class="btn btn-outline-secondary w-100 mt-2">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login here
        </a>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const el = document.getElementById(inputId);
            const ic = document.getElementById(iconId);
            if (el.type === 'password') { el.type = 'text'; ic.className = 'bi bi-eye-slash'; }
            else { el.type = 'password'; ic.className = 'bi bi-eye'; }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>