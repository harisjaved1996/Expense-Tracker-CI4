# Expense Tracker — Claude Project Context

## Project Overview
**Expense Tracker** is a personal expense tracking web application.

- **Framework**: CodeIgniter 4 (CI4) — PHP 8.2+
- **Language**: PHP 8.2 with strict types
- **Database**: MySQL via CI4's MySQLi driver, database `expense-tracker`
- **Frontend**: Bootstrap 5 (CDN), plain CSS, vanilla JS — no npm, no build pipeline
- **Auth**: Not yet configured (planned: CI4 session-based auth)

---

## Architecture

```
expense/
├── app/
│   ├── Controllers/       # CI4 controllers (one per feature domain)
│   ├── Models/            # CI4 models extending Model base class
│   ├── Views/             # PHP view files, organized by feature
│   │   └── layouts/       # Shared layout templates
│   ├── Database/
│   │   ├── Migrations/    # CI4 migration files (never edit after applying)
│   │   └── Seeds/         # Database seeders
│   └── Config/            # App config files (Database.php, Routes.php, etc.)
├── public/                # Web root — index.php lives here
├── writable/              # Logs, cache, uploads (never commit contents)
└── .env                   # Local environment overrides (never commit)
```

---

## Tech Stack Details

### Backend
- CodeIgniter 4 MVC — controllers in `app/Controllers/`, models in `app/Models/`
- CI4 Query Builder for all DB access — no raw SQL except in migrations
- Database config loaded from `.env` → `app/Config/Database.php`
- MySQL on `localhost:3306`, database `expense-tracker`, user `root`
- Migrations run via `php spark migrate`

### Frontend
- Bootstrap 5.3 CDN — utility classes preferred, custom CSS only when Bootstrap can't do it
- Custom styles go in `public/assets/css/`
- Custom scripts go in `public/assets/js/`
- No TypeScript, no Webpack, no npm — keep it that way unless explicitly asked

---

## Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Namespace | Match folder path | `App\Controllers` |
| Controllers | PascalCase + `Controller` suffix | `ExpenseController` |
| Models | PascalCase + `Model` suffix | `ExpenseModel` |
| Methods (actions) | camelCase | `index`, `create`, `store` |
| DB columns | snake_case | `expense_date`, `payment_method` |
| Migration files | `YYYY-MM-DD-HHMMSS_DescriptiveName` | `2026-04-29-000001_CreateExpenseTable` |
| View files | snake_case | `expense_list.php`, `create_expense.php` |
| Partial views | Underscore prefix | `_expense_row.php` |

---

## Development Workflow

### Running the app
```bash
php spark serve
# http://localhost:8080
```

### Migrations
```bash
php spark migrate              # run all pending migrations
php spark migrate:rollback     # rollback last batch
php spark make:migration Name  # create a new migration
```

Never edit a migration file after it has been applied to any database.

### Seeders
```bash
php spark db:seed SeederName   # run a specific seeder
```

---

## Code Style

- Always declare `declare(strict_types=1);` at the top of PHP files
- Keep controllers thin — DB logic belongs in the Model
- Use CI4's Query Builder (`$this->db->table(...)`) not raw SQL in application code
- Validate user input at the controller boundary via CI4's Validation library
- Use `$model->save()`, `$model->insert()`, `$model->find()` — CI4 Model API, not direct DB calls
- No unnecessary null checks on values CI4 guarantees loaded

---

## What to Avoid

- Do not use raw `mysqli_*` or PDO directly — use CI4's DB layer
- Do not add npm, webpack, or any JS build tooling unless asked
- Do not edit migration files after they have been applied
- Do not commit `.env` or `writable/` contents
- Do not create `.md` documentation files unless explicitly requested
- Do not add comments that describe *what* the code does — only when the *why* is non-obvious
