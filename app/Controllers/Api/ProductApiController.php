<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductApiController extends BaseController
{
    private ProductModel $model;

    public function __construct()
    {
        $this->model = new ProductModel();
    }

    /**
     * GET /api/products
     * Query: ?search=...&category=...
     */
    public function index()
    {
        $search   = $this->request->getGet('search') ?? '';
        $category = $this->request->getGet('category') ?? '';

        $builder = $this->model->db->table('products p')
            ->select('p.*, s.name AS supplier_name')
            ->join('suppliers s', 's.id = p.supplier_id', 'left')
            ->where('p.stock >', 0)
            ->orderBy('p.name', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.category', $search)
                ->groupEnd();
        }

        if (!empty($category)) {
            $builder->where('p.product_type', strtoupper($category));
        }

        $products = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $products,
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(int $id)
    {
        $product = $this->model->db->table('products p')
            ->select('p.*, s.name AS supplier_name')
            ->join('suppliers s', 's.id = p.supplier_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$product) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $product,
        ]);
    }

    /**
     * POST /api/products
     */
    public function store()
    {
        $body = $this->request->getJSON(true);

        $name        = trim($body['name'] ?? '');
        $price       = (float) ($body['price'] ?? 0);
        $stock       = (int) ($body['stock'] ?? 0);
        $category    = $body['category'] ?? 'Other';
        $description = $body['description'] ?? '';

        if (empty($name) || $price <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'Product name and price are required.']);
        }

        $this->model->insert([
            'name'        => $name,
            'price'       => $price,
            'stock'       => $stock,
            'category'    => $category,
            'description' => $description,
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['status' => 'success', 'message' => 'Product added successfully.']);
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(int $id)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        $body = $this->request->getJSON(true);

        $this->model->update($id, [
            'name'        => trim($body['name'] ?? $product['name']),
            'price'       => (float) ($body['price'] ?? $product['price']),
            'stock'       => (int) ($body['stock'] ?? $product['stock']),
            'category'    => $body['category'] ?? $product['category'],
            'description' => $body['description'] ?? $product['description'],
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Product updated successfully.',
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        $this->model->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Product deleted successfully.',
        ]);
    }
}
