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
        return view('users/register', ['title' => 'Register — Minimarket']);
    }

    public function save()
    {
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'Username already taken, please choose another.',
                ],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'permit_empty|valid_email|max_length[100]|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'Email already registered.',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'min_length' => 'Password must be at least 6 characters.',
                ],
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Password confirmation does not match.',
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
            'role' => 'cashier',    // new registrants get the cashier role
        ]);

        return redirect()->to('/login')
            ->with('success', 'Account created successfully! Please login.');
    }
}
