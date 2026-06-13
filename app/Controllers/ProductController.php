<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\SupplierModel;

class ProductController extends BaseController
{
    private ProductModel $model;
    private SupplierModel $supplierModel;

    public function __construct()
    {
        $this->model = new ProductModel();
        $this->supplierModel = new SupplierModel();
    }

    public function index()
    {
        return view('products/list', [
            'title' => 'Daftar Produk',
            'products' => $this->model->withSupplier(),
        ]);
    }

    public function add()
    {
        return view('products/add', [
            'title' => 'Tambah Produk',
            'suppliers' => $this->supplierModel->findAll(),
            'types' => ['FOOD', 'BEVERAGE', 'ELECTRONIC'],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        $rules = [
            'product_type' => 'required|in_list[FOOD,BEVERAGE,ELECTRONIC]',
            'name' => 'required|max_length[100]',
            'price' => 'required|decimal',
            'stock' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'product_type' => $this->request->getPost('product_type'),
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'description' => $this->request->getPost('description'),
            'supplier_id' => $this->request->getPost('supplier_id') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'category' => $this->request->getPost('category'),
        ]);

        return $this->withSuccess('/products', 'Produk berhasil ditambahkan!');
    }

    public function edit(int $id)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->withError('/products', 'Produk tidak ditemukan.');
        }

        return view('products/edit', [
            'title' => 'Edit Produk',
            'product' => $product,
            'suppliers' => $this->supplierModel->findAll(),
            'types' => ['FOOD', 'BEVERAGE', 'ELECTRONIC'],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'product_type' => 'required|in_list[FOOD,BEVERAGE,ELECTRONIC]',
            'name' => 'required|max_length[100]',
            'price' => 'required|decimal',
            'stock' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'product_type' => $this->request->getPost('product_type'),
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'description' => $this->request->getPost('description'),
            'supplier_id' => $this->request->getPost('supplier_id') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'category' => $this->request->getPost('category'),
        ]);

        return $this->withSuccess('/products', 'Produk berhasil diperbarui!');
    }

    public function destroy(int $id)
    {
        $this->model->delete($id);
        return $this->withSuccess('/products', 'Produk berhasil dihapus.');
    }
}
