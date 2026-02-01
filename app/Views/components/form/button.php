<?php
/**
 * Button Component
 * Params: type, text, class, id, form, onclick
 */
$type = $type ?? 'button';
$text = $text ?? 'Submit';
$class = $class ?? 'btn-primary';
$id = $id ?? '';
$form = $form ?? '';
?>
<button type="<?= $type ?>" class="btn <?= $class ?>" <?php if($id): ?>id="<?= $id ?>"<?php endif; ?> <?php if($form): ?>form="<?= $form ?>"<?php endif; ?>>
    <?= $text ?>
</button>
