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
            'title' => 'Customer List',
            'customers' => $this->model->orderBy('name')->findAll(),
        ]);
    }

    public function add()
    {
        return view('customers/add', [
            'title' => 'Add Customer',
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

        return $this->withSuccess('/customers', 'Customer added successfully!');
    }

    public function edit(string $id)
    {
        $customer = $this->model->find($id);
        if (!$customer) {
            return $this->withError('/customers', 'Customer not found.');
        }

        return view('customers/edit', [
            'title' => 'Edit Customer',
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

        return $this->withSuccess('/customers', 'Customer updated successfully!');
    }

    public function destroy(string $id)
    {
        $this->model->delete($id);
        return $this->withSuccess('/customers', 'Customer deleted successfully.');
    }
}
