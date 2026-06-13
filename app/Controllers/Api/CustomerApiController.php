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
     * Used by the cashier to look up registered customers by name.
     */
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';

        $builder = $this->model->orderBy('name', 'ASC');

        if (!empty($search)) {
            $builder->like('name', $search);
        }

        $customers = $builder->findAll(50); // max 50 results

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
}
