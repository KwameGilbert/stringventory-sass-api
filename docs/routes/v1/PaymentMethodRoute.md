# PaymentMethodRoute

Source: `src/routes/v1/PaymentMethodRoute.php`

Base path(s): `/v1/payment-methods`
Controller(s): `App\Controllers\PaymentMethodController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/payment-methods` | `PaymentMethodController::index` | Public | Get all payment methods |
| `POST` | `/v1/payment-methods` | `PaymentMethodController::create` | Public | Create payment method |
| `DELETE` | `/v1/payment-methods/{id}` | `PaymentMethodController::delete` | Public | Delete payment method |
| `GET` | `/v1/payment-methods/{id}` | `PaymentMethodController::show` | Public | Get single payment method |
| `PUT` | `/v1/payment-methods/{id}` | `PaymentMethodController::update` | Public | Update payment method |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

