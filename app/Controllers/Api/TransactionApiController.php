<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;

class TransactionApiController extends BaseController
{
    private TransactionModel       $model;
    private TransactionDetailModel $detailModel;
    private ProductModel           $productModel;
    private CustomerModel          $customerModel;

    public function __construct()
    {
        $this->model         = new TransactionModel();
        $this->detailModel   = new TransactionDetailModel();
        $this->productModel  = new ProductModel();
        $this->customerModel = new CustomerModel();
    }

    /**
     * GET /api/transactions
     * Query: ?limit=30&user_id=...
     */
    public function index()
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 30);
        $userId = (int) ($this->request->getGet('user_id') ?? 0);

        // Read user_id from JWT payload if not supplied in query string
        $payload = $this->request->jwtPayload ?? null;
        if ($userId === 0 && $payload) {
            $userId = (int) ($payload->user_id ?? 0);
        }

        $builder = $this->model->db->table('transactions t')
            ->select('t.*, c.name AS customer_name, u.username AS kasir_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->orderBy('t.transaction_date', 'DESC')
            ->limit($limit);

        // Cashiers only see their own transactions
        if ($userId > 0 && isset($payload->role) && $payload->role !== 'admin') {
            $builder->where('t.user_id', $userId);
        }

        $transactions = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $transactions,
        ]);
    }

    /**
     * GET /api/transactions/{id}
     */
    public function show(string $id)
    {
        $transaction = $this->model->db->table('transactions t')
            ->select('t.*, c.name AS customer_name, c.phone AS customer_phone, u.username AS kasir_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$transaction) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Transaction not found.']);
        }

        $details = $this->detailModel->getByTransaction($id);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => array_merge($transaction, ['items' => $details]),
        ]);
    }

    /**
     * POST /api/transactions
     * Body JSON:
     * {
     *   "items": [{"product_id": 1, "quantity": 2}],
     *   "total_amount": 6000,
     *   "bayar": 10000,
     *   "payment_method": "cash",
     *   "customer_id": null,
     *   "notes": ""
     * }
     */
    public function store()
    {
        $body = $this->request->getJSON(true);

        $items         = $body['items'] ?? [];
        $paymentMethod = $body['payment_method'] ?? 'cash';
        $customerId    = $body['customer_id'] ?? null;
        $notes         = $body['notes'] ?? '';
        $bayar         = (float) ($body['bayar'] ?? 0);

        if (empty($items)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'Please select at least one product.']);
        }

        // Read user_id from JWT token
        $payload = $this->request->jwtPayload;
        $userId  = (int) ($payload->user_id ?? 0);

        helper('uuid');
        $db = \Config\Database::connect();
        $db->transStart();

        $txId = generate_uuid();

        $this->model->insert([
            'id'               => $txId,
            'transaction_date' => date('Y-m-d H:i:s'),
            'customer_id'      => $customerId ?: null,
            'user_id'          => $userId,
            'status'           => 'PENDING',
            'payment_method'   => $paymentMethod,
            'bayar'            => $bayar,
            'notes'            => $notes,
            'total_amount'     => 0,
        ]);

        // Process each item
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty       = (int) ($item['quantity'] ?? 1);

            if ($productId < 1 || $qty < 1) continue;

            $product = $this->productModel->find($productId);
            if (!$product) continue;

            if ($product['stock'] < $qty) {
                $db->transRollback();
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => "Insufficient stock for {$product['name']}! (only {$product['stock']} left)",
                    ]);
            }

            $unitPrice = (float) $product['price'];
            $subtotal  = $unitPrice * $qty;

            $this->detailModel->insert([
                'transaction_id' => $txId,
                'product_id'     => $productId,
                'quantity'       => $qty,
                'unit_price'     => $unitPrice,
                'subtotal'       => $subtotal,
            ]);

            $this->productModel->decreaseStock($productId, $qty);
        }

        // Recalculate total
        $this->model->recalculateTotal($txId);
        $this->model->update($txId, ['status' => 'COMPLETED']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => 'Transaction could not be saved. Please try again.']);
        }

        // Fetch the newly created transaction
        $newTransaction = $this->model->find($txId);
        $change         = $bayar - (float) $newTransaction['total_amount'];

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Transaction saved successfully.',
                'data'    => [
                    'id'           => $txId,
                    'total_amount' => $newTransaction['total_amount'],
                    'amount_paid'  => $bayar,
                    'change'       => $change,
                    'status'       => 'COMPLETED',
                ],
            ]);
    }
}
