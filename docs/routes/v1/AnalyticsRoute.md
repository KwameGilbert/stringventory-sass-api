# AnalyticsRoute

Source: `src/routes/v1/AnalyticsRoute.php`

Base path(s): `/v1/analytics`
Controller(s): `App\Controllers\AnalyticsController`

## Endpoints

| Method | Path | Controller action | Access | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/v1/analytics/activity-logs` | `AnalyticsController::getActivityLogs` | Authenticated | Get Activity Logs with Summary and Pagination |
| `GET` | `/v1/analytics/customer-report` | `AnalyticsController::getCustomerReport` | Authenticated | Get Customer Report |
| `GET` | `/v1/analytics/dashboard` | `AnalyticsController::getDashboardOverview` | Authenticated | Get Dashboard Overview Metrics and Charts |
| `GET` | `/v1/analytics/expense-report` | `AnalyticsController::getExpenseReport` | Authenticated | Get Expense Report |
| `GET` | `/v1/analytics/export/{reportType}` | `AnalyticsController::exportReport` | Authenticated | Export Report (PDF/Excel) |
| `GET` | `/v1/analytics/financial-report` | `AnalyticsController::getFinancialReport` | Authenticated | Get Financial Report |
| `GET` | `/v1/analytics/inventory-report` | `AnalyticsController::getInventoryReport` | Authenticated | Get Inventory Report |
| `GET` | `/v1/analytics/sales-report` | `AnalyticsController::getSalesReport` | Authenticated | Get Sales Report |

## Notes

- Authentication and role requirements are derived from route middleware declarations in the route file.
- Request and response payloads should be verified against the controller implementation and model validation rules before publishing as an external API contract.

