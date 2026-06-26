<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TenantContext
 * 
 * Stores the current request's tenant state (businessId) so that
 * Eloquent models and global scopes can access it without having
 * to manually pass it down from controllers.
 */
class TenantContext
{
    private static ?int $tenantId = null;
    private static bool $isSuperAdmin = false;

    /**
     * Set the current tenant ID for the request cycle.
     */
    public static function setTenantId(?int $id): void
    {
        self::$tenantId = $id;
    }

    /**
     * Get the current tenant ID.
     */
    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * Set whether the current user is a superadmin.
     */
    public static function setIsSuperAdmin(bool $isSuperAdmin): void
    {
        self::$isSuperAdmin = $isSuperAdmin;
    }

    /**
     * Check if the current user is a superadmin.
     */
    public static function isSuperAdmin(): bool
    {
        return self::$isSuperAdmin;
    }

    /**
     * Clear the context (useful for testing or long-running processes).
     */
    public static function clear(): void
    {
        self::$tenantId = null;
        self::$isSuperAdmin = false;
    }
}
