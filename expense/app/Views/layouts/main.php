<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Expense Tracker') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy: #1e3a5f;
            --navy-dark: #162d4a;
            --gold: #f5a623;
            --gold-light: #fdb94a;
        }

        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .navbar-brand-text {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }

        .navbar-brand-text span {
            color: var(--gold);
        }

        .nav-top {
            background: var(--navy);
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
        }

        .stat-card .icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .filter-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
        }

        .filter-card .card-header {
            background: var(--navy);
            color: #fff;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            font-size: .9rem;
            padding: .75rem 1.25rem;
        }

        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            overflow: hidden;
        }

        .table-card .card-header {
            background: var(--navy);
            color: #fff;
            font-weight: 600;
            font-size: .95rem;
            padding: .9rem 1.25rem;
        }

        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #e2e8f0;
            padding: .75rem 1rem;
        }

        .table tbody td {
            padding: .75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: .9rem;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .amount-text {
            font-weight: 600;
            color: #1e3a5f;
        }

        .btn-navy {
            background: var(--navy);
            color: #fff;
            border: none;
        }

        .btn-navy:hover {
            background: var(--navy-dark);
            color: #fff;
        }

        .btn-gold {
            background: var(--gold);
            color: #fff;
            border: none;
            font-weight: 600;
        }

        .btn-gold:hover {
            background: var(--gold-light);
            color: #fff;
        }

        .active-filter-badge {
            background: rgba(245,166,35,.15);
            color: #b37a10;
            border: 1px solid rgba(245,166,35,.4);
            font-size: .75rem;
            padding: .25rem .6rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .pagination .page-link {
            color: var(--navy);
            border-radius: 6px !important;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            min-width: 36px;
            text-align: center;
        }

        .pagination .page-item.active .page-link {
            background: var(--navy);
            border-color: var(--navy);
            color: var(--gold);
            font-weight: 700;
        }

        .pagination .page-item.disabled .page-link {
            color: #adb5bd;
        }

        .pagination .page-link:hover:not(.active) {
            background: #e8eef5;
            color: var(--navy);
        }

        .no-results {
            padding: 3rem;
            text-align: center;
            color: #94a3b8;
        }

        .no-results i {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .row-num {
            color: #94a3b8;
            font-size: .8rem;
        }
    </style>
</head>
<body>

<nav class="navbar nav-top py-2 mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand navbar-brand-text text-white" href="<?= base_url('/') ?>">
            <i class="bi bi-wallet2 me-2" style="color:var(--gold)"></i>Spend<span>Wise</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
        </div>
    </div>
</nav>

<main class="container-fluid px-4 pb-5">
    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
