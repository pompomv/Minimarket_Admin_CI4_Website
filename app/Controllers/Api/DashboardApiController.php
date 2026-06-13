<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DashboardApiController extends BaseController
{
    /**
     * GET /api/dashboard
     * Dashboard statistics for cashier or admin.
     * Cashiers see only their own data; admins see all.
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $payload = $this->request->jwtPayload;
        $userId  = (int) ($payload->user_id ?? 0);
        $role    = strtolower($payload->role ?? 'cashier');

        // Today's range
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');

        $builderToday = $db->table('transactions')
            ->where('status', 'COMPLETED')
            ->where('transaction_date >=', $todayStart)
            ->where('transaction_date <=', $todayEnd);

        if ($role !== 'admin') {
            $builderToday->where('user_id', $userId);
        }

        $todayData  = $builderToday->selectSum('total_amount')->selectCount('id', 'total_count')->get()->getRowArray();
        $todaySales = (float) ($todayData['total_amount'] ?? 0);
        $todayCount = (int) ($todayData['total_count'] ?? 0);

        // Total products in stock
        $totalProducts = (int) $db->table('products')->where('stock >', 0)->countAllResults();

        // Low stock products (stock <= 5)
        $lowStock = (int) $db->table('products')->where('stock <=', 5)->where('stock >', 0)->countAllResults();

        // Total customers
        $totalCustomers = (int) $db->table('customers')->countAllResults();

        // This month's sales
        $monthStart   = date('Y-m-01 00:00:00');
        $builderMonth = $db->table('transactions')
            ->where('status', 'COMPLETED')
            ->where('transaction_date >=', $monthStart);

        if ($role !== 'admin') {
            $builderMonth->where('user_id', $userId);
        }

        $monthData  = $builderMonth->selectSum('total_amount')->get()->getRowArray();
        $monthSales = (float) ($monthData['total_amount'] ?? 0);

        // Last 5 transactions
        $recentBuilder = $db->table('transactions t')
            ->select('t.id, t.transaction_date, t.total_amount, t.status, c.name AS customer_name, u.username AS cashier_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->where('t.status', 'COMPLETED')
            ->orderBy('t.transaction_date', 'DESC')
            ->limit(5);

        if ($role !== 'admin') {
            $recentBuilder->where('t.user_id', $userId);
        }

        $recentTransactions = $recentBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'sales_today'         => $todaySales,
                'transactions_today'  => $todayCount,
                'sales_this_month'    => $monthSales,
                'total_products'      => $totalProducts,
                'low_stock'           => $lowStock,
                'total_customers'     => $totalCustomers,
                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }
}
