<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['parent_id', 'title', 'url', 'icon', 'order', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get menu hierarchy as nested array
     */
    public function getTree()
    {
        // 1. Get all active menus ordered by hierarchy and order
        // We order by parent_id ASC so NULLs (roots) come first, but really we just need raw data
        $menus = $this->where('is_active', 1)
                      ->orderBy('parent_id', 'ASC')
                      ->orderBy('order', 'ASC')
                      ->findAll();

        return $this->buildTree($menus);
    }

    /**
     * Recursive function to build tree
     */
    private function buildTree(array $elements, $parentId = null)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                // Find children for this element
                $children = $this->buildTree($elements, $element['id']);
                
                if ($children) {
                    $element['children'] = $children;
                }
                
                $branch[] = $element;
            }
        }

        return $branch;
    }
}
