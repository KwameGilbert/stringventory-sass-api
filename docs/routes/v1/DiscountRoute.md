# DiscountRoute

Source: `src/routes/v1/DiscountRoute.php`

Base path(s): `/v1/discounts`
Controller(s): `App\Controllers\DiscountController`

## Definition

Discount routes manage discount rules and records. They are related to pricing, promotions, order totals, and sales adjustments.

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/discounts` | `DiscountController::index` | Authenticated | Get all discounts |
| `POST` | `/v1/discounts` | `DiscountController::create` | Authenticated; roles: ceo, manager | Create discount |
| `DELETE` | `/v1/discounts/{id}` | `DiscountController::delete` | Authenticated; roles: ceo, manager | Delete discount |
| `GET` | `/v1/discounts/{id}` | `DiscountController::show` | Authenticated | Get single discount |
| `PUT` | `/v1/discounts/{id}` | `DiscountController::update` | Authenticated; roles: ceo, manager | Update discount |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.


