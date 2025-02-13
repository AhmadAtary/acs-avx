<nav class="navbar navbar-expand align-items-center gap-4">
  <div class="btn-toggle">
    <a href="javascript:void(0);"><i class="material-icons-outlined">menu</i></a>
  </div>

  @php
                $user = Auth::user();
            @endphp
  <!-- Search Bar -->
  <div class="position-relative">
    @if(!$user->access->role == 'cs')
    <form class="d-flex" onsubmit="return redirectToDeviceInfo(event)">
      <input
        id="serialNumberInput"
        class="form-control rounded-5 px-5"
        type="text"
        placeholder="Enter Device Serial Number"
        style="width: 450px;"
        required
      />
      <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
      <span type="button" class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 mobile-search-close" onclick="clearInput()">close</span>
    </form>
    @endif
  </div>

  <!-- User Profile Dropdown -->
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

<script>
  function redirectToDeviceInfo(event) {
    event.preventDefault();
    const serialNumber = document.getElementById('serialNumberInput').value.trim();
    if (serialNumber) {
      const targetUrl = `/device-info/${encodeURIComponent(serialNumber)}`;
      window.location.href = targetUrl;
    } else {
      alert('Please enter a serial number.');
    }
  }

  function clearInput() {
    document.getElementById('serialNumberInput').value = '';
  }
</script>
