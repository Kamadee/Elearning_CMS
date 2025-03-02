<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="{{ url('/') }}" class="brand-link">
    <img src="{{ asset('path-to-your-logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3">
    <span class="brand-text font-weight-light">Admin Panel</span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        @foreach(config('adminlte.menu') as $item)
        <li class="nav-item {{ isset($item['submenu']) ? 'has-treeview' : '' }}">
          <a href="{{ isset($item['submenu']) ? '#' : (isset($item['url']) ? url($item['url']) : '#') }}" class="nav-link">
            <i class="nav-icon {{ $item['icon'] ?? 'fas fa-circle' }}"></i>
            <p>
              {{ $item['text'] ?? 'No Text' }} {{-- Gán giá trị mặc định nếu không có text --}}
              @if(isset($item['submenu'])) <i class="right fas fa-angle-left"></i> @endif
            </p>
          </a>
          @if(isset($item['submenu']))
          <ul class="nav nav-treeview">
            @foreach($item['submenu'] as $sub)
            <li class="nav-item">
              <a href="{{ isset($sub['url']) ? url($sub['url']) : '#' }}" class="nav-link">
                <i class="nav-icon {{ $sub['icon'] ?? 'far fa-circle' }}"></i>
                <p>{{ $sub['text'] ?? 'No Text' }}</p> {{-- Gán giá trị mặc định nếu không có text --}}
              </a>
            </li>
            @endforeach
          </ul>
          @endif
        </li>
        @endforeach
      </ul>
    </nav>
  </div>
</aside>