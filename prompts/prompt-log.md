I'm building a take-home assignment for a Laravel Developer role. Build a small
Laravel API application: "Store Order & Inventory Mini-System."

CONTEXT
A retail counter records customer orders against a product catalog and keeps
stock in sync.

DOMAIN
- Products: name, unique code, price per unit, tax percentage, stock on hand.
- Customers: name, email (unique).
- Orders: one customer, one or more product lines (product + quantity),
  computed subtotal, tax, and grand total.

REQUIREMENTS
1. Design a normalized schema (migrations) for the above, plus any supporting
   tables you judge necessary (e.g. order line items).
2. POST endpoint to create an order: accepts customer email/name and a list
   of {product_id, quantity}. Validate stock availability, compute totals
   including tax, deduct stock, and return the created order.
3. GET endpoint to fetch a customer's order history by email.
4. GET endpoint returning products below a configurable low-stock threshold.
5. Dispatch a queued job on order creation that simulates sending an
   order-confirmation email (log entry or fake mailer — no real SMTP).
6. Write Pest or PHPUnit feature/unit tests covering order creation,
   including at least one edge case (insufficient stock).
7. Make the stock check-and-deduct step safe under concurrent requests — if
   two orders for the same product arrive near-simultaneously and only one
   unit is left, exactly one must succeed and the other must fail cleanly
   (no overselling). Explain and test how you guarantee this.
8. Seed the database with sample products and customers via
   factories/seeders.

STANDARDS
- Eloquent relationships and migrations done correctly.
- Thin controllers; business logic in a service/action class.
- Form Request classes for validation.
- Clean, idiomatic Laravel structure.

DELIVERABLE
Give me the full file set (migrations, models, service class, controllers,
form requests, job, routes, factories, seeder, tests) ready to drop into a
fresh `laravel/laravel` project, plus a README covering setup steps and any
assumptions you made where the brief is ambiguous — proceed on reasonable
assumptions rather than stopping to ask me.
