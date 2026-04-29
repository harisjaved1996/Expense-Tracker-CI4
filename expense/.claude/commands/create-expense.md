---
description: Create multiple mock expense records in the expense-tracker database
argument-hint: "<count>"
allowed-tools: [bash]
---

# create-expense

Create multiple mock expense records in the `expense` table of the MySQL `expense-tracker` database for development and testing purposes.

## Usage

```
/create-expense <count>
```

## Parameters

- `count` (integer): Number of expense records to create (e.g., 5, 10, 20)

## Examples

```
/create-expense 5
/create-expense 10
/create-expense 20
```

---

## Step 1 — Parse Arguments

Extract and validate the `count` parameter from user input.

- Validate that count is a positive integer
- If invalid or missing, display usage error: `Please provide a positive integer (e.g., /create-expense 5)`
- Exit if validation fails

---

## Step 2 — Generate Expense Data

Generate `count` varied, realistic expense rows using the pools below. Rotate through all options so consecutive rows are never identical.

### Categories (rotate through all 10):
Food, Transport, Entertainment, Utilities, Healthcare, Shopping, Education, Travel, Rent, Other

### Payment Methods (rotate through all 4):
cash, card, bank_transfer, other

### Titles per category:
- **Food**: Grocery Shopping, Restaurant Dinner, Coffee & Snacks, Food Delivery, Bakery Items, Lunch at Cafe
- **Transport**: Uber Ride, Petrol Refill, Bus Ticket, Car Maintenance, Parking Fee, Bike Repair
- **Entertainment**: Netflix Subscription, Movie Tickets, Concert Tickets, Gaming Purchase, Book Purchase, Streaming Service
- **Utilities**: Electricity Bill, Gas Bill, Internet Bill, Water Bill, Phone Bill, Cable TV
- **Healthcare**: Doctor Visit, Medicine Purchase, Lab Tests, Dental Checkup, Eye Checkup, Health Insurance
- **Shopping**: Clothing Purchase, Shoe Shopping, Home Decor, Electronics, Online Shopping, Stationery
- **Education**: Course Fee, Books Purchase, Workshop Fee, Online Course, Tutoring Session, University Fee
- **Travel**: Hotel Booking, Flight Ticket, Travel Insurance, Visa Fee, Tour Package, Taxi to Airport
- **Rent**: Monthly Rent, House Maintenance, Furniture Repair, Cleaning Service, Security Deposit, Utility Setup
- **Other**: ATM Withdrawal, Bank Charges, Miscellaneous Expense, Gift Purchase, Charity Donation, Subscription Fee

### Amounts in PKR (vary per category):
- Food: 200–3000
- Transport: 100–5000
- Entertainment: 500–8000
- Utilities: 1000–15000
- Healthcare: 500–20000
- Shopping: 1000–25000
- Education: 2000–50000
- Travel: 5000–80000
- Rent: 10000–60000
- Other: 100–10000

### Descriptions:
- Set `NULL` for roughly 30% of rows
- Others: "Monthly payment", "Paid via app", "Shared with family", "Work related", "Urgent purchase", "Regular expense", "One-time payment", "Weekly expense", "Paid in installments"

### Dates:
- `expense_date`: Random date within the last 6 months from today. Distribute evenly across all 6 months — do not cluster dates
- `created_at`: Current datetime
- `updated_at`: Current datetime
- `deleted_at`: Always `NULL`

---

## Step 3 — Write Temporary Seeder

Write a temporary CI4 seeder file at `app/Database/Seeds/TempExpenseSeeder.php` with all generated rows using `$this->db->table('expense')->insertBatch($rows)`.

- Use `declare(strict_types=1);` at the top
- Namespace: `App\Database\Seeds`
- Extend `CodeIgniter\Database\Seeder`
- All data must be hardcoded in the `$rows` array (no random generation inside the seeder)

---

## Step 4 — Run Seeder

```bash
php spark db:seed TempExpenseSeeder
```

- Run from the project root
- Continue only if the seeder exits successfully
- If it fails, show the error and stop

---

## Step 5 — Cleanup

Delete the temporary seeder file `app/Database/Seeds/TempExpenseSeeder.php` after successful insertion.

---

## Step 6 — Confirm

Display confirmation with a summary:

1. **Status line**: `✅ Created: {count} expense records`

2. **Summary table** of all inserted records:

```
| # | Title | Category | Amount (PKR) | Payment Method | Expense Date |
|---|-------|----------|-------------|----------------|--------------|
| 1 | ...   | ...      | ...         | ...            | ...          |
```

3. **Output format per row**:
```
✅ Created: Grocery Shopping | Food | PKR 1,500 | cash | 2026-02-14
✅ Created: Electricity Bill | Utilities | PKR 4,200 | bank_transfer | 2026-01-05
...

Summary: {count} records inserted into `expense` table.
```

---

## Notes

- All expense records are mock data intended for development and testing only
- Amounts are in PKR (Pakistani Rupees)
- Dates are spread across the last 6 months so charts and filters have meaningful data
- The temporary seeder file is always deleted after use — it is never committed
- `deleted_at` is always `NULL` (soft-delete column, records are active)
