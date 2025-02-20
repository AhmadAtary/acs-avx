<nav class="navbar navbar-expand align-items-center gap-4">
  <div class="btn-toggle">
    <a href="javascript:void(0);"><i class="material-icons-outlined">menu</i></a>
  </div>

  @php
      $user = Auth::user();
  @endphp

  <img style="width:150px;" src="{{ asset('assets/AVXAV Logos/logo_black.png') }}" class="logo-img" alt="">

  <!-- Search Bar -->
  <div class="position-relative">
    @if($user->access->role == 'owner')
      <!-- Desktop Search (Always Visible) -->
      <form class="d-flex d-none d-md-flex" onsubmit="return redirectToDeviceInfo(event)">
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
            <!-- Mobile Search Button -->
    <a class="d-md-none" onclick="openSearchPopup()" style="margin-right: 10px;"><i class="material-icons-outlined">search</i></a>
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
        <hr class="dropdown-divider">
        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="material-icons-outlined">logout</i>Logout
        </a>
      </div>
    </li>
  </ul>

  <!-- Hidden Logout Form -->
  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
  </form>
</nav>

<!-- Mobile Search Popup (Modal) -->
<div id="mobileSearchPopup" class="search-popup">
  <div class="search-popup-content">
    <span class="close-btn" onclick="closeSearchPopup()">&times;</span>
    <h4 class="text-center">Search for a Device</h4>
    <form class="d-flex mt-3" onsubmit="return redirectToDeviceInfo(event)">
      <input
        id="mobileSerialNumberInput"
        class="form-control rounded-5 px-5"
        type="text"
        placeholder="Enter Serial Number"
        required
      />
      <button type="submit" class="btn btn-primary ms-2">Search</button>
    </form>
  </div>
</div>

<script>
  function redirectToDeviceInfo(event) {
    event.preventDefault();
    const serialNumber = document.getElementById('serialNumberInput')?.value.trim() ||
                         document.getElementById('mobileSerialNumberInput')?.value.trim();

    if (serialNumber) {
      const routeUrl = "{{ route('device.info', ['serialNumber' => ':serialNumber']) }}";
      const targetUrl = routeUrl.replace(':serialNumber', encodeURIComponent(serialNumber));
      window.location.href = targetUrl;
    } else {
      alert('Please enter a serial number.');
    }
  }

  function clearInput() {
    document.getElementById('serialNumberInput').value = '';
    document.getElementById('mobileSerialNumberInput').value = '';
  }

  function openSearchPopup() {
    document.getElementById('mobileSearchPopup').classList.add('active');
  }

  function closeSearchPopup() {
    document.getElementById('mobileSearchPopup').classList.remove('active');
  }
</script>

<style>
  /* Search Popup (Modal) */
  .search-popup {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
  }

  .search-popup.active {
    display: flex;
  }

  .search-popup-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    text-align: center;
    position: relative;
  }

  .close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
  }

  .search-popup form {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .search-popup input {
    width: 100%;
  }
</style>
