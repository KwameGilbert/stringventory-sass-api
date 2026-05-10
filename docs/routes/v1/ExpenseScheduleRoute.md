# ExpenseScheduleRoute

Source: `src/routes/v1/ExpenseScheduleRoute.php`

Base path(s): `/v1/expense-schedules`
Controller(s): `App\Controllers\ExpenseScheduleController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/expense-schedules` | `ExpenseScheduleController::index` | Authenticated | Get all schedules |
| `POST` | `/v1/expense-schedules` | `ExpenseScheduleController::create` | Authenticated; roles: ceo, manager | Create schedule |
| `DELETE` | `/v1/expense-schedules/{id}` | `ExpenseScheduleController::delete` | Authenticated; roles: ceo, manager | Delete schedule |
| `GET` | `/v1/expense-schedules/{id}` | `ExpenseScheduleController::show` | Authenticated | Get single schedule |
| `PUT` | `/v1/expense-schedules/{id}` | `ExpenseScheduleController::update` | Authenticated; roles: ceo, manager | Update schedule |
| `POST` | `/v1/expense-schedules/process` | `ExpenseScheduleController::process` | Authenticated; roles: ceo, manager | Trigger processing of all due schedules |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

