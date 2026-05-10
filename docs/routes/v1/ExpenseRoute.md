# ExpenseRoute

Source: `src/routes/v1/ExpenseRoute.php`

Base path(s): `/v1/expenses`
Controller(s): `App\Controllers\ExpenseController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/expenses` | `ExpenseController::index` | Authenticated | Get all expenses |
| `POST` | `/v1/expenses` | `ExpenseController::create` | Authenticated; roles: ceo, manager | Create expense |
| `DELETE` | `/v1/expenses/{id}` | `ExpenseController::delete` | Authenticated; roles: ceo, manager | Delete expense |
| `GET` | `/v1/expenses/{id}` | `ExpenseController::show` | Authenticated | Get single expense |
| `PUT` | `/v1/expenses/{id}` | `ExpenseController::update` | Authenticated; roles: ceo, manager | Update expense |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

