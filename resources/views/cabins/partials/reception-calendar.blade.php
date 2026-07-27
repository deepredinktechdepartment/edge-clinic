<div class="cabin-panel">
    <div class="panel-head">
        <div>
            <h5 class="mb-1">Cabin Availability Calendar</h5>
            <div class="text-muted">Choose a day to see shift-wise availability and every room's free or booked timings.</div>
        </div>
    </div>
    <div class="panel-body">
        <div class="cabin-calendar-layout">
            <div>
                <div class="cabin-calendar-controls">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="receptionCalendarPrevious"><i class="bi bi-chevron-left"></i></button>
                    <strong id="receptionCalendarTitle"></strong>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="receptionCalendarNext"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="cabin-month-weekdays"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                <div class="cabin-month-days" id="receptionCabinMonthDays"></div>
            </div>
            <div class="cabin-day-view">
                <div class="cabin-day-view-head">
                    <div><strong id="receptionSelectedDateTitle"></strong><div class="text-muted small">Read-only room availability for the selected day.</div></div>
                    <div class="cabin-calendar-legend"><span><i class="available"></i> Available</span><span><i class="booking"></i> Booked</span><span><i class="subscription"></i> Subscription</span><span><i class="unavailable"></i> Unavailable</span></div>
                </div>
                <div id="receptionCabinDaySchedule" class="cabin-day-schedule"><div class="empty-note">Loading cabin availability…</div></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const monthDays = document.getElementById('receptionCabinMonthDays');
    if (!monthDays) return;

    const title = document.getElementById('receptionCalendarTitle');
    const selectedTitle = document.getElementById('receptionSelectedDateTitle');
    const schedule = document.getElementById('receptionCabinDaySchedule');
    const availabilityUrl = @json(route('admin.cabins.dashboard.availability'));
    let bookingShifts = [];
    let selected = new Date(); selected.setHours(0, 0, 0, 0);
    let displayed = new Date(selected.getFullYear(), selected.getMonth(), 1);

    const iso = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const dateLabel = value => new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const time = value => new Date(`2000-01-01T${value}`).toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'});
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[char]));
    const overlaps = (startA, endA, startB, endB) => startA < endB && endA > startB;

    function shiftStatus(cabin, shift) {
        const events = cabin.events || [];
        const hasAvailableTime = (cabin.available || []).some(item => overlaps(item.start, item.end, shift.start, shift.end));
        const matchingEvents = events.filter(item => overlaps(item.start, item.end, shift.start, shift.end));
        const unavailable = matchingEvents.some(item => item.type === 'unavailable');
        const subscription = matchingEvents.some(item => item.type === 'subscription');
        const booking = matchingEvents.some(item => item.type === 'booking');
        const details = matchingEvents.filter(item => ['booking', 'subscription', 'unavailable'].includes(item.type)).map(item => `${time(item.start)} - ${time(item.end)}: ${item.label}`);
        if (unavailable) return {label: 'Unavailable', className: 'unavailable', details};
        if (subscription && hasAvailableTime) return {label: 'Partly allocated', className: 'monthly', details};
        if (booking && hasAvailableTime) return {label: 'Partly booked', className: 'booked', details};
        if (subscription) return {label: 'Monthly plan', className: 'monthly', details};
        if (booking) return {label: 'Booked', className: 'booked', details};
        return {label: hasAvailableTime ? 'Available' : 'Unavailable', className: hasAvailableTime ? 'available' : 'unavailable', details: []};
    }

    function renderSchedule(data) {
        selectedTitle.textContent = dateLabel(data.date);
        bookingShifts = data.booking_shifts || [];
        if (!data.cabins.length) { schedule.innerHTML = '<div class="empty-note">No cabin records are available yet.</div>'; return; }
        const headers = bookingShifts.map(shift => `<th><div class="fw-semibold">${escapeHtml(shift.label)}</div><div class="small fw-normal text-muted">${time(shift.start)} - ${time(shift.end)}</div></th>`).join('');
        const rows = data.cabins.map(cabin => `<tr><td><div class="fw-semibold text-dark">${escapeHtml(cabin.code)}</div><div class="small text-muted">${escapeHtml(cabin.name)}</div></td>${bookingShifts.map(shift => { const status = shiftStatus(cabin, shift); const details = status.details.length ? `<div class="cabin-shift-detail">${status.details.map(escapeHtml).join('<br>')}</div>` : ''; return `<td><span class="cabin-shift-status ${status.className}">${status.label}</span>${details}</td>`; }).join('')}</tr>`).join('');
        schedule.innerHTML = `<div class="small fw-semibold text-uppercase text-muted mb-2">Shift-wise availability for selected date</div><div class="table-responsive"><table class="table cabin-shift-table mb-0"><thead><tr><th>Cabin</th>${headers}</tr></thead><tbody>${rows}</tbody></table></div>`;
    }

    function renderMonth() {
        title.textContent = displayed.toLocaleDateString(undefined, {month: 'long', year: 'numeric'});
        monthDays.innerHTML = '';
        const offset = displayed.getDay();
        const days = new Date(displayed.getFullYear(), displayed.getMonth() + 1, 0).getDate();
        for (let i = 0; i < offset; i++) monthDays.insertAdjacentHTML('beforeend', '<span></span>');
        for (let day = 1; day <= days; day++) {
            const date = new Date(displayed.getFullYear(), displayed.getMonth(), day);
            const isToday = iso(date) === iso(new Date());
            const isSelected = iso(date) === iso(selected);
            monthDays.insertAdjacentHTML('beforeend', `<button type="button" class="cabin-day-button ${isToday ? 'is-today' : ''} ${isSelected ? 'is-selected' : ''}" data-date="${iso(date)}">${day}</button>`);
        }
    }

    async function loadDay() {
        schedule.innerHTML = '<div class="empty-note">Loading cabin availability…</div>';
        try {
            const response = await fetch(`${availabilityUrl}?date=${encodeURIComponent(iso(selected))}`, {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error();
            renderSchedule(await response.json());
        } catch {
            schedule.innerHTML = '<div class="empty-note">Could not load cabin availability. Please try again.</div>';
        }
    }

    monthDays.addEventListener('click', event => {
        const button = event.target.closest('[data-date]');
        if (!button) return;
        selected = new Date(`${button.dataset.date}T00:00:00`);
        renderMonth();
        loadDay();
    });
    document.getElementById('receptionCalendarPrevious').addEventListener('click', () => { displayed.setMonth(displayed.getMonth() - 1); renderMonth(); });
    document.getElementById('receptionCalendarNext').addEventListener('click', () => { displayed.setMonth(displayed.getMonth() + 1); renderMonth(); });
    renderMonth();
    loadDay();
})();
</script>
@endpush
