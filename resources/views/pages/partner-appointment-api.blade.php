<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Appointment API Documentation</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-soft: #f8fbff;
            --text: #1f2d3d;
            --muted: #66788a;
            --line: #d8e3ef;
            --blue: #2a7fc1;
            --teal: #17b890;
            --pink: #d25498;
            --green: #1f8f5f;
            --amber: #c98a11;
            --red: #c44536;
            --shadow: 0 16px 40px rgba(30, 67, 114, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(42, 127, 193, 0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(210, 84, 152, 0.08), transparent 24%),
                var(--bg);
            color: var(--text);
            line-height: 1.55;
        }

        a { color: var(--blue); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }

        .hero {
            background: linear-gradient(135deg, #1e5f9f 0%, #2997a8 48%, #cf5c97 100%);
            color: #fff;
            border-radius: 24px;
            padding: 34px 36px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1.15;
        }

        .hero p {
            margin: 0;
            max-width: 860px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 16px;
        }

        .hero-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .hero-links a {
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.12);
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 600;
        }

        .grid {
            display: grid;
            gap: 20px;
        }

        .grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .card h2, .card h3 {
            margin-top: 0;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 14px 0 0;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            color: var(--text);
            font-size: 13px;
            font-weight: 600;
        }

        .subtle {
            color: var(--muted);
            font-size: 14px;
        }

        .endpoint {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }

        .verb {
            min-width: 64px;
            padding: 7px 10px;
            border-radius: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .verb.get { background: var(--blue); }
        .verb.post { background: var(--teal); }

        .path {
            font-family: Consolas, Monaco, monospace;
            font-size: 15px;
            word-break: break-all;
            color: #19324d;
        }

        .base-url {
            background: #f0f7ff;
            border: 1px solid #cfe3f8;
            border-radius: 16px;
            padding: 14px 16px;
            margin-top: 14px;
        }

        .base-url strong {
            display: block;
            margin-bottom: 4px;
            color: #18456d;
        }

        code.inline {
            background: #f1f5f9;
            border: 1px solid #dce7f2;
            border-radius: 7px;
            padding: 2px 7px;
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
        }

        pre {
            margin: 14px 0 0;
            background: #0f1720;
            color: #ebf5ff;
            border-radius: 16px;
            padding: 16px 18px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.55;
            border: 1px solid #1f3347;
        }

        .steps, .notes, .route-list {
            margin: 0;
            padding-left: 18px;
        }

        .route-list li, .steps li, .notes li {
            margin-bottom: 8px;
        }

        .table-wrap {
            overflow-x: auto;
            margin-top: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
            font-size: 14px;
        }

        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #f7fbff;
            color: #244566;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-ok { color: var(--green); font-weight: 700; }
        .status-warn { color: var(--amber); font-weight: 700; }
        .status-bad { color: var(--red); font-weight: 700; }

        .footer-note {
            margin-top: 24px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
        }

        .examples-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 20px;
            align-items: start;
        }

        .example-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .example-tab {
            border: 1px solid var(--line);
            background: var(--surface-soft);
            color: #20405f;
            padding: 9px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .example-tab:hover {
            border-color: #b8d2ea;
            background: #eef6ff;
        }

        .example-tab.active {
            background: linear-gradient(135deg, #1e5f9f 0%, #2997a8 55%, #cf5c97 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 10px 24px rgba(42, 127, 193, 0.18);
        }

        .example-panel {
            display: none;
        }

        .example-panel.active {
            display: block;
        }

        .example-panel h3,
        .rules-panel h3 {
            margin-bottom: 10px;
        }

        .example-panel pre,
        .rules-panel .table-wrap {
            margin-top: 0;
        }

        @media (max-width: 900px) {
            .grid.two,
            .grid.three {
                grid-template-columns: 1fr;
            }

            .examples-layout {
                grid-template-columns: 1fr;
            }

            .hero {
                padding: 26px 22px;
            }

            .hero h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="hero">
            <h1>Partner Appointment API Documentation</h1>
            <p>
                This API is for external portals that need to show Edge Clinic doctors, display their real available slots,
                grey out or hide already-booked slots, and create appointments directly inside Edge Clinic so the booking
                appears immediately in the application.
            </p>
            <div class="hero-links">
                <a href="#base-urls">Base URLs</a>
                <a href="#security">Security</a>
                <a href="#routes">API Routes</a>
                <a href="#examples">Code Examples</a>
            </div>
        </section>

        <div class="grid two">
            <section class="card" id="base-urls">
                <h2>Base URLs</h2>
                <p class="subtle">Use the demo domain now. Later you can switch the same integration to the final production domain.</p>

                <div class="base-url">
                    <strong>Current Demo Base URL</strong>
                    <div class="path">https://edge.clinic/demos/edge-clinic-v1/api/partner/v1</div>
                </div>

                <div class="base-url">
                    <strong>Future Production Base URL</strong>
                    <div class="path">https://edge.clinic/api/partner/v1</div>
                </div>

                <div class="pill-row">
                    <span class="pill">External booking supported</span>
                    <span class="pill">Real doctor availability</span>
                    <span class="pill">Booked slots can be hidden or greyed out</span>
                </div>
            </section>

            <section class="card" id="security">
                <h2>Security</h2>
                <p>Every request must send a partner API key.</p>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Header</th>
                                <th>Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code class="inline">Authorization</code></td>
                                <td><code class="inline">Bearer YOUR_API_KEY</code></td>
                            </tr>
                            <tr>
                                <td><code class="inline">X-API-Key</code></td>
                                <td><code class="inline">YOUR_API_KEY</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <pre>PARTNER_API_KEYS=portal-a:change-this-key,portal-b:change-that-key
PARTNER_API_DEFAULT_COUNTRY_CODE=+91
PARTNER_API_SOURCE_PREFIX=API</pre>
            </section>
        </div>

        <section class="card" id="routes" style="margin-top: 20px;">
            <h2>API Routes</h2>
            <p class="subtle">These are the routes that external portals should use.</p>

            <div class="route-list">
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">/api/partner/v1/doctors</span>
                </div>
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">/api/partner/v1/doctors/{doctor}/slots</span>
                </div>
                <div class="endpoint">
                    <span class="verb post">POST</span>
                    <span class="path">/api/partner/v1/appointments</span>
                </div>
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">/api/partner/v1/appointments/{paymentId}</span>
                </div>
            </div>
        </section>

        <div class="grid two" style="margin-top: 20px;">
            <section class="card">
                <h3>1. Get Doctors</h3>
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/doctors</span>
                </div>
                <pre>{
  "data": [
    {
      "id": 38,
      "name": "Ananth Egoor",
      "designation": "Consultant",
      "appointment_fee": 1,
      "followup_days": 10,
      "online_payment": true,
      "slots_public": true,
      "advance_booking_days": 15,
      "slot_duration": 20
    }
  ]
}</pre>
            </section>

            <section class="card">
                <h3>2. Get Slots</h3>
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/doctors/38/slots?start_date=2026-05-21&amp;end_date=2026-05-21&amp;include_unavailable=1</span>
                </div>
                <ul class="notes">
                    <li>Without <code class="inline">include_unavailable=1</code>, only free slots are returned.</li>
                    <li>With <code class="inline">include_unavailable=1</code>, the portal can grey out booked or blocked times.</li>
                    <li>Slot statuses can be <code class="inline">available</code>, <code class="inline">booked</code>, <code class="inline">reserved</code>, <code class="inline">past</code>, <code class="inline">full</code>, <code class="inline">weekly_off</code>, or <code class="inline">non_practice_day</code>.</li>
                </ul>
                <pre>{
  "doctor_id": 38,
  "doctor_name": "Ananth Egoor",
  "slots_public": true,
  "dates": [
    {
      "date": "2026-05-21",
      "status": "available",
      "slots": [
        {
          "time": "11:00",
          "display_time": "11:00 AM",
          "status": "booked",
          "is_available": false
        },
        {
          "time": "11:20",
          "display_time": "11:20 AM",
          "status": "available",
          "is_available": true
        }
      ]
    }
  ]
}</pre>
            </section>
        </div>

        <div class="grid two" style="margin-top: 20px;">
            <section class="card">
                <h3>3. Book Appointment</h3>
                <div class="endpoint">
                    <span class="verb post">POST</span>
                    <span class="path">https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/appointments</span>
                </div>
                <pre>{
  "doctor_id": 38,
  "appointment_date": "2026-05-21",
  "slot_time": "11:20",
  "patient_name": "Portal Test Patient",
  "mobile": "9000000001",
  "email": "portal.test@example.com",
  "gender": "female",
  "age": "32",
  "source_name": "Partner Portal",
  "external_booking_id": "PORTAL-100045",
  "notes": "Booked from external website"
}</pre>

                <ul class="steps">
                    <li>If the patient already exists, the API reuses the same patient record.</li>
                    <li>If the patient does not exist, the API creates a new patient automatically.</li>
                    <li>The appointment is inserted directly into Edge Clinic tables.</li>
                    <li>The slot becomes unavailable for future booking checks.</li>
                </ul>
            </section>

            <section class="card">
                <h3>4. Get Booking Details</h3>
                <div class="endpoint">
                    <span class="verb get">GET</span>
                    <span class="path">https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/appointments/pay_api_example1234</span>
                </div>
                <pre>{
  "data": {
    "payment_id": "pay_api_example1234",
    "appointment_no": "APT202605201230XYZ",
    "appointment_date": "2026-05-21",
    "slot_time": "11:20",
    "payment_status": "Pending",
    "appointment_status": "Scheduled",
    "doctor": {
      "id": 38,
      "name": "Ananth Egoor"
    },
    "patient": {
      "id": 1,
      "name": "Portal Test Patient"
    }
  }
}</pre>
            </section>
        </div>

        <section class="card" id="examples" style="margin-top: 20px;">
            <h2>Common Integration Examples</h2>
            <div class="examples-layout">
                <div>
                    <div class="example-tabs" role="tablist" aria-label="Integration language examples">
                        <button class="example-tab active" type="button" data-example-tab="curl" role="tab" aria-selected="true">cURL</button>
                        <button class="example-tab" type="button" data-example-tab="javascript" role="tab" aria-selected="false">JavaScript</button>
                        <button class="example-tab" type="button" data-example-tab="php" role="tab" aria-selected="false">PHP</button>
                        <button class="example-tab" type="button" data-example-tab="python" role="tab" aria-selected="false">Python</button>
                    </div>

                    <div class="example-panel active" data-example-panel="curl" role="tabpanel">
                        <h3>cURL</h3>
                        <pre>curl -X GET "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/doctors" \
  -H "Authorization: Bearer YOUR_API_KEY"</pre>
                    </div>

                    <div class="example-panel" data-example-panel="javascript" role="tabpanel">
                        <h3>JavaScript</h3>
                        <pre>const API_BASE = "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1";
const API_KEY = "YOUR_API_KEY";

const res = await fetch(`${API_BASE}/doctors`, {
  headers: {
    Authorization: `Bearer ${API_KEY}`
  }
});

const data = await res.json();</pre>
                    </div>

                    <div class="example-panel" data-example-panel="php" role="tabpanel">
                        <h3>PHP</h3>
                        <pre>$apiBase = 'https://edge.clinic/demos/edge-clinic-v1/api/partner/v1';
$apiKey = 'YOUR_API_KEY';

$ch = curl_init($apiBase . '/doctors');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
    ],
]);

$response = curl_exec($ch);</pre>
                    </div>

                    <div class="example-panel" data-example-panel="python" role="tabpanel">
                        <h3>Python</h3>
                        <pre>import requests

api_base = "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1"
api_key = "YOUR_API_KEY"

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json",
}

response = requests.get(f"{api_base}/doctors", headers=headers, timeout=30)
print(response.json())</pre>
                    </div>
                </div>

                <div class="rules-panel">
                    <h3>Portal Behavior Rules</h3>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Need</th>
                                    <th>How To Handle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Show only free slots</td>
                                    <td>Call slots API without <code class="inline">include_unavailable</code>.</td>
                                </tr>
                                <tr>
                                    <td>Grey out booked slots</td>
                                    <td>Call slots API with <code class="inline">include_unavailable=1</code> and grey out rows where <code class="inline">is_available = false</code>.</td>
                                </tr>
                                <tr>
                                    <td>Book into Edge Clinic instantly</td>
                                    <td>Use <code class="inline">POST /appointments</code>.</td>
                                </tr>
                                <tr>
                                    <td>Check booked appointment later</td>
                                    <td>Use <code class="inline">GET /appointments/{paymentId}</code>.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="card" style="margin-top: 20px;">
            <h2>Documentation Route</h2>
            <p class="subtle">Open this HTML documentation page in browser using the route below.</p>
            <div class="base-url">
                <strong>Current Documentation URL</strong>
                <div class="path">https://edge.clinic/demos/edge-clinic-v1/partner-api-docs</div>
            </div>
            <div class="base-url">
                <strong>Future Production Documentation URL</strong>
                <div class="path">https://edge.clinic/partner-api-docs</div>
            </div>
        </section>

        <div class="footer-note">
            Partner Appointment API documentation for Edge Clinic.
        </div>
    </div>
    <script>
        document.querySelectorAll('[data-example-tab]').forEach(function (button) {
            button.addEventListener('click', function () {
                const target = button.getAttribute('data-example-tab');

                document.querySelectorAll('[data-example-tab]').forEach(function (tab) {
                    tab.classList.toggle('active', tab === button);
                    tab.setAttribute('aria-selected', tab === button ? 'true' : 'false');
                });

                document.querySelectorAll('[data-example-panel]').forEach(function (panel) {
                    panel.classList.toggle('active', panel.getAttribute('data-example-panel') === target);
                });
            });
        });
    </script>
</body>
</html>
