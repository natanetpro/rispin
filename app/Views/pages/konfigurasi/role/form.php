<form id="roleForm" autocomplete="off">
    <input type="hidden" id="id" name="id">
    
    <div class="row mb-4 align-items-center">
        <div class="col-lg-4">
            <label for="role_name" class="fw-semibold">Role Name <span class="text-danger">*</span></label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" id="role_name" name="role_name" placeholder="Enter role name" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-lg-4">
            <label for="description" class="fw-semibold">Description</label>
        </div>
        <div class="col-lg-8">
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"></textarea>
        </div>
    </div>
</form>
