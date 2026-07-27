<header class="main-header bg-white border-end border-2">
    <div class="nav-strip-brand d-flex justify-content-between align-items-center">
        <span>
            <img class="img-fluid nav-brand-img" src="{{URL::to('assets/img/SH-Final-Logo.png')}}" alt="">
        </span>
        <a href="#" class="btn-burger burger-close">
            <img class="img-fluid" width="18" src="{{URL::to('assets/img/burger-menu.svg')}}" alt="">
        </a>
    </div>

    <div class="nav-sub-strip-container">
        <div class="nav-sub-strip">

            @auth
            @php $role = auth()->user()->role; @endphp

            <div class="nav flex-column">
                    <div class="list-group">
                        <a href="{{route('admin.dashboard')}}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                           Dashboard
                        </a>
                    </div>
                {{-- ================= ADMIN ONLY ================= --}}
                @if((int) $role === 1)
                    <div class="list-group">
                        <a href="{{route('admin.specializations')}}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/specializations*') ? 'active' : '' }}">
                           Specializations
                        </a>
                    </div>

                    <div class="list-group">
                        <a href="{{route('admin.doctors')}}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/doctors*') ? 'active' : '' }}">
                           Doctors
                        </a>
                    </div>

                @endif


                {{-- ================= ROLE 1 + ROLE 4 ================= --}}
                @if(in_array($role, [1,3,5]))

                    <div class="list-group">
                        <a href="{{route('patients.index')}}"
                           class="list-group-item list-group-item-action {{ request()->is('patients*') ? 'active' : '' }}">
                           Patients
                        </a>
                    </div>

                    @php
                        $currentUrl = request()->fullUrl();
                        $isActive = request()->is('admin/appointments-report*')
                                    || str_contains($currentUrl, 'manualappointment/patientcreate?action=appointment');
                    @endphp

                    <div class="list-group">
                        <a href="{{ route('admin.appointments.report') }}"
                           class="list-group-item list-group-item-action {{ $isActive ? 'active' : '' }}">
                           Appointments
                        </a>
                    </div>

                    @if(in_array((int) $role, [1, 3], true))
                    <div class="list-group">
                        <a href="{{ route('admin.follow-ups.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.follow-ups.*') ? 'active' : '' }}">
                           Follow-ups
                        </a>
                    </div>
                    @endif

                    <div class="list-group">
                        <a href="{{ route('admin.payment.report') }}"
                           class="list-group-item list-group-item-action
                           {{ request()->routeIs('admin.payment.report') && request('payment_status') !== 'failed' ? 'active' : '' }}">
                           Payments
                        </a>
                    </div>

                    <div class="list-group">
                        <a href="{{ route('admin.payment.report', ['payment_status' => 'failed']) }}"
                           class="list-group-item list-group-item-action
                           {{ request()->routeIs('admin.payment.report') && request('payment_status') == 'failed' ? 'active' : '' }}">
                           Failed Payments
                        </a>
                    </div>

                @endif


                {{-- ================= ADMIN + RECEPTION ================= --}}
                @if(in_array((int) $role, [1, 3], true))
                    <div class="list-group">
                        <a href="{{ route('admin.invoices.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('invoices*') ? 'active' : '' }}">
                           Service Billing
                        </a>
                    </div>
                @endif

                {{-- ================= ADMIN ONLY ================= --}}
                @if((int) $role === 1)
                    @php $misOpen = request()->is('admin/mis*'); $misReport = request()->route('report', 'dashboard'); @endphp
                    <div class="accordion mb-2" id="sidebarReportsAndCabins">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="sidebarMisReportsHeading">
                                <button class="accordion-button {{ $misOpen ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#sidebarMisReportsCollapse"
                                        aria-expanded="{{ $misOpen ? 'true' : 'false' }}" aria-controls="sidebarMisReportsCollapse">
                                    MIS Reports
                                </button>
                            </h2>
                            <div id="sidebarMisReportsCollapse" class="accordion-collapse collapse {{ $misOpen ? 'show' : '' }}"
                                 aria-labelledby="sidebarMisReportsHeading" data-bs-parent="#sidebarReportsAndCabins">
                                <div class="accordion-body">
                                    <div class="list-group list-group-sm">
                                        <a href="{{ route('admin.mis.index', 'dashboard') }}" class="list-group-item list-group-item-action {{ $misReport === 'dashboard' ? 'active' : '' }}">Dashboard</a>
                                        <a href="{{ route('admin.mis.index', 'collection-summary') }}" class="list-group-item list-group-item-action {{ $misReport === 'collection-summary' ? 'active' : '' }}">Collection Summary</a>
                                        <a href="{{ route('admin.mis.index', 'doctor-collection') }}" class="list-group-item list-group-item-action {{ $misReport === 'doctor-collection' ? 'active' : '' }}">Doctor-wise Collection</a>
                                        <a href="{{ route('admin.mis.index', 'service-reports') }}" class="list-group-item list-group-item-action {{ $misReport === 'service-reports' ? 'active' : '' }}">Service Reports</a>
                                        <a href="{{ route('admin.mis.index', 'source-referral') }}" class="list-group-item list-group-item-action {{ $misReport === 'source-referral' ? 'active' : '' }}">Source &amp; Referral</a>
                                        <a href="{{ route('admin.mis.index', 'patient-visits') }}" class="list-group-item list-group-item-action {{ $misReport === 'patient-visits' ? 'active' : '' }}">Patient Visit</a>
                                        <a href="{{ route('admin.mis.index', 'appointment-operations') }}" class="list-group-item list-group-item-action {{ $misReport === 'appointment-operations' ? 'active' : '' }}">Appointment Operations</a>
                                        <a href="{{ route('admin.mis.index', 'payment-closing') }}" class="list-group-item list-group-item-action {{ $misReport === 'payment-closing' ? 'active' : '' }}">Payment Mode &amp; Closing</a>
                                        <a href="{{ route('admin.mis.index', 'discount-report') }}" class="list-group-item list-group-item-action {{ $misReport === 'discount-report' ? 'active' : '' }}">Discount Report</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @php $cabinsOpen = request()->is('admin/cabins*'); @endphp
                    <div class="accordion-item">
                            <h2 class="accordion-header" id="sidebarCabinHeading">
                                <button
                                    class="accordion-button {{ $cabinsOpen ? '' : 'collapsed' }}"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#sidebarCabinCollapse"
                                    aria-expanded="{{ $cabinsOpen ? 'true' : 'false' }}"
                                    aria-controls="sidebarCabinCollapse">
                                    Cabin Management
                                </button>
                            </h2>
                            <div
                                id="sidebarCabinCollapse"
                                class="accordion-collapse collapse {{ $cabinsOpen ? 'show' : '' }}"
                                aria-labelledby="sidebarCabinHeading"
                                data-bs-parent="#sidebarReportsAndCabins">
                                <div class="accordion-body">
                                    <div class="list-group list-group-sm">
                                        <a href="{{ route('admin.cabins.dashboard') }}"
                                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.dashboard') ? 'active' : '' }}">
                                           Dashboard
                                        </a>
                                        @if((int) $role === 1)
                                            <a href="{{ route('admin.cabins.index') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.index', 'admin.cabins.create', 'admin.cabins.edit') ? 'active' : '' }}">
                                               Cabins
                                            </a>
                                            <a href="{{ route('admin.cabins.bookings.index') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.bookings.*') ? 'active' : '' }}">
                                               Bookings
                                            </a>
                                            <a href="{{ route('admin.cabins.subscriptions.index') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.subscriptions.*') ? 'active' : '' }}">
                                               Subscriptions
                                            </a>
                                            <a href="{{ route('admin.cabins.invoices.index') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.invoices.*') ? 'active' : '' }}">
                                               Invoices
                                            </a>
                                            <a href="{{ route('admin.cabins.facilities.index') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.facilities.*') ? 'active' : '' }}">
                                               Facilities
                                            </a>
                                            <a href="{{ route('admin.cabins.reports') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.reports') ? 'active' : '' }}">
                                               Reports
                                            </a>
                                            <a href="{{ route('admin.cabins.settings') }}"
                                               class="list-group-item list-group-item-action {{ request()->routeIs('admin.cabins.settings') ? 'active' : '' }}">
                                               Settings
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if((int) $role === 1)
                    <div class="list-group">
                        <a href="{{ route('admin.appointment-config.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/appointment-config*') ? 'active' : '' }}">
                           Appointment Config
                        </a>
                    </div>
                @endif
                @if(in_array($role, [1]))
                    <div class="list-group">
                        <a href="{{ route('admin.users') }}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/users') ? 'active' : '' }}">
                           Users
                        </a>
                    </div>
                    <hr>
                    <div class="list-group">
                        <a href="{{ route('admin.registration-fees.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('registration-fees*') ? 'active' : '' }}">
                           Registration Fee
                        </a>
                    </div>

                    <div class="list-group">
                        <a href="{{ route('admin.services.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('services*') ? 'active' : '' }}">
                           Services Setup
                        </a>
                    </div>
                    <div class="list-group">
                        <a href="{{ route('admin.sources.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/sources*') ? 'active' : '' }}">
                           Sources
                        </a>
                    </div>
                    <div class="list-group">
                        <a href="{{ route('admin.medicines.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/medicines*') ? 'active' : '' }}">
                           Medicines
                        </a>
                    </div>
                    <div class="list-group">
                        <a href="{{ route('admin.icd10.index') }}"
                           class="list-group-item list-group-item-action {{ request()->is('admin/icd10*') ? 'active' : '' }}">
                           ICD10 Data
                        </a>
                    </div>
                @endif

            </div>

            @endauth

        </div>
    </div>
</header>
