<style>
    .cabin-shell {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .cabin-pagebar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 18px;
        padding: 1rem 1.25rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }
    .cabin-pagebar h5 {
        color: #10314f;
        font-weight: 700;
    }
    .cabin-pagebar-main {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-width: 0;
    }
    .cabin-pagebar-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, #10314f 0%, #216aae 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex: 0 0 42px;
        box-shadow: 0 10px 20px rgba(33, 106, 174, 0.16);
    }
    .cabin-detail-hero {
        background: linear-gradient(135deg, #10314f 0%, #216aae 100%);
        border-radius: 20px;
        padding: 1.35rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #fff;
        box-shadow: 0 18px 40px rgba(16, 49, 79, 0.18);
    }
    .cabin-detail-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex: 0 0 52px;
    }
    .cabin-detail-eyebrow {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(255, 255, 255, 0.68);
        margin-bottom: .15rem;
    }
    .cabin-detail-title {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .cabin-detail-subtitle {
        color: rgba(255, 255, 255, 0.82);
        font-size: .92rem;
        margin-top: .2rem;
    }
    .cabin-detail-value {
        color: #12263a;
        font-size: .95rem;
        font-weight: 600;
    }
    .cabin-billing-footer {
        padding: .9rem 1rem;
        border-top: 1px solid #edf1f6;
        display: flex;
        justify-content: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: .9rem;
        color: #5f6b7a;
    }
    .cabin-billing-footer .grand {
        color: #12263a;
        font-size: .98rem;
        font-weight: 700;
    }
    .booking-invoice-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .9rem;
        margin-bottom: 1rem;
    }
    .booking-invoice-meta-box {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        background: #fbfdff;
        padding: .9rem 1rem;
    }
    .booking-invoice-table-wrap {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    .booking-invoice-table thead th {
        background: #10314f;
        color: #fff;
        font-size: .74rem;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        border-bottom: 0;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }
    .booking-invoice-table tbody td {
        padding-top: .95rem;
        padding-bottom: .95rem;
        border-color: #edf1f6;
    }
    .booking-invoice-totals {
        width: min(340px, 100%);
        margin-left: auto;
        margin-top: 1rem;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        background: #fbfdff;
        padding: 1rem 1.1rem;
    }
    .booking-invoice-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        color: #5f6b7a;
        font-size: .92rem;
        padding: .35rem 0;
    }
    .booking-invoice-total-row strong {
        color: #12263a;
    }
    .booking-invoice-total-grand {
        margin-top: .4rem;
        padding-top: .8rem;
        border-top: 1px solid #dbe4ef;
        font-size: 1rem;
        font-weight: 700;
        color: #10314f;
    }
    .cabin-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .cabin-timeline-row {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
    }
    .cabin-timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-top: .35rem;
        flex: 0 0 10px;
    }
    .cabin-timeline-title {
        color: #12263a;
        font-size: .92rem;
        font-weight: 600;
        line-height: 1.3;
    }
    .cabin-timeline-time {
        color: #6b7785;
        font-size: .82rem;
        margin-top: .15rem;
    }
    .cabin-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #3a86ce 0%, #7c3aed 100%);
        color: #fff;
        font-size: .95rem;
        font-weight: 700;
        flex: 0 0 42px;
    }
    .cabin-page-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .cabin-page-actions .btn {
        margin-top: 0 !important;
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 1rem !important;
        border-radius: 15px !important;
        line-height: 1.2;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .cabin-page-actions .btn-outline-secondary,
    .cabin-page-actions .btn-outline-primary,
    .cabin-panel .panel-head .btn-outline-secondary,
    .cabin-panel .panel-head .btn-outline-primary,
    .cabin-card .btn-outline-secondary,
    .cabin-card .btn-outline-primary,
    .cabin-shell .table .btn-outline-secondary,
    .cabin-shell .table .btn-outline-primary,
    .cabin-form-actions .btn-outline-secondary,
    .cabin-form-actions .btn-outline-primary {
        min-height: 40px !important;
        padding: .5rem 1rem !important;
        border-radius: 15px !important;
        line-height: 1.2;
    }
    .cabin-page-actions .btn-outline-secondary,
    .cabin-page-actions .btn-outline-primary {
        transition: all .18s ease;
    }
    .cabin-page-actions .btn-outline-secondary:hover,
    .cabin-page-actions .btn-outline-secondary:focus,
    .cabin-page-actions .btn-outline-primary:hover,
    .cabin-page-actions .btn-outline-primary:focus {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #2f8fcb 0%, #d34b96 100%);
        box-shadow: 0 10px 20px rgba(47, 143, 203, 0.18);
    }
    .cabin-shell .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        white-space: nowrap;
    }
    .cabin-form-actions .btn {
        margin-top: 0 !important;
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 1rem !important;
        border-radius: 15px !important;
        line-height: 1.2;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .cabin-form-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .cabin-shell .btn-brand:hover,
    .cabin-shell .btn-brand:focus,
    .cabin-shell .btn-brand:active,
    .cabin-shell .btn-brand.active,
    .cabin-shell .btn.show.btn-brand,
    .cabin-shell :not(.btn-check) + .btn-brand:active {
        color: #fff !important;
        border-color: transparent !important;
        background: linear-gradient(90deg, rgba(64, 144, 188, 1) 25%, rgba(205, 74, 144, 1) 100%) !important;
        box-shadow: none !important;
    }
    .cabin-hero {
        background: linear-gradient(135deg, #10314f 0%, #216aae 100%);
        border-radius: 20px;
        color: #fff;
        padding: 1.5rem;
        box-shadow: 0 18px 40px rgba(16, 49, 79, 0.18);
    }
    .cabin-hero p,
    .cabin-hero small {
        color: rgba(255, 255, 255, 0.8);
    }
    .cabin-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }
    .cabin-btn-soft {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .cabin-btn-soft:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.2);
    }
    .cabin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    .cabin-shift-table th,
    .cabin-shift-table td {
        padding: 1rem 1.15rem;
        vertical-align: middle;
        border-color: #edf1f6;
        min-width: 190px;
    }
    .cabin-shift-table th {
        color: #163f69;
        background: #f8fbfe;
        font-size: .86rem;
    }
    .cabin-shift-table th:first-child,
    .cabin-shift-table td:first-child {
        min-width: 145px;
    }
    .cabin-shift-status {
        display: inline-flex;
        align-items: center;
        padding: .35rem .68rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .cabin-shift-status.available { background: #e8f7ef; color: #18794e; }
    .cabin-shift-status.booked { background: #e8f1ff; color: #216aae; }
    .cabin-shift-status.monthly { background: #eef2ff; color: #5b4ab5; }
    .cabin-shift-status.unavailable { background: #fff3e8; color: #b45309; }
    .cabin-shift-detail {
        margin-top: .45rem;
        color: #66788a;
        font-size: .72rem;
        line-height: 1.45;
    }
    .cabin-stat,
    .cabin-card,
    .cabin-panel {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }
    .cabin-stat {
        padding: 1rem 1.1rem;
    }
    .cabin-stat .value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #10314f;
        line-height: 1;
    }
    .cabin-stat .label {
        color: #5f6b7a;
        font-size: .92rem;
        margin-top: .35rem;
    }
    .cabin-panel .panel-head {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf1f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }
    .cabin-panel .panel-body {
        padding: 1rem 1.25rem;
    }
    .cabin-card {
        overflow: hidden;
    }
    .cabin-card .status-bar {
        height: 6px;
    }
    .status-available {
        background: #dbe6ef;
        color: #566371;
    }
    .status-booked {
        background: #216aae;
        color: #1f5d95;
    }
    .status-monthly {
        background: #059669;
        color: #047857;
    }
    .status-maintenance {
        background: #d97706;
        color: #b45309;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .78rem;
        font-weight: 600;
        padding: .3rem .65rem;
        border-radius: 999px;
        background: #f3f6f9;
    }
    .cabin-card .body {
        padding: 1rem;
    }
    .cabin-card h5 {
        margin: 0;
        color: #10314f;
        font-weight: 700;
    }
    .cabin-code {
        font-size: .78rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #7b8794;
    }
    .cabin-meta {
        color: #5f6b7a;
        font-size: .9rem;
    }
    .cabin-master-view {
        display: none;
    }
    .cabin-master-view.active {
        display: block;
    }
    .cabin-view-switch {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: center;
    }
    .cabin-view-switch .btn.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #2f8fcb 0%, #d34b96 100%);
        box-shadow: 0 10px 20px rgba(47, 143, 203, 0.18);
    }
    .cabin-master-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    .cabin-master-card {
        height: 100%;
    }
    .cabin-master-card.cabin-type-standard { border-top: 4px solid #216aae; }
    .cabin-master-card.cabin-type-premium { border-top: 4px solid #8b5cf6; }
    .cabin-master-card.cabin-type-procedure { border-top: 4px solid #d97706; }
    .cabin-master-card.cabin-type-other { border-top: 4px solid #64748b; }
    .cabin-type-chip { display: inline-flex; align-items: center; padding: .15rem .45rem; border-radius: 999px; font-size: .7rem; font-weight: 700; margin-right: .3rem; }
    .cabin-type-chip.cabin-type-standard { color: #1d5e99; background: #e7f0fa; }
    .cabin-type-chip.cabin-type-premium { color: #7045bd; background: #f0eaff; }
    .cabin-type-chip.cabin-type-procedure { color: #a85b05; background: #fff2dc; }
    .cabin-type-chip.cabin-type-other { color: #4b5563; background: #edf0f3; }
    .cabin-master-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .85rem;
        min-height: 92px;
    }
    .cabin-master-copy {
        min-width: 0;
        flex: 1 1 auto;
    }
    .cabin-master-title {
        font-size: 1rem;
        line-height: 1.35;
        min-height: 2.7em;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .cabin-master-copy .cabin-meta {
        display: block;
        line-height: 1.45;
        min-height: 2.6em;
        margin-top: .2rem;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .cabin-master-head .status-chip {
        flex: 0 0 auto;
        max-width: 45%;
        white-space: normal;
        text-align: center;
        justify-content: center;
    }
    .cabin-master-meta {
        display: flex;
        flex-direction: column;
        gap: .55rem;
        margin-top: 1rem;
        padding-top: .95rem;
        border-top: 1px solid #edf1f6;
    }
    .cabin-master-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .8rem;
        font-size: .88rem;
        color: #5f6b7a;
    }
    .cabin-master-meta-row strong {
        color: #10314f;
        text-align: right;
    }
    .cabin-master-facilities {
        margin-top: 1rem;
        padding: .85rem .95rem;
        border: 1px solid #e7edf4;
        border-radius: 14px;
        background: #fbfdff;
        min-height: 76px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow-wrap: anywhere;
    }
    .cabin-master-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin-top: 1rem;
    }
    .cabin-master-actions form {
        margin: 0;
    }
    .cabin-master-actions .btn {
        flex: 0 0 auto;
    }
    .cabin-split {
        display: grid;
        grid-template-columns: 1.3fr .9fr;
        gap: 1rem;
    }
    .cabin-calendar-layout { display: grid; grid-template-columns: 310px minmax(0, 1fr); gap: 1.25rem; }
    .cabin-calendar-controls { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .85rem; color: #10314f; }
    .cabin-month-weekdays, .cabin-month-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: .3rem; }
    .cabin-month-weekdays { margin-bottom: .35rem; color: #7b8794; font-size: .7rem; text-align: center; font-weight: 700; }
    .cabin-day-button { border: 0; border-radius: 10px; background: transparent; color: #334155; min-height: 38px; font-size: .82rem; transition: .15s; }
    .cabin-day-button:hover { background: #edf5fb; color: #10314f; }
    .cabin-day-button.is-today { box-shadow: inset 0 0 0 1px #216aae; }
    .cabin-day-button.is-selected { background: #10314f; color: #fff; }
    .cabin-day-button.is-outside { color: #c2cad4; pointer-events: none; }
    .cabin-day-view { min-width: 0; border-left: 1px solid #edf1f6; padding-left: 1.25rem; }
    .cabin-day-view-head { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; color: #10314f; }
    .cabin-calendar-legend { display: flex; flex-wrap: wrap; gap: .65rem; color: #657384; font-size: .75rem; }
    .cabin-calendar-legend span { display: inline-flex; align-items: center; gap: .28rem; }
    .cabin-calendar-legend i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
    .cabin-calendar-legend .available { background: #22a06b; }.cabin-calendar-legend .booking { background: #216aae; }.cabin-calendar-legend .subscription { background: #8b5cf6; }.cabin-calendar-legend .unavailable { background: #94a3b8; }
    .cabin-day-schedule { display: flex; flex-direction: column; gap: .7rem; }
    .cabin-subscription-days { display: flex; gap: .45rem; }
    .cabin-subscription-days span { width: 29px; height: 29px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #edf0f3; color: #7b8794; font-size: .72rem; font-weight: 700; }
    .cabin-subscription-days span.active { background: #efe8ff; color: #7045bd; box-shadow: inset 0 0 0 1px #c4b5fd; }
    .cabin-schedule-row { display: grid; grid-template-columns: 140px minmax(0, 1fr); gap: .75rem; align-items: center; }
    .cabin-schedule-label { color: #10314f; font-weight: 700; font-size: .86rem; }.cabin-schedule-label small { display: block; color: #7b8794; font-weight: 400; }
    .cabin-schedule-events { display: flex; gap: .35rem; flex-wrap: wrap; min-height: 40px; }
    .invoice-screen-sheet { background:#fff; border:1px solid #e3e8ef; border-radius:18px; box-shadow:0 8px 24px rgba(15,23,42,.06); padding:1.5rem; }
    .invoice-screen-head,.invoice-screen-parties,.invoice-screen-bottom { display:flex; justify-content:space-between; gap:2rem; flex-wrap:wrap; }
    .invoice-screen-head { border-bottom:2px solid #10314f; padding-bottom:1.25rem; margin-bottom:1.25rem; }.invoice-screen-brand{font-size:1.35rem;font-weight:800;color:#10314f;letter-spacing:.06em}.invoice-screen-title{font-size:1.1rem;font-weight:800;color:#10314f;letter-spacing:.08em}.invoice-screen-parties{padding-bottom:1.25rem}.invoice-screen-parties>div{min-width:260px}.invoice-screen-table thead th{background:#10314f;color:#fff;border:0;padding:.8rem}.invoice-screen-table td{padding:.85rem .75rem;border-color:#edf1f6}.invoice-screen-bottom{padding-top:1rem}.invoice-screen-totals{width:min(340px,100%);margin-left:auto}.invoice-screen-totals>div{display:flex;justify-content:space-between;padding:.35rem 0}.invoice-screen-totals .grand{border-top:1px solid #cfd9e5;margin-top:.35rem;padding-top:.75rem;font-size:1.08rem;color:#10314f}
    .cabin-time-block { border: 0; border-radius: 10px; padding: .45rem .65rem; font-size: .76rem; line-height: 1.25; text-align: left; }.cabin-time-block.available { background: #e6f7ee; color: #127144; cursor: pointer; }.cabin-time-block.booking { background: #e7f0fa; color: #1b5d98; }.cabin-time-block.subscription { background: #f0eaff; color: #7045bd; }.cabin-time-block.unavailable { background: #edf0f3; color: #64748b; }
    .metric-table td,
    .metric-table th {
        white-space: nowrap;
    }
    .empty-note {
        border: 1px dashed #c7d2de;
        border-radius: 16px;
        padding: 1rem;
        text-align: center;
        color: #708090;
        background: #f8fbfd;
    }
    .mini-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #7b8794;
    }
    .mini-value {
        font-size: 1rem;
        font-weight: 600;
        color: #10314f;
    }
    .cabin-type-row {
        flex-wrap: nowrap;
    }
    .cabin-select-card {
        border: 1px solid #dbe4ef;
        border-radius: 16px;
        padding: .9rem 1rem;
        background: #fff;
        min-height: 82px;
        transition: all .18s ease;
    }
    .cabin-select-card strong {
        display: block;
        color: #10314f;
        margin-bottom: .15rem;
    }
    .cabin-select-card small {
        color: #6b7785;
        display: block;
        margin-bottom: .3rem;
        font-size: 11px;
    }
    .cabin-select-card em {
        display: block;
        color: #7b8794;
        font-size: 11px;
        font-style: normal;
        line-height: 1.35;
    }
    .btn-check:checked + .cabin-select-card {
        border-color: #216aae;
        background: #eef4ff;
        box-shadow: inset 0 0 0 1px #216aae;
    }
    .facility-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .facility-group + .facility-group {
        margin-top: 1.25rem;
    }
    .facility-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .75rem;
    }
    .facility-group-title {
        font-size: 13px;
        font-weight: 700;
        color: #10314f;
    }
    .facility-group-sub {
        font-size: 11px;
        color: #7b8794;
        margin-top: .1rem;
    }
    .facility-group-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 .55rem;
        border-radius: 999px;
        background: #e9f9ef;
        color: #15803d;
        border: 1px solid #b7ebc7;
        font-size: 11px;
        font-weight: 700;
    }
    .facility-group-count-paid {
        background: #fff1f2;
        color: #be185d;
        border-color: #f9c3d3;
    }
    .facility-item {
        position: relative;
        cursor: pointer;
    }
    .facility-item input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .facility-choice {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-height: 52px;
        border: 1px solid #dbe4ef;
        border-radius: 14px;
        padding: .7rem .85rem;
        color: #566371;
        background: #fff;
        transition: all .18s ease;
    }
    .facility-mark {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 2px solid #d3dce8;
        flex: 0 0 16px;
        position: relative;
        background: #fff;
    }
    .facility-copy {
        display: flex;
        flex-direction: column;
        gap: .1rem;
        min-width: 0;
    }
    .facility-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .facility-title {
        font-size: 12px;
        font-weight: 500;
        color: #334155;
    }
    .facility-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .15rem .45rem;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .facility-badge-free {
        background: #e9f9ef;
        color: #15803d;
        border: 1px solid #b7ebc7;
    }
    .facility-badge-paid {
        background: #fff1f2;
        color: #be185d;
        border: 1px solid #f9c3d3;
    }
    .facility-meta {
        font-size: 11px;
        color: #7b8794;
        line-height: 1.3;
    }
    .facility-item-free .facility-choice {
        background: #fbfefc;
        border-color: #cfe8d7;
    }
    .facility-item-paid .facility-choice {
        background: #fffafb;
        border-color: #f0d3de;
    }
    .facility-item input:checked + .facility-choice {
        border-color: #9fd4b5;
        background: #effaf3;
    }
    .facility-item-paid input:checked + .facility-choice {
        border-color: #e8a7c0;
        background: #fff1f6;
    }
    .facility-item input:checked + .facility-choice .facility-title {
        color: #1f2937;
    }
    .facility-item input:checked + .facility-choice .facility-mark {
        border-color: #16a34a;
        background: #16a34a;
    }
    .facility-item input:checked + .facility-choice .facility-mark::after {
        content: "";
        position: absolute;
        left: 4px;
        top: 1px;
        width: 4px;
        height: 8px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    .cabin-overview-tile {
        height: 100%;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        padding: 1rem 1.1rem;
    }
    .cabin-overview-sub {
        color: #7b8794;
        font-size: 11px;
        margin-top: .35rem;
    }
    .cabin-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }
    .cabin-detail-box {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        background: #fbfdff;
        padding: .95rem 1rem;
    }
    .cabin-note-box {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        background: #fbfdff;
        padding: .95rem 1rem;
    }
    .cabin-status-stack {
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }
    .cabin-status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-bottom: .85rem;
        border-bottom: 1px solid #edf1f6;
    }
    .cabin-status-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }
    .cabin-name-link {
        color: inherit;
        text-decoration: none;
        display: inline-block;
    }
    .cabin-name-link:hover {
        color: #216aae;
        text-decoration: none;
    }
    .booking-availability-panel {
        border: 1px solid #e3e8ef;
        border-radius: 18px;
        background: #fbfdff;
        padding: 1rem 1.1rem;
    }
    .booking-availability-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: .75rem;
    }
    .booking-availability-title {
        font-size: .98rem;
        font-weight: 700;
        color: #10314f;
    }
    .booking-availability-sub {
        margin-top: .2rem;
        color: #6b7785;
        font-size: .82rem;
        line-height: 1.45;
    }
    .booking-availability-window {
        flex: 0 0 auto;
        padding: .38rem .7rem;
        border-radius: 999px;
        background: #eef4ff;
        color: #216aae;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .booking-availability-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .9rem;
        margin-bottom: .85rem;
    }
    .booking-availability-legend-item {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #5f6b7a;
        font-size: .82rem;
        font-weight: 600;
    }
    .booking-availability-swatch {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }
    .booking-availability-swatch-available {
        background: #16a34a;
    }
    .booking-availability-swatch-blocked {
        background: #dc2626;
    }
    .booking-availability-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .7rem;
    }
    .booking-availability-slot {
        border-radius: 14px;
        padding: .8rem .9rem;
        border: 1px solid #dbe4ef;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .booking-availability-slot-available {
        background: #effaf3;
        border-color: #b7ebc7;
        cursor: pointer;
    }
    .booking-availability-slot-available:hover,
    .booking-availability-slot-available:focus {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(22, 163, 74, 0.10);
        border-color: #65c88a;
    }
    .booking-availability-slot-blocked {
        background: #fff1f2;
        border-color: #fecdd3;
    }
    .booking-availability-slot-selected {
        background: #dff4e7;
        border-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.10);
    }
    .booking-availability-slot-time {
        font-size: .86rem;
        font-weight: 700;
        color: #10314f;
        line-height: 1.35;
    }
    .booking-availability-slot-note {
        margin-top: .22rem;
        font-size: .78rem;
        color: #5f6b7a;
        line-height: 1.4;
    }
    .booking-availability-empty {
        grid-column: 1 / -1;
        border: 1px dashed #c7d2de;
        border-radius: 14px;
        padding: .9rem 1rem;
        text-align: center;
        color: #708090;
        background: #fff;
        font-size: .85rem;
    }
    .cabin-select-note {
        margin-top: .45rem;
        font-size: .8rem;
        color: #708090;
    }
    .cabin-unavailable-list {
        margin-top: .75rem;
        border: 1px dashed #d8dee8;
        border-radius: 14px;
        background: #f8fafc;
        padding: .8rem .9rem;
    }
    .cabin-unavailable-title {
        font-size: .78rem;
        font-weight: 700;
        color: #526272;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .45rem;
    }
    .cabin-unavailable-items {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .cabin-unavailable-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        border: 1px solid #d7dee7;
        background: #edf2f7;
        color: #5d6b79;
        padding: .34rem .65rem;
        font-size: .76rem;
        line-height: 1.25;
    }
    .cabin-unavailable-chip strong {
        color: #314355;
        font-weight: 600;
    }
    .cabin-filterbar {
        display: grid;
        grid-template-columns: minmax(210px, 1.45fr) repeat(5, minmax(130px, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
        align-items: end;
    }
    .cabin-filter-field {
        display: flex;
        flex-direction: column;
        gap: .35rem;
        min-width: 0;
    }
    .cabin-filter-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #7b8794;
        font-weight: 700;
    }
    .cabin-filter-input,
    .cabin-filter-select {
        width: 100%;
        height: 42px;
        border: 1px solid #dbe4ef;
        border-radius: 14px;
        background: #fff;
        padding: 0 .95rem;
        color: #10314f;
        font-size: .92rem;
        outline: none;
        box-shadow: none;
    }
    .cabin-filter-input:focus,
    .cabin-filter-select:focus {
        border-color: #216aae;
        box-shadow: 0 0 0 3px rgba(33, 106, 174, 0.08);
    }
    .cabin-filter-empty {
        grid-column: 1 / -1;
    }
    .cabin-report-section + .cabin-report-section {
        margin-top: 1.5rem;
    }
    .cabin-report-head {
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }
    .cabin-report-head > .cabin-report-subtitle {
        flex: 1 1 auto;
        min-width: 0;
    }
    .cabin-report-tabs {
        display: flex;
        margin-left: auto;
        justify-content: flex-end;
        align-items: center;
        flex: 0 0 auto;
        width: auto !important;
        max-width: none;
    }
    .cabin-report-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .cabin-report-filter-form {
        width: 100%;
    }
    .cabin-report-filterbar {
        grid-template-columns: repeat(6, minmax(130px, 1fr));
        margin-bottom: 0;
    }
    .cabin-filter-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        justify-content: flex-end;
        align-items: flex-end;
        gap: .75rem;
        min-width: 0;
    }
    .cabin-filter-actions.cabin-filter-field {
        gap: .5rem .75rem;
        align-content: end;
    }
    .cabin-filter-actions .cabin-filter-label {
        grid-column: 1 / -1;
    }
    .cabin-filter-actions .btn {
        min-height: 40px !important;
        padding: .5rem 1rem !important;
        border-radius: 15px !important;
        line-height: 1.2;
        width: 100%;
    }
    .cabin-report-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #10314f;
        margin: 0;
    }
    .cabin-report-subtitle {
        color: #6b7785;
        font-size: .92rem;
        margin-top: .2rem;
    }
    .cabin-report-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .cabin-report-bar-list {
        display: flex;
        flex-direction: column;
        gap: .9rem;
    }
    .cabin-report-bar-row {
        display: grid;
        grid-template-columns: minmax(0, 180px) minmax(0, 1fr) auto;
        align-items: center;
        gap: .9rem;
    }
    .cabin-report-bar-label {
        font-size: .9rem;
        color: #495869;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cabin-report-bar-track {
        height: 10px;
        border-radius: 999px;
        background: #e8edf4;
        overflow: hidden;
    }
    .cabin-report-bar-fill {
        height: 100%;
        border-radius: 999px;
    }
    .cabin-report-bar-value {
        font-size: .88rem;
        font-weight: 700;
        color: #10314f;
        text-align: right;
        white-space: nowrap;
    }
    .cabin-report-donut-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    .cabin-report-donut {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: #e8edf4;
        flex-shrink: 0;
    }
    .cabin-report-legend {
        display: flex;
        flex-direction: column;
        gap: .55rem;
        width: 100%;
    }
    .cabin-report-legend-row {
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .9rem;
        color: #495869;
    }
    .cabin-report-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex: 0 0 12px;
    }
    .cabin-report-trend {
        display: flex;
        align-items: flex-end;
        gap: .6rem;
        height: 170px;
    }
    .cabin-report-trend-col {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        gap: .45rem;
        min-width: 0;
    }
    .cabin-report-trend-bar {
        width: 100%;
        min-height: 6px;
        border-radius: 6px 6px 0 0;
        background: #216aae;
    }
    .cabin-report-trend-label {
        font-size: .78rem;
        color: #6b7785;
    }
    .cabin-report-trend-value {
        font-size: .74rem;
        color: #10314f;
        font-weight: 600;
    }
    @media (max-width: 991.98px) {
        .cabin-detail-hero {
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .booking-invoice-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .booking-availability-head {
            flex-wrap: wrap;
        }
        .cabin-split {
            grid-template-columns: 1fr;
        }
        .cabin-view-switch {
            width: 100%;
        }
        .cabin-master-head {
            min-height: 0;
        }
        .cabin-filterbar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cabin-report-filterbar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cabin-type-row {
            flex-wrap: wrap;
        }
        .cabin-detail-grid {
            grid-template-columns: 1fr;
        }
        .facility-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cabin-report-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cabin-report-bar-row {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 575.98px) {
        .cabin-billing-footer {
            justify-content: flex-start;
            gap: .6rem 1rem;
        }
        .booking-invoice-meta {
            grid-template-columns: 1fr;
        }
        .booking-availability-grid {
            grid-template-columns: 1fr;
        }
        .cabin-filterbar {
            grid-template-columns: 1fr;
        }
        .cabin-report-filterbar {
            grid-template-columns: 1fr;
        }
        .cabin-master-head {
            flex-wrap: wrap;
        }
        .cabin-master-head .status-chip {
            max-width: 100%;
        }
        .cabin-master-title,
        .cabin-master-copy .cabin-meta {
            min-height: 0;
            display: block;
        }
        .facility-grid {
            grid-template-columns: 1fr;
        }
        .cabin-report-metrics {
            grid-template-columns: 1fr;
        }
        .cabin-report-donut {
            width: 150px;
            height: 150px;
        }
    }
</style>
