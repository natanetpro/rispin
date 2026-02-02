<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class DebugMenus extends BaseCommand
{
    protected $group       = 'Debug';
    protected $name        = 'debug:menus';
    protected $description = 'Displays menu items and their permission requirements';

    public function run(array $params)
    {
        $db = Database::connect();

        CLI::write("=== MENU PERMISSION STATUS ===", 'yellow');
        CLI::write(str_pad("ID", 5) . str_pad("Title", 25) . "Permission", 'white');
        CLI::write(str_repeat("-", 60));

        $menus = $db->table('menus')->select('id, title, permission_name')->orderBy('id')->get()->getResultArray();

        foreach ($menus as $m) {
            $perm = $m['permission_name'] ?: '(none - PUBLIC)';
            $color = $m['permission_name'] ? 'green' : 'red';
            
            CLI::write(
                str_pad($m['id'], 5) . 
                str_pad($m['title'], 25) . 
                CLI::color($perm, $color)
            );
        }

        CLI::write(str_repeat("-", 60));
        CLI::write("Menus with (none - PUBLIC) will be visible to everyone.", 'light_gray');
    }
}
