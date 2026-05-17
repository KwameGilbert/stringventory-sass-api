<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class ConvertSuperAdminToRole extends AbstractMigration
{
    public function up(): void
    {
        // 1. Update role enum to include super_admin
        $this->table('users')
            ->changeColumn('role', 'enum', ['values' => ['super_admin', 'owner', 'ceo', 'manager', 'salesperson'], 'default' => 'salesperson'])
            ->update();

        // 2. Backfill data: set role to super_admin for users where isSuperAdmin was true
        $this->execute("UPDATE users SET role = 'super_admin' WHERE isSuperAdmin = 1");

        // 3. Drop isSuperAdmin column
        $this->table('users')
            ->removeColumn('isSuperAdmin')
            ->update();
    }

    public function down(): void
    {
        // 1. Add isSuperAdmin column back
        $this->table('users')
            ->addColumn('isSuperAdmin', 'boolean', ['default' => false, 'after' => 'businessId'])
            ->update();

        // 2. Restore data: set isSuperAdmin to true where role is super_admin
        $this->execute("UPDATE users SET isSuperAdmin = 1 WHERE role = 'super_admin'");

        // 3. Reset role for super admins to salesperson (or another valid role) before changing enum
        $this->execute("UPDATE users SET role = 'salesperson' WHERE role = 'super_admin'");

        // 4. Revert role enum
        $this->table('users')
            ->changeColumn('role', 'enum', ['values' => ['owner', 'ceo', 'manager', 'salesperson'], 'default' => 'salesperson'])
            ->update();
    }
}
