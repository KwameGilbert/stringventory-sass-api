# AuthRoute

Source: `src/routes/v1/AuthRoute.php`

Base path(s): `/v1/auth`
Controller(s): `App\Controllers\AuthController`, `App\Controllers\PasswordResetController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `POST` | `/v1/auth/login` | `AuthController::login` | Public | Login user POST /auth/login |
| `POST` | `/v1/auth/logout` | `AuthController::logout` | Authenticated | Logout |
| `GET` | `/v1/auth/me` | `AuthController::me` | Authenticated | Get current user |
| `POST` | `/v1/auth/password/change` | `AuthController::changePassword` | Authenticated | Change password for logged-in user POST /auth/password/change |
| `POST` | `/v1/auth/password/forgot` | `PasswordResetController::requestReset` | Public | Request a password reset link POST /auth/password/reset-request |
| `POST` | `/v1/auth/password/reset` | `PasswordResetController::reset` | Public | Reset password using token POST /auth/password/reset |
| `POST` | `/v1/auth/refresh` | `AuthController::refresh` | Public | Refresh access token |
| `POST` | `/v1/auth/register` | `AuthController::register` | Public | Register a new user POST /auth/register |
| `POST` | `/v1/auth/resend-verification` | `AuthController::resendVerificationEmail` | Public | Resend verification email |
| `GET` | `/v1/auth/verify-email` | `AuthController::verifyEmail` | Public | Verify email with token |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

