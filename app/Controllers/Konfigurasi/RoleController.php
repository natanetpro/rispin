<?php

namespace App\Controllers\Konfigurasi;

use App\Controllers\BaseController;
use Myth\Auth\Authorization\GroupModel;
use Hermawan\DataTables\DataTable;

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
                    return '<div class="hstack gap-2 justify-content-end">
                                <a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-info btn-view" data-id="'.$row->id.'"><i class="feather-eye"></i></a>
                                <a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i></a>
                                <a href="javascript:void(0);" class="avatar-text avatar-md bg-soft-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash"></i></a>
                            </div>';
                })->toJson(true);
        }

        return view('pages/konfigurasi/role/index', ['title' => 'Manajemen Role']);
    }

    public function show($id)
    {
        $data = $this->groupModel->find($id);
        return $this->response->setJSON($data ? ['status' => 'success', 'data' => $data] : ['status' => 'error', 'message' => 'Not found']);
    }

    public function create()
    {
        $roleName = $this->request->getPost('role_name');
        
        // Manual Validation
        if (empty($roleName)) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name is required']]);
        }
        
        if (strlen($roleName) < 3) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name must be at least 3 characters']]);
        }
        
        // Manual Unique Check
        if ($this->groupModel->where('name', $roleName)->first()) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name already exists']]);
        }

        $this->groupModel->skipValidation(true)->insert([
            'name' => $roleName,
            'description' => $this->request->getPost('description')
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Role created successfully']);
    }

    public function update($id)
    {
        $roleName = $this->request->getPost('role_name');

        // Manual Validation
        if (empty($roleName)) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name is required']]);
        }
        
        if (strlen($roleName) < 3) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name must be at least 3 characters']]);
        }
        
        // Manual Unique Check (Exclude current ID)
        $existing = $this->groupModel->where('name', $roleName)->first();
        if ($existing && $existing->id != $id) {
            return $this->response->setJSON(['status' => 'error', 'errors' => ['role_name' => 'Role name already exists']]);
        }

        $this->groupModel->skipValidation(true)->update($id, [
            'name' => $roleName,
            'description' => $this->request->getPost('description')
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Role updated successfully']);
    }

    public function delete($id)
    {
        if ($this->groupModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Role deleted successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete role']);
    }
}
