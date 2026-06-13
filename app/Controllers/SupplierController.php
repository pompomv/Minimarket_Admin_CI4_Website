<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class SupplierController extends BaseController
{
    private SupplierModel $model;

    public function __construct()
    {
        $this->model = new SupplierModel();
    }

    public function index()
    {
        return view('suppliers/list', [
            'title' => 'Daftar Supplier',
            'suppliers' => $this->model->orderBy('name')->findAll(),
        ]);
    }

    public function add()
    {
        return view('suppliers/add', [
            'title' => 'Tambah Supplier',
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

        return $this->withSuccess('/suppliers', 'Supplier berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $supplier = $this->model->find($id);
        if (!$supplier) {
            return $this->withError('/suppliers', 'Supplier tidak ditemukan.');
        }

        return view('suppliers/edit', [
            'title' => 'Edit Supplier',
            'supplier' => $supplier,
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

        return $this->withSuccess('/suppliers', 'Supplier berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $this->model->delete($id);
        return $this->withSuccess('/suppliers', 'Supplier berhasil dihapus.');
    }
}
