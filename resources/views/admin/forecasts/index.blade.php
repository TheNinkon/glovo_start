@extends('layouts/layoutMaster')

@section('title', 'Forecasts List')

@section('content')
  <div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="mb-0">
      <span class="text-muted fw-light">Admin /</span> Forecasts
    </h4>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.forecasts.create') }}" class="btn btn-primary">
        <i class="ti tabler-plus me-1"></i> New Forecast
      </a>
      <button type="button" class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#uploadForecastModal">
        <i class="ti tabler-upload me-1"></i> Upload From File
      </button>
    </div>
  </div>

  <div class="card">
    <h5 class="card-header">All Forecasts</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Week</th>
            <th>Selection Deadline</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse ($forecasts as $forecast)
            <tr>
              <td><strong>{{ $forecast->name }}</strong></td>
              <td>{{ $forecast->week_start->format('M d') }} - {{ $forecast->week_end->format('M d, Y') }}</td>
              <td>{{ $forecast->selection_deadline_at->format('d M Y, H:i') }}</td>
              <td>
                @php
                  $statusBadges = [
                      'draft' => 'bg-label-secondary',
                      'published' => 'bg-label-success',
                      'closed' => 'bg-label-danger',
                  ];
                @endphp
                <span class="badge {{ $statusBadges[$forecast->status] }} text-capitalize">{{ $forecast->status }}</span>
              </td>
              <td>
                <a href="{{ route('admin.forecasts.show', $forecast) }}" class="btn btn-sm btn-icon item-edit"
                  title="View Details"><i class="ti tabler-eye"></i></a>
                <button type="button" class="btn btn-sm btn-icon" title="Edit Forecast"
                  data-bs-toggle="modal" data-bs-target="#editForecastModal"
                  data-forecast-id="{{ $forecast->id }}"
                  data-forecast-name="{{ $forecast->name }}"
                  data-status="{{ $forecast->status }}"
                  data-deadline="{{ $forecast->selection_deadline_at?->format('Y-m-d\TH:i') }}"
                  data-city="{{ $forecast->city_code }}">
                  <i class="ti tabler-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon" title="Replace Slots"
                  data-bs-toggle="modal" data-bs-target="#replaceForecastModal"
                  data-forecast-id="{{ $forecast->id }}"
                  data-forecast-name="{{ $forecast->name }}"
                  data-week-start="{{ $forecast->week_start->toDateString() }}">
                  <i class="ti tabler-upload"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">No forecasts found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($forecasts->hasPages())
      <div class="card-footer">
        {{ $forecasts->links() }}
      </div>
    @endif
  </div>
@endsection

@push('pricing-script')
  @php
    $now = \Carbon\Carbon::now();
    $defaultWeekStart = $now->copy()->startOfWeek();
    $defaultDeadline = $defaultWeekStart->copy()->subDays(2)->setTime(18, 0);
  @endphp
  <div class="modal fade" id="uploadForecastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" action="{{ route('admin.forecasts.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Upload Forecast from File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="file" class="form-label">File (CSV/TSV exported from Excel)</label>
            <input type="file" class="form-control" id="file" name="file" accept=".csv,.tsv,.txt" required>
            <small class="text-muted">Exporta el Excel como CSV o TSV. Ignoramos la columna "Total general".</small>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="week_start" class="form-label">Week Start (Mon)</label>
              <input type="date" class="form-control" id="week_start" name="week_start" value="{{ $defaultWeekStart->toDateString() }}" required>
            </div>
            <div class="col-md-6">
              <label for="selection_deadline_at" class="form-label">Selection Deadline</label>
              <input type="datetime-local" class="form-control" id="selection_deadline_at" name="selection_deadline_at" value="{{ $defaultDeadline->format('Y-m-d\TH:i') }}" required>
            </div>
          </div>
          <div class="row g-3 mt-0">
            <div class="col-md-6">
              <label for="status" class="form-label">Status</label>
              <select id="status" name="status" class="form-select">
                <option value="draft">Draft</option>
                <option value="published" selected>Published</option>
                <option value="closed">Closed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="name" class="form-label">Name (optional)</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Week 35 (2025)">
            </div>
            <div class="col-md-6">
              <label for="city_code" class="form-label">City</label>
              <select id="city_code" name="city_code" class="form-select" required>
                <option value="FIG">FIG</option>
                <option value="GRO">GRO</option>
                <option value="CAL">CAL</option>
                <option value="MAT">MAT</option>
                <option value="BCN">BCN</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
  <div class="modal fade" id="replaceForecastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="replaceForecastForm" method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Replace Slots for <span id="replaceForecastName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <small class="text-muted">Week start: <span id="replaceWeekStart"></span></small>
          </div>
          <div class="mb-3">
            <label for="replace_file" class="form-label">File (CSV/TSV)</label>
            <input type="file" class="form-control" id="replace_file" name="file" accept=".csv,.tsv,.txt" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="replace_status" class="form-label">Status</label>
              <select id="replace_status" name="status" class="form-select">
                <option value="">(keep)</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="closed">Closed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="replace_selection_deadline_at" class="form-label">Selection Deadline</label>
              <input type="datetime-local" id="replace_selection_deadline_at" name="selection_deadline_at" class="form-control">
            </div>
            <div class="col-12">
              <label for="replace_name" class="form-label">Name</label>
              <input type="text" id="replace_name" name="name" class="form-control" placeholder="(keep)">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Replace</button>
        </div>
      </form>
    </div>
  </div>
  <div class="modal fade" id="editForecastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="editForecastForm" method="POST" action="#">
        @csrf
        @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title">Edit Forecast</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="edit_name" class="form-label">Name</label>
              <input type="text" id="edit_name" name="name" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="edit_status" class="form-label">Status</label>
              <select id="edit_status" name="status" class="form-select">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="closed">Closed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_selection_deadline_at" class="form-label">Selection Deadline</label>
              <input type="datetime-local" id="edit_selection_deadline_at" name="selection_deadline_at" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="edit_city_code" class="form-label">City</label>
              <select id="edit_city_code" name="city_code" class="form-select">
                <option value="">(none)</option>
                <option value="FIG">FIG</option>
                <option value="GRO">GRO</option>
                <option value="CAL">CAL</option>
                <option value="MAT">MAT</option>
                <option value="BCN">BCN</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var replaceModal = document.getElementById('replaceForecastModal');
      if (!replaceModal) return;
      replaceModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-forecast-id');
        var name = button.getAttribute('data-forecast-name');
        var weekStart = button.getAttribute('data-week-start');
        document.getElementById('replaceForecastName').textContent = name;
        document.getElementById('replaceWeekStart').textContent = weekStart;
        var form = document.getElementById('replaceForecastForm');
        form.action = '{{ url('admin/forecasts') }}/' + id + '/upload';
      });
      var editModal = document.getElementById('editForecastModal');
      if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
          var btn = event.relatedTarget;
          var id = btn.getAttribute('data-forecast-id');
          document.getElementById('edit_name').value = btn.getAttribute('data-forecast-name') || '';
          document.getElementById('edit_status').value = btn.getAttribute('data-status') || 'draft';
          document.getElementById('edit_selection_deadline_at').value = btn.getAttribute('data-deadline') || '';
          document.getElementById('edit_city_code').value = btn.getAttribute('data-city') || '';
          var f = document.getElementById('editForecastForm');
          f.action = '{{ url('admin/forecasts') }}/' + id;
        });
      }
    });
  </script>
@endpush
