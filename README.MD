

# Department

## Create Departmen

## Department Name:
- **Required:** Must not be empty.
- **Format:** Must contain only letters and spaces. (Validation using regex: `^[a-zA-Z\s]+$`)
- **Uniqueness:** Must not already exist in the database.

## Established Year:
- **Required:** Must not be empty.
- **Format:** Must be a numeric value and exactly 4 digits long.

## Dean:
- **Required:** Must not be empty.
- **Format:** Must contain only letters and spaces. (Validation using regex: `^[a-zA-Z\s]+$`)

## Contact Email:
- **Required:** Must not be empty.
- **Format:** Must be a valid email address (Validation using `filter_var` with `FILTER_VALIDATE_EMAIL`).

## Phone:
- **Required:** Must not be empty.
- **Format:** Must match either:
  - 11-digit mobile format (e.g., `+639123456789` or `09123456789`)
  - Landline format (e.g., `xxxx-xxxx`) (Validation using regex: `^(?:\+63[9]\d{9}|09\d{9}|(?:\+63|0)?[9]\d{10}|^\d{4}-\d{4})$`).

## Location:
- **Required:** Must not be empty.
- **Format:** Must contain only letters, spaces, and potentially other valid characters (considering the context).
