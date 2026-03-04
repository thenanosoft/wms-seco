# Migration – Decimal Support Status

## Current state: **NOT SUPPORTED**

Abhi migrations mein **prices** aur **quantities** dono **integer** (unsignedInteger / unsignedBigInteger) hain.  
4 decimal prices aur 2–4 decimal quantities ke liye DB columns decimal nahi hain.

---

### Tables & columns (current type)

| Table | Price/Value columns | Quantity columns |
|------|---------------------|------------------|
| **purchase_lines** | `purchase_price` → unsignedInteger, `line_total` → unsignedBigInteger | `quantity` → unsignedInteger |
| **issue_lines** | `issue_price` → unsignedInteger, `line_total` → unsignedBigInteger | `quantity` → unsignedInteger |
| **stock_batches** | `unit_price` → unsignedInteger (nullable) | `qty_purchased`, `qty_available` → unsignedInteger |
| **stock_ledger** | `unit_price` → unsignedInteger | `qty_in`, `qty_out` → unsignedInteger |
| **issue_return_lines** | `issue_price` → unsignedInteger, `line_total` → unsignedBigInteger | `quantity` → unsignedInteger |
| **purchase_return_lines** | `purchase_price` → unsignedInteger, `line_total` → unsignedBigInteger | `quantity` → unsignedInteger |
| **return_lines** | `unit_price` → unsignedInteger, `line_total` → unsignedBigInteger | `quantity` → unsignedInteger |

---

### Decimal support ke liye kya chahiye

- **Prices / values:** `DECIMAL(16,4)` (ya `DOUBLE`) – 4 decimal places.
- **Quantities:** `DECIMAL(16,4)` (ya 2–4 decimals) – 2–4 decimal places.

App code ab prices aur quantities dono ko 4 decimals handle karta hai (validation, round, display).  
Jab tak in columns ko migration se `DECIMAL` (ya compatible type) mein change nahi karte, DB mein decimals persist nahi honge; integer columns values truncate kar denge.

---

### Summary

- **Supported hai?** → **Nahi.** Sab relevant columns abhi integer types hain.
- **4 decimal prices / 2–4 decimal quantities persist karne ke liye:**  
  Nayi migration chahiye jo in columns ko `decimal(16,4)` (ya apne hisaab se 2–4 scale) mein alter kare.
