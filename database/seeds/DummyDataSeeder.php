<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DummyDataSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Clear tables to avoid duplicate keys and reset auto-increment counters
        $this->execute("SET FOREIGN_KEY_CHECKS = 0");
        $this->execute("TRUNCATE TABLE subscriptions");
        $this->execute("TRUNCATE TABLE users");
        $this->execute("TRUNCATE TABLE payment_methods");
        $this->execute("TRUNCATE TABLE businesses");
        $this->execute("TRUNCATE TABLE plans");
        $this->execute("SET FOREIGN_KEY_CHECKS = 1");

        // 1. Seed Plans
        $plansTable = $this->table('plans');
        $plansTable->insert([
            [
                'name' => 'Free Plan',
                'description' => 'Basic features for small stores',
                'monthlyPrice' => 0.00,
                'yearlyPrice' => 0.00,
                'trialDays' => 0,
                'maxUsers' => 2,
                'maxProducts' => 100,
                'maxOrdersPerMonth' => 50,
                'maxLocations' => 1,
                'maxStorageMb' => 100
            ],
            [
                'name' => 'Pro Plan',
                'description' => 'Advanced features for growing businesses',
                'monthlyPrice' => 29.99,
                'yearlyPrice' => 299.99,
                'trialDays' => 14,
                'maxUsers' => 10,
                'maxProducts' => 1000,
                'maxOrdersPerMonth' => 500,
                'maxLocations' => 5,
                'maxStorageMb' => 1024,
                'isPopular' => true
            ]
        ])->saveData();

        // 2. Seed Businesses
        $businessesTable = $this->table('businesses');
        $businessesTable->insert([
            [
                'name' => 'Acme Retail',
                'email' => 'contact@acmeretail.com',
                'status' => 'active',
                'domain' => 'acme.store'
            ],
            [
                'name' => 'Global Traders',
                'email' => 'info@globaltraders.com',
                'status' => 'active',
                'domain' => 'global.store'
            ]
        ])->saveData();

        // 3. Seed Subscriptions
        $subscriptionsTable = $this->table('subscriptions');
        $subscriptionsTable->insert([
            [
                'businessId' => 1,
                'planId' => 2, // Pro Plan
                'billingCycle' => 'monthly',
                'mrr' => 29.99,
                'status' => 'active',
                'currentPeriodStart' => date('Y-m-d H:i:s'),
                'currentPeriodEnd' => date('Y-m-d H:i:s', strtotime('+1 month'))
            ],
            [
                'businessId' => 2,
                'planId' => 1, // Free Plan
                'billingCycle' => 'yearly',
                'mrr' => 0.00,
                'status' => 'active',
                'currentPeriodStart' => date('Y-m-d H:i:s'),
                'currentPeriodEnd' => date('Y-m-d H:i:s', strtotime('+1 year'))
            ]
        ])->saveData();

        // 4. Seed Payment Methods (since we cleared them in migration)
        $paymentMethodsTable = $this->table('payment_methods');
        $paymentMethodsTable->insert([
            [
                'name' => 'Credit Card',
                'type' => 'card',
                'enabled' => true,
                'provider' => 'stripe',
                'businessId' => 1
            ],
            [
                'name' => 'Bank Transfer',
                'type' => 'bank',
                'enabled' => true,
                'provider' => 'internal',
                'businessId' => 1
            ],
            [
                'name' => 'Cash',
                'type' => 'cash',
                'enabled' => true,
                'provider' => 'internal',
                'businessId' => 1
            ],
            [
                'name' => 'Credit Card',
                'type' => 'card',
                'enabled' => true,
                'provider' => 'stripe',
                'businessId' => 2
            ],
            [
                'name' => 'Cash',
                'type' => 'cash',
                'enabled' => true,
                'provider' => 'internal',
                'businessId' => 2
            ]
        ])->saveData();

        // 5. Seed Users
        // All passwords are 'password' hashed with Bcrypt
        $passwordHash = password_hash('password', PASSWORD_BCRYPT);

        $usersTable = $this->table('users');
        $usersTable->insert([
            // Super Admins (no businessId)
            [
                'firstName' => 'System',
                'lastName' => 'Admin',
                'email' => 'superadmin@stringventory.com',
                'role' => 'super_admin',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => null
            ],
            [
                'firstName' => 'Support',
                'lastName' => 'Team',
                'email' => 'support@stringventory.com',
                'role' => 'super_admin',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => null
            ],
            // Business 1 Users
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'owner1@acme.com',
                'role' => 'owner',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 1
            ],
            [
                'firstName' => 'Jane',
                'lastName' => 'Manager',
                'email' => 'manager1@acme.com',
                'role' => 'manager',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 1
            ],
            [
                'firstName' => 'Bob',
                'lastName' => 'Sales',
                'email' => 'sales1@acme.com',
                'role' => 'salesperson',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 1
            ],
            // Business 2 Users
            [
                'firstName' => 'Alice',
                'lastName' => 'Smith',
                'email' => 'owner2@global.com',
                'role' => 'owner',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 2
            ],
            [
                'firstName' => 'Charlie',
                'lastName' => 'Manager',
                'email' => 'manager2@global.com',
                'role' => 'manager',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 2
            ],
            [
                'firstName' => 'Eve',
                'lastName' => 'Sales',
                'email' => 'sales2@global.com',
                'role' => 'salesperson',
                'status' => 'active',
                'passwordHash' => $passwordHash,
                'businessId' => 2
            ]
        ])->saveData();
    }
}
