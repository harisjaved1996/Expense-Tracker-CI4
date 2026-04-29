Create $ARGUMENTS mock expense records in the `expense` table of the MySQL `expense-tracker` database for development/testing purposes.

## Steps

1. Validate that `$ARGUMENTS` is a positive integer. If it is not, stop and tell the user.

2. Generate `$ARGUMENTS` varied, realistic expense rows. Use the pools below — rotate through them so consecutive rows are never identical.

### Data Pools

**Categories (rotate through all):**
Food, Transport, Entertainment, Utilities, Healthcare, Shopping, Education, Travel, Rent, Other

**Payment Methods (rotate through all):**
cash, card, bank_transfer, other

**Sample titles per category:**
- Food: Grocery Shopping, Restaurant Dinner, Coffee & Snacks, Food Delivery, Bakery Items, Lunch at Cafe
- Transport: Uber Ride, Petrol Refill, Bus Ticket, Car Maintenance, Parking Fee, Bike Repair
- Entertainment: Netflix Subscription, Movie Tickets, Concert Tickets, Gaming Purchase, Book Purchase, Streaming Service
- Utilities: Electricity Bill, Gas Bill, Internet Bill, Water Bill, Phone Bill, Cable TV
- Healthcare: Doctor Visit, Medicine Purchase, Lab Tests, Dental Checkup, Eye Checkup, Health Insurance
- Shopping: Clothing Purchase, Shoe Shopping, Home Decor, Electronics, Online Shopping, Stationery
- Education: Course Fee, Books Purchase, Workshop Fee, Online Course, Tutoring Session, University Fee
- Travel: Hotel Booking, Flight Ticket, Travel Insurance, Visa Fee, Tour Package, Taxi to Airport
- Rent: Monthly Rent, House Maintenance, Furniture Repair, Cleaning Service, Security Deposit, Utility Setup
- Other: ATM Withdrawal, Bank Charges, Miscellaneous Expense, Gift Purchase, Charity Donation, Subscription Fee

**Amounts (PKR range, vary per category):**
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

**Descriptions (some rows should be NULL, others short):**
- NULL for roughly 30% of rows
- Others: "Monthly payment", "Paid via app", "Shared with family", "Work related", "Urgent purchase", "Regular expense", "One-time payment", "Weekly expense", "Paid in installments"

**expense_date:** Random date within the last 6 months from today's date (2026-04-29). Distribute dates across all 6 months, not clustered.

**created_at / updated_at:** Current datetime `NOW()`.

**deleted_at:** Always `NULL`.

3. Write a temporary CI4 seeder file at `app/Database/Seeds/TempExpenseSeeder.php` that inserts all the rows using CI4's `$this->db->table('expense')->insertBatch($rows)`.

4. Run the seeder:
```bash
php spark db:seed TempExpenseSeeder
```

5. Delete the temporary seeder file after it runs successfully.

6. Show the user a summary table of the inserted expenses (title, category, amount, expense_date, payment_method) — all `$ARGUMENTS` rows.
