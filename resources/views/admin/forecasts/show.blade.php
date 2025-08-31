@extends('layouts/layoutMaster')

@section('title', 'Forecast Details')

@section('content')
  <div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="mb-0">
      <span class="text-muted fw-light">Admin / Forecasts /</span> {{ $forecast->name }}
    </h4>
    <a href="{{ route('admin.forecasts.index') }}" class="btn btn-label-secondary">
      <i class="ti tabler-arrow-left me-1"></i> Back
    </a>
  </div>

  <div class="row g-4">
    <div class="col-xl-4 col-lg-5 col-md-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Summary</h5></div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="mb-3"><span class="fw-medium me-2">Name:</span> {{ $forecast->name }}</li>
            <li class="mb-3"><span class="fw-medium me-2">Week:</span>
              {{ $forecast->week_start->format('M d') }} - {{ $forecast->week_end->format('M d, Y') }}
            </li>
            <li class="mb-3"><span class="fw-medium me-2">Selection Deadline:</span>
              {{ $forecast->selection_deadline_at->format('d M Y, H:i') }}
            </li>
            <li class="mb-0"><span class="fw-medium me-2">Status:</span>
              <span class="badge {{ ['draft'=>'bg-label-secondary','published'=>'bg-label-success','closed'=>'bg-label-danger'][$forecast->status] ?? 'bg-label-secondary' }} text-capitalize">{{ $forecast->status }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7 col-md-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Slots</h5>
        </div>
        <div class="card-body">
          @forelse($slotsByDate as $date => $slots)
            <div class="mb-4">
              <h6 class="text-muted mb-2">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h6>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Start</th>
                      <th>End</th>
                      <th>Capacity</th>
                      <th>Reserved</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach($slots as $slot)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                      <td>{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                      <td>{{ $slot->capacity }}</td>
                      <td>{{ method_exists($slot, 'getAssignedCountAttribute') ? $slot->assigned_count : ($slot->rider_schedules_count ?? '-') }}</td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @empty
            <p class="text-muted mb-0">No slots for this forecast.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endsection

