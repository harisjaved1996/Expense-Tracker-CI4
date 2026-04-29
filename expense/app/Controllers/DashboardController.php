<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ExpenseModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $model = new ExpenseModel();

        $filters = [
            'category'       => $this->request->getGet('category') ?? '',
            'payment_method' => $this->request->getGet('payment_method') ?? '',
            'date_from'      => $this->request->getGet('date_from') ?? '',
            'date_to'        => $this->request->getGet('date_to') ?? '',
            'search'         => $this->request->getGet('search') ?? '',
        ];

        $expenses    = $model->applyFilters($filters)->orderBy('expense_date', 'DESC')->paginate(10);
        $pager       = $model->pager;
        $totalAmount = $model->getTotalAmount($filters);
        $totalCount  = $model->getTotalCount($filters);

        return view('dashboard/index', [
            'expenses'    => $expenses,
            'pager'       => $pager,
            'filters'     => $filters,
            'totalAmount' => $totalAmount,
            'totalCount'  => $totalCount,
        ]);
    }
}
