@extends('template_v1')

@section('content')
    <style>
        .page-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 24px;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        }
        .page-header-left h5 { margin: 0; font-weight: 700; font-size: 1rem; color: #1e293b; }
        .page-header-left small { color: #94a3b8; font-size: .74rem; }
        .doc-pill { background: var(--primary-light, #e8f0fe); border-radius: 30px; padding: 5px 14px; display: flex; align-items: center; gap: 9px; }
        .doc-pill .av { width: 30px; height: 30px; border-radius: 50%; background: var(--primary, #0d6efd); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; }
        .doc-pill span { font-size: .82rem; font-weight: 700; color: var(--primary, #0d6efd); }
        .ccard { background: #fff; border-radius: 14px; border: 1px solid #e8ecf0; margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0, 0, 0, .04); }
        .ccard-head { padding: 16px 22px 12px; border-bottom: 1px solid #f0f2f5; display: flex; align-items: center; gap: 11px; }
        .ccard-head .ico { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .ccard-head h6 { margin: 0; font-weight: 700; font-size: .9rem; color: #1e293b; }
        .ccard-head small { color: #94a3b8; font-size: .75rem; }
        .ccard-body { padding: 20px 22px; }
        .session-block { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; position: relative; background: #fafbfc; }
        .session-block.morning   { border-color: #fde68a; background: #fffbeb; }
        .session-block.afternoon { border-color: #bfdbfe; background: #eff6ff; }
        .session-block.evening   { border-color: #ddd6fe; background: #f5f3ff; }
        .session-block.night     { border-color: #fbcfe8; background: #fdf2f8; }
        .session-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px; cursor: default; }
        .label-morning   { background: #fef3c7; color: #92400e; }
        .label-afternoon { background: #dbeafe; color: #1e40af; }
        .label-evening   { background: #ede9fe; color: #5b21b6; }
        .label-night     { background: #fce7f3; color: #9d174d; }

        /* ── Dual range ─────────────────────────── */
        .dual-range-wrap { position: relative; height: 38px; margin: 4px 0; }
        .dual-range-wrap .track { position: absolute; top: 50%; transform: translateY(-50%); width: 100%; height: 5px; background: #e2e8f0; border-radius: 4px; z-index: 0; }
        .dual-range-wrap .range-fill { position: absolute; top: 50%; transform: translateY(-50%); height: 5px; border-radius: 4px; z-index: 1; pointer-events: none; }
        .dual-range-wrap input[type=range] {
            position: absolute; width: 100%; top: 50%; transform: translateY(-50%);
            appearance: none; -webkit-appearance: none;
            background: transparent; pointer-events: none; z-index: 2; margin: 0; padding: 0;
        }
        .dual-range-wrap input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%;
            background: #fff; border: 2.5px solid currentColor; cursor: pointer;
            pointer-events: all; box-shadow: 0 1px 5px rgba(0,0,0,.2);
        }
        .dual-range-wrap input[type=range]::-moz-range-thumb {
            width: 18px; height: 18px; border-radius: 50%;
            background: #fff; border: 2.5px solid currentColor; cursor: pointer;
            pointer-events: all; box-shadow: 0 1px 5px rgba(0,0,0,.2); border: none;
        }
        /* Firefox needs the track hidden on each range input */
        .dual-range-wrap input[type=range]::-moz-range-track { background: transparent; }
        .dual-range-wrap input[type=range]::-webkit-slider-runnable-track { background: transparent; }

        .range-time-labels { display: flex; justify-content: space-between; font-size: .68rem; color: #94a3b8; margin-top: 2px; }
        .time-pair { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .time-box { display: flex; align-items: center; gap: 6px; background: #fff; border: 1.5px solid #d1d9e0; border-radius: 9px; padding: 6px 10px; }
        .time-box label { font-size: .72rem; font-weight: 700; color: #64748b; margin: 0; white-space: nowrap; }
        .time-box input[type=time] { border: none; background: transparent; font-size: .9rem; font-weight: 700; color: #1e293b; outline: none; cursor: pointer; width: 90px; min-width: 80px; }
        .break-row { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #d1d9e0; }
        .break-toggle { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .btn-del-session { position: absolute; top: 12px; right: 14px; background: none; border: none; color: #ef4444; font-size: .9rem; cursor: pointer; opacity: .6; transition: .15s; }
        .btn-del-session:hover { opacity: 1; }
        .btn-add-session { border: 2px dashed #cbd5e1; background: transparent; border-radius: 10px; padding: 10px 20px; color: #64748b; font-size: .83rem; font-weight: 600; cursor: pointer; width: 100%; transition: .2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-add-session:hover { border-color: var(--primary, #0d6efd); color: var(--primary, #0d6efd); background: var(--primary-light, #e8f0fe); }
        .stepper { display: flex; align-items: center; border: 1.5px solid #d1d9e0; border-radius: 10px; overflow: hidden; width: fit-content; }
        .stepper button { background: #f8fafc; border: none; padding: 8px 13px; font-size: 1rem; color: #475569; cursor: pointer; transition: .15s; line-height: 1; }
        .stepper button:hover { background: #e2e8f0; }
        .stepper input { border: none; width: 56px; text-align: center; font-size: .95rem; font-weight: 700; color: #1e293b; outline: none; background: #fff; }
        .stepper .unit { padding: 8px 12px; background: #f1f5f9; font-size: .78rem; font-weight: 600; color: #64748b; border-left: 1.5px solid #d1d9e0; }
        .day-row { border: 1.5px solid #e8ecf0; border-radius: 12px; margin-bottom: 12px; overflow: hidden; }
        .day-hd { background: #f8fafc; padding: 12px 16px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .day-num-badge { background: var(--primary, #0d6efd); color: #fff; border-radius: 6px; padding: 2px 9px; font-size: .72rem; font-weight: 700; }
        .day-name { font-weight: 700; font-size: .88rem; color: #1e293b; min-width: 88px; }
        .woff-label { font-size: .8rem; color: #64748b; display: flex; align-items: center; gap: 6px; }
        .badge-count { background: #e2e8f0; color: #475569; border-radius: 20px; padding: 2px 9px; font-size: .72rem; font-weight: 700; }
        .badge-count.has { background: #dcfce7; color: #166534; }
        .btn-ov { background: #fd7e14; color: #fff; border: none; border-radius: 7px; padding: 4px 12px; font-size: .75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .btn-ov:hover { opacity: .85; }
        .slots-wrap { padding: 12px 16px; display: flex; flex-wrap: wrap; gap: 7px; }
        .slot-chip { display: flex; align-items: center; gap: 5px; background: #f0f4ff; border: 1.5px solid #c7d9fd; border-radius: 7px; padding: 4px 9px; font-size: .79rem; font-weight: 600; color: #1e4cad; cursor: pointer; transition: .15s; user-select: none; }
        .slot-chip:hover { background: #dce8ff; }
        .slot-chip.reserved { background: #fef3c7; border-color: #fbbf24; color: #92400e; }
        .slot-chip.dayoff { background: #f1f5f9; border-color: #cbd5e1; color: #94a3b8; text-decoration: line-through; pointer-events: none; }
        .slot-chip .xbtn { color: #ef4444; border: none; background: none; cursor: pointer; padding: 0 1px; font-size: .75rem; line-height: 1; }
        .cal-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .cal-nav button { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 7px; padding: 5px 11px; font-size: .82rem; cursor: pointer; }
        .cal-nav button:hover { background: var(--primary, #0d6efd); color: #fff; border-color: var(--primary, #0d6efd); }
        .cal-title { font-size: 1rem; font-weight: 700; color: #1e293b; }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
        .cal-dh { text-align: center; padding: 7px 2px; font-size: .72rem; font-weight: 700; color: #64748b; background: #f8fafc; border-radius: 5px; }
        .cal-d { min-height: 64px; border: 1px solid #e8ecf0; border-radius: 8px; padding: 5px 7px; cursor: pointer; transition: .15s; background: #fff; }
        .cal-d:hover { border-color: var(--primary, #0d6efd); background: var(--primary-light, #e8f0fe); }
        .cal-d.today { border-color: var(--primary, #0d6efd); background: #eff6ff; }
        .cal-d.holiday { background: #fef2f2; border-color: #fca5a5; }
        .cal-d.np { background: #fff7ed; border-color: #fdba74; }
        .cal-d.other { opacity: .35; pointer-events: none; }
        .cal-d .dn { font-size: .79rem; font-weight: 700; color: #1e293b; }
        .cal-d.today .dn { color: var(--primary, #0d6efd); }
        .dtag { font-size: .62rem; padding: 1px 5px; border-radius: 3px; margin-top: 3px; display: inline-block; font-weight: 700; }
        .dtag-h { background: #fee2e2; color: #991b1b; }
        .dtag-n { background: #ffedd5; color: #9a3412; }
        .btn-gen { background: linear-gradient(135deg, #0d6efd, #0950c5); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-size: .88rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; box-shadow: 0 4px 12px rgba(13,110,253,.28); transition: .2s; }
        .btn-gen:hover { opacity: .9; }
        .btn-sv { background: #198754; color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-size: .88rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; transition: .2s; }
        .btn-sv:hover { opacity: .9; }
        .hint { font-size: .77rem; color: #94a3b8; margin-top: 5px; }
        .hint i { color: #60a5fa; }
        .toast-container { position: fixed; bottom: 22px; right: 22px; z-index: 9999; }

        /* Spinner overlay for doctor switch */
        #loadingOverlay { display:none; position:fixed; inset:0; background:rgba(255,255,255,.55); z-index:8888; align-items:center; justify-content:center; }
        #loadingOverlay.show { display:flex; }
    </style>

    {{-- Loading overlay --}}
    <div id="loadingOverlay">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
    </div>

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h5><i class="bi bi-sliders2 me-2 text-primary"></i>Appointment Configuration</h5>
            <small>Doctor-wise slot &amp; timing configuration</small>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <select class="form-select form-select-sm" id="doctorSelect" style="width:260px;border-radius:9px;">
                @foreach ($doctors as $doc)
                    @php
                        $words    = array_filter(explode(' ', $doc->name));
                        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
                        $label    = $doc->name . ($doc->department_name ? ' — ' . $doc->department_name : '');
                    @endphp
                    <option value="{{ $doc->id }}"
                        data-initials="{{ $initials }}"
                        data-short="{{ \Illuminate\Support\Str::limit($doc->name, 20) }}"
                        {{ $doc->id == $doctor->id ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div class="doc-pill">
                @php
                    $words    = array_filter(explode(' ', $doctor->name));
                    $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
                @endphp
                <div class="av" id="docAv">{{ $initials }}</div>
                <span id="docSh">{{ \Illuminate\Support\Str::limit($doctor->name, 18) }}</span>
            </div>
        </div>
    </div>

    {{-- ① CONSULTATION SESSIONS --}}
    <div class="ccard">
        <div class="ccard-head">
            <div class="ico" style="background:#e0f2fe;"><i class="bi bi-clock-fill text-info"></i></div>
            <div>
                <h6>Consultation Sessions</h6>
                <small>Add multiple sessions — Morning, Afternoon, Evening or Night. Session type updates automatically based on the time range you set.</small>
            </div>
        </div>
        <div class="ccard-body">
            <div id="sessionsContainer"></div>
            <button class="btn-add-session" onclick="addSession()">
                <i class="bi bi-plus-circle"></i> Add Another Session
            </button>
        </div>
    </div>

    {{-- ② SLOT & BOOKING SETTINGS --}}
    <div class="ccard">
        <div class="ccard-head">
            <div class="ico" style="background:#f0fdf4;"><i class="bi bi-gear-fill text-success"></i></div>
            <div>
                <h6>Slot &amp; Booking Settings</h6>
                <small>Duration per slot and how far in advance patients can book</small>
            </div>
        </div>
        <div class="ccard-body">
            <div class="row g-4 align-items-start">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary" style="font-size:.78rem;">SLOT DURATION</label>
                    <div class="stepper">
                        <button type="button" onclick="chg('slotDur',-5)">−</button>
                        <input type="number" id="slotDur" value="15" min="5" max="120" step="5" />
                        <span class="unit">Min</span>
                    </div>
                    <p class="hint mt-2"><i class="bi bi-info-circle me-1"></i>Time allocated per patient per slot.</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary" style="font-size:.78rem;">ADVANCE BOOKING UPTO</label>
                    <div class="stepper">
                        <button type="button" onclick="chg('bookUpto',-1)">−</button>
                        <input type="number" id="bookUpto" value="120" min="0" max="365" />
                        <span class="unit">Days</span>
                    </div>
                    <p class="hint mt-2"><i class="bi bi-info-circle me-1"></i>0 = disabled, 1 = today only, 2 = today + tomorrow…</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary d-block" style="font-size:.78rem;">TIMESLOT VISIBILITY</label>
                    <div class="p-3 mt-1" style="background:#f8fafc;border-radius:10px;border:1px solid #e8ecf0;">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="privateSlots" style="width:40px;height:20px;">
                            <label class="form-check-label fw-semibold" for="privateSlots" style="font-size:.85rem;">Make Slots Private</label>
                        </div>
                        <div class="hint" style="margin:0;"><i class="bi bi-eye-slash me-1"></i>Public won't see slots; only Front Desk can view &amp; book.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Generate button --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <button class="btn-gen" onclick="generateSlots()"><i class="bi bi-magic"></i> Generate Timeslots</button>
        <span class="text-muted" style="font-size:.8rem;">Generates slots for all 7 days using sessions above</span>
    </div>

    {{-- ③ TIMESLOTS PER DAY --}}
    <div class="ccard">
        <div class="ccard-head">
            <div class="ico" style="background:#ede9fe;"><i class="bi bi-calendar3-week-fill" style="color:#7c3aed;"></i></div>
            <div>
                <h6>Your Timeslots</h6>
                <small>✓ Check a slot = Reserved (hidden from public) &nbsp;|&nbsp; ✕ = Delete slot &nbsp;|&nbsp; Override = custom slots for that day</small>
            </div>
        </div>
        <div class="ccard-body" id="slotsContainer">
            <div class="text-center text-muted py-4" style="font-size:.85rem;" id="slotsPlaceholder">
                <i class="bi bi-arrow-up-circle me-2"></i>Click "Generate Timeslots" above to auto-fill all days.
            </div>
        </div>
    </div>

    {{-- ④ NON-PRACTICE CALENDAR --}}
    <div class="ccard">
        <div class="ccard-head">
            <div class="ico" style="background:#fce7f3;"><i class="bi bi-calendar-x-fill" style="color:#db2777;"></i></div>
            <div>
                <h6>Non-Practice Days &amp; Holidays</h6>
                <small>Click a date → Holiday → Non-Practice → Clear</small>
            </div>
        </div>
        <div class="ccard-body">
            <div class="d-flex gap-3 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2"><span style="width:12px;height:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:3px;display:inline-block;"></span><span style="font-size:.76rem;">Holiday</span></div>
                <div class="d-flex align-items-center gap-2"><span style="width:12px;height:12px;background:#fff7ed;border:1px solid #fdba74;border-radius:3px;display:inline-block;"></span><span style="font-size:.76rem;">Non-Practice</span></div>
                <div class="d-flex align-items-center gap-2"><span style="width:12px;height:12px;background:#eff6ff;border:1px solid #7dd3fc;border-radius:3px;display:inline-block;"></span><span style="font-size:.76rem;">Today</span></div>
            </div>
            <div class="cal-hd">
                <div class="cal-nav d-flex gap-2">
                    <button type="button" onclick="chgMonth(-1)"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" onclick="goToday()">Today</button>
                    <button type="button" onclick="chgMonth(1)"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="cal-title" id="calTitle"></div>
                <div></div>
            </div>
            <div class="cal-grid" id="calGrid"></div>
        </div>
    </div>

    {{-- SAVE BAR --}}
    <div class="d-flex justify-content-end gap-3 pb-4">
        <button type="button" class="btn btn-light border px-4" style="border-radius:10px;" onclick="resetConfig()">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
        </button>
        <button class="btn-sv" onclick="saveConfig()">
            <i class="bi bi-check2-circle"></i> Save Configuration
        </button>
    </div>

    {{-- OVERRIDE MODAL --}}
    <div class="modal fade" id="ovModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:14px;border:none;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Override — <span id="ovDayName"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.82rem;">Add or remove custom slots for this specific day only.</p>
                    <div class="d-flex gap-2 mb-3">
                        <input type="time" id="ovTime" class="form-control" style="max-width:130px;border-radius:9px;" value="09:00" />
                        <button type="button" class="btn btn-primary btn-sm" style="border-radius:8px;" onclick="addOvSlot()">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                    <div id="ovList" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-sm btn-success" style="border-radius:8px;" data-bs-dismiss="modal" onclick="renderSlots()">
                        <i class="bi bi-check-lg me-1"></i>Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div class="toast-container">
        <div id="mainToast" class="toast align-items-center border-0 text-bg-success" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg">Saved!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// ROUTES
// ════════════════════════════════════════════════════════════
const ROUTES = {
    load:           '{{ route('admin.appointment-config.load', ':id') }}'.replace(':id', ''),
    save:           '{{ route('admin.appointment-config.save') }}',
    slotOverride:   '{{ route('admin.appointment-config.slot.override') }}',
    weeklyOff:      '{{ route('admin.appointment-config.weekly-off') }}',
    nonPracticeDay: '{{ route('admin.appointment-config.non-practice-day') }}',
};
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let currentDoctorId = {{ $doctor->id }};

// ════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════
function t2m(t) {
    if (!t) return 0;
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
}
function m2t(m) {
    m = Math.max(0, Math.min(1440, +m));
    return String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
}
function chg(id, d) {
    const el = document.getElementById(id);
    el.value = Math.max(+el.min || 0, Math.min(+el.max || 9999, +el.value + d));
}
function showToast(msg, type = 'success') {
    const el = document.getElementById('mainToast');
    el.className = `toast align-items-center border-0 text-bg-${type}`;
    document.getElementById('toastMsg').textContent = msg;
    new bootstrap.Toast(el, { delay: 2600 }).show();
}
function showLoader(on) {
    document.getElementById('loadingOverlay').classList.toggle('show', on);
}

// ════════════════════════════════════════════════════════════
// SESSION TYPES
// ════════════════════════════════════════════════════════════
const SESSION_TYPES = [
    { key: 'morning',   label: 'Morning',   icon: 'bi-brightness-high-fill', cls: 'morning',   lbl: 'label-morning',   color: '#d97706', fill: '#fcd34d', start: 480,  end: 720  },
    { key: 'afternoon', label: 'Afternoon', icon: 'bi-sun-fill',             cls: 'afternoon', lbl: 'label-afternoon', color: '#2563eb', fill: '#93c5fd', start: 780,  end: 960  },
    { key: 'evening',   label: 'Evening',   icon: 'bi-sunset-fill',          cls: 'evening',   lbl: 'label-evening',   color: '#7c3aed', fill: '#c4b5fd', start: 1020, end: 1200 },
    { key: 'night',     label: 'Night',     icon: 'bi-moon-stars-fill',      cls: 'night',     lbl: 'label-night',     color: '#db2777', fill: '#f9a8d4', start: 1200, end: 1380 },
];

function detectSessionType(startMinutes) {
    if (startMinutes < 720)  return SESSION_TYPES[0]; // morning  00:00–11:59
    if (startMinutes < 1020) return SESSION_TYPES[1]; // afternoon 12:00–16:59
    if (startMinutes < 1260) return SESSION_TYPES[2]; // evening  17:00–20:59
    return SESSION_TYPES[3];                           // night    21:00–23:59
}

// ════════════════════════════════════════════════════════════
// SESSIONS
// ════════════════════════════════════════════════════════════
let sessions       = [];
let sessionCounter = 0;

/**
 * Add a new session.
 * @param {object|null} preset  – full SESSION_TYPE object (optional)
 * @param {object|null} data    – existing data when loading from API
 */
function addSession(preset = null, data = null) {
    const type = preset || SESSION_TYPES[sessions.length % SESSION_TYPES.length];
    const id   = 'sess_' + (++sessionCounter);
    sessions.push({
        id,
        type,
        start:  data ? data.start_minutes  : type.start,
        end:    data ? data.end_minutes    : type.end,
        breakOn: data ? !!data.break_enabled : false,
        breakS:  data ? (data.break_start_minutes || 0) : 0,
        breakE:  data ? (data.break_end_minutes   || 0) : 0,
    });
    renderSessions();
}

function removeSession(id) {
    sessions = sessions.filter(s => s.id !== id);
    renderSessions();
}

function renderSessions() {
    const c = document.getElementById('sessionsContainer');
    c.innerHTML = '';

    sessions.forEach(s => {
        const sPct   = (s.start / 1440 * 100).toFixed(3) + '%';
        const ePct   = (100 - s.end / 1440 * 100).toFixed(3) + '%';
        const durMin = s.end - s.start;
        const durTxt = durMin > 0
            ? (Math.floor(durMin / 60) > 0 ? Math.floor(durMin / 60) + 'h ' : '') + (durMin % 60) + 'm'
            : '—';

        // ── Break fill needs to be relative to the SESSION range, not 0-1440 ──
        const brkRange = s.end - s.start;
        const bsPct    = brkRange > 0 ? ((s.breakS - s.start) / brkRange * 100).toFixed(3) + '%' : '0%';
        const bePct    = brkRange > 0 ? (100 - (s.breakE - s.start) / brkRange * 100).toFixed(3) + '%' : '100%';

        const div = document.createElement('div');
        div.className = `session-block ${s.type.cls}`;
        div.id = s.id;
        div.innerHTML = `
<button class="btn-del-session" onclick="removeSession('${s.id}')" title="Remove"><i class="bi bi-x-circle-fill"></i></button>
<div class="session-label ${s.type.lbl}" id="lbl_${s.id}">
    <i class="bi ${s.type.icon}" id="lblico_${s.id}"></i>
    <span id="lbltxt_${s.id}">${s.type.label}</span>
</div>
<div class="row g-3 align-items-center">
    <div class="col-md-5">
        <div class="time-pair">
            <div class="time-box">
                <label>From</label>
                <input type="time" id="from_${s.id}" value="${m2t(s.start)}"
                    oninput="syncFromTime('${s.id}','start',this.value)"/>
            </div>
            <i class="bi bi-arrow-right" style="color:#94a3b8;"></i>
            <div class="time-box">
                <label>To</label>
                <input type="time" id="to_${s.id}" value="${m2t(s.end)}"
                    oninput="syncFromTime('${s.id}','end',this.value)"/>
            </div>
            <span class="badge bg-light text-secondary border ms-1" style="font-size:.72rem;" id="dur_${s.id}">${durTxt}</span>
        </div>
    </div>
    <div class="col-md-7">
        <div class="dual-range-wrap" id="wrap_${s.id}">
            <div class="track"></div>
            <div class="range-fill" id="fill_${s.id}"
                style="left:${sPct};right:${ePct};background:${s.type.fill};"></div>
            <input type="range" min="0" max="1440" step="15" value="${s.start}"
                style="color:${s.type.color};" id="rstart_${s.id}"
                oninput="slideSession('${s.id}','start',+this.value)"/>
            <input type="range" min="0" max="1440" step="15" value="${s.end}"
                style="color:${s.type.color};" id="rend_${s.id}"
                oninput="slideSession('${s.id}','end',+this.value)"/>
        </div>
        <div class="range-time-labels">
            <span>12AM</span><span>6AM</span><span>12PM</span><span>6PM</span><span>11PM</span>
        </div>
    </div>
</div>

<!-- Break section -->
<div class="break-row">
    <div class="break-toggle">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="brk_${s.id}"
                ${s.breakOn ? 'checked' : ''} style="width:34px;height:17px;"
                onchange="toggleBreak('${s.id}',this.checked)"/>
            <label class="form-check-label" for="brk_${s.id}"
                style="font-size:.8rem;font-weight:600;color:#475569;">Include Break</label>
        </div>
    </div>
    <div id="brkDetail_${s.id}" style="${s.breakOn ? '' : 'display:none;'}">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="time-pair">
                    <div class="time-box">
                        <label>Break From</label>
                        <input type="time" id="bfrom_${s.id}" value="${m2t(s.breakS)}"
                            oninput="syncBreakTime('${s.id}','S',this.value)"/>
                    </div>
                    <i class="bi bi-arrow-right" style="color:#94a3b8;"></i>
                    <div class="time-box">
                        <label>Break To</label>
                        <input type="time" id="bto_${s.id}" value="${m2t(s.breakE)}"
                            oninput="syncBreakTime('${s.id}','E',this.value)"/>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="dual-range-wrap" id="bwrap_${s.id}">
                    <div class="track"></div>
                    <div class="range-fill" id="bfill_${s.id}"
                        style="left:${bsPct};right:${bePct};background:#fca5a5;"></div>
                    <input type="range" min="${s.start}" max="${s.end}" step="15"
                        value="${s.breakS}" style="color:#ef4444;" id="brstart_${s.id}"
                        oninput="slideBreak('${s.id}','S',+this.value)"/>
                    <input type="range" min="${s.start}" max="${s.end}" step="15"
                        value="${s.breakE}" style="color:#ef4444;" id="brend_${s.id}"
                        oninput="slideBreak('${s.id}','E',+this.value)"/>
                </div>
                <div class="range-time-labels" id="brkTicks_${s.id}">
                    <span>${m2t(s.start)}</span>
                    <span>${m2t(Math.round((s.start + s.end) / 2))}</span>
                    <span>${m2t(s.end)}</span>
                </div>
            </div>
        </div>
    </div>
</div>`;
        c.appendChild(div);
    });
}

// ── Session slider / time-input sync ─────────────────────────────────
function slideSession(id, which, val) {
    const s = sessions.find(x => x.id === id);
    if (!s) return;
    if (which === 'start') s.start = Math.min(val, s.end - 15);
    else                   s.end   = Math.max(val, s.start + 15);

    // Clamp break inside new session range
    if (s.breakOn) {
        s.breakS = Math.max(s.start, Math.min(s.breakS, s.end - 15));
        s.breakE = Math.max(s.breakS + 15, Math.min(s.breakE, s.end));
    }

    // Sync time inputs
    document.getElementById('from_' + id).value = m2t(s.start);
    document.getElementById('to_'   + id).value = m2t(s.end);

    // Sync duration badge
    _refreshDur(id, s);
    // Sync range thumbs (in case of clamping)
    document.getElementById('rstart_' + id).value = s.start;
    document.getElementById('rend_'   + id).value = s.end;

    _refreshSessionFill(id, s);
    _refreshBreakRangeMinMax(id, s);
    _refreshBreakFill(id, s);
    autoUpdateLabel(id);
}

function syncFromTime(id, which, val) {
    const s = sessions.find(x => x.id === id);
    if (!s) return;
    const m = t2m(val);
    if (which === 'start') { s.start = m; document.getElementById('rstart_' + id).value = m; }
    else                   { s.end   = m; document.getElementById('rend_'   + id).value = m; }

    // Clamp break
    if (s.breakOn) {
        s.breakS = Math.max(s.start, Math.min(s.breakS, s.end - 15));
        s.breakE = Math.max(s.breakS + 15, Math.min(s.breakE, s.end));
        _refreshBreakInputs(id, s);
    }

    _refreshDur(id, s);
    _refreshSessionFill(id, s);
    _refreshBreakRangeMinMax(id, s);
    _refreshBreakFill(id, s);
    autoUpdateLabel(id);
}

// ── Break slider / time-input sync ────────────────────────────────────
function slideBreak(id, which, val) {
    const s = sessions.find(x => x.id === id);
    if (!s) return;
    // Clamp within session
    val = Math.max(s.start, Math.min(val, s.end));
    if (which === 'S') s.breakS = Math.min(val, s.breakE - 15);
    else               s.breakE = Math.max(val, s.breakS + 15);

    // Sync thumbs (prevent overshoot)
    document.getElementById('brstart_' + id).value = s.breakS;
    document.getElementById('brend_'   + id).value = s.breakE;

    _refreshBreakInputs(id, s);
    _refreshBreakFill(id, s);
}

function syncBreakTime(id, which, val) {
    const s = sessions.find(x => x.id === id);
    if (!s) return;
    const m = t2m(val);
    if (which === 'S') { s.breakS = Math.max(s.start, Math.min(m, s.end - 15)); }
    else               { s.breakE = Math.max(s.start + 15, Math.min(m, s.end)); }

    // Sync thumbs
    document.getElementById('brstart_' + id).value = s.breakS;
    document.getElementById('brend_'   + id).value = s.breakE;

    _refreshBreakInputs(id, s);
    _refreshBreakFill(id, s);
}

function toggleBreak(id, on) {
    const s = sessions.find(x => x.id === id);
    if (!s) return;
    s.breakOn = on;
    if (on && s.breakS === 0 && s.breakE === 0) {
        // Default break to middle 15 mins of session
        const mid = Math.round((s.start + s.end) / 2 / 15) * 15;
        s.breakS  = Math.max(s.start, mid - 15);
        s.breakE  = Math.min(s.end,   mid + 15);
    }
    const detail = document.getElementById('brkDetail_' + id);
    if (detail) detail.style.display = on ? '' : 'none';
    if (on) {
        // Update range min/max and values
        _refreshBreakRangeMinMax(id, s);
        _refreshBreakInputs(id, s);
        _refreshBreakFill(id, s);
    }
}

// ── Private refresh helpers ───────────────────────────────────────────
function _refreshDur(id, s) {
    const dur  = s.end - s.start;
    const dtxt = dur > 0 ? (Math.floor(dur/60) > 0 ? Math.floor(dur/60)+'h ' : '') + (dur%60)+'m' : '—';
    const el   = document.getElementById('dur_' + id);
    if (el) el.textContent = dtxt;
}

function _refreshSessionFill(id, s) {
    const fill = document.getElementById('fill_' + id);
    if (!fill) return;
    fill.style.left  = (s.start / 1440 * 100).toFixed(3) + '%';
    fill.style.right = (100 - s.end / 1440 * 100).toFixed(3) + '%';
}

/**
 * FIX: Break fill is relative to the SESSION range (not 0-1440).
 * left  = (breakS - sessStart) / sessRange * 100
 * right = (sessEnd - breakE)   / sessRange * 100
 */
function _refreshBreakFill(id, s) {
    const fill = document.getElementById('bfill_' + id);
    if (!fill) return;
    const range = s.end - s.start;
    if (range <= 0) return;
    const left  = ((s.breakS - s.start) / range * 100).toFixed(3);
    const right = ((s.end - s.breakE)   / range * 100).toFixed(3);
    fill.style.left  = Math.max(0, +left)  + '%';
    fill.style.right = Math.max(0, +right) + '%';
}

function _refreshBreakRangeMinMax(id, s) {
    const rs = document.getElementById('brstart_' + id);
    const re = document.getElementById('brend_'   + id);
    if (rs) { rs.min = s.start; rs.max = s.end; }
    if (re) { re.min = s.start; re.max = s.end; }
    // Also update tick labels
    const ticks = document.getElementById('brkTicks_' + id);
    if (ticks) {
        const spans = ticks.querySelectorAll('span');
        if (spans[0]) spans[0].textContent = m2t(s.start);
        if (spans[1]) spans[1].textContent = m2t(Math.round((s.start + s.end) / 2));
        if (spans[2]) spans[2].textContent = m2t(s.end);
    }
}

function _refreshBreakInputs(id, s) {
    const bf = document.getElementById('bfrom_' + id);
    const bt = document.getElementById('bto_'   + id);
    if (bf) bf.value = m2t(s.breakS);
    if (bt) bt.value = m2t(s.breakE);
    document.getElementById('brstart_' + id).value = s.breakS;
    document.getElementById('brend_'   + id).value = s.breakE;
}

/**
 * Update session label + block colour WITHOUT full re-render (preserves focus).
 */
function autoUpdateLabel(id) {
    const s    = sessions.find(x => x.id === id);
    if (!s) return;
    const auto = detectSessionType(s.start);
    if (auto.key === s.type.key) return;
    s.type = auto;

    const block = document.getElementById(id);
    if (block) {
        block.className = `session-block ${auto.cls}`;
        const fill = document.getElementById('fill_' + id);
        if (fill) fill.style.background = auto.fill;
        const rs = document.getElementById('rstart_' + id);
        const re = document.getElementById('rend_'   + id);
        if (rs) rs.style.color = auto.color;
        if (re) re.style.color = auto.color;
    }

    const lbl    = document.getElementById('lbl_'    + id);
    const lblico = document.getElementById('lblico_' + id);
    const lbltxt = document.getElementById('lbltxt_' + id);
    if (lbl) {
        lbl.className = `session-label ${auto.lbl}`;
        if (lblico) lblico.className = `bi ${auto.icon}`;
        if (lbltxt) lbltxt.textContent = auto.label;
    }
}

// ════════════════════════════════════════════════════════════
// DOCTOR SELECTOR
// ════════════════════════════════════════════════════════════
document.getElementById('doctorSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('docAv').textContent = opt.dataset.initials || '??';
    document.getElementById('docSh').textContent = opt.dataset.short    || opt.text;
    currentDoctorId = +this.value;
    loadDoctorConfig(currentDoctorId);
});

// ════════════════════════════════════════════════════════════
// TIMESLOTS
// ════════════════════════════════════════════════════════════
const DAYS    = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
let slotsData    = {};
let currentOvDay = null;

function generateSlots() {
    if (sessions.length === 0) { showToast('Add at least one consultation session first.', 'warning'); return; }
    const dur = Math.max(5, +document.getElementById('slotDur').value);
    slotsData = {};
    DAYS.forEach((day, idx) => {
        const slots = [];
        sessions.forEach(s => {
            for (let tt = s.start; tt < s.end; tt += dur) {
                if (s.breakOn && tt >= s.breakS && tt < s.breakE) continue;
                slots.push({ time: m2t(tt), reserved: false, session: s.type.key });
            }
        });
        const seen   = new Set();
        const unique = slots.filter(sl => { if (seen.has(sl.time)) return false; seen.add(sl.time); return true; });
        slotsData[idx] = { off: false, slots: unique };
    });
    renderSlots();
    showToast('Timeslots generated for all 7 days!', 'success');
}

function renderSlots() {
    const c = document.getElementById('slotsContainer');
    c.innerHTML = '';

    // FIX: If no slots at all, show placeholder
    const hasAnySlot = Object.values(slotsData).some(d => d.slots.length > 0);
    if (!hasAnySlot) {
        c.innerHTML = `<div class="text-center text-muted py-4" style="font-size:.85rem;" id="slotsPlaceholder">
            <i class="bi bi-arrow-up-circle me-2"></i>Click "Generate Timeslots" above to auto-fill all days.
        </div>`;
        return;
    }

    const sessionColors = { morning: '#fde68a', afternoon: '#bfdbfe', evening: '#ddd6fe', night: '#fbcfe8' };
    DAYS.forEach((day, idx) => {
        const data   = slotsData[idx] || { off: false, slots: [] };
        const active = data.slots.filter(s => !s.reserved).length;
        const row    = document.createElement('div');
        row.className = 'day-row';
        row.innerHTML = `
<div class="day-hd">
    <span class="day-num-badge">${idx + 1}</span>
    <span class="day-name">${day}</span>
    <label class="woff-label mb-0">
        <input type="checkbox" class="form-check-input" id="off_${idx}"
            ${data.off ? 'checked' : ''} onchange="toggleOff(${idx})" style="width:15px;height:15px;"/>
        Weekly Off
    </label>
    <span class="badge-count ${active > 0 && !data.off ? 'has' : ''} ms-auto" id="cnt_${idx}">
        <i class="bi bi-grid-3x3-gap me-1"></i>${data.slots.length} Slots
    </span>
    <button class="btn-ov" onclick="openOv(${idx},'${day}')"><i class="bi bi-pencil-fill"></i> Override</button>
</div>
<div class="slots-wrap" id="grid_${idx}">
    ${data.slots.length === 0
        ? `<span class="text-muted" style="font-size:.8rem;"><i class="bi bi-info-circle me-1"></i>${data.off ? 'Weekly off' : 'No slots generated'}</span>`
        : data.slots.map((sl, si) => `
            <div class="slot-chip ${sl.reserved ? 'reserved' : ''} ${data.off ? 'dayoff' : ''}"
                style="border-color:${sessionColors[sl.session] || '#c7d9fd'};"
                onclick="toggleRes(${idx},${si})"
                title="${sl.reserved ? 'Reserved (hidden)' : 'Click to reserve'}">
                <input type="checkbox" ${sl.reserved ? 'checked' : ''}
                    onclick="event.stopPropagation();toggleRes(${idx},${si})"
                    style="width:12px;height:12px;"/>
                <i class="bi bi-clock" style="font-size:.7rem;"></i>${sl.time}
                <button class="xbtn" onclick="event.stopPropagation();delSlot(${idx},${si})" title="Delete">✕</button>
            </div>`).join('')}
</div>`;
        c.appendChild(row);
    });
}

function toggleOff(idx) {
    if (!slotsData[idx]) slotsData[idx] = { off: false, slots: [] };
    slotsData[idx].off = document.getElementById('off_' + idx).checked;
    renderSlots();
}
function toggleRes(idx, si) { slotsData[idx].slots[si].reserved = !slotsData[idx].slots[si].reserved; renderSlots(); }
function delSlot(idx, si)   { slotsData[idx].slots.splice(si, 1); renderSlots(); }

function openOv(idx, name) {
    currentOvDay = idx;
    document.getElementById('ovDayName').textContent = name;
    refreshOvList();
    new bootstrap.Modal(document.getElementById('ovModal')).show();
}
function refreshOvList() {
    const list = document.getElementById('ovList');
    if (!slotsData[currentOvDay]) { list.innerHTML = ''; return; }
    list.innerHTML = slotsData[currentOvDay].slots.map((s, i) => `
        <span class="slot-chip">
            <i class="bi bi-clock" style="font-size:.7rem;"></i>${s.time}
            <button class="xbtn" onclick="delSlot(${currentOvDay},${i});refreshOvList()">✕</button>
        </span>`).join('');
}
function addOvSlot() {
    const t = document.getElementById('ovTime').value;
    if (!t) return;
    if (!slotsData[currentOvDay]) slotsData[currentOvDay] = { off: false, slots: [] };
    slotsData[currentOvDay].slots.push({ time: t, reserved: false, session: 'morning' });
    slotsData[currentOvDay].slots.sort((a, b) => a.time.localeCompare(b.time));
    refreshOvList();
    showToast(`Slot ${t} added`, 'info');
}

// ════════════════════════════════════════════════════════════
// CALENDAR
// ════════════════════════════════════════════════════════════
let calY, calM;
let marked = {};
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function renderCal() {
    document.getElementById('calTitle').textContent = `${MONTHS[calM]} ${calY}`;
    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';
    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
        const h = document.createElement('div'); h.className = 'cal-dh'; h.textContent = d; grid.appendChild(h);
    });
    const first = new Date(calY, calM, 1).getDay();
    const total = new Date(calY, calM + 1, 0).getDate();
    const today = new Date();
    for (let i = 0; i < first; i++) { const b = document.createElement('div'); b.className = 'cal-d other'; grid.appendChild(b); }
    for (let d = 1; d <= total; d++) {
        const key     = `${calY}-${String(calM+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isToday = d === today.getDate() && calM === today.getMonth() && calY === today.getFullYear();
        const mk      = marked[key];
        const cell    = document.createElement('div');
        cell.className = 'cal-d' + (isToday ? ' today' : '') + (mk === 'holiday' ? ' holiday' : mk === 'non_practice' ? ' np' : '');
        cell.innerHTML = `<div class="dn">${d}</div>${mk === 'holiday' ? '<span class="dtag dtag-h">Holiday</span>' : mk === 'non_practice' ? '<span class="dtag dtag-n">Non-Practice</span>' : ''}`;
        cell.onclick   = () => toggleDate(key);
        grid.appendChild(cell);
    }
}
function toggleDate(key) {
    const cur = marked[key];
    if (!cur)                    marked[key] = 'holiday';
    else if (cur === 'holiday')  marked[key] = 'non_practice';
    else                         delete marked[key];
    renderCal();
    showToast(marked[key] ? `Marked as ${marked[key].replace('_', ' ')}` : 'Date cleared', 'info');
}
function chgMonth(d) { calM += d; if (calM > 11) { calM = 0; calY++; } if (calM < 0) { calM = 11; calY--; } renderCal(); }
function goToday()   { const t = new Date(); calY = t.getFullYear(); calM = t.getMonth(); renderCal(); }

// ════════════════════════════════════════════════════════════
// LOAD DOCTOR CONFIG (AJAX)
// ════════════════════════════════════════════════════════════
async function loadDoctorConfig(doctorId) {
    showLoader(true);

    // FIX: Clear slots immediately on doctor change — don't show stale data
    slotsData    = {};
    sessions     = [];
    sessionCounter = 0;
    document.getElementById('sessionsContainer').innerHTML = '';
    document.getElementById('slotsContainer').innerHTML = `
        <div class="text-center text-muted py-4" style="font-size:.85rem;">
            <i class="bi bi-arrow-up-circle me-2"></i>Click "Generate Timeslots" above to auto-fill all days.
        </div>`;

    try {
        const res  = await fetch(ROUTES.load + doctorId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // ── Sessions ──────────────────────────────────────
        if (data.sessions && data.sessions.length > 0) {
            data.sessions.forEach(s => {
                const type = SESSION_TYPES.find(t => t.key === s.session_type)
                          || detectSessionType(s.start_minutes);
                addSession(type, s);
            });
        } else {
            // FIX: No saved sessions → add one default Morning session
            addSession(SESSION_TYPES[0]);
        }

        // ── Settings ──────────────────────────────────────
        if (data.settings) {
            document.getElementById('slotDur').value        = data.settings.slot_duration        || 15;
            document.getElementById('bookUpto').value       = data.settings.advance_booking_days  || 120;
            document.getElementById('privateSlots').checked = !!data.settings.slots_private;
        }

        // ── Slots ─────────────────────────────────────────
        // FIX: Only render if there are actual saved slots
        slotsData = {};
        let hasSlots = false;
        DAYS.forEach((_, idx) => {
            const daySlots = data.slots?.[idx] || [];
            const isOff    = data.weekly_off?.[idx] ?? false;
            slotsData[idx] = {
                off:   isOff,
                slots: daySlots.map(sl => ({
                    time:     sl.slot_time.substring(0, 5),
                    reserved: sl.is_reserved,
                    session:  sl.session_type || 'morning',
                }))
            };
            if (daySlots.length > 0) hasSlots = true;
        });

        if (hasSlots) {
            renderSlots();
        }
        // If no slots, the placeholder already set above stays visible

        // ── Calendar ──────────────────────────────────────
        marked = {};
        Object.entries(data.non_practice_days || {}).forEach(([date, type]) => { marked[date] = type; });
        renderCal();

        showToast(`Loaded: ${data.doctor.name}`, 'info');

    } catch (e) {
        showToast('Failed to load doctor config', 'danger');
        console.error(e);
        // Still add default session on failure
        if (sessions.length === 0) addSession(SESSION_TYPES[0]);
    } finally {
        showLoader(false);
    }
}

// ════════════════════════════════════════════════════════════
// SAVE CONFIG
// ════════════════════════════════════════════════════════════
async function saveConfig() {
    const sessionsPayload = sessions.map(s => ({
        session_type:  s.type.key,
        start_time:    m2t(s.start),
        end_time:      m2t(s.end),
        break_enabled: s.breakOn,
        break_start:   s.breakOn ? m2t(s.breakS) : null,
        break_end:     s.breakOn ? m2t(s.breakE) : null,
    }));

    const slotsPayload = {};
    DAYS.forEach((_, idx) => {
        const d = slotsData[idx] || { off: false, slots: [] };
        slotsPayload[idx] = d.slots.map(sl => ({
            slot_time:     sl.time,
            session_type:  sl.session || 'morning',
            is_reserved:   sl.reserved,
            is_weekly_off: d.off,
        }));
    });

    const payload = {
        doctor_id:            currentDoctorId,
        slot_duration:        +document.getElementById('slotDur').value,
        advance_booking_days: +document.getElementById('bookUpto').value,
        slots_private:        document.getElementById('privateSlots').checked,
        sessions:             sessionsPayload,
        slots:                slotsPayload,
        non_practice_days:    marked,
    };

    try {
        const res  = await fetch(ROUTES.save, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Server error');
        showToast(data.message || 'Configuration saved!', 'success');
    } catch (e) {
        showToast(e.message || 'Failed to save configuration', 'danger');
        console.error(e);
    }
}

// ════════════════════════════════════════════════════════════
// RESET
// ════════════════════════════════════════════════════════════
function resetConfig() {
    if (confirm('Reset to last saved configuration?')) loadDoctorConfig(currentDoctorId);
}

// ════════════════════════════════════════════════════════════
// INIT
// ════════════════════════════════════════════════════════════
(function init() {
    const today = new Date();
    calY = today.getFullYear();
    calM = today.getMonth();
    loadDoctorConfig(currentDoctorId);
})();
</script>
@endpush