<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table = 'transaction_details';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    protected $useTimestamps = false;

    /** Get all items for a transaction with product name */
    public function getByTransaction(string $txId): array
    {
        return $this->db->table('transaction_details td')
            ->select('td.*, p.name AS product_name, p.product_type')
            ->join('products p', 'p.id = td.product_id', 'left')
            ->where('td.transaction_id', $txId)
            ->get()->getResultArray();
    }
}
