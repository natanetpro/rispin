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
// 1. Siapkan Variabel Global
var RoleApp;

$(document).ready(() => {
    // 2. Init CRUD (Auto-Detect ID dari URL: .../role -> tableRole, modalRole, roleForm)
    RoleApp = new RispinCRUD({
        baseUrl: '<?= base_url('konfigurasi/role') ?>',
        
        // Mapping Data ke Form
        onEdit: (data) => {
            $('#id').val(data.id);
            $('#role_name').val(data.name);
            $('#description').val(data.description);
        }
    });
});
</script>
<?= $this->endSection() ?>
