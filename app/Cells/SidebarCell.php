<?php

namespace App\Cells;

use App\Models\MenuModel;

class SidebarCell
{
    public function render()
    {
        $model = new MenuModel();
        
        $menuTree = $model->getTree();

        return view('components/layout/sidebar', ['menu' => $menuTree]);
    }
}
