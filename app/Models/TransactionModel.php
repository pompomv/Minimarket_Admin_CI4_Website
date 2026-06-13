<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false; // VARCHAR UUID PK
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'transaction_date',
        'customer_id',
        'user_id',
        'total_amount',
        'payment_method',
        'bayar',
        'status',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /** Get all transactions with customer name and kasir name */
    public function withCustomer(): array
    {
        return $this->db->table('transactions t')
            ->select('t.*, c.name AS customer_name, u.username AS kasir_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->orderBy('t.transaction_date', 'DESC')
            ->get()->getResultArray();
    }

    /** Get one transaction with customer info and kasir name */
    public function getWithCustomer(string $id): ?array
    {
        return $this->db->table('transactions t')
            ->select('t.*, c.name AS customer_name, c.phone AS customer_phone, u.username AS kasir_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();
    }

    /** Recalculate and update total_amount from transaction_details */
    public function recalculateTotal(string $txId): void
    {
        $total = $this->db->table('transaction_details')
            ->selectSum('subtotal')
            ->where('transaction_id', $txId)
            ->get()->getRowArray()['subtotal'] ?? 0;

        $this->update($txId, ['total_amount' => $total]);
    }
}
