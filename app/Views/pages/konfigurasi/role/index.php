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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Helper to get cookie value by name
function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

const RoleApp = {
    baseUrl: '<?= base_url('konfigurasi/role') ?>',
    csrfTokenName: '<?= csrf_token() ?>',
    csrfCookieName: '<?= config('Security')->cookieName ?>',
    
    modal: new bootstrap.Modal(document.getElementById('modalRole')),
    form: $('#roleForm'),
    saveUrl: '',
    
    init() {
        console.log('RoleApp Initialized');
        
        // Save handler
        $('#btnSave').off('click').on('click', (e) => { 
            e.preventDefault(); 
            this.save(); 
        });
        
        // Delegated events for Datatable buttons (Edit, View, Delete)
        // Kita bind ke 'body' atau wrapper static terdekat agar jalan meski tabel di-reload
        $(document).on('click', '.btn-edit', function() { 
            let id = $(this).data('id');
            console.log('Edit clicked, ID:', id);
            RoleApp.edit(id); 
        });

        $(document).on('click', '.btn-view', function() { 
            let id = $(this).data('id');
            console.log('View clicked, ID:', id);
            RoleApp.view(id); 
        });

        $(document).on('click', '.btn-delete', function() { 
            let id = $(this).data('id');
            console.log('Delete clicked, ID:', id);
            RoleApp.delete(id); 
        });
    },

    reset() {
        this.form[0].reset();
        this.form.find('.is-invalid').removeClass('is-invalid');
        this.form.find('input, textarea').prop('disabled', false);
        $('#btnSave').show();
        $('#modalRoleLabel').text('Role Form');
    },

    add() {
        this.reset();
        this.saveUrl = this.baseUrl + '/create';
        $('#modalRoleLabel').text('Add Role');
        this.modal.show();
    },

    edit(id) {
        this.reset();
        this.saveUrl = this.baseUrl + '/update/' + id;
        $('#modalRoleLabel').text('Edit Role');
        this.fetch(id);
    },

    view(id) {
        this.reset();
        $('#modalRoleLabel').text('View Role');
        $('#roleForm input, #roleForm textarea').prop('disabled', true);
        $('#btnSave').hide();
        this.fetch(id);
    },

    fetch(id) {
        $.get(this.baseUrl + '/show/' + id, (res) => {
            if (res.status === 'success') {
                $('#id').val(res.data.id);
                $('#role_name').val(res.data.name);
                $('#description').val(res.data.description);
                this.modal.show();
            }
        });
    },

    save() {
        // Get fresh CSRF token
        let csrfToken = getCookie(this.csrfCookieName) || '<?= csrf_hash() ?>';
        let formData = new FormData(this.form[0]);
        formData.append(this.csrfTokenName, csrfToken);

        $.ajax({
            url: this.saveUrl, 
            type: 'POST', 
            data: formData,
            processData: false, 
            contentType: false,
            success: (res) => {
                if (res.status === 'success') {
                    this.modal.hide();
                    reloadTable_tableRole();
                    Swal.fire('Success', res.message, 'success');
                } else this.showErrors(res.errors);
            },
            error: (xhr) => {
                console.error(xhr);
                Swal.fire('Error', 'Server Error: ' + xhr.status, 'error');
            }
        });
    },


    delete(id) {
        if (!id) {
            Swal.fire('Error', 'Invalid ID', 'error');
            return;
        }

        Swal.fire({
            title: 'Delete Role?', 
            text: "This action cannot be undone!",
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            // Support multiple SweetAlert versions
            if (result.isConfirmed || result.value === true) { 
                try {
                    // Get fresh CSRF token
                    let csrfName = this.csrfTokenName || 'csrf_test_name';
                    let csrfCookie = this.csrfCookieName || 'csrf_cookie_name';
                    let csrfToken = getCookie(csrfCookie) || '<?= csrf_hash() ?>';

                    let data = { _method: 'DELETE' };
                    data[csrfName] = csrfToken;

                    $.ajax({
                        url: this.baseUrl + '/delete/' + id, 
                        type: 'POST',
                        data: data,
                        success: (res) => {
                            if(res.status === 'success') {
                                reloadTable_tableRole();
                                Swal.fire('Deleted!', res.message, 'success');
                            } else {
                                Swal.fire('Failed!', res.message || 'Could not delete data', 'error');
                            }
                        },
                        error: (xhr) => {
                            let msg = 'Unknown Error';
                            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            else if(xhr.statusText) msg = xhr.statusText;
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                } catch (err) {
                    console.error('Delete error:', err);
                    Swal.fire('Error', 'An unexpected error occurred', 'error');
                }
            }
        });
    },

    showErrors(errors) {
        if (!errors) return Swal.fire('Error', 'Validation failed', 'error');
        for (let key in errors) {
            $(`#${key}`).addClass('is-invalid').siblings('.invalid-feedback').text(errors[key]);
        }
    }
};

$(document).ready(() => {
    // Unbind first to prevent duplicate bindings if script runs twice
    $(document).off('click', '.btn-edit');
    $(document).off('click', '.btn-view');
    $(document).off('click', '.btn-delete');
    
    RoleApp.init();
});
</script>
<?= $this->endSection() ?>
