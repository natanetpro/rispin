<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<!-- [ page-header ] start -->
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Manajemen Role</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item">Konfigurasi</li>
            <li class="breadcrumb-item">Role</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button class="btn btn-primary" onclick="RoleApp.add()">
                    <i class="feather-plus me-2"></i>
                    <span>Add Role</span>
                </button>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>
<!-- [ page-header ] end -->

<!-- [ Main Content ] start -->
<div class="main-content">
    <div class="row">
        <div class="col-lg-12">
            <?= view('components/datatable', [
                'tableId' => 'tableRole',
                'showHeader' => false,
                'ajax' => base_url('konfigurasi/role'),
                'header' => ['ID', 'Name', 'Description', 'Action'],
                'data' => [
                    'id' => ['searchable' => false],
                    'name' => [],
                    'description' => [],
                    'action' => ['orderable' => false, 'searchable' => false, 'className' => 'text-end']
                ]
            ]) ?>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->

<?= view('components/modal', ['id' => 'modalRole', 'title' => 'Role Form', 'formId' => 'roleForm', 'contentView' => 'pages/konfigurasi/role/form']) ?>

<!-- Modal Permission (XL) -->
<?= view('components/modal', [
    'id' => 'modalPermission', 
    'title' => 'Assign Permissions', 
    'size' => 'xl',
    'formId' => 'permissionForm',
    'saveBtnId' => 'btnSavePermission',
    'contentView' => 'pages/konfigurasi/role/form_permission'
]) ?>

<!-- Form for permission (must be in content, not scripts) -->
<form id="permissionForm" onsubmit="return false;"></form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
jQuery(function($) {
    // 2. Init CRUD Standard
    window.RoleApp = new RispinCRUD({
        baseUrl: '<?= base_url('konfigurasi/role') ?>',
        onEdit: function(data) {
            $('#id').val(data.id);
            $('#role_name').val(data.name);
            $('#description').val(data.description);
        }
    });

    // --- PERMISSION ASSIGNMENT LOGIC ---
    var modalPermEl = document.getElementById('modalPermission');
    var modalPerm = new bootstrap.Modal(modalPermEl);
    
    // Open Modal
    $(document).on('click', '.btn-permission', function() {
        var id = $(this).data('id');
        $('#perm_role_id').val(id);
        
        // UI Reset
        $('#permissionList').html('');
        $('#permissionLoading').show();
        modalPerm.show();
        
        // Fetch API
        $.get('<?= base_url('konfigurasi/permissions') ?>/' + id, function(res) {
            $('#permissionLoading').hide();
            if(res.status === 'success') {
                renderPermissions(res.data);
                $('#modalPermissionLabel').text('Permissions: ' + res.data.role.name);
            } else {
                Swal.fire('Error', res.message, 'error');
                modalPerm.hide();
            }
        }).fail(function() {
            Swal.fire('Error', 'Failed to fetch permissions', 'error');
            modalPerm.hide();
        });
    });

    // Render Matrix Checkbox
    function renderPermissions(data) {
        var grouped = data.grouped_permissions;
        var assigned = data.assigned_ids.map(String); 
        
        var html = '';
        
        if (Object.keys(grouped).length === 0) {
            html = '<div class="col-12 text-center text-muted">No permissions found. Please run seeder.</div>';
        }

        for (var resource in grouped) {
            html += '<div class="col-md-4 mb-4">' +
                        '<div class="card h-100 border shadow-sm">' +
                            '<div class="card-header bg-light py-2 px-3 border-bottom">' +
                                '<div class="form-check m-0">' +
                                    '<input class="form-check-input check-all-group" type="checkbox" data-group="' + resource + '">' +
                                    '<label class="form-check-label fw-bold text-uppercase fs-12 text-primary">' +
                                        resource +
                                    '</label>' +
                                '</div>' +
                            '</div>' +
                            '<div class="card-body p-3">';
                            
            grouped[resource].forEach(function(perm) {
                var isChecked = assigned.includes(String(perm.id)) ? 'checked' : '';
                var actionName = perm.name.split('_')[0]; 
                var label = actionName.charAt(0).toUpperCase() + actionName.slice(1);
                
                html += '<div class="form-check mb-2">' +
                            '<input class="form-check-input perm-item group-' + resource + '" type="checkbox" name="permissions[]" value="' + perm.id + '" id="perm_' + perm.id + '" ' + isChecked + '>' +
                            '<label class="form-check-label fs-13" for="perm_' + perm.id + '">' +
                                label +
                            '</label>' +
                         '</div>';
            });
            
            html += '</div></div></div>';
        }
        
        $('#permissionList').html(html);
    }
    
    // Helper: Check All per Group
    $(document).on('change', '.check-all-group', function() {
        var group = $(this).data('group');
        var isChecked = $(this).is(':checked');
        $('.group-' + group).prop('checked', isChecked);
    });

    // Save Button Handler
    $('#btnSavePermission').click(function(e) {
        e.preventDefault();
        var id = $('#perm_role_id').val();
        
        var selected = [];
        $('.perm-item:checked').each(function() {
            selected.push($(this).val());
        });
        
        var csrfToken = RISPIN_CONFIG.csrfCookieName ? getCookie(RISPIN_CONFIG.csrfCookieName) : '';

        $.ajax({
            url: '<?= base_url('konfigurasi/permissions') ?>/' + id,
            type: 'POST',
            data: {
                permissions: selected,
                [RISPIN_CONFIG.csrfTokenName]: csrfToken
            },
            success: function(res) {
                if(res.status === 'success') {
                    modalPerm.hide();
                    Swal.fire('Success', 'Permissions updated successfully', 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server Error', 'error');
            }
        });
    });
    
    // Function Helper Cookie
    function getCookie(name) {
        var cookieArr = document.cookie.split(";");
        for(var i = 0; i < cookieArr.length; i++) {
            var cookiePair = cookieArr[i].split("=");
            if(name == cookiePair[0].trim()) {
                return decodeURIComponent(cookiePair[1]);
            }
        }
        return null;
    }
});
</script>
<?= $this->endSection() ?>
