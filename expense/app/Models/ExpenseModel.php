<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table      = 'expense';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'title', 'amount', 'category', 'payment_method',
        'description', 'expense_date',
    ];

    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    public function applyFilters(array $filters): static
    {
        if (!empty($filters['category'])) {
            $this->where('category', $filters['category']);
        }
        if (!empty($filters['payment_method'])) {
            $this->where('payment_method', $filters['payment_method']);
        }
        if (!empty($filters['date_from'])) {
            $this->where('expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->where('expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->like('title', $filters['search']);
        }

        return $this;
    }

    public function getTotalAmount(array $filters): float
    {
        $builder = $this->db->table($this->table)
            ->selectSum('amount')
            ->where('deleted_at IS NULL');

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }
        if (!empty($filters['payment_method'])) {
            $builder->where('payment_method', $filters['payment_method']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $builder->like('title', $filters['search']);
        }

        return (float) ($builder->get()->getRow()->amount ?? 0);
    }

    public function getTotalCount(array $filters): int
    {
        $builder = $this->db->table($this->table)
            ->where('deleted_at IS NULL');

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }
        if (!empty($filters['payment_method'])) {
            $builder->where('payment_method', $filters['payment_method']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $builder->like('title', $filters['search']);
        }

        return $builder->countAllResults();
    }

    public function getGroupedByCategory(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('category')
            ->selectSum('amount', 'total')
            ->where('deleted_at IS NULL');

        if (!empty($filters['date_from'])) {
            $builder->where('expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['payment_method'])) {
            $builder->where('payment_method', $filters['payment_method']);
        }

        return $builder->groupBy('category')->orderBy('total', 'DESC')->get()->getResultArray();
    }

    public function getMonthlyTotals(int $year): array
    {
        return $this->db->table($this->table)
            ->select('MONTH(expense_date) as month')
            ->selectSum('amount', 'total')
            ->where('YEAR(expense_date)', $year)
            ->where('deleted_at IS NULL')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();
    }
}
