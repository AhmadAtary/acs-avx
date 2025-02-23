<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header"></div>

    <div class="sidebar-nav">
        <ul class="metismenu" id="sidenav">
            @php
                $user = Auth::user();
                $permissions = $user->access->permissions ?? [];
            @endphp

            <!-- Dashboard -->
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-label">
                    <div class="parent-icon"><i class="material-icons-outlined">home</i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>

            <!-- Devices (Only for Super Admins) -->
            @if($user->access->role == 'owner')
                <li class="{{ request()->routeIs('devices.*') ? 'active' : '' }}">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="material-icons-outlined">devices</i></div>
                        <div class="menu-title">Devices</div>
                    </a>
                    <ul>
                        <li class="{{ request()->routeIs('devices.all') ? 'active' : '' }}">
                            <a href="{{ route('devices.all') }}">
                                <i class="material-icons-outlined">arrow_right</i>All Devices
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('device.model') ? 'active' : '' }}">
                            <a href="{{ route('device.model') }}">
                                <i class="material-icons-outlined">arrow_right</i>Devices per Model
                            </a>
                        </li>
                    </ul>
                </li>
            @endif 

            <!-- Users Management -->
            @if(isset($permissions['user_management']['view']) && $permissions['user_management']['view'])
                <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-label">
                        <div class="parent-icon"><i class="lni lni-network"></i></div>
                        <div class="menu-title">Users Management</div>
                    </a>
                </li>
            @endif

            <!-- Files Management -->
            @if(isset($permissions['files_management']['view']) && $permissions['files_management']['view'])
                <li class="{{ request()->routeIs('files.index') ? 'active' : '' }}">
                    <a href="{{ route('files.index') }}" class="menu-label">
                        <div class="parent-icon"><i class="lni lni-files"></i></div>
                        <div class="menu-title">Files Management</div>
                    </a>
                </li>
            @endif

            <!-- Bulk Actions -->
            @if(isset($permissions['bulk_actions']['view']) && $permissions['bulk_actions']['view'])
                <li class="{{ request()->routeIs('bulk-actions.index') ? 'active' : '' }}">
                    <a href="{{ route('bulk-actions.index') }}" class="menu-label">
                        <div class="parent-icon"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="menu-title">Bulk Actions</div>
                    </a>
                </li>
            @endif

            <!-- Models Management -->
            @if(isset($permissions['models_management']['view']) && $permissions['models_management']['view'])
                <li class="{{ request()->routeIs('device-models.index') ? 'active' : '' }}">
                    <a href="{{ route('device-models.index') }}" class="menu-label">
                        <div class="parent-icon"><i class="fa-solid fa-cubes"></i></div>
                        <div class="menu-title">Models Management</div>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</aside>
