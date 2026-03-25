<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OP Consultation — MedConsult</title>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root {
    --white:        #ffffff;
    --off-white:    #f7f9fb;
    --baby-blue:    #a8c8e8;
    --blue-mid:     #6aaed6;
    --blue-deep:    #3a82b5;
    --blue-light:   #e8f3fb;
    --steel:        #6b7c8d;
    --steel-light:  #9aaab8;
    --steel-dark:   #3d4f5f;
    --steel-bg:     #eef1f4;
    --border:       #d8e2eb;
    --text-primary: #1e2d3d;
    --text-muted:   #7a8fa0;
    --success:      #3aab82;
    --warning:      #e8a84a;
    --danger:       #e05c5c;
    --radius-sm:    6px;
    --radius-md:    10px;
    --radius-lg:    16px;
    --shadow-sm:    0 1px 4px rgba(58,82,115,.08);
    --shadow-md:    0 3px 14px rgba(58,82,115,.12);
    --shadow-lg:    0 8px 32px rgba(58,82,115,.15);
  }

  * { box-sizing: border-box; }

  html, body { height: 100%; overflow: hidden; }

  body {
    font-family: 'Source Sans 3', sans-serif;
    background: var(--off-white);
    color: var(--text-primary);
    font-size: 14px;
    display: flex;
    flex-direction: column;
  }

  /* ── TOP NAV ── */
  .top-nav {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    height: 56px;
    min-height: 56px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    padding: 0 24px;
    gap: 16px;
    z-index: 100;
    box-shadow: var(--shadow-sm);
  }
  .nav-brand {
    font-size: 17px;
    font-weight: 700;
    color: var(--blue-deep);
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .nav-brand i { font-size: 20px; }
  .nav-divider { width: 1px; height: 24px; background: var(--border); }
  .nav-context {
    font-size: 13px;
    color: var(--steel);
    font-weight: 500;
  }
  .nav-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }
  .nav-badge {
    background: var(--blue-light);
    color: var(--blue-deep);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 600;
  }
  .nav-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--steel-dark);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
  }

  /* ── PATIENT BANNER ── */
  .patient-banner {
    background: var(--white);
    border-bottom: 2px solid var(--baby-blue);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    flex-shrink: 0;
  }
  .patient-avatar {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--baby-blue), var(--blue-mid));
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 700;
    flex-shrink: 0;
  }
  .patient-name {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
  }
  .patient-meta {
    font-size: 12.5px;
    color: var(--steel);
    margin-top: 2px;
  }
  .patient-meta span { margin-right: 12px; }
  .patient-tags { display: flex; gap: 6px; flex-wrap: wrap; }
  .tag {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 11.5px;
    font-weight: 600;
  }
  .tag-allergy { background: #fdecea; color: #c0392b; }
  .tag-chronic { background: #fff8e6; color: #a0660a; }
  .tag-visit   { background: var(--blue-light); color: var(--blue-deep); }
  .banner-actions { margin-left: auto; display: flex; gap: 8px; }

  /* ── LAYOUT ── */
  .main-layout {
    display: grid;
    grid-template-columns: 240px 1fr 260px;
    flex: 1;
    min-height: 0;
    overflow: hidden;
  }

  /* ── LEFT SIDEBAR: History ── */
  .sidebar-left {
    background: var(--white);
    border-right: 1px solid var(--border);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px 0;
    height: 100%;
  }
  .sidebar-section-title {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: var(--steel-light);
    padding: 0 16px 8px;
    margin-top: 12px;
  }
  .sidebar-section-title:first-child { margin-top: 0; }
  .history-item {
    padding: 8px 16px;
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all .15s;
  }
  .history-item:hover { background: var(--off-white); border-left-color: var(--baby-blue); }
  .history-item.active { background: var(--blue-light); border-left-color: var(--blue-deep); }
  .history-date {
    font-size: 11.5px;
    color: var(--steel);
    font-weight: 500;
  }
  .history-diag {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 1px;
  }
  .history-doc {
    font-size: 11.5px;
    color: var(--steel-light);
    margin-top: 1px;
  }

  /* Sidebar scrollbar */
  .sidebar-left::-webkit-scrollbar,
  .main-content::-webkit-scrollbar,
  .sidebar-right::-webkit-scrollbar { width: 4px; }
  .sidebar-left::-webkit-scrollbar-thumb,
  .main-content::-webkit-scrollbar-thumb,
  .sidebar-right::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
  }

  /* ── MAIN CONTENT ── */
  .main-content {
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    height: 100%;
    align-content: flex-start;
  }

  /* ── CARD ── */
  .card-section {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }
  .card-header {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: var(--white);
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    user-select: none;
    gap: 10px;
    transition: background .12s;
    min-width: 0;
  }
  .card-header:hover { background: var(--off-white); }
  .card-icon {
    width: 28px; height: 28px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
  }
  .icon-blue  { background: var(--blue-light); color: var(--blue-deep); }
  .icon-steel { background: var(--steel-bg);   color: var(--steel-dark); }
  .icon-green { background: #e6f7f1;           color: var(--success); }
  .icon-warn  { background: #fff5e6;           color: var(--warning); }

  .card-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .card-subtitle {
    font-size: 12px;
    color: var(--steel);
    margin-left: 4px;
  }
  .collapse-icon {
    font-size: 12px;
    color: var(--steel-light);
    transition: transform .2s;
  }
  .collapsed .collapse-icon { transform: rotate(-90deg); }
  .card-body {
    padding: 16px;
  }

  /* ── VITALS GRID ── */
  .vitals-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }
  .vital-box {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    position: relative;
    transition: border-color .15s;
  }
  .vital-box:focus-within { border-color: var(--blue-mid); box-shadow: 0 0 0 3px rgba(106,174,214,.12); }
  .vital-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--steel-light);
    letter-spacing: .4px;
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .vital-input {
    border: none; outline: none;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 17px;
    font-weight: 600;
    color: var(--text-primary);
    width: 100%;
    background: transparent;
  }
  .vital-unit {
    font-size: 11px;
    color: var(--steel-light);
    margin-top: 2px;
  }
  .vital-status {
    position: absolute;
    top: 8px; right: 8px;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--success);
  }
  .vital-status.warn  { background: var(--warning); }
  .vital-status.alert { background: var(--danger); }
  .vital-expand-btn {
    margin-top: 10px;
    font-size: 12px;
    color: var(--blue-deep);
    background: none; border: none;
    padding: 0; cursor: pointer;
    display: flex; align-items: center; gap: 4px;
    font-family: 'Source Sans 3', sans-serif;
    font-weight: 600;
  }
  .vital-extra {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed var(--border);
  }
  .vital-extra.visible { display: grid; }

  /* ── COMPLAINTS / TEXT AREAS ── */
  .form-label-custom {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--steel);
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 5px;
    display: block;
  }
  .form-ctrl {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 9px 12px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    background: var(--white);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    resize: vertical;
  }
  .form-ctrl:focus {
    border-color: var(--blue-mid);
    box-shadow: 0 0 0 3px rgba(106,174,214,.12);
  }
  .form-ctrl::placeholder { color: var(--steel-light); }

  /* Tag inputs */
  .tag-input-wrap {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 6px 10px;
    display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
    min-height: 40px;
    cursor: text;
    transition: border-color .15s, box-shadow .15s;
  }
  .tag-input-wrap:focus-within {
    border-color: var(--blue-mid);
    box-shadow: 0 0 0 3px rgba(106,174,214,.12);
  }
  .tag-pill {
    background: var(--blue-light);
    color: var(--blue-deep);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 12.5px;
    font-weight: 600;
    display: flex; align-items: center; gap: 5px;
    white-space: nowrap;
  }
  .tag-pill i { cursor: pointer; font-size: 10px; opacity: .6; }
  .tag-pill i:hover { opacity: 1; }
  .tag-input-field {
    border: none; outline: none;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    flex: 1;
    min-width: 80px;
    background: transparent;
  }

  /* ── EXAMINATION BODY MAP ── */
  .exam-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .exam-system {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden;
  }
  .exam-system-header {
    background: var(--off-white);
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--steel-dark);
    display: flex; align-items: center; gap: 6px;
  }
  .exam-system-body { padding: 10px 12px; }
  .radio-group { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 6px; }
  .radio-opt {
    display: flex; align-items: center; gap: 5px;
    cursor: pointer;
    font-size: 13px;
    color: var(--text-primary);
  }
  .radio-opt input { accent-color: var(--blue-deep); cursor: pointer; }
  .exam-note-inline {
    width: 100%;
    border: none;
    border-top: 1px dashed var(--border);
    outline: none;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    padding: 6px 0 0;
    background: transparent;
    margin-top: 6px;
  }
  .exam-note-inline::placeholder { color: var(--steel-light); }
  .exam-expand-link {
    margin-top: 8px;
    font-size: 12px;
    color: var(--blue-deep);
    background: none; border: none;
    padding: 0; cursor: pointer;
    font-family: 'Source Sans 3', sans-serif;
    font-weight: 600;
    display: flex; align-items: center; gap: 4px;
  }

  /* ── DIAGNOSIS — compact single row ── */
  .diag-header-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 4px 6px;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--steel-light);
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 4px;
  }
  .diag-row {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 5px 4px;
    border-bottom: 1px solid var(--border);
    position: relative;
  }
  .diag-row:last-of-type { border-bottom: none; }

  /* ICD-10 typeahead wrapper */
  .icd-wrap {
    flex: 1;
    min-width: 0;
    position: relative;
  }
  .icd-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 7px 10px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    outline: none;
    transition: border-color .15s;
    background: var(--white);
  }
  .icd-input:focus { border-color: var(--blue-mid); box-shadow: 0 0 0 3px rgba(106,174,214,.12); }
  .icd-input::placeholder { color: var(--steel-light); }

  .icd-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 3px);
    left: 0; right: 0;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    z-index: 200;
    max-height: 220px;
    overflow-y: auto;
  }
  .icd-dropdown.open { display: block; }
  .icd-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: baseline;
    gap: 10px;
    transition: background .1s;
    border-bottom: 1px solid var(--border);
  }
  .icd-item:last-child { border-bottom: none; }
  .icd-item:hover, .icd-item.focused { background: var(--blue-light); }
  .icd-code {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--blue-deep);
    font-family: 'Source Sans 3', monospace;
    flex-shrink: 0;
    min-width: 52px;
  }
  .icd-name {
    font-size: 13px;
    color: var(--text-primary);
    flex: 1;
    min-width: 0;
  }
  .icd-name em {
    font-style: normal;
    background: #fff3b0;
    border-radius: 2px;
    padding: 0 1px;
  }
  .icd-loading {
    padding: 10px 14px;
    font-size: 12.5px;
    color: var(--steel-light);
    font-style: italic;
  }
  .icd-empty {
    padding: 10px 14px;
    font-size: 12.5px;
    color: var(--steel-light);
  }

  /* ICD code badge shown after selection */
  .icd-code-badge {
    width: 86px;
    flex-shrink: 0;
    background: var(--blue-light);
    color: var(--blue-deep);
    border-radius: var(--radius-sm);
    padding: 6px 8px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Source Sans 3', monospace;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1px solid transparent;
    cursor: default;
    line-height: 1.4;
  }
  .icd-code-badge.empty {
    background: var(--off-white);
    color: var(--steel-light);
    font-weight: 400;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 11.5px;
  }

  .diag-select {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 7px 6px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 12.5px;
    color: var(--text-primary);
    outline: none;
    background: var(--white);
    cursor: pointer;
    transition: border-color .15s;
  }
  .diag-select:focus { border-color: var(--blue-mid); }
  .diag-del {
    border: none; background: none;
    color: var(--steel-light);
    cursor: pointer;
    font-size: 14px;
    padding: 3px;
    border-radius: 4px;
    display: flex; align-items: center;
    transition: all .12s;
    flex-shrink: 0;
  }
  .diag-del:hover { background: #fdecea; color: var(--danger); }

  .add-diag-btn {
    border: 1.5px dashed var(--baby-blue);
    background: none;
    color: var(--blue-deep);
    border-radius: var(--radius-sm);
    padding: 7px 14px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    transition: all .15s;
    width: 100%;
    margin-top: 8px;
  }
  .add-diag-btn:hover { background: var(--blue-light); border-style: solid; }

  /* ── PRESCRIPTION — new lean design ── */
  .rx-col-headers {
    display: grid;
    grid-template-columns: 2fr 130px 120px 100px 1fr 60px;
    gap: 6px;
    padding: 0 6px 7px;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--steel-light);
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 2px;
  }
  .rxh-drug,.rxh-pack,.rxh-freq,.rxh-dur,.rxh-inst,.rxh-actions { display:flex; align-items:center; }

  /* Each Rx entry = wrapper div that can expand */
  .rx-entry {
    border-bottom: 1px solid var(--border);
    transition: background .1s;
  }
  .rx-entry:last-child { border-bottom: none; }

  /* The one-line row */
  .rx-row {
    display: grid;
    grid-template-columns: 2fr 130px 120px 100px 1fr 60px;
    gap: 6px;
    padding: 6px 6px;
    align-items: center;
  }
  .rx-entry:hover .rx-row { background: var(--off-white); }

  /* Drug typeahead cell */
  .rx-drug-cell { position: relative; min-width: 0; }
  .rx-drug-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 7px 10px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    outline: none;
    transition: border-color .15s;
    background: var(--white);
  }
  .rx-drug-input:focus { border-color: var(--blue-mid); box-shadow: 0 0 0 3px rgba(106,174,214,.12); }
  .rx-drug-input::placeholder { color: var(--steel-light); }
  .rx-drug-input.selected-drug {
    font-weight: 600;
    border-color: var(--baby-blue);
    background: var(--blue-light);
    color: var(--blue-deep);
  }

  .rx-drug-drop {
    display: none;
    position: absolute;
    top: calc(100% + 3px);
    left: 0; right: 0;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    z-index: 300;
    max-height: 240px;
    overflow-y: auto;
  }
  .rx-drug-drop.open { display: block; }

  .rx-drug-item {
    padding: 9px 12px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background .1s;
  }
  .rx-drug-item:last-child { border-bottom: none; }
  .rx-drug-item:hover, .rx-drug-item.focused { background: var(--blue-light); }
  .rx-drug-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
  }
  .rx-drug-name em { font-style:normal; background:#fff3b0; border-radius:2px; padding:0 1px; }
  .rx-drug-meta {
    font-size: 11.5px;
    color: var(--steel);
    margin-top: 2px;
  }
  .rx-drug-mfr {
    font-size: 11px;
    color: var(--steel-light);
    margin-top: 1px;
  }

  /* Pack label — shown after selection */
  .rx-pack-label {
    font-size: 12px;
    color: var(--steel-dark);
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 0 4px;
  }
  .rx-pack-empty {
    font-size: 12px;
    color: var(--steel-light);
    font-style: italic;
    padding: 0 4px;
  }

  /* Compact selects */
  .rx-sel {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 7px 6px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    background: var(--white);
    outline: none;
    cursor: pointer;
    transition: border-color .12s;
  }
  .rx-sel:focus { border-color: var(--blue-mid); }
  .rx-dur-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 7px 9px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    background: var(--white);
    outline: none;
    transition: border-color .12s;
  }
  .rx-dur-input:focus { border-color: var(--blue-mid); }
  .rx-dur-input::placeholder { color: var(--steel-light); }

  /* Action buttons cell */
  .rx-actions-cell {
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: flex-end;
  }
  .rx-info-btn {
    border: 1px solid var(--border);
    background: var(--white);
    color: var(--steel);
    border-radius: var(--radius-sm);
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all .12s;
    flex-shrink: 0;
  }
  .rx-info-btn:hover { background: var(--blue-light); color: var(--blue-deep); border-color: var(--baby-blue); }
  .rx-info-btn.active { background: var(--blue-deep); color: white; border-color: var(--blue-deep); }
  .rx-del-btn {
    border: none; background: none;
    color: var(--steel-light);
    cursor: pointer;
    font-size: 14px;
    width: 28px; height: 28px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    transition: all .12s;
    flex-shrink: 0;
  }
  .rx-del-btn:hover { background: #fdecea; color: var(--danger); }

  /* ── Drug detail drawer ── */
  .rx-detail-drawer {
    display: none;
    background: var(--off-white);
    border-top: 1px dashed var(--border);
    padding: 14px 16px 16px;
    animation: drawerSlide .15s ease;
  }
  .rx-detail-drawer.open { display: block; }
  @keyframes drawerSlide {
    from { opacity:0; transform:translateY(-4px); }
    to   { opacity:1; transform:translateY(0); }
  }
  .drawer-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
  }
  .drawer-block-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--steel-light);
    margin-bottom: 4px;
  }
  .drawer-val {
    font-size: 13px;
    color: var(--text-primary);
    font-weight: 500;
    line-height: 1.4;
  }
  .drawer-val.muted { color: var(--steel); font-weight: 400; }
  .drawer-tag {
    display: inline-block;
    background: var(--steel-bg);
    color: var(--steel-dark);
    border-radius: 20px;
    padding: 2px 9px;
    font-size: 11.5px;
    font-weight: 600;
    margin: 2px 2px 0 0;
  }
  .drawer-tag.blue { background: var(--blue-light); color: var(--blue-deep); }
  .drawer-tag.warn { background: #fff5e6; color: #a0660a; }
  .drawer-tag.danger { background: #fdecea; color: #c0392b; }
  .drawer-divider { border: none; border-top: 1px solid var(--border); margin: 10px 0; }
  .alternatives-row {
    display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
  }
  .alt-pill {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 4px 10px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--steel-dark);
    background: var(--white);
    cursor: pointer;
    transition: all .12s;
    display: flex; align-items: center; gap: 5px;
  }
  .alt-pill:hover { border-color: var(--baby-blue); background: var(--blue-light); color: var(--blue-deep); }
  .alt-pill i { font-size: 11px; }

  .add-rx-btn {
    border: 1.5px dashed var(--baby-blue);
    background: none;
    color: var(--blue-deep);
    border-radius: var(--radius-sm);
    padding: 7px 14px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    transition: all .15s;
    margin-top: 8px;
    margin-left: 0;
  }
  .add-rx-btn:hover { background: var(--blue-light); border-style: solid; }


  /* ── RIGHT SIDEBAR: Summary ── */
  .sidebar-right {
    background: var(--white);
    border-left: 1px solid var(--border);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    height: 100%;
  }
  .summary-block-title {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
    color: var(--steel-light);
    margin-bottom: 8px;
  }
  .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 5px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    gap: 8px;
  }
  .summary-item:last-child { border-bottom: none; }
  .summary-key { color: var(--steel); font-weight: 500; flex-shrink: 0; }
  .summary-val { color: var(--text-primary); font-weight: 600; text-align: right; }
  .summary-val.normal { color: var(--success); }
  .summary-val.abnormal { color: var(--danger); }

  .quick-actions { display: flex; flex-direction: column; gap: 7px; }
  .quick-btn {
    border: 1px solid var(--border);
    background: var(--white);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: all .12s;
    text-align: left;
  }
  .quick-btn:hover { background: var(--off-white); border-color: var(--baby-blue); }
  .quick-btn i { font-size: 15px; color: var(--blue-deep); }

  .followup-picker {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
  }
  .followup-opt {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 6px 8px;
    font-size: 12.5px;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    color: var(--steel-dark);
    transition: all .12s;
  }
  .followup-opt:hover, .followup-opt.selected {
    background: var(--blue-light);
    border-color: var(--baby-blue);
    color: var(--blue-deep);
  }

  /* ── BOTTOM BAR ── */
  .bottom-bar {
    background: var(--white);
    border-top: 1px solid var(--border);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    box-shadow: 0 -2px 8px rgba(58,82,115,.07);
  }
  .btn-primary-custom {
    background: var(--blue-deep);
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    padding: 10px 24px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: all .15s;
    box-shadow: 0 2px 8px rgba(58,130,181,.3);
  }
  .btn-primary-custom:hover { background: #2d6d9b; box-shadow: 0 4px 14px rgba(58,130,181,.4); }
  .btn-secondary-custom {
    background: var(--white);
    color: var(--steel-dark);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 18px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; gap: 7px;
    transition: all .15s;
  }
  .btn-secondary-custom:hover { background: var(--off-white); border-color: var(--baby-blue); }
  .btn-danger-outline {
    background: none;
    color: var(--danger);
    border: 1px solid #f5b7b7;
    border-radius: var(--radius-sm);
    padding: 10px 18px;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; gap: 7px;
    transition: all .15s;
    margin-left: auto;
  }
  .btn-danger-outline:hover { background: #fdecea; }

  /* ── PROGRESS STEPS ── */
  .progress-steps {
    display: flex; align-items: center; gap: 0;
    margin-right: 16px;
  }
  .step {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--steel-light);
  }
  .step-dot {
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 2px solid var(--border);
    background: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--steel-light);
    transition: all .2s;
    flex-shrink: 0;
  }
  .step.done .step-dot  { background: var(--success); border-color: var(--success); color: white; }
  .step.active .step-dot { background: var(--blue-deep); border-color: var(--blue-deep); color: white; }
  .step.active { color: var(--blue-deep); }
  .step.done   { color: var(--success); }
  .step-line {
    width: 24px; height: 2px;
    background: var(--border);
    margin: 0 2px;
    transition: background .2s;
  }
  .step-line.done { background: var(--success); }

  /* ── UTILITIES ── */
  .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
  select.form-ctrl { cursor: pointer; }

  .section-note {
    font-size: 12px;
    color: var(--steel-light);
    font-style: italic;
    margin-top: 4px;
  }

  /* ── MODAL: more vitals / system exam ── */
  .modal-backdrop-custom {
    display: none;
    position: fixed; inset: 0;
    background: rgba(30,45,61,.3);
    backdrop-filter: blur(2px);
    z-index: 999;
  }
  .modal-backdrop-custom.visible { display: flex; align-items: center; justify-content: center; }
  .modal-box {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    width: 600px;
    max-width: 95vw;
    max-height: 85vh;
    display: flex; flex-direction: column;
  }
  .modal-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
  }
  .modal-title { font-size: 16px; font-weight: 700; flex: 1; }
  .modal-close {
    border: none; background: none;
    color: var(--steel);
    font-size: 18px;
    cursor: pointer;
    line-height: 1;
    padding: 2px;
  }
  .modal-body { padding: 20px; overflow-y: auto; flex: 1; }
  .modal-foot {
    padding: 14px 20px;
    border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 10px;
  }

  /* ── SPECIALTY BAR ── */
  .specialty-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--off-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    margin-bottom: 14px;
    flex-wrap: wrap;
  }
  .specialty-bar-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--steel-light);
    white-space: nowrap;
  }
  .specialty-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex: 1;
  }
  .spec-chip {
    border: 1.5px solid var(--border);
    background: var(--white);
    color: var(--steel-dark);
    border-radius: 20px;
    padding: 4px 13px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Source Sans 3', sans-serif;
    transition: all .15s;
    white-space: nowrap;
  }
  .spec-chip:hover { border-color: var(--baby-blue); color: var(--blue-deep); background: var(--blue-light); }
  .spec-chip.active { background: var(--blue-deep); border-color: var(--blue-deep); color: white; }

  /* ── ADD SYSTEM MODAL ── */
  .system-picker-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 16px;
  }
  .sys-pick-item {
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--steel-dark);
    transition: all .15s;
  }
  .sys-pick-item:hover { border-color: var(--baby-blue); background: var(--blue-light); color: var(--blue-deep); }
  .sys-pick-item.selected { border-color: var(--blue-deep); background: var(--blue-light); color: var(--blue-deep); }
  .sys-pick-item i { font-size: 15px; }
  .sys-pick-item .sys-check {
    margin-left: auto;
    font-size: 13px;
    display: none;
  }
  .sys-pick-item.selected .sys-check { display: block; color: var(--blue-deep); }

  /* Exam system card — remove button on hover */
  .exam-system { position: relative; }
  .exam-system-remove {
    position: absolute;
    top: 5px; right: 6px;
    border: none; background: none;
    color: var(--steel-light);
    font-size: 13px;
    cursor: pointer;
    line-height: 1;
    padding: 2px 4px;
    border-radius: 4px;
    opacity: 0;
    transition: opacity .15s, color .15s;
  }
  .exam-system:hover .exam-system-remove { opacity: 1; }
  .exam-system-remove:hover { color: var(--danger); background: #fdecea; }

    font-size: 10.5px;
    color: var(--steel-light);
    margin-top: 1px;
  }
  .inline-flex { display: flex; align-items: center; gap: 8px; }
  .ml-auto { margin-left: auto; }
</style>
</head>
<body>

<!-- TOP NAV -->
<div class="top-nav">
  <div class="nav-brand">
    <i class="bi bi-heart-pulse-fill"></i>
    MedConsult
  </div>
  <div class="nav-divider"></div>
  <div class="nav-context">Outpatient · General Medicine</div>
  <div class="nav-right">
    <div class="nav-badge"><i class="bi bi-clock me-1"></i>Token 14 of 28</div>
    <div class="nav-badge" style="background:#e6f7f1;color:#2d8a6a;"><i class="bi bi-wifi me-1"></i>Online</div>
    <div class="nav-avatar" title="Dr. Priya Anand">PA</div>
  </div>
</div>

<!-- PATIENT BANNER -->
<div class="patient-banner">
  <div class="patient-avatar">RK</div>
  <div>
    <div class="patient-name">Ramesh Kumar &nbsp;<span style="font-size:13px;font-weight:400;color:var(--steel)">M / 46 yrs</span></div>
    <div class="patient-meta">
      <span><i class="bi bi-hash me-1"></i>OP-2024-08471</span>
      <span><i class="bi bi-telephone me-1"></i>+91 98400 12345</span>
      <span><i class="bi bi-geo-alt me-1"></i>Chennai</span>
    </div>
  </div>
  <div class="patient-tags">
    <span class="tag tag-allergy"><i class="bi bi-exclamation-triangle-fill"></i>Penicillin Allergy</span>
    <span class="tag tag-chronic"><i class="bi bi-heart-fill"></i>HTN · DM2</span>
    <span class="tag tag-visit"><i class="bi bi-arrow-repeat"></i>Follow-up Visit #3</span>
  </div>
  <div class="banner-actions">
    <button class="btn-secondary-custom" onclick="openModal('histModal')"><i class="bi bi-clock-history"></i> Full History</button>
    <button class="btn-secondary-custom"><i class="bi bi-file-earmark-medical"></i> Reports</button>
  </div>
</div>

<!-- MAIN 3-COLUMN LAYOUT -->
<div class="main-layout">

  <!-- LEFT: Past Visits -->
  <div class="sidebar-left">
    <div class="sidebar-section-title">Past Visits</div>

    <div class="history-item active">
      <div class="history-date">12 Mar 2024 · Today</div>
      <div class="history-diag">Current Visit</div>
      <div class="history-doc">Dr. Priya Anand</div>
    </div>
    <div class="history-item" onclick="loadHistory(this)">
      <div class="history-date">18 Jan 2024</div>
      <div class="history-diag">Hypertension F/U</div>
      <div class="history-doc">Dr. Priya Anand</div>
    </div>
    <div class="history-item" onclick="loadHistory(this)">
      <div class="history-date">04 Nov 2023</div>
      <div class="history-diag">Fever + Cough</div>
      <div class="history-doc">Dr. Suresh Iyer</div>
    </div>
    <div class="history-item" onclick="loadHistory(this)">
      <div class="history-date">22 Aug 2023</div>
      <div class="history-diag">Diabetes F/U</div>
      <div class="history-doc">Dr. Priya Anand</div>
    </div>
    <div class="history-item" onclick="loadHistory(this)">
      <div class="history-date">10 May 2023</div>
      <div class="history-diag">Annual Checkup</div>
      <div class="history-doc">Dr. Meena Raj</div>
    </div>

    <div class="sidebar-section-title" style="margin-top:16px">Ongoing Meds</div>
    <div style="padding:0 16px;">
      <div class="history-item" style="padding:8px 0;">
        <div class="history-diag" style="font-size:12.5px;">Metformin 500mg · BD</div>
        <div class="history-doc">Since Jan 2023</div>
      </div>
      <div class="history-item" style="padding:8px 0;">
        <div class="history-diag" style="font-size:12.5px;">Amlodipine 5mg · OD</div>
        <div class="history-doc">Since Mar 2022</div>
      </div>
      <div class="history-item" style="padding:8px 0;border-bottom:none;">
        <div class="history-diag" style="font-size:12.5px;">Atorvastatin 10mg · HS</div>
        <div class="history-doc">Since Mar 2022</div>
      </div>
    </div>

    <div class="sidebar-section-title" style="margin-top:16px">Investigations Due</div>
    <div style="padding:0 16px;">
      <div class="history-item" style="padding:8px 0;">
        <div class="history-diag" style="font-size:12.5px;color:var(--warning)"><i class="bi bi-exclamation-circle me-1"></i>HbA1c — Overdue</div>
        <div class="history-doc">Last: 3 months ago</div>
      </div>
      <div class="history-item" style="padding:8px 0;border-bottom:none;">
        <div class="history-diag" style="font-size:12.5px;">Lipid Panel</div>
        <div class="history-doc">Last: 1 month ago — OK</div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content" id="mainContent">

    <!-- ❶ VITALS -->
    <div class="card-section" id="secVitals">
      <div class="card-header" onclick="toggleCard('secVitals')">
        <div class="card-icon icon-blue"><i class="bi bi-activity"></i></div>
        <div class="card-title">Vitals</div>
        <span class="card-subtitle" id="vitals-summary">BP 128/84 · HR 78 · SpO₂ 98%</span>
        <i class="bi bi-chevron-down collapse-icon"></i>
      </div>
      <div class="card-body">
        <div class="vitals-grid" id="vitalsBasic">
          <!-- BP -->
          <div class="vital-box">
            <div class="vital-status warn" id="bpDot"></div>
            <div class="vital-label">Blood Pressure</div>
            <div style="display:flex;align-items:center;gap:4px;">
              <input class="vital-input" id="bpSys" type="number" value="128" style="width:52px;" onchange="checkVitals()">
              <span style="color:var(--steel-light);font-size:16px;">/</span>
              <input class="vital-input" id="bpDia" type="number" value="84" style="width:52px;" onchange="checkVitals()">
            </div>
            <div class="vital-unit">mmHg &nbsp;<span class="ref-range">Normal &lt;120/80</span></div>
          </div>
          <!-- HR -->
          <div class="vital-box">
            <div class="vital-status" id="hrDot"></div>
            <div class="vital-label">Heart Rate</div>
            <input class="vital-input" id="hr" type="number" value="78" onchange="checkVitals()">
            <div class="vital-unit">bpm &nbsp;<span class="ref-range">60–100</span></div>
          </div>
          <!-- SpO2 -->
          <div class="vital-box">
            <div class="vital-status" id="spoDot"></div>
            <div class="vital-label">SpO₂</div>
            <input class="vital-input" id="spo2" type="number" value="98" onchange="checkVitals()">
            <div class="vital-unit">% &nbsp;<span class="ref-range">≥95%</span></div>
          </div>
          <!-- Temp -->
          <div class="vital-box">
            <div class="vital-status" id="tempDot"></div>
            <div class="vital-label">Temperature</div>
            <input class="vital-input" id="temp" type="number" step="0.1" value="98.4" onchange="checkVitals()">
            <div class="vital-unit">°F &nbsp;<span class="ref-range">97–99°F</span></div>
          </div>
        </div>

        <button class="vital-expand-btn" onclick="toggleVitalsExtra(this)">
          <i class="bi bi-plus-circle"></i> More vitals (Weight, Height, RR, GRBS…)
        </button>

        <div class="vital-extra" id="vitalsExtra">
          <div class="vital-box">
            <div class="vital-label">Weight</div>
            <input class="vital-input" type="number" value="78" style="width:70px;">
            <div class="vital-unit">kg</div>
          </div>
          <div class="vital-box">
            <div class="vital-label">Height</div>
            <input class="vital-input" type="number" value="172" style="width:70px;">
            <div class="vital-unit">cm</div>
          </div>
          <div class="vital-box">
            <div class="vital-label">BMI</div>
            <input class="vital-input" type="text" value="26.4" readonly style="color:var(--warning)">
            <div class="vital-unit">kg/m² &nbsp;<span style="color:var(--warning)">Overweight</span></div>
          </div>
          <div class="vital-box">
            <div class="vital-label">Resp. Rate</div>
            <input class="vital-input" type="number" value="16">
            <div class="vital-unit">breaths/min</div>
          </div>
          <div class="vital-box">
            <div class="vital-label">GRBS</div>
            <input class="vital-input" type="number" value="142" style="color:var(--warning)">
            <div class="vital-unit">mg/dL &nbsp;<span class="ref-range">PP &lt;140</span></div>
          </div>
          <div class="vital-box">
            <div class="vital-label">Waist Circ.</div>
            <input class="vital-input" type="number" value="91">
            <div class="vital-unit">cm</div>
          </div>
          <div class="vital-box">
            <div class="vital-label">Pain Score</div>
            <input class="vital-input" type="number" value="2" min="0" max="10">
            <div class="vital-unit">/ 10 (VAS)</div>
          </div>
          <div class="vital-box">
            <div class="vital-label">GCS</div>
            <input class="vital-input" type="number" value="15" min="3" max="15">
            <div class="vital-unit">/ 15</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ❷ CHIEF COMPLAINTS -->
    <div class="card-section" id="secComplaints">
      <div class="card-header" onclick="toggleCard('secComplaints')">
        <div class="card-icon icon-steel"><i class="bi bi-chat-left-text"></i></div>
        <div class="card-title">Chief Complaints</div>
        <i class="bi bi-chevron-down collapse-icon"></i>
      </div>
      <div class="card-body">
        <div class="row-2" style="margin-bottom:12px;">
          <div>
            <label class="form-label-custom">Complaints</label>
            <div class="tag-input-wrap" id="complaintWrap" onclick="focusTagInput('complaintInput')">
              <span class="tag-pill">Headache <i class="bi bi-x" onclick="removeTag(this)"></i></span>
              <span class="tag-pill">Dizziness <i class="bi bi-x" onclick="removeTag(this)"></i></span>
              <input class="tag-input-field" id="complaintInput" placeholder="Type and press Enter…" onkeydown="addTag(event,'complaintWrap')">
            </div>
          </div>
          <div>
            <label class="form-label-custom">Duration</label>
            <div style="display:flex;gap:8px;">
              <input type="number" class="form-ctrl" value="3" style="width:70px;">
              <select class="form-ctrl">
                <option>Days</option>
                <option>Weeks</option>
                <option>Months</option>
              </select>
            </div>
          </div>
        </div>
        <div>
          <label class="form-label-custom">History of Present Illness <span style="font-weight:400;text-transform:none;font-size:12px;color:var(--steel-light);">(optional)</span></label>
          <textarea class="form-ctrl" rows="2" placeholder="Briefly describe the presenting complaints, onset, progression…"></textarea>
        </div>
        <button class="vital-expand-btn" onclick="toggleSection('hpiExtra',this)" style="margin-top:10px;">
          <i class="bi bi-plus-circle"></i> Add more detail (Aggravating / Relieving factors, Associated symptoms)
        </button>
        <div id="hpiExtra" style="display:none;margin-top:12px;">
          <div class="row-2">
            <div>
              <label class="form-label-custom">Aggravating Factors</label>
              <div class="tag-input-wrap" id="aggWrap" onclick="focusTagInput('aggInput')">
                <input class="tag-input-field" id="aggInput" placeholder="e.g. exertion…" onkeydown="addTag(event,'aggWrap')">
              </div>
            </div>
            <div>
              <label class="form-label-custom">Relieving Factors</label>
              <div class="tag-input-wrap" id="relWrap" onclick="focusTagInput('relInput')">
                <input class="tag-input-field" id="relInput" placeholder="e.g. rest, medication…" onkeydown="addTag(event,'relWrap')">
              </div>
            </div>
          </div>
          <div style="margin-top:12px;">
            <label class="form-label-custom">Associated Symptoms</label>
            <div class="tag-input-wrap" id="assocWrap" onclick="focusTagInput('assocInput')">
              <input class="tag-input-field" id="assocInput" placeholder="e.g. nausea, blurred vision…" onkeydown="addTag(event,'assocWrap')">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ❸ EXAMINATION -->
    <div class="card-section" id="secExam">
      <div class="card-header" onclick="toggleCard('secExam')">
        <div class="card-icon icon-green"><i class="bi bi-stethoscope"></i></div>
        <div class="card-title">Examination</div>
        <span class="card-subtitle" id="examSpecialtyLabel">General Medicine</span>
        <button class="exam-expand-link" style="margin:0;margin-left:8px;font-size:12px;" onclick="event.stopPropagation();openAddSystemModal()">
          <i class="bi bi-plus-circle"></i> Add system
        </button>
        <i class="bi bi-chevron-down collapse-icon" style="margin-left:8px;"></i>
      </div>
      <div class="card-body">

        <!-- General appearance -->
        <div id="genAppearanceRow" style="margin-bottom:12px;">
          <label class="form-label-custom">General Appearance</label>
          <div class="radio-group">
            <label class="radio-opt"><input type="radio" name="gen" value="well" checked> Well</label>
            <label class="radio-opt"><input type="radio" name="gen" value="ill"> Ill-looking</label>
            <label class="radio-opt"><input type="radio" name="gen" value="distress"> In distress</label>
            <label class="radio-opt"><input type="radio" name="gen" value="toxic"> Toxic</label>
          </div>
        </div>

        <!-- Dynamic systems grid -->
        <div class="exam-grid" id="examSystemsGrid"></div>

      </div>
    </div>

    <!-- ❹ DIAGNOSIS -->
    <div class="card-section" id="secDiag">
      <div class="card-header" onclick="toggleCard('secDiag')">
        <div class="card-icon icon-warn"><i class="bi bi-clipboard2-pulse"></i></div>
        <div class="card-title">Diagnosis</div>
        <i class="bi bi-chevron-down collapse-icon"></i>
      </div>
      <div class="card-body" id="diagBody">

        <!-- Column headers -->
        <div class="diag-header-row">
          <div style="flex:1;min-width:0;">Diagnosis</div>
          <div style="width:86px;">ICD-10</div>
          <div style="width:106px;">Type</div>
          <div style="width:90px;">Status</div>
          <div style="width:24px;"></div>
        </div>

        <div id="diagRows"></div>

        <button class="add-diag-btn" onclick="addDiagRow()">
          <i class="bi bi-plus-circle"></i> Add diagnosis
        </button>
      </div>
    </div>

    <!-- ❺ INVESTIGATIONS -->
    <div class="card-section" id="secInv">
      <div class="card-header" onclick="toggleCard('secInv')">
        <div class="card-icon icon-blue"><i class="bi bi-flask"></i></div>
        <div class="card-title">Investigations</div>
        <i class="bi bi-chevron-down collapse-icon"></i>
      </div>
      <div class="card-body">
        <div style="margin-bottom:10px;">
          <label class="form-label-custom">Order Tests</label>
          <div class="tag-input-wrap" id="invWrap" onclick="focusTagInput('invInput')">
            <span class="tag-pill" style="background:#e6f7f1;color:#2d8a6a;">HbA1c <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <span class="tag-pill" style="background:#e6f7f1;color:#2d8a6a;">Lipid Profile <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <span class="tag-pill" style="background:#e6f7f1;color:#2d8a6a;">Serum Creatinine <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <input class="tag-input-field" id="invInput" placeholder="Search test…" onkeydown="addTag(event,'invWrap','#e6f7f1','#2d8a6a')">
          </div>
        </div>
        <div>
          <label class="form-label-custom">Special Instructions <span style="font-weight:400;text-transform:none;font-size:12px;color:var(--steel-light);">(optional)</span></label>
          <input type="text" class="form-ctrl" placeholder="e.g. Fasting sample required, collect before next dose…">
        </div>
      </div>
    </div>

    <!-- ❻ PRESCRIPTION (Rx) -->
    <div class="card-section" id="secRx">
      <div class="card-header" onclick="toggleCard('secRx')">
        <div class="card-icon" style="background:var(--blue-deep);color:white;font-weight:700;font-family:'Source Sans 3',sans-serif;font-size:13px;display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;flex-shrink:0;">Rx</div>
        <div class="card-title">Prescription</div>
        <i class="bi bi-chevron-down collapse-icon"></i>
      </div>
      <div class="card-body">

        <!-- Column headers -->
        <div class="rx-col-headers">
          <div class="rxh-drug">Medicine</div>
          <div class="rxh-pack">Pack</div>
          <div class="rxh-freq">Frequency</div>
          <div class="rxh-dur">Duration</div>
          <div class="rxh-inst">Instructions</div>
          <div class="rxh-actions"></div>
        </div>

        <div id="rxRows"></div>

        <button class="add-rx-btn" onclick="addRxRow()">
          <i class="bi bi-plus-circle"></i> Add medicine
        </button>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);">
          <label class="form-label-custom">Advice / Instructions to Patient</label>
          <textarea class="form-ctrl" rows="2" placeholder="Dietary advice, lifestyle modifications, what to watch for…">Low salt diet. Monitor BP daily. Reduce stress. Follow up in 4 weeks.</textarea>
        </div>
      </div>
    </div>

    <!-- Spacer -->
    <div style="height:8px;"></div>

  </div><!-- /main-content -->

  <!-- RIGHT SIDEBAR: Live Summary -->
  <div class="sidebar-right">

    <!-- Current Vitals Summary -->
    <div>
      <div class="summary-block-title">Vitals Summary</div>
      <div class="summary-item">
        <span class="summary-key">BP</span>
        <span class="summary-val abnormal">128 / 84</span>
      </div>
      <div class="summary-item">
        <span class="summary-key">HR</span>
        <span class="summary-val normal">78 bpm</span>
      </div>
      <div class="summary-item">
        <span class="summary-key">SpO₂</span>
        <span class="summary-val normal">98%</span>
      </div>
      <div class="summary-item">
        <span class="summary-key">Temp</span>
        <span class="summary-val normal">98.4°F</span>
      </div>
      <div class="summary-item">
        <span class="summary-key">GRBS</span>
        <span class="summary-val abnormal" style="color:var(--warning)">142 mg/dL</span>
      </div>
    </div>

    <!-- Follow-up -->
    <div>
      <div class="summary-block-title">Follow-up</div>
      <div class="followup-picker">
        <div class="followup-opt">1 Week</div>
        <div class="followup-opt selected">4 Weeks</div>
        <div class="followup-opt">3 Months</div>
        <div class="followup-opt">SOS</div>
      </div>
      <input type="date" class="form-ctrl" style="margin-top:8px;font-size:13px;">
    </div>

    <!-- Referral -->
    <div>
      <div class="summary-block-title">Referral</div>
      <select class="form-ctrl" style="font-size:13px;">
        <option value="">— None —</option>
        <option>Cardiology</option>
        <option>Endocrinology</option>
        <option>Nephrology</option>
        <option>Ophthalmology</option>
        <option>Dermatology</option>
        <option>Neurology</option>
      </select>
      <input type="text" class="form-ctrl" style="margin-top:8px;font-size:13px;" placeholder="Referral note…">
    </div>

    <!-- Quick Actions -->
    <div>
      <div class="summary-block-title">Quick Actions</div>
      <div class="quick-actions">
        <button class="quick-btn"><i class="bi bi-printer"></i> Print Prescription</button>
        <button class="quick-btn"><i class="bi bi-whatsapp"></i> Send via WhatsApp</button>
        <button class="quick-btn"><i class="bi bi-envelope-at"></i> Email to Patient</button>
        <button class="quick-btn"><i class="bi bi-receipt"></i> Generate OP Bill</button>
        <button class="quick-btn"><i class="bi bi-flask"></i> Lab Requisition</button>
        <button class="quick-btn"><i class="bi bi-file-earmark-arrow-down"></i> Download PDF</button>
      </div>
    </div>

    <!-- Encounter Note -->
    <div>
      <div class="summary-block-title">Doctor's Note (Internal)</div>
      <textarea class="form-ctrl" rows="3" style="font-size:13px;" placeholder="Private notes, not printed on prescription…"></textarea>
    </div>

  </div>
</div>

<!-- BOTTOM BAR -->
<div class="bottom-bar">
  <div class="progress-steps">
    <div class="step done">
      <div class="step-dot"><i class="bi bi-check"></i></div>
      <span>Vitals</span>
    </div>
    <div class="step-line done"></div>
    <div class="step done">
      <div class="step-dot"><i class="bi bi-check"></i></div>
      <span>Complaints</span>
    </div>
    <div class="step-line done"></div>
    <div class="step active">
      <div class="step-dot">3</div>
      <span>Diagnosis</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
      <div class="step-dot">4</div>
      <span>Rx</span>
    </div>
  </div>

  <button class="btn-secondary-custom" onclick="saveDraft()">
    <i class="bi bi-cloud-arrow-up"></i> Save Draft
  </button>
  <button class="btn-secondary-custom">
    <i class="bi bi-arrow-left-short"></i> Previous Patient
  </button>
  <button class="btn-primary-custom" onclick="finaliseConsultation()">
    <i class="bi bi-check2-circle"></i> Finalise &amp; Print
  </button>
  <button class="btn-danger-outline">
    <i class="bi bi-x-circle"></i> Discard
  </button>
</div>

<!-- MODAL: Add Examination System -->
<div class="modal-backdrop-custom" id="addSystemModal">
  <div class="modal-box" style="width:520px;">
    <div class="modal-head">
      <div class="card-icon icon-green"><i class="bi bi-stethoscope"></i></div>
      <div class="modal-title">Add Examination System</div>
      <button class="modal-close" onclick="closeModal('addSystemModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--steel);margin-bottom:14px;">Select systems to add to the current examination. Already-active systems are shown checked.</p>
      <div class="system-picker-grid" id="systemPickerGrid"></div>
      <div>
        <label class="form-label-custom">Custom system name</label>
        <div style="display:flex;gap:8px;">
          <input type="text" class="form-ctrl" id="customSystemName" placeholder="e.g. Perianal, Lymph nodes…" style="flex:1;">
          <button class="btn-secondary-custom" onclick="addCustomSystem()"><i class="bi bi-plus"></i> Add</button>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary-custom" onclick="closeModal('addSystemModal')">Cancel</button>
      <button class="btn-primary-custom" onclick="applySystemPicker()"><i class="bi bi-check2"></i> Apply</button>
    </div>
  </div>
</div>

<!-- MODAL: Full History -->

<div class="modal-backdrop-custom" id="histModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="card-icon icon-blue"><i class="bi bi-clock-history"></i></div>
      <div class="modal-title">Patient History — Ramesh Kumar</div>
      <button class="modal-close" onclick="closeModal('histModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="row-3" style="margin-bottom:16px;">
        <div>
          <label class="form-label-custom">Past Medical History</label>
          <div class="tag-input-wrap" id="pmhWrap" onclick="focusTagInput('pmhInput')">
            <span class="tag-pill">Hypertension <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <span class="tag-pill">Type 2 DM <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <span class="tag-pill">Dyslipidemia <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <input class="tag-input-field" id="pmhInput" placeholder="Add…" onkeydown="addTag(event,'pmhWrap')">
          </div>
        </div>
        <div>
          <label class="form-label-custom">Surgical History</label>
          <div class="tag-input-wrap" id="surgWrap" onclick="focusTagInput('surgInput')">
            <span class="tag-pill" style="background:var(--steel-bg);color:var(--steel-dark);">Appendectomy 2010 <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <input class="tag-input-field" id="surgInput" placeholder="Add…" onkeydown="addTag(event,'surgWrap')">
          </div>
        </div>
        <div>
          <label class="form-label-custom">Family History</label>
          <div class="tag-input-wrap" id="famWrap" onclick="focusTagInput('famInput')">
            <span class="tag-pill" style="background:var(--steel-bg);color:var(--steel-dark);">Father: IHD <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <input class="tag-input-field" id="famInput" placeholder="Add…" onkeydown="addTag(event,'famWrap')">
          </div>
        </div>
      </div>
      <div class="row-3" style="margin-bottom:16px;">
        <div>
          <label class="form-label-custom">Drug Allergies</label>
          <div class="tag-input-wrap" id="allergyWrap">
            <span class="tag-pill tag-allergy">Penicillin <i class="bi bi-x" onclick="removeTag(this)"></i></span>
            <input class="tag-input-field" id="allergyInput" placeholder="Add…" onkeydown="addTag(event,'allergyWrap','#fdecea','#c0392b')">
          </div>
        </div>
        <div>
          <label class="form-label-custom">Smoking / Alcohol</label>
          <select class="form-ctrl" style="margin-bottom:6px;">
            <option>Non-smoker</option><option>Ex-smoker</option><option>Active smoker</option>
          </select>
          <select class="form-ctrl">
            <option>Non-drinker</option><option>Occasional</option><option>Regular</option>
          </select>
        </div>
        <div>
          <label class="form-label-custom">Occupation</label>
          <input type="text" class="form-ctrl" value="Software Engineer">
          <label class="form-label-custom" style="margin-top:8px;">Blood Group</label>
          <select class="form-ctrl">
            <option>O+</option><option>O-</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-secondary-custom" onclick="closeModal('histModal')">Close</button>
      <button class="btn-primary-custom"><i class="bi bi-save"></i> Save History</button>
    </div>
  </div>
</div>

<script>
/* ══════════════════════════════════════════════════════
   SPECIALTY-AWARE EXAMINATION ENGINE
══════════════════════════════════════════════════════ */

// Master catalogue of ALL possible examination systems
const ALL_SYSTEMS = [
  { id:'cvs',      label:'Cardiovascular',    icon:'bi-heart-pulse',        placeholder:'Heart sounds, murmurs, JVP…',         normalOpts:true  },
  { id:'resp',     label:'Respiratory',        icon:'bi-lungs',              placeholder:'Air entry, wheeze, crepts…',           normalOpts:true  },
  { id:'abd',      label:'Abdomen',            icon:'bi-body-text',          placeholder:'Tenderness, organomegaly, bowel sounds…', normalOpts:true },
  { id:'neuro',    label:'Neurological',       icon:'bi-braces',             placeholder:'Power, tone, reflexes, cranial nerves…', normalOpts:true  },
  { id:'msk',      label:'Musculoskeletal',    icon:'bi-person-arms-up',     placeholder:'ROM, swelling, tenderness, crepitus…',  normalOpts:true  },
  { id:'ent',      label:'ENT',                icon:'bi-ear',                placeholder:'Ear canals, tympanic membrane, throat…', normalOpts:true  },
  { id:'eyes',     label:'Eyes / Ophthalmology', icon:'bi-eye',             placeholder:'Visual acuity, pupils, fundoscopy…',    normalOpts:true  },
  { id:'skin',     label:'Skin / Dermatology', icon:'bi-droplet',            placeholder:'Rash, lesions, texture, colour…',       normalOpts:true  },
  { id:'breast',   label:'Breast',             icon:'bi-circle',             placeholder:'Lump, nipple discharge, skin changes…', normalOpts:true  },
  { id:'gu',       label:'Genitourinary',      icon:'bi-slash-circle',       placeholder:'Renal angle, bladder, external genitalia…', normalOpts:true },
  { id:'rectal',   label:'Rectal / Perineal',  icon:'bi-person',             placeholder:'PR exam findings…',                    normalOpts:true  },
  { id:'thyroid',  label:'Thyroid / Neck',     icon:'bi-person-fill',        placeholder:'Goitre, nodes, tracheal deviation…',   normalOpts:true  },
  { id:'lymph',    label:'Lymph Nodes',        icon:'bi-dot',                placeholder:'Cervical, axillary, inguinal…',         normalOpts:true  },
  { id:'vascular', label:'Vascular / Peripheral', icon:'bi-bezier',         placeholder:'Pulses, capillary refill, oedema…',     normalOpts:true  },

  // Ophthalmology-specific
  { id:'va',       label:'Visual Acuity',      icon:'bi-eye',                placeholder:'RE: 6/6, LE: 6/9 (Snellen)…',          normalOpts:false, freeText:true },
  { id:'iop',      label:'IOP',                icon:'bi-circle-half',        placeholder:'RE: 14 mmHg, LE: 16 mmHg…',             normalOpts:false, freeText:true },
  { id:'fundus',   label:'Fundoscopy',         icon:'bi-search',             placeholder:'Disc, macula, vessels, periphery…',     normalOpts:true  },
  { id:'slit',     label:'Slit Lamp',          icon:'bi-lamp',               placeholder:'Cornea, AC, lens, vitreous…',           normalOpts:true  },
  { id:'pupils',   label:'Pupils / RAPD',      icon:'bi-record-circle',      placeholder:'Size, reaction, RAPD present/absent…',  normalOpts:true  },
  { id:'ocular_motility', label:'Ocular Motility', icon:'bi-arrows-move',   placeholder:'Full, restricted in direction…',         normalOpts:true  },

  // Dental
  { id:'dental_extra', label:'Extra-oral',     icon:'bi-person-face',        placeholder:'Facial symmetry, TMJ, LN, lips…',       normalOpts:true  },
  { id:'dental_intra', label:'Intra-oral',     icon:'bi-mouth',              placeholder:'Mucosa, gingiva, tongue, floor of mouth…', normalOpts:true },
  { id:'dental_teeth', label:'Dentition',      icon:'bi-grid',               placeholder:'Teeth chart, caries, mobility, occlusion…', normalOpts:false, freeText:true },
  { id:'perio',    label:'Periodontal',        icon:'bi-bar-chart-steps',    placeholder:'BPE scores, probing depths, BOP…',      normalOpts:false, freeText:true },
  { id:'occlusion',label:'Occlusion',         icon:'bi-intersect',          placeholder:'Angle class, overjet, overbite, crossbite…', normalOpts:false, freeText:true },

  // Psychiatry / Psychology
  { id:'mse_appear',  label:'Appearance & Behaviour', icon:'bi-person-check', placeholder:'Dress, eye contact, psychomotor activity…', normalOpts:false, freeText:true },
  { id:'mse_speech',  label:'Speech',          icon:'bi-chat-quote',         placeholder:'Rate, rhythm, tone, volume…',            normalOpts:false, freeText:true },
  { id:'mse_mood',    label:'Mood & Affect',   icon:'bi-emoji-neutral',      placeholder:'Subjective mood, observed affect, congruence…', normalOpts:false, freeText:true },
  { id:'mse_thought', label:'Thought',         icon:'bi-diagram-3',          placeholder:'Form, content, thought disorder, delusions…', normalOpts:false, freeText:true },
  { id:'mse_percep',  label:'Perception',      icon:'bi-soundwave',          placeholder:'Hallucinations — auditory, visual, tactile…', normalOpts:false, freeText:true },
  { id:'mse_cognition', label:'Cognition',     icon:'bi-cpu',                placeholder:'Orientation, attention, memory, insight…',  normalOpts:false, freeText:true },
  { id:'mse_insight', label:'Insight & Judgement', icon:'bi-lightbulb',     placeholder:'Awareness of illness, treatment compliance…', normalOpts:false, freeText:true },
  { id:'risk_assess',  label:'Risk Assessment', icon:'bi-shield-exclamation', placeholder:'Suicidality, harm to others, vulnerability…', normalOpts:false, freeText:true },

  // Paediatrics
  { id:'growth',   label:'Growth & Development', icon:'bi-rulers',          placeholder:'Height, weight centiles, milestones…',   normalOpts:false, freeText:true },
  { id:'fontanelle', label:'Fontanelle',        icon:'bi-circle-dotted',     placeholder:'Anterior fontanelle — open/closed, bulging…', normalOpts:true },

  // Orthopaedics
  { id:'gait',     label:'Gait',               icon:'bi-person-walking',     placeholder:'Antalgic, Trendelenburg, ataxic…',       normalOpts:false, freeText:true },
  { id:'spine',    label:'Spine',              icon:'bi-arrow-up',           placeholder:'Scoliosis, kyphosis, ROM, tenderness…',  normalOpts:true  },
  { id:'upper_limb', label:'Upper Limb',       icon:'bi-hand-index',         placeholder:'Shoulder, elbow, wrist, hand…',          normalOpts:true  },
  { id:'lower_limb', label:'Lower Limb',       icon:'bi-person-standing',    placeholder:'Hip, knee, ankle, foot…',                normalOpts:true  },

  // Gynaecology / Obstetrics
  { id:'pv',       label:'Per Vaginal',        icon:'bi-gender-female',      placeholder:'Cervix, uterus, adnexa, discharge…',     normalOpts:true  },
  { id:'obstetric',label:'Obstetric',          icon:'bi-heart',              placeholder:'Fundal height, presentation, FHR…',      normalOpts:false, freeText:true },
];

// Specialty presets — which system IDs are shown by default
const SPECIALTIES = [
  {
    id: 'general', label: 'General Medicine', showGeneral: true,
    systems: ['cvs','resp','abd','neuro','skin','lymph'],
    hint: 'Standard systemic examination'
  },
  {
    id: 'cardiology', label: 'Cardiology', showGeneral: true,
    systems: ['cvs','resp','vascular','lymph','abd'],
    hint: 'Focused cardiac & vascular exam'
  },
  {
    id: 'ortho', label: 'Orthopaedics', showGeneral: false,
    systems: ['gait','spine','upper_limb','lower_limb','msk','vascular'],
    hint: 'Locomotor & neurovascular exam'
  },
  {
    id: 'ophthalmology', label: 'Ophthalmology', showGeneral: false,
    systems: ['va','iop','pupils','ocular_motility','slit','fundus'],
    hint: 'Ophthalmic examination'
  },
  {
    id: 'dental', label: 'Dentistry', showGeneral: false,
    systems: ['dental_extra','dental_intra','dental_teeth','perio','occlusion'],
    hint: 'Oro-dental examination'
  },
  {
    id: 'psychiatry', label: 'Psychiatry / Psychology', showGeneral: false,
    systems: ['mse_appear','mse_speech','mse_mood','mse_thought','mse_percep','mse_cognition','mse_insight','risk_assess'],
    hint: 'Mental State Examination (MSE)'
  },
  {
    id: 'ent', label: 'ENT', showGeneral: true,
    systems: ['ent','thyroid','lymph','neuro'],
    hint: 'Ear, Nose, Throat & Head/Neck'
  },
  {
    id: 'gynaecology', label: 'Gynaecology', showGeneral: true,
    systems: ['abd','pv','breast','lymph'],
    hint: 'Gynaecological & obstetric exam'
  },
  {
    id: 'paediatrics', label: 'Paediatrics', showGeneral: true,
    systems: ['growth','fontanelle','cvs','resp','abd','neuro'],
    hint: 'Paediatric systemic examination'
  },
  {
    id: 'dermatology', label: 'Dermatology', showGeneral: true,
    systems: ['skin','lymph','msk'],
    hint: 'Dermatological & systemic exam'
  },
];

let currentSpecialty = SPECIALTIES[0];
let activeSystemIds = [...currentSpecialty.systems]; // mutable per session
let pickerSelected  = [];

/* ── Build specialty chips ── */
function buildSpecialtyChips() {
  const wrap = document.getElementById('specialtyChips');
  wrap.innerHTML = '';
  SPECIALTIES.forEach(sp => {
    const btn = document.createElement('button');
    btn.className = 'spec-chip' + (sp.id === currentSpecialty.id ? ' active' : '');
    btn.textContent = sp.label;
    btn.onclick = () => switchSpecialty(sp.id);
    wrap.appendChild(btn);
  });
}

/* ── Switch specialty ── */
function switchSpecialty(id) {
  currentSpecialty = SPECIALTIES.find(s => s.id === id);
  activeSystemIds  = [...currentSpecialty.systems];
  document.getElementById('examSpecialtyLabel').textContent = currentSpecialty.label;

  // Show/hide general appearance row
  document.getElementById('genAppearanceRow').style.display =
    currentSpecialty.showGeneral ? '' : 'none';

  // Rebuild chips
  document.querySelectorAll('.spec-chip').forEach(c => {
    c.classList.toggle('active', c.textContent === currentSpecialty.label);
  });

  renderExamSystems();
}

/* ── Render exam system cards from activeSystemIds ── */
function renderExamSystems() {
  const grid = document.getElementById('examSystemsGrid');
  grid.innerHTML = '';
  activeSystemIds.forEach(sid => {
    const sys = ALL_SYSTEMS.find(s => s.id === sid);
    if (!sys) return;
    const uid = 'sys_' + sys.id;
    const card = document.createElement('div');
    card.className = 'exam-system';
    card.dataset.sysId = sys.id;

    let innerHTML = `
      <div class="exam-system-header">
        <i class="bi ${sys.icon}"></i> ${sys.label}
        <button class="exam-system-remove" title="Remove" onclick="removeExamSystem('${sys.id}')">
          <i class="bi bi-x"></i>
        </button>
      </div>
      <div class="exam-system-body">`;

    if (sys.normalOpts) {
      innerHTML += `
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="${uid}" value="normal" checked> Normal</label>
          <label class="radio-opt"><input type="radio" name="${uid}" value="abnormal"> Abnormal</label>
        </div>`;
    }
    innerHTML += `<input class="exam-note-inline" type="text" placeholder="${sys.placeholder}">`;
    innerHTML += `</div>`;

    card.innerHTML = innerHTML;
    grid.appendChild(card);
  });
}

/* ── Remove a system inline ── */
function removeExamSystem(id) {
  activeSystemIds = activeSystemIds.filter(s => s !== id);
  renderExamSystems();
}

/* ── Open add-system modal ── */
function openAddSystemModal() {
  pickerSelected = [...activeSystemIds];
  const grid = document.getElementById('systemPickerGrid');
  grid.innerHTML = '';
  ALL_SYSTEMS.forEach(sys => {
    const isActive = pickerSelected.includes(sys.id);
    const item = document.createElement('div');
    item.className = 'sys-pick-item' + (isActive ? ' selected' : '');
    item.dataset.sysId = sys.id;
    item.innerHTML = `<i class="bi ${sys.icon}"></i><span style="flex:1;">${sys.label}</span><i class="bi bi-check2 sys-check"></i>`;
    item.onclick = () => toggleSystemPick(item, sys.id);
    grid.appendChild(item);
  });
  openModal('addSystemModal');
}

function toggleSystemPick(el, id) {
  el.classList.toggle('selected');
  if (el.classList.contains('selected')) {
    if (!pickerSelected.includes(id)) pickerSelected.push(id);
  } else {
    pickerSelected = pickerSelected.filter(s => s !== id);
  }
}

function addCustomSystem() {
  const input = document.getElementById('customSystemName');
  const name  = input.value.trim();
  if (!name) return;
  const id = 'custom_' + Date.now();
  ALL_SYSTEMS.push({ id, label: name, icon: 'bi-plus-square', placeholder: 'Findings…', normalOpts: true });
  pickerSelected.push(id);
  // Refresh picker grid
  openAddSystemModal();
  input.value = '';
}

function applySystemPicker() {
  activeSystemIds = [...pickerSelected];
  renderExamSystems();
  closeModal('addSystemModal');
}

/* ── Init on load ── */
document.addEventListener('DOMContentLoaded', () => {
  renderExamSystems();
  // Seed diagnosis rows
  createDiagRow('Essential Hypertension', 'I10', 'Primary', 'Chronic');
  createDiagRow('Type 2 Diabetes Mellitus', 'E11.9', 'Secondary', 'Chronic');
  // Seed prescription rows
  addRxRow({ name:'Amlodipine 5mg', pack_size_label:'5 mg · 15 Tablets/Strip', form:'Tablet', route:'Oral',
    manufacturer:'Sun Pharma', use:'Hypertension — calcium channel blocker.',
    therapeutic_class:'Antihypertensive — CCB', chemical_class:'Dihydropyridine CCB',
    side_effects:['Peripheral oedema','Flushing','Headache'],
    alternatives:['Stamlo 5','Amlopres 5'], dur:'30 days' });
  addRxRow({ name:'Glycomet 500', pack_size_label:'500 mg · 20 Tablets/Strip', form:'Tablet', route:'Oral',
    manufacturer:'USV Pvt. Ltd.', use:'Type 2 Diabetes Mellitus.',
    therapeutic_class:'Antidiabetic — Biguanide', chemical_class:'Biguanide',
    side_effects:['Nausea','Diarrhoea','Metallic taste'],
    alternatives:['Glyciphage 500','Obimet 500'], dur:'30 days' });
});
</script>

<script>
  /* ── COLLAPSE / EXPAND CARDS ── */
  function toggleCard(id) {
    const sec  = document.getElementById(id);
    const body = sec.querySelector('.card-body');
    const hdr  = sec.querySelector('.card-header');
    const isOpen = sec.dataset.open !== 'false'; // default open
    if (isOpen) {
      body.style.display = 'none';
      hdr.classList.add('collapsed');
      sec.dataset.open = 'false';
    } else {
      body.style.display = '';
      hdr.classList.remove('collapsed');
      sec.dataset.open = 'true';
    }
  }

  /* ── TOGGLE EXTRA SECTIONS ── */
  function toggleSection(id, btn) {
    const el = document.getElementById(id);
    const isHidden = el.dataset.visible !== 'true';
    if (isHidden) {
      el.style.display = '';
      el.dataset.visible = 'true';
      if (btn) { const i = btn.querySelector('i'); if (i) i.className = 'bi bi-dash-circle'; }
    } else {
      el.style.display = 'none';
      el.dataset.visible = 'false';
      if (btn) { const i = btn.querySelector('i'); if (i) i.className = 'bi bi-plus-circle'; }
    }
  }

  function toggleVitalsExtra(btn) {
    const extra = document.getElementById('vitalsExtra');
    if (extra.classList.contains('visible')) {
      extra.classList.remove('visible');
      btn.innerHTML = '<i class="bi bi-plus-circle"></i> More vitals (Weight, Height, RR, GRBS…)';
    } else {
      extra.classList.add('visible');
      btn.innerHTML = '<i class="bi bi-dash-circle"></i> Hide extra vitals';
    }
  }

  /* ── TAG INPUT ── */
  function focusTagInput(id) {
    const el = document.getElementById(id);
    if (el) el.focus();
  }

  function addTag(e, wrapId, bg, color) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const input = e.target;
      const text = input.value.trim();
      if (!text) return;
      const wrap = document.getElementById(wrapId);
      const pill = document.createElement('span');
      pill.className = 'tag-pill';
      if (bg)    pill.style.background = bg;
      if (color) pill.style.color      = color;
      pill.innerHTML = `${text} <i class="bi bi-x" onclick="removeTag(this)"></i>`;
      wrap.insertBefore(pill, input);
      input.value = '';
    }
  }

  function removeTag(btn) {
    btn.parentElement.remove();
  }

  /* ══════════════════════════════════════════════════════
     ICD-10 TYPEAHEAD ENGINE
     Uses NLM ClinicalTables free public API
     https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search
  ══════════════════════════════════════════════════════ */

  let diagCount = 0;
  let icdTimers = {};
  let icdFocusIdx = {}; // keyboard nav per row

  function createDiagRow(prefillName, prefillCode, prefillType, prefillStatus) {
    diagCount++;
    const id = diagCount;
    const row = document.createElement('div');
    row.className = 'diag-row';
    row.id = `diagRow${id}`;
    row.innerHTML = `
      <div class="icd-wrap" id="icdWrap${id}">
        <input
          class="icd-input"
          id="icdInput${id}"
          type="text"
          autocomplete="off"
          placeholder="Search diagnosis or ICD-10 code…"
          value="${prefillName || ''}"
          oninput="onIcdInput(${id})"
          onkeydown="onIcdKey(event,${id})"
          onblur="onIcdBlur(${id})"
          onfocus="if(this.value.length>1) openDrop(${id})"
        >
        <div class="icd-dropdown" id="icdDrop${id}"></div>
      </div>
      <div class="icd-code-badge ${prefillCode ? '' : 'empty'}" id="icdBadge${id}">
        ${prefillCode || 'ICD-10'}
      </div>
      <select class="diag-select" id="diagType${id}" style="width:106px;">
        <option ${prefillType==='Primary'    ?'selected':''}>Primary</option>
        <option ${prefillType==='Secondary'  ?'selected':''}>Secondary</option>
        <option ${prefillType==='Provisional'?'selected':''}>Provisional</option>
        <option ${prefillType==='Differential'?'selected':''}>Differential</option>
      </select>
      <select class="diag-select" id="diagStatus${id}" style="width:90px;">
        <option ${prefillStatus==='Active'  ?'selected':''}>Active</option>
        <option ${prefillStatus==='Chronic' ?'selected':''}>Chronic</option>
        <option ${prefillStatus==='Resolved'?'selected':''}>Resolved</option>
      </select>
      <button class="diag-del" onclick="document.getElementById('diagRow${id}').remove()" title="Remove">
        <i class="bi bi-trash3"></i>
      </button>`;
    document.getElementById('diagRows').appendChild(row);
  }

  function onIcdInput(id) {
    const val = document.getElementById(`icdInput${id}`).value.trim();
    // Clear badge
    setBadge(id, '', false);
    clearTimeout(icdTimers[id]);
    if (val.length < 2) { closeDrop(id); return; }
    showLoading(id);
    icdTimers[id] = setTimeout(() => fetchIcd(id, val), 300);
  }

  async function fetchIcd(id, query) {
    const url = `https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?sf=code,name&terms=${encodeURIComponent(query)}&maxList=10`;
    try {
      const r   = await fetch(url);
      const data = await r.json();
      // data = [totalCount, [codes], null, [[code, name], ...]]
      const results = data[3] || [];
      renderDrop(id, results, query);
    } catch(e) {
      renderDropError(id);
    }
  }

  function renderDrop(id, results, query) {
    const drop = document.getElementById(`icdDrop${id}`);
    icdFocusIdx[id] = -1;
    if (!results.length) {
      drop.innerHTML = `<div class="icd-empty">No results for "<strong>${escHtml(query)}</strong>"</div>`;
    } else {
      drop.innerHTML = results.map((r, i) => {
        const code = r[0], name = r[1];
        const hl = highlightMatch(name, query);
        return `<div class="icd-item" data-idx="${i}" data-code="${escHtml(code)}" data-name="${escHtml(name)}"
          onmousedown="pickIcd(event,${id},'${escHtml(code)}','${escHtml(name)}')"
          onmouseover="hoverItem(${id},${i})">
          <span class="icd-code">${escHtml(code)}</span>
          <span class="icd-name">${hl}</span>
        </div>`;
      }).join('');
    }
    openDrop(id);
  }

  function renderDropError(id) {
    document.getElementById(`icdDrop${id}`).innerHTML =
      `<div class="icd-empty" style="color:var(--danger)">Could not reach ICD-10 service</div>`;
    openDrop(id);
  }

  function showLoading(id) {
    document.getElementById(`icdDrop${id}`).innerHTML =
      `<div class="icd-loading"><i class="bi bi-arrow-repeat"></i> Searching…</div>`;
    openDrop(id);
  }

  function pickIcd(e, id, code, name) {
    e.preventDefault();
    document.getElementById(`icdInput${id}`).value = name;
    setBadge(id, code, true);
    closeDrop(id);
  }

  function setBadge(id, code, filled) {
    const b = document.getElementById(`icdBadge${id}`);
    b.textContent = filled ? code : 'ICD-10';
    b.className   = 'icd-code-badge' + (filled ? '' : ' empty');
  }

  function openDrop(id) {
    document.getElementById(`icdDrop${id}`).classList.add('open');
  }
  function closeDrop(id) {
    document.getElementById(`icdDrop${id}`).classList.remove('open');
    icdFocusIdx[id] = -1;
  }

  function onIcdBlur(id) {
    setTimeout(() => closeDrop(id), 180);
  }

  function onIcdKey(e, id) {
    const drop  = document.getElementById(`icdDrop${id}`);
    const items = drop.querySelectorAll('.icd-item');
    if (!drop.classList.contains('open') || !items.length) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      icdFocusIdx[id] = Math.min((icdFocusIdx[id]||0)+1, items.length-1);
      updateFocus(id, items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      icdFocusIdx[id] = Math.max((icdFocusIdx[id]||0)-1, 0);
      updateFocus(id, items);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const fi = icdFocusIdx[id];
      if (fi >= 0 && items[fi]) {
        const el = items[fi];
        pickIcd(e, id, el.dataset.code, el.dataset.name);
      }
    } else if (e.key === 'Escape') {
      closeDrop(id);
    }
  }

  function hoverItem(id, idx) {
    icdFocusIdx[id] = idx;
    const items = document.getElementById(`icdDrop${id}`).querySelectorAll('.icd-item');
    updateFocus(id, items);
  }

  function updateFocus(id, items) {
    items.forEach((el, i) => el.classList.toggle('focused', i === icdFocusIdx[id]));
    const fi = icdFocusIdx[id];
    if (fi >= 0 && items[fi]) items[fi].scrollIntoView({ block:'nearest' });
  }

  function highlightMatch(text, query) {
    const safe = escHtml(text);
    const safeQ = query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
    return safe.replace(new RegExp(`(${safeQ})`, 'gi'), '<em>$1</em>');
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  function addDiagRow() {
    createDiagRow();
  }

  /* ══════════════════════════════════════════════════════
     MEDICINE TYPEAHEAD + DETAIL DRAWER ENGINE
     Replace MEDICINE_DB with your own database call.
     Fields used: name, pack_size_label, form, route,
       manufacturer, use, therapeutic_class, chemical_class,
       side_effects[], alternatives[]
  ══════════════════════════════════════════════════════ */

  // ── Sample medicine DB (wire to your own backend) ──
  const MEDICINE_DB = [
    {
      name:'Glycomet 500',
      pack_size_label:'500 mg · 20 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'USV Pvt. Ltd.',
      use:'Type 2 Diabetes Mellitus — lowers blood glucose by reducing hepatic glucose production.',
      therapeutic_class:'Antidiabetic — Biguanide',
      chemical_class:'Biguanide',
      side_effects:['Nausea','Diarrhoea','Lactic acidosis (rare)','Metallic taste'],
      alternatives:['Glyciphage 500','Obimet 500','Gluconorm 500']
    },
    {
      name:'Glycomet 1000',
      pack_size_label:'1000 mg · 10 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'USV Pvt. Ltd.',
      use:'Type 2 Diabetes — higher dose for inadequate glycaemic control.',
      therapeutic_class:'Antidiabetic — Biguanide',
      chemical_class:'Biguanide',
      side_effects:['Nausea','Diarrhoea','Lactic acidosis (rare)'],
      alternatives:['Glyciphage 1000','Bigomet 1000']
    },
    {
      name:'Glycomet GP 1',
      pack_size_label:'Metformin 500mg + Glipizide 1mg · 10 Tablets',
      form:'Tablet', route:'Oral',
      manufacturer:'USV Pvt. Ltd.',
      use:'Combination antidiabetic for T2DM.',
      therapeutic_class:'Antidiabetic — Biguanide + Sulfonylurea',
      chemical_class:'Biguanide / Sulfonylurea',
      side_effects:['Hypoglycaemia','Nausea','Weight gain'],
      alternatives:['Amaryl M 1','Glucovance']
    },
    {
      name:'Amlodipine 5mg',
      pack_size_label:'5 mg · 15 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'Sun Pharma',
      use:'Hypertension, Stable Angina — calcium channel blocker.',
      therapeutic_class:'Antihypertensive — CCB',
      chemical_class:'Dihydropyridine CCB',
      side_effects:['Peripheral oedema','Flushing','Headache','Palpitations'],
      alternatives:['Stamlo 5','Amlopres 5','Norvasc 5']
    },
    {
      name:'Amlodipine 10mg',
      pack_size_label:'10 mg · 15 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'Sun Pharma',
      use:'Hypertension — higher dose when 5 mg is insufficient.',
      therapeutic_class:'Antihypertensive — CCB',
      chemical_class:'Dihydropyridine CCB',
      side_effects:['Peripheral oedema','Flushing','Headache'],
      alternatives:['Stamlo 10','Amlopres 10']
    },
    {
      name:'Zorceft T 1000mg/125mg Injection',
      pack_size_label:'Ceftriaxone 1g + Tazobactam 125mg · Vial',
      form:'Injection', route:'IV / IM',
      manufacturer:'Zordis Pharma',
      use:'Severe bacterial infections — broad-spectrum beta-lactam + beta-lactamase inhibitor.',
      therapeutic_class:'Antibiotic — 3rd Gen Cephalosporin + BLI',
      chemical_class:'Cephalosporin / Tazobactam',
      side_effects:['Injection site pain','Diarrhoea','Rash','Eosinophilia','Elevated LFTs'],
      alternatives:['Monocef-SB 1g','Zifi-TZ 1g/125mg','Cefzone-T']
    },
    {
      name:'Atorvastatin 10mg',
      pack_size_label:'10 mg · 15 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'Cipla',
      use:'Hypercholesterolaemia, CVD risk reduction — HMG-CoA reductase inhibitor.',
      therapeutic_class:'Antilipidaemic — Statin',
      chemical_class:'HMG-CoA Reductase Inhibitor',
      side_effects:['Myalgia','Elevated CK','Hepatotoxicity (rare)','Headache'],
      alternatives:['Lipitor 10','Storvas 10','Tonact 10']
    },
    {
      name:'Atorvastatin 20mg',
      pack_size_label:'20 mg · 15 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'Cipla',
      use:'Hypercholesterolaemia, post-ACS — higher intensity statin therapy.',
      therapeutic_class:'Antilipidaemic — Statin',
      chemical_class:'HMG-CoA Reductase Inhibitor',
      side_effects:['Myalgia','Elevated CK','Hepatotoxicity (rare)'],
      alternatives:['Lipitor 20','Storvas 20']
    },
    {
      name:'Pantoprazole 40mg',
      pack_size_label:'40 mg · 15 Tablets/Strip',
      form:'Tablet', route:'Oral',
      manufacturer:'Alkem Labs',
      use:'GERD, peptic ulcer, Zollinger–Ellison — proton pump inhibitor.',
      therapeutic_class:'Proton Pump Inhibitor',
      chemical_class:'Benzimidazole PPI',
      side_effects:['Headache','Diarrhoea','Hypomagnesaemia (long-term)','C. diff (rare)'],
      alternatives:['Pan 40','Pantocid 40','Rantac 40']
    },
    {
      name:'Azithromycin 500mg',
      pack_size_label:'500 mg · 3 Tablets/Pack',
      form:'Tablet', route:'Oral',
      manufacturer:'Abbott India',
      use:'Community-acquired pneumonia, pharyngitis, skin infections.',
      therapeutic_class:'Antibiotic — Macrolide',
      chemical_class:'Macrolide',
      side_effects:['Nausea','Abdominal pain','QT prolongation (rare)','Hepatotoxicity (rare)'],
      alternatives:['Zithromax 500','Azee 500','Azifast 500']
    },
  ];

  let rxCount  = 0;
  let rxTimers = {};
  let rxFocusIdx = {};

  function addRxRow(prefill) {
    rxCount++;
    const id = rxCount;
    const entry = document.createElement('div');
    entry.className = 'rx-entry';
    entry.id = `rxEntry${id}`;

    entry.innerHTML = `
      <div class="rx-row">
        <!-- Drug typeahead -->
        <div class="rx-drug-cell" id="rxDrugCell${id}">
          <input class="rx-drug-input" id="rxDrugInput${id}" type="text" autocomplete="off"
            placeholder="Type medicine name…"
            value="${prefill ? prefill.name : ''}"
            oninput="onRxInput(${id})"
            onkeydown="onRxKey(event,${id})"
            onblur="onRxBlur(${id})"
            onfocus="if(this.value.length>1) openRxDrop(${id})">
          <div class="rx-drug-drop" id="rxDrop${id}"></div>
        </div>

        <!-- Pack label (populated on selection) -->
        <div id="rxPack${id}" class="${prefill ? 'rx-pack-label' : 'rx-pack-empty'}">
          ${prefill ? prefill.pack_size_label : 'Select medicine first'}
        </div>

        <!-- Frequency -->
        <select class="rx-sel" id="rxFreq${id}">
          <option>OD — Once daily</option>
          <option>BD — Twice daily</option>
          <option>TDS — Thrice daily</option>
          <option>QID — Four times</option>
          <option>SOS — As needed</option>
          <option>Stat — Immediately</option>
          <option>Weekly</option>
          <option>Monthly</option>
        </select>

        <!-- Duration -->
        <input class="rx-dur-input" id="rxDur${id}" type="text" placeholder="e.g. 30 days"
          value="${prefill ? prefill.dur || '' : ''}">

        <!-- Instructions -->
        <select class="rx-sel" id="rxInst${id}">
          <option value="">— Timing —</option>
          <option>Before food</option>
          <option>With food</option>
          <option>After food</option>
          <option>Empty stomach</option>
          <option>At bedtime</option>
          <option>In the morning</option>
        </select>

        <!-- Actions -->
        <div class="rx-actions-cell">
          <button class="rx-info-btn" id="rxInfoBtn${id}" title="Drug info"
            onclick="toggleRxDrawer(${id})">
            <i class="bi bi-info-circle"></i>
          </button>
          <button class="rx-del-btn" title="Remove" onclick="document.getElementById('rxEntry${id}').remove()">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </div>

      <!-- Detail drawer -->
      <div class="rx-detail-drawer" id="rxDrawer${id}">
        <div style="color:var(--steel-light);font-size:13px;font-style:italic;">Select a medicine to see details.</div>
      </div>`;

    document.getElementById('rxRows').appendChild(entry);

    if (prefill) {
      applyRxSelection(id, prefill);
    }
  }

  /* ── Search ── */
  function onRxInput(id) {
    const val = document.getElementById(`rxDrugInput${id}`).value.trim();
    // clear selection state
    document.getElementById(`rxDrugInput${id}`).classList.remove('selected-drug');
    document.getElementById(`rxPack${id}`).className = 'rx-pack-empty';
    document.getElementById(`rxPack${id}`).textContent = 'Select medicine first';
    clearTimeout(rxTimers[id]);
    if (val.length < 2) { closeRxDrop(id); return; }
    rxTimers[id] = setTimeout(() => searchMeds(id, val), 200);
  }

  function searchMeds(id, q) {
    const ql = q.toLowerCase();
    const results = MEDICINE_DB.filter(m =>
      m.name.toLowerCase().includes(ql) ||
      m.therapeutic_class.toLowerCase().includes(ql) ||
      m.chemical_class.toLowerCase().includes(ql)
    ).slice(0, 8);
    renderRxDrop(id, results, q);
  }

  function renderRxDrop(id, results, q) {
    const drop = document.getElementById(`rxDrop${id}`);
    rxFocusIdx[id] = -1;
    if (!results.length) {
      drop.innerHTML = `<div style="padding:10px 12px;font-size:12.5px;color:var(--steel-light);">No medicines found for "<strong>${escHtml(q)}</strong>"</div>`;
    } else {
      drop.innerHTML = results.map((m, i) => {
        const hl = highlightMatch(m.name, q);
        return `<div class="rx-drug-item" data-idx="${i}"
          onmousedown="pickRxMed(event,${id},${i})"
          onmouseover="rxHover(${id},${i})">
          <div class="rx-drug-name">${hl}</div>
          <div class="rx-drug-meta">${escHtml(m.pack_size_label)} &nbsp;·&nbsp; ${escHtml(m.form)}</div>
          <div class="rx-drug-mfr"><i class="bi bi-building" style="font-size:10px;"></i> ${escHtml(m.manufacturer)}</div>
        </div>`;
      }).join('');
    }
    // stash results for keyboard pick
    drop._results = results;
    openRxDrop(id);
  }

  function pickRxMed(e, id, idx) {
    e.preventDefault();
    const drop = document.getElementById(`rxDrop${id}`);
    const med  = drop._results[idx];
    if (!med) return;
    applyRxSelection(id, med);
    closeRxDrop(id);
  }

  function applyRxSelection(id, med) {
    const input = document.getElementById(`rxDrugInput${id}`);
    input.value = med.name;
    input.classList.add('selected-drug');

    const pack = document.getElementById(`rxPack${id}`);
    pack.className = 'rx-pack-label';
    pack.textContent = med.pack_size_label;

    // Populate drawer
    populateDrawer(id, med);
  }

  function populateDrawer(id, med) {
    const drawer = document.getElementById(`rxDrawer${id}`);
    const sideEffectsHtml = (med.side_effects||[])
      .map(s => `<span class="drawer-tag warn">${escHtml(s)}</span>`).join('');
    const altsHtml = (med.alternatives||[])
      .map(a => `<button class="alt-pill" onclick="swapAlt(${id},'${escHtml(a)}')" title="Switch to ${escHtml(a)}">
        <i class="bi bi-arrow-left-right"></i>${escHtml(a)}
      </button>`).join('');

    drawer.innerHTML = `
      <div class="drawer-grid">
        <div>
          <div class="drawer-block-title">Indication / Use</div>
          <div class="drawer-val">${escHtml(med.use)}</div>
        </div>
        <div>
          <div class="drawer-block-title">Therapeutic Class</div>
          <div class="drawer-val"><span class="drawer-tag blue">${escHtml(med.therapeutic_class)}</span></div>
          <div class="drawer-block-title" style="margin-top:10px;">Chemical Class</div>
          <div class="drawer-val muted">${escHtml(med.chemical_class)}</div>
        </div>
        <div>
          <div class="drawer-block-title">Manufacturer</div>
          <div class="drawer-val">${escHtml(med.manufacturer)}</div>
          <div class="drawer-block-title" style="margin-top:10px;">Form · Route</div>
          <div class="drawer-val muted">${escHtml(med.form)} &nbsp;·&nbsp; ${escHtml(med.route)}</div>
        </div>
      </div>
      <hr class="drawer-divider">
      <div style="margin-bottom:8px;">
        <div class="drawer-block-title">Common Side Effects</div>
        <div style="margin-top:4px;">${sideEffectsHtml}</div>
      </div>
      ${altsHtml ? `<hr class="drawer-divider">
      <div>
        <div class="drawer-block-title" style="margin-bottom:6px;">Alternative Brands — click to switch</div>
        <div class="alternatives-row">${altsHtml}</div>
      </div>` : ''}`;
  }

  function swapAlt(id, altName) {
    const input = document.getElementById(`rxDrugInput${id}`);
    // Find alt in DB or just set the name
    const med = MEDICINE_DB.find(m => m.name === altName);
    if (med) {
      applyRxSelection(id, med);
    } else {
      input.value = altName;
      input.classList.add('selected-drug');
      document.getElementById(`rxPack${id}`).className = 'rx-pack-empty';
      document.getElementById(`rxPack${id}`).textContent = altName;
    }
  }

  function toggleRxDrawer(id) {
    const drawer = document.getElementById(`rxDrawer${id}`);
    const btn    = document.getElementById(`rxInfoBtn${id}`);
    const isOpen = drawer.classList.contains('open');
    drawer.classList.toggle('open', !isOpen);
    btn.classList.toggle('active', !isOpen);
  }

  function openRxDrop(id)  { document.getElementById(`rxDrop${id}`).classList.add('open'); }
  function closeRxDrop(id) { document.getElementById(`rxDrop${id}`).classList.remove('open'); rxFocusIdx[id]=-1; }
  function onRxBlur(id)    { setTimeout(() => closeRxDrop(id), 180); }

  function onRxKey(e, id) {
    const drop  = document.getElementById(`rxDrop${id}`);
    const items = drop.querySelectorAll('.rx-drug-item');
    if (!drop.classList.contains('open') || !items.length) return;
    if (e.key==='ArrowDown') { e.preventDefault(); rxFocusIdx[id]=Math.min((rxFocusIdx[id]||0)+1,items.length-1); rxUpdateFocus(id,items); }
    else if (e.key==='ArrowUp') { e.preventDefault(); rxFocusIdx[id]=Math.max((rxFocusIdx[id]||0)-1,0); rxUpdateFocus(id,items); }
    else if (e.key==='Enter') { e.preventDefault(); const fi=rxFocusIdx[id]; if(fi>=0&&items[fi]) pickRxMed(e,id,fi); }
    else if (e.key==='Escape') closeRxDrop(id);
  }

  function rxHover(id, idx) { rxFocusIdx[id]=idx; const items=document.getElementById(`rxDrop${id}`).querySelectorAll('.rx-drug-item'); rxUpdateFocus(id,items); }
  function rxUpdateFocus(id,items) { items.forEach((el,i)=>el.classList.toggle('focused',i===rxFocusIdx[id])); const fi=rxFocusIdx[id]; if(fi>=0&&items[fi]) items[fi].scrollIntoView({block:'nearest'}); }

  function removeRxRow(btn) { btn.closest('.rx-entry').remove(); }

  /* ── VITALS CHECK ── */
  function checkVitals() {
    const sys  = +document.getElementById('bpSys').value;
    const dia  = +document.getElementById('bpDia').value;
    const hr   = +document.getElementById('hr').value;
    const spo2 = +document.getElementById('spo2').value;
    const temp = +document.getElementById('temp').value;

    setDot('bpDot',  sys > 140 || dia > 90  ? 'alert' : (sys > 130 || dia > 85 ? 'warn' : ''));
    setDot('hrDot',  hr < 60 || hr > 100    ? 'alert' : '');
    setDot('spoDot', spo2 < 95              ? 'alert' : (spo2 < 97 ? 'warn' : ''));
    setDot('tempDot',temp > 99.5            ? 'alert' : (temp > 99 ? 'warn' : ''));
  }

  function setDot(id, cls) {
    const d = document.getElementById(id);
    d.className = 'vital-status' + (cls ? ' ' + cls : '');
  }

  /* ── HISTORY SIDEBAR ── */
  function loadHistory(item) {
    document.querySelectorAll('.history-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
  }

  /* ── MODAL ── */
  function openModal(id) {
    document.getElementById(id).classList.add('visible');
  }
  function closeModal(id) {
    document.getElementById(id).classList.remove('visible');
  }
  document.querySelectorAll('.modal-backdrop-custom').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('visible'); });
  });

  /* ── SAVE / FINALISE ── */
  function saveDraft() {
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2"></i> Saved!';
    btn.style.color = 'var(--success)';
    btn.style.borderColor = 'var(--success)';
    setTimeout(() => { btn.innerHTML = orig; btn.style.color=''; btn.style.borderColor=''; }, 2000);
  }

  function finaliseConsultation() {
    const btn = event.currentTarget;
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Finalising…';
    btn.disabled = true;
    setTimeout(() => {
      btn.innerHTML = '<i class="bi bi-check2-circle"></i> Done — Printing';
      btn.style.background = 'var(--success)';
      setTimeout(() => {
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Finalise &amp; Print';
        btn.style.background = '';
        btn.disabled = false;
      }, 2500);
    }, 1200);
  }

  /* ── FOLLOW-UP PICKER ── */
  document.querySelectorAll('.followup-opt').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.followup-opt').forEach(o => o.classList.remove('selected'));
      opt.classList.add('selected');
    });
  });

  /* init vitals check */
  checkVitals();
</script>
@php
$consultationPayload = [
    'id' => $consultation->id,
    'patient' => [
        'id' => $patient->id,
        'name' => $patient->name,
        'gender' => $patient->gender,
        'age' => $patient->age,
        'mobile' => $patient->mobile,
        'country_code' => $patient->country_code,
        'email' => $patient->email,
    ],
    'doctor' => $doctor ? ['id' => $doctor->id, 'name' => $doctor->name] : null,
    'payment' => $payment ? ['id' => $payment->id, 'appointment_no' => $payment->mocdoc_apptkey, 'visit_type' => $payment->is_followup ? 'Follow-up Visit' : 'Main Visit'] : null,
    'history' => [
        'past_medical_history' => $history->past_medical_history ?? [],
        'surgical_history' => $history->surgical_history ?? [],
        'family_history' => $history->family_history ?? [],
        'drug_allergies' => $history->drug_allergies ?? [],
        'chronic_conditions' => $history->chronic_conditions ?? [],
        'ongoing_medications' => $history->ongoing_medications ?? [],
    ],
    'consultation' => [
        'status' => $consultation->status ?? 'draft',
        'visit_date' => optional($consultation->visit_date)->toDateString(),
        'visit_time' => $consultation->visit_time,
        'token_number' => $consultation->token_number,
        'duration_value' => $consultation->chief_complaint_duration_value,
        'duration_unit' => $consultation->chief_complaint_duration_unit,
        'hpi' => $consultation->history_of_present_illness,
        'general_appearance' => $consultation->general_appearance,
        'follow_up_label' => $consultation->follow_up_label,
        'follow_up_date' => optional($consultation->follow_up_date)->toDateString(),
        'referral_department' => $consultation->referral_department,
        'referral_note' => $consultation->referral_note,
        'investigation_instructions' => $consultation->investigation_instructions,
        'advice' => $consultation->advice,
        'doctor_note' => $consultation->doctor_note,
        'vitals' => [
            'bp_systolic' => $consultation->bp_systolic,
            'bp_diastolic' => $consultation->bp_diastolic,
            'heart_rate' => $consultation->heart_rate,
            'spo2' => $consultation->spo2,
            'temperature' => $consultation->temperature,
            'weight' => $consultation->weight,
            'height' => $consultation->height,
            'bmi' => $consultation->bmi,
            'respiratory_rate' => $consultation->respiratory_rate,
            'grbs' => $consultation->grbs,
            'waist_circumference' => $consultation->waist_circumference,
            'pain_score' => $consultation->pain_score,
            'gcs' => $consultation->gcs,
        ],
        'chief_complaints' => $consultation->chief_complaints ?? [],
        'aggravating_factors' => $consultation->aggravating_factors ?? [],
        'relieving_factors' => $consultation->relieving_factors ?? [],
        'associated_symptoms' => $consultation->associated_symptoms ?? [],
        'diagnoses' => $consultation->diagnoses->map(fn($d) => ['name' => $d->diagnosis_name, 'code' => $d->icd10_code, 'type' => ucfirst($d->diagnosis_type), 'status' => ucfirst($d->clinical_status)])->values()->all(),
        'examinations' => $consultation->examinations->map(fn($e) => ['system' => $e->system_name, 'status' => $e->finding_status, 'notes' => $e->notes])->values()->all(),
        'investigations' => $consultation->investigations->pluck('test_name')->values()->all(),
        'prescriptions' => $consultation->prescriptions->map(fn($r) => ['id' => $r->medicine_id, 'name' => $r->medicine_name, 'pack_size_label' => $r->pack, 'form' => 'Tablet', 'route' => 'Oral', 'manufacturer' => '', 'use' => $r->details, 'therapeutic_class' => '', 'chemical_class' => '', 'side_effects' => [], 'alternatives' => [], 'dur' => $r->duration, 'frequency' => $r->frequency, 'instruction' => $r->instruction])->values()->all(),
    ],
    'past_consultations' => $pastConsultations->map(fn($c) => ['id' => $c->id, 'date' => optional($c->visit_date)->format('d M Y'), 'doctor' => optional($c->doctor)->name, 'title' => optional($c->diagnoses->first())->diagnosis_name ?: 'Consultation Visit'])->values()->all(),
    'routes' => ['store' => route('consultations.store')],
];
@endphp
<form id="consultation-post-form" method="POST" action="{{ route('consultations.store') }}" style="display:none;">@csrf<input type="hidden" name="consultation_id" value="{{ $consultation->id }}"><input type="hidden" name="payment_id" value="{{ $payment?->id }}"><input type="hidden" name="patient_id" value="{{ $patient->id }}"><input type="hidden" name="doctor_id" value="{{ $doctor?->id }}"><input type="hidden" name="status" id="post_status"><input type="hidden" name="print_after_save" id="post_print_after_save" value="0"><input type="hidden" name="visit_date" id="post_visit_date"><input type="hidden" name="visit_time" id="post_visit_time"><input type="hidden" name="token_number" id="post_token_number"><input type="hidden" name="duration_value" id="post_duration_value"><input type="hidden" name="duration_unit" id="post_duration_unit"><input type="hidden" name="history_of_present_illness" id="post_hpi"><input type="hidden" name="general_appearance" id="post_general_appearance"><input type="hidden" name="follow_up_label" id="post_follow_up_label"><input type="hidden" name="follow_up_date" id="post_follow_up_date"><input type="hidden" name="referral_department" id="post_referral_department"><input type="hidden" name="referral_note" id="post_referral_note"><input type="hidden" name="investigation_instructions" id="post_investigation_instructions"><input type="hidden" name="advice" id="post_advice"><input type="hidden" name="doctor_note" id="post_doctor_note"><input type="hidden" name="bp_systolic" id="post_bp_systolic"><input type="hidden" name="bp_diastolic" id="post_bp_diastolic"><input type="hidden" name="heart_rate" id="post_heart_rate"><input type="hidden" name="spo2" id="post_spo2"><input type="hidden" name="temperature" id="post_temperature"><input type="hidden" name="weight" id="post_weight"><input type="hidden" name="height" id="post_height"><input type="hidden" name="bmi" id="post_bmi"><input type="hidden" name="respiratory_rate" id="post_respiratory_rate"><input type="hidden" name="grbs" id="post_grbs"><input type="hidden" name="waist_circumference" id="post_waist_circumference"><input type="hidden" name="pain_score" id="post_pain_score"><input type="hidden" name="gcs" id="post_gcs"><input type="hidden" name="chief_complaints_json" id="post_chief_complaints_json"><input type="hidden" name="aggravating_factors_json" id="post_aggravating_factors_json"><input type="hidden" name="relieving_factors_json" id="post_relieving_factors_json"><input type="hidden" name="associated_symptoms_json" id="post_associated_symptoms_json"><input type="hidden" name="past_medical_history_json" id="post_past_medical_history_json"><input type="hidden" name="surgical_history_json" id="post_surgical_history_json"><input type="hidden" name="family_history_json" id="post_family_history_json"><input type="hidden" name="drug_allergies_json" id="post_drug_allergies_json"><input type="hidden" name="chronic_conditions_json" id="post_chronic_conditions_json"><input type="hidden" name="ongoing_medications_json" id="post_ongoing_medications_json"><input type="hidden" name="diagnoses_json" id="post_diagnoses_json"><input type="hidden" name="examinations_json" id="post_examinations_json"><input type="hidden" name="investigations_json" id="post_investigations_json"><input type="hidden" name="prescriptions_json" id="post_prescriptions_json"></form>
<script>
const CONSULTATION_PAYLOAD = @json($consultationPayload);
function setTagWrap(wrapId,inputId,items,bg,color,extraClass=''){ const wrap=document.getElementById(wrapId); if(!wrap) return; wrap.querySelectorAll('.tag-pill').forEach(el=>el.remove()); const input=document.getElementById(inputId); (items||[]).forEach(text=>{ const pill=document.createElement('span'); pill.className='tag-pill '+extraClass; if(bg) pill.style.background=bg; if(color) pill.style.color=color; pill.innerHTML=`${text} <i class="bi bi-x" onclick="removeTag(this)"></i>`; wrap.insertBefore(pill,input);}); }
function getTags(wrapId){ const wrap=document.getElementById(wrapId); if(!wrap) return []; return [...wrap.querySelectorAll('.tag-pill')].map(el=>el.textContent.replace('x','').trim()).filter(Boolean); }
function hydrateConsultation(){ const p=CONSULTATION_PAYLOAD; document.title = 'OP Consultation - ' + (p.patient.name || 'Patient'); document.querySelector('.nav-context').textContent='Outpatient � ' + (p.doctor?.name || 'Doctor Consultation'); document.querySelector('.nav-avatar').textContent=((p.doctor?.name||'DR').split(' ').map(x=>x[0]).slice(0,2).join('')).toUpperCase(); document.querySelector('.nav-avatar').title=p.doctor?.name || 'Doctor'; document.querySelector('.patient-avatar').textContent=((p.patient.name||'PT').split(' ').map(x=>x[0]).slice(0,2).join('')).toUpperCase(); document.querySelector('.patient-name').innerHTML=`${p.patient.name} &nbsp;<span style="font-size:13px;font-weight:400;color:var(--steel)">${p.patient.gender || ''} / ${p.patient.age || ''} yrs</span>`; document.querySelector('.patient-meta').innerHTML=`${p.payment?.appointment_no ? `<span><i class="bi bi-hash me-1"></i>${p.payment.appointment_no}</span>` : ''}<span><i class="bi bi-telephone me-1"></i>+${p.patient.country_code || '91'} ${p.patient.mobile || ''}</span>${p.patient.email ? `<span><i class="bi bi-envelope me-1"></i>${p.patient.email}</span>` : ''}`; const tags=document.querySelector('.patient-tags'); tags.innerHTML=''; (p.history.drug_allergies||[]).forEach(item=>{ tags.insertAdjacentHTML('beforeend', `<span class="tag tag-allergy"><i class="bi bi-exclamation-triangle-fill"></i>${item}</span>`);}); if((p.history.chronic_conditions||[]).length){ tags.insertAdjacentHTML('beforeend', `<span class="tag tag-chronic"><i class="bi bi-heart-fill"></i>${p.history.chronic_conditions.join(' � ')}</span>`);} if(p.payment?.visit_type){ tags.insertAdjacentHTML('beforeend', `<span class="tag tag-visit"><i class="bi bi-arrow-repeat"></i>${p.payment.visit_type}</span>`);} const left=document.querySelector('.sidebar-left'); left.querySelectorAll('.history-item').forEach((el,idx)=>{ if(idx<5) el.remove();}); const title=left.querySelector('.sidebar-section-title'); p.past_consultations.forEach(item=>{ title.insertAdjacentHTML('afterend', `<div class="history-item" onclick="window.location='${window.location.origin + '/edge-clinic/admin/consultations/' + item.id + '/edit'}'"><div class="history-date">${item.date || ''}</div><div class="history-diag">${item.title || 'Consultation Visit'}</div><div class="history-doc">${item.doctor || ''}</div></div>`);}); const medsSectionTitles=[...left.querySelectorAll('.sidebar-section-title')]; const medsTitle=medsSectionTitles.find(el=>el.textContent.includes('Ongoing Meds')); if(medsTitle){ medsTitle.nextElementSibling.innerHTML=(p.history.ongoing_medications||[]).map(m=>`<div class="history-item" style="padding:8px 0;"><div class="history-diag" style="font-size:12.5px;">${m}</div></div>`).join('') || '<div class="history-item" style="padding:8px 0;"><div class="history-doc">No ongoing medicines</div></div>'; }
const v=p.consultation.vitals||{}; bpSys.value=v.bp_systolic||''; bpDia.value=v.bp_diastolic||''; hr.value=v.heart_rate||''; spo2.value=v.spo2||''; temp.value=v.temperature||''; const extra=[v.weight,v.height,v.bmi,v.respiratory_rate,v.grbs,v.waist_circumference,v.pain_score,v.gcs]; [...document.querySelectorAll('#vitalsExtra .vital-input')].forEach((el,i)=>el.value=extra[i]||''); if(extra.some(Boolean)){ document.getElementById('vitalsExtra').classList.add('visible'); }
setTagWrap('complaintWrap','complaintInput',p.consultation.chief_complaints); setTagWrap('aggWrap','aggInput',p.consultation.aggravating_factors); setTagWrap('relWrap','relInput',p.consultation.relieving_factors); setTagWrap('assocWrap','assocInput',p.consultation.associated_symptoms); setTagWrap('pmhWrap','pmhInput',p.history.past_medical_history); setTagWrap('surgWrap','surgInput',p.history.surgical_history,'var(--steel-bg)','var(--steel-dark)'); setTagWrap('famWrap','famInput',p.history.family_history,'var(--steel-bg)','var(--steel-dark)'); setTagWrap('allergyWrap','allergyInput',p.history.drug_allergies,'#fdecea','#c0392b','tag-allergy'); const complaintSection=document.getElementById('secComplaints'); const complaintInputs=complaintSection.querySelectorAll('input.form-ctrl'); if(complaintInputs[0]) complaintInputs[0].value=p.consultation.duration_value||''; const durationSelect=complaintSection.querySelector('select.form-ctrl'); if(durationSelect && p.consultation.duration_unit) durationSelect.value=p.consultation.duration_unit; const complaintTextareas=complaintSection.querySelectorAll('textarea.form-ctrl'); if(complaintTextareas[0]) complaintTextareas[0].value=p.consultation.hpi||''; const examRadio=document.querySelector(`input[name="gen"][value="${p.consultation.general_appearance || 'well'}"]`); if(examRadio) examRadio.checked=true; activeSystemIds=[]; renderExamSystems(); document.getElementById('diagRows').innerHTML=''; diagCount=0; (p.consultation.diagnoses||[]).forEach(d=>createDiagRow(d.name,d.code,d.type,d.status)); document.getElementById('invWrap').querySelectorAll('.tag-pill').forEach(el=>el.remove()); setTagWrap('invWrap','invInput',p.consultation.investigations,'#e6f7f1','#2d8a6a'); const invInput=document.querySelector('#secInv input.form-ctrl'); if(invInput) invInput.value=p.consultation.investigation_instructions||''; document.getElementById('rxRows').innerHTML=''; rxCount=0; (p.consultation.prescriptions||[]).forEach(r=>{ addRxRow(r); const id=rxCount; const freq=document.getElementById(`rxFreq${id}`); const inst=document.getElementById(`rxInst${id}`); if(freq && r.frequency){ [...freq.options].forEach(o=>{ if(o.text.includes(r.frequency)||o.value===r.frequency) freq.value=o.value;}); } if(inst && r.instruction){ [...inst.options].forEach(o=>{ if(o.text.toLowerCase()===String(r.instruction).toLowerCase()) inst.value=o.value;}); } }); const rxAdvice=document.querySelector('#secRx textarea.form-ctrl'); if(rxAdvice) rxAdvice.value=p.consultation.advice||''; document.querySelectorAll('.followup-opt').forEach(el=>el.classList.toggle('selected', el.textContent.trim()=== (p.consultation.follow_up_label || '4 Weeks'))); const dateInput=document.querySelector('.sidebar-right input[type="date"]'); if(dateInput) dateInput.value=p.consultation.follow_up_date||''; const refSelect=document.querySelector('.sidebar-right select.form-ctrl'); if(refSelect && p.consultation.referral_department) refSelect.value=p.consultation.referral_department; const refInput=document.querySelector('.sidebar-right input.form-ctrl'); if(refInput) refInput.value=p.consultation.referral_note||''; const sideTextareas=document.querySelectorAll('.sidebar-right textarea.form-ctrl'); if(sideTextareas[0]) sideTextareas[0].value=p.consultation.doctor_note||''; if(sideTextareas[1]) sideTextareas[1].value=p.consultation.doctor_note||''; checkVitals(); }
function collectAndSubmit(status, printAfter){ document.getElementById('post_status').value=status; document.getElementById('post_print_after_save').value=printAfter ? '1' : '0'; document.getElementById('post_visit_date').value=document.querySelector('.sidebar-right input[type="date"]')?.value || CONSULTATION_PAYLOAD.consultation.visit_date || ''; document.getElementById('post_visit_time').value=CONSULTATION_PAYLOAD.consultation.visit_time || ''; document.getElementById('post_token_number').value=CONSULTATION_PAYLOAD.consultation.token_number || CONSULTATION_PAYLOAD.payment?.appointment_no || ''; const complaintSection=document.getElementById('secComplaints'); const complaintInputs=complaintSection.querySelectorAll('input.form-ctrl'); document.getElementById('post_duration_value').value=complaintInputs[0]?.value || ''; document.getElementById('post_duration_unit').value=complaintSection.querySelector('select.form-ctrl')?.value || ''; document.getElementById('post_hpi').value=complaintSection.querySelector('textarea.form-ctrl')?.value || ''; document.getElementById('post_general_appearance').value=document.querySelector('input[name="gen"]:checked')?.value || 'well'; document.getElementById('post_follow_up_label').value=document.querySelector('.followup-opt.selected')?.textContent.trim() || '4 Weeks'; document.getElementById('post_follow_up_date').value=document.querySelector('.sidebar-right input[type="date"]')?.value || ''; document.getElementById('post_referral_department').value=document.querySelector('.sidebar-right select.form-ctrl')?.value || ''; document.getElementById('post_referral_note').value=document.querySelector('.sidebar-right input.form-ctrl')?.value || ''; document.getElementById('post_investigation_instructions').value=document.querySelector('#secInv input.form-ctrl')?.value || ''; document.getElementById('post_advice').value=document.querySelector('#secRx textarea.form-ctrl')?.value || ''; document.getElementById('post_doctor_note').value=document.querySelector('.sidebar-right textarea.form-ctrl')?.value || ''; document.getElementById('post_bp_systolic').value=bpSys.value || ''; document.getElementById('post_bp_diastolic').value=bpDia.value || ''; document.getElementById('post_heart_rate').value=hr.value || ''; document.getElementById('post_spo2').value=spo2.value || ''; document.getElementById('post_temperature').value=temp.value || ''; const extraVals=[...document.querySelectorAll('#vitalsExtra .vital-input')].map(el=>el.value || ''); ['weight','height','bmi','respiratory_rate','grbs','waist_circumference','pain_score','gcs'].forEach((key,idx)=>document.getElementById('post_'+key).value=extraVals[idx]||''); document.getElementById('post_chief_complaints_json').value=JSON.stringify(getTags('complaintWrap')); document.getElementById('post_aggravating_factors_json').value=JSON.stringify(getTags('aggWrap')); document.getElementById('post_relieving_factors_json').value=JSON.stringify(getTags('relWrap')); document.getElementById('post_associated_symptoms_json').value=JSON.stringify(getTags('assocWrap')); document.getElementById('post_past_medical_history_json').value=JSON.stringify(getTags('pmhWrap')); document.getElementById('post_surgical_history_json').value=JSON.stringify(getTags('surgWrap')); document.getElementById('post_family_history_json').value=JSON.stringify(getTags('famWrap')); document.getElementById('post_drug_allergies_json').value=JSON.stringify(getTags('allergyWrap')); document.getElementById('post_chronic_conditions_json').value=JSON.stringify(CONSULTATION_PAYLOAD.history.chronic_conditions || []); document.getElementById('post_ongoing_medications_json').value=JSON.stringify(CONSULTATION_PAYLOAD.history.ongoing_medications || []); const diags=[...document.querySelectorAll('#diagRows .diag-row')].map(row=>({ diagnosis_name: row.querySelector('.icd-input')?.value || '', icd10_code: row.querySelector('.icd-code-badge')?.textContent === 'ICD-10' ? '' : row.querySelector('.icd-code-badge')?.textContent, diagnosis_type: row.querySelectorAll('.diag-select')[0]?.value || 'Provisional', clinical_status: row.querySelectorAll('.diag-select')[1]?.value || 'Active' })).filter(x=>x.diagnosis_name); document.getElementById('post_diagnoses_json').value=JSON.stringify(diags); const exams=[...document.querySelectorAll('#examSystemsGrid .exam-system')].map(card=>({ system_name: card.querySelector('.exam-system-header')?.childNodes[1]?.textContent.trim() || '', finding_status: card.querySelector('input[type="radio"]:checked')?.value || 'normal', notes: card.querySelector('.exam-note-inline')?.value || '' })).filter(x=>x.system_name); document.getElementById('post_examinations_json').value=JSON.stringify(exams); document.getElementById('post_investigations_json').value=JSON.stringify(getTags('invWrap').map(x=>({test_name:x}))); const meds=[...document.querySelectorAll('#rxRows .rx-entry')].map(row=>({ medicine_name: row.querySelector('.rx-drug-input')?.value || '', pack: row.querySelector('[id^="rxPack"]')?.textContent || '', frequency: row.querySelector('.rx-sel')?.value || '', duration: row.querySelector('.rx-dur-input')?.value || '', instruction: row.querySelectorAll('.rx-sel')[1]?.value || '', details: row.querySelector('.rx-detail-drawer')?.innerText || '' })).filter(x=>x.medicine_name); document.getElementById('post_prescriptions_json').value=JSON.stringify(meds); document.getElementById('consultation-post-form').submit(); }
window.saveDraft=function(){ collectAndSubmit('draft', false); }
window.finaliseConsultation=function(){ collectAndSubmit('finalized', true); }
window.fetchIcd = async function(id, query){ const res = await fetch(`{{ route('consultations.search.icd10') }}?q=${encodeURIComponent(query)}`); const data = await res.json(); renderDrop(id, data.map(x=>[x.code,x.name]), query); }
window.searchMeds = function(id, q){ fetch(`{{ route('consultations.search.medicines') }}?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(data=>renderRxDrop(id, data.map(x=>({name:x.name, pack_size_label:x.pack || 'Pack not set', form:'Tablet', route:'Oral', manufacturer:x.manufacturer || '', use:x.details || '', therapeutic_class:'', chemical_class:'', side_effects:[], alternatives:[]})), q)); }
document.addEventListener('DOMContentLoaded', hydrateConsultation);
</script>
</body>
</html>

