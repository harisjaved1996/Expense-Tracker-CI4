---
description: Create a spec file for the Expense Tracker
argument-hint: "Step number and feature name e.g. 02-add-expense"
allowed-tools: Read, Write
---

You are a senior PHP developer spinning up a new feature for the
Expense Tracker — a CodeIgniter 4 personal finance app. Always follow the rules in CLAUDE.md.

User input: $ARGUMENTS

## Step 1 — Parse the arguments
From $ARGUMENTS extract:

1. `step_number` — zero-padded to 2 digits: 2 → 02, 11 → 11

2. `feature_title` — human readable title in Title Case
   - Example: "Add Expense" or "Expense Categories"

3. `feature_slug` — git and file-safe slug
   - Lowercase, kebab-case
   - Only a-z, 0-9 and -
   - Maximum 40 characters
   - Example: add-expense, expense-categories

If you cannot infer these from $ARGUMENTS, ask the user
to clarify before proceeding.

## Step 2 — Research the codebase
Read these files before writing the spec:
- `CLAUDE.md` — roadmap, conventions, tech stack
- `app/Controllers/` — existing controllers and method signatures
- `app/Models/` — existing CI4 models, `$allowedFields`, and query methods
- `app/Database/Migrations/` — existing migration files and table schemas
- `app/Views/` — existing view file structure
- `app/Config/Routes.php` — currently registered routes
- All files in `.claude/specs/` — avoid duplicating existing specs

Check `CLAUDE.md` to confirm the requested step is not already
marked complete. If it is, warn the user and stop.

## Step 3 — Write the spec
Generate a spec document with this exact structure:

---
# Spec: <feature_title>

## Overview
One paragraph describing what this feature does and why
it exists at this stage of the Expense Tracker roadmap.

## Depends on
Which previous steps this feature requires to be complete.

## Routes
Every new route needed (add to `app/Config/Routes.php`):
- HTTP verb + URI → `ControllerName::methodName` — description — access level (anonymous/authenticated)

If no new routes: state "No new routes".

## Database changes
Any new CI4 migrations, new tables, or column changes.
Always verify against existing migration files before writing this.
Migrations use `$this->forge` — never raw SQL.
If none: state "No database changes".

## Models
- **New models:** list new model classes with their path under `app/Models/`
- **Modify:** list existing model files and what `$allowedFields`, methods, or properties change

## Views
- **Create:** list new view files with their path under `app/Views/`
- **Modify:** list existing views and what changes
- All views must extend `app/Views/layouts/main.php` via CI4's `$this->extend()` / `$this->section()`

## Files to change
Every existing file that will be modified.

## Files to create
Every new file that will be created.

## New dependencies
Any new Composer packages required. If none: state "No new packages".

## Rules for implementation
Specific constraints Claude must follow when implementing this spec. Always include:
- Follow CodeIgniter 4 MVC conventions — controllers in `app/Controllers/`, models in `app/Models/`
- Declare `declare(strict_types=1);` at the top of every PHP file
- Use CI4 Query Builder via the Model API (`$model->save()`, `$model->find()`, etc.) — no raw SQL in application code
- Validate at the controller boundary using CI4's Validation library; trust CI4 internals below
- Keep controllers thin — move query logic into the Model if it exceeds ~20 lines
- Namespaces must match folder path: controllers → `App\Controllers`, models → `App\Models`
- Migrations use `$this->forge` only — never edit a migration after it has been applied
- Bootstrap 5 utility classes preferred; custom CSS only when Bootstrap cannot do it
- All custom styles go in `public/assets/css/`; all custom scripts in `public/assets/js/`
- All views must extend `app/Views/layouts/main.php`
- No npm, Webpack, TypeScript, or JS build tooling
- No comments describing what the code does — only when the why is non-obvious

## Definition of done
A specific testable checklist. Each item must be
something that can be verified by running the app (`php spark serve`).
---

## Step 4 — Save the spec
Save to: `.claude/specs/<step_number>-<feature_slug>.md`

## Step 5 — Report to the user
Print a short summary in this exact format:
```
Spec file: .claude/specs/<step_number>-<feature_slug>.md
Title:     <feature_title>
```

Then tell the user:
"Review the spec at `.claude/specs/<step_number>-<feature_slug>.md`
then enter Plan Mode with Shift+Tab twice to begin implementation."

Do not print the full spec in chat unless explicitly asked.
