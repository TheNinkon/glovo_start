'use strict';

$(function () {
  const dtUserTable = $('.datatables-riders');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const offcanvasForm = $('#addNewRiderForm');
  const offcanvasElement = $('#offcanvasAddRider');

  // DataTable
  if (dtUserTable.length) {
    const dataTable = dtUserTable.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '/admin/api/riders' // La URL correcta de nuestra API de listado
      },
      columns: [
        // Columna de Rider (Nombre y Email)
        {
          data: 'name',
          name: 'name',
          render: function (data, type, full, meta) {
            const riderUrl = `/admin/riders/${full.id}`;
            return `<div class="d-flex justify-content-start align-items-center user-name">
                      <div class="avatar-wrapper"><div class="avatar me-3"><img src="/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle"></div></div>
                      <div class="d-flex flex-column">
                        <a href="${riderUrl}" class="text-body text-truncate"><span class="fw-medium">${full.name}</span></a>
                        <small class="text-muted">${full.email}</small>
                      </div>
                    </div>`;
          }
        },
        // Otras columnas
        { data: 'phone', name: 'phone', defaultContent: 'N/A' },
        {
          data: 'status',
          name: 'status',
          render: function (data, type, full, meta) {
            const statusObj = {
              active: { title: 'Active', class: 'bg-label-success' },
              inactive: { title: 'Inactive', class: 'bg-label-secondary' },
              blocked: { title: 'Blocked', class: 'bg-label-danger' }
            };
            return `<span class="badge ${
              statusObj[data]?.class || 'bg-label-secondary'
            } text-capitalize">${statusObj[data]?.title || 'Unknown'}</span>`;
          }
        },
        { data: 'city_code', name: 'city_code', defaultContent: '-' },
        { data: 'city_code', name: 'city_code', defaultContent: '-' },
        {
          data: 'contract_hours_per_week',
          name: 'contract_hours_per_week',
          render: function (data) {
            const hours = typeof data === 'number' ? data : parseInt(data || 0, 10);
            return `${isNaN(hours) ? 0 : hours} h`;
          }
        },
        { data: 'supervisor_name', name: 'supervisor.name', defaultContent: 'N/A' },
        // Columna de Acciones
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            const riderUrl = `/admin/riders/${full.id}`;
            return (
              '<div class="d-flex align-items-center">' +
              `<a href="${riderUrl}" class="text-body" title="View"><i class="ti tabler-eye ti-sm me-2"></i></a>` +
              `<a href="javascript:;" class="text-body edit-record" data-id="${full.id}" title="Edit"><i class="ti tabler-edit ti-sm me-2"></i></a>` +
              `<a href="javascript:;" class="text-body text-danger delete-record" data-id="${full.id}" title="Delete"><i class="ti tabler-trash ti-sm"></i></a>` +
              '</div>'
            );
          }
        }
      ],
      // Estructura de controles de la tabla (DOM)
      dom:
        '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
        '<"table-responsive"t>' +
        '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      // ¡ELIMINADO! Ya no intentará cargar archivos de idioma. Usará el inglés por defecto.
      // language: { ... },
      buttons: [
        {
          text: '<i class="ti tabler-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add New Rider</span>',
          className: 'add-new btn btn-primary ms-3',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddRider'
          }
        }
      ],
      // Mueve los botones al lugar correcto después de que la tabla se inicialice
      initComplete: function () {
        $('.dataTables_length').addClass('mt-0 mt-md-3');
        $('.dt-buttons').appendTo('.dataTables_filter');
      }
    });
  }

  // Lógica para AÑADIR y EDITAR
  offcanvasForm.on('submit', function (e) {
    e.preventDefault();
    const riderId = $('#rider_id').val();
    const isEdit = !!riderId;
    const url = isEdit ? `/admin/api/riders/${riderId}` : '/admin/api/riders';

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
        $('.datatables-riders').DataTable().ajax.reload();
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

  // Lógica para el botón de EDITAR
  $('.datatables-riders tbody').on('click', '.edit-record', function () {
    const riderId = $(this).data('id');

    offcanvasElement.find('.offcanvas-title').text('Loading...');
    offcanvasElement.offcanvas('show');
    offcanvasForm[0].reset();
    $('#rider_id').val('');

    $.ajax({
      url: `/admin/api/riders/${riderId}`,
      method: 'GET',
          success: function (response) {
            if (response.success) {
              const rider = response.data;
              $('#offcanvasAddRiderLabel').text('Edit Rider');
              $('#rider_id').val(rider.id);
              $('#add-rider-name').val(rider.name);
              $('#add-rider-email').val(rider.email);
              $('#add-rider-phone').val(rider.phone);
              $('#add-rider-status').val(rider.status);
              $('#add-rider-contract-hours').val(rider.contract_hours_per_week || 0);
              $('#add-rider-city').val(rider.city_code || '');
              $('#add-rider-city').val(rider.city_code || '');
            }
          }
        });
      });

  // Resetear el formulario cuando se abre para AÑADIR
  $('.card-datatable').on('click', '.add-new', function () {
    $('#rider_id').val('');
    $('#offcanvasAddRiderLabel').text('Add Rider');
    offcanvasForm[0].reset();
    $('#add-rider-contract-hours').val(0);
  });

  // Lógica para ELIMINAR un rider
  $('.datatables-riders tbody').on('click', '.delete-record', function () {
    const riderId = $(this).data('id');
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
          url: `/admin/api/riders/${riderId}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          success: function (response) {
            Swal.fire('Deleted!', response.message, 'success');
            $('.datatables-riders').DataTable().ajax.reload();
          }
        });
      }
    });
  });
});
