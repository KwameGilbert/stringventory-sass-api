# SupplierRoute

Source: `src/routes/v1/SupplierRoute.php`

Base path(s): `/v1/suppliers`
Controller(s): `App\Controllers\SupplierController`

## Definition

Supplier routes manage supplier records. They are related to product sourcing, purchases, procurement history, and inventory replenishment.

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/suppliers` | `SupplierController::index` | Authenticated | Get all suppliers |
| `POST` | `/v1/suppliers` | `SupplierController::create` | Authenticated; roles: ceo, manager | Create supplier |
| `DELETE` | `/v1/suppliers/{id}` | `SupplierController::delete` | Authenticated; roles: ceo, manager | Delete supplier |
| `GET` | `/v1/suppliers/{id}` | `SupplierController::show` | Authenticated | Get single supplier |
| `PUT` | `/v1/suppliers/{id}` | `SupplierController::update` | Authenticated; roles: ceo, manager | Update supplier |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.


