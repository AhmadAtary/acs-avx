
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
      <div class="logo-name flex-grow-1">
        <img src="{{ asset('assets/AVXAV Logos/logo_black.png') }}" class="logo-img" alt="">
      </div>
      <div class="logo-name flex-grow-1">
        <h5 class="mb-0">AVXAV</h5>
      </div>
      <!-- <div class="sidebar-close">
        <span class="material-icons-outlined">close</span>
      </div> -->
    </div>
    <div class="sidebar-nav">
      <ul class="metismenu" id="sidenav">
        @php
          $user = Auth::user();
        @endphp

        <li>
            <a href="{{ route('dashboard') }}" class="menu-label">
              <div class="parent-icon"><i class="material-icons-outlined">home</i></div>
              <div class="menu-title">Dashboard</div>
            </a>
          </li>
        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="material-icons-outlined">devices</i></div>
            <div class="menu-title">Devices</div>
          </a>
          <ul>
          @if (str_contains(request()->url(), 'device-info/'))
            <li class="{{ request()->routeIs('devices.all') ? 'active' : '' }}">
              <a href="{{ route('devices.all') }}">
                <i class="material-icons-outlined">arrow_right</i>All Devices
              </a>
            </li>
            <li class="{{ request()->routeIs('device-info') ? 'active' : '' }}">
              <a href="">
                <i class="material-icons-outlined"></i>Device Info
              </a>
            </li>
          @else
            <li class="{{ request()->routeIs('devices.all') ? 'active' : '' }}">
              <a href="{{ route('devices.all') }}">
                <i class="material-icons-outlined">arrow_right</i>All Devices
              </a>
            </li>
          @endif
          <!-- request()->routeIs('device.modelShow') ? 'active' : '' -->
          <li class="{{ (request()->routeIs('device.model') || request()->routeIs('device.modelShow'))  ? 'active' : '' }}"><a href="{{ route('device.model') }}"><i class="material-icons-outlined">arrow_right</i>Devices per Model</a></li>
          </ul>
        </li>
        @if($user->access->permissions['view_user'])
          <li>
            <a href="{{ route('users.index') }}" class="menu-label">
              <div class="parent-icon"><i class="lni lni-network"></i></div>
              <div class="menu-title">Users Managment</div>
            </a>
          </li>
          <li>
            <a href="{{ route('files.index') }}" class="menu-label">
              <div class="parent-icon"><i class="lni lni-files"></i></div>
              <div class="menu-title">Files Managment</div>
            </a>
          </li>
          <li>
            <a href="{{ route('users.index') }}" class="menu-label">
              <div class="parent-icon"><i class="fa-solid fa-layer-group"></i></div>
              <div class="menu-title">Bulk Actions</div>
            </a>
          </li>
        @endif

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="material-icons-outlined">widgets</i></div>
            <div class="menu-title">Widgets</div>
          </a>
          <ul>
            <li><a href="widgets-data.html"><i class="material-icons-outlined">arrow_right</i>Data</a></li>
            <li><a href="widgets-static.html"><i class="material-icons-outlined">arrow_right</i>Static</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </aside>
  