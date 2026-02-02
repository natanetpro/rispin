<?php

if (!function_exists('check_permission')) {
    /**
     * Checks if the current logged-in user has a specific permission.
     * This bypasses Myth\Auth session caching and queries the database directly
     * to ensure absolute real-time accuracy.
     * 
     * @param string $permission
     * @return bool
     */
    function check_permission($permission) {
        // Load auth helper ensure user_id() is available
        helper('auth');
        
        $user_id = user_id();
        if (!$user_id) return false;

        // Superadmin bypass (Opsional, matikan jika ingin strict total)
        // if (in_groups('superadmin')) return true; 
        
        $db = \Config\Database::connect();

        // 1. Check Direct User Permission
        // (Seringkali permission ditempel langsung ke user, override role)
        $builder = $db->table('auth_users_permissions');
        $builder->join('auth_permissions', 'auth_permissions.id = auth_users_permissions.permission_id');
        $builder->where('user_id', $user_id);
        $builder->where('auth_permissions.name', $permission);
        if ($builder->countAllResults() > 0) {
            return true;
        }

        // 2. Check Role (Group) Permission
        // (Permission didapat dari Group/Role user)
        $builder = $db->table('auth_groups_users');
        $builder->join('auth_groups_permissions', 'auth_groups_permissions.group_id = auth_groups_users.group_id');
        $builder->join('auth_permissions', 'auth_permissions.id = auth_groups_permissions.permission_id');
        $builder->where('auth_groups_users.user_id', $user_id);
        $builder->where('auth_permissions.name', $permission);
        
        return $builder->countAllResults() > 0;
    }
}
