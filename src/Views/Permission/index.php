<!-- Include -->
<?= $this->include('agungsugiarto\boilerplate\Views\load\datatables') ?>
<?= $this->include('agungsugiarto\boilerplate\Views\load\sweetalert') ?>
<!-- Extend from layout index -->
<?= $this->extend('agungsugiarto\boilerplate\Views\layout\index') ?>

<!-- Section content -->
<?= $this->section('content') ?>
    <?= $this->include('agungsugiarto\boilerplate\Views\Permission\create') ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="float-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-block btn-primary" id="create-book"
                                data-toggle="modal" data-target="#modal-create-permission"><i class="fa fa-plus"></i>
                                <?= lang('permission.add') ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-permission" class="table table-striped table-hover va-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('permission.name') ?></th>
                                    <th><?= lang('permission.description') ?></th>
                                    <th><?= lang('permission.action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<!-- Push section js -->
<?= $this->section('js') ?>
<script>
    $.get('https://www.google.com/');

    var tablePermission = $('#table-permission').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,

        ajax : {
            url: '<?= route_to('admin/permission') ?>',
            method: 'get'
        },
        columns : [
            { 'data': null },
            { 'data': 'name' },
            { 'data': 'description' },
            { 
                'data': function (data) {
                    return '<button type="button" class="btn btn-primary btn-sm btn-edit" data-id="' + data.id + '"><span class="fa fa-fw fa-pencil-alt"></span></button> <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' + data.id + '"><span class="fa fa-fw fa-trash"></span></button>';
                }
            }
        ]
    });

    tablePermission.on('order.dt search.dt', () => {
        tablePermission.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        });
    }).draw();

    $(document).on('click', '#btn-save-permission', () => {
        $('.text-danger').remove();
        $('.is-invalid').removeClass('is-invalid');
        var createForm = $('#form-create-permission');
        
        $.ajax({
            url: '<?= route_to('admin/permission') ?>',
            method: 'post',
            data: createForm.serializeArray()

        }).done((data, textStatus) => {
            Toast.fire({
                icon: 'success',
                title: textStatus
            })
            tablePermission.ajax.reload();
            $("#form-create-permission").trigger("reset");
            $("#modal-create-permission").modal('hide');

        }).fail((xhr, status, error) => {
            if (xhr.responseJSON.message) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON.message,
                });
            }

            $.each(xhr.responseJSON.messages, (elem, messages) => {
                createForm.find('input[name="' + elem + '"]').addClass('is-invalid').after('<p class="text-danger">' + messages + '</p>');
                createForm.find('textarea[name="' + elem + '"]').addClass('is-invalid').after('<p class="text-danger">' + messages + '</p>');
            });
        })
    })

    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        $.ajax({
            url: `<?= route_to('admin/permission') ?>/${$(this).attr('data-id')}/edit`,
            method: 'GET',
            
        }).done((response) => {
            var editForm = $('#form-edit-permission');
            editForm.find('input[name="name"]').val(response.data.name);
            editForm.find('textarea[name="description"]').val(response.data.description);
            $("#permission_id").val(response.data.id);
            $("#modal-edit-permission").modal('show');
        }).fail((error) => {
            Toast.fire({
                icon: 'error',
                title: error.responseJSON.messages.error,
            });
        })
    })

    $(document).on('click', '#btn-update-permission', function(e) {
        e.preventDefault();
        $('.text-danger').remove();
        var editForm = $('#form-edit-permission');

        $.ajax({
            url: `<?= route_to('admin/permission') ?>/${ $('#permission_id').val() }`,
            method: 'PUT',
            data: editForm.serialize()
            
        }).done((data, textStatus) => {
            Toast.fire({
                icon: 'success',
                title: textStatus
            })
            tablePermission.ajax.reload();
            $("#form-edit-permission").trigger("reset");
            $("#modal-edit-permission").modal('hide');

        }).fail((xhr, status, error) => {
            $.each(xhr.responseJSON.messages, (elem, messages) => {
                editForm.find('input[name="' + elem + '"]').addClass('is-invalid').after('<p class="text-danger">' + messages + '</p>');
                editForm.find('textarea[name="' + elem + '"]').addClass('is-invalid').after('<p class="text-danger">' + messages + '</p>');
            });
        })
    })

    $(document).on('click', '.btn-delete', function(e) {
        Swal.fire({
            title: '<?= lang('global.title') ?>',
            text: "<?= lang('global.text') ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<?= lang('global.confirm_delete') ?>'
        })
        .then((result) => {
            if (result.value) {
                $.ajax({
                    url: `<?= route_to('admin/permission') ?>/${$(this).attr('data-id')}`,
                    method: 'DELETE',
                }).done((data, textStatus) => {
                    Toast.fire({
                        icon: 'success',
                        title: textStatus,
                    });
                    tablePermission.ajax.reload();
                }).fail((error) => {
                    Toast.fire({
                        icon: 'error',
                        title: error.responseJSON.messages.error,
                    });
                })
            }
        })
    })

    $('#modal-create-permission').on('hidden.bs.modal', function() {
        $(this).find('#form-create-permission')[0].reset();
        $('.text-danger').remove();
        $('.is-invalid').removeClass('is-invalid');
    });

    $('#modal-edit-permission').on('hidden.bs.modal', function() {
        $(this).find('#form-edit-permission')[0].reset();
        $('.text-danger').remove();
        $('.is-invalid').removeClass('is-invalid');
    });
</script>

<?= $this->endSection() ?>
