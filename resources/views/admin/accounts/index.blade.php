@extends('layouts/layoutMaster')

@section('title', 'Accounts List')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/admin/accounts/accounts-list.js'])
@endsection

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Total Accounts</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <span>All accounts in system</span>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i
                  class="ti tabler-device-sim ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Active Accounts</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['active'] }}</h4>
              </div>
              <span>Ready for assignment</span>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i
                  class="ti tabler-device-sim-check ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Inactive Accounts</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['inactive'] }}</h4>
              </div>
              <span>Currently not in use</span>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-secondary"><i
                  class="ti tabler-device-sim-off ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Blocked Accounts</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['blocked'] }}</h4>
              </div>
              <span>Service restricted</span>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i
                  class="ti tabler-device-sim-x ti-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-3">Search Filter</h5>
      <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
        <div class="col-md-4 account_status"></div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-accounts table">
        <thead class="border-top">
          <tr>
            <th>Courier ID</th>
            <th>Status</th>
            <th>Current Rider</th>
            <th>Delivery Date</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddAccount"
      aria-labelledby="offcanvasAddAccountLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasAddAccountLabel" class="offcanvas-title">Add Account</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
        <form class="add-new-account pt-0" id="addNewAccountForm">
          @csrf
          <input type="hidden" name="id" id="account_id">
          <div class="mb-3">
            <label class="form-label" for="add-account-courier-id">Courier ID</label>
            <input type="text" class="form-control" id="add-account-courier-id" placeholder="GLV-12345678"
              name="courier_id" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-account-status">Status</label>
            <select id="add-account-status" name="status" class="form-select">
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
              <option value="blocked">Blocked</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-account-delivery-date">Date of Delivery</label>
            <input type="date" class="form-control" id="add-account-delivery-date" name="date_of_delivery" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-account-return-date">Date of Return</label>
            <input type="date" class="form-control" id="add-account-return-date" name="date_of_return" />
          </div>
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection
