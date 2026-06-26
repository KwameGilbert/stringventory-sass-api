# StringVentory — Backend Implementation Plan

> **For:** Backend Development Team  
> **Derived From:** Full frontend codebase analysis (React + Vite)  
> **Live API Base:** `https://stringventory-sass-api.onrender.com`  
> **Date:** June 25, 2026  
> **Version:** 1.0

---

## Overview

StringVentory is a **multi-tenant SaaS inventory platform**. The frontend is fully built and expects a specific API contract. This document describes every endpoint the frontend calls, the exact request/response shapes it expects, the authorization rules, and any business logic the backend must implement.

The backend must support **three classes of actors**:

| Actor | Role Value | Access |
|---|---|---|
| Platform Super Admin | `superadmin` / `super_admin` | All endpoints — cross-tenant |
| Business Owner (CEO) | `ceo` / `owner` / `admin` | All `/v1/*` endpoints for their own tenant |
| Business Manager | `manager` / `management` | Most `/v1/*` endpoints for their own tenant |
| Sales Staff | `sales` / `salesperson` | Read + order creation endpoints only |

---

## Table of Contents

1. [Architecture Requirements](#1-architecture-requirements)
2. [Authentication Module](#2-authentication-module)
3. [Multi-Tenancy & Business Isolation](#3-multi-tenancy--business-isolation)
4. [Superadmin Module](#4-superadmin-module)
5. [Pricing Plans Module](#5-pricing-plans-module)
6. [Business Dashboard Modules](#6-business-dashboard-modules)
7. [Settings Module](#7-settings-module)
8. [Analytics & Reports Module](#8-analytics--reports-module)
9. [Messaging Module](#9-messaging-module)
10. [Notifications Module](#10-notifications-module)
11. [Response Format Standard](#11-response-format-standard)
12. [Authorization Matrix](#12-authorization-matrix)
13. [Missing / Partially Implemented Endpoints](#13-missing--partially-implemented-endpoints)
14. [Implementation Priority](#14-implementation-priority)

---

## 1. Architecture Requirements

### Multi-Tenancy Model

- Every resource (products, orders, customers, etc.) must be **scoped to a `businessId`**
- The backend must extract the `businessId` from the authenticated JWT and use it as a filter on **every** `/v1/*` query
- A user from Business A must **never** be able to read or modify data belonging to Business B
- Superadmin endpoints (`/v1/businesses/*`, `/superadmin/*`) bypass tenant scoping intentionally

### JWT Token Structure

The frontend extracts the following fields from the login response:

```json
{
  "accessToken": "eyJ...",
  "refreshToken": "eyJ...",
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "role": "ceo",
    "roleId": "uuid",
    "status": "active",
    "businessId": "uuid",
    "subscriptionPlan": "professional",
    "subscriptionStatus": "active",
    "isSuperAdmin": false
  }
}
```

> **Important:** The frontend checks `user.isSuperAdmin === true` OR `user.role === "superadmin"` to grant access to the superadmin portal. Make sure to include `isSuperAdmin` on the user object for superadmins.

### Token Refresh

The frontend sends a refresh request automatically on any `401` response:

```
POST /v1/auth/refresh
Body: { "refreshToken": "...", "refresh_token": "..." }
```

Response must return a new `accessToken` (and optionally new `refreshToken`). The frontend accepts multiple field names: `accessToken`, `access_token`, or `token`.

---

## 2. Authentication Module

### 2.1 Register

```
POST /v1/auth/register
```

**Request Body:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "businessName": "My Business",
  "phone": "+1234567890"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "data": {
    "user": { "id": "uuid", "email": "john@example.com", ... },
    "tokens": {
      "accessToken": "eyJ...",
      "refreshToken": "eyJ..."
    }
  }
}
```

**Business Logic:**
- Create a new `Business` record with the provided `businessName`
- Create a `User` record linked to that business with role `CEO`
- Assign the `free_trial` subscription plan to the new business
- Set `trial_ends_at` to 14 days from now (configurable)
- Send verification email with token link

---

### 2.2 Login

```
POST /v1/auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "john@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "role": "ceo",
      "roleId": "uuid",
      "status": "active",
      "businessId": "uuid",
      "subscriptionPlan": "professional",
      "subscriptionStatus": "active",
      "isSuperAdmin": false,
      "mustChangePassword": false
    },
    "tokens": {
      "accessToken": "eyJ...",
      "refreshToken": "eyJ..."
    }
  }
}
```

**Business Logic:**
- Validate credentials
- Check if business is suspended → return `403` with message `"Your account has been suspended. Please contact support."`
- If user `mustChangePassword` / `first_login` is true → include `mustChangePassword: true` in user object
- For superadmin users, include `isSuperAdmin: true`

---

### 2.3 Logout

```
POST /v1/auth/logout
Body: { "refreshToken": "..." }
```

Invalidate the refresh token server-side. Return `200` regardless of whether the token was valid.

---

### 2.4 Token Refresh

```
POST /v1/auth/refresh
Body: { "refreshToken": "...", "refresh_token": "..." }
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tokens": {
      "accessToken": "eyJ...",
      "refreshToken": "eyJ..."
    }
  }
}
```

Return `401` if refresh token is expired or invalid. The frontend will clear tokens and fire a logout event on `401` from this endpoint.

---

### 2.5 Forgot Password

```
POST /v1/auth/password/forgot
Body: { "email": "john@example.com" }
```

Send a password reset email. Always return success (do not reveal if email exists).

---

### 2.6 Reset Password

```
POST /v1/auth/password/reset
Body: { "token": "reset-token", "newPassword": "...", "confirmPassword": "..." }
```

---

### 2.7 Change Password (Authenticated)

```
POST /v1/auth/password/change
Headers: Authorization: Bearer <token>
Body: {
  "current_password": "OldPass",
  "new_password": "NewPass",
  "password": "NewPass",
  "password_confirmation": "NewPass"
}
```

> **Note:** The frontend sends multiple field aliases. Accept any of: `current_password`, `new_password`, `password`, `password_confirmation`.

---

### 2.8 Get Current User Profile

```
GET /v1/auth/me
Headers: Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "email": "john@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "role": "ceo",
    "businessId": "uuid",
    "subscriptionPlan": "professional",
    "subscriptionStatus": "active",
    "avatar": "https://...",
    "isSuperAdmin": false
  }
}
```

---

### 2.9 Update Profile

```
PUT /v1/auth/me
Headers: Authorization: Bearer <token>
Body: { "firstName": "...", "lastName": "...", "phone": "..." }
```

---

### 2.10 Upload Avatar

```
POST /v1/auth/avatar
Headers: Authorization: Bearer <token>
Content-Type: multipart/form-data
Body: FormData with file field
```

Return the URL of the uploaded avatar:
```json
{ "success": true, "data": { "avatar": "https://cdn.example.com/avatars/uuid.jpg" } }
```

---

### 2.11 Verify Email

```
GET /v1/auth/verify-email?email=john@example.com&token=abc123
```

---

### 2.12 User Activity Logs (Auth-Level)

```
GET /v1/auth/activity-logs
Headers: Authorization: Bearer <token>
Query: ?page=1&limit=10
```

Returns login history and profile changes for the authenticated user.

---

## 3. Multi-Tenancy & Business Isolation

### Key Principle

**Every `/v1/*` route (except auth routes) must:**
1. Validate the JWT Bearer token
2. Extract `businessId` from the token payload
3. Scope all DB queries to that `businessId`
4. Return `403` if the user tries to access a different business's data

### Business Data Returned on Login

When TenantProvider initializes (after login), it reads `current_business` from `localStorage` which is populated from the login response user object. The frontend also calls `GET /v1/settings/business` separately to populate the Settings context.

**Business object shape the frontend expects** (from `business.js` normalization model):

```json
{
  "id": "uuid",
  "name": "My Business",
  "email": "owner@business.com",
  "domain": "mybusiness.com",
  "phone": "+1234567890",
  "industry": "Retail",
  "country": "Ghana",
  "city": "Accra",
  "address": "123 Main St",
  "owner_name": "John Doe",
  "subscription_plan": "professional",
  "status": "active",
  "current_usage": {
    "total_users": 8,
    "total_products": 1200,
    "storage_used": 512
  },
  "usage_limits": {
    "maxUsers": 15,
    "maxProducts": 5000,
    "maxStorage": 51200
  },
  "mrr": 149.00,
  "billing_cycle": "monthly",
  "next_billing_date": "2026-07-25T00:00:00Z",
  "subscription_status": "active",
  "created_at": "2026-01-10T00:00:00Z",
  "logo_url": "https://...",
  "subscription": {
    "status": "active",
    "billingCycle": "monthly",
    "currentPeriodStart": "2026-06-25T00:00:00Z",
    "currentPeriodEnd": "2026-07-25T00:00:00Z",
    "trialEndsAt": null,
    "mrr": 149.00,
    "gatewayCustomerId": "cus_xxxx",
    "gatewaySubscriptionId": "sub_xxxx",
    "paymentMethodBrand": "visa",
    "paymentMethodLast4": "4242",
    "cancelAtPeriodEnd": false,
    "createdAt": "2026-01-10T00:00:00Z",
    "updatedAt": "2026-06-25T00:00:00Z"
  }
}
```

> **Note:** The frontend's `normalizeBusiness()` tries many field aliases. To be safe, use the exact field names shown above.

---

## 4. Superadmin Module

All superadmin endpoints require `isSuperAdmin: true` in the JWT. Return `403` for any non-superadmin user.

### 4.1 List All Businesses

```
GET /v1/businesses
Headers: Authorization: Bearer <superadmin-token>
Query: ?page=1&limit=20&status=active&plan=professional&search=keyword
```

**Response:**
```json
{
  "success": true,
  "data": {
    "businesses": [
      {
        "id": "uuid",
        "name": "Business Name",
        "email": "owner@business.com",
        "subscription_plan": "professional",
        "status": "active",
        "mrr": 149.00,
        "current_usage": { "total_users": 8, "total_products": 1200 },
        "created_at": "2026-01-10T00:00:00Z"
      }
    ],
    "pagination": { "total": 150, "page": 1, "limit": 20, "pages": 8 }
  }
}
```

---

### 4.2 Get Business by ID

```
GET /v1/businesses/:id
Headers: Authorization: Bearer <superadmin-token>
```

Return the **full business object** including:
- All base fields (see section 3 for shape)
- `users` array (nested team members)
- `activityLogs` array (recent activity)
- `subscription` object (full subscription detail)

```json
{
  "success": true,
  "data": {
    "business": {
      "id": "uuid",
      "name": "Business Name",
      ...all base fields...,
      "users": [
        {
          "id": "uuid",
          "firstName": "John",
          "lastName": "Doe",
          "email": "john@business.com",
          "role": "ceo",
          "status": "active",
          "lastLogin": "2026-06-20T10:00:00Z",
          "phone": "+1234567890",
          "emailVerified": true
        }
      ],
      "activityLogs": [
        {
          "id": "uuid",
          "action": "Product Created",
          "description": "New product 'Widget A' was created",
          "type": "inventory",
          "timestamp": "2026-06-24T14:00:00Z"
        }
      ],
      "subscription": { ...full subscription object... }
    }
  }
}
```

---

### 4.3 Create Business (Manual)

```
POST /v1/businesses
Headers: Authorization: Bearer <superadmin-token>
```

**Request Body:**
```json
{
  "name": "New Business",
  "email": "owner@newbusiness.com",
  "phone": "+1234567890",
  "industry": "Retail",
  "country": "Ghana",
  "city": "Accra",
  "address": "123 Main St",
  "domain": "newbusiness.com",
  "subscription_plan": "starter",
  "owner": {
    "firstName": "Jane",
    "lastName": "Smith",
    "email": "jane@newbusiness.com",
    "password": "TempPass123!"
  }
}
```

**Business Logic:**
- Create `Business` record
- Create `User` with role `CEO` linked to the business
- Assign the specified subscription plan
- Send invitation email to owner

---

### 4.4 Update Business

```
PUT /v1/businesses/:id
Headers: Authorization: Bearer <superadmin-token>
Body: { ...any business fields to update... }
```

---

### 4.5 Delete Business

```
DELETE /v1/businesses/:id
Headers: Authorization: Bearer <superadmin-token>
```

**Business Logic:**
- Hard delete all business data (users, products, orders, etc.)
- Cancel any active subscription in the payment gateway
- Return `200` on success

---

### 4.6 Suspend Business

```
POST /v1/businesses/:id/suspend
Headers: Authorization: Bearer <superadmin-token>
```

**Business Logic:**
- Set `business.status = "suspended"`
- All subsequent requests from this business's users should return `403`
- Do NOT delete data

---

### 4.7 Reactivate Business

```
POST /v1/businesses/:id/reactivate
Headers: Authorization: Bearer <superadmin-token>
```

Set `business.status = "active"`.

---

### 4.8 Platform Analytics

```
GET /superadmin/analytics/platform
Headers: Authorization: Bearer <superadmin-token>
Query: ?timeRange=30days
```

**Response:**
```json
{
  "success": true,
  "currency": "USD",
  "data": {
    "totalBusinesses": 185,
    "activeSubscriptions": 162,
    "monthlyRecurringRevenue": 42850.50,
    "totalUsers": 1847,
    "businessesChange": 12.3,
    "subscriptionsChange": 8.5,
    "mrrChange": 15.4,
    "usersChange": 23.5,
    "churnRate": 2.4,
    "totalSubscribers": 162,
    "totalMRR": 42850.50,
    "revenueTrends": [
      { "month": "Jan", "date": "Jan", "revenue": 28000, "mrr": 27500, "subscriptions": 140 },
      { "month": "Feb", "date": "Feb", "revenue": 31200, "mrr": 30800, "subscriptions": 148 }
    ],
    "planDistribution": [
      { "plan": "Free Trial", "count": 23, "percentage": 12.4, "revenue": 0, "color": "bg-gray-400" },
      { "plan": "Starter", "count": 55, "percentage": 29.7, "revenue": 2695, "color": "bg-emerald-500" },
      { "plan": "Professional", "count": 72, "percentage": 38.9, "revenue": 10728, "color": "bg-blue-500" },
      { "plan": "Enterprise", "count": 35, "percentage": 18.9, "revenue": 17465, "color": "bg-amber-500" }
    ],
    "revenueByPlan": [
      { "plan": "Starter", "name": "Starter", "revenue": 2695, "fill": "#10b981" },
      { "plan": "Professional", "name": "Professional", "revenue": 10728, "fill": "#3b82f6" },
      { "plan": "Enterprise", "name": "Enterprise", "revenue": 17465, "fill": "#f59e0b" }
    ],
    "recentActivity": [
      {
        "id": "uuid",
        "type": "signup",
        "business": "New Business Co",
        "plan": null,
        "amount": 0,
        "time": "2 hours ago"
      },
      {
        "id": "uuid",
        "type": "upgrade",
        "business": "Retail Corp",
        "plan": "Professional",
        "amount": 149,
        "time": "5 hours ago"
      },
      {
        "id": "uuid",
        "type": "payment",
        "business": "Shop Ltd",
        "plan": "Starter",
        "amount": 49,
        "time": "1 day ago"
      },
      {
        "id": "uuid",
        "type": "cancellation",
        "business": "Old Store",
        "plan": null,
        "amount": 0,
        "time": "2 days ago"
      }
    ],
    "topBusinesses": [
      { "id": "uuid", "name": "Global Tech", "revenue": 2500, "growth": 18 }
    ],
    "userGrowth": [
      { "date": "Week 1", "new": 120, "active": 890 }
    ],
    "geographicDistribution": [
      { "country": "Ghana", "users": 294, "percentage": 16 }
    ],
    "planStats": {
      "plan-uuid-starter": { "subscribers": 55 },
      "plan-uuid-professional": { "subscribers": 72 }
    }
  }
}
```

> **Note:** `timeRange` query param accepts: `7days`, `30days`, `90days`, `1year`. Aggregate data for the selected period.

---

### 4.9 Superadmin Settings

```
GET /superadmin/settings
PUT /superadmin/settings
Headers: Authorization: Bearer <superadmin-token>
```

Platform-wide configuration (platform name, default currency, maintenance mode, etc.).

---

## 5. Pricing Plans Module

Plans are managed by superadmin and consumed by business users.

### 5.1 List Plans

```
GET /v1/plans
Headers: Authorization: Bearer <any-valid-token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "plans": [
      {
        "id": "uuid",
        "name": "Starter",
        "description": "Perfect for small businesses",
        "priceMonthly": 49,
        "priceYearly": 490,
        "trialDays": 14,
        "isPopular": false,
        "status": "active",
        "color": "#10b981",
        "features": ["Dashboard", "Products", "Orders", "Customers", "Inventory"],
        "marketingFeatures": ["5 Users", "500 Products", "5GB Storage"],
        "systemCapabilities": [],
        "featureFlags": ["dashboard", "products", "orders", "customers", "inventory", "suppliers", "purchases", "expenses", "basic_reports"],
        "limits": {
          "maxUsers": 5,
          "maxProducts": 500,
          "maxStorageMB": 5120,
          "maxOrdersPerMonth": 0,
          "maxCategories": 50,
          "maxSuppliers": 20,
          "maxCustomers": 200,
          "maxLocations": 1
        },
        "subscribers": 55,
        "monthlyRecurringRevenue": 2695,
        "createdAt": "2026-01-01T00:00:00Z",
        "updatedAt": "2026-01-01T00:00:00Z"
      }
    ]
  }
}
```

> **Field aliases the frontend also accepts:** `monthlyPrice`, `price` for `priceMonthly`; `yearlyPrice` for `priceYearly`; `maxStorageMb` for `maxStorageMB`.

---

### 5.2 Get Plan by ID

```
GET /v1/plans/:id
```

Return the single plan object as above, plus full feature list.

---

### 5.3 Create Plan

```
POST /v1/plans
Headers: Authorization: Bearer <superadmin-token>
```

**Request Body:**
```json
{
  "name": "Enterprise",
  "description": "For large organizations",
  "priceMonthly": 499,
  "priceYearly": 4990,
  "trialDays": 30,
  "isPopular": false,
  "color": "#8b5cf6",
  "features": ["All Professional features", "Custom reports", "Webhooks", "Audit logs"],
  "featureFlags": ["dashboard", "products", "advanced_analytics", "custom_reports", "webhooks", "audit_logs"],
  "limits": {
    "maxUsers": -1,
    "maxProducts": -1,
    "maxStorageMB": -1,
    "maxOrdersPerMonth": -1,
    "maxCategories": -1,
    "maxSuppliers": -1,
    "maxCustomers": -1,
    "maxLocations": -1
  }
}
```

> `-1` means **unlimited** for all limit fields.

---

### 5.4 Update Plan

```
PUT /v1/plans/:id
Headers: Authorization: Bearer <superadmin-token>
Body: { ...fields to update... }
```

**Business Logic:** When limits are updated, all currently subscribed businesses should have their `usage_limits` updated immediately on next data fetch.

---

### 5.5 Delete Plan

```
DELETE /v1/plans/:id
Headers: Authorization: Bearer <superadmin-token>
```

Only allow deletion if no businesses are currently subscribed to this plan.

---

### 5.6 Plan Comparison Matrix

```
GET /v1/plans/comparison
Headers: Authorization: Bearer <any-valid-token>
```

Returns a structured feature comparison. The frontend handles gracefully if this returns `{ data: [] }`.

---

## 6. Business Dashboard Modules

All endpoints below are tenant-scoped. Extract `businessId` from JWT and filter accordingly.

---

### 6.1 Users

```
GET    /v1/users              → List users in business
POST   /v1/users              → Create user (CEO only)
GET    /v1/users/:id          → Get single user
PUT    /v1/users/:id          → Update user
DELETE /v1/users/:id          → Delete user
POST   /v1/users/:id/resend-verification → Resend verification email
```

**User object shape:**
```json
{
  "id": "uuid",
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@business.com",
  "phone": "+1234567890",
  "role": "manager",
  "roleId": "uuid",
  "status": "active",
  "emailVerified": true,
  "lastLogin": "2026-06-20T10:00:00Z",
  "businessId": "uuid",
  "createdAt": "2026-01-10T00:00:00Z"
}
```

**Plan limit enforcement:** On `POST /v1/users`, check if `current_usage.total_users >= usage_limits.maxUsers`. Return `403` with message `"User limit reached. Please upgrade your plan."` if at capacity.

---

### 6.2 Roles

```
GET /v1/roles        → List available roles for this business
GET /v1/roles/:id    → Get single role
```

Accepted role values (frontend normalizes these): `superadmin`, `super_admin`, `ceo`, `owner`, `admin`, `administrator`, `manager`, `management`, `sales`, `salesperson`, `sales_person`, `sales rep`, `sales_rep`.

---

### 6.3 Products

```
GET    /v1/products              → List products (paginated, filterable)
POST   /v1/products              → Create product
GET    /v1/products/:id          → Get single product
PUT    /v1/products/:id          → Update product
DELETE /v1/products/:id          → Delete product
GET    /v1/products/low-stock    → Products below low stock threshold
GET    /v1/products/expiring     → Products near expiry date
```

**Query params for list:** `?page=1&limit=20&categoryId=uuid&search=keyword&status=active&sortBy=name&sortOrder=asc`

**Product shape:**
```json
{
  "id": "uuid",
  "name": "Product Name",
  "sku": "SKU-001",
  "description": "...",
  "price": 29.99,
  "costPrice": 15.00,
  "categoryId": "uuid",
  "category": { "id": "uuid", "name": "Electronics" },
  "unitOfMeasureId": "uuid",
  "unitOfMeasure": { "id": "uuid", "name": "Piece" },
  "stockQuantity": 100,
  "reorderPoint": 10,
  "imageUrl": "https://...",
  "status": "active",
  "expiryDate": null,
  "businessId": "uuid",
  "createdAt": "2026-01-10T00:00:00Z",
  "updatedAt": "2026-06-20T00:00:00Z"
}
```

**Plan limit enforcement:** On `POST /v1/products`, check `total_products >= maxProducts`.

---

### 6.4 Categories

```
GET    /v1/categories       → List categories
POST   /v1/categories       → Create category
GET    /v1/categories/:id   → Get single category
PUT    /v1/categories/:id   → Update category
DELETE /v1/categories/:id   → Delete category
```

---

### 6.5 Inventory

```
GET  /v1/inventory                          → List inventory records
GET  /v1/inventory/product/:productId       → Get inventory for a specific product
POST /v1/inventory                          → Create inventory record
PUT  /v1/inventory/:id                      → Update inventory record
POST /v1/inventory/adjust                   → Adjust stock level
POST /v1/inventory/transfer                 → Transfer stock between locations
```

**Inventory adjustment body:**
```json
{
  "productId": "uuid",
  "type": "add",
  "quantity": 50,
  "reason": "Received new shipment",
  "notes": "PO-12345"
}
```

---

### 6.6 Suppliers

```
GET    /v1/suppliers       → List suppliers
POST   /v1/suppliers       → Create supplier
GET    /v1/suppliers/:id   → Get single supplier
PUT    /v1/suppliers/:id   → Update supplier
DELETE /v1/suppliers/:id   → Delete supplier
```

---

### 6.7 Purchases

```
GET    /v1/purchases            → List purchase orders
POST   /v1/purchases            → Create purchase order
GET    /v1/purchases/:id        → Get single purchase
PUT    /v1/purchases/:id        → Update purchase
DELETE /v1/purchases/:id        → Delete purchase
POST   /v1/purchases/:id/approve → Approve purchase order
```

---

### 6.8 Customers

```
GET    /v1/customers       → List customers
POST   /v1/customers       → Create customer
GET    /v1/customers/:id   → Get single customer
PUT    /v1/customers/:id   → Update customer
DELETE /v1/customers/:id   → Delete customer
```

---

### 6.9 Orders

```
GET    /v1/orders                   → List orders
POST   /v1/orders                   → Create order
GET    /v1/orders/:id               → Get single order
PUT    /v1/orders/:id               → Update order
DELETE /v1/orders/:id               → Delete order
POST   /v1/orders/:id/refunds       → Create refund for order
POST   /v1/orders/:id/fulfill       → Fulfill order (decrement stock)
```

**Order creation business logic:**
- On `POST /v1/orders`, automatically decrement product stock quantities
- Create a `Transaction` record linked to the order

---

### 6.10 Refunds

```
GET  /v1/refunds                 → List refunds
POST /v1/refunds                 → Create refund
GET  /v1/refunds/:id             → Get single refund
PUT  /v1/refunds/:id/status      → Update refund status
```

---

### 6.11 Transactions

```
GET /v1/transactions      → List transactions
GET /v1/transactions/:id  → Get single transaction
```

Transactions are read-only. They are created automatically when orders are placed/refunded/fulfilled.

---

### 6.12 Expenses

```
GET    /v1/expenses                     → List expenses
POST   /v1/expenses                     → Create expense
GET    /v1/expenses/:id                 → Get single expense
PUT    /v1/expenses/:id                 → Update expense
DELETE /v1/expenses/:id                 → Delete expense
GET    /v1/expense-categories           → List expense categories
POST   /v1/expense-categories           → Create expense category
PUT    /v1/expense-categories/:id       → Update category
DELETE /v1/expense-categories/:id       → Delete category
```

---

### 6.13 Units of Measure

```
GET /v1/units-of-measure → List all units of measure
```

This is a reference list (global or per-tenant). Used in product creation forms.

---

## 7. Settings Module

Settings are scoped per business (extracted from JWT).

### 7.1 Business Settings

```
GET /v1/settings/business
PUT /v1/settings/business
```

**Response shape:**
```json
{
  "success": true,
  "data": {
    "businessName": "My Store",
    "name": "My Store",
    "address": "123 Main St",
    "phone": "+1234567890",
    "avatar": "https://cdn.example.com/logos/uuid.jpg",
    "logo": "https://cdn.example.com/logos/uuid.jpg",
    "email": "store@example.com",
    "website": "https://mystore.com",
    "taxNumber": "TAX-12345",
    "country": "Ghana",
    "city": "Accra",
    "currency": "GHS"
  }
}
```

---

### 7.2 Notification Settings

```
GET /v1/settings/notifications
PUT /v1/settings/notifications
```

**Response shape:**
```json
{
  "success": true,
  "data": {
    "lowStockThreshold": 10,
    "expiryAlertDays": 30,
    "emailNotifications": true,
    "dashboardRefresh": 5,
    "currency": "GHS"
  }
}
```

---

### 7.3 Currency Settings

```
GET  /v1/settings/currency
PUT  /v1/settings/currency
GET  /v1/settings/currency/history
POST /v1/settings/currency/fetch-rates
```

**Currency GET response:**
```json
{
  "success": true,
  "data": {
    "currency": "GHS",
    "currentCurrency": "GHS",
    "rates": {
      "USD": 0.066,
      "EUR": 0.061,
      "GBP": 0.052
    }
  }
}
```

> **Critical:** The Axios response interceptor dispatches a `app:currency-update` custom event whenever any response contains `rates` or `currency` on a currency endpoint. The `rates` object must be a flat map of `{ "CURRENCY_CODE": number }`.

---

### 7.4 Payment Settings

```
GET /v1/settings/payment
```

Returns payment method configuration for the business.

---

### 7.5 API Key Settings

```
GET  /v1/settings/api
POST /v1/settings/api/regenerate-key
```

Returns/regenerates the business's API key for integration purposes (Enterprise plan).

---

### 7.6 Subscription Info

```
GET /v1/settings/subscription
```

Returns current subscription details for the Settings page:
```json
{
  "success": true,
  "data": {
    "plan": "professional",
    "status": "active",
    "billingCycle": "monthly",
    "currentPeriodEnd": "2026-07-25T00:00:00Z",
    "mrr": 149.00,
    "usage": {
      "total_users": 8,
      "total_products": 1200,
      "storage_used": 512
    },
    "limits": {
      "maxUsers": 15,
      "maxProducts": 5000,
      "maxStorage": 51200
    }
  }
}
```

---

## 8. Analytics & Reports Module

All analytics are scoped to the authenticated business's data.

### 8.1 Dashboard Overview

```
GET /v1/analytics/dashboard
Query: ?startDate=2026-06-01&endDate=2026-06-25&period=monthly
```

**Response shape:**
```json
{
  "success": true,
  "currency": "GHS",
  "data": {
    "totalRevenue": 15420.00,
    "totalOrders": 342,
    "totalProducts": 1200,
    "totalCustomers": 87,
    "revenueChange": 12.5,
    "ordersChange": 8.3,
    "lowStockCount": 5,
    "pendingOrders": 12,
    "recentOrders": [...],
    "salesTrend": [
      { "date": "2026-06-01", "revenue": 520, "orders": 12 }
    ],
    "topProducts": [
      { "id": "uuid", "name": "Product A", "sold": 45, "revenue": 1350 }
    ],
    "topCategories": [
      { "name": "Electronics", "revenue": 5420, "percentage": 35.1 }
    ]
  }
}
```

---

### 8.2 Sales Report

```
GET /v1/analytics/sales-report
Query: ?startDate=...&endDate=...&groupBy=day|week|month
```

---

### 8.3 Inventory Report

```
GET /v1/analytics/inventory-report
Query: ?startDate=...&endDate=...
```

---

### 8.4 Financial Report

```
GET /v1/analytics/financial-report
Query: ?startDate=...&endDate=...
```

---

### 8.5 Customer Report

```
GET /v1/analytics/customer-report
Query: ?startDate=...&endDate=...
```

---

### 8.6 Expense Report

```
GET /v1/analytics/expense-report
Query: ?startDate=...&endDate=...
```

---

### 8.7 Export Report

```
GET /v1/analytics/export/:type
Query: ?format=csv|xlsx&startDate=...&endDate=...
```

Returns a binary file (`responseType: blob`). Types: `sales`, `inventory`, `financial`, `customer`, `expense`.

---

### 8.8 Activity Logs

```
GET /v1/analytics/activity-logs
Query: ?page=1&limit=10&module=products&severity=high&userId=uuid&startDate=...&endDate=...
```

**Response:**
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": "uuid",
        "action": "Product Created",
        "module": "products",
        "description": "Created product 'Widget A'",
        "severity": "info",
        "userId": "uuid",
        "userName": "John Doe",
        "timestamp": "2026-06-24T14:00:00Z",
        "metadata": {}
      }
    ],
    "summary": { "total": 450 },
    "pagination": { "total": 450, "page": 1, "limit": 10, "pages": 45 }
  }
}
```

---

## 9. Messaging Module

### 9.1 Send Message

```
POST /v1/messaging/messages
Headers: Authorization: Bearer <token>
Body: {
  "recipientId": "uuid-or-SUPPORT",
  "body": "Hello, this is a message"
}
```

---

### 9.2 Send Bulk Message (Campaign)

```
POST /v1/messaging/bulk-messages
Body: {
  "recipientIds": ["uuid1", "uuid2", "uuid3"],
  "body": "Your message content here",
  "templateId": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully sent to 3 customers",
  "data": {
    "campaignId": "CAMP-12345",
    "status": "queued"
  }
}
```

---

### 9.3 List Messages

```
GET /v1/messaging/messages
Query: ?page=1&limit=10&recipientId=uuid
```

---

### 9.4 Get Message Detail

```
GET /v1/messaging/messages/:id
```

---

### 9.5 List Templates

```
GET /v1/messaging/templates
```

---

### 9.6 Create Template

```
POST /v1/messaging/templates
Body: { "name": "Welcome Text", "body": "Hi {{name}}, welcome!" }
```

---

## 10. Notifications Module

```
GET    /v1/notifications              → List notifications (paginated)
PUT    /v1/notifications/:id/read     → Mark single as read
PUT    /v1/notifications/read-all     → Mark all as read
DELETE /v1/notifications/:id          → Delete single
DELETE /v1/notifications/delete-all   → Delete all
```

**Notification shape:**
```json
{
  "id": "uuid",
  "title": "Low Stock Alert",
  "message": "Product 'Widget A' is running low (8 units remaining)",
  "type": "warning",
  "read": false,
  "createdAt": "2026-06-24T14:00:00Z",
  "link": "/dashboard/products/uuid"
}
```

Auto-generate notifications for:
- Low stock (`stockQuantity <= lowStockThreshold`)
- Expiring products (`expiryDate <= now + expiryAlertDays`)
- Order status changes
- Subscription changes / trial expiry warnings

---

## 11. Response Format Standard

All endpoints must return responses in this consistent envelope format:

### Success

```json
{
  "success": true,
  "message": "Optional success message",
  "data": { ...payload... }
}
```

### Error

```json
{
  "success": false,
  "message": "Human-readable error description",
  "error": "MACHINE_READABLE_CODE",
  "details": { ...optional validation errors... }
}
```

### Paginated Lists

```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "total": 450,
      "page": 1,
      "limit": 20,
      "pages": 23
    }
  }
}
```

> **Note:** The frontend's `extractBusinesses()` / `extractPlans()` normalization functions try many response shapes including: top-level array, `data.businesses[]`, `data.items[]`, `data.results[]`, `data.data[]`. To be safe, always wrap lists in `data: { businesses: [...] }` or `data: { plans: [...] }`.

### HTTP Status Codes

| Status | When to Use |
|---|---|
| 200 | Successful GET, PUT, DELETE |
| 201 | Successful POST (resource created) |
| 400 | Validation errors / bad request |
| 401 | Missing or invalid JWT token |
| 403 | Valid token but insufficient permissions, suspended account, or plan limit exceeded |
| 404 | Resource not found |
| 409 | Conflict (e.g., duplicate email) |
| 422 | Semantic validation errors |
| 500 | Internal server error |

---

## 12. Authorization Matrix

| Endpoint Group | Superadmin | CEO | Manager | Sales |
|---|---|---|---|---|
| `/v1/auth/*` | ✅ | ✅ | ✅ | ✅ |
| `/v1/businesses/*` | ✅ | ❌ | ❌ | ❌ |
| `/superadmin/*` | ✅ | ❌ | ❌ | ❌ |
| `/v1/plans` (read) | ✅ | ✅ | ✅ | ✅ |
| `/v1/plans` (write) | ✅ | ❌ | ❌ | ❌ |
| `/v1/users` (read) | ✅ | ✅ | ✅ | ❌ |
| `/v1/users` (create/delete) | ✅ | ✅ | ❌ | ❌ |
| `/v1/products` (read) | ✅ | ✅ | ✅ | ✅ |
| `/v1/products` (write) | ✅ | ✅ | ✅ | ❌ |
| `/v1/inventory/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/orders` (read + create) | ✅ | ✅ | ✅ | ✅ |
| `/v1/orders` (update/delete) | ✅ | ✅ | ✅ | ❌ |
| `/v1/customers` (read + create) | ✅ | ✅ | ✅ | ✅ |
| `/v1/customers` (update/delete) | ✅ | ✅ | ✅ | ❌ |
| `/v1/suppliers/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/purchases/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/expenses/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/refunds/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/transactions/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/analytics/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/settings/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/messaging/*` | ✅ | ✅ | ✅ | ❌ |
| `/v1/notifications/*` | ✅ | ✅ | ✅ | ✅ |
| `/v1/roles/*` | ✅ | ✅ | ✅ | ✅ |

---

## 13. Missing / Partially Implemented Endpoints

The frontend has code wired to these endpoints but they may not be fully implemented yet:

| Status | Endpoint | Notes |
|---|---|---|
| 🔴 Missing | `GET /superadmin/analytics/platform` | Analytics page falls back to mock data — highest priority |
| 🔴 Missing | `GET /superadmin/settings` | Platform settings page broken |
| 🔴 Missing | `PUT /superadmin/settings` | Platform settings save broken |
| 🟡 Partial | `GET /v1/businesses/:id` | Nested `users[]`, `activityLogs[]`, and `subscription{}` fields must be included |
| 🟡 Partial | `GET /v1/plans` | Must include `subscribers`, `monthlyRecurringRevenue`, `planStats` in response |
| 🟡 Partial | `GET /v1/plans/comparison` | Frontend gracefully handles empty `{ data: [] }` — implement when ready |
| 🟡 Partial | `GET /v1/settings/business` | Must return `subscription_plan`, `usage_limits`, `current_usage` |
| 🔴 Missing | `GET /v1/settings/subscription` | Frontend calls this manually in settingsService |
| 🔴 Missing | `PUT /v1/businesses/:id/overrides` | Needed for BusinessSettings tab (verified, maintenance, beta toggles) |
| 🔴 Missing | Suspension check on login | Suspended businesses must receive `403` with clear message on all requests |
| 🔴 Missing | Plan limit enforcement | Products, Users creation must check against plan limits |
| 🟡 Partial | `POST /v1/auth/refresh` | Verify response includes tokens in `data.tokens` or `tokens` or `accessToken` directly |

---

## 14. Implementation Priority

### Phase 1 — Critical (Blocks Frontend Completely)

1. **Auth login/refresh** — Correct token response structure with `isSuperAdmin` flag
2. **`GET /v1/businesses`** — Superadmin can list businesses
3. **`GET /v1/businesses/:id`** — Full nested response (users, activityLogs, subscription)
4. **`POST/PUT /v1/plans`** — Plans CRUD (feature flags + limits)
5. **`GET /v1/plans`** — Plans list with subscriber counts
6. **`GET /superadmin/analytics/platform`** — Platform dashboard KPIs

### Phase 2 — High Priority (Core Features)

7. **Plan limit enforcement** on Products and Users creation
8. **Suspend/Reactivate business** with `403` propagation
9. **`GET /v1/analytics/dashboard`** — Business dashboard overview
10. **`GET /v1/settings/business`** + `GET /v1/settings/currency` — Settings context loads correctly
11. **Notifications auto-generation** (low stock, expiry, etc.)

### Phase 3 — Standard Features

12. Products, Categories, Inventory, Orders, Customers CRUD
13. Suppliers, Purchases, Expenses CRUD
14. Refunds + Transactions
15. Messaging module
16. Reports and export endpoints

### Phase 4 — Advanced / Polish

17. `PUT /v1/businesses/:id/overrides` — Superadmin override settings
18. User impersonation token system
19. `GET /v1/plans/comparison` matrix
20. Webhook system (Enterprise plan)
21. `GET /superadmin/settings` + `PUT /superadmin/settings`
22. Currency rates caching and `POST /v1/settings/currency/fetch-rates`

---

*This document was generated from a complete analysis of the StringVentory frontend codebase.*
*All endpoint contracts are derived from actual frontend service calls, normalization models, and context providers.*
*Base URL: `https://stringventory-sass-api.onrender.com`*
