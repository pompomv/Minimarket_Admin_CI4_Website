<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class CustomerApiController extends BaseController
{
    private CustomerModel $model;

    public function __construct()
    {
        $this->model = new CustomerModel();
    }

    /**
     * GET /api/customers
     * Query: ?search=customer_name
     */
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';

        $builder = $this->model->orderBy('name', 'ASC');

        if (!empty($search)) {
            $builder->like('name', $search);
        }

        $customers = $builder->findAll(100);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $customers,
        ]);
    }

    /**
     * GET /api/customers/{id}
     */
    public function show(int $id)
    {
        $customer = $this->model->find($id);

        if (!$customer) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Customer not found.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $customer,
        ]);
    }

    /**
     * POST /api/customers
     * Body: { "name": "...", "phone": "...", "address": "...", "email": "..." }
     */
    public function store()
    {
        $body = $this->request->getJSON(true);
        $name = trim($body['name'] ?? '');

        if (empty($name)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'Name is required.']);
        }

        $data = [
            'name'    => $name,
            'phone'   => $body['phone']   ?? null,
            'address' => $body['address'] ?? null,
            'email'   => $body['email']   ?? null,
        ];

        $this->model->insert($data);
        $newId    = $this->model->getInsertID();
        $customer = $this->model->find($newId);

        return $this->response
            ->setStatusCode(201)
            ->setJSON(['status' => 'success', 'data' => $customer]);
    }
}
