'use strict';

$(function () {
  const assignRiderForm = $('#assignRiderForm');
  const endAssignmentBtn = $('#end-assignment-btn');
  const riderSelect = $('#rider_id');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const accountId = window.location.pathname.split('/').pop();

  // Poblar el selector de riders
  function loadRiders() {
    // Apuntamos al nuevo endpoint '/admin/api/riders/active-list'
    fetch('/admin/api/riders/active-list')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(riders => {
        riderSelect.empty().append('<option value="">Select a Rider</option>');
        riders.forEach(rider => {
          riderSelect.append(`<option value="${rider.id}">${rider.name}</option>`);
        });
      })
      .catch(error => {
        console.error('Failed to load riders:', error);
        riderSelect.empty().append('<option value="">Error loading riders</option>');
      });
  }

  // Evento para cargar los riders cuando se abre el modal
  $('#assignRiderModal').on('show.bs.modal', function () {
    loadRiders();
  });

  // Enviar el formulario de nueva asignación
  assignRiderForm.on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    // CORRECCIÓN: La URL debe apuntar al endpoint de la API
    fetch(`/admin/api/accounts/${accountId}/assignments`, {
      method: 'POST',
      body: new URLSearchParams(formData),
      headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.data && data.data.id) {
          Swal.fire('Success!', 'Account assigned successfully.', 'success').then(() => location.reload());
        } else {
          const message = data.message || 'Could not assign account.';
          const errors = data.errors ? '<br><small>' + Object.values(data.errors).flat().join('<br>') + '</small>' : '';
          Swal.fire('Error!', message + errors, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'An unexpected error occurred.', 'error');
      });
  });

  // Terminar una asignación
  endAssignmentBtn.on('click', function () {
    const assignmentId = $(this).data('assignment-id');
    Swal.fire({
      title: 'Are you sure?',
      text: "This will end the rider's current assignment with this account.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, end it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`/admin/api/assignments/${assignmentId}/end`, {
          // URL debe apuntar a la API
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.data && data.data.id) {
              Swal.fire('Success!', 'Assignment ended successfully.', 'success').then(() => location.reload());
            } else {
              Swal.fire('Error!', data.message || 'Could not end assignment.', 'error');
            }
          });
      }
    });
  });
});
