@php
  // Importamos las fachadas necesarias para la vista.
  use Illuminate\Support\Facades\Auth;
@endphp

@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-sm"></i>
    </a>
  </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

  <ul class="navbar-nav flex-row align-items-center ms-auto">

    {{-- User Dropdown (Nuestra versión limpia y con la estructura correcta) --}}
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <div class="d-flex">
              <div class="flex-shrink-0 me-3">
                <div class="avatar avatar-online">
                  <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                </div>
              </div>
              <div class="flex-grow-1">
                <span class="fw-medium d-block">{{ Auth::user() ? Auth::user()->name : 'Invitado' }}</span>
                @if (Auth::user())
                  <small class="text-muted">
                    {{ ucfirst(Auth::user()->getRoleNames()->first()) }}
                  </small>
                @endif
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li>
          <a class="dropdown-item" href="#"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class='ti tabler-logout me-2 ti-sm'></i>
            <span class="align-middle">Cerrar Sesión</span>
          </a>
        </li>
      </ul>
    </li>
    {{-- / User --}}

  </ul>
</div>

{{-- Formulario oculto para el Logout --}}
@if (Auth::check())
  @php
    $logoutRoute = 'login'; // Ruta por defecto
    if (Auth::user()->hasRole('super-admin')) {
        $logoutRoute = 'admin.logout';
    } elseif (Auth::user()->hasRole('rider')) {
        $logoutRoute = 'rider.logout';
    }
  @endphp
  <form method="POST" id="logout-form" action="{{ route($logoutRoute) }}" style="display: none;">
    @csrf
  </form>
@endif
