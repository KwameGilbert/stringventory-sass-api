# PurchaseRoute

Source: `src/routes/v1/PurchaseRoute.php`

Base path(s): `/v1/purchases`
Controller(s): `App\Controllers\PurchaseController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/purchases` | `PurchaseController::index` | Authenticated | Get all purchases (Restocks) |
| `POST` | `/v1/purchases` | `PurchaseController::create` | Authenticated | Create a new restock/purchase order |
| `DELETE` | `/v1/purchases/{id}` | `PurchaseController::delete` | Authenticated | Cancel/Delete Purchase |
| `GET` | `/v1/purchases/{id}` | `PurchaseController::show` | Authenticated | Get single purchase details |
| `PUT` | `/v1/purchases/{id}` | `PurchaseController::update` | Authenticated | Update/Receive Purchase |
| `POST` | `/v1/purchases/{id}/approve` | `PurchaseController::approve` | Authenticated | Approve a pending purchase (CEO only) |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

