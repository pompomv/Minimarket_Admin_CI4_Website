<?php

namespace App\Controllers;

use App\Models\Users;

class LoginController extends BaseController
{
    private Users $model;

    public function __construct()
    {
        $this->model = new Users();
    }

    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('users/login', ['title' => 'Login — Minimarket']);
    }

    public function auth()
    {
        $session = session();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->model->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            // Website is restricted to admin accounts only
            if (strtolower($user['role']) !== 'admin') {
                $session->setFlashdata('error', 'Access denied. Cashier accounts must use the mobile app.');
                return redirect()->to('/login');
            }

            $session->set([
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'role'      => strtolower($user['role']),
                'logged_in' => true,
            ]);
            return redirect()->to('/dashboard');
        }

        $session->setFlashdata('error', 'Invalid username or password!');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}