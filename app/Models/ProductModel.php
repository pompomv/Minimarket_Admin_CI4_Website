<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'price',
        'stock',
        'description',
        'supplier_id',
        'expiry_date',
        'category',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'price' => 'required|decimal',
        'stock' => 'required|integer',
    ];

    /** Get products with supplier name joined */
    public function withSupplier(): array
    {
        return $this->db->table('products p')
            ->select('p.*, s.name AS supplier_name')
            ->join('suppliers s', 's.id = p.supplier_id', 'left')
            ->orderBy('p.id', 'DESC')
            ->get()->getResultArray();
    }

    /** Decrease stock after a sale */
    public function decreaseStock(int $productId, int $qty): bool
    {
        return $this->db->query(
            'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
            [$qty, $productId, $qty]
        );
    }
}
