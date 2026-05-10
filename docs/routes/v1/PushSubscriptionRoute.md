# PushSubscriptionRoute

Source: `src/routes/v1/PushSubscriptionRoute.php`

Base path(s): `/v1/push-subscriptions`
Controller(s): `App\Controllers\PushSubscriptionController`

## Definition

Push subscription routes manage browser or device push subscription records. They are related to web push notifications and user notification delivery targets.

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/push-subscriptions` | `PushSubscriptionController::index` | Public | Get all push subscriptions |
| `POST` | `/v1/push-subscriptions` | `PushSubscriptionController::create` | Public | Create push subscription |
| `DELETE` | `/v1/push-subscriptions/{id}` | `PushSubscriptionController::delete` | Public | Delete push subscription |
| `GET` | `/v1/push-subscriptions/{id}` | `PushSubscriptionController::show` | Public | Get single push subscription |
| `PUT` | `/v1/push-subscriptions/{id}` | `PushSubscriptionController::update` | Public | Update push subscription |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.


