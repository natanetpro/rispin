<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermissionNameToMenus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('menus', [
            'permission_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_active' // Posisi setelah is_active
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('menus', 'permission_name');
    }
}
