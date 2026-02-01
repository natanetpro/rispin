/**
 * RispinCRUD - Simple & Clean Version
 */
class RispinCRUD {
    constructor(config) {
        // 1. Auto-Detect Nama Entity dari URL (misal: '.../role' -> 'Role')
        const urlParts = config.baseUrl.split('/');
        const entity = urlParts[urlParts.length - 1]; // "role"
        const Entity = entity.charAt(0).toUpperCase() + entity.slice(1); // "Role"

        // 2. Setup Config Pintar (Convention over Configuration)
        Object.assign(this, {
            title: Entity, // Default: "Role"
            
            // Auto Tebak ID HTML (Convention: tableRole, modalRole, roleForm)
            tableId: `table${Entity}`, 
            modalId: `modal${Entity}`,
            formId: `${entity}Form`,
            
            // Auto CSRF (Ambil dari Global Variable Layout)
            csrfTokenName: (typeof RISPIN_CONFIG !== 'undefined') ? RISPIN_CONFIG.csrfTokenName : 'csrf_test_name', 
            csrfCookieName: (typeof RISPIN_CONFIG !== 'undefined') ? RISPIN_CONFIG.csrfCookieName : 'csrf_cookie_name',
            
            // Handlers
            onEdit: null, // Jika null, akan pakai Auto-Map
            onView: null
        }, config);

        // 3. Init Elemen
        this.modal = new bootstrap.Modal(document.getElementById(this.modalId));
        this.form = $(`#${this.formId}`);
        this.init();
    }

    init() {
        // 2. Setup Event Listeners (Save & Table Buttons)
        $('#btnSave').off('click').on('click', (e) => { e.preventDefault(); this.save(); });

        const self = this;
        $(document)
            .off('click', '.btn-edit, .btn-view, .btn-delete') // Hapus listener lama biar ga double
            .on('click', '.btn-edit',   function() { self.edit($(this).data('id')); })
            .on('click', '.btn-view',   function() { self.view($(this).data('id')); })
            .on('click', '.btn-delete', function() { self.delete($(this).data('id')); });
        
        console.log(`RispinCRUD Ready: ${this.title}`);
    }

    // 3. Helper Pintar untuk Reset Form & Modal
    reset(state = 'Form') {
        this.form[0].reset();
        this.form.find('.is-invalid').removeClass('is-invalid');
        this.form.find('input, textarea, select').prop('disabled', state === 'View'); // Disable input jika View
        $('#btnSave').toggle(state !== 'View'); // Sembunyikan tombol save jika View
        $(`#${this.modalId}Label`).text(`${state} ${this.title}`);
    }

    add() {
        this.reset('Add');
        this.saveUrl = `${this.baseUrl}/create`;
        this.modal.show();
    }

    edit(id) {
        this.reset('Edit');
        this.saveUrl = `${this.baseUrl}/update/${id}`;
        this.fetch(id, 'edit');
    }

    view(id) {
        this.reset('View');
        this.fetch(id, 'view');
    }

    // 4. Ambil Data dari Server
    fetch(id, mode) {
        $.get(`${this.baseUrl}/show/${id}`, (res) => {
            if (res.status !== 'success') return Swal.fire('Error', res.message || 'Not Found', 'error');

            if (mode === 'view' && this.onView) {
                this.onView(res.data);
            } else if (this.onEdit) {
                this.onEdit(res.data);
            } else {
                // Auto Map Feature: Isi input yang ID-nya sama dengan key database
                Object.keys(res.data).forEach(key => {
                    let input = $(`#${key}`);
                    if (input.length) input.val(res.data[key]);
                });
            }
            
            this.modal.show();
        }).fail(() => Swal.fire('Error', 'Connection Failed', 'error'));
    }

    // 5. Simpan Data (Create/Update)
    save() {
        let formData = new FormData(this.form[0]);
        formData.append(this.csrfTokenName, this.getCookie(this.csrfCookieName) || '');

        $.ajax({
            url: this.saveUrl, type: 'POST', data: formData,
            processData: false, contentType: false,
            success: (res) => {
                if (res.status === 'success') {
                    this.modal.hide();
                    this.reloadTable();
                    Swal.fire('Success', res.message, 'success');
                } else if (res.errors) {
                    this.showErrors(res.errors);
                } else {
                    Swal.fire('Error', res.message || 'Operation failed', 'error');
                }
            },
            error: (xhr) => {
                const res = xhr.responseJSON;
                if (xhr.status === 400 && res && res.errors) {
                    // Handle Validation Error (HTTP 400)
                    this.showErrors(res.errors);
                } else {
                    // Handle System Error
                    Swal.fire('Error', res?.message || 'Server Error', 'error');
                }
            }
        });
    }

    // 6. Hapus Data
    delete(id) {
        if (!id) return;
        Swal.fire({
            title: `Delete ${this.title}?`, text: "Irreversible action!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (!result.isConfirmed && result.value !== true) return;
            
            $.ajax({
                url: `${this.baseUrl}/delete/${id}`, type: 'POST',
                data: { _method: 'DELETE', [this.csrfTokenName]: this.getCookie(this.csrfCookieName) },
                success: (res) => {
                    if (res.status === 'success') { this.reloadTable(); Swal.fire('Deleted!', res.message, 'success'); }
                    else Swal.fire('Failed!', res.message, 'error');
                },
                error: (xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'Delete Failed', 'error')
            });
        });
    }

    // Utilities
    reloadTable() {
        const fn = window[`reloadTable_${this.tableId}`] || window[`reloadTable_table_${this.tableId.replace(/-/g, '_')}`];
        if (fn) fn();
    }

    showErrors(errors) {
        if (!errors) return;
        this.form.find('.is-invalid').removeClass('is-invalid');
        for (let key in errors) {
            $(`#${key}`).addClass('is-invalid').siblings('.invalid-feedback').text(errors[key]);
        }
    }

    getCookie(name) {
        let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }
}
