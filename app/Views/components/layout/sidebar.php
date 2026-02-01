    <?php
    // Helper function for recursive menu rendering
    if (!function_exists('renderMenu')) {
        function renderMenu($items) {
            foreach ($items as $item) {
                // Determine if item has submenu
                $hasSubmenu = isset($item['children']) && !empty($item['children']);
                
                // Determine URL
                $url = 'javascript:void(0);';
                // Access 'url' key instead of 'nama_url'
                if (isset($item['url']) && !empty($item['url'])) {
                    if ($item['url'] === 'javascript:void(0);' || $item['url'] === '#') {
                        $url = $item['url'];
                    } else {
                        // If default dot icon, assume it's a submenu item or standard item, handle base_url
                        $url = base_url($item['url']);
                    }
                }
                
                // Determine icon. If 'dot', usually it means specific class or just simple circle
                // The request asked for 'dot' as icon. We can map 'dot' to 'feather-circle' or use it as is if CSS supports it.
                // Assuming 'dot' maps to a small circle icon.
                $icon = !empty($item['icon']) ? $item['icon'] : 'feather-circle';
                if ($icon === 'dot') {
                    $iconClass = 'feather-circle'; // Map 'dot' to standard circle icon for now
                    // Or if you want a tiny dot style:
                    // $iconClass = 'feather-circle'; // adjust size in CSS if needed
                } else {
                    $iconClass = $icon;
                }

                echo '<li class="nxl-item ' . ($hasSubmenu ? 'nxl-hasmenu' : '') . '">';
                echo '<a href="' . $url . '" class="nxl-link">';
                
                // Icon rendering
                // Only render icon if it's a root menu OR if specific requirement asks for it.
                // Usually sidebar items have icons.
                echo '<span class="nxl-micon"><i class="' . $iconClass . '"></i></span>';
                
                // Title rendering: Access 'title' instead of 'nama_modul'
                echo '<span class="nxl-mtext">' . $item['title'] . '</span>';
                
                if ($hasSubmenu) {
                    echo '<span class="nxl-arrow"><i class="feather-chevron-right"></i></span>';
                }
                
                echo '</a>';

                if ($hasSubmenu) {
                    echo '<ul class="nxl-submenu">';
                    renderMenu($item['children']);
                    echo '</ul>';
                }

                echo '</li>';
            }
        }
    }
    ?>

    <nav class="nxl-navigation">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="<?= base_url() ?>" class="b-brand">
                    <!-- ========   change your logo hear   ============ -->
                    <img src="<?= base_url('assets/images/logo-full.png') ?>" alt="" class="logo logo-lg" />
                    <img src="<?= base_url('assets/images/logo-abbr.png') ?>" alt="" class="logo logo-sm" />
                </a>
            </div>
            <div class="navbar-content">
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Navigation</label>
                    </li>
                    <?php if (isset($menu) && is_array($menu)) renderMenu($menu); ?>
                </ul>
            </div>
        </div>
    </nav>
