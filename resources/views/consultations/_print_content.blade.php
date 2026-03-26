@php($history = $consultation->patient->consultationHistory)
<style>
body{font-family:DejaVu Sans,sans-serif;color:#213140;font-size:13px}
.sheet{max-width:960px;margin:0 auto}
.head{display:flex;justify-content:space-between;gap:20px;padding-bottom:12px;border-bottom:2px solid #2f7aa9;margin-bottom:16px}
.title{font-size:24px;font-weight:700;color:#2f7aa9}
.muted{font-size:12px;color:#6d7f90}
.section{border:1px solid #dce5ee;border-radius:10px;padding:14px;margin-bottom:14px}
.section h3{margin:0 0 10px;font-size:15px;font-weight:700;color:#2f7aa9;border-bottom:1px solid #dce5ee;padding-bottom:6px}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.row{padding:8px 0;border-bottom:1px solid #edf2f6}
.row:last-child{border-bottom:none}
.label{font-size:11px;text-transform:uppercase;color:#7c8f9f;font-weight:700;margin-bottom:3px}
.pill{display:inline-block;background:#e8f2fa;color:#2f7aa9;border-radius:999px;padding:4px 10px;margin:3px 4px 0 0;font-size:12px}
/* Prescription table */
.rx-table{width:100%;border-collapse:collapse;margin-top:4px}
.rx-table th{background:#2f7aa9;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:.04em;padding:7px 10px;text-align:left;font-weight:700}
.rx-table td{padding:8px 10px;font-size:13px;border-bottom:1px solid #edf2f6;vertical-align:top}
.rx-table tr:last-child td{border-bottom:none}
.rx-table tr:nth-child(even) td{background:#f7fbfe}
.rx-num{color:#2f7aa9;font-weight:700;text-align:center;width:30px}
.rx-med{font-weight:600}
.rx-detail{font-size:12px;color:#6d7f90;margin-top:2px}
</style>
<div class="sheet">

  {{-- HEADER --}}
  <div class="head">
    <div>
      <div class="title">Edge Clinic Consultation</div>
      <div class="muted">Current Visit Summary</div>
    </div>
    <div style="text-align:right">
      <div><strong>{{ $consultation->patient->name }}</strong></div>
      <div class="muted">{{ $consultation->patient->gender }} / {{ $consultation->patient->age }} yrs</div>
      <div class="muted">Doctor: {{ $consultation->doctor?->name ?? 'Not assigned' }}</div>
    </div>
  </div>

  {{-- VISIT DETAILS --}}
  <div class="section">
    <h3>Visit Details</h3>
    <div class="grid">
      <div><div class="label">Date</div><div>{{ optional($consultation->visit_date)->format('d M Y') ?? '-' }}</div></div>
      <div><div class="label">Time</div><div>{{ $consultation->visit_time ?: '-' }}</div></div>
      <div><div class="label">Token</div><div>{{ $consultation->token_number ?: '-' }}</div></div>
      <div><div class="label">Status</div><div>{{ ucfirst($consultation->status) }}</div></div>
    </div>
  </div>

  {{-- VITALS --}}
  <div class="section">
    <h3>Vitals</h3>
    <div class="grid">
      <div><div class="label">BP</div><div>{{ ($consultation->bp_systolic ?: '-') . ' / ' . ($consultation->bp_diastolic ?: '-') }}</div></div>
      <div><div class="label">Heart Rate</div><div>{{ $consultation->heart_rate ?: '-' }}</div></div>
      <div><div class="label">SpO₂</div><div>{{ $consultation->spo2 ? $consultation->spo2.'%' : '-' }}</div></div>
      <div><div class="label">Temperature</div><div>{{ $consultation->temperature ?: '-' }}</div></div>
      <div><div class="label">Weight</div><div>{{ $consultation->weight ? $consultation->weight.' kg' : '-' }}</div></div>
      <div><div class="label">Height</div><div>{{ $consultation->height ? $consultation->height.' cm' : '-' }}</div></div>
      <div><div class="label">BMI</div><div>{{ $consultation->bmi ?: '-' }}</div></div>
      <div><div class="label">GRBS</div><div>{{ $consultation->grbs ? $consultation->grbs.' mg/dL' : '-' }}</div></div>
      @if($consultation->respiratory_rate)
      <div><div class="label">Resp. Rate</div><div>{{ $consultation->respiratory_rate }}</div></div>
      @endif
      @if($consultation->waist_circumference)
      <div><div class="label">Waist Circ.</div><div>{{ $consultation->waist_circumference }} cm</div></div>
      @endif
      @if($consultation->pain_score)
      <div><div class="label">Pain Score</div><div>{{ $consultation->pain_score }} / 10</div></div>
      @endif
      @if($consultation->gcs)
      <div><div class="label">GCS</div><div>{{ $consultation->gcs }} / 15</div></div>
      @endif
    </div>
  </div>

  {{-- COMPLAINTS & EXAMINATION --}}
  <div class="section">
    <h3>Complaints &amp; Examination</h3>
    <div class="row">
      <div class="label">Chief Complaints</div>
      <div>
        @forelse($consultation->chief_complaints ?? [] as $item)
          <span class="pill">{{ $item }}</span>
        @empty
          <span>-</span>
        @endforelse
        @if($consultation->chief_complaint_duration_value && $consultation->chief_complaint_duration_unit)
          &nbsp;<span class="muted">for {{ $consultation->chief_complaint_duration_value }} {{ $consultation->chief_complaint_duration_unit }}</span>
        @endif
      </div>
    </div>
    @if($consultation->history_of_present_illness)
    <div class="row">
      <div class="label">History of Present Illness</div>
      <div>{{ $consultation->history_of_present_illness }}</div>
    </div>
    @endif
    @if(($consultation->aggravating_factors ?? []) || ($consultation->relieving_factors ?? []))
    <div class="row">
      <div class="grid-2">
        <div>
          <div class="label">Aggravating Factors</div>
          <div>
            @forelse($consultation->aggravating_factors ?? [] as $item)
              <span class="pill">{{ $item }}</span>
            @empty -
            @endforelse
          </div>
        </div>
        <div>
          <div class="label">Relieving Factors</div>
          <div>
            @forelse($consultation->relieving_factors ?? [] as $item)
              <span class="pill">{{ $item }}</span>
            @empty -
            @endforelse
          </div>
        </div>
      </div>
    </div>
    @endif
    @if($consultation->associated_symptoms ?? [])
    <div class="row">
      <div class="label">Associated Symptoms</div>
      <div>
        @foreach($consultation->associated_symptoms as $item)
          <span class="pill">{{ $item }}</span>
        @endforeach
      </div>
    </div>
    @endif
    <div class="row">
      <div class="label">General Appearance</div>
      <div>{{ ucfirst($consultation->general_appearance ?: 'Well') }}</div>
    </div>
    <div class="row">
      <div class="label">Examinations</div>
      <div>
        @forelse($consultation->examinations as $exam)
          <div>
            <strong>{{ $exam->system_name }}:</strong>
            {{ ucfirst($exam->finding_status) }}@if($exam->notes) – {{ $exam->notes }}@endif
          </div>
        @empty
          -
        @endforelse
      </div>
    </div>
  </div>

  {{-- DIAGNOSIS & PLAN --}}
  <div class="section">
    <h3>Diagnosis &amp; Plan</h3>
    <div class="row">
      <div class="label">Diagnosis</div>
      <div>
        @forelse($consultation->diagnoses as $diagnosis)
          <div>
            <strong>{{ $diagnosis->diagnosis_name }}</strong>
            @if($diagnosis->icd10_code) <span class="muted">({{ $diagnosis->icd10_code }})</span> @endif
            – {{ ucfirst($diagnosis->diagnosis_type) }}, {{ ucfirst($diagnosis->clinical_status) }}
          </div>
        @empty
          -
        @endforelse
      </div>
    </div>
    <div class="row">
      <div class="label">Investigations</div>
      <div>
        @forelse($consultation->investigations as $investigation)
          <span class="pill">{{ $investigation->test_name }}</span>
        @empty
          -
        @endforelse
      </div>
    </div>
    @if($consultation->investigation_instructions)
    <div class="row">
      <div class="label">Investigation Instructions</div>
      <div>{{ $consultation->investigation_instructions }}</div>
    </div>
    @endif
    @if($consultation->referral_department)
    <div class="row">
      <div class="label">Referral</div>
      <div>
        {{ $consultation->referral_department }}
        @if($consultation->referral_note) – {{ $consultation->referral_note }} @endif
      </div>
    </div>
    @endif
    <div class="row">
      <div class="label">Advice</div>
      <div>{{ $consultation->advice ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Follow Up</div>
      <div>
        {{ $consultation->follow_up_label ?: '-' }}
        @if($consultation->follow_up_date)
          on {{ $consultation->follow_up_date->format('d M Y') }}
        @endif
      </div>
    </div>
  </div>

  {{-- PRESCRIPTION TABLE --}}
  <div class="section">
    <h3>Prescription (Rx)</h3>
    @if($consultation->prescriptions->isNotEmpty())
    <table class="rx-table">
      <thead>
        <tr>
          <th class="rx-num">#</th>
          <th>Medicine</th>
          <th>Pack / Strength</th>
          <th>Frequency</th>
          <th>Duration</th>
          <th>Instructions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($consultation->prescriptions as $i => $prescription)
        <tr>
          <td class="rx-num">{{ $i + 1 }}</td>
          <td>
            <div class="rx-med">{{ $prescription->medicine_name }}</div>
            @if($prescription->details)
              <div class="rx-detail">{{ $prescription->details }}</div>
            @endif
          </td>
          <td>{{ $prescription->pack ?: '-' }}</td>
          <td>{{ $prescription->frequency ?: '-' }}</td>
          <td>{{ $prescription->duration ?: '-' }}</td>
          <td>{{ $prescription->instruction ?: '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
      <div style="color:#6d7f90;font-style:italic">No medicines prescribed.</div>
    @endif
  </div>

  {{-- PATIENT HISTORY SNAPSHOT --}}
  <div class="section">
    <h3>History Snapshot</h3>
    <div class="row">
      <div class="label">Past Medical History</div>
      <div>{{ collect($history?->past_medical_history ?? [])->implode(', ') ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Surgical History</div>
      <div>{{ collect($history?->surgical_history ?? [])->implode(', ') ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Family History</div>
      <div>{{ collect($history?->family_history ?? [])->implode(', ') ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Drug Allergies</div>
      <div>{{ collect($history?->drug_allergies ?? [])->implode(', ') ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Chronic Conditions</div>
      <div>{{ collect($history?->chronic_conditions ?? [])->implode(', ') ?: '-' }}</div>
    </div>
    <div class="row">
      <div class="label">Ongoing Medications</div>
      <div>{{ collect($history?->ongoing_medications ?? [])->implode(', ') ?: '-' }}</div>
    </div>
  </div>

</div>