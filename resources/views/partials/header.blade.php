<nav class="navbar navbar-expand align-items-center gap-4">
  <div class="btn-toggle">
    <a href="javascript:void(0);"><i class="material-icons-outlined">menu</i></a>
  </div>
  <ul class="navbar-nav ms-auto align-items-center">
    <li class="nav-item dropdown">
      <a href="{{ route('profile.show') }}" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
        <i class="material-icons-outlined">account_circle</i>
      </a>
      <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
        <a class="dropdown-item gap-2 py-2" href="{{ route('profile.show') }}">
          <div class="text-center">
            <h5 class="user-name mb-0 fw-bold">{{ Auth::user()->name }}</h5>
          </div>
        </a>
        <hr class="dropdown-divider">
        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('profile.show') }}">
          <i class="material-icons-outlined">person_outline</i>Profile
        </a>
      </div>
    </li>
  </ul>
</nav>