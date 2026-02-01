<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('menus');
        
        // Untuk PostgreSQL, gunakan RESTART IDENTITY CASCADE untuk reset auto increment dan handle FK
        $db->query('TRUNCATE TABLE menus RESTART IDENTITY CASCADE');

        // 1. Insert Menu Utama (Root)
        $menus = [
            [
                'title'     => 'Dashboard',
                'url'       => 'dashboard',
                'icon'      => 'dot', 
                'order'     => 1,
                'parent_id' => null,
            ],
            [
                'title'     => 'Master',
                'url'       => '#',
                'icon'      => 'dot',
                'order'     => 2,
                'parent_id' => null,
            ],
            [
                'title'     => 'Konfigurasi',
                'url'       => '#',
                'icon'      => 'dot',
                'order'     => 3,
                'parent_id' => null,
            ],
        ];

        // Array untuk menyimpan ID dari menu parent yang baru dibuat
        $menuIds = [];

        foreach ($menus as $menu) {
            // Bypass validasi model, insert raw data
            $builder->insert($menu);
            $newId = $db->insertID();
            $menuIds[$menu['title']] = $newId;
            
            CLI::write("Menu dibuat: {$menu['title']}", 'green');
        }

        // 2. Insert Sub Menu
        $subMenus = [
            // Master -> Pengguna
            [
                'title'     => 'Pengguna',
                'url'       => 'master/pengguna',
                'icon'      => 'dot',
                'order'     => 1,
                'parent_id' => $menuIds['Master'],
            ],
            // Konfigurasi -> Role
            [
                'title'     => 'Role',
                'url'       => 'konfigurasi/role',
                'icon'      => 'dot',
                'order'     => 1,
                'parent_id' => $menuIds['Konfigurasi'],
            ],
            // Konfigurasi -> Menu
            [
                'title'     => 'Menu',
                'url'       => 'konfigurasi/menu',
                'icon'      => 'dot',
                'order'     => 2,
                'parent_id' => $menuIds['Konfigurasi'],
            ],
        ];

        foreach ($subMenus as $subMenu) {
            $builder->insert($subMenu);
            CLI::write("Submenu dibuat: {$subMenu['title']}", 'green');
        }
    }
}
