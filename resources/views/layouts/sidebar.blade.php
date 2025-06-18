<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-grid"></i>
        <span>Home</span>
      </a>
    </li>
    @if ($role=='Admin')
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('tickets.index') }}">
        <i class="bi bi-ticket-detailed-fill"></i>
        <span>All Tickets</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('tickets.create') }}">
        <i class="bi bi-ticket-detailed-fill"></i>
        <span>Create Ticket</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('user')}}">
        <i class="bi bi-person"></i>
        <span>User Management</span>
      </a>
    </li>
    @endif

    @if ($role=='Advertisement' )
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Master Data </span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="master-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{route('/master-data/mipr-no')}}">
            <i class="bi bi-circle"></i><span>MIPR No</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/mipr-file-no')}}">
            <i class="bi bi-circle"></i><span>MIPR File No</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/departments')}}">
            <i class="bi bi-circle"></i><span>Departments</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/empanelled-newspaper')}}">
            <i class="bi bi-circle"></i><span>Empanelled Organizations</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/rates-for-advertisements')}}">
            <i class="bi bi-circle"></i><span>Rates of Advertisement</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/newspaper_types')}}">
            <i class="bi bi-circle"></i><span>Media Types</span>
          </a>
        </li>
        <li>
          <a href="{{ route('/master-data/advertisement-types') }}">
            <i class="bi bi-circle"></i><span>Advertisement Types</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/subject')}}">
            <i class="bi bi-circle"></i><span>Subject of Advertisement</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/color')}}">
            <i class="bi bi-circle"></i><span>Color of Advertisement</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/page-info')}}">
            <i class="bi bi-circle"></i><span>Page Information</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/department-category')}}">
            <i class="bi bi-circle"></i><span>Department Categories</span>
          </a>
        </li>
        <li>
          <a href="{{route('/master-data/gst-rates')}}">
            <i class="bi bi-circle"></i><span>GST Rates</span>
          </a>
        </li>
      </ul>
    </li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{route('advertisements')}}">
        <i class="bi bi-badge-ad"></i>
        <span>Advertisements</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" href="{{route('bills')}}">
        <i class="bi bi-currency-rupee"></i>
        <span>Bills</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-text"></i><span>Reports </span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reports-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{route('/reports/issue-register')}}">
            <i class="bi bi-circle"></i><span>Issue Register</span>
          </a>
        </li>
        <li>
          <a href="{{route('/reports/billing-register')}}">
            <i class="bi bi-circle"></i><span>Billing Register (paid by DIPR)</span>
          </a>
        </li>
        <li>
          <a href="{{route('reports/not_paid_by_dipr')}}">
            <i class="bi bi-circle"></i><span>Billing Register (paid by dept.)</span>
          </a>
        </li>
        <li>
          <a href="">
            <i class="bi bi-circle"></i><span>Detailed Expenditure Report</span>
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if ($role=='Billing' )
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{route('bills')}}">
        <i class="bi bi-person"></i>
        <span>Bills</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Reports </span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reports-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{route('/reports/issue-register')}}">
            <i class="bi bi-circle"></i><span>Issue Register</span>
          </a>
        </li>
        <li>
          <a href="{{route('/reports/billing-register')}}">
            <i class="bi bi-circle"></i><span>Billing Register (paid by DIPR)</span>
          </a>
        </li>
        <li>
          <a href="{{route('reports/not_paid_by_dipr')}}">
            <i class="bi bi-circle"></i><span>Billing Register (paid by dept.)</span>
          </a>
        </li>
        <li>
          <a href="">
            <i class="bi bi-circle"></i><span>Detailed Expenditure Report</span>
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if ($role=='User')
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('tickets.index') }}">
        <i class="bi bi-ticket-detailed"></i>
        <span>All Tickets</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{ route('tickets.create') }}">
        <i class="bi bi-ticket-detailed"></i>
        <span>Create Ticket</span>
      </a>
    </li>
    @endif

    <!-- End Login Page Nav -->

  </ul>
</aside>