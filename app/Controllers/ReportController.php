<?php

namespace App\Controllers;

use App\Models\TransactionModel;

class ReportController extends BaseController
{
    private TransactionModel $model;

    public function __construct()
    {
        $this->model = new TransactionModel();
    }

    public function index()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $rows = $this->model->db->table('transactions t')
            ->select('t.*, c.name AS customer_name')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->where('t.status', 'COMPLETED')
            ->where('DATE(t.transaction_date) >=', $startDate)
            ->where('DATE(t.transaction_date) <=', $endDate)
            ->orderBy('t.transaction_date', 'ASC')
            ->get()->getResultArray();

        $totalRevenue = array_sum(array_column($rows, 'total_amount'));

        // Group by date
        $byDate = [];
        foreach ($rows as $r) {
            $day = date('Y-m-d', strtotime($r['transaction_date']));
            $byDate[$day] = ($byDate[$day] ?? 0) + $r['total_amount'];
        }

        // Top products in range
        $topProducts = $this->model->db->table('transaction_details td')
            ->select('p.name AS product_name, p.product_type, SUM(td.quantity) AS total_qty, SUM(td.subtotal) AS total_revenue')
            ->join('products p', 'p.id = td.product_id')
            ->join('transactions t', 't.id = td.transaction_id')
            ->where('t.status', 'COMPLETED')
            ->where('DATE(t.transaction_date) >=', $startDate)
            ->where('DATE(t.transaction_date) <=', $endDate)
            ->groupBy('p.id')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('reports/index', [
            'title' => 'Laporan Penjualan',
            'rows' => $rows,
            'totalRevenue' => $totalRevenue,
            'byDate' => $byDate,
            'topProducts' => $topProducts,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
