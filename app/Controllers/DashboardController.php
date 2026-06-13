<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\CustomerModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $transactionModel = new TransactionModel();
        $today = date('Y-m-d');
        $role = session()->get('role');

        // Penjualan hari ini (semua kasir)
        $todayTx = $transactionModel->db->table('transactions')
            ->where("DATE(transaction_date)", $today)
            ->where('status', 'COMPLETED')
            ->get()->getResultArray();

        $todaySales = array_sum(array_column($todayTx, 'total_amount'));
        $todayCount = count($todayTx);

        // Data tambahan — hanya untuk admin
        $lowStock = [];
        $recentTx = [];
        $pendingCount = 0;
        $totalProducts = 0;
        $totalCustomers = 0;

        if ($role === 'admin') {
            $productModel = new ProductModel();
            $customerModel = new CustomerModel();

            $totalProducts = $productModel->countAll();
            $totalCustomers = $customerModel->countAll();

            // Low stock products (stock <= 10)
            $lowStock = $productModel->where('stock <=', 10)->orderBy('stock', 'ASC')->findAll(5);

            // Recent transactions
            $recentTx = array_slice($transactionModel->withCustomer(), 0, 5);

            // Pending count
            $pendingCount = $transactionModel->where('status', 'PENDING')->countAllResults();
        }

        return view('dashboard/index', [
            'title' => 'Dashboard — Minimarket',
            'userRole' => $role,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'todaySales' => $todaySales,
            'todayCount' => $todayCount,
            'lowStock' => $lowStock,
            'recentTx' => $recentTx,
            'pendingCount' => $pendingCount,
        ]);
    }

    /** Halaman 403 — Akses Ditolak */
    public function forbidden()
    {
        return view('errors/403', [
            'title' => '403 — Akses Ditolak',
        ]);
    }
}
