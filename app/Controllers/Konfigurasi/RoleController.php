<?php

namespace App\Controllers\Konfigurasi;

use App\Controllers\BaseController;
use Myth\Auth\Authorization\GroupModel;
use Hermawan\DataTables\DataTable;
use App\Helpers\ResponseFormatter;

class RoleController extends BaseController
{
    protected $groupModel;

    public function __construct()
    {
        $this->groupModel = new GroupModel();
    }

    public function index()
    {
        if ($this->request->isAJAX()) {
            return DataTable::of($this->groupModel->builder()->select('id, name, description'))
                ->add('action', function($row){
                    $perms = [
                        'edit' => check_permission('edit_role'),
                        'delete' => check_permission('delete_role'),
                        'view' => check_permission('view_role')
                    ];

                    $btnPerm = $perms['edit'] 
                        ? '<a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-warning btn-permission" data-id="'.$row->id.'" title="Assign Permission"><i class="feather-key"></i></a>' 
                        : '<a href="javascript:void(0);" class="avatar-text avatar-md bg-light text-muted" style="cursor:not-allowed; opacity:0.6" title="Access Denied"><i class="feather-key"></i></a>';
                    
                    $btnView = $perms['view'] 
                        ? '<a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-info btn-view" data-id="'.$row->id.'"><i class="feather-eye"></i></a>' 
                        : '<a href="javascript:void(0);" class="avatar-text avatar-md bg-light text-muted" style="cursor:not-allowed; opacity:0.6"><i class="feather-eye"></i></a>';

                    $btnEdit = $perms['edit'] 
                        ? '<a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i></a>' 
                        : '<a href="javascript:void(0);" class="avatar-text avatar-md bg-light text-muted" style="cursor:not-allowed; opacity:0.6"><i class="feather-edit"></i></a>';

                    $btnDelete = $perms['delete'] 
                        ? '<a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash"></i></a>' 
                        : '<a href="javascript:void(0);" class="avatar-text avatar-md bg-light text-muted" style="cursor:not-allowed; opacity:0.6"><i class="feather-trash"></i></a>';

                    return '<div class="hstack gap-2 justify-content-end">' . $btnPerm . $btnView . $btnEdit . $btnDelete . '</div>';
                })->toJson(true);
        }

        return view('pages/konfigurasi/role/index', ['title' => 'Manajemen Role']);
    }

    public function show($id)
    {
        $data = $this->groupModel->find($id);
        
        if ($data) {
            return ResponseFormatter::success($data);
        } else {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
    }

    public function create()
    {
        $roleName = $this->request->getPost('role_name');
        
        // Manual Validation
        if (empty($roleName)) {
            return ResponseFormatter::error(['role_name' => 'Role name is required'], 'Validation Failed');
        }
        
        if (strlen($roleName) < 3) {
            return ResponseFormatter::error(['role_name' => 'Role name must be at least 3 characters'], 'Validation Failed');
        }
        
        // Manual Unique Check
        if ($this->groupModel->where('name', $roleName)->first()) {
            return ResponseFormatter::error(['role_name' => 'Role name already exists'], 'Validation Failed');
        }

        $this->groupModel->skipValidation(true)->insert([
            'name' => $roleName,
            'description' => $this->request->getPost('description')
        ]);

        return ResponseFormatter::success(null, 'Role created successfully', 201);
    }

    public function update($id)
    {
        $roleName = $this->request->getPost('role_name');

        // Manual Validation
        if (empty($roleName)) {
            return ResponseFormatter::error(['role_name' => 'Role name is required'], 'Validation Failed');
        }
        
        if (strlen($roleName) < 3) {
            return ResponseFormatter::error(['role_name' => 'Role name must be at least 3 characters'], 'Validation Failed');
        }
        
        // Manual Unique Check (Exclude current ID)
        $existing = $this->groupModel->where('name', $roleName)->first();
        if ($existing && $existing->id != $id) {
            return ResponseFormatter::error(['role_name' => 'Role name already exists'], 'Validation Failed');
        }

        $this->groupModel->skipValidation(true)->update($id, [
            'name' => $roleName,
            'description' => $this->request->getPost('description')
        ]);

        return ResponseFormatter::success(null, 'Role updated successfully');
    }

    public function delete($id)
    {
        if ($this->groupModel->delete($id)) {
            return ResponseFormatter::success(null, 'Role deleted successfully');
        }
        return ResponseFormatter::error(null, 'Failed to delete role', 500);
    }

    // --- Permission Management Methods ---

    public function permissions($id)
    {
        $role = $this->groupModel->find($id);
        if (!$role) return ResponseFormatter::error(null, 'Role not found', 404);

        $db = \Config\Database::connect();

        // 1. Get All Permissions & Group by 'Resource' (Suffix)
        // Assumption: format is action_resource (view_dashboard)
        $allPerms = $db->table('auth_permissions')->orderBy('id', 'asc')->get()->getResultArray();
        
        $groupedPerms = [];
        foreach ($allPerms as $perm) {
            $parts = explode('_', $perm['name'], 2); // Split view_dashboard -> view, dashboard
            $resource = count($parts) > 1 ? ucfirst($parts[1]) : 'Other'; // Dashboard
            
            $groupedPerms[$resource][] = $perm;
        }

        // 2. Get Assigned Permissions for this Role (ID only)
        $assigned = $db->table('auth_groups_permissions')
                       ->select('permission_id')
                       ->where('group_id', $id)
                       ->get()
                       ->getResultArray();
        $assignedIds = array_column($assigned, 'permission_id');

        return ResponseFormatter::success([
            'role' => $role,
            'grouped_permissions' => $groupedPerms,
            'assigned_ids' => $assignedIds
        ]);
    }

    public function updatePermissions($id)
    {
        $role = $this->groupModel->find($id);
        if (!$role) return ResponseFormatter::error(null, 'Role not found', 404);

        $permissionIds = $this->request->getPost('permissions') ?? []; // Array of IDs
        
        $db = \Config\Database::connect();
        
        // Transaction safety
        $db->transStart();

        // 1. Delete old permissions
        $db->table('auth_groups_permissions')->where('group_id', $id)->delete();

        // 2. Insert new permissions
        if (!empty($permissionIds)) {
            $data = [];
            foreach ($permissionIds as $pid) {
                $data[] = [
                    'group_id' => (int)$id,
                    'permission_id' => (int)$pid
                ];
            }
            $db->table('auth_groups_permissions')->insertBatch($data);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $error = $db->error();
            return ResponseFormatter::error(null, 'Failed to update permissions: ' . $error['message'], 500);
        }

        return ResponseFormatter::success(null, 'Permissions updated successfully');
    }
}
