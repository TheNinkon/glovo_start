'use strict';

$(function () {
  const dtAccountTable = $('.datatables-accounts');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const offcanvasForm = $('#addNewAccountForm');
  const offcanvasElement = $('#offcanvasAddAccount');

  if (dtAccountTable.length) {
    dtAccountTable.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '/admin/api/accounts',
        data: function (d) {
          d.status = $('#account-status-filter').val();
        }
      },
      columns: [
        {
          data: 'courier_id',
          name: 'courier_id',
          render: (data, type, full) => `<a href="/admin/accounts/${full.id}"><strong>${data}</strong></a>`
        },
        {
          data: 'status',
          name: 'status',
          render: data => {
            const statusObj = { active: 'success', inactive: 'secondary', blocked: 'danger' };
            return `<span class="badge bg-label-${statusObj[data]} text-capitalize">${data}</span>`;
          }
        },
        {
          data: 'active_assignment',
          name: 'activeAssignment.rider.name',
          orderable: false,
          searchable: false,
          render: data => data?.rider?.name || '<span class="text-muted">Unassigned</span>'
        },
        { data: 'date_of_delivery', name: 'date_of_delivery', defaultContent: 'N/A' },
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          render: data =>
            '<div class="d-flex align-items-center">' +
            `<a href="/admin/accounts/${data}" class="text-body" title="View"><i class="ti tabler-eye ti-sm me-2"></i></a>` +
            `<a href="javascript:;" class="text-body edit-record" data-id="${data}" title="Edit"><i class="ti tabler-edit ti-sm me-2"></i></a>` +
            `<a href="javascript:;" class="text-body text-danger delete-record" data-id="${data}" title="Delete"><i class="ti tabler-trash ti-sm"></i></a>` +
            '</div>'
        }
      ],
      dom:
        '<"row me-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      buttons: [
        {
          text: '<i class="ti tabler-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add New Account</span>',
          className: 'add-new btn btn-primary',
          attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasAddAccount' }
        }
      ],
      initComplete: function () {
        this.api()
          .columns(1)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="account-status-filter" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
            )
              .appendTo('.account_status')
              .on('change', function () {
                column.search($(this).val() ? '^' + $(this).val() + '$' : '', true, false).draw();
              });
            select.append(
              '<option value="active">Active</option><option value="inactive">Inactive</option><option value="blocked">Blocked</option>'
            );
          });
      }
    });
  }

  // Lógica para AÑADIR y EDITAR
  offcanvasForm.on('submit', function (e) {
    e.preventDefault();
    const accountId = $('#account_id').val();
    const isEdit = !!accountId;
    const url = isEdit ? `/admin/api/accounts/${accountId}` : '/admin/api/accounts';

    let formData = new FormData(this);
    if (isEdit) {
      formData.append('_method', 'PUT');
    }

    $.ajax({
      url: url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrfToken },
      success: function (response) {
        offcanvasElement.offcanvas('hide');
        $('.datatables-accounts').DataTable().ajax.reload();
        Swal.fire('Success!', response.message, 'success');
      },
      error: function (xhr) {
        const response = xhr.responseJSON;
        let errorMsg = response.message || 'Could not process request.';
        if (response.errors) {
          errorMsg = Object.values(response.errors)
            .map(e => e[0])
            .join('<br>');
        }
        Swal.fire('Error!', errorMsg, 'error');
      }
    });
  });

  // Lógica para el botón de EDITAR (abre el off-canvas y rellena los datos)
  $('.datatables-accounts tbody').on('click', '.edit-record', function () {
    const accountId = $(this).data('id');

    offcanvasElement.find('.offcanvas-title').text('Loading...');
    offcanvasElement.offcanvas('show');
    offcanvasForm[0].reset();
    $('#account_id').val('');

    $.ajax({
      url: `/admin/api/accounts/${accountId}`,
      method: 'GET',
      success: function (response) {
        if (response.data) {
          const account = response.data;
          $('#offcanvasAddAccountLabel').text('Edit Account');
          $('#account_id').val(account.id);
          $('#add-account-courier-id').val(account.courier_id);
          $('#add-account-status').val(account.status);
          $('#add-account-delivery-date').val(account.date_of_delivery);
          $('#add-account-return-date').val(account.date_of_return);
        }
      },
      error: function () {
        offcanvasElement.offcanvas('hide');
        Swal.fire('Error!', 'Could not fetch account data.', 'error');
      }
    });
  });

  // Resetear el formulario cuando se abre para AÑADIR
  $('.card-datatable').on('click', '.add-new', function () {
    $('#account_id').val('');
    $('#offcanvasAddAccountLabel').text('Add Account');
    offcanvasForm[0].reset();
  });

  // Lógica para ELIMINAR una cuenta
  $('.datatables-accounts tbody').on('click', '.delete-record', function () {
    const accountId = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: `/admin/api/accounts/${accountId}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          success: function (response) {
            Swal.fire('Deleted!', 'Account has been deleted.', 'success');
            $('.datatables-accounts').DataTable().ajax.reload();
          },
          error: function (xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Could not delete account.', 'error');
          }
        });
      }
    });
  });
});
