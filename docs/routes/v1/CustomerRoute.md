# CustomerRoute

Source: `src/routes/v1/CustomerRoute.php`

Base path(s): `/v1/customers`
Controller(s): `App\Controllers\CustomerController`

## Definition

Customer routes manage customer records. They are related to sales, orders, refunds, customer history, and customer-focused reporting.

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/customers` | `CustomerController::index` | Authenticated | Get all customers |
| `POST` | `/v1/customers` | `CustomerController::create` | Authenticated | Create customer |
| `DELETE` | `/v1/customers/{id}` | `CustomerController::delete` | Authenticated; roles: ceo, manager | Delete customer |
| `GET` | `/v1/customers/{id}` | `CustomerController::show` | Authenticated | Get single customer |
| `PUT` | `/v1/customers/{id}` | `CustomerController::update` | Authenticated | Update customer |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.


