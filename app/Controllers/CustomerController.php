<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    private CustomerModel $model;

    public function __construct()
    {
        $this->model = new CustomerModel();
    }

    public function index()
    {
        return view('customers/list', [
            'title' => 'Daftar Pelanggan',
            'customers' => $this->model->orderBy('name')->findAll(),
        ]);
    }

    public function add()
    {
        return view('customers/add', [
            'title' => 'Tambah Pelanggan',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        if (!$this->validate(['name' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('uuid');
        $this->model->insert([
            'id' => generate_uuid(),
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone') ?: null,
            'email' => $this->request->getPost('email') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ]);

        return $this->withSuccess('/customers', 'Pelanggan berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $customer = $this->model->find($id);
        if (!$customer) {
            return $this->withError('/customers', 'Pelanggan tidak ditemukan.');
        }

        return view('customers/edit', [
            'title' => 'Edit Pelanggan',
            'customer' => $customer,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(string $id)
    {
        if (!$this->validate(['name' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone') ?: null,
            'email' => $this->request->getPost('email') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ]);

        return $this->withSuccess('/customers', 'Pelanggan berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $this->model->delete($id);
        return $this->withSuccess('/customers', 'Pelanggan berhasil dihapus.');
    }
}
