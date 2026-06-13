<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JwtHelper;
use App\Models\Users;

class AuthController extends BaseController
{
    private Users $userModel;

    public function __construct()
    {
        $this->userModel = new Users();
    }

    /**
     * POST /api/auth/login
     * Body JSON: { "username": "...", "password": "..." }
     */
    public function login()
    {
        $body = $this->request->getJSON(true);

        $username = trim($body['username'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($username) || empty($password)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Username and password are required.',
                ]);
        }

        // Find user by username or email
        $user = $this->userModel->where('username', $username)->orWhere('email', $username)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid username or password.',
                ]);
        }

        // Generate JWT token
        $token = JwtHelper::generate([
            'user_id'  => $user['id'],
            'username' => $user['username'],
            'role'     => strtolower($user['role']),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Login successful.',
            'token'   => $token,
            'data'    => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => strtolower($user['role']),
                'name'     => $user['username'],
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Stateless JWT — client simply discards the token on their side.
     */
    public function logout()
    {
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}
