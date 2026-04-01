<div class="casesheet-page">
    <div class="casesheet-simple">
        <div class="casesheet-grid">
            <div>
                <div class="casesheet-field-label">ID</div>
                <div class="casesheet-field-value">{{ $consultation->token_number ?: ('EDGE-' . $consultation->id) }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Name</div>
                <div class="casesheet-field-value">{{ $consultation->patient->name ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Visit Date</div>
                <div class="casesheet-field-value">{{ optional($consultation->visit_date)->format('d/m/Y') ?? '-' }} {{ $consultation->visit_time ?: '' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Dr</div>
                <div class="casesheet-field-value">{{ $consultation->doctor?->name ?? '-' }}</div>
            </div>

            <div>
                <div class="casesheet-field-label">Mobile</div>
                <div class="casesheet-field-value">{{ $consultation->patient->mobile ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Age / Gender</div>
                <div class="casesheet-field-value">{{ $consultation->patient->age ?: '-' }} / {{ $consultation->patient->gender ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Time</div>
                <div class="casesheet-field-value">{{ $consultation->visit_time ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Remarks</div>
                <div class="casesheet-field-value">&nbsp;</div>
            </div>
        </div>

        <div class="casesheet-vitals">
            <div>
                <div class="casesheet-field-label">Height</div>
                <div class="casesheet-field-value">{{ $consultation->height ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Weight</div>
                <div class="casesheet-field-value">{{ $consultation->weight ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">BMI</div>
                <div class="casesheet-field-value">{{ $consultation->bmi ?: '-' }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">BP</div>
                <div class="casesheet-field-value">{{ ($consultation->bp_systolic ?: '-') . ' / ' . ($consultation->bp_diastolic ?: '-') }}</div>
            </div>
            <div>
                <div class="casesheet-field-label">Temp</div>
                <div class="casesheet-field-value">{{ $consultation->temperature ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="casesheet-blank">
        <div class="casesheet-visit-note">Next visit :</div>
    </div>
</div>
