<?php

namespace App\Controllers\Konfigurasi;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Helpers\ResponseFormatter;

class MenuController extends BaseController
{
    protected $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
    }

    public function index()
    {
        // Ambil data menu yang sudah terstruktur (Tree)
        // Kita bisa pakai library recursive model atau manual fetch
        // Untuk view nestable, lebih mudah fetch all ordered by urutan, lalu group by parent di JS atau PHP
        
        $menuItems = $this->menuModel->orderBy('order', 'ASC')->findAll();
        
        // Get Permissions List for Dropdown
        $db = \Config\Database::connect();
        $permissions = $db->table('auth_permissions')->orderBy('name', 'ASC')->get()->getResultArray();

        return view('pages/konfigurasi/menu/index', [
            'menuItems' => $menuItems,
            'permissions' => $permissions
        ]);
    }

    public function show($id)
    {
        $data = $this->menuModel->find($id);
        return $data ? ResponseFormatter::success($data) : ResponseFormatter::error(null, 'Not Found', 404);
    }

    public function create()
    {
        $data = [
            'title' => $this->request->getPost('title'),
            'url' => $this->request->getPost('url'),
            'icon' => $this->request->getPost('icon'),
            'permission_name' => $this->request->getPost('permission_name') ?: null,
            'is_active' => 1,
            'parent_id' => null, // Default root (NULL, not 0)
            'order' => 99 // Taruh paling bawah
        ];

        if (!$this->menuModel->insert($data)) {
            return ResponseFormatter::error($this->menuModel->errors());
        }

        return ResponseFormatter::success(null, 'Menu added successfully');
    }

    public function update($id)
    {
        // Validation Logic for Parent Menu
        $newUrl = $this->request->getPost('url');
        
        // Cek apakah menu ini punya children?
        $hasChildren = $this->menuModel->where('parent_id', $id)->countAllResults() > 0;
        
        if ($hasChildren && $newUrl !== '#' && $newUrl !== 'javascript:void(0);') {
            // Return error spesifik field 'url' agar muncul inline
            return ResponseFormatter::error([
                'url' => 'Cannot change URL of a parent menu! It must remain "#".'
            ], 'Validation Failed', 400); 
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'url' => $newUrl,
            'icon' => $this->request->getPost('icon'),
            'permission_name' => $this->request->getPost('permission_name') ?: null
        ];

        if (!$this->menuModel->update($id, $data)) {
            return ResponseFormatter::error($this->menuModel->errors());
        }

        return ResponseFormatter::success(null, 'Menu updated successfully');
    }

    public function delete($id)
    {
        // Recursive Delete: Hapus semua keturunan dulu
        $this->deleteChildren($id);

        if ($this->menuModel->delete($id)) {
            return ResponseFormatter::success(null, 'Menu deleted successfully');
        }
        return ResponseFormatter::error(null, 'Delete failed');
    }

    private function deleteChildren($parentId) {
        $children = $this->menuModel->where('parent_id', $parentId)->findAll();
        foreach($children as $child) {
            $this->deleteChildren($child['id']); // Recursion
            $this->menuModel->delete($child['id']);
        }
    }

    // API Khusus: Simpan Urutan (Drag & Drop Result)
    public function saveOrder()
    {
        $json = $this->request->getJSON(true); // Terima JSON Array
        
        if (empty($json)) return ResponseFormatter::error(null, 'Invalid Data');

        // Mulai update dengan parentId NULL (untuk root items)
        $this->updateTree($json, null);

        return ResponseFormatter::success(null, 'Menu structure saved successfully');
    }

    // Rekursif Helper untuk update parent_id & order
    private function updateTree($items, $parentId)
    {
        foreach ($items as $index => $item) {
            // Pastikan jika parentId 0 (dari JS client root), diubah jadi NULL
            $pid = ($parentId === 0 || $parentId === '0') ? null : $parentId;

            $data = [
                'parent_id' => $pid,
                'order' => $index + 1
            ];

            // AUTO-URL LOGIC: Jika punya anak, url harus # (dropdown)
            if (isset($item['children']) && !empty($item['children'])) {
                $data['url'] = '#';
            }

            $this->menuModel->update($item['id'], $data);

            if (isset($item['children']) && !empty($item['children'])) {
                $this->updateTree($item['children'], $item['id']);
            }
        }
    }
}
