<?php
// Prioritize 'tableId' if passed, then 'id', then generate a unique one.
$tableId = $tableId ?? $id ?? 'table-' . uniqid();
$cardId = 'card-' . $tableId;

// Create a safe JavaScript variable name (replace dashes with underscores)
$jsVar = 'table_' . str_replace('-', '_', $tableId);

// Normalize parameters if using old call style
if (!isset($columns) && isset($header) && isset($data)) {
    $columns = [];
    $dataKeys = array_keys($data);
    foreach ($dataKeys as $index => $key) {
        $colDef = [
            'data' => $key,
            'title' => $header[$index] ?? ucfirst($key)
        ];
        // Merge extra attributes from $data[$key]
        if (is_array($data[$key])) {
            $colDef = array_merge($colDef, $data[$key]);
        }
        $columns[] = $colDef;
    }
}

$url = $url ?? $ajax ?? '';
$showHeader = $showHeader ?? false;
?>
<div class="card stretch stretch-full" id="<?= $cardId ?>">
    <?php if ($showHeader && isset($title)): ?>
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0"><?= $title ?></h5>
        <div class="card-header-action d-flex align-items-center gap-2">
            <?php if (isset($actions)): ?>
                <?= $actions ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="<?= $tableId ?>" style="width:100%">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?= $col['title'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let <?= $jsVar ?>;
    
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded. Please ensure it is included before this script.');
            return;
        }

        <?= $jsVar ?> = $('#<?= $tableId ?>').DataTable({
            processing: true,
            serverSide: true,
            ajax: '<?= $url ?>',
            columns: <?= json_encode($columns) ?>,
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100, 200, 500],
            language: {
                processing: '<i class="feather-rotate-cw fa-spin"></i> Loading...'
            },
            drawCallback: function() {
                this.api().columns.adjust();
            }
        });

        // Handle sidebar toggle resize
        $(document).on('click', '#menu-mini-button, #menu-expend-button', function() {
            setTimeout(function() {
                if (<?= $jsVar ?>) {
                    <?= $jsVar ?>.columns.adjust().responsive.recalc();
                }
            }, 350);
        });

        // Handle window resize
        $(window).on('resize', function() {
            if (<?= $jsVar ?>) {
                <?= $jsVar ?>.columns.adjust();
            }
        });
    });

    function reloadTable_<?= $jsVar ?>() {
        if (typeof <?= $jsVar ?> !== 'undefined') {
            <?= $jsVar ?>.ajax.reload();
        } else {
            console.warn('Table not initialized yet');
        }
    }
    
    // Fallback for older code calling reloadTable_tableRole directly if tableId matches
    <?php if ($tableId === 'tableRole'): ?>
    window.reloadTable_tableRole = reloadTable_<?= $jsVar ?>;
    <?php endif; ?>
</script>
