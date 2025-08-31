@extends('layouts/layoutMaster')

@section('title', 'Create Forecast')

@section('content')
  <div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="mb-0"><span class="text-muted fw-light">Admin / Forecasts /</span> Create</h4>
    <a href="{{ route('admin.forecasts.index') }}" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Back</a>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0">New Forecast</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.forecasts.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label" for="week_start">Week Start</label>
            <input type="date" id="week_start" name="week_start" class="form-control" value="{{ old('week_start') }}" required>
            @error('week_start')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label" for="week_end">Week End</label>
            <input type="date" id="week_end" name="week_end" class="form-control" value="{{ old('week_end') }}" required>
            @error('week_end')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="selection_deadline_at">Selection Deadline</label>
            <input type="datetime-local" id="selection_deadline_at" name="selection_deadline_at" class="form-control" value="{{ old('selection_deadline_at') }}" required>
            @error('selection_deadline_at')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select" required>
              @php $statuses = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed']; @endphp
              @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status')===$value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
@endsection

