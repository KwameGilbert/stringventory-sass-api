<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class FixMissingColumnsForDeployment extends AbstractMigration
{
    /**
     * Up Method.
     */
    public function up(): void
    {
        // 1. Check and add 'phone' column to 'users' table
        $users = $this->table('users');
        if ($users->exists()) {
            if (!$users->hasColumn('phone')) {
                $users->addColumn('phone', 'string', [
                    'limit' => 50,
                    'null' => true,
                    'default' => null,
                    'after' => 'email'
                ])->update();
                echo "Added 'phone' column to 'users' table.\n";
            } else {
                echo "'phone' column already exists in 'users' table. Skipping.\n";
            }
        }

        // 2. Check and add 'user_id' column to 'audit_logs' table
        $auditLogs = $this->table('audit_logs');
        if ($auditLogs->exists()) {
            if (!$auditLogs->hasColumn('user_id')) {
                $auditLogs->addColumn('user_id', 'integer', [
                    'signed' => false,
                    'null' => true,
                    'default' => null,
                    'after' => 'id'
                ])->update();
                echo "Added 'user_id' column to 'audit_logs' table.\n";
            } else {
                echo "'user_id' column already exists in 'audit_logs' table. Skipping.\n";
            }
        }
    }

    /**
     * Down Method.
     */
    public function down(): void
    {
        $users = $this->table('users');
        if ($users->exists() && $users->hasColumn('phone')) {
            $users->removeColumn('phone')->update();
            echo "Removed 'phone' column from 'users' table.\n";
        }

        $auditLogs = $this->table('audit_logs');
        if ($auditLogs->exists() && $auditLogs->hasColumn('user_id')) {
            $auditLogs->removeColumn('user_id')->update();
            echo "Removed 'user_id' column from 'audit_logs' table.\n";
        }
    }
}
