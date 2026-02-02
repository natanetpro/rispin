<?php

// Load CodeIgniter Framework
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Config\Database;

$db = Database::connect();

echo "\n=== DEBUG AUTH permissions ===\n";

// 1. Get All Users and their Roles
echo "\n[1] Users & Roles:\n";
$users = $db->table('users')
    ->select('users.id, users.username, users.email, auth_groups.name as role')
    ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
    ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
    ->get()->getResultArray();

foreach ($users as $u) {
    echo "User: " . str_pad($u['username'], 15) . " | Role: " . ($u['role'] ?? 'NO ROLE') . " (ID: $u[id])\n";
}

// 2. Get Permissions per Role
echo "\n[2] Roles & Permissions:\n";
$roles = $db->table('auth_groups')->get()->getResultArray();

foreach ($roles as $r) {
    echo "\nRole: [ " . strtoupper($r['name']) . " ]\n";
    
    $perms = $db->table('auth_groups_permissions')
        ->select('auth_permissions.name')
        ->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id')
        ->where('group_id', $r['id'])
        ->get()->getResultArray();
        
    if (empty($perms)) {
        echo "  - No permissions assigned.\n";
    } else {
        foreach ($perms as $p) {
            echo "  - " . $p['name'] . "\n";
        }
    }
}

echo "\n==============================\n";
