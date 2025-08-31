'use strict';

$(function () {
  const assignRiderForm = $('#assignRiderForm');
  const endAssignmentBtn = $('#end-assignment-btn');
  const riderSelect = $('#rider_id');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const accountId = window.location.pathname.split('/').pop();

  // Poblar el selector de riders
  function loadRiders() {
    fetch('/admin/api/riders') // Asume que tienes un endpoint que devuelve todos los riders
      .then(response => response.json())
      .then(data => {
        riderSelect.empty().append('<option value="">Select a Rider</option>');
        data.data.data.forEach(rider => {
          riderSelect.append(`<option value="${rider.id}">${rider.name}</option>`);
        });
      });
  }

  $('#assignRiderModal').on('show.bs.modal', loadRiders);

  // Enviar el formulario de nueva asignación
  assignRiderForm.on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`/admin/accounts/${accountId}/assignments`, {
      method: 'POST',
      body: new URLSearchParams(formData),
      headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.id) {
          Swal.fire('Success!', 'Account assigned successfully.', 'success').then(() => location.reload());
        } else {
          Swal.fire('Error!', data.message || 'Could not assign account.', 'error');
        }
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
      confirmButtonText: 'Yes, end it!'
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`/admin/assignments/${assignmentId}/end`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.id) {
              Swal.fire('Success!', 'Assignment ended successfully.', 'success').then(() => location.reload());
            } else {
              Swal.fire('Error!', data.message || 'Could not end assignment.', 'error');
            }
          });
      }
    });
  });
});
