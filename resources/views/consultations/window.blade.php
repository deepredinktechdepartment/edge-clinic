<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OP Consultation – Edge Clinic</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --white: #ffffff;
            --off-white: #f7f9fb;
            --baby-blue: #a8c8e8;
            --blue-mid: #6aaed6;
            --blue-deep: #3a82b5;
            --blue-light: #e8f3fb;
            --steel: #6b7c8d;
            --steel-light: #9aaab8;
            --steel-dark: #3d4f5f;
            --steel-bg: #eef1f4;
            --border: #d8e2eb;
            --text-primary: #1e2d3d;
            --text-muted: #7a8fa0;
            --success: #3aab82;
            --warning: #e8a84a;
            --danger: #e05c5c;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 4px rgba(58, 82, 115, .08);
            --shadow-md: 0 3px 14px rgba(58, 82, 115, .12);
            --shadow-lg: 0 8px 32px rgba(58, 82, 115, .15);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
            background: var(--off-white);
            color: var(--text-primary);
            font-size: 14px;
            display: flex;
            flex-direction: column;
        }

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

        .nav-brand i {
            font-size: 20px;
        }

        .nav-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
        }

        .nav-context {
            font-size: 13px;
            color: var(--steel);
            font-weight: 500;
        }

        .nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-badge {
            background: var(--blue-light);
            color: var(--blue-deep);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .nav-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--steel-dark);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

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
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--baby-blue), var(--blue-mid));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
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

        .patient-meta span {
            margin-right: 12px;
        }

        .patient-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .tag-allergy {
            background: #fdecea;
            color: #c0392b;
        }

        .tag-chronic {
            background: #fff8e6;
            color: #a0660a;
        }

        .tag-visit {
            background: var(--blue-light);
            color: var(--blue-deep);
        }

        .banner-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }

        .main-layout {
            display: grid;
            grid-template-columns: 240px 1fr 260px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

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

        .sidebar-section-title:first-child {
            margin-top: 0;
        }

        .history-item {
            padding: 8px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all .15s;
        }

        .history-item:hover {
            background: var(--off-white);
            border-left-color: var(--baby-blue);
        }

        .history-item.active {
            background: var(--blue-light);
            border-left-color: var(--blue-deep);
        }

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

        .sidebar-left::-webkit-scrollbar,
        .main-content::-webkit-scrollbar,
        .sidebar-right::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-left::-webkit-scrollbar-thumb,
        .main-content::-webkit-scrollbar-thumb,
        .sidebar-right::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

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

        .card-header:hover {
            background: var(--off-white);
        }

        .card-icon {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .icon-blue {
            background: var(--blue-light);
            color: var(--blue-deep);
        }

        .icon-steel {
            background: var(--steel-bg);
            color: var(--steel-dark);
        }

        .icon-green {
            background: #e6f7f1;
            color: var(--success);
        }

        .icon-warn {
            background: #fff5e6;
            color: var(--warning);
        }

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

        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }

        .card-body {
            padding: 16px;
        }

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

        .vital-box:focus-within {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 3px rgba(106, 174, 214, .12);
        }

        .vital-label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--steel-light);
            letter-spacing: .4px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .vital-input {
            border: none;
            outline: none;
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
            top: 8px;
            right: 8px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
        }

        .vital-status.warn {
            background: var(--warning);
        }

        .vital-status.alert {
            background: var(--danger);
        }

        .vital-expand-btn {
            margin-top: 10px;
            font-size: 12px;
            color: var(--blue-deep);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
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

        .vital-extra.visible {
            display: grid;
        }

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
            box-shadow: 0 0 0 3px rgba(106, 174, 214, .12);
        }

        .form-ctrl::placeholder {
            color: var(--steel-light);
        }

        select.form-ctrl {
            cursor: pointer;
        }

        .tag-input-wrap {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            min-height: 40px;
            cursor: text;
            transition: border-color .15s, box-shadow .15s;
        }

        .tag-input-wrap:focus-within {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 3px rgba(106, 174, 214, .12);
        }

        .tag-pill {
            background: var(--blue-light);
            color: var(--blue-deep);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 12.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .tag-pill i {
            cursor: pointer;
            font-size: 10px;
            opacity: .6;
        }

        .tag-pill i:hover {
            opacity: 1;
        }

        .tag-input-field {
            border: none;
            outline: none;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 14px;
            color: var(--text-primary);
            flex: 1;
            min-width: 80px;
            background: transparent;
        }

        .exam-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .exam-system {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
            position: relative;
        }

        .exam-system-header {
            background: var(--off-white);
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--steel-dark);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .exam-system-body {
            padding: 10px 12px;
        }

        .radio-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .radio-opt {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-primary);
        }

        .radio-opt input {
            accent-color: var(--blue-deep);
            cursor: pointer;
        }

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

        .exam-note-inline::placeholder {
            color: var(--steel-light);
        }

        .exam-system-remove {
            position: absolute;
            top: 5px;
            right: 6px;
            border: none;
            background: none;
            color: var(--steel-light);
            font-size: 13px;
            cursor: pointer;
            line-height: 1;
            padding: 2px 4px;
            border-radius: 4px;
            opacity: 0;
            transition: opacity .15s, color .15s;
        }

        .exam-system:hover .exam-system-remove {
            opacity: 1;
        }

        .exam-system-remove:hover {
            color: var(--danger);
            background: #fdecea;
        }

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

        .diag-row:last-of-type {
            border-bottom: none;
        }

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

        .icd-input:focus {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 3px rgba(106, 174, 214, .12);
        }

        .icd-input::placeholder {
            color: var(--steel-light);
        }

        .icd-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 3px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            z-index: 200;
            max-height: 220px;
            overflow-y: auto;
        }

        .icd-dropdown.open {
            display: block;
        }

        .icd-item {
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: baseline;
            gap: 10px;
            transition: background .1s;
            border-bottom: 1px solid var(--border);
        }

        .icd-item:last-child {
            border-bottom: none;
        }

        .icd-item:hover,
        .icd-item.focused {
            background: var(--blue-light);
        }

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

        .icd-loading,
        .icd-empty {
            padding: 10px 14px;
            font-size: 12.5px;
            color: var(--steel-light);
            font-style: italic;
        }

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

        .diag-select:focus {
            border-color: var(--blue-mid);
        }

        .diag-del {
            border: none;
            background: none;
            color: var(--steel-light);
            cursor: pointer;
            font-size: 14px;
            padding: 3px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            transition: all .12s;
            flex-shrink: 0;
        }

        .diag-del:hover {
            background: #fdecea;
            color: var(--danger);
        }

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
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all .15s;
            width: 100%;
            margin-top: 8px;
        }

        .add-diag-btn:hover {
            background: var(--blue-light);
            border-style: solid;
        }

        /* PRESCRIPTION TABLE */
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

        .rx-entry {
            border-bottom: 1px solid var(--border);
            transition: background .1s;
        }

        .rx-entry:last-child {
            border-bottom: none;
        }

        .rx-row {
            display: grid;
            grid-template-columns: 2fr 130px 120px 100px 1fr 60px;
            gap: 6px;
            padding: 6px;
            align-items: center;
        }

        .rx-entry:hover .rx-row {
            background: var(--off-white);
        }

        .rx-drug-cell {
            position: relative;
            min-width: 0;
        }

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

        .rx-drug-input:focus {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 3px rgba(106, 174, 214, .12);
        }

        .rx-drug-input::placeholder {
            color: var(--steel-light);
        }

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
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            z-index: 300;
            max-height: 240px;
            overflow-y: auto;
        }

        .rx-drug-drop.open {
            display: block;
        }

        .rx-drug-item {
            padding: 9px 12px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background .1s;
        }

        .rx-drug-item:last-child {
            border-bottom: none;
        }

        .rx-drug-item:hover,
        .rx-drug-item.focused {
            background: var(--blue-light);
        }

        .rx-drug-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .rx-drug-name em {
            font-style: normal;
            background: #fff3b0;
            border-radius: 2px;
            padding: 0 1px;
        }

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

        .rx-sel:focus {
            border-color: var(--blue-mid);
        }

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

        .rx-dur-input:focus {
            border-color: var(--blue-mid);
        }

        .rx-dur-input::placeholder {
            color: var(--steel-light);
        }

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
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            transition: all .12s;
            flex-shrink: 0;
        }

        .rx-info-btn:hover {
            background: var(--blue-light);
            color: var(--blue-deep);
            border-color: var(--baby-blue);
        }

        .rx-info-btn.active {
            background: var(--blue-deep);
            color: white;
            border-color: var(--blue-deep);
        }

        .rx-del-btn {
            border: none;
            background: none;
            color: var(--steel-light);
            cursor: pointer;
            font-size: 14px;
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .12s;
            flex-shrink: 0;
        }

        .rx-del-btn:hover {
            background: #fdecea;
            color: var(--danger);
        }

        .rx-detail-drawer {
            display: none;
            background: var(--off-white);
            border-top: 1px dashed var(--border);
            padding: 14px 16px 16px;
        }

        .rx-detail-drawer.open {
            display: block;
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

        .drawer-val.muted {
            color: var(--steel);
            font-weight: 400;
        }

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

        .drawer-tag.blue {
            background: var(--blue-light);
            color: var(--blue-deep);
        }

        .drawer-tag.warn {
            background: #fff5e6;
            color: #a0660a;
        }

        .drawer-tag.danger {
            background: #fdecea;
            color: #c0392b;
        }

        .drawer-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 10px 0;
        }

        .alternatives-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
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
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .alt-pill:hover {
            border-color: var(--baby-blue);
            background: var(--blue-light);
            color: var(--blue-deep);
        }

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
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all .15s;
            margin-top: 8px;
        }

        .add-rx-btn:hover {
            background: var(--blue-light);
            border-style: solid;
        }

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

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-key {
            color: var(--steel);
            font-weight: 500;
            flex-shrink: 0;
        }

        .summary-val {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
        }

        .summary-val.normal {
            color: var(--success);
        }

        .summary-val.abnormal {
            color: var(--danger);
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

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
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .12s;
            text-align: left;
            text-decoration: none;
            width: 100%;
        }

        .quick-btn:hover {
            background: var(--off-white);
            border-color: var(--baby-blue);
        }

        .quick-btn i {
            font-size: 15px;
            color: var(--blue-deep);
        }

        .quick-btn.disabled-btn {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }

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

        .followup-opt:hover,
        .followup-opt.selected {
            background: var(--blue-light);
            border-color: var(--baby-blue);
            color: var(--blue-deep);
        }

        .bottom-bar {
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            box-shadow: 0 -2px 8px rgba(58, 82, 115, .07);
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
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .15s;
            box-shadow: 0 2px 8px rgba(58, 130, 181, .3);
        }

        .btn-primary-custom:hover {
            background: #2d6d9b;
            box-shadow: 0 4px 14px rgba(58, 130, 181, .4);
        }

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
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all .15s;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            background: var(--off-white);
            border-color: var(--baby-blue);
        }

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
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all .15s;
            margin-left: auto;
        }

        .btn-danger-outline:hover {
            background: #fdecea;
        }

        .progress-steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-right: 16px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--steel-light);
        }

        .step-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: var(--steel-light);
            transition: all .2s;
            flex-shrink: 0;
        }

        .step.done .step-dot {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step.active .step-dot {
            background: var(--blue-deep);
            border-color: var(--blue-deep);
            color: white;
        }

        .step.active {
            color: var(--blue-deep);
        }

        .step.done {
            color: var(--success);
        }

        .step-line {
            width: 24px;
            height: 2px;
            background: var(--border);
            margin: 0 2px;
            transition: background .2s;
        }

        .step-line.done {
            background: var(--success);
        }

        .modal-backdrop-custom {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(30, 45, 61, .3);
            backdrop-filter: blur(2px);
            z-index: 999;
        }

        .modal-backdrop-custom.visible {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 600px;
            max-width: 95vw;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        .modal-head {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 700;
            flex: 1;
        }

        .modal-close {
            border: none;
            background: none;
            color: var(--steel);
            font-size: 18px;
            cursor: pointer;
            line-height: 1;
            padding: 2px;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-foot {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

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

        .spec-chip:hover {
            border-color: var(--baby-blue);
            color: var(--blue-deep);
            background: var(--blue-light);
        }

        .spec-chip.active {
            background: var(--blue-deep);
            border-color: var(--blue-deep);
            color: white;
        }

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

        .sys-pick-item:hover {
            border-color: var(--baby-blue);
            background: var(--blue-light);
            color: var(--blue-deep);
        }

        .sys-pick-item.selected {
            border-color: var(--blue-deep);
            background: var(--blue-light);
            color: var(--blue-deep);
        }

        .sys-pick-item i {
            font-size: 15px;
        }

        .sys-check {
            margin-left: auto;
            font-size: 13px;
            display: none;
        }

        .sys-pick-item.selected .sys-check {
            display: block;
            color: var(--blue-deep);
        }

        .exam-expand-link {
            margin-top: 8px;
            font-size: 12px;
            color: var(--blue-deep);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            font-family: 'Source Sans 3', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        /* Visit Setup row */
        .visit-setup-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 12px;
        }

        /* Toast */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e2d3d;
            color: #fff;
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            z-index: 9999;
            display: none;
            gap: 8px;
            align-items: center;
        }

        .toast-msg.show {
            display: flex;
        }
    </style>
</head>

<body>

    @php
        $consultationPayload = [
            'id' => $consultation->id,
            'exists' => $consultation->exists,
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'gender' => $patient->gender,
                'age' => $patient->age,
                'mobile' => $patient->mobile,
                'country_code' => $patient->country_code ?? '91',
                'email' => $patient->email,
            ],
            'doctor' => $doctor ? ['id' => $doctor->id, 'name' => $doctor->name] : null,
            'payment' => $payment
                ? [
                    'id' => $payment->id,
                    'appointment_no' => $payment->mocdoc_apptkey,
                    'visit_type' => $payment->is_followup ?? false ? 'Follow-up Visit' : 'Main Visit',
                ]
                : null,
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
                'finalized_at' => optional($consultation->finalized_at)?->toDateTimeString(),
                'receptionist_updated_at' => optional($consultation->receptionist_updated_at)?->toDateTimeString(),
                'visit_date' => optional($consultation->visit_date)->toDateString() ?? now()->toDateString(),
                'visit_time' => $consultation->visit_time ?? ($payment?->aptTime ?? ''),
                'token_number' => $consultation->token_number ?? ($payment?->mocdoc_apptkey ?? ''),
                'doctor_id' => $consultation->doctor_id ?? $doctor?->id,
                'duration_value' => $consultation->chief_complaint_duration_value,
                'duration_unit' => $consultation->chief_complaint_duration_unit,
                'hpi' => $consultation->history_of_present_illness,
                'general_appearance' => $consultation->general_appearance ?? 'well',
                'follow_up_label' => $consultation->follow_up_label ?? '4 Weeks',
                'follow_up_date' => optional($consultation->follow_up_date)->toDateString() ?? $defaultFollowUpDate,
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
                'diagnoses' => $consultation->diagnoses
                    ->map(
                        fn($d) => [
                            'name' => $d->diagnosis_name,
                            'code' => $d->icd10_code,
                            'code_id' => $d->icd10_code_id,
                            'type' => ucfirst($d->diagnosis_type),
                            'status' => ucfirst($d->clinical_status),
                        ],
                    )
                    ->values()
                    ->all(),
                'examinations' => $consultation->examinations
                    ->map(fn($e) => ['system' => $e->system_name, 'status' => $e->finding_status, 'notes' => $e->notes])
                    ->values()
                    ->all(),
                'investigations' => $consultation->investigations->pluck('test_name')->values()->all(),
                'prescriptions' => $consultation->prescriptions
                    ->map(
                        fn($r) => [
                            'id' => $r->medicine_id,
                            'name' => $r->medicine_name,
                            'pack_size_label' => $r->pack,
                            'form' => '',
                            'route' => '',
                            'manufacturer' => '',
                            'use' => $r->details,
                            'therapeutic_class' => '',
                            'chemical_class' => '',
                            'side_effects' => [],
                            'alternatives' => [],
                            'dur' => $r->duration,
                            'frequency' => $r->frequency,
                            'instruction' => $r->instruction,
                        ],
                    )
                    ->values()
                    ->all(),
            ],
            'past_consultations' => $pastConsultations
                ->map(
                    fn($c) => [
                        'id' => $c->id,
                        'date' => optional($c->visit_date)->format('d M Y'),
                        'doctor' => optional($c->doctor)->name,
                        'title' => optional($c->diagnoses->first())->diagnosis_name ?: 'Consultation Visit',
                    ],
                )
                ->values()
                ->all(),
            'routes' => [
                'store' => route('consultations.store'),
                'print' => $consultation->exists ? route('consultations.print', $consultation) : null,
                'pdf' => $consultation->exists ? route('consultations.pdf', $consultation) : null,
                'email' => $consultation->exists ? route('consultations.email', $consultation) : null,
                'icd10' => route('consultations.search.icd10'),
                'medicines' => route('consultations.search.medicines'),
            ],
            'permissions' => $consultationPermissions,
            'departments' => $departments->map(fn($d) => ['name' => $d->dept_name])->values()->all(),
        ];
    @endphp

    <!-- HIDDEN POST FORM -->
    <form id="consultation-post-form" method="POST" action="{{ route('consultations.store') }}" style="display:none;">
        @csrf
        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->id ?? '' }}">
        <input type="hidden" name="payment_id" value="{{ $payment?->id }}">
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        <input type="hidden" name="status" id="post_status">
        <input type="hidden" name="print_after_save" id="post_print_after_save" value="0">
        <input type="hidden" name="doctor_id" id="post_doctor_id">
        <input type="hidden" name="visit_date" id="post_visit_date">
        <input type="hidden" name="visit_time" id="post_visit_time">
        <input type="hidden" name="token_number" id="post_token_number">
        <input type="hidden" name="duration_value" id="post_duration_value">
        <input type="hidden" name="duration_unit" id="post_duration_unit">
        <input type="hidden" name="history_of_present_illness" id="post_hpi">
        <input type="hidden" name="general_appearance" id="post_general_appearance">
        <input type="hidden" name="follow_up_label" id="post_follow_up_label">
        <input type="hidden" name="follow_up_date" id="post_follow_up_date">
        <input type="hidden" name="referral_department" id="post_referral_department">
        <input type="hidden" name="referral_note" id="post_referral_note">
        <input type="hidden" name="investigation_instructions" id="post_investigation_instructions">
        <input type="hidden" name="advice" id="post_advice">
        <input type="hidden" name="doctor_note" id="post_doctor_note">
        <input type="hidden" name="bp_systolic" id="post_bp_systolic">
        <input type="hidden" name="bp_diastolic" id="post_bp_diastolic">
        <input type="hidden" name="heart_rate" id="post_heart_rate">
        <input type="hidden" name="spo2" id="post_spo2">
        <input type="hidden" name="temperature" id="post_temperature">
        <input type="hidden" name="weight" id="post_weight">
        <input type="hidden" name="height" id="post_height">
        <input type="hidden" name="bmi" id="post_bmi">
        <input type="hidden" name="respiratory_rate" id="post_respiratory_rate">
        <input type="hidden" name="grbs" id="post_grbs">
        <input type="hidden" name="waist_circumference" id="post_waist_circumference">
        <input type="hidden" name="pain_score" id="post_pain_score">
        <input type="hidden" name="gcs" id="post_gcs">
        <input type="hidden" name="chief_complaints_json" id="post_chief_complaints_json">
        <input type="hidden" name="aggravating_factors_json" id="post_aggravating_factors_json">
        <input type="hidden" name="relieving_factors_json" id="post_relieving_factors_json">
        <input type="hidden" name="associated_symptoms_json" id="post_associated_symptoms_json">
        <input type="hidden" name="past_medical_history_json" id="post_past_medical_history_json">
        <input type="hidden" name="surgical_history_json" id="post_surgical_history_json">
        <input type="hidden" name="family_history_json" id="post_family_history_json">
        <input type="hidden" name="drug_allergies_json" id="post_drug_allergies_json">
        <input type="hidden" name="chronic_conditions_json" id="post_chronic_conditions_json">
        <input type="hidden" name="ongoing_medications_json" id="post_ongoing_medications_json">
        <input type="hidden" name="diagnoses_json" id="post_diagnoses_json">
        <input type="hidden" name="examinations_json" id="post_examinations_json">
        <input type="hidden" name="investigations_json" id="post_investigations_json">
        <input type="hidden" name="prescriptions_json" id="post_prescriptions_json">
    </form>

    <!-- TOAST -->
    <div class="toast-msg" id="toastMsg"><i class="bi bi-check2-circle"></i><span id="toastText"></span></div>

    <!-- TOP NAV -->
    <div class="top-nav">
        <div class="nav-brand"><span>
            <img class="img-fluid nav-brand-img" src="{{URL::to('assets/img/SH-Final-Logo.png')}}" alt="" style="max-width: 80px;">
        </span></div>
        <div class="nav-divider"></div>
        <div class="nav-context" id="navContext">Outpatient</div>
        <div class="nav-right">
            <div class="nav-badge" id="navBadge"><i class="bi bi-clock me-1"></i>Consultation</div>
            <div class="nav-badge" style="background:#e6f7f1;color:#2d8a6a;"><i class="bi bi-wifi me-1"></i>Online
            </div>
            <div class="nav-avatar" id="navAvatar" title="">DR</div>
            <!-- ✅ Close Button -->
          <button type="button" id="closeWindowBtn"
              style="
                  margin-left:10px;
                  width:32px;
                  height:32px;
                  border-radius:50%;
                  border:none;
                  background:#ffe6e6;
                  color:#d11a2a;
                  display:flex;
                  align-items:center;
                  justify-content:center;
                  cursor:pointer;
              ">
              <i class="bi bi-x-lg"></i>
          </button>
        </div>
    </div>

    <!-- PATIENT BANNER -->
    <div class="patient-banner">
        <div class="patient-avatar" id="patientAvatar">PT</div>
        <div>
            <div class="patient-name" id="patientName">Loading…</div>
            <div class="patient-meta" id="patientMeta"></div>
        </div>
        <div class="patient-tags" id="patientTags"></div>
        <div class="banner-actions">
            <button class="btn-secondary-custom" onclick="openModal('histModal')"><i class="bi bi-clock-history"></i>
                Full History</button>
            <a id="bannerPrintBtn" class="btn-secondary-custom disabled-btn" href="#" target="_blank"><i
                    class="bi bi-printer"></i> Print</a>
            <a id="bannerPdfBtn" class="btn-secondary-custom disabled-btn" href="#"><i
                    class="bi bi-file-earmark-arrow-down"></i> PDF</a>
        </div>
    </div>

    <!-- MAIN 3-COLUMN LAYOUT -->
    <div class="main-layout">

        <!-- LEFT SIDEBAR: Past Visits -->
        <div class="sidebar-left">
            <div class="sidebar-section-title">Past Visits</div>
            <div class="history-item active">
                <div class="history-date" id="currentVisitDate">Today</div>
                <div class="history-diag">Current Visit</div>
                <div class="history-doc" id="currentVisitDoc">–</div>
            </div>
            <div id="pastVisitsList"></div>
            <div class="sidebar-section-title" style="margin-top:16px">Ongoing Meds</div>
            <div id="ongoingMedsList" style="padding:0 16px;"></div>
            <div class="sidebar-section-title" style="margin-top:16px">Allergies</div>
            <div id="allergiesList" style="padding:0 16px;"></div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content" id="mainContent">

            @if ($errors->any())
                <div class="alert alert-danger mb-0">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success mb-0">{{ session('success') }}</div>
            @endif

            <div class="alert alert-info mb-0" id="workflowNotice" style="display:none;"></div>

            <!-- VISIT SETUP -->
            <div class="card-section" id="secVisit">
                <div class="card-header" onclick="toggleCard('secVisit')">
                    <div class="card-icon icon-steel"><i class="bi bi-calendar-check"></i></div>
                    <div class="card-title">Visit Setup</div>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="card-body">
                    <div class="visit-setup-grid">
                        <div>
                            <label class="form-label-custom">Visit Date</label>
                            <input type="date" class="form-ctrl" id="inp_visit_date">
                        </div>
                        <div>
                            <label class="form-label-custom">Visit Time</label>
                            <input type="text" class="form-ctrl" id="inp_visit_time" placeholder="e.g. 10:30 AM">
                        </div>
                        <div>
                            <label class="form-label-custom">Token / Appointment #</label>
                            <input type="text" class="form-ctrl" id="inp_token_number"
                                placeholder="Token or apt key">
                        </div>
                        <div>
                            <label class="form-label-custom">Doctor</label>
                            <input type="text" class="form-ctrl" id="inp_doctor_name" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VITALS -->
            <div class="card-section" id="secVitals">
                <div class="card-header" onclick="toggleCard('secVitals')">
                    <div class="card-icon icon-blue"><i class="bi bi-activity"></i></div>
                    <div class="card-title">Vitals</div>
                    <span class="card-subtitle" id="vitals-summary"></span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="card-body">
                    <div class="vitals-grid" id="vitalsBasic">
                        <div class="vital-box">
                            <div class="vital-status" id="bpDot"></div>
                            <div class="vital-label">Blood Pressure</div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <input class="vital-input" id="bpSys" type="number" placeholder="120"
                                    style="width:52px;" oninput="onVitalChange()">
                                <span style="color:var(--steel-light);font-size:16px;">/</span>
                                <input class="vital-input" id="bpDia" type="number" placeholder="80"
                                    style="width:52px;" oninput="onVitalChange()">
                            </div>
                            <div class="vital-unit">mmHg &nbsp;<span
                                    style="font-size:10px;color:var(--steel-light);">Normal &lt;120/80</span></div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-status" id="hrDot"></div>
                            <div class="vital-label">Heart Rate</div>
                            <input class="vital-input" id="hr" type="number" placeholder="72"
                                oninput="onVitalChange()">
                            <div class="vital-unit">bpm &nbsp;<span
                                    style="font-size:10px;color:var(--steel-light);">60–100</span></div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-status" id="spoDot"></div>
                            <div class="vital-label">SpO₂</div>
                            <input class="vital-input" id="spo2" type="number" placeholder="98"
                                oninput="onVitalChange()">
                            <div class="vital-unit">% &nbsp;<span
                                    style="font-size:10px;color:var(--steel-light);">&ge;95%</span></div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-status" id="tempDot"></div>
                            <div class="vital-label">Temperature</div>
                            <input class="vital-input" id="temp" type="number" step="0.1"
                                placeholder="98.6" oninput="onVitalChange()">
                            <div class="vital-unit">°F &nbsp;<span
                                    style="font-size:10px;color:var(--steel-light);">97–99°F</span></div>
                        </div>
                    </div>
                    <button class="vital-expand-btn" onclick="toggleVitalsExtra(this)">
                        <i class="bi bi-plus-circle"></i> More vitals (Weight, Height, BMI, RR, GRBS…)
                    </button>
                    <div class="vital-extra" id="vitalsExtra">
                        <div class="vital-box">
                            <div class="vital-label">Weight</div>
                            <input class="vital-input" id="vWeight" type="number" placeholder="70"
                                oninput="calcBmi()">
                            <div class="vital-unit">kg</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">Height</div>
                            <input class="vital-input" id="vHeight" type="number" placeholder="170"
                                oninput="calcBmi()">
                            <div class="vital-unit">cm</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">BMI</div>
                            <input class="vital-input" id="vBmi" type="text" placeholder="–" readonly>
                            <div class="vital-unit" id="bmiLabel">kg/m²</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">Resp. Rate</div>
                            <input class="vital-input" id="vRr" type="number" placeholder="16">
                            <div class="vital-unit">breaths/min</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-status" id="grbsDot"></div>
                            <div class="vital-label">GRBS</div>
                            <input class="vital-input" id="vGrbs" type="number" placeholder="–"
                                oninput="onVitalChange()">
                            <div class="vital-unit">mg/dL &nbsp;<span
                                    style="font-size:10px;color:var(--steel-light);">PP &lt;140</span></div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">Waist Circ.</div>
                            <input class="vital-input" id="vWaist" type="number" placeholder="–">
                            <div class="vital-unit">cm</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">Pain Score</div>
                            <input class="vital-input" id="vPain" type="number" placeholder="0" min="0"
                                max="10">
                            <div class="vital-unit">/ 10 (VAS)</div>
                        </div>
                        <div class="vital-box">
                            <div class="vital-label">GCS</div>
                            <input class="vital-input" id="vGcs" type="number" placeholder="15"
                                min="3" max="15">
                            <div class="vital-unit">/ 15</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHIEF COMPLAINTS -->
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
                                <input class="tag-input-field" id="complaintInput" placeholder="Type and press Enter"
                                    onkeydown="addTag(event,'complaintWrap')">
                            </div>
                        </div>
                        <div>
                            <label class="form-label-custom">Duration</label>
                            <div style="display:flex;gap:8px;">
                                <input type="number" class="form-ctrl" id="durationValue" style="width:80px;"
                                    placeholder="3">
                                <select class="form-ctrl" id="durationUnit">
                                    <option>Days</option>
                                    <option>Weeks</option>
                                    <option>Months</option>
                                    <option>Years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">History of Present Illness <span
                                style="font-weight:400;text-transform:none;font-size:12px;color:var(--steel-light);">(optional)</span></label>
                        <textarea class="form-ctrl" id="hpiText" rows="2"
                            placeholder="Describe onset, progression, character of complaints…"></textarea>
                    </div>
                    <button class="vital-expand-btn" onclick="toggleSection('hpiExtra',this)"
                        style="margin-top:10px;">
                        <i class="bi bi-plus-circle"></i> Add more detail (Aggravating / Relieving / Associated
                        symptoms)
                    </button>
                    <div id="hpiExtra" style="display:none;margin-top:12px;">
                        <div class="row-2">
                            <div>
                                <label class="form-label-custom">Aggravating Factors</label>
                                <div class="tag-input-wrap" id="aggWrap" onclick="focusTagInput('aggInput')">
                                    <input class="tag-input-field" id="aggInput" placeholder="e.g. exertion"
                                        onkeydown="addTag(event,'aggWrap')">
                                </div>
                            </div>
                            <div>
                                <label class="form-label-custom">Relieving Factors</label>
                                <div class="tag-input-wrap" id="relWrap" onclick="focusTagInput('relInput')">
                                    <input class="tag-input-field" id="relInput" placeholder="e.g. rest, medication"
                                        onkeydown="addTag(event,'relWrap')">
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <label class="form-label-custom">Associated Symptoms</label>
                            <div class="tag-input-wrap" id="assocWrap" onclick="focusTagInput('assocInput')">
                                <input class="tag-input-field" id="assocInput"
                                    placeholder="e.g. nausea, blurred vision" onkeydown="addTag(event,'assocWrap')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EXAMINATION -->
            <div class="card-section" id="secExam">
                <div class="card-header" onclick="toggleCard('secExam')">
                    <div class="card-icon icon-green"><i class="bi bi-stethoscope"></i></div>
                    <div class="card-title">Examination</div>
                    <span class="card-subtitle" id="examSpecialtyLabel">General Medicine</span>
                    <button class="exam-expand-link" style="margin:0;margin-left:8px;font-size:12px;"
                        onclick="event.stopPropagation();openAddSystemModal()">
                        <i class="bi bi-plus-circle"></i> Add system
                    </button>
                    <i class="bi bi-chevron-down collapse-icon" style="margin-left:8px;"></i>
                </div>
                <div class="card-body">
                    <div id="genAppearanceRow" style="margin-bottom:12px;">
                        <label class="form-label-custom">General Appearance</label>
                        <div class="radio-group">
                            <label class="radio-opt"><input type="radio" name="gen" value="well" checked>
                                Well</label>
                            <label class="radio-opt"><input type="radio" name="gen" value="ill">
                                Ill-looking</label>
                            <label class="radio-opt"><input type="radio" name="gen" value="distress"> In
                                distress</label>
                            <label class="radio-opt"><input type="radio" name="gen" value="toxic">
                                Toxic</label>
                        </div>
                    </div>
                    <div class="exam-grid" id="examSystemsGrid"></div>
                </div>
            </div>

            <!-- DIAGNOSIS -->
            <div class="card-section" id="secDiag">
                <div class="card-header" onclick="toggleCard('secDiag')">
                    <div class="card-icon icon-warn"><i class="bi bi-clipboard2-pulse"></i></div>
                    <div class="card-title">Diagnosis</div>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="card-body" id="diagBody">
                    <div class="diag-header-row">
                        <div style="flex:1;min-width:0;">Diagnosis</div>
                        <div style="width:86px;">ICD-10</div>
                        <div style="width:106px;">Type</div>
                        <div style="width:90px;">Status</div>
                        <div style="width:24px;"></div>
                    </div>
                    <div id="diagRows"></div>
                    <button class="add-diag-btn" onclick="addDiagRow()"><i class="bi bi-plus-circle"></i> Add
                        diagnosis</button>
                </div>
            </div>

            <!-- INVESTIGATIONS -->
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
                            <input class="tag-input-field" id="invInput"
                                placeholder="Type test name and press Enter"
                                onkeydown="addTag(event,'invWrap','#e6f7f1','#2d8a6a')">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">Special Instructions <span
                                style="font-weight:400;text-transform:none;font-size:12px;color:var(--steel-light);">(optional)</span></label>
                        <input type="text" class="form-ctrl" id="invInstructions"
                            placeholder="e.g. Fasting sample required">
                    </div>
                </div>
            </div>

            <!-- PRESCRIPTION -->
            <div class="card-section" id="secRx">
                <div class="card-header" onclick="toggleCard('secRx')">
                    <div class="card-icon"
                        style="background:var(--blue-deep);color:white;font-weight:700;font-family:'Source Sans 3',sans-serif;font-size:13px;display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;flex-shrink:0;">
                        Rx</div>
                    <div class="card-title">Prescription</div>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="card-body">
                    <div class="rx-col-headers">
                        <div>Medicine</div>
                        <div>Pack</div>
                        <div>Frequency</div>
                        <div>Duration</div>
                        <div>Instructions</div>
                        <div></div>
                    </div>
                    <div id="rxRows"></div>
                    <button class="add-rx-btn" onclick="addRxRow()"><i class="bi bi-plus-circle"></i> Add
                        medicine</button>
                    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);">
                        <label class="form-label-custom">Advice / Instructions to Patient</label>
                        <textarea class="form-ctrl" id="adviceText" rows="2"
                            placeholder="Dietary advice, lifestyle modifications, what to watch for…"></textarea>
                    </div>
                </div>
            </div>

            <div style="height:8px;"></div>
        </div><!-- /main-content -->

        <!-- RIGHT SIDEBAR -->
        <div class="sidebar-right">
            <!-- Vitals Summary -->
            <div>
                <div class="summary-block-title">Vitals Summary</div>
                <div class="summary-item"><span class="summary-key">BP</span><span class="summary-val"
                        id="sumBp">–</span></div>
                <div class="summary-item"><span class="summary-key">HR</span><span class="summary-val"
                        id="sumHr">–</span></div>
                <div class="summary-item"><span class="summary-key">SpO₂</span><span class="summary-val"
                        id="sumSpo2">–</span></div>
                <div class="summary-item"><span class="summary-key">Temp</span><span class="summary-val"
                        id="sumTemp">–</span></div>
                <div class="summary-item"><span class="summary-key">BMI</span><span class="summary-val"
                        id="sumBmi">–</span></div>
                <div class="summary-item"><span class="summary-key">GRBS</span><span class="summary-val"
                        id="sumGrbs">–</span></div>
            </div>
            <!-- Follow-up -->
            <div id="followUpBlock">
                <div class="summary-block-title">Follow-up</div>
                <div class="followup-picker" id="followupPicker">
                    <div class="followup-opt" data-fu="1 Week">1 Week</div>
                    <div class="followup-opt" data-fu="4 Weeks">4 Weeks</div>
                    <div class="followup-opt" data-fu="3 Months">3 Months</div>
                    <div class="followup-opt" data-fu="SOS">SOS</div>
                </div>
                <input type="date" class="form-ctrl" id="inp_follow_up_date"
                    style="margin-top:8px;font-size:13px;">
            </div>
            <!-- Referral -->
            <div id="referralBlock">
                <div class="summary-block-title">Referral</div>
                <select class="form-ctrl" id="inp_referral_dept" style="font-size:13px;">
                    <option value="">— None —</option>
                </select>
                <input type="text" class="form-ctrl" id="inp_referral_note"
                    style="margin-top:8px;font-size:13px;" placeholder="Referral note…">
            </div>
            <!-- Quick Actions -->
            <div>
                <div class="summary-block-title">Quick Actions</div>
                <div class="quick-actions">
                    <a id="qaPrint" href="#" target="_blank" class="quick-btn disabled-btn"><i
                            class="bi bi-printer"></i> Print</a>
                    <a id="qaPdf" href="#" class="quick-btn disabled-btn"><i
                            class="bi bi-file-earmark-arrow-down"></i> Download PDF</a>
                    <form id="emailForm" method="POST" action="#" style="margin:0;">
                        @csrf
                        <button id="qaEmail" type="submit" class="quick-btn disabled-btn" style="width:100%;"><i
                                class="bi bi-envelope-at"></i> Email to Patient</button>
                    </form>
                </div>
            </div>
            <!-- Doctor Note -->
            <div id="doctorNoteBlock">
                <div class="summary-block-title">Doctor's Note (Internal)</div>
                <textarea class="form-ctrl" id="doctorNote" rows="3" style="font-size:13px;"
                    placeholder="Private notes, not printed on prescription"></textarea>
            </div>
        </div>
    </div><!-- /main-layout -->

    <!-- BOTTOM BAR -->
    <div class="bottom-bar">
        <div class="progress-steps">
            <div class="step done">
                <div class="step-dot"><i class="bi bi-check"></i></div><span>Vitals</span>
            </div>
            <div class="step-line done"></div>
            <div class="step done">
                <div class="step-dot"><i class="bi bi-check"></i></div><span>Complaints</span>
            </div>
            <div class="step-line done"></div>
            <div class="step active">
                <div class="step-dot">3</div><span>Diagnosis</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-dot">4</div><span>Rx</span>
            </div>
        </div>
        <button class="btn-secondary-custom" id="saveDraftBtn" onclick="collectAndSubmit('draft',false)"><i
                class="bi bi-cloud-arrow-up"></i> Save Draft</button>
        <button class="btn-secondary-custom" id="draftPrintBtn" onclick="collectAndSubmit('draft',true)"><i
                class="bi bi-printer"></i> Save Draft &amp; Print Case Sheet</button>
        <button class="btn-primary-custom" id="finalizeBtn" onclick="collectAndSubmit('finalized',false)"><i
                class="bi bi-check2-circle"></i> Finalise</button>
        <button class="btn-primary-custom" id="finalizePrintBtn" onclick="collectAndSubmit('finalized',true)"
            style="background:var(--success);box-shadow:none;"><i class="bi bi-printer"></i> Finalise &amp;
            Print</button>
        <button class="btn-danger-outline" onclick="window.history.back()"><i class="bi bi-x-circle"></i>
            Discard</button>
    </div>

    <!-- MODAL: Add Examination System -->
    <div class="modal-backdrop-custom" id="addSystemModal">
        <div class="modal-box" style="width:520px;">
            <div class="modal-head">
                <div class="card-icon icon-green"><i class="bi bi-stethoscope"></i></div>
                <div class="modal-title">Add Examination System</div>
                <button class="modal-close" onclick="closeModal('addSystemModal')"><i
                        class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:var(--steel);margin-bottom:14px;">Select systems to add. Already-active
                    systems are checked.</p>
                <div class="system-picker-grid" id="systemPickerGrid"></div>
                <div>
                    <label class="form-label-custom">Custom system name</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" class="form-ctrl" id="customSystemName"
                            placeholder="e.g. Perianal, Lymph nodes" style="flex:1;">
                        <button class="btn-secondary-custom" onclick="addCustomSystem()"><i class="bi bi-plus"></i>
                            Add</button>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn-secondary-custom" onclick="closeModal('addSystemModal')">Cancel</button>
                <button class="btn-primary-custom" onclick="applySystemPicker()"><i class="bi bi-check2"></i>
                    Apply</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Full History -->
    <div class="modal-backdrop-custom" id="histModal">
        <div class="modal-box">
            <div class="modal-head">
                <div class="card-icon icon-blue"><i class="bi bi-clock-history"></i></div>
                <div class="modal-title" id="histModalTitle">Patient History</div>
                <button class="modal-close" onclick="closeModal('histModal')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="row-3" style="margin-bottom:16px;">
                    <div>
                        <label class="form-label-custom">Past Medical History</label>
                        <div class="tag-input-wrap" id="pmhWrap" onclick="focusTagInput('pmhInput')">
                            <input class="tag-input-field" id="pmhInput" placeholder="Add"
                                onkeydown="addTag(event,'pmhWrap')">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">Surgical History</label>
                        <div class="tag-input-wrap" id="surgWrap" onclick="focusTagInput('surgInput')">
                            <input class="tag-input-field" id="surgInput" placeholder="Add"
                                onkeydown="addTag(event,'surgWrap','var(--steel-bg)','var(--steel-dark)')">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">Family History</label>
                        <div class="tag-input-wrap" id="famWrap" onclick="focusTagInput('famInput')">
                            <input class="tag-input-field" id="famInput" placeholder="Add"
                                onkeydown="addTag(event,'famWrap','var(--steel-bg)','var(--steel-dark)')">
                        </div>
                    </div>
                </div>
                <div class="row-3">
                    <div>
                        <label class="form-label-custom">Drug Allergies</label>
                        <div class="tag-input-wrap" id="allergyWrap" onclick="focusTagInput('allergyInput')">
                            <input class="tag-input-field" id="allergyInput" placeholder="Add"
                                onkeydown="addTag(event,'allergyWrap','#fdecea','#c0392b')">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">Chronic Conditions</label>
                        <div class="tag-input-wrap" id="chronicWrap" onclick="focusTagInput('chronicInput')">
                            <input class="tag-input-field" id="chronicInput" placeholder="Add"
                                onkeydown="addTag(event,'chronicWrap','#fff8e6','#a0660a')">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-custom">Ongoing Medications</label>
                        <div class="tag-input-wrap" id="ongoingMedWrap" onclick="focusTagInput('ongoingMedInput')">
                            <input class="tag-input-field" id="ongoingMedInput" placeholder="Add"
                                onkeydown="addTag(event,'ongoingMedWrap')">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn-secondary-custom" onclick="closeModal('histModal')">Close</button>
                <button class="btn-primary-custom" onclick="closeModal('histModal');showToast('History saved.')"><i
                        class="bi bi-save"></i> Save History</button>
            </div>
        </div>
    </div>

    <script>
        const CONSULTATION_PAYLOAD = @json($consultationPayload);
    </script>
    <script>
        /* =====================================================================
       UTILITY
    ===================================================================== */
        function escHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function highlightMatch(text, q) {
            const s = escHtml(text),
                sq = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return s.replace(new RegExp('(' + sq + ')', 'gi'), '<em>$1</em>');
        }

        function showToast(msg) {
            const t = document.getElementById('toastMsg');
            document.getElementById('toastText').textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        function toggleCard(id) {
            const sec = document.getElementById(id),
                body = sec.querySelector('.card-body'),
                hdr = sec.querySelector('.card-header');
            const open = sec.dataset.open !== 'false';
            if (open) {
                body.style.display = 'none';
                hdr.classList.add('collapsed');
                sec.dataset.open = 'false';
            } else {
                body.style.display = '';
                hdr.classList.remove('collapsed');
                sec.dataset.open = 'true';
            }
        }

        function toggleSection(id, btn) {
            const el = document.getElementById(id);
            const hidden = el.dataset.visible !== 'true';
            if (hidden) {
                el.style.display = '';
                el.dataset.visible = 'true';
                if (btn) {
                    const i = btn.querySelector('i');
                    if (i) i.className = 'bi bi-dash-circle';
                }
            } else {
                el.style.display = 'none';
                el.dataset.visible = 'false';
                if (btn) {
                    const i = btn.querySelector('i');
                    if (i) i.className = 'bi bi-plus-circle';
                }
            }
        }

        function toggleVitalsExtra(btn) {
            const ex = document.getElementById('vitalsExtra');
            if (ex.classList.contains('visible')) {
                ex.classList.remove('visible');
                btn.innerHTML = '<i class="bi bi-plus-circle"></i> More vitals (Weight, Height, BMI, RR, GRBS…)';
            } else {
                ex.classList.add('visible');
                btn.innerHTML = '<i class="bi bi-dash-circle"></i> Hide extra vitals';
            }
        }

        function openModal(id) {
            document.getElementById(id).classList.add('visible');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('visible');
        }
        document.querySelectorAll('.modal-backdrop-custom').forEach(m => m.addEventListener('click', e => {
            if (e.target === m) m.classList.remove('visible');
        }));

        function focusTagInput(id) {
            const el = document.getElementById(id);
            if (el) el.focus();
        }

        function addTag(e, wrapId, bg, color) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const input = e.target,
                text = input.value.trim();
            if (!text) return;
            const wrap = document.getElementById(wrapId);
            const pill = document.createElement('span');
            pill.className = 'tag-pill';
            if (bg) pill.style.background = bg;
            if (color) pill.style.color = color;
            pill.innerHTML = `${escHtml(text)} <i class="bi bi-x" onclick="this.parentElement.remove()"></i>`;
            wrap.insertBefore(pill, input);
            input.value = '';
        }

        function getTags(wrapId) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return [];
            return [...wrap.querySelectorAll('.tag-pill')].map(el => el.firstChild.textContent.trim()).filter(Boolean);
        }

        function setTagWrap(wrapId, inputId, items, bg, color) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            wrap.querySelectorAll('.tag-pill').forEach(el => el.remove());
            const input = document.getElementById(inputId);
            (items || []).forEach(text => {
                const pill = document.createElement('span');
                pill.className = 'tag-pill';
                if (bg) pill.style.background = bg;
                if (color) pill.style.color = color;
                pill.innerHTML = `${escHtml(text)} <i class="bi bi-x" onclick="this.parentElement.remove()"></i>`;
                wrap.insertBefore(pill, input);
            });
        }

        /* =====================================================================
           VITALS
        ===================================================================== */
        function calcBmi() {
            const w = parseFloat(document.getElementById('vWeight').value);
            const h = parseFloat(document.getElementById('vHeight').value);
            const bmiEl = document.getElementById('vBmi');
            const lblEl = document.getElementById('bmiLabel');
            if (w > 0 && h > 0) {
                const bmi = (w / Math.pow(h / 100, 2)).toFixed(1);
                bmiEl.value = bmi;
                let cat = '';
                const b = parseFloat(bmi);
                if (b < 18.5) cat = 'Underweight';
                else if (b < 25) cat = 'Normal';
                else if (b < 30) cat = 'Overweight';
                else cat = 'Obese';
                lblEl.textContent = 'kg/m² — ' + cat;
                lblEl.style.color = b < 18.5 || b >= 25 ? 'var(--warning)' : 'var(--success)';
                document.getElementById('post_bmi').value = bmi;
                document.getElementById('sumBmi').textContent = bmi + ' (' + cat + ')';
                document.getElementById('sumBmi').className = 'summary-val ' + (b < 18.5 || b >= 25 ? 'abnormal' :
                'normal');
            } else {
                bmiEl.value = '';
                lblEl.textContent = 'kg/m²';
                lblEl.style.color = '';
            }
        }

        function setDot(id, cls) {
            const d = document.getElementById(id);
            if (!d) return;
            d.className = 'vital-status' + (cls ? ' ' + cls : '');
        }

        function onVitalChange() {
            const sys = +document.getElementById('bpSys').value;
            const dia = +document.getElementById('bpDia').value;
            const hr = +document.getElementById('hr').value;
            const spo = +document.getElementById('spo2').value;
            const tmp = +document.getElementById('temp').value;
            const grbs = +document.getElementById('vGrbs').value;
            setDot('bpDot', sys > 140 || dia > 90 ? 'alert' : (sys > 130 || dia > 85 ? 'warn' : ''));
            setDot('hrDot', hr < 60 || hr > 100 ? 'alert' : '');
            setDot('spoDot', spo < 95 ? 'alert' : (spo < 97 ? 'warn' : ''));
            setDot('tempDot', tmp > 99.5 ? 'alert' : (tmp > 99 ? 'warn' : ''));
            setDot('grbsDot', grbs > 180 ? 'alert' : (grbs > 140 ? 'warn' : ''));
            // update sidebar summary
            const bpStr = (sys && dia) ? sys + ' / ' + dia : '–';
            const bpCls = sys > 140 || dia > 90 ? 'abnormal' : 'normal';
            document.getElementById('sumBp').textContent = bpStr;
            document.getElementById('sumBp').className = 'summary-val ' + bpCls;
            document.getElementById('sumHr').textContent = hr ? hr + ' bpm' : '–';
            document.getElementById('sumHr').className = 'summary-val ' + (hr < 60 || hr > 100 ? 'abnormal' : 'normal');
            document.getElementById('sumSpo2').textContent = spo ? spo + '%' : '–';
            document.getElementById('sumSpo2').className = 'summary-val ' + (spo < 95 ? 'abnormal' : 'normal');
            document.getElementById('sumTemp').textContent = tmp ? tmp + '°F' : '–';
            document.getElementById('sumTemp').className = 'summary-val ' + (tmp > 99.5 ? 'abnormal' : 'normal');
            document.getElementById('sumGrbs').textContent = grbs ? grbs + ' mg/dL' : '–';
            document.getElementById('sumGrbs').className = 'summary-val ' + (grbs > 140 ? 'abnormal' : 'normal');
            // update vitals summary header
            document.getElementById('vitals-summary').textContent =
                (sys && dia ? 'BP ' + sys + '/' + dia + ' ' : '') +
                (hr ? 'HR ' + hr + ' ' : '') +
                (spo ? 'SpO₂ ' + spo + '% ' : '');
        }

        /* =====================================================================
           SPECIALTY / EXAMINATION ENGINE
        ===================================================================== */
        const ALL_SYSTEMS = [{
                id: 'cvs',
                label: 'Cardiovascular',
                icon: 'bi-heart-pulse',
                ph: 'Heart sounds, murmurs, JVP',
                norm: true
            },
            {
                id: 'resp',
                label: 'Respiratory',
                icon: 'bi-lungs',
                ph: 'Air entry, wheeze, crepts',
                norm: true
            },
            {
                id: 'abd',
                label: 'Abdomen',
                icon: 'bi-body-text',
                ph: 'Tenderness, organomegaly, bowel sounds',
                norm: true
            },
            {
                id: 'neuro',
                label: 'Neurological',
                icon: 'bi-braces',
                ph: 'Power, tone, reflexes, cranial nerves',
                norm: true
            },
            {
                id: 'msk',
                label: 'Musculoskeletal',
                icon: 'bi-person-arms-up',
                ph: 'ROM, swelling, tenderness',
                norm: true
            },
            {
                id: 'ent',
                label: 'ENT',
                icon: 'bi-ear',
                ph: 'Ear canals, tympanic membrane, throat',
                norm: true
            },
            {
                id: 'eyes',
                label: 'Eyes',
                icon: 'bi-eye',
                ph: 'Visual acuity, pupils, fundoscopy',
                norm: true
            },
            {
                id: 'skin',
                label: 'Skin',
                icon: 'bi-droplet',
                ph: 'Rash, lesions, texture, colour',
                norm: true
            },
            {
                id: 'thyroid',
                label: 'Thyroid / Neck',
                icon: 'bi-person-fill',
                ph: 'Goitre, nodes, tracheal deviation',
                norm: true
            },
            {
                id: 'lymph',
                label: 'Lymph Nodes',
                icon: 'bi-dot',
                ph: 'Cervical, axillary, inguinal',
                norm: true
            },
            {
                id: 'vascular',
                label: 'Vascular',
                icon: 'bi-bezier',
                ph: 'Pulses, capillary refill, oedema',
                norm: true
            },
            {
                id: 'va',
                label: 'Visual Acuity',
                icon: 'bi-eye',
                ph: 'RE: 6/6, LE: 6/9 (Snellen)',
                norm: false
            },
            {
                id: 'iop',
                label: 'IOP',
                icon: 'bi-circle-half',
                ph: 'RE: 14 mmHg, LE: 16 mmHg',
                norm: false
            },
            {
                id: 'fundus',
                label: 'Fundoscopy',
                icon: 'bi-search',
                ph: 'Disc, macula, vessels',
                norm: true
            },
            {
                id: 'spine',
                label: 'Spine',
                icon: 'bi-arrow-up',
                ph: 'Scoliosis, kyphosis, ROM, tenderness',
                norm: true
            },
            {
                id: 'upper_limb',
                label: 'Upper Limb',
                icon: 'bi-hand-index',
                ph: 'Shoulder, elbow, wrist, hand',
                norm: true
            },
            {
                id: 'lower_limb',
                label: 'Lower Limb',
                icon: 'bi-person-standing',
                ph: 'Hip, knee, ankle, foot',
                norm: true
            },
            {
                id: 'pv',
                label: 'Per Vaginal',
                icon: 'bi-gender-female',
                ph: 'Cervix, uterus, adnexa, discharge',
                norm: true
            },
            {
                id: 'breast',
                label: 'Breast',
                icon: 'bi-circle',
                ph: 'Lump, nipple discharge, skin changes',
                norm: true
            },
            {
                id: 'mse_mood',
                label: 'Mood & Affect',
                icon: 'bi-emoji-neutral',
                ph: 'Subjective mood, observed affect',
                norm: false
            },
            {
                id: 'mse_thought',
                label: 'Thought',
                icon: 'bi-diagram-3',
                ph: 'Form, content, delusions',
                norm: false
            },
            {
                id: 'gait',
                label: 'Gait',
                icon: 'bi-person-walking',
                ph: 'Antalgic, Trendelenburg, ataxic',
                norm: false
            },
        ];
        const SPECIALTIES = [{
                id: 'general',
                label: 'General Medicine',
                showGen: true,
                systems: ['cvs', 'resp', 'abd', 'neuro', 'skin', 'lymph']
            },
            {
                id: 'cardiology',
                label: 'Cardiology',
                showGen: true,
                systems: ['cvs', 'resp', 'vascular', 'lymph', 'abd']
            },
            {
                id: 'ortho',
                label: 'Orthopaedics',
                showGen: false,
                systems: ['gait', 'spine', 'upper_limb', 'lower_limb', 'msk', 'vascular']
            },
            {
                id: 'ophthalmology',
                label: 'Ophthalmology',
                showGen: false,
                systems: ['va', 'iop', 'eyes', 'fundus']
            },
            {
                id: 'ent',
                label: 'ENT',
                showGen: true,
                systems: ['ent', 'thyroid', 'lymph', 'neuro']
            },
            {
                id: 'gynaecology',
                label: 'Gynaecology',
                showGen: true,
                systems: ['abd', 'pv', 'breast', 'lymph']
            },
            {
                id: 'psychiatry',
                label: 'Psychiatry',
                showGen: false,
                systems: ['mse_mood', 'mse_thought']
            },
            {
                id: 'dermatology',
                label: 'Dermatology',
                showGen: true,
                systems: ['skin', 'lymph', 'msk']
            },
        ];
        let currentSpecialty = SPECIALTIES[0];
        let activeSystemIds = [...currentSpecialty.systems];
        let pickerSelected = [];

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
                let html = `<div class="exam-system-header"><i class="bi ${sys.icon}"></i><span>${sys.label}</span>
      <button class="exam-system-remove" title="Remove" onclick="removeExamSystem('${sys.id}')"><i class="bi bi-x"></i></button></div>
      <div class="exam-system-body">`;
                if (sys.norm) html += `<div class="radio-group">
      <label class="radio-opt"><input type="radio" name="${uid}" value="normal" checked> Normal</label>
      <label class="radio-opt"><input type="radio" name="${uid}" value="abnormal"> Abnormal</label></div>`;
                html += `<input class="exam-note-inline" type="text" placeholder="${sys.ph}"></div>`;
                card.innerHTML = html;
                grid.appendChild(card);
            });
        }

        function removeExamSystem(id) {
            activeSystemIds = activeSystemIds.filter(s => s !== id);
            renderExamSystems();
        }

        function openAddSystemModal() {
            pickerSelected = [...activeSystemIds];
            const grid = document.getElementById('systemPickerGrid');
            grid.innerHTML = '';
            ALL_SYSTEMS.forEach(sys => {
                const isActive = pickerSelected.includes(sys.id);
                const item = document.createElement('div');
                item.className = 'sys-pick-item' + (isActive ? ' selected' : '');
                item.dataset.sysId = sys.id;
                item.innerHTML =
                    `<i class="bi ${sys.icon}"></i><span style="flex:1;">${sys.label}</span><i class="bi bi-check2 sys-check"></i>`;
                item.onclick = () => {
                    item.classList.toggle('selected');
                    if (item.classList.contains('selected')) {
                        if (!pickerSelected.includes(sys.id)) pickerSelected.push(sys.id);
                    } else {
                        pickerSelected = pickerSelected.filter(s => s !== sys.id);
                    }
                };
                grid.appendChild(item);
            });
            openModal('addSystemModal');
        }

        function addCustomSystem() {
            const inp = document.getElementById('customSystemName');
            const name = inp.value.trim();
            if (!name) return;
            const id = 'custom_' + Date.now();
            ALL_SYSTEMS.push({
                id,
                label: name,
                icon: 'bi-plus-square',
                ph: 'Findings',
                norm: true
            });
            pickerSelected.push(id);
            inp.value = '';
            openAddSystemModal();
        }

        function applySystemPicker() {
            activeSystemIds = [...pickerSelected];
            renderExamSystems();
            closeModal('addSystemModal');
        }

        /* =====================================================================
           ICD-10 TYPEAHEAD
        ===================================================================== */
        let diagCount = 0;
        const icdTimers = {};
        const icdFocusIdx = {};

        function addDiagRow() {
            createDiagRow();
        }

        function createDiagRow(prefillName, prefillCode, prefillType, prefillStatus) {
            diagCount++;
            const id = diagCount;
            const row = document.createElement('div');
            row.className = 'diag-row';
            row.id = `diagRow${id}`;
            row.innerHTML =
                `
    <div class="icd-wrap" id="icdWrap${id}">
      <input class="icd-input" id="icdInput${id}" type="text" autocomplete="off"
        placeholder="Search diagnosis or ICD-10 code" value="${escHtml(prefillName||'')}"
        oninput="onIcdInput(${id})" onkeydown="onIcdKey(event,${id})"
        onblur="onIcdBlur(${id})" onfocus="if(this.value.length>1) openDrop(${id})">
      <div class="icd-dropdown" id="icdDrop${id}"></div>
    </div>
    <div class="icd-code-badge ${prefillCode?'':'empty'}" id="icdBadge${id}">${escHtml(prefillCode||'ICD-10')}</div>
    <select class="diag-select" id="diagType${id}" style="width:106px;">
      <option ${prefillType==='Primary'?'selected':''}>Primary</option>
      <option ${prefillType==='Secondary'?'selected':''}>Secondary</option>
      <option ${!prefillType||prefillType==='Provisional'?'selected':''}>Provisional</option>
      <option ${prefillType==='Differential'?'selected':''}>Differential</option>
    </select>
    <select class="diag-select" id="diagStatus${id}" style="width:90px;">
      <option ${!prefillStatus||prefillStatus==='Active'?'selected':''}>Active</option>
      <option ${prefillStatus==='Chronic'?'selected':''}>Chronic</option>
      <option ${prefillStatus==='Resolved'?'selected':''}>Resolved</option>
    </select>
    <button class="diag-del" onclick="document.getElementById('diagRow${id}').remove()" title="Remove"><i class="bi bi-trash3"></i></button>`;
            document.getElementById('diagRows').appendChild(row);
        }

        function onIcdInput(id) {
            setBadge(id, '', false);
            clearTimeout(icdTimers[id]);
            const val = document.getElementById(`icdInput${id}`).value.trim();
            if (val.length < 2) {
                closeDrop(id);
                return;
            }
            showIcdLoading(id);
            icdTimers[id] = setTimeout(() => fetchIcd(id, val), 280);
        }
        async function fetchIcd(id, query) {
            try {
                const res = await fetch(`${CONSULTATION_PAYLOAD.routes.icd10}?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                renderDrop(id, data.map(x => [x.code, x.name]), query);
            } catch (e) {
                renderDropError(id);
            }
        }

        function renderDrop(id, results, query) {
            const drop = document.getElementById(`icdDrop${id}`);
            icdFocusIdx[id] = -1;
            if (!results.length) {
                drop.innerHTML = `<div class="icd-empty">No results for "<strong>${escHtml(query)}</strong>"</div>`;
            } else {
                drop.innerHTML = results.map((r, i) => `<div class="icd-item" data-idx="${i}" data-code="${escHtml(r[0])}" data-name="${escHtml(r[1])}"
    onmousedown="pickIcd(event,${id},'${escHtml(r[0])}','${escHtml(r[1])}')"
    onmouseover="hoverIcdItem(${id},${i})">
    <span class="icd-code">${escHtml(r[0])}</span>
    <span class="icd-name">${highlightMatch(r[1],query)}</span></div>`).join('');
            }
            openDrop(id);
        }

        function renderDropError(id) {
            document.getElementById(`icdDrop${id}`).innerHTML =
                `<div class="icd-empty" style="color:var(--danger)">Could not reach ICD-10 service.</div>`;
            openDrop(id);
        }

        function showIcdLoading(id) {
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
            b.className = 'icd-code-badge' + (filled ? '' : ' empty');
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
            const drop = document.getElementById(`icdDrop${id}`),
                items = drop.querySelectorAll('.icd-item');
            if (!drop.classList.contains('open') || !items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                icdFocusIdx[id] = Math.min((icdFocusIdx[id] || 0) + 1, items.length - 1);
                updateIcdFocus(id, items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                icdFocusIdx[id] = Math.max((icdFocusIdx[id] || 0) - 1, 0);
                updateIcdFocus(id, items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const fi = icdFocusIdx[id];
                if (fi >= 0 && items[fi]) pickIcd(e, id, items[fi].dataset.code, items[fi].dataset.name);
            } else if (e.key === 'Escape') closeDrop(id);
        }

        function hoverIcdItem(id, idx) {
            icdFocusIdx[id] = idx;
            updateIcdFocus(id, document.getElementById(`icdDrop${id}`).querySelectorAll('.icd-item'));
        }

        function updateIcdFocus(id, items) {
            items.forEach((el, i) => el.classList.toggle('focused', i === icdFocusIdx[id]));
            const fi = icdFocusIdx[id];
            if (fi >= 0 && items[fi]) items[fi].scrollIntoView({
                block: 'nearest'
            });
        }

        /* =====================================================================
           MEDICINE TYPEAHEAD + DRAWER
        ===================================================================== */
        let rxCount = 0;
        const rxTimers = {};
        const rxFocusIdx = {};

        function addRxRow(prefill) {
            rxCount++;
            const id = rxCount;
            const entry = document.createElement('div');
            entry.className = 'rx-entry';
            entry.id = `rxEntry${id}`;
            entry.innerHTML =
                `
    <div class="rx-row">
      <div class="rx-drug-cell" id="rxDrugCell${id}">
        <input class="rx-drug-input" id="rxDrugInput${id}" type="text" autocomplete="off"
          placeholder="Type medicine name" value="${escHtml(prefill?prefill.name:'')}"
          oninput="onRxInput(${id})" onkeydown="onRxKey(event,${id})"
          onblur="onRxBlur(${id})" onfocus="if(this.value.length>1) openRxDrop(${id})">
        <div class="rx-drug-drop" id="rxDrop${id}"></div>
      </div>
      <div id="rxPack${id}" class="${prefill&&prefill.pack_size_label?'rx-pack-label':'rx-pack-empty'}">${escHtml(prefill&&prefill.pack_size_label?prefill.pack_size_label:'Select medicine first')}</div>
      <select class="rx-sel" id="rxFreq${id}">
        <option value="OD">OD – Once daily</option>
        <option value="BD">BD – Twice daily</option>
        <option value="TDS">TDS – Thrice daily</option>
        <option value="QID">QID – Four times</option>
        <option value="HS">HS – At bedtime</option>
        <option value="SOS">SOS – As needed</option>
        <option value="Stat">Stat – Immediately</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
      <input class="rx-dur-input" id="rxDur${id}" type="text" placeholder="e.g. 30 days" value="${escHtml(prefill&&prefill.dur?prefill.dur:'')}">
      <select class="rx-sel" id="rxInst${id}">
        <option value="">— Timing —</option>
        <option value="Before food">Before food</option>
        <option value="With food">With food</option>
        <option value="After food">After food</option>
        <option value="Empty stomach">Empty stomach</option>
        <option value="At bedtime">At bedtime</option>
        <option value="In the morning">In the morning</option>
      </select>
      <div class="rx-actions-cell">
        <button class="rx-info-btn" id="rxInfoBtn${id}" title="Drug info" onclick="toggleRxDrawer(${id})"><i class="bi bi-info-circle"></i></button>
        <button class="rx-del-btn" title="Remove" onclick="document.getElementById('rxEntry${id}').remove()"><i class="bi bi-trash3"></i></button>
      </div>
    </div>
    <div class="rx-detail-drawer" id="rxDrawer${id}"><div style="color:var(--steel-light);font-size:13px;font-style:italic;">Select a medicine to see details.</div></div>`;
            document.getElementById('rxRows').appendChild(entry);
            // Pre-select frequency / instruction if provided
            if (prefill) {
                if (prefill.frequency) {
                    const sel = document.getElementById(`rxFreq${id}`);
                    [...sel.options].forEach(o => {
                        if (o.value === prefill.frequency || o.text.startsWith(prefill.frequency)) sel.value = o
                            .value;
                    });
                }
                if (prefill.instruction) {
                    const sel = document.getElementById(`rxInst${id}`);
                    [...sel.options].forEach(o => {
                        if (o.value.toLowerCase() === String(prefill.instruction).toLowerCase()) sel.value = o
                        .value;
                    });
                }
                if (prefill.use) populateDrawer(id, {
                    name: prefill.name,
                    pack_size_label: prefill.pack_size_label || '',
                    form: prefill.form || '',
                    route: prefill.route || '',
                    manufacturer: prefill.manufacturer || '',
                    use: prefill.use || '',
                    therapeutic_class: prefill.therapeutic_class || '',
                    chemical_class: prefill.chemical_class || '',
                    side_effects: prefill.side_effects || [],
                    alternatives: prefill.alternatives || []
                });
                if (prefill.name) document.getElementById(`rxDrugInput${id}`).classList.add('selected-drug');
            }
        }

        function onRxInput(id) {
            const val = document.getElementById(`rxDrugInput${id}`).value.trim();
            document.getElementById(`rxDrugInput${id}`).classList.remove('selected-drug');
            document.getElementById(`rxPack${id}`).className = 'rx-pack-empty';
            document.getElementById(`rxPack${id}`).textContent = 'Select medicine first';
            clearTimeout(rxTimers[id]);
            if (val.length < 2) {
                closeRxDrop(id);
                return;
            }
            rxTimers[id] = setTimeout(() => searchMeds(id, val), 220);
        }

        function searchMeds(id, q) {
            fetch(`${CONSULTATION_PAYLOAD.routes.medicines}?q=${encodeURIComponent(q)}`)
                .then(r => r.json()).then(data => {
                    const mapped = data.map(x => ({
                        name: x.name,
                        pack_size_label: x.pack || '',
                        form: 'Tablet',
                        route: 'Oral',
                        manufacturer: x.manufacturer || '',
                        use: x.details || '',
                        therapeutic_class: '',
                        chemical_class: '',
                        side_effects: [],
                        alternatives: []
                    }));
                    renderRxDrop(id, mapped, q);
                }).catch(() => renderRxDropError(id));
        }

        function renderRxDrop(id, results, q) {
            const drop = document.getElementById(`rxDrop${id}`);
            drop._results = results;
            rxFocusIdx[id] = -1;
            if (!results.length) {
                drop.innerHTML =
                    `<div style="padding:10px 12px;font-size:12.5px;color:var(--steel-light);">No medicines found for "<strong>${escHtml(q)}</strong>"</div>`;
            } else {
                drop.innerHTML = results.map((m, i) =>
                    `<div class="rx-drug-item" data-idx="${i}"
    onmousedown="pickRxMed(event,${id},${i})" onmouseover="rxHover(${id},${i})">
    <div class="rx-drug-name">${highlightMatch(m.name,q)}</div>
    <div class="rx-drug-meta">${escHtml(m.pack_size_label)} &nbsp;·&nbsp; ${escHtml(m.form)}</div>
    <div class="rx-drug-mfr"><i class="bi bi-building" style="font-size:10px;"></i> ${escHtml(m.manufacturer)}</div></div>`).join('');
            }
            openRxDrop(id);
        }

        function renderRxDropError(id) {
            const d = document.getElementById(`rxDrop${id}`);
            d.innerHTML =
                `<div style="padding:10px 12px;font-size:12.5px;color:var(--danger);">Could not load medicines.</div>`;
            openRxDrop(id);
        }

        function pickRxMed(e, id, idx) {
            e.preventDefault();
            const med = document.getElementById(`rxDrop${id}`)._results[idx];
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
            pack.textContent = med.pack_size_label || '–';
            populateDrawer(id, med);
        }

        function populateDrawer(id, med) {
            const drawer = document.getElementById(`rxDrawer${id}`);
            const se = (med.side_effects || []).map(s => `<span class="drawer-tag warn">${escHtml(s)}</span>`).join('');
            const alts = (med.alternatives || []).map(a =>
                `<button class="alt-pill" onclick="swapAlt(${id},'${escHtml(a)}')" title="Switch to ${escHtml(a)}"><i class="bi bi-arrow-left-right"></i>${escHtml(a)}</button>`
                ).join('');
            drawer.innerHTML =
                `<div class="drawer-grid">
    <div><div class="drawer-block-title">Indication / Use</div><div class="drawer-val">${escHtml(med.use||'–')}</div></div>
    <div><div class="drawer-block-title">Therapeutic Class</div><div class="drawer-val"><span class="drawer-tag blue">${escHtml(med.therapeutic_class||'–')}</span></div>
      <div class="drawer-block-title" style="margin-top:10px;">Chemical Class</div><div class="drawer-val muted">${escHtml(med.chemical_class||'–')}</div></div>
    <div><div class="drawer-block-title">Manufacturer</div><div class="drawer-val">${escHtml(med.manufacturer||'–')}</div>
      <div class="drawer-block-title" style="margin-top:10px;">Form · Route</div><div class="drawer-val muted">${escHtml(med.form||'–')} · ${escHtml(med.route||'–')}</div></div>
  </div>${se?`<hr class="drawer-divider"><div class="drawer-block-title">Common Side Effects</div><div style="margin-top:4px;">${se}</div>`:''}
  ${alts?`<hr class="drawer-divider"><div class="drawer-block-title" style="margin-bottom:6px;">Alternative Brands – click to switch</div><div class="alternatives-row">${alts}</div>`:''}`;
        }

        function swapAlt(id, altName) {
            searchMeds(id, altName);
            document.getElementById(`rxDrugInput${id}`).value = altName;
        }

        function toggleRxDrawer(id) {
            const d = document.getElementById(`rxDrawer${id}`),
                b = document.getElementById(`rxInfoBtn${id}`);
            const o = d.classList.contains('open');
            d.classList.toggle('open', !o);
            b.classList.toggle('active', !o);
        }

        function openRxDrop(id) {
            document.getElementById(`rxDrop${id}`).classList.add('open');
        }

        function closeRxDrop(id) {
            document.getElementById(`rxDrop${id}`).classList.remove('open');
            rxFocusIdx[id] = -1;
        }

        function onRxBlur(id) {
            setTimeout(() => closeRxDrop(id), 180);
        }

        function onRxKey(e, id) {
            const drop = document.getElementById(`rxDrop${id}`),
                items = drop.querySelectorAll('.rx-drug-item');
            if (!drop.classList.contains('open') || !items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                rxFocusIdx[id] = Math.min((rxFocusIdx[id] || 0) + 1, items.length - 1);
                rxUpdateFocus(id, items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                rxFocusIdx[id] = Math.max((rxFocusIdx[id] || 0) - 1, 0);
                rxUpdateFocus(id, items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const fi = rxFocusIdx[id];
                if (fi >= 0 && items[fi]) pickRxMed(e, id, fi);
            } else if (e.key === 'Escape') closeRxDrop(id);
        }

        function rxHover(id, idx) {
            rxFocusIdx[id] = idx;
            rxUpdateFocus(id, document.getElementById(`rxDrop${id}`).querySelectorAll('.rx-drug-item'));
        }

        function rxUpdateFocus(id, items) {
            items.forEach((el, i) => el.classList.toggle('focused', i === rxFocusIdx[id]));
            const fi = rxFocusIdx[id];
            if (fi >= 0 && items[fi]) items[fi].scrollIntoView({
                block: 'nearest'
            });
        }

        /* =====================================================================
           COLLECT & SUBMIT
        ===================================================================== */
        function collectAndSubmit(status, printAfter) {
            const perms = CONSULTATION_PAYLOAD.permissions || {};
            if (!perms.can_edit) {
                showToast('This visit is view only.', 'warning');
                return;
            }
            if (status === 'finalized' && !perms.can_finalize) {
                showToast('Only doctor login can finalize this visit.', 'warning');
                return;
            }
            // visit setup
            document.getElementById('post_status').value = status;
            document.getElementById('post_print_after_save').value = printAfter ? '1' : '0';
            document.getElementById('post_doctor_id').value = CONSULTATION_PAYLOAD.consultation.doctor_id || '';
            document.getElementById('post_visit_date').value = document.getElementById('inp_visit_date').value || '';
            document.getElementById('post_visit_time').value = document.getElementById('inp_visit_time').value || '';
            document.getElementById('post_token_number').value = document.getElementById('inp_token_number').value || '';
            // complaints
            document.getElementById('post_duration_value').value = document.getElementById('durationValue').value || '';
            document.getElementById('post_duration_unit').value = document.getElementById('durationUnit').value || '';
            document.getElementById('post_hpi').value = document.getElementById('hpiText').value || '';
            // general appearance
            document.getElementById('post_general_appearance').value = document.querySelector('input[name="gen"]:checked')
                ?.value || 'well';
            // follow-up
            document.getElementById('post_follow_up_label').value = document.querySelector('.followup-opt.selected')
                ?.dataset.fu || '4 Weeks';
            document.getElementById('post_follow_up_date').value = document.getElementById('inp_follow_up_date').value ||
            '';
            // referral
            document.getElementById('post_referral_department').value = document.getElementById('inp_referral_dept')
                .value || '';
            document.getElementById('post_referral_note').value = document.getElementById('inp_referral_note').value || '';
            // other
            document.getElementById('post_investigation_instructions').value = document.getElementById('invInstructions')
                .value || '';
            document.getElementById('post_advice').value = document.getElementById('adviceText').value || '';
            document.getElementById('post_doctor_note').value = document.getElementById('doctorNote').value || '';
            // vitals
            document.getElementById('post_bp_systolic').value = document.getElementById('bpSys').value || '';
            document.getElementById('post_bp_diastolic').value = document.getElementById('bpDia').value || '';
            document.getElementById('post_heart_rate').value = document.getElementById('hr').value || '';
            document.getElementById('post_spo2').value = document.getElementById('spo2').value || '';
            document.getElementById('post_temperature').value = document.getElementById('temp').value || '';
            document.getElementById('post_weight').value = document.getElementById('vWeight').value || '';
            document.getElementById('post_height').value = document.getElementById('vHeight').value || '';
            document.getElementById('post_bmi').value = document.getElementById('vBmi').value || '';
            document.getElementById('post_respiratory_rate').value = document.getElementById('vRr').value || '';
            document.getElementById('post_grbs').value = document.getElementById('vGrbs').value || '';
            document.getElementById('post_waist_circumference').value = document.getElementById('vWaist').value || '';
            document.getElementById('post_pain_score').value = document.getElementById('vPain').value || '';
            document.getElementById('post_gcs').value = document.getElementById('vGcs').value || '';
            // tags
            document.getElementById('post_chief_complaints_json').value = JSON.stringify(getTags('complaintWrap'));
            document.getElementById('post_aggravating_factors_json').value = JSON.stringify(getTags('aggWrap'));
            document.getElementById('post_relieving_factors_json').value = JSON.stringify(getTags('relWrap'));
            document.getElementById('post_associated_symptoms_json').value = JSON.stringify(getTags('assocWrap'));
            document.getElementById('post_past_medical_history_json').value = JSON.stringify(getTags('pmhWrap'));
            document.getElementById('post_surgical_history_json').value = JSON.stringify(getTags('surgWrap'));
            document.getElementById('post_family_history_json').value = JSON.stringify(getTags('famWrap'));
            document.getElementById('post_drug_allergies_json').value = JSON.stringify(getTags('allergyWrap'));
            document.getElementById('post_chronic_conditions_json').value = JSON.stringify(getTags('chronicWrap'));
            document.getElementById('post_ongoing_medications_json').value = JSON.stringify(getTags('ongoingMedWrap'));
            // diagnoses
            const diags = [...document.querySelectorAll('#diagRows .diag-row')].map(row => ({
                diagnosis_name: row.querySelector('.icd-input')?.value || '',
                icd10_code: (() => {
                    const b = row.querySelector('.icd-code-badge');
                    return b && b.textContent !== 'ICD-10' ? b.textContent : '';
                })(),
                diagnosis_type: row.querySelectorAll('.diag-select')[0]?.value || 'Provisional',
                clinical_status: row.querySelectorAll('.diag-select')[1]?.value || 'Active',
            })).filter(x => x.diagnosis_name);
            document.getElementById('post_diagnoses_json').value = JSON.stringify(diags);
            // examinations – read label from span inside header, not childNodes (fixes text node bug)
            const exams = [...document.querySelectorAll('#examSystemsGrid .exam-system')].map(card => ({
                system_name: card.querySelector('.exam-system-header span')?.textContent.trim() || '',
                finding_status: card.querySelector('input[type="radio"]:checked')?.value || 'normal',
                notes: card.querySelector('.exam-note-inline')?.value || '',
            })).filter(x => x.system_name);
            document.getElementById('post_examinations_json').value = JSON.stringify(exams);
            // investigations
            document.getElementById('post_investigations_json').value = JSON.stringify(getTags('invWrap').map(x => ({
                test_name: x
            })));
            // prescriptions – collect directly from row inputs (not from drawer innerText)
            const meds = [...document.querySelectorAll('#rxRows .rx-entry')].map(row => {
                const nameEl = row.querySelector('.rx-drug-input');
                const packEl = row.querySelector('[id^="rxPack"]');
                const freqEl = row.querySelector('.rx-sel');
                const durEl = row.querySelector('.rx-dur-input');
                const instEl = row.querySelectorAll('.rx-sel')[1];
                return {
                    medicine_name: nameEl?.value || '',
                    pack: packEl?.textContent === 'Select medicine first' ? '' : packEl?.textContent || '',
                    frequency: freqEl?.value || '',
                    duration: durEl?.value || '',
                    instruction: instEl?.value || '',
                    details: '',
                };
            }).filter(x => x.medicine_name);
            document.getElementById('post_prescriptions_json').value = JSON.stringify(meds);
            document.getElementById('consultation-post-form').submit();
        }

        /* =====================================================================
           HYDRATE from CONSULTATION_PAYLOAD
        ===================================================================== */
        function setBlockVisibility(id, shouldShow) {
            const el = document.getElementById(id);
            if (el) el.style.display = shouldShow ? '' : 'none';
        }

        function setReadOnlyMode() {
            document.querySelectorAll('#mainContent input, #mainContent textarea, #mainContent select, .sidebar-right input, .sidebar-right textarea, .sidebar-right select, #histModal input, #histModal textarea, #histModal select').forEach(el => {
                el.disabled = true;
                el.readOnly = true;
            });
            document.querySelectorAll('#mainContent button, .sidebar-right button, #histModal button').forEach(btn => {
                if (btn.id !== 'closeHistoryBtn' && btn.id !== 'closeWindowBtn') {
                    btn.disabled = true;
                }
            });
        }

        function setHistoryReadOnly() {
            document.querySelectorAll('#histModal input, #histModal textarea, #histModal select').forEach(el => {
                el.disabled = true;
                el.readOnly = true;
            });
            const saveHistoryBtn = document.getElementById('saveHistoryBtn');
            if (saveHistoryBtn) saveHistoryBtn.style.display = 'none';
        }

        function applyConsultationWorkflow(p) {
            const perms = p.permissions || {};
            const isFinalized = !!perms.is_finalized;
            const isReception = !!perms.is_reception;
            const vitalsOnly = !!perms.vitals_only;
            const notice = document.getElementById('workflowNotice');
            const printLabel = isFinalized ? 'Print Prescription' : 'Print Case Sheet';

            [['bannerPrintBtn', '<i class="bi bi-printer"></i> ' + printLabel], ['qaPrint', '<i class="bi bi-printer"></i> ' + printLabel]].forEach(([id, html]) => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = html;
            });

            setBlockVisibility('saveDraftBtn', !!perms.can_edit);
            setBlockVisibility('draftPrintBtn', !!perms.can_edit && isReception);
            setBlockVisibility('finalizeBtn', !!perms.can_finalize);
            setBlockVisibility('finalizePrintBtn', !!perms.can_finalize);

            if (vitalsOnly) {
                ['secComplaints', 'secExam', 'secDiag', 'secInv', 'secRx'].forEach(id => setBlockVisibility(id, false));
                setBlockVisibility('followUpBlock', false);
                setBlockVisibility('referralBlock', false);
                setBlockVisibility('doctorNoteBlock', false);
                setHistoryReadOnly();
                if (notice) {
                    notice.style.display = '';
                    notice.textContent = 'Receptionist mode: only visit setup, vitals, draft save, and case sheet print are allowed until the doctor completes the consultation.';
                }
            } else if (!perms.can_edit) {
                setReadOnlyMode();
                setHistoryReadOnly();
                if (notice) {
                    notice.style.display = '';
                    notice.textContent = isFinalized
                        ? 'This visit has been finalized by the doctor. It is now view/print only.'
                        : 'This visit is view only.';
                }
            } else if (perms.is_doctor && notice && p.consultation.receptionist_updated_at) {
                notice.style.display = '';
                notice.textContent = 'Reception vitals draft is ready. Doctor can complete the remaining consultation details and finalize the visit.';
            }
        }

        function hydrateConsultation() {
            const p = CONSULTATION_PAYLOAD;
            // nav
            document.title = 'OP Consultation – ' + (p.patient.name || 'Patient');
            document.getElementById('navContext').textContent = 'Outpatient · ' + (p.doctor?.name || 'Doctor Consultation');
            const docInitials = ((p.doctor?.name || 'DR').split(' ').map(x => x[0]).slice(0, 2).join('')).toUpperCase();
            document.getElementById('navAvatar').textContent = docInitials;
            document.getElementById('navAvatar').title = p.doctor?.name || '';
            if (p.consultation.token_number) document.getElementById('navBadge').innerHTML =
                `<i class="bi bi-hash me-1"></i>Token ${p.consultation.token_number}`;
            // patient banner
            const pi = ((p.patient.name || 'PT').split(' ').map(x => x[0]).slice(0, 2).join('')).toUpperCase();
            document.getElementById('patientAvatar').textContent = pi;
            document.getElementById('patientName').innerHTML =
                `${escHtml(p.patient.name)} &nbsp;<span style="font-size:13px;font-weight:400;color:var(--steel)">${p.patient.gender||''} / ${p.patient.age||''} yrs</span>`;
            document.getElementById('patientMeta').innerHTML =
                (p.payment?.appointment_no ?
                    `<span><i class="bi bi-hash me-1"></i>${escHtml(p.payment.appointment_no)}</span>` : '') +
                `<span><i class="bi bi-telephone me-1"></i>+${p.patient.country_code} ${p.patient.mobile||''}</span>` +
                (p.patient.email ? `<span><i class="bi bi-envelope me-1"></i>${escHtml(p.patient.email)}</span>` : '');
            const tags = document.getElementById('patientTags');
            tags.innerHTML = '';
            (p.history.drug_allergies || []).forEach(a => tags.insertAdjacentHTML('beforeend',
                    `<span class="tag tag-allergy"><i class="bi bi-exclamation-triangle-fill"></i>${escHtml(a)}</span>`
                    ));
            if ((p.history.chronic_conditions || []).length) tags.insertAdjacentHTML('beforeend',
                `<span class="tag tag-chronic"><i class="bi bi-heart-fill"></i>${p.history.chronic_conditions.map(x=>escHtml(x)).join(' · ')}</span>`
                );
            if (p.payment?.visit_type) tags.insertAdjacentHTML('beforeend',
                `<span class="tag tag-visit"><i class="bi bi-arrow-repeat"></i>${escHtml(p.payment.visit_type)}</span>`);
            // quick action links
            const isFinalized = !!(p.permissions && p.permissions.is_finalized);
            if (p.exists && p.routes.print) {
                ['bannerPrintBtn', 'qaPrint'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.href = p.routes.print;
                        el.classList.remove('disabled-btn');
                    }
                });
            }
            if (isFinalized) {
                ['bannerPdfBtn', 'qaPdf'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.href = p.routes.pdf;
                        el.classList.remove('disabled-btn');
                    }
                });
                const ef = document.getElementById('emailForm');
                if (ef) {
                    ef.action = p.routes.email;
                }
                const qe = document.getElementById('qaEmail');
                if (qe) qe.classList.remove('disabled-btn');
            }
            // past visits sidebar
             const CONSULTATION_EDIT_URL = '{{ url('admin/consultations') }}';
            const pvl = document.getElementById('pastVisitsList');
              pvl.innerHTML = '';
              p.past_consultations.forEach(item => {
                  const div = document.createElement('div');
                  div.className = 'history-item';
                  div.style.cursor = 'pointer';
                  div.innerHTML =
                      `<div class="history-date">${escHtml(item.date || '')}</div>
                      <div class="history-diag">${escHtml(item.title || 'Consultation')}</div>
                      <div class="history-doc">${escHtml(item.doctor || '')}</div>`;

                  // ✅ Use Blade-generated base URL instead of hardcoded path
                  div.onclick = () => window.location = CONSULTATION_EDIT_URL + '/' + item.id + '/edit';

                  pvl.appendChild(div);
              });
            if (!p.past_consultations.length) pvl.innerHTML =
                '<div class="history-item"><div class="history-doc">No previous visits.</div></div>';
            document.getElementById('currentVisitDate').textContent = p.consultation.visit_date || 'Today';
            document.getElementById('currentVisitDoc').textContent = p.doctor?.name || '–';
            // ongoing meds in sidebar
            const oml = document.getElementById('ongoingMedsList');
            oml.innerHTML = '';
            (p.history.ongoing_medications || []).forEach(m => {
                oml.insertAdjacentHTML('beforeend',
                    `<div class="history-item" style="padding:8px 0;border:none;"><div class="history-diag" style="font-size:12.5px;">${escHtml(m)}</div></div>`
                    );
            });
            if (!(p.history.ongoing_medications || []).length) oml.innerHTML =
                '<div class="history-doc" style="padding:4px 0;">None recorded.</div>';
            const al = document.getElementById('allergiesList');
            al.innerHTML = '';
            (p.history.drug_allergies || []).forEach(a => {
                al.insertAdjacentHTML('beforeend',
                    `<div class="history-item" style="padding:4px 0;border:none;"><span class="tag tag-allergy" style="font-size:11px;">${escHtml(a)}</span></div>`
                    );
            });
            if (!(p.history.drug_allergies || []).length) al.innerHTML =
                '<div class="history-doc" style="padding:4px 0;">NKDA</div>';
            // doctor display
            const doctorNameInput = document.getElementById('inp_doctor_name');
            if (doctorNameInput) {
                doctorNameInput.value = p.doctor?.name || '';
            }
            const rdsel = document.getElementById('inp_referral_dept');
            (p.departments || []).forEach(d => {
                rdsel.insertAdjacentHTML('beforeend',
                    `<option value="${escHtml(d.name)}" ${p.consultation.referral_department===d.name?'selected':''}>${escHtml(d.name)}</option>`
                    );
            });
            // visit setup fields
            document.getElementById('inp_visit_date').value = p.consultation.visit_date || '';
            document.getElementById('inp_visit_time').value = p.consultation.visit_time || '';
            document.getElementById('inp_token_number').value = p.consultation.token_number || '';
            // vitals
            const v = p.consultation.vitals || {};
            document.getElementById('bpSys').value = v.bp_systolic || '';
            document.getElementById('bpDia').value = v.bp_diastolic || '';
            document.getElementById('hr').value = v.heart_rate || '';
            document.getElementById('spo2').value = v.spo2 || '';
            document.getElementById('temp').value = v.temperature || '';
            document.getElementById('vWeight').value = v.weight || '';
            document.getElementById('vHeight').value = v.height || '';
            document.getElementById('vRr').value = v.respiratory_rate || '';
            document.getElementById('vGrbs').value = v.grbs || '';
            document.getElementById('vWaist').value = v.waist_circumference || '';
            document.getElementById('vPain').value = v.pain_score || '';
            document.getElementById('vGcs').value = v.gcs || '';
            if (v.bmi) {
                document.getElementById('vBmi').value = v.bmi;
            } else {
                calcBmi();
            }
            if ([v.weight, v.height, v.respiratory_rate, v.grbs, v.waist_circumference, v.pain_score, v.gcs].some(Boolean))
                document.getElementById('vitalsExtra').classList.add('visible');
            onVitalChange();
            // complaints
            setTagWrap('complaintWrap', 'complaintInput', p.consultation.chief_complaints);
            setTagWrap('aggWrap', 'aggInput', p.consultation.aggravating_factors);
            setTagWrap('relWrap', 'relInput', p.consultation.relieving_factors);
            setTagWrap('assocWrap', 'assocInput', p.consultation.associated_symptoms);
            document.getElementById('durationValue').value = p.consultation.duration_value || '';
            document.getElementById('durationUnit').value = p.consultation.duration_unit || 'Days';
            document.getElementById('hpiText').value = p.consultation.hpi || '';
            // history modal tags
            setTagWrap('pmhWrap', 'pmhInput', p.history.past_medical_history);
            setTagWrap('surgWrap', 'surgInput', p.history.surgical_history, 'var(--steel-bg)', 'var(--steel-dark)');
            setTagWrap('famWrap', 'famInput', p.history.family_history, 'var(--steel-bg)', 'var(--steel-dark)');
            setTagWrap('allergyWrap', 'allergyInput', p.history.drug_allergies, '#fdecea', '#c0392b');
            setTagWrap('chronicWrap', 'chronicInput', p.history.chronic_conditions, '#fff8e6', '#a0660a');
            setTagWrap('ongoingMedWrap', 'ongoingMedInput', p.history.ongoing_medications);
            // general appearance
            const ga = document.querySelector(`input[name="gen"][value="${p.consultation.general_appearance||'well'}"]`);
            if (ga) ga.checked = true;
            // examinations – pre-load saved systems if any, else use specialty default
            if (p.consultation.examinations && p.consultation.examinations.length) {
                activeSystemIds = [];
                renderExamSystems();
                p.consultation.examinations.forEach(ex => {
                    const sid = ALL_SYSTEMS.find(s => s.label === ex.system)?.id || ('custom_' + ex.system);
                    if (!ALL_SYSTEMS.find(s => s.id === sid)) ALL_SYSTEMS.push({
                        id: sid,
                        label: ex.system,
                        icon: 'bi-plus-square',
                        ph: 'Findings',
                        norm: true
                    });
                    activeSystemIds.push(sid);
                });
                renderExamSystems();
                // fill values
                p.consultation.examinations.forEach((ex, i) => {
                    const card = document.querySelectorAll('#examSystemsGrid .exam-system')[i];
                    if (!card) return;
                    const rb = card.querySelector(`input[type="radio"][value="${ex.status||'normal'}"]`);
                    if (rb) rb.checked = true;
                    const ni = card.querySelector('.exam-note-inline');
                    if (ni) ni.value = ex.notes || '';
                });
            } else {
                renderExamSystems();
            }
            // diagnoses
            document.getElementById('diagRows').innerHTML = '';
            diagCount = 0;
            (p.consultation.diagnoses || []).forEach(d => createDiagRow(d.name, d.code, d.type, d.status));
            if (!p.consultation.diagnoses?.length) createDiagRow(); // start with one empty row
            // investigations
            setTagWrap('invWrap', 'invInput', p.consultation.investigations, '#e6f7f1', '#2d8a6a');
            document.getElementById('invInstructions').value = p.consultation.investigation_instructions || '';
            // prescriptions
            document.getElementById('rxRows').innerHTML = '';
            rxCount = 0;
            (p.consultation.prescriptions || []).forEach(r => addRxRow(r));
            // advice / doctor note
            document.getElementById('adviceText').value = p.consultation.advice || '';
            document.getElementById('doctorNote').value = p.consultation.doctor_note || '';
            // follow-up
            document.querySelectorAll('.followup-opt').forEach(el => el.classList.toggle('selected', el.dataset.fu === (p
                .consultation.follow_up_label || '4 Weeks')));
            document.getElementById('inp_follow_up_date').value = p.consultation.follow_up_date || '';
            document.getElementById('inp_referral_note').value = p.consultation.referral_note || '';
            document.getElementById('histModalTitle').textContent = 'Patient History - ' + (p.patient.name || '');
            applyConsultationWorkflow(p);
        }

        /* Follow-up picker */
        document.getElementById('followupPicker').addEventListener('click', e => {
            const opt = e.target.closest('.followup-opt');
            if (!opt) return;
            document.querySelectorAll('.followup-opt').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            // auto-set date based on choice
            const m = {
                '1 Week': 7,
                '4 Weeks': 28,
                '3 Months': 91
            };
            const days = m[opt.dataset.fu];
            if (days) {
                const d = new Date();
                d.setDate(d.getDate() + days);
                document.getElementById('inp_follow_up_date').value = d.toISOString().split('T')[0];
            }
        });

        /* Global saveDraft / finaliseConsultation aliases for backward-compat */
        window.saveDraft = function() {
            collectAndSubmit('draft', false);
        };
        window.finaliseConsultation = function() {
            collectAndSubmit('finalized', true);
        };

        document.addEventListener('DOMContentLoaded', hydrateConsultation);
    </script>

    <script>
let isFormDirty = false;
let isSubmitting = false;

// ✅ Track ALL input changes inside main UI
document.getElementById('mainContent').addEventListener('input', function () {
    isFormDirty = true;
});

// ✅ When saving (draft/finalize)
function submitConsultation(status) {
    isSubmitting = true;
    isFormDirty = false;

    document.getElementById('post_status').value = status;

    document.getElementById('consultation-post-form').submit();
}

// ✅ Close button
document.getElementById('closeWindowBtn').addEventListener('click', function () {

    if (isFormDirty && !isSubmitting) {

        if (confirm("⚠️ You have unsaved changes.\nDo you want to save before closing?")) {

            // default save as draft
            submitConsultation('draft');

        } else {
            if (confirm("Are you sure you want to close without saving?")) {
                safeClose();
            }
        }

    } else {
        if (confirm("Are you sure you want to close?")) {
            safeClose();
        }
    }
});

// ✅ Browser refresh / tab close
window.addEventListener('beforeunload', function (e) {
    if (isFormDirty && !isSubmitting) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ✅ Safe close handler
function safeClose() {
    window.close();
    window.location.href = "{{ url('admin/appointments-report') }}";
}
</script>

<script>
document.getElementById('closeWindowBtn').addEventListener('click', function () {
    if (confirm("Close this consultation? Unsaved data will be lost.")) {
        window.location.href = "{{ url('admin/appointments-report') }}";
    }
});
</script>
</body>

</html>



