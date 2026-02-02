<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Menu Manager</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item">Konfigurasi</li>
            <li class="breadcrumb-item">Menu</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <?php if(check_permission('edit_menu')): ?>
            <button class="btn btn-success" id="btnSaveOrder" style="display:none;">
                <i class="feather-save me-2"></i> Save Order
            </button>
            <?php endif; ?>
            
            <?php if(check_permission('create_menu')): ?>
            <button class="btn btn-primary" onclick="addMenu()">
                <i class="feather-plus me-2"></i> Add Menu
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info py-2">
                        <i class="feather-info me-2"></i> Drag & Drop list items to reorder or create sub-menus.
                    </div>
                    
                    <div class="nestable-lists">
                        <ul id="menuList" class="sortable-list">
                            <?php 
                            // Helper Function untuk EDITABLE Menu (dengan tombol Edit/Delete)
                            // Nama unik agar tidak bentrok dengan sidebar's renderMenu
                            function renderEditableMenu($items, $parentId = null) {
                                foreach ($items as $menu) {
                                    // Handle NULL parent_id untuk root level
                                    $menuParent = $menu['parent_id'] ?? null;
                                    if ($menuParent == $parentId) {
                                        echo '<li class="dd-item" data-id="' . $menu['id'] . '">';
                                        
                                        // Wrapper Container
                                        echo '<div class="d-flex align-items-center mb-2 p-2 border rounded bg-white">';
                                        
                                        // 1. DRAG HANDLE (Icon Grip + Title + URL) -> Cuma ini yang bisa buat nge-drag
                                        echo '<div class="dd-handle flex-grow-1 d-flex align-items-center" style="cursor: move;">';
                                            echo '<i class="feather-move text-muted me-3"></i>'; // Visual Grip
                                            if($menu['icon']) echo '<i class="'.$menu['icon'].' me-2 text-primary"></i>';
                                            echo '<div>';
                                                echo '<span class="fw-bold d-block">' . esc($menu['title']) . '</span>';
                                                echo '<small class="text-muted">'.esc($menu['url']).'</small>';
                                            echo '</div>';
                                        echo '</div>';
                                        
                                        // 2. ACTION BUTTONS
                                        echo '<div class="dd-actions ms-2">';
                                            if (check_permission('edit_menu')) {
                                                echo '<button class="btn btn-sm btn-light-primary btn-edit me-1" data-id="'.$menu['id'].'"><i class="feather-edit"></i></button>';
                                            }
                                            if (check_permission('delete_menu')) {
                                                echo '<button class="btn btn-sm btn-light-danger btn-delete" data-id="'.$menu['id'].'"><i class="feather-trash-2"></i></button>';
                                            }
                                        echo '</div>';
                                        
                                        echo '</div>'; // End Wrapper
                                        
                                        // Render Children
                                        echo '<ul class="nested-sortable">';
                                        renderEditableMenu($items, $menu['id']);
                                        echo '</ul>';
                                        
                                        echo '</li>';
                                    }
                                }
                            }
                            
                            // Panggil fungsi dengan NULL parent (untuk root items)
                            renderEditableMenu($menuItems, null); 
                            ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
             <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Icons Reference</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Use standard Feather Icons class names.</p>
                    <a href="https://feathericons.com/" target="_blank" class="btn btn-outline-secondary w-100">Browse Icons</a>
                </div>
             </div>
        </div>
    </div>
</div>

<!-- Modal Form (Managed by RispinCRUD) -->
<?= view('components/modal', ['id' => 'modalMenu', 'title' => 'Menu Form', 'formId' => 'menuForm', 'contentView' => 'pages/konfigurasi/menu/form']) ?>

<style>
/* Nestable/Kanban Styles */
.sortable-list, .nested-sortable {
    list-style-type: none;
    margin: 0;
    padding: 0;
    min-height: 20px;
}
.nested-sortable {
    margin-left: 30px; /* Indent for child items */
    padding-left: 10px;
    border-left: 1px dashed #e2e5e8;
}
.dd-item {
    display: block;
    margin: 10px 0;
    position: relative;
    min-height: 20px;
}
.dd-handle {
    display: flex;
    padding: 12px 15px;
    background: #fff;
    border: 1px solid #e2e5e8;
    border-radius: 6px;
    cursor: move;
    transition: all 0.2s;
}
.dd-handle:hover {
    border-color: #348cd4;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
/* Placeholder saat drag */
.ui-sortable-placeholder {
    border: 1px dashed #348cd4;
    background: #f0f8ff;
    height: 45px;
    border-radius: 6px;
    margin: 10px 0;
    visibility: visible !important;
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Use jQuery UI for Sortable -->
<script src="<?= base_url('assets/vendors/js/jquery-ui.min.js') ?>"></script>

<script>
$(document).ready(function() {
    const baseUrl = '<?= base_url('konfigurasi/menu') ?>';
    const csrfName = RISPIN_CONFIG.csrfTokenName;
    const csrfCookie = RISPIN_CONFIG.csrfCookieName;
    
    // Cookie Helper
    function getCookie(name) {
        let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }

    // --- 1. MODAL FORM HANDLER ---
    // --- 0. HELPER: SELECT2 INIT ---
    function initSelect2() {
        $('.form-select2').select2({
            theme: 'bootstrap-5',
            component: 24, // Opsional rispin component style
            width: '100%',
            dropdownParent: $('#modalMenu'), // Fix Select2 focus in Modal
            placeholder: 'Select an option',
            allowClear: true
        });
    }

    // --- 1. MODAL FORM HANDLER ---
    const modalMenu = new bootstrap.Modal(document.getElementById('modalMenu'));
    const menuForm = $('#menuForm');

    // Add Menu
    window.addMenu = function() {
        menuForm[0].reset();
        
        // Reset Error State
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').removeClass('d-block').text('');
        
        // Reset Select2
        $('#permission_name').val(null).trigger('change');
        
        $('#id').val(''); // Clear ID -> Create Mode
        $('#modalMenuLabel').text('Add Menu');
        modalMenu.show();
        
        // Init Select2 after modal logic
        setTimeout(initSelect2, 200); 
    };

    // Edit Menu
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        // Reset Error State
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').removeClass('d-block').text('');
        
        // Fetch Data
        $.get(`${baseUrl}/show/${id}`, function(res) {
            if(res.status === 'success') {
                const data = res.data;
                $('#id').val(data.id);
                $('#title').val(data.title);
                $('#url').val(data.url);
                $('#icon').val(data.icon);
                
                // Set Value dulu baru Init/Trigger
                $('#permission_name').val(data.permission_name); 

                $('#modalMenuLabel').text('Edit Menu');
                modalMenu.show();
                
                // Init Select2 dan Trigger Change agar value tampil
                setTimeout(function(){
                    initSelect2();
                    $('#permission_name').trigger('change');
                }, 200);
            } else {
                Swal.fire('Error', res.message || 'Menu not found', 'error');
            }
        });
    });

    // Save Form (Create/Update)
    $('#btnSave').click(function(e) {
        e.preventDefault();
        
        let formData = new FormData(menuForm[0]);
        let id = $('#id').val();
        let url = id ? `${baseUrl}/update/${id}` : `${baseUrl}/create`;
        
        // Append CSRF Manual
        formData.append(csrfName, getCookie(csrfCookie));

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.status === 'success') {
                    modalMenu.hide();
                    Swal.fire('Success', res.message, 'success').then(() => window.location.reload());
                } else if(res.errors) {
                    // Show Inline Errors
                    $('.is-invalid').removeClass('is-invalid');
                    for (let key in res.errors) {
                        $(`#${key}`).addClass('is-invalid').siblings('.invalid-feedback').text(res.errors[key]);
                    }
                } else {
                    Swal.fire('Error', res.message || 'Operation failed', 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON;
                
                // Prioritas 1: Validation Errors (Inline)
                if(xhr.status === 400 && res && res.errors) {
                     $('.is-invalid').removeClass('is-invalid');
                    for (let key in res.errors) {
                        let input = $(`#${key}`);
                        input.addClass('is-invalid');
                        
                        // Cari feedback element dengan logic traversal yang aman
                        // Case 1: Standard Input (sibling langsung)
                        let feedback = input.siblings('.invalid-feedback');
                        
                        // Case 2: Input Group (feedback ada di luar .input-group)
                        if (feedback.length === 0) {
                            feedback = input.closest('.input-group').siblings('.invalid-feedback');
                        }
                        
                        // Case 3: Parent Wrapper (Jaga-jaga)
                        if (feedback.length === 0) {
                            feedback = input.closest('.mb-3').find('.invalid-feedback');
                        }
                        
                        // FORCE SHOW: Tambahkan d-block karena struktur HTML nested bikin CSS Bootstrap gak jalan
                        feedback.text(res.errors[key]).addClass('d-block');
                    }
                    // Jika ada global message juga, tampilkan toast/swal kecil opsional? 
                    // Jika ada global message juga, tampilkan toast/swal kecil opsional? 
                    // Tapi biasanya inline cukup.
                } 
                // Prioritas 2: Specific Error Message from Server (e.g. Logic Validation)
                else if (res && res.message) {
                    Swal.fire('Error', res.message, 'error');
                }
                // Fallback: Generic Error
                else {
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                }
            }
        });
    });

    // Delete Menu
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');

        Swal.fire({
            title: 'Delete this menu?',
            text: "All sub-menus must be deleted/moved first.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            // Cek kompatibilitas versi SweetAlert (isConfirmed untuk v10+, value untuk v9-)
            if (result.isConfirmed || result.value) {
                $.ajax({
                    url: `${baseUrl}/delete/${id}`,
                    type: 'POST',
                    data: { 
                        _method: 'DELETE',
                        [csrfName]: getCookie(csrfCookie)
                    },
                    success: function(res) {
                        if(res.status === 'success') {
                            Swal.fire('Deleted!', res.message || 'Menu has been deleted.', 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Failed', res.message || 'Failed to delete menu', 'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Delete Failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.status === 500) {
                            msg = 'Server Error (500). Possible cause: Menu has children or DB constraint.';
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });


    // --- 2. DRAG & DROP LOGIC ---
    // Gunakan selector spesifik agar tidak konflik dengan sidebar
    $('#menuList, .nested-sortable').sortable({
        connectWith: '#menuList, .nested-sortable',
        placeholder: 'ui-sortable-placeholder',
        items: 'li.dd-item', 
        handle: '.dd-handle', // Handle only
        cursor: 'move',
        tolerance: 'pointer',
        start: function(e, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function(e, ui) {
             $('#btnSaveOrder').fadeIn();
        }
    }).disableSelection();
    
    // Save Order
    $('#btnSaveOrder').click(function() {
        let structure = getStructure($('#menuList'));
        
        $.ajax({
            url: `${baseUrl}/saveOrder`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(structure),
            headers: {
                [csrfName]: getCookie(csrfCookie)
            },
            success: function(res) {
                 if(res.status === 'success') {
                     Swal.fire({
                        title: 'Saved!',
                        text: 'Menu structure updated successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                     }).then(() => {
                        window.location.reload();
                     });
                 } else {
                     Swal.fire('Error', res.message, 'error');
                 }
            }
        });
    });
    
    // Helper: Parse HTML List ke JSON Recursive
    function getStructure(list) {
        let result = [];
        list.children('li').each(function() {
            let item = { id: $(this).data('id') };
            let subList = $(this).children('ul');
            if (subList.length && subList.children('li').length > 0) {
                item.children = getStructure(subList);
            }
            result.push(item);
        });
        return result;
    }
});
</script>
<?= $this->endSection() ?>
