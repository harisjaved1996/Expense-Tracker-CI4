---
description: Create multiple user records in the spendwise database with Islamic names
argument-hint: "<count>"
allowed-tools: [bash, sqlcmd]
---

# create-users

Create multiple user records in the spendwise database with Islamic names.

## Usage

```
/create-users <count>
```

## Parameters

- `count` (integer): Number of users to create (e.g., 2, 4, 5, 6)
- Maximum: 30 users (available Islamic names in the pool)

## Examples

```
/create-users 2
/create-users 5
/create-users 10
```

---

## Step 1 — Parse Arguments

Extract and validate the `count` parameter from user input.

- If no count provided, default to 1
- Validate that count is a positive integer
- If invalid, display usage error: `Please provide a positive integer (e.g., /create-users 5)`
- Exit if validation fails

## Step 2 — Verify Available Names

Check if requested count does not exceed available Islamic names in the pool.

- Available names: 30 unique Islamic names (male and female)
- If `count > 30`, display error: `Only 30 unique names available. Requested: {count}`
- Exit if count exceeds available names

## Step 3 — Generate and Insert Users

For each user (1 to count):

1. **Select Islamic Name**: take any random islamic names

2. **Generate Email**: `{name}@gmail.com`

3. **Hash Password**: 
   - Password: "1234"
   - Method: PBKDF2-SHA256
   - Salt: [123, 45, 67, 89, 101, 112, 123, 145, 156, 178, 189, 201, 212, 223, 234, 245]
   - Output: Base64 encoded hash

4. **Set User Type**: "user" (default)

5. **Insert into Database**:
   ```sql
   INSERT INTO Users (Email, Name, PasswordHash, UserType, CreatedAt)
   VALUES ('{email}', '{name}', '{passwordHash}', 'user', GETDATE())
   ```
   - Use parameterized queries to prevent SQL injection
   - Continue on success, log error on failure
   - Track created and failed counts

## Step 4 — Confirm

Display confirmation with summary:

1. **Show Summary**:
   - Total created count
   - Total failed count
   - Status message

2. **Display Created Users**:
   ```sql
   SELECT TOP 10 Id, Email, Name, UserType, CreatedAt 
   FROM Users 
   WHERE UserType = 'user' 
   ORDER BY Id DESC
   ```

3. **Output Format**:
   ```
   ✅ Created: {name} ({email})
   ❌ Failed: {name} ({email})
   
   Summary: {created} created, {failed} failed
   ```

---

## Notes

- Each user has a unique Islamic name and corresponding email
- All passwords are securely hashed and identical ("1234" hashed)
- All users default to "user" type (not admin)
- CreatedAt timestamp is set to current server time
- Maximum 30 users can be created in one command (limited by available names)
