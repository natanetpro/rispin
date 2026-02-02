<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Myth\Auth\Models\GroupModel;
use Myth\Auth\Models\PermissionModel;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $groupModel = new GroupModel();
        $permissionModel = new PermissionModel();
        $db = \Config\Database::connect();

        // KONFIGURASI RESOURCE & PERMISSIONS
        // Format:
        // 1. 'resource_name' (String) -> Akan menggunakan DEFAULT actions (view, create, edit, delete)
        // 2. 'resource_name' => ['action1', 'action2'] -> Akan menggunakan custom actions
        
        $defaultActions = ['view', 'create', 'edit', 'delete'];
        
        $resourcesConfig = [
            'dashboard' => ['view'],                                     // Custom: Cuma view
            'user', // Custom: +Import
            'profile' => ['view', 'edit'],                             // Custom: Edit only
            'role',                                                      // Default
            'menu',                                                      // Default
        ];

        // 1. Generate Permissions List
        $finalPermissions = [];
        
        foreach ($resourcesConfig as $key => $val) {
            if (is_int($key)) {
                $resource = $val;
                $actions  = $defaultActions;
            } else {
                $resource = $key;
                $actions  = $val;
            }

            foreach ($actions as $action) {
                // e.g., view_dashboard, import_user
                $permissionName = $action . '_' . $resource; 
                $description    = ucfirst($action) . ' ' . ucfirst($resource);
                
                $finalPermissions[$permissionName] = $description;
            }
        }

        // 2. Sync Permissions (Create new, Delete obsolete)
        
        // 2a. Get all existing permissions from DB
        $existingPerms = $permissionModel->findAll();
        $existingNames = array_map(fn($p) => $p->name, $existingPerms);
        
        // 2b. Insert NEW permissions
        foreach ($finalPermissions as $name => $desc) {
            if (!in_array($name, $existingNames)) {
                $permissionModel->skipValidation(true)->insert([
                    'name'        => $name,
                    'description' => $desc
                ]);
                echo "\033[32m[+] Created Permission:\033[0m $name\n";
            }
        }
        
        // 2c. DELETE obsolete permissions (not in config anymore)
        $configNames = array_keys($finalPermissions);
        foreach ($existingPerms as $perm) {
            if (!in_array($perm->name, $configNames)) {
                // Delete from pivot table first (foreign key)
                $db->table('auth_groups_permissions')->where('permission_id', $perm->id)->delete();
                $db->table('auth_users_permissions')->where('permission_id', $perm->id)->delete();
                
                // Delete permission itself
                $permissionModel->delete($perm->id);
                echo "\033[31m[-] Deleted Permission:\033[0m $perm->name\n";
            }
        }

        // 3. Create Default Roles & Assign Permissions
        
        // --- SUPERADMIN : All Permissions ---
        $superadminId = $this->createRole($groupModel, 'superadmin', 'Super Administrator');
        $allPerms = $permissionModel->findAll();
        foreach ($allPerms as $perm) {
            $this->assignPermission($db, $superadminId, $perm->id);
        }

        // --- ADMIN : User, Role, Menu, Dashboard (Example) ---
        $adminId = $this->createRole($groupModel, 'admin', 'Administrator');
        
        // Ambil permission yang mengandung kata kunci tertentu
        // Note: Logic ini hanya contoh assignment otomatis. Anda bisa manual assign lewat UI nanti.
        $adminPerms = $permissionModel->groupStart()
                        ->like('name', 'user')
                        ->orLike('name', 'role')
                        ->orLike('name', 'menu')
                        ->orLike('name', 'dashboard')
                        ->groupEnd()
                        ->findAll();
                        
        foreach ($adminPerms as $perm) {
            $this->assignPermission($db, $adminId, $perm->id);
        }

        // --- USER : Dashboard, Profile ---
        $userId = $this->createRole($groupModel, 'user', 'Regular User');
        $userPerms = $permissionModel->groupStart()
                        ->like('name', 'dashboard')
                        ->orLike('name', 'profile')
                        ->groupEnd()
                        ->findAll();
                        
        foreach ($userPerms as $perm) {
            $this->assignPermission($db, $userId, $perm->id);
        }
    }

    private function createRole($model, $name, $desc) {
        $role = $model->where('name', $name)->first();
        if (!$role) {
            $id = $model->skipValidation(true)->insert(['name' => $name, 'description' => $desc]);
            echo "\033[36m[+] Created Role:\033[0m $name\n";
            return $id;
        }
        return $role->id;
    }

    private function assignPermission($db, $groupId, $permId) {
        $exists = $db->table('auth_groups_permissions')
                     ->where('group_id', $groupId)
                     ->where('permission_id', $permId)
                     ->countAllResults();
                     
        if ($exists == 0) {
            $db->table('auth_groups_permissions')->insert([
                'group_id' => $groupId,
                'permission_id' => $permId
            ]);
            // echo " [>] Assigned Perm ID $permId to Group ID $groupId\n";
        }
    }
}
