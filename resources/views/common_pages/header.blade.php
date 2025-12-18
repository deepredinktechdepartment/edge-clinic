<header class="main-header bg-white border-end border-2">
        <div class="nav-strip-brand d-flex justify-content-between align-items-center">
            <span>
                <img class="img-fluid nav-brand-img" src="{{URL::to('assets/img/SH-Final-Logo.png')}}" alt="">
            </span>
            <a href="#" class="btn-burger burger-close"><img class="img-fluid" width="18"
                    src="{{URL::to('assets/img/burger-menu.svg')}}" alt=""></a>
        </div>
      
        <div class="nav-sub-strip-container">
            <div class="nav-sub-strip">

                <div class="nav flex-column">
                    <div class="list-group">
                        <a href="{{route('admin.dashboard')}}" class="list-group-item list-group-item-action {{ (request()->is('admin/dashboard')) ? 'active' : '' }}">Dashboard</a>
                    </div>
                    <div class="list-group">
                        <a href="{{route('admin.specializations')}}" class="list-group-item list-group-item-action {{ (request()->is('admin/specializations') || (request()->is('admin/specializations/*'))) ? 'active' : '' }}">Specializations</a>
                    </div>

                    <div class="list-group">
                        <a href="{{route('admin.doctors')}}" class="list-group-item list-group-item-action {{ (request()->is('admin/doctors') || (request()->is('admin/doctors/*'))) ? 'active' : '' }}">Doctors</a>
                    </div>
                     <div class="list-group">
                        <a href="{{route('patients.index')}}" class="list-group-item list-group-item-action {{ (request()->is('patients') || (request()->is('patients/*'))) ? 'active' : '' }}">Patients</a>
                    </div>
                     <div class="list-group">
                        <a href="{{route('admin.appointments.report')}}" class="list-group-item list-group-item-action {{ (request()->is('admin/appointments-report') || (request()->is('admin/appointments-report/*'))) ? 'active' : '' }}">Appointments</a>
                    </div>
                     <div class="list-group">
                        <a href="{{route('admin.payment.report')}}" class="list-group-item list-group-item-action {{ (request()->is('admin/payment/report') || (request()->is('admin/payment/report/*'))) ? 'active' : '' }}">Payments</a>
                    </div>


                </div>

             @auth
    @if(auth()->user()->role == 1)
        <div class="nav flex-column">
            <div class="list-group">
                <a href="{{ route('admin.users') }}"
                   class="list-group-item list-group-item-action {{ request()->is('admin/users') ? 'active' : '' }}">
                    Users
                </a>
            </div>
        </div>
    @endif
@endauth
            </div>
        </div>

    </header>