'use strict';

$(function () {
  const dtAccountTable = $('.datatables-accounts');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

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
          render: data => data?.rider?.name || '<span class="text-muted">Unassigned</span>'
        },
        { data: 'date_of_delivery', name: 'date_of_delivery' },
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          render: data => `
            <div class="d-inline-block">
              <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="text-primary ti ti-dots-vertical"></i></a>
              <div class="dropdown-menu dropdown-menu-end m-0">
                <a href="/admin/accounts/${data}" class="dropdown-item"><i class="ti tabler-eye me-1"></i> View</a>
                <a href="javascript:;" class="dropdown-item edit-record" data-id="${data}"><i class="ti tabler-edit me-1"></i> Edit</a>
                <a href="javascript:;" class="dropdown-item text-danger delete-record" data-id="${data}"><i class="ti tabler-trash me-1"></i> Delete</a>
              </div>
            </div>`
        }
      ],
      dom: '<"row me-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
              .on('change', () => this.search(select.val()).draw());
            select.append(
              '<option value="active">Active</option><option value="inactive">Inactive</option><option value="blocked">Blocked</option>'
            );
          });
      }
    });
  }
  // Lógica para añadir, editar y eliminar (similar a riders-list.js)
});
