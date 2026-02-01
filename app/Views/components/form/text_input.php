<?php
/**
 * Text Input Component
 * Params: type, name, id, value, label, placeholder, required, disabled, class
 */
$id = $id ?? $name;
$type = $type ?? 'text';
$value = $value ?? '';
$label = $label ?? '';
$class = $class ?? '';
$placeholder = $placeholder ?? '';
$required = isset($required) && $required ? 'required' : '';
$disabled = isset($disabled) && $disabled ? 'disabled' : '';
?>
<div class="mb-3">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="form-label"><?= $label ?> <?php if($required): ?><span class="text-danger">*</span><?php endif; ?></label>
    <?php endif; ?>
    <input type="<?= $type ?>" class="form-control <?= $class ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= esc($value) ?>" placeholder="<?= $placeholder ?>" <?= $required ?> <?= $disabled ?>>
    <div class="invalid-feedback"></div>
</div>
