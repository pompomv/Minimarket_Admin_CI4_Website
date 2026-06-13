<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;

class TransactionController extends BaseController
{
    private TransactionModel $model;
    private TransactionDetailModel $detailModel;
    private ProductModel $productModel;
    private CustomerModel $customerModel;

    public function __construct()
    {
        $this->model = new TransactionModel();
        $this->detailModel = new TransactionDetailModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
    }

    /** List all transactions */
    public function index()
    {
        return view('transactions/list', [
            'title' => 'Daftar Transaksi',
            'transactions' => $this->model->withCustomer(),
        ]);
    }

    /** Create new transaction form */
    public function create()
    {
        return view('transactions/create', [
            'title' => 'Transaksi Baru',
            'customers' => $this->customerModel->orderBy('name')->findAll(),
            'products' => $this->productModel->withSupplier(),
        ]);
    }

    /** Save new transaction + details in one POST */
    public function store()
    {
        helper('uuid');

        $productIds = $this->request->getPost('product_id');
        $quantities = $this->request->getPost('quantity');

        if (empty($productIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu produk!');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Create transaction header
        $txId = generate_uuid();
        $this->model->insert([
            'id' => $txId,
            'transaction_date' => date('Y-m-d H:i:s'),
            'customer_id' => $this->request->getPost('customer_id') ?: null,
            'status' => 'PENDING',
            'notes' => $this->request->getPost('notes'),
            'total_amount' => 0,
        ]);

        // Insert each detail line
        foreach ($productIds as $i => $pid) {
            $qty = (int) ($quantities[$i] ?? 1);
            if ($qty < 1)
                continue;

            $product = $this->productModel->find($pid);
            if (!$product)
                continue;

            if ($product['stock'] < $qty) {
                $db->transRollback();
                return redirect()->back()->with('error', "Stok {$product['name']} tidak cukup! (tersisa {$product['stock']})");
            }

            $unit = (float) $product['price'];
            $subtotal = $unit * $qty;

            $this->detailModel->insert([
                'transaction_id' => $txId,
                'product_id' => $pid,
                'quantity' => $qty,
                'unit_price' => $unit,
                'subtotal' => $subtotal,
            ]);

            $this->productModel->decreaseStock((int) $pid, $qty);
        }

        // Recalculate and complete
        $this->model->recalculateTotal($txId);
        $this->model->update($txId, ['status' => 'COMPLETED']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Transaksi gagal disimpan, coba lagi.');
        }

        return redirect()->to('/transactions/detail/' . $txId)->with('success', 'Transaksi berhasil disimpan!');
    }

    /** Show transaction detail */
    public function detail(string $id)
    {
        $transaction = $this->model->getWithCustomer($id);
        if (!$transaction) {
            return $this->withError('/transactions', 'Transaksi tidak ditemukan.');
        }

        return view('transactions/detail', [
            'title' => 'Detail Transaksi',
            'transaction' => $transaction,
            'details' => $this->detailModel->getByTransaction($id),
        ]);
    }

    /** Cancel a PENDING transaction and restore stock */
    public function cancel(string $id)
    {
        $transaction = $this->model->find($id);
        if (!$transaction || $transaction['status'] !== 'PENDING') {
            return $this->withError('/transactions', 'Transaksi tidak bisa dibatalkan.');
        }

        $details = $this->detailModel->getByTransaction($id);
        foreach ($details as $d) {
            $this->productModel->db->table('products')
                ->where('id', $d['product_id'])
                ->set('stock', "stock + {$d['quantity']}", false)
                ->update();
        }

        $this->model->update($id, ['status' => 'CANCELLED']);
        return $this->withSuccess('/transactions', 'Transaksi dibatalkan dan stok dikembalikan.');
    }
}
