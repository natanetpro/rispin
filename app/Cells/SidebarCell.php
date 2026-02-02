<?php

namespace App\Cells;

use App\Models\MenuModel;

class SidebarCell
{
    public function render()
    {
        helper(['auth', 'permission']); // Load helper here
        $model = new MenuModel();
        $menuTree = $model->getTree();

        // Filter permissions logic
        $filteredMenu = $this->filterMenu($menuTree);

        return view('components/layout/sidebar', ['menu' => $filteredMenu]);
    }

    private function filterMenu($items)
    {
        $result = [];

        foreach ($items as $item) {
            // 1. Check Permission for this item
            if (!empty($item['permission_name'])) {
                // Use robust check_permission helper
                if (!check_permission($item['permission_name'])) {
                    continue; // Skip if doesn't have permission
                }
            }
            
            // 2. Filter Children (Recursive)
            if (isset($item['children']) && !empty($item['children'])) {
                $item['children'] = $this->filterMenu($item['children']);
                
                // 3. Cleanup: If parent is just a grouper (URL='#') and has no children left, remove it
                if ($item['url'] === '#' && empty($item['children'])) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }
}
