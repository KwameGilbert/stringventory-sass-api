<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class StandardizePaymentMethods extends AbstractMigration
{
    public function change(): void
    {
        // The original code tried to change the column type of 'id' to integer auto_increment,
        // which fails in MySQL when data exists or due to primary key constraints.
        // Since there are no foreign keys pointing to this table yet (this migration adds them),
        // it is safer to drop and recreate the table with the correct schema.
        
        if ($this->hasTable('payment_methods')) {
            $this->table('payment_methods')->drop()->update();
        }

        $this->table('payment_methods', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('type', 'string', ['limit' => 50])
            ->addColumn('enabled', 'boolean', ['default' => true])
            ->addColumn('provider', 'string', ['limit' => 50, 'default' => 'internal'])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Add paymentMethodId to expenses
        $expenses = $this->table('expenses');
        if (!$expenses->hasColumn('paymentMethodId')) {
            $expenses->addColumn('paymentMethodId', 'integer', ['signed' => false, 'null' => true, 'after' => 'reference'])
                ->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // Add paymentMethodId to expenseSchedules
        $expenseSchedules = $this->table('expenseSchedules');
        if (!$expenseSchedules->hasColumn('paymentMethodId')) {
            $expenseSchedules->addColumn('paymentMethodId', 'integer', ['signed' => false, 'null' => true, 'after' => 'isActive'])
                ->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // Update purchases: change paymentMethod (string) to paymentMethodId (int)
        $purchases = $this->table('purchases');
        if ($purchases->hasColumn('paymentMethod')) {
            $purchases->removeColumn('paymentMethod')->update();
        }
        if (!$purchases->hasColumn('paymentMethodId')) {
            $purchases->addColumn('paymentMethodId', 'integer', ['signed' => false, 'null' => true, 'after' => 'paymentStatus'])
                ->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // Update refunds: change paymentMethod (string) to paymentMethodId (int)
        $refunds = $this->table('refunds');
        if ($refunds->hasColumn('paymentMethod')) {
            $refunds->removeColumn('paymentMethod')->update();
        }
        if (!$refunds->hasColumn('paymentMethodId')) {
            $refunds->addColumn('paymentMethodId', 'integer', ['signed' => false, 'null' => true, 'after' => 'refundType'])
                ->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // Ensure transactions table is correct
        $transactions = $this->table('transactions');
        // If it has paymentMethod string, remove it
        if ($transactions->hasColumn('paymentMethod')) {
            $transactions->removeColumn('paymentMethod')->update();
        }
        // Ensure paymentMethodId exists and has FK
        if (!$transactions->hasColumn('paymentMethodId')) {
            $transactions->addColumn('paymentMethodId', 'integer', ['signed' => false, 'null' => true, 'after' => 'transactionType'])
                ->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        } else {
            // Ensure foreign key exists
            $foreignKeys = $transactions->getForeignKeys();
            $hasFk = false;
            foreach ($foreignKeys as $fk) {
                if ($fk->getColumns() === ['paymentMethodId']) {
                    $hasFk = true;
                    break;
                }
            }
            if (!$hasFk) {
                $transactions->addForeignKey('paymentMethodId', 'payment_methods', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                    ->update();
            }
        }
    }
}
