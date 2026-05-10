# UtilsRoute

Source: `src/routes/v1/UtilsRoute.php`

Base path(s): `/v1/utils`
Controller(s): `App\Controllers\UtilsController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/utils/currencies` | `UtilsController::currencies` | Public | Get system-wide supported currencies list |
| `GET` | `/v1/utils/generate-token` | `UtilsController::generateToken` | Public | Generate secure random string/token helper |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

