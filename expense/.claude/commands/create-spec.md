---
description: Create a spec file for SpendWise
argument-hint: "Step number and feature name e.g. 02-registration"
allowed-tools: Read, Write
---

You are a senior .NET developer spinning up a new feature for the
SpendWise expense tracker. Always follow the rules in CLAUDE.md.

User input: $ARGUMENTS

## Step 1 — Parse the arguments
From $ARGUMENTS extract:

1. `step_number` — zero-padded to 2 digits: 2 → 02, 11 → 11

2. `feature_title` — human readable title in Title Case
   - Example: "User Registration" or "Expense Categories"

3. `feature_slug` — git and file-safe slug
   - Lowercase, kebab-case
   - Only a-z, 0-9 and -
   - Maximum 40 characters
   - Example: user-registration, expense-categories

If you cannot infer these from $ARGUMENTS, ask the user
to clarify before proceeding.

## Step 2 — Research the codebase
Read these files before writing the spec:
- `CLAUDE.md` — roadmap, conventions, tech stack
- `spendwise/Controllers/` — existing controllers and action signatures
- `spendwise/Models/` — existing domain entities and ViewModels
- `spendwise/Data/AppDbContext.cs` — existing DbContext, DbSets, and relationships
- `spendwise/Views/` — existing Razor view structure
- All files in `.claude/specs/` — avoid duplicating existing specs

Check `CLAUDE.md` to confirm the requested step is not already
marked complete. If it is, warn the user and stop.

## Step 3 — Write the spec
Generate a spec document with this exact structure:

---
# Spec: <feature_title>

## Overview
One paragraph describing what this feature does and why
it exists at this stage of the SpendWise roadmap.

## Depends on
Which previous steps this feature requires to be complete.

## Routes (Controller Actions)
Every new controller action needed:
- `ControllerName` → `ActionName` — HTTP verb — description — access level (anonymous/authenticated)

If no new actions: state "No new controller actions".

## Database changes
Any new EF Core entities, DbSet registrations, properties, or migration notes.
Always verify against `spendwise/Data/AppDbContext.cs` before writing this.
If none: state "No database changes".

## Models & ViewModels
- **New entities:** list new domain model classes with their path under `Models/`
- **New ViewModels:** list new ViewModel classes with their path under `Models/`
- **Modify:** list existing model files and what properties/annotations change

## Views
- **Create:** list new Razor views with their path under `Views/`
- **Modify:** list existing views and what changes

## Files to change
Every existing file that will be modified.

## Files to create
Every new file that will be created.

## New NuGet packages
Any new NuGet packages required. If none: state "No new packages".

## Rules for implementation
Specific constraints Claude must follow. Always include:
- Follow ASP.NET Core MVC conventions — no Razor Pages, no Web API controllers
- Use EF Core async APIs (`ToListAsync`, `FirstOrDefaultAsync`, etc.) for all DB calls
- Code-first migrations only — never edit generated migration files after applying them
- Validate at the controller boundary via `ModelState.IsValid`; trust EF internals below
- Keep controllers thin — move query logic to AppDbContext or a service if it exceeds ~20 lines
- File-scoped namespaces matching folder path (e.g. `namespace spendwise.Controllers;`)
- Bootstrap 5 utility classes preferred; custom CSS only when Bootstrap cannot do it
- All custom styles go in `wwwroot/css/site.css`; all custom scripts in `wwwroot/js/site.js`
- All views must extend `_Layout.cshtml`
- No npm, Webpack, TypeScript, or JS build tooling
- No XML doc comments on internal/private members

## Definition of done
A specific testable checklist. Each item must be
something that can be verified by running the app.
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
