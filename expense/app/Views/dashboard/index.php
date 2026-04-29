<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$categoryColors = [
    'Food'          => ['bg' => '#dcfce7', 'text' => '#166534'],
    'Transport'     => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'Entertainment' => ['bg' => '#ede9fe', 'text' => '#5b21b6'],
    'Utilities'     => ['bg' => '#fef9c3', 'text' => '#854d0e'],
    'Healthcare'    => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    'Shopping'      => ['bg' => '#fce7f3', 'text' => '#9d174d'],
    'Education'     => ['bg' => '#e0f2fe', 'text' => '#0c4a6e'],
    'Travel'        => ['bg' => '#d1fae5', 'text' => '#065f46'],
    'Rent'          => ['bg' => '#f1f5f9', 'text' => '#334155'],
    'Other'         => ['bg' => '#f5f3ff', 'text' => '#4c1d95'],
];

$paymentIcons = [
    'cash'          => ['icon' => 'bi-cash-stack',       'label' => 'Cash'],
    'card'          => ['icon' => 'bi-credit-card',      'label' => 'Card'],
    'bank_transfer' => ['icon' => 'bi-bank',             'label' => 'Bank Transfer'],
    'other'         => ['icon' => 'bi-three-dots-circle','label' => 'Other'],
];

$activeFilters = array_filter($filters);
$isFiltered    = !empty($activeFilters);

$currentPage  = $pager->getCurrentPage();
$perPage      = 10;
$offset       = ($currentPage - 1) * $perPage;
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e3a5f">Expense Dashboard</h4>
        <p class="text-muted mb-0 small">Track and manage all your expenses</p>
    </div>
    <?php if ($isFiltered): ?>
        <a href="<?= base_url('/') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </a>
    <?php endif ?>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="icon-wrap" style="background:#e0f2fe">
                    <i class="bi bi-receipt" style="color:#0369a1"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Total Records</div>
                    <div class="fw-bold fs-4" style="color:#1e3a5f"><?= number_format($totalCount) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="icon-wrap" style="background:#fef9c3">
                    <i class="bi bi-currency-dollar" style="color:#a16207"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Total Amount</div>
                    <div class="fw-bold fs-5" style="color:#1e3a5f">PKR <?= number_format($totalAmount, 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="icon-wrap" style="background:#dcfce7">
                    <i class="bi bi-filter-circle" style="color:#166534"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Filter Status</div>
                    <div class="fw-bold" style="color:#1e3a5f">
                        <?= $isFiltered ? '<span class="active-filter-badge">Active</span>' : '<span class="text-muted small">None</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="icon-wrap" style="background:#ede9fe">
                    <i class="bi bi-layers" style="color:#5b21b6"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Current Page</div>
                    <div class="fw-bold" style="color:#1e3a5f"><?= $currentPage ?> of <?= $pager->getPageCount() ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card filter-card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-funnel-fill" style="color:#f5a623"></i>
        Filter Expenses
        <?php if ($isFiltered): ?>
            <span class="ms-auto active-filter-badge"><?= count($activeFilters) ?> active</span>
        <?php endif ?>
    </div>
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('/') ?>">
            <div class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Title</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="e.g. Grocery…"
                            value="<?= esc($filters['search']) ?>"
                        >
                    </div>
                </div>

                <!-- Category -->
                <div class="col-6 col-md-2 col-xl-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach (['Food','Transport','Entertainment','Utilities','Healthcare','Shopping','Education','Travel','Rent','Other'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Payment Method -->
                <div class="col-6 col-md-2 col-xl-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Payment</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="cash"          <?= $filters['payment_method'] === 'cash'          ? 'selected' : '' ?>>Cash</option>
                        <option value="card"          <?= $filters['payment_method'] === 'card'          ? 'selected' : '' ?>>Card</option>
                        <option value="bank_transfer" <?= $filters['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="other"         <?= $filters['payment_method'] === 'other'         ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-6 col-md-2 col-xl-2">
                    <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                    <input
                        type="date"
                        name="date_from"
                        class="form-control form-control-sm"
                        value="<?= esc($filters['date_from']) ?>"
                    >
                </div>

                <!-- Date To -->
                <div class="col-6 col-md-2 col-xl-2">
                    <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                    <input
                        type="date"
                        name="date_to"
                        class="form-control form-control-sm"
                        value="<?= esc($filters['date_to']) ?>"
                    >
                </div>

                <!-- Buttons -->
                <div class="col-12 col-xl-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-navy flex-fill">
                        <i class="bi bi-search me-1"></i>Apply
                    </button>
                    <a href="<?= base_url('/') ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Expense Table -->
<div class="card table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-2" style="color:#f5a623"></i>Expense Records</span>
        <span class="badge" style="background:rgba(255,255,255,.15);font-size:.75rem">
            Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalCount) ?> of <?= number_format($totalCount) ?>
        </span>
    </div>

    <?php if (empty($expenses)): ?>
        <div class="no-results">
            <i class="bi bi-inbox"></i>
            <div class="fw-semibold mb-1">No expenses found</div>
            <div class="small">Try adjusting your filters</div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th class="text-end">Amount (PKR)</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $i => $expense): ?>
                        <?php
                            $cat   = $expense['category'] ?? 'Other';
                            $color = $categoryColors[$cat] ?? ['bg' => '#f1f5f9', 'text' => '#334155'];
                            $pm    = $paymentIcons[$expense['payment_method']] ?? $paymentIcons['other'];
                        ?>
                        <tr>
                            <td class="row-num"><?= $offset + $i + 1 ?></td>
                            <td class="fw-semibold" style="color:#1e3a5f"><?= esc($expense['title']) ?></td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1" style="background:<?= $color['bg'] ?>;color:<?= $color['text'] ?>;font-size:.75rem;font-weight:600">
                                    <?= esc($cat) ?>
                                </span>
                            </td>
                            <td class="text-end amount-text">
                                <?= number_format((float) $expense['amount'], 0) ?>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="bi <?= $pm['icon'] ?> me-1"></i><?= $pm['label'] ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('d M Y', strtotime($expense['expense_date'])) ?>
                            </td>
                            <td class="text-muted small" style="max-width:180px">
                                <?php if (!empty($expense['description'])): ?>
                                    <span title="<?= esc($expense['description']) ?>">
                                        <?= esc(mb_strimwidth($expense['description'], 0, 30, '…')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic" style="opacity:.5">—</span>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pager->getPageCount() > 1): ?>
            <div class="card-footer bg-white border-top py-3 d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    Page <strong><?= $currentPage ?></strong> of <strong><?= $pager->getPageCount() ?></strong>
                </div>
                <nav>
                    <?= $pager->only(array_keys($filters))->links('default', 'expense_full') ?>
                </nav>
            </div>
        <?php endif ?>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
