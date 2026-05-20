@extends('template_v1')

@section('content')

@php
    $taxType = 'intra'; // change to 'inter' if required
@endphp
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>

        <div class="p-2 bd-highlight">
            @if(isset($addlink) && !empty($addlink))
                <a href="{{ $addlink }}">
                    <i class="fa-solid fa-circle-plus"></i>
                </a>
            @endif
        </div>
    </div>
</div>
<div class="card shadow-sm p-4 mb-3">

    <div class="row mb-3">
        <div class="col-md-4 position-relative">

            <label class="fw-bold">Search Patient (Name / Mobile)</label>

            <div class="input-group">
                <input type="text"
                       id="patientSearch"
                       class="form-control"
                       placeholder="Type name or mobile">

                <button type="button"
                        class="btn btn-primary"
                        id="searchPatientBtn">
                    Go
                </button>
            </div>

        </div>
    </div>

    <div id="patientMessage" class="text-danger fw-bold"></div>

</div>
<form method="POST"
      action="{{ isset($invoice)
          ? route('admin.invoices.update', $invoice->id)
          : route('admin.invoices.store') }}">

    @csrf

    @if(isset($invoice))
        @method('PUT')
    @endif
<input type="hidden" name="patient_id" id="patient_id">
<div class="card shadow-sm p-4 mb-3">

    {{-- ================= PATIENT SEARCH ================= --}}



    {{-- ================= PATIENT DETAILS ================= --}}
    <div class="row mb-4">
        <div class="col-md-9">
            {{-- <h6 class="fw-bold">INVOICE TO</h6> --}}
            <div id="patientDetails"></div>
        </div>

        <div class="col-md-3">
            <label>Appointment No</label>
        <input type="text" id="appointment_no" name="appointment_no"
               class="form-control" readonly>
        <label>Doctor</label>
        <input type="text" id="doctor_name"
               class="form-control" readonly>
        <input type="hidden" id="doctor_id" name="doctor_id">
            <label>Invoice No</label>
            <input type="text"
                   name="invoice_number"
                   value="{{ isset($invoice)
                    ? $invoice->invoice_number
                    : ($autoInvoiceNumber ?? '') }}"
                   class="form-control mb-2"
                   readonly>

            <label>Invoice Date</label>
            <input type="date"
            name="invoice_date"
            value="{{ isset($invoice)
                    ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')
                    : date('Y-m-d') }}"
            class="form-control mb-2">
            <label>Date & Time</label>
        <input type="text" id="appointment_datetime"
               class="form-control" readonly>
        </div>
    </div>

    <hr>

    {{-- ================= ADD ITEM BUTTON ================= --}}
    <div class="text-end mb-2">
        <button type="button" class="btn btn-primary btn-sm" id="addRow">
            + Add Item
        </button>
    </div>

    {{-- ================= ITEMS TABLE ================= --}}
    <div class="table-responsive">
        <table class="table table-bordered text-end" id="itemsTable">
            <thead class="table-light text-center">
            <tr>
                <th class="text-start">Service</th>
                <th width="8%">Qty</th>
                <th width="12%">Rate</th>

                @if($taxType == 'intra')
                    <th width="10%">CGST %</th>
                    <th width="10%">SGST %</th>
                @else
                    <th width="10%">IGST %</th>
                @endif

                <th width="12%">Total</th>
                <th width="5%"></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    {{-- ================= SUMMARY ================= --}}
    <div class="row mt-4">
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <div class="mb-3">
                    <label class="fw-bold">Discount (%)</label>
                    <input type="number"
                           name="discount_percentage"
                           id="discountPercentage"
                           class="form-control"
                           min="0"
                           max="100"
                           step="0.01"
                           value="{{ isset($invoice) ? number_format((float) ($invoice->discount_percentage ?? 0), 2, '.', '') : '0' }}"
                           placeholder="Enter discount percentage">
                    <small class="text-muted">Only percentage discount is allowed.</small>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Taxable Value</span>
                    <span>₹ <span id="subTotal">0.00</span></span>
                </div>

                <div id="taxBreakup"></div>

                <div class="d-flex justify-content-between mt-2">
                    <span>Discount Amount</span>
                    <span>₹ <span id="discountAmount">0.00</span></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fw-bold">
                    <span>Grand Total</span>
                    <span>₹ <span id="grandTotal">0.00</span></span>
                </div>

            </div>
        </div>
    </div>



</div>
<button type="submit" class="btn btn-success mt-3">
        Save Invoice
    </button>
</form>

<style>
.search-box{
    position: fixed;
    background:#fff;
    border:1px solid #ddd;
    border-radius:6px;
    max-height:220px;
    overflow-y:auto;
    display:none;
    z-index:9999;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.search-box div{
    padding:8px 12px;
    cursor:pointer;
}

.search-box div:hover{
    background:#f1f1f1;
}
.search-box div{
    padding:8px;
    cursor:pointer;
}
.search-box div:hover{
    background:#f1f1f1;
}
.patient-select-wrapper {
    width: 30%;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}

.patient-option {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s ease;
}

.patient-option:last-child {
    border-bottom: none;
}

.patient-option:hover {
    background-color: #f5f5f5;
}
</style>

@endsection
@push('scripts')

<script>
let existingInvoice = @json(isset($invoice) ? $invoice->load('items') : null);
let services  = @json($services);
let patients  = @json($patients);
let taxType   = '{{ $taxType }}';

$(document).ready(function(){
    if(existingInvoice){

        // Preload patient
        let patient = patients.find(p => p.id == existingInvoice.patient_id);
        if(patient){
            renderPatient(patient);
        }

        // Preload items
        if(existingInvoice.items && existingInvoice.items.length > 0){

            existingInvoice.items.forEach(function(item){

                addRow();

                let row = $('#itemsTable tbody tr').last();

                row.find('.service_id').val(item.service_id);
                row.find('.service_name').val(item.service_name);
                row.find('.serviceSearch').val(item.service_name);
                row.find('.qty').val(item.quantity);
                row.find('.rate').val(item.rate);

                if(taxType === 'intra'){
                    row.find('.cgstPercent').val(item.cgst_percent);
                    row.find('.sgstPercent').val(item.sgst_percent);
                } else {
                    row.find('.igstPercent').val(item.igst_percent);
                }
            });

            calculateTotals();

        } else {
            addRow();
        }

    } else {
        addRow();
    }
});


// =============================================
// Utility: Capitalize First Letter Of Each Word
// =============================================
function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

// =============================================
// GO BUTTON CLICK SEARCH
// =============================================

// =============================================
// GO BUTTON CLICK SEARCH (FIXED)
// =============================================

$('#searchPatientBtn').on('click', function(){

    let value = $('#patientSearch').val().trim().toLowerCase();
    let messageBox = $('#patientMessage');
    let detailsBox = $('#patientDetails');

    messageBox.text('');
    detailsBox.html('');

    if(value.length < 2){
        messageBox.text('Please enter valid name or mobile');
        return;
    }

    // 🔥 FILTER instead of FIND
    let matchedPatients = patients.filter(p =>
        p.name.toLowerCase().includes(value) ||
        p.mobile.includes(value)
    );

    if(matchedPatients.length === 0){
        messageBox.text('Patient Details Not Found');
        return;
    }

    // If only one patient → auto select
    if(matchedPatients.length === 1){
        renderPatient(matchedPatients[0]);
        return;
    }

    // 🔥 If multiple patients found (same mobile case)
    let listHtml = `
        <div class="patient-select-wrapper mt-2">
        `;

        matchedPatients.forEach(p=>{
            listHtml += `
                <div class="patient-option"
                    onclick="renderPatientById(${p.id})">
                    <div class="fw-bold">${toTitleCase(p.name)}</div>
                    <small class="text-muted">Mobile: ${p.mobile}</small>
                </div>
            `;
        });

        listHtml += `</div>`;

        messageBox.html(`
            <div class="fw-bold mb-2 text-dark">
                Multiple Patients Found. Please Select:
            </div>
            ${listHtml}
        `);

});


// ======================================================
// 2️⃣ SELECT PATIENT FUNCTION
// ======================================================

function renderPatientById(id){
    let patient = patients.find(p => p.id == id);
    renderPatient(patient);
}

// function renderPatient(patient){

//     $('#patient_id').val(patient.id);
//     $('#patientMessage').html('');

//     $('#patientDetails').html(`
//         <p><strong>${toTitleCase(patient.name)}</strong></p>
//         <p>Mobile: ${patient.mobile ?? ''}</p>
//         <p>Email: ${patient.email ?? ''}</p>
//         <p>${toTitleCase(patient.address ?? '')}</p>
//     `);
// }
function renderPatient(patient){

    $('#patient_id').val(patient.id);
    $('#patientMessage').html('');

    $('#patientDetails').html(`
        <p><strong>${toTitleCase(patient.name)}</strong></p>
        <p>Mobile: ${patient.mobile ?? ''}</p>
        <p>Email: ${patient.email ?? ''}</p>
        <p>${toTitleCase(patient.address ?? '')}</p>
    `);

    // 🔥 Fetch latest appointment from payments table
    $.get("{{ route('get.latest.appointment', ':id') }}"
        .replace(':id', patient.id),
    function(data){

        if(!data || !data.appointment_id){
            $('#appointment_no').val('');
            $('#doctor_name').val('');
            $('#appointment_datetime').val('');
            $('#appointment_id').val('');
            $('#doctor_id').val('');
            return;
        }

        $('#appointment_no').val(data.appointment_no);
        $('#appointment_id').val(data.appointment_id);

        $('#doctor_name').val(data.doctor_name);
        $('#doctor_id').val(data.doctor_id);

        $('#appointment_datetime').val(
            data.apt_date + ' ' + data.apt_time
        );
});
}

// ======================================================
// 3️⃣ LOAD ORDERS BASED ON PATIENT
// ======================================================

// function loadPatientOrders(patientId){

//     $.get('/get-patient-orders/'+patientId, function(data){

//         $('#orderSelect').empty();

//         if(data.length === 0){
//             $('#orderSelect').append(
//                 `<option value="">No Pending Orders</option>`
//             );
//             return;
//         }

//         $('#orderSelect').append(
//             `<option value="">-- Select Order --</option>`
//         );

//         data.forEach(o=>{
//             $('#orderSelect').append(`
//                 <option value="${o.order_id}">
//                     ${o.order_id} - ₹${o.amount}
//                 </option>
//             `);
//         });

//     });
// }


// ======================================================
// 4️⃣ ADD ITEM ROW
// ======================================================

$('#addRow').click(function(){
    addRow();
});

let rowIndex = 0;

function addRow(){

    let index = rowIndex++;

    $('#itemsTable tbody').append(`
    <tr>

        <td class="text-start position-relative">
            <input type="hidden" name="items[${index}][service_id]" class="service_id">
            <input type="hidden" name="items[${index}][service_name]" class="service_name">

            <input type="text"
                   class="form-control serviceSearch"
                   placeholder="Search service">

            <div class="search-box serviceResults"></div>
        </td>

        <td>
            <input type="number"
                   name="items[${index}][quantity]"
                   class="form-control qty"
                   value="1">
        </td>

        <td>
            <input type="number"
                   name="items[${index}][rate]"
                   class="form-control rate">
        </td>

        ${ taxType === 'intra' ? `
            <td>
                <input type="number"
                       name="items[${index}][cgst_percent]"
                       class="form-control cgstPercent">
            </td>

            <td>
                <input type="number"
                       name="items[${index}][sgst_percent]"
                       class="form-control sgstPercent">
            </td>
        ` : `
            <td>
                <input type="number"
                       name="items[${index}][igst_percent]"
                       class="form-control igstPercent">
            </td>
        ` }

        <td class="rowTotal fw-bold">0.00</td>

        <td>
            <button type="button"
                    class="btn btn-danger btn-sm removeRow text-white">
                X
            </button>
        </td>
    </tr>
    `);
}


// ======================================================
// 5️⃣ SERVICE SEARCH FUNCTION
// ======================================================

$(document).on('keyup','.serviceSearch', function(){

    let input = $(this);
    let value = input.val().toLowerCase();
    let box   = input.siblings('.serviceResults');

    box.empty();

    if(value.length < 1){
        box.hide();
        return;
    }

    services.filter(s => s.name.toLowerCase().includes(value))
        .forEach(s => {

        box.append(`
        <div onclick="selectService(this)"
            data-id="${s.id}"
            data-name="${s.name}"
            data-rate="${s.amount}"
            data-cgst="${s.cgst}"
            data-sgst="${s.sgst}"
            data-igst="${s.igst}">
            ${s.name}
        </div>
    `);
    });

    // 🔥 Dynamic Positioning
    let offset = input.offset();
    let height = input.outerHeight();

    box.css({
        top: offset.top + height,
        left: offset.left,
        width: input.outerWidth()
    });

    box.show();
});


// ======================================================
// 6️⃣ SELECT SERVICE FUNCTION
// ======================================================

function selectService(el){

    let row = $(el).closest('tr');

    let name  = $(el).data('name');
    let rate  = $(el).data('rate');
    let cgst  = $(el).data('cgst');
    let sgst  = $(el).data('sgst');
    let igst  = $(el).data('igst');

    row.find('.serviceSearch').val(name);
    row.find('.rate').val(rate);

    row.find('.service_id').val($(el).data('id') ?? '');
    row.find('.service_name').val(name);

    if(taxType === 'intra'){
        row.find('.cgstPercent').val(cgst);
        row.find('.sgstPercent').val(sgst);
    }else{
        row.find('.igstPercent').val(igst);
    }

    $(el).parent().hide();

    calculateTotals();
}


// ======================================================
// 7️⃣ CALCULATE TOTALS
// ======================================================

$(document).on('input',
'.qty,.rate,.cgstPercent,.sgstPercent,.igstPercent',
calculateTotals);

$(document).on('click','.removeRow',function(){
    $(this).closest('tr').remove();
    calculateTotals();
});

function calculateTotals(){

    let sub = 0;
    let taxMap = {};
    let taxTotal = 0;

    $('#itemsTable tbody tr').each(function(){

        let qty  = parseFloat($(this).find('.qty').val())  || 0;
        let rate = parseFloat($(this).find('.rate').val()) || 0;

        let base = qty * rate;
        let total = base;

        if(taxType === 'intra'){

            let cgst = parseFloat($(this).find('.cgstPercent').val()) || 0;
            let sgst = parseFloat($(this).find('.sgstPercent').val()) || 0;

            let cgAmt = (base * cgst)/100;
            let sgAmt = (base * sgst)/100;

            taxMap['CGST '+cgst+'%'] =
                (taxMap['CGST '+cgst+'%']||0) + cgAmt;

            taxMap['SGST '+sgst+'%'] =
                (taxMap['SGST '+sgst+'%']||0) + sgAmt;

            taxTotal += cgAmt + sgAmt;
            total += cgAmt + sgAmt;

        }else{

            let igst = parseFloat($(this).find('.igstPercent').val()) || 0;
            let igAmt = (base * igst)/100;

            taxMap['IGST '+igst+'%'] =
                (taxMap['IGST '+igst+'%']||0) + igAmt;

            taxTotal += igAmt;
            total += igAmt;
        }

        $(this).find('.rowTotal').text(total.toFixed(2));

        sub += base;
    });

    let discountPercentage = parseFloat($('#discountPercentage').val()) || 0;

    if(discountPercentage < 0){
        discountPercentage = 0;
    }

    if(discountPercentage > 100){
        discountPercentage = 100;
    }

    $('#discountPercentage').val(discountPercentage);

    let discountAmount = (sub * discountPercentage) / 100;
    let grand = Math.max((sub + taxTotal) - discountAmount, 0);

    $('#subTotal').text(sub.toFixed(2));
    $('#discountAmount').text(discountAmount.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));

    let breakupHtml = '';

    Object.keys(taxMap).forEach(key=>{
        breakupHtml += `
        <div class="d-flex justify-content-between">
            <span>${key}</span>
            <span>₹ ${taxMap[key].toFixed(2)}</span>
        </div>`;
    });

    $('#taxBreakup').html(breakupHtml);
}
$(document).on('input', '#discountPercentage', calculateTotals);
$(document).click(function(e){
    if(!$(e.target).hasClass('serviceSearch')){
        $('.serviceResults').hide();
    }
});

</script>

@endpush
