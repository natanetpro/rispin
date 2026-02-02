<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class DebugAuth extends BaseCommand
{
    protected $group       = 'Debug';
    protected $name        = 'debug:auth';
    protected $description = 'Displays user roles and permissions';

    public function run(array $params)
    {
        $db = Database::connect();

        CLI::write("=== DEBUG AUTH permissions ===", 'yellow');

        // 1. Get All Users and their Roles
        CLI::write("\n[1] Users & Roles:", 'green');
        $users = $db->table('users')
            ->select('users.id, users.username, users.email, auth_groups.name as role')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->get()->getResultArray();

        foreach ($users as $u) {
            $role = $u['role'] ?? 'NO ROLE';
            CLI::write("User: " . str_pad($u['username'], 15) . " | Role: " . $role . " (ID: $u[id])");
        }

        // 2. Get Permissions per Role
        CLI::write("\n[2] Roles & Permissions:", 'green');
        $roles = $db->table('auth_groups')->get()->getResultArray();

        foreach ($roles as $r) {
            CLI::write("\nRole: [ " . strtoupper($r['name']) . " ]", 'cyan');
            
            $perms = $db->table('auth_groups_permissions')
                ->select('auth_permissions.name')
                ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
                ->where('group_id', $r['id'])
                ->get()->getResultArray();
                
            if (empty($perms)) {
                CLI::write("  - No permissions assigned.", 'red');
            } else {
                foreach ($perms as $p) {
                    CLI::write("  - " . $p['name']);
                }
            }
        }

        CLI::write("\n==============================", 'yellow');
    }
}
