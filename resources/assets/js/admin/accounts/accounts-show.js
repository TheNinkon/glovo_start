'use strict';

$(function () {
  // Move modal to body to avoid aria-hidden focus issues
  const modalEl = document.getElementById('assignRiderModal');
  if (modalEl && modalEl.parentElement !== document.body) {
    document.body.appendChild(modalEl);
  }

  const assignRiderForm = $('#assignRiderForm');
  const endAssignmentBtn = $('#end-assignment-btn');
  const riderSelect = $('#rider_id');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const accountId = window.location.pathname.split('/').pop();

  function loadRiders() {
    fetch('/admin/api/riders/active-list')
      .then(response => response.json())
      .then(riders => {
        riderSelect.empty().append('<option value="">Select a Rider</option>');
        riders.forEach(rider => {
          riderSelect.append(`<option value="${rider.id}">${rider.name}</option>`);
        });
      });
  }

  $('#assignRiderModal').on('show.bs.modal', loadRiders);

  // Ensure a11y attributes are consistent to avoid aria-hidden warning
  $('#assignRiderModal')
    .on('show.bs.modal', function () {
      // Ensure aria-hidden is not true while about to show
      this.setAttribute('aria-hidden', 'false');
      this.setAttribute('aria-modal', 'true');
    })
    .on('shown.bs.modal', function () {
      this.setAttribute('aria-hidden', 'false');
      this.setAttribute('aria-modal', 'true');
    })
    .on('hide.bs.modal', function () {
      // Blur focused element before aria-hidden is applied during fade
      if (document.activeElement) document.activeElement.blur();
    })
    .on('hidden.bs.modal', function () {
      this.setAttribute('aria-hidden', 'true');
      this.removeAttribute('aria-modal');
    });

  assignRiderForm.on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(`/admin/api/accounts/${accountId}/assignments`, {
      method: 'POST',
      body: new URLSearchParams(formData),
      headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
    })
      .then(response => {
        if (!response.ok) {
          return response.json().then(err => {
            throw err;
          });
        }
        return response.json();
      })
      .then(data => {
        Swal.fire({ icon: 'success', title: 'Success!', text: data.message || 'Account assigned successfully.' }).then(
          () => location.reload()
        );
      })
      .catch(error => {
        const message = error.message || 'Could not assign account.';
        const errors = error.errors
          ? '<br><small class="text-danger">' + Object.values(error.errors).flat().join('<br>') + '</small>'
          : '';
        Swal.fire({ icon: 'error', title: 'Authorization Error', html: message + errors });
      });
  });

  endAssignmentBtn.on('click', function () {
    const assignmentId = $(this).data('assignment-id');
    Swal.fire({
      title: 'Are you sure?',
      text: "This will end the rider's current assignment.",
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
