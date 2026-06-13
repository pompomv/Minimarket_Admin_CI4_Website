<?php

namespace App\Controllers;

use App\Models\Users;

class RegisterController extends BaseController
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
        return view('users/register', ['title' => 'Daftar Akun — Minimarket']);
    }

    public function save()
    {
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'Username sudah digunakan, pilih yang lain.',
                ],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'permit_empty|valid_email|max_length[100]|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'Email sudah terdaftar.',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'min_length' => 'Password minimal 6 karakter.',
                ],
            ],
            'password_confirm' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Konfirmasi password tidak cocok.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email') ?: null,
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role' => 'cashier',    // pendaftar baru mendapat role cashier
        ]);

        return redirect()->to('/login')
            ->with('success', 'Akun berhasil dibuat! Silahkan login.');
    }
}
