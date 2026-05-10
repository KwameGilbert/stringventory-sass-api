# MessagingTemplateRoute

Source: `src/routes/v1/MessagingTemplateRoute.php`

Base path(s): `/v1/messaging-templates`
Controller(s): `App\Controllers\MessagingTemplateController`

## Definition

Messaging template routes manage reusable message templates. They are related to consistent SMS, email, or campaign content used by messaging workflows.

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/messaging-templates` | `MessagingTemplateController::index` | Public | Get all messaging templates |
| `POST` | `/v1/messaging-templates` | `MessagingTemplateController::create` | Public | Create messaging template using MessagingService |
| `DELETE` | `/v1/messaging-templates/{id}` | `MessagingTemplateController::delete` | Public | Delete messaging template using MessagingService |
| `GET` | `/v1/messaging-templates/{id}` | `MessagingTemplateController::show` | Public | Get single messaging template |
| `PUT` | `/v1/messaging-templates/{id}` | `MessagingTemplateController::update` | Public | Update messaging template using MessagingService |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.


