@extends('layouts/layoutMaster')

@section('title', 'Rider Details')

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/page-user-view.scss')
@endsection

@section('content')
  <div class="row">
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
      <div class="card mb-4">
        <div class="card-body">
          <div class="user-avatar-section">
            <div class=" d-flex align-items-center flex-column">
              <img class="img-fluid rounded mb-4" src="{{ asset('assets/img/avatars/1.png') }}" height="120" width="120"
                alt="User avatar" />
              <div class="user-info text-center">
                <h4>{{ $rider->name }}</h4>
                <span
                  class="badge bg-label-secondary">{{ $rider->user ? ucfirst($rider->user->getRoleNames()->first()) : 'Rider' }}</span>
              </div>
            </div>
          </div>
          <h5 class="pb-2 border-bottom mb-4">Details</h5>
          <div class="info-container">
            <ul class="list-unstyled">
              <li class="mb-3">
                <span class="fw-medium me-2">Email:</span>
                <span>{{ $rider->email }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">Status:</span>
                <span class="badge bg-label-success text-capitalize">{{ $rider->status }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">Phone:</span>
                <span>{{ $rider->phone ?? 'N/A' }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">Supervisor:</span>
                <span>{{ $rider->supervisor->name ?? 'N/A' }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
      <div class="card mb-4">
        <h5 class="card-header">Assignments History</h5>
        <div class="table-responsive mb-3">
          <table class="table datatable-project border-top">
            <thead>
              <tr>
                <th>Courier ID</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rider->assignments as $assignment)
                <tr>
                  <td>{{ $assignment->account->courier_id }}</td>
                  <td><span class="badge bg-label-info">{{ $assignment->account->status }}</span></td>
                  <td>{{ $assignment->start_date }}</td>
                  <td>{{ $assignment->end_date ?? 'Present' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">No assignments found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
