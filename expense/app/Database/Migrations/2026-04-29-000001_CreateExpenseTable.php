<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'card', 'bank_transfer', 'other'],
                'default'    => 'cash',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expense_date' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('category');
        $this->forge->addKey('expense_date');

        $this->forge->createTable('expense');
    }

    public function down(): void
    {
        $this->forge->dropTable('expense');
    }
}
