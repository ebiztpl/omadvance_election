@php
    $pageTitle = 'विभाग रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'विभाग रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Area Wise Report')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row mt-2 align-items-end">
                        <div class="col-md-2">
                            <label>तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>आवेदक प्रकार</label>
                            <select name="office_type" class="form-control">
                                <option value="">-- सभी --</option>
                                <option value="1" {{ request('office_type') == '1' ? 'selected' : '' }}>कमांडर</option>
                                <option value="2" {{ request('office_type') == '2' ? 'selected' : '' }}>कार्यालय
                                </option>
                            </select>
                        </div>

                        <div class="col-md-5 d-flex flex-wrap align-items-center mt-2">
                            @php
                                $filterOptions = [
                                    'sambhag' => 'संभाग',
                                    'jila' => 'जिला',
                                    'vidhansabha' => 'विधानसभा',
                                    'nagar' => 'नगर/मंडल',
                                    'polling' => 'पोलिंग/क्षेत्र',
                                ];
                            @endphp
                            @foreach ($filterOptions as $val => $label)
                                <div class="form-check form-check-inline big-radio-box">
                                    <input class="form-check-input summaryRadio" type="radio" name="summary"
                                        id="summary_{{ $val }}" value="{{ $val }}"
                                        {{ request('summary') == $val ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="summary_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-2 mt-4">
                            <button type="button" class="btn btn-success" onclick="printReport()"
                                style="font-size: 12px; float:inline-end">प्रिंट रिपोर्ट</button>
                        </div>

                    </div>

                    <div class="row mt-3">
                        <div class="col-md-2">
                            <label>संभाग</label>
                            <select name="division_id" id="division_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->division_id }}"
                                        {{ request('division_id') == $division->division_id ? 'selected' : '' }}>
                                        {{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>जिला</label>
                            <select name="district_id" id="district_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($districts ?? [] as $district)
                                    <option value="{{ $district->district_id }}"
                                        {{ request('district_id') == $district->district_id ? 'selected' : '' }}>
                                        {{ $district->district_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>विधानसभा</label>
                            <select name="vidhansabha_id" id="vidhansabha_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($vidhansabhas ?? [] as $vidhansabha)
                                    <option value="{{ $vidhansabha->vidhansabha_id }}"
                                        {{ request('vidhansabha_id') == $vidhansabha->vidhansabha_id ? 'selected' : '' }}>
                                        {{ $vidhansabha->vidhansabha }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>नगर/मंडल</label>
                            <select name="nagar_id" id="txtgram" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($nagars as $nagar)
                                    <option value="{{ $nagar->nagar_id }}"
                                        {{ request('nagar_id') == $nagar->nagar_id ? 'selected' : '' }}>
                                        {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2" style="display: none">
                            <label>पोलिंग/क्षेत्र</label>
                            <select name="polling_id" id="txtpolling" class="form-control"
                                {{ isset($pollings) ? '' : 'disabled' }}>
                                <option value="">--चुने--</option>
                                @foreach ($pollings ?? [] as $polling)
                                    <option value="{{ $polling->gram_polling_id }}"
                                        {{ request('polling_id') == $polling->gram_polling_id ? 'selected' : '' }}>
                                        {{ $polling->polling_name }} ({{ $polling->polling_no }})
                                        @if ($polling->area)
                                            - {{ $polling->area->area_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1 mt-1">
                            <br>
                            <button type="submit" class="btn btn-primary" id="filterBtn"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>

                        <div class="col-md-1 form-check form-check-inline pill-radio-alt mt-3 mr-2"
                            style="white-space: nowrap;">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_received" value="received"
                                {{ request('complaint_type', 'received') === 'received' ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="complaint_received">शिकायत प्राप्त</label>
                        </div>
                        <div class="col-md-1 form-check type-radio form-check-inline pill-radio-alt mt-3 mr-2"
                            style="white-space: nowrap;">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_not_received" value="not_received"
                                {{ request('complaint_type') === 'not_received' ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="complaint_not_received">शिकायत अप्राप्त</label>
                        </div>
                        <div class="col-md-1 form-check type-radio form-check-inline pill-radio-alt mt-3"
                            style="white-space: nowrap;">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_all" value="all"
                                {{ request('complaint_type') === 'all' ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="complaint_all">सभी</label>
                        </div>
                    </div>
                </form>

                {{-- <div class="row mt-3 mb-2">
                    <div class="col-md-12 d-flex justify-content-start align-items-center">
                        <div class="form-check form-check-inline pill-radio-alt">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_received" value="received"
                                {{ request('complaint_type', 'received') === 'received' ? 'checked' : '' }}>
                            <label class="form-check-label" for="complaint_received">शिकायत प्राप्त</label>
                        </div>
                        <div class="form-check form-check-inline pill-radio-alt">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_not_received" value="not_received"
                                {{ request('complaint_type') === 'not_received' ? 'checked' : '' }}>
                            <label class="form-check-label" for="complaint_not_received">शिकायत अप्राप्त</label>
                        </div>
                        <div class="form-check form-check-inline pill-radio-alt">
                            <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                id="complaint_all" value="all"
                                {{ request('complaint_type') === 'all' ? 'checked' : '' }}>
                            <label class="form-check-label" for="complaint_all">सभी</label>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>


        @if (isset($areaData) && $areaData->count())
            <div class="row">
                <div class="col-12">
                    <div class="card" id="reportContainer" style="display: none;">
                        <div class="card-body" id="report-results" style="color: black">

                            <div
                                class="step-header border-header bg-dark text-white p-2 rounded d-flex justify-content-between align-items-center mb-3">
                                @php
                                    $fromDate = request('from_date')
                                        ? \Carbon\Carbon::parse(request('from_date'))->format('d-m-Y')
                                        : null;
                                    $toDate = request('to_date')
                                        ? \Carbon\Carbon::parse(request('to_date'))->format('d-m-Y')
                                        : null;
                                    $officeType = request('office_type');
                                    $officeLabel = null;
                                    if ($officeType == '1') {
                                        $officeLabel = 'कमांडर';
                                    }
                                    if ($officeType == '2') {
                                        $officeLabel = 'कार्यालय';
                                    }
                                @endphp

                                <h5 class="mb-0 text-white">
                                    क्षेत्र रिपोर्ट:
                                    @if ($fromDate && $toDate)
                                        {{ $fromDate }} से {{ $toDate }}
                                    @elseif($fromDate)
                                        {{ $fromDate }} से
                                    @elseif($toDate)
                                        {{ $toDate }} तक
                                    @else
                                        -
                                    @endif
                                </h5>

                                @if ($officeLabel)
                                    <span class="step-number badge bg-light text-dark fs-4" style="font-size: 100%">
                                        {{ $officeLabel }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                {{-- शिकायत प्राप्त --}}
                                @if (request('complaint_type', 'received') === 'received')
                                    @if (isset($areaData) && $areaData->count())

                                   <div class="text-center text-white py-1 rounded mb-2 complaint-type-title"
                                        style="font-size: 1.2rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                        कुल शिकायतें: ({{ $totalsAll['total_registered'] }}),
                                        कुल निरस्त: ({{ $totalsAll['total_cancel'] }}),
                                        कुल समाधान: ({{ $totalsAll['total_solved'] }})
                                    </div>


                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm text-center" style="color: black">
                                                <thead style="background-color: blanchedalmond">
                                                    <tr>
                                                        <th>क्रमांक</th>
                                                        <th>
                                                            @switch($summary)
                                                                @case('sambhag')
                                                                    संभाग
                                                                @break

                                                                @case('jila')
                                                                    जिला
                                                                @break

                                                                @case('vidhansabha')
                                                                    विधानसभा
                                                                @break

                                                                @case('nagar')
                                                                    नगर/मंडल
                                                                @break

                                                                @case('polling')
                                                                    पोलिंग/क्षेत्र
                                                                @break
                                                            @endswitch
                                                        </th>
                                                        <th>कुल पंजीकृत</th>
                                                        <th>कुल निरस्त</th>
                                                        <th>कुल समाधान</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($withComplaints as $index => $data)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $data->area_name }}</td>
                                                            <td>{{ $data->total_registered }}</td>
                                                            <td>{{ $data->total_cancel }}</td>
                                                            <td>{{ $data->total_solved }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-center">कोई शिकायत उपलब्ध नहीं है।</p>
                                    @endif
                                @endif

                                {{-- शिकायत अप्राप्त --}}
                                @if (request('complaint_type') === 'not_received')
                                    @php
                                        $labels = [
                                            'sambhag' => 'संभाग',
                                            'jila' => 'जिला',
                                            'vidhansabha' => 'विधानसभा',
                                            'nagar' => 'नगर/मंडल',
                                            'polling' => 'पोलिंग/क्षेत्र',
                                        ];
                                        $label = $labels[$summary] ?? 'विभाग';
                                    @endphp

                                    <div class="mt-4 mb-2 complaint-type-title text-center text-white py-1 rounded"
                                        style="font-size: 1.2rem; font-weight: 600; background-color:#4a54e9">
                                        अप्राप्त शिकायत: कुल {{ $label }}:
                                        ({{ $totalsAll['total_department'] ?? 0 }}),
                                        पंजीकृत {{ $label }}:
                                        ({{ $totalsRegistered['total_department'] ?? 0 }})
                                    </div>

                                    <table class="table table-bordered table-sm text-center" style="color: black">
                                        <tbody>
                                            @foreach ($noComplaints->chunk(8) as $row)
                                                <tr>
                                                    @foreach ($row as $item)
                                                        <td>{{ $item->area_name }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                {{-- सभी --}}
                                @if (request('complaint_type') === 'all')
                                    <div>
                                        <div class="text-center text-white py-1 rounded mb-2 complaint-type-title"
                                            style="font-size: 1.2rem; font-weight: 600; background-color: #4a54e9">
                                            कुल शिकायतें: ({{ $totalsAll['total_registered'] ?? 0 }}),
                                            कुल निरस्त: ({{ $totalsAll['total_cancel'] ?? 0 }}),
                                            कुल समाधान: ({{ $totalsAll['total_solved'] ?? 0 }})
                                        </div>

                                        <table class="table table-bordered table-sm" style="color: black">
                                            <thead style="background-color: blanchedalmond">
                                                <tr>
                                                    <th>क्र.</th>
                                                    <th>
                                                        @switch($summary)
                                                            @case('sambhag')
                                                                संभाग
                                                            @break

                                                            @case('jila')
                                                                जिला
                                                            @break

                                                            @case('vidhansabha')
                                                                विधानसभा
                                                            @break

                                                            @case('nagar')
                                                                नगर/मंडल
                                                            @break

                                                            @case('polling')
                                                                पोलिंग/क्षेत्र
                                                            @break
                                                        @endswitch
                                                    </th>
                                                    <th>कुल शिकायतें</th>
                                                    <th>कुल निरस्त</th>
                                                    <th>कुल समाधान</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($withComplaints as $row)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $row->area_name }}</td>
                                                        <td>{{ $row->total_registered }}</td>
                                                        <td>{{ $row->total_cancel }}</td>
                                                        <td>{{ $row->total_solved }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">कोई शिकायत
                                                            उपलब्ध
                                                            नहीं</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        @if ($noComplaints->count() > 0)

                                            @php
                                                $labels = [
                                                    'sambhag' => 'संभाग',
                                                    'jila' => 'जिला',
                                                    'vidhansabha' => 'विधानसभा',
                                                    'nagar' => 'नगर/मंडल',
                                                    'polling' => 'पोलिंग/क्षेत्र',
                                                ];
                                                $label = $labels[$summary] ?? 'विभाग';
                                            @endphp

                                            <div class="mt-4 mb-2 complaint-type-title text-center text-white py-1 rounded"
                                                style="font-size: 1.2rem; font-weight: 600; background-color:#4a54e9">
                                                अप्राप्त शिकायत: कुल {{ $label }}:
                                                ({{ $totalsAll['total_department'] ?? 0 }}),
                                                पंजीकृत {{ $label }}:
                                                ({{ $totalsRegistered['total_department'] ?? 0 }})
                                            </div>

                                            <table class="table table-bordered table-sm text-center" style="color: black">
                                                <tbody>
                                                    @foreach ($noComplaints->chunk(8) as $row)
                                                        <tr>
                                                            @foreach ($row as $item)
                                                                <td>{{ $item->area_name }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const summaryRadios = document.querySelectorAll('.summaryRadio');
                const filterBtn = document.getElementById('filterBtn');
                const divisionSelect = document.getElementById('division_id');
                const districtSelect = document.getElementById('district_id');
                const vidhansabhaSelect = document.getElementById('vidhansabha_id');
                const nagarSelect = document.getElementById('txtgram');
                const pollingSelect = document.getElementById('txtpolling');

                function resetDropdowns() {
                    divisionSelect.disabled = true;
                    districtSelect.disabled = true;
                    vidhansabhaSelect.disabled = true;
                    nagarSelect.disabled = true;
                    pollingSelect.disabled = true;

                    divisionSelect.value = '';
                    districtSelect.value = '';
                    vidhansabhaSelect.value = '';
                    nagarSelect.value = '';
                    pollingSelect.value = '';
                }

                function checkFilterState() {
                    const selectedRadio = document.querySelector('.summaryRadio:checked');
                    filterBtn.disabled = !selectedRadio;
                }

                summaryRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        resetDropdowns();
                        checkFilterState();

                        switch (this.value) {
                            case 'sambhag':
                                break;
                            case 'jila':
                                divisionSelect.disabled = false;
                                break;
                            case 'vidhansabha':
                                divisionSelect.disabled = false;
                                districtSelect.disabled = false;
                                break;
                            case 'nagar':
                                divisionSelect.disabled = false;
                                districtSelect.disabled = false;
                                vidhansabhaSelect.disabled = false;
                                break;
                            case 'polling':
                                divisionSelect.disabled = false;
                                districtSelect.disabled = false;
                                vidhansabhaSelect.disabled = false;
                                nagarSelect.disabled = false;
                                break;
                        }
                    });
                });

                $('#division_id, #district_id, #vidhansabha_id, #txtgram, #txtpolling').on('change', function() {
                    checkFilterState();
                });

                resetDropdowns();
                checkFilterState();

                $('#division_id').on('change', function() {
                    const divisionId = $(this).val();
                    $('#district_id').html('<option value="">--चुने--</option>');
                    $('#vidhansabha_id').html('<option value="">--चुने--</option>');
                    $('#txtgram').html('<option value="">--चुने--</option>');
                    if (!divisionId) return;
                    $.get('/admin/get-districts/' + divisionId, function(data) {
                        $('#district_id').append(data);
                    });
                });

                $('#district_id').on('change', function() {
                    const districtId = $(this).val();
                    $('#vidhansabha_id').html('<option value="">--चुने--</option>');
                    $('#txtgram').html('<option value="">--चुने--</option>');
                    if (!districtId) return;
                    $.get('/admin/get-vidhansabha/' + districtId, function(data) {
                        $('#vidhansabha_id').append(data);
                    });
                });

                $('#vidhansabha_id').on('change', function() {
                    const vidhansabhaId = $(this).val();
                    $('#txtgram').html('<option value="">--चुने--</option>');
                    if (!vidhansabhaId) return;
                    $.get('/admin/get-nagars-by-vidhansabha/' + vidhansabhaId, function(data) {
                        $('#txtgram').append(data);
                    });
                });

                @if (isset($areaData) && $areaData->count())
                    document.getElementById('reportContainer').style.display = 'block';
                @endif
            });

            document.addEventListener('DOMContentLoaded', function() {
                const radios = document.querySelectorAll('.complaintTypeRadio');
                const table = document.querySelector('#report-results');

                if (table) {
                    radios.forEach(radio => radio.removeAttribute('disabled'));
                }
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const form = document.getElementById('complaintFilterForm');
                        form.submit();
                    });
                });
            });


            window.addEventListener('load', function() {
                if (window.location.search) {
                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            });

            function printReport() {
                const content = document.getElementById('report-results').innerHTML;
                const printWindow = window.open('', '', 'height=800,width=1200');
                printWindow.document.write(`
                        <html>
                        <head>
                            <title>Area Report</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .step-header {
                                    background-color: #343a40 !important;
                                    color: white !important;
                                    padding: 4px !important; 
                                    border-radius: 6px;          
                                    margin-bottom: 10px;        
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                }
                                .badge {
                                    background-color: #f8f9fa !important;
                                    color: #000 !important;
                                    border: 1px solid #000;
                                    padding: 5px 10px;
                                    border-radius: 6px;
                                }
                                .complaint-type-title {
                                    background-color: #4a54e9 !important;
                                    color: white !important;
                                    font-size: 1.2rem;
                                    font-weight: bold;
                                    text-align: center;
                                    padding: 8px;
                                    border-radius: 6px;
                                    margin-bottom: 12px;
                                    margin-top: 6px;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                }
                                table th, table td {
                                    border: 1px solid #000;
                                    padding: 6px;
                                    text-align: left;
                                }
                                table thead tr {
                                    background-color: blanchedalmond !important;
                                    font-weight: bold;
                                }
                            </style>
                        </head>
                        <body>
                            ${content}
                        </body>
                        </html>
                    `);

                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }
        </script>
    @endpush
@endsection
