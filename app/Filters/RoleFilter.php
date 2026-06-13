<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * RoleFilter — Membatasi akses berdasarkan role user.
 *
 * Penggunaan di Routes.php:
 *   'filter' => 'role:admin'
 *
 * Argumen pertama ($arguments[0]) adalah role yang diizinkan.
 * Jika role session tidak cocok → redirect ke halaman 403.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedRole = $arguments[0] ?? 'admin';
        $userRole = strtolower(session()->get('role') ?? '');

        if ($userRole !== strtolower($allowedRole)) {
            return redirect()->to('/403');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
