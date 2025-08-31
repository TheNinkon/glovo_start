@extends('layouts/layoutMaster')

@section('title', 'Account Details')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/page-user-view.scss')
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/admin/accounts/accounts-show.js'])
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var modal = document.getElementById('assignRiderModal');
      if (!modal) return;

      // Ensure modal lives under body to avoid aria-hidden conflicts
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }

      var ariaObserver;
      function startObserver() {
        if (ariaObserver) ariaObserver.disconnect();
        ariaObserver = new MutationObserver(function () {
          if (modal.classList.contains('show') && modal.getAttribute('aria-hidden') === 'true') {
            modal.setAttribute('aria-hidden', 'false');
          }
        });
        ariaObserver.observe(modal, { attributes: true, attributeFilter: ['aria-hidden'] });
      }

      modal.addEventListener('show.bs.modal', function () {
        modal.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-modal', 'true');
        modal.removeAttribute('data-previous-aria-hidden');
        startObserver();
      });

      modal.addEventListener('shown.bs.modal', function () {
        modal.setAttribute('aria-hidden', 'false');
        var first = modal.querySelector('input, select, textarea, button:not(.btn-close), [tabindex]:not([tabindex="-1"])');
        if (first) {
          try { first.focus({ preventScroll: true }); } catch (e) { first.focus(); }
        }
      });

      modal.addEventListener('hide.bs.modal', function () {
        if (document.activeElement) document.activeElement.blur();
      });

      modal.addEventListener('hidden.bs.modal', function () {
        if (ariaObserver) { ariaObserver.disconnect(); ariaObserver = null; }
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
      });
    });
  </script>
@endsection

@section('content')
  <div class="row">
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
      <div class="card mb-4">
        <div class="card-body">
          <div class="user-avatar-section">
            <div class=" d-flex align-items-center flex-column">
              <i class="ti tabler-device-sim ti-lg text-primary mb-4"></i>
              <div class="user-info text-center">
                <h4>{{ $account->courier_id }}</h4>
                <span class="badge bg-label-secondary">Account</span>
              </div>
            </div>
          </div>
          <h5 class="pb-2 border-bottom mb-4">Details</h5>
          <div class="info-container">
            <ul class="list-unstyled">
              <li class="mb-3">
                <span class="fw-medium me-2">Status:</span>
                <span class="badge bg-label-success text-capitalize">{{ $account->status }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">Date of Delivery:</span>
                <span>{{ $account->date_of_delivery?->format('d M Y') ?? 'N/A' }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">Date of Return:</span>
                <span>{{ $account->date_of_return?->format('d M Y') ?? 'N/A' }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
      <div class="card mb-4">
        <h5 class="card-header">Current Assignment</h5>
        <div class="card-body">
          @if ($account->activeAssignment)
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><span class="fw-medium">Rider: </span> {{ $account->activeAssignment->rider->name }}</p>
                <p class="mb-0"><span class="fw-medium">Start Date: </span>
                  {{ $account->activeAssignment->start_date->format('d M Y') }}</p>
              </div>
              <div class="col-md-6 text-end">
                @can('update', $account->activeAssignment)
                  <button class="btn btn-label-danger" id="end-assignment-btn"
                    data-assignment-id="{{ $account->activeAssignment->id }}">End Assignment</button>
                @endcan
              </div>
            </div>
          @else
            <p>This account is not currently assigned to any rider.</p>
            @can('create', App\Models\Assignment::class)
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignRiderModal">Assign to
                Rider</button>
            @endcan
          @endif
        </div>
      </div>
      <div class="card">
        <h5 class="card-header">Assignments History</h5>
        <div class="table-responsive mb-3">
          <table class="table datatable-project border-top">
            <thead>
              <tr>
                <th>Rider</th>
                <th>Start Date</th>
                <th>End Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($account->assignments->sortByDesc('start_date') as $assignment)
                <tr>
                  <td>{{ $assignment->rider->name }}</td>
                  <td>{{ $assignment->start_date->format('d M Y') }}</td>
                  <td>{{ $assignment->end_date ? $assignment->end_date->format('d M Y') : 'Active' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center">No assignment history found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="assignRiderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Assign Account to Rider</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="assignRiderForm">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="rider_id" class="form-label">Select Rider</label>
              <select class="form-select" id="rider_id" name="rider_id" required>
                {{-- JS llenará esto --}}
                <option>Loading riders...</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Assign</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
