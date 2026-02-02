<form id="menuForm">
    <input type="hidden" name="id" id="id">
    
    <div class="mb-3">
        <label class="form-label">Menu Title</label>
        <input type="text" class="form-control" name="title" id="title" placeholder="e.g. Dashboard" required>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">URL Path</label>
        <div class="input-group">
            <span class="input-group-text"><?= base_url() ?>/</span>
            <input type="text" class="form-control" name="url" id="url" placeholder="dashboard">
        </div>
        <div class="form-text">Leave empty for parent menu with dropdown</div>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Icon Class</label>
        <input type="text" class="form-control" name="icon" id="icon" placeholder="feather-circle">
        <div class="form-text">Check <a href="https://feathericons.com/" target="_blank">Feather Icons</a> for reference</div>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label class="form-label">Permission Requirement (Optional)</label>
        <select class="form-select form-select2" name="permission_name" id="permission_name" data-parent="#modalMenu">
            <option value="">-- No Permission Required --</option>
            <?php if(isset($permissions)): ?>
                <?php foreach($permissions as $perm): ?>
                    <option value="<?= $perm['name'] ?>"><?= $perm['name'] ?> (<?= $perm['description'] ?>)</option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <div class="form-text">Menu will be hidden if user doesn't have this permission.</div>
    </div>
</form>
