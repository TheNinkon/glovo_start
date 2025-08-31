@extends('layouts/layoutMaster')

@section('title', 'Riders List')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/admin/riders/riders-list.js'])
@endsection

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Total Riders</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <span>All riders in system</span>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-users ti-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Active Riders</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['active'] }}</h4>
              </div>
              <span>Ready to work</span>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-user-check ti-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Inactive Riders</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['inactive'] }}</h4>
              </div>
              <span>Not currently working</span>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti tabler-user-off ti-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Blocked Riders</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['blocked'] }}</h4>
              </div>
              <span>Access restricted</span>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti tabler-user-x ti-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-3">Search Filter</h5>
      <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
        <div class="col-md-4 rider_status"></div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-riders table">
        <thead class="border-top">
          <tr>
            <th>Rider</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Supervisor</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRider" aria-labelledby="offcanvasAddRiderLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasAddRiderLabel" class="offcanvas-title">Add Rider</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
        <form class="add-new-rider pt-0" id="addNewRiderForm">
          @csrf
          <input type="hidden" name="id" id="rider_id">
          <div class="mb-3">
            <label class="form-label" for="add-rider-name">Full Name</label>
            <input type="text" class="form-control" id="add-rider-name" placeholder="John Doe" name="name"
              required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-rider-email">Email</label>
            <input type="email" id="add-rider-email" class="form-control" placeholder="john.doe@example.com"
              name="email" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-rider-phone">Phone</label>
            <input type="text" id="add-rider-phone" class="form-control" placeholder="+1 (609) 988-44-11"
              name="phone" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-rider-status">Status</label>
            <select id="add-rider-status" name="status" class="form-select">
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
              <option value="blocked">Blocked</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection
