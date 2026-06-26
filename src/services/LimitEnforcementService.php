<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Customer;
use App\Services\TenantContext;

class LimitEnforcementService
{
    /**
     * Checks if a specific limit key has been exceeded for the active business.
     */
    public function checkLimit(string $limitKey, int $currentCount): bool
    {
        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            // Superadmins don't have a business context and generally shouldn't create business data,
            // but if they do, we can't check limits against a specific plan.
            return true;
        }

        $business = Business::with('subscription.plan')->find($tenantId);
        if (!$business || !$business->subscription || !$business->subscription->plan) {
            // If no plan is assigned, assume limits are exceeded to enforce plan selection
            return false;
        }

        $plan = $business->subscription->plan;
        
        // If the limit in the plan is -1 or null, assume unlimited
        if (!isset($plan->$limitKey) || $plan->$limitKey === -1) {
            return true;
        }

        return $currentCount < $plan->$limitKey;
    }

    public function canCreateProduct(): bool
    {
        return $this->checkLimit('maxProducts', Product::count());
    }

    public function canCreateUser(): bool
    {
        return $this->checkLimit('maxUsers', User::count());
    }

    public function canCreateOrder(): bool
    {
        // For orders, limit is per month
        $currentMonthCount = Order::whereMonth('createdAt', date('m'))
                                  ->whereYear('createdAt', date('Y'))
                                  ->count();
        return $this->checkLimit('maxOrdersPerMonth', $currentMonthCount);
    }

    public function canCreateCategory(): bool
    {
        return $this->checkLimit('maxCategories', Category::count());
    }

    public function canCreateSupplier(): bool
    {
        return $this->checkLimit('maxSuppliers', Supplier::count());
    }

    public function canCreateCustomer(): bool
    {
        return $this->checkLimit('maxCustomers', Customer::count());
    }
}
