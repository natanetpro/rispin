<!-- Modal Component -->
<div class="modal fade" id="<?= $id ?>" tabindex="-1" aria-labelledby="<?= $id ?>Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-<?= $size ?? 'md' ?> modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $id ?>Label"><?= $title ?? 'Modal Title' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($contentView) && !empty($contentView)): ?>
                    <?= view($contentView, $contentData ?? []) ?>
                <?php else: ?>
                    <?= $slot ?? '' ?>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php if (!isset($hideSubmit) || !$hideSubmit): ?>
                    <button type="submit" form="<?= $formId ?? 'modalForm' ?>" class="btn btn-primary" id="<?= $saveBtnId ?? 'btnSave' ?>">Save</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Fix modal z-index issue by moving modal to body
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('<?= $id ?>');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
    });
</script>
