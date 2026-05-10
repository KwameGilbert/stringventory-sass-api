# RefreshTokenRoute

Source: `src/routes/v1/RefreshTokenRoute.php`

Base path(s): `/v1/refresh-tokens`
Controller(s): `App\Controllers\RefreshTokenController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/refresh-tokens` | `RefreshTokenController::index` | Public | Get all refresh tokens |
| `POST` | `/v1/refresh-tokens` | `RefreshTokenController::create` | Public | Create refresh token |
| `DELETE` | `/v1/refresh-tokens/{id}` | `RefreshTokenController::delete` | Public | Delete/Revoke refresh token |
| `GET` | `/v1/refresh-tokens/{id}` | `RefreshTokenController::show` | Public | Get single refresh token |
| `PUT` | `/v1/refresh-tokens/{id}` | `RefreshTokenController::update` | Public | Update refresh token |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

