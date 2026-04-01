<div class="card">
    <div class="card-title">Patient & Visit Details</div>
    <div class="grid grid-4">
        <div><div class="field-label">Patient Name</div><div class="field-value">{{ $consultation->patient->name }}</div></div>
        <div><div class="field-label">Age / Gender</div><div class="field-value">{{ $consultation->patient->age ?: '-' }} / {{ $consultation->patient->gender ?: '-' }}</div></div>
        <div><div class="field-label">Doctor</div><div class="field-value">{{ $consultation->doctor?->name ?? '-' }}</div></div>
        <div><div class="field-label">Date</div><div class="field-value">{{ optional($consultation->visit_date)->format('d M Y') ?? '-' }}</div></div>
        <div><div class="field-label">Time</div><div class="field-value">{{ $consultation->visit_time ?: '-' }}</div></div>
        <div><div class="field-label">Token</div><div class="field-value">{{ $consultation->token_number ?: '-' }}</div></div>
        <div><div class="field-label">Mobile</div><div class="field-value">{{ $consultation->patient->mobile ?: '-' }}</div></div>
        <div><div class="field-label">Status</div><div class="field-value">{{ ucfirst($consultation->status ?: 'finalized') }}</div></div>
    </div>
</div>

<div class="card">
    <div class="card-title">Prescription</div>
    @if($consultation->prescriptions->isNotEmpty())
        <table class="rx-table">
            <thead>
                <tr>
                    <th class="rx-no">#</th>
                    <th>Medicine</th>
                    <th>Pack / Strength</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consultation->prescriptions as $index => $prescription)
                    <tr>
                        <td class="rx-no">{{ $index + 1 }}</td>
                        <td>
                            <div class="rx-name">{{ $prescription->medicine_name }}</div>
                            @if($prescription->details)
                                <div class="rx-notes">{{ $prescription->details }}</div>
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
        <div class="field-value">No medicines prescribed.</div>
    @endif
</div>

<div class="card">
    <div class="card-title">Clinical Summary</div>
    <div class="row">
        <div class="field-label">Diagnosis</div>
        <div class="field-value">
            @forelse($consultation->diagnoses as $diagnosis)
                <div>
                    <strong>{{ $diagnosis->diagnosis_name }}</strong>
                    @if($diagnosis->icd10_code) <span class="muted">({{ $diagnosis->icd10_code }})</span> @endif
                </div>
            @empty
                -
            @endforelse
        </div>
    </div>
    <div class="row">
        <div class="field-label">Advice</div>
        <div class="field-value">{{ $consultation->advice ?: '-' }}</div>
    </div>
    <div class="row">
        <div class="field-label">Investigations</div>
        <div class="field-value">
            @forelse($consultation->investigations as $investigation)
                <span class="pill">{{ $investigation->test_name }}</span>
            @empty
                -
            @endforelse
        </div>
    </div>
    <div class="row">
        <div class="field-label">Follow Up</div>
        <div class="field-value">
            {{ $consultation->follow_up_label ?: '-' }}
            @if($consultation->follow_up_date)
                on {{ $consultation->follow_up_date->format('d M Y') }}
            @endif
        </div>
    </div>
</div>

<div class="sign-row">
    <div></div>
    <div class="sign-box">Doctor Signature</div>
</div>
