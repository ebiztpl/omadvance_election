@php
    $pageTitle = 'रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Detailed Report')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" class="mb-1" id="complaintFilterForm">
                    <div class="row mt-1">
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
                                <option value="1" {{ request('office_type') == '1' ? 'selected' : '' }}>कमांडर
                                </option>
                                <option value="2" {{ request('office_type') == '2' ? 'selected' : '' }}>कार्यालय
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px">फ़िल्टर</button>
                        </div>

                        @if ($hasFilter)
                            <div class="col-md-6">
                                <h1 class="label-radio" style="font-weight: bold">फ़िल्टर प्रकार चुनें: </h1>

                                @php
                                    $options = [
                                        'jati' => 'जाति के द्वारा',
                                        'department' => 'विभाग के द्वारा',
                                        'area' => 'ग्राम चौपाल के द्वारा',
                                        'all' => 'सभी',
                                    ];
                                    $selectedSummary = request('summary', 'jati');
                                @endphp

                                @foreach ($options as $val => $label)
                                    <div class="big-radio-box">
                                        <input type="radio" id="summary_{{ $val }}" name="summary"
                                            value="{{ $val }}" {{ $selectedSummary == $val ? 'checked' : '' }}>
                                        <label for="summary_{{ $val }}">{{ $label }}</label>
                                    </div>
                                @endforeach


                                <button type="button" class="btn btn-success mt-2" onclick="printReport()"
                                    style="font-size: 12px; float: inline-end">प्रिंट
                                    रिपोर्ट</button>
                            </div>
                        @endif
                    </div>
                </form>

                @if ($hasFilter)
                    {{-- <div class="col-md-6 mt-3">
                        <label>फ़िल्टर प्रकार चुनें</label><br>

                        @php
                            $options = [
                                'jati' => 'जाति के द्वारा',
                                'department' => 'विभाग के द्वारा',
                                'area' => 'ग्राम चौपाल के द्वारा',
                                'all' => 'सभी',
                            ];
                            $selectedSummary = request('summary', 'jati');
                        @endphp

                        @foreach ($options as $val => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="summary_{{ $val }}"
                                    name="summary" value="{{ $val }}"
                                    {{ $selectedSummary == $val ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="summary_{{ $val }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div> --}}

                    @php
                        if ($selectedSummary == 'all' && empty($summaryType)) {
                            $summaryType = 'all_area';
                        }
                    @endphp


                    <div class="mt-1" id="allOptionsContainer"
                        style="{{ $selectedSummary == 'all' ? 'display:flex;' : 'display:none;' }}">

                        @php
                            $allOptions = [
                                'all_area' => 'सभी क्षेत्र',
                                'all_department' => 'सभी विभाग',
                                'all_jati' => 'सभी जाति',
                            ];
                        @endphp

                        @foreach ($allOptions as $val => $label)
                            <div class="pill-radio-alt">
                                <input type="radio" id="{{ $val }}" name="all_filter"
                                    value="{{ $val }}"
                                    {{ $summaryType == $val || ($summaryType == 'all' && $val == 'all_area') ? 'checked' : '' }}>
                                <label for="{{ $val }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>


                    <div id="areaFilters" class="row mt-1"
                        style="{{ request('summary') == 'area' ? 'display:flex;' : 'display:none;' }}">
                        <div class="col-md-2 mb-2">
                            <label>संभाग</label>
                            <select name="division_id" id="division_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($divisions as $d)
                                    <option value="{{ $d->division_id }}"
                                        {{ $divisionId == $d->division_id ? 'selected' : '' }}>
                                        {{ $d->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>जिला</label>
                            <select name="district_id" id="district_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($districts as $dist)
                                    <option value="{{ $dist->district_id }}"
                                        {{ $districtId == $dist->district_id ? 'selected' : '' }}>
                                        {{ $dist->district_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>विधानसभा</label>
                            <select name="vidhansabha_id" id="vidhansabha_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($vidhansabhas as $vid)
                                    <option value="{{ $vid->vidhansabha_id }}"
                                        {{ $vidhansabhaId == $vid->vidhansabha_id ? 'selected' : '' }}>
                                        {{ $vid->vidhansabha }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>नगर/मंडल</label>
                            <select name="gram_id" id="txtgram" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($nagars as $nagar)
                                    <option value="{{ $nagar->nagar_id }}"
                                        {{ request('gram_id') == $nagar->nagar_id ? 'selected' : '' }}>
                                        {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 mt-2">
                            <br>
                            <button type="button" id="areafilters_filters" class="btn btn-primary"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>
                @endif



            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    @if (isset($hasFilter) && $hasFilter)
                        <div class="card-body" id="report-results" style="color: black">

                            <div
                                class="step-header border-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
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
                                    रिपोर्ट:
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


                            @if ($summaryType == 'all_area')
                                <div>
                                    {{-- <strong>क्षेत्र के अनुसार:</strong> --}}
                                    <div class="text-center text-white py-1 rounded mb-3 complaint-type-title"
                                        style="font-size: 1.5rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                        क्षेत्र के अनुसार: ({{ $areaCounts->sum('count') }})
                                    </div>

                                    @if ($areaCounts->isEmpty())
                                        <p class="text-center text-muted">डाटा उपलब्ध नहीं है</p>
                                    @else
                                        <table class="table table-bordered table-sm" style="color: black">
                                            <thead style="background-color: blanchedalmond">
                                                <tr>
                                                    <th>क्षेत्र</th>
                                                    <th>कुल</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($areaCounts as $row)
                                                    <tr>
                                                        <td>{{ $row['area'] }}</td>
                                                        <td>{{ $row['count'] }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr style="background-color: #aff5af">
                                                    <th>कुल</th>
                                                    <th>{{ $areaCounts->sum('count') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @elseif ($summaryType == 'all_department')
                                <div>
                                    <div class="text-center text-white py-1 rounded mb-3 complaint-type-title"
                                        style="font-size: 1.5rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                        विभाग के अनुसार: ({{ $departmentCounts->sum('count') }})
                                    </div>

                                    @if ($departmentCounts->isEmpty())
                                        <p class="text-center text-muted">डाटा उपलब्ध नहीं है</p>
                                    @else
                                        <table class="table table-bordered table-sm" style="color: black">
                                            <thead style="background-color: blanchedalmond">
                                                <tr>
                                                    <th>विभाग</th>
                                                    <th>कुल</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($departmentCounts as $row)
                                                    <tr>
                                                        <td>{{ $row['department'] }}</td>
                                                        <td>{{ $row['count'] }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr style="background-color: #aff5af">
                                                    <th>कुल</th>
                                                    <th>{{ $departmentCounts->sum('count') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @elseif ($summaryType == 'all_jati')
                                <div>
                                    <div class="text-center text-white py-1 rounded mb-3 complaint-type-title"
                                        style="font-size: 1.5rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                        जाति के अनुसार: ({{ $jatiCounts->sum('count') }})
                                    </div>

                                    @if ($jatiCounts->isEmpty())
                                        <p class="text-center text-muted">डाटा उपलब्ध नहीं है</p>
                                    @else
                                        <table class="table table-bordered table-sm" style="color: black">
                                            <thead style="background-color: blanchedalmond">
                                                <tr>
                                                    <th>जाति</th>
                                                    <th>कुल</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($jatiCounts as $row)
                                                    <tr>
                                                        <td>{{ $row['jati'] }}</td>
                                                        <td>{{ $row['count'] }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr style="background-color: #aff5af">
                                                    <th>कुल</th>
                                                    <th>{{ $jatiCounts->sum('count') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @else
                                @php
                                    $currentCounts =
                                        $summaryType == 'area'
                                            ? $areaCounts
                                            : ($summaryType == 'department'
                                                ? $departmentCounts
                                                : $jatiCounts);
                                    $label =
                                        $summaryType == 'area'
                                            ? 'क्षेत्र'
                                            : ($summaryType == 'department'
                                                ? 'विभाग'
                                                : 'जाति');
                                @endphp

                                @php
                                    $appliedFilters = [];

                                    if ($divisionId) {
                                        $division = $divisions->firstWhere('division_id', $divisionId);
                                        if ($division) {
                                            $appliedFilters[] = '<strong>संभाग: </strong>' . $division->division_name;
                                        }
                                    }

                                    if ($districtId) {
                                        $district = $districts->firstWhere('district_id', $districtId);
                                        if ($district) {
                                            $appliedFilters[] = '<strong>जिला: </strong>' . $district->district_name;
                                        }
                                    }

                                    if ($vidhansabhaId) {
                                        $vidhansabha = $vidhansabhas->firstWhere('vidhansabha_id', $vidhansabhaId);
                                        if ($vidhansabha) {
                                            $appliedFilters[] =
                                                '<strong>विधानसभा: </strong>' . $vidhansabha->vidhansabha;
                                        }
                                    }

                                    if (request('gram_id')) {
                                        $nagar = $nagars->firstWhere('nagar_id', request('gram_id'));
                                        if ($nagar) {
                                            $appliedFilters[] =
                                                '<strong>नगर/मंडल: </strong>' .
                                                $nagar->nagar_name .
                                                ' - ' .
                                                ($nagar->mandal->mandal_name ?? '');
                                        }
                                    }
                                @endphp

                                <div>
                                    <div class="text-center text-white py-1 rounded mb-3 complaint-type-title"
                                        style="font-size: 1.5rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                        {{ $label }} के अनुसार: ({{ $currentCounts->sum('count') }})
                                    </div>

                                    @if ($currentCounts->isEmpty())
                                        <p class="text-center text-muted">डाटा उपलब्ध नहीं है</p>
                                    @else
                                        <table class="table table-bordered table-sm" style="color: black">
                                            <thead style="background-color: blanchedalmond">
                                                <tr>
                                                    <th>{{ $label }}</th>
                                                    <th>कुल</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($currentCounts as $row)
                                                    <tr>
                                                        <td>
                                                            @if ($summaryType == 'area' && count($appliedFilters))
                                                                {!! implode(' → ', $appliedFilters) !!} → <strong>क्षेत्र:</strong>
                                                                {{ $row[$summaryType] }}
                                                            @else
                                                                {{ $row[$summaryType] }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['count'] }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr style="background-color: #aff5af">
                                                    <th>कुल</th>
                                                    <th>{{ $currentCounts->sum('count') }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const radios = document.querySelectorAll('input[name="summary"]');
                const form = document.getElementById('complaintFilterForm');
                const areaFilters = document.getElementById('areaFilters');
                const reportResults = document.getElementById('report-results');
                const areaFilterBtn = document.getElementById('areafilters_filters');

                function toggleAreaFilters() {
                    if (!areaFilters) return;
                    const selected = document.querySelector('input[name="summary"]:checked');
                    areaFilters.style.display = (selected && selected.value === 'area') ? 'flex' : 'none';
                }


                function toggleAllOptions() {
                    const selected = document.querySelector('input[name="summary"]:checked');
                    const allOptions = document.getElementById('allOptionsContainer');
                    if (!allOptions) return;
                    allOptions.style.display = (selected && selected.value === 'all') ? 'flex' : 'none';
                }

                function submitFormAJAX(summaryValue, extraData = null) {
                    if (!reportResults || !form) return;
                    $("#loader-wrapper").show();

                    let formData = extraData ? extraData : $(form).serialize();
                    if (summaryValue && !formData.includes('summary=')) {
                        formData += '&summary=' + summaryValue;
                    }


                    $.ajax({
                        url: form.action || window.location.href,
                        method: 'GET',
                        data: formData,
                        success: function(response) {
                            const newContent = $(response).find('#report-results').html();
                            reportResults.innerHTML = newContent ||
                                '<p class="text-muted text-center">फिल्टर चुनें या डाटा उपलब्ध नहीं है।</p>';
                            toggleAreaFilters();
                            toggleAllOptions();
                            $("#loader-wrapper").hide();
                        },
                        error: function() {
                            alert('Something went wrong');
                            $("#loader-wrapper").hide();
                        }
                    });
                }

                // Radio change
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        toggleAreaFilters();
                        toggleAllOptions();
                        submitFormAJAX(this.value);
                    });
                });

                const allFilterRadios = document.querySelectorAll('input[name="all_filter"]');
                allFilterRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Append the correct summaryType for server
                        let summaryMap = {
                            'all_area': 'all_area',
                            'all_department': 'all_department',
                            'all_jati': 'all_jati'
                        };
                        const summaryValue = summaryMap[this.value];
                        submitFormAJAX(summaryValue);
                    });
                });

                // Area filter button click
                if (areaFilterBtn) {
                    areaFilterBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const summaryValue = 'area';
                        const divisionId = document.getElementById('division_id')?.value;
                        const districtId = document.getElementById('district_id')?.value;
                        const vidhansabhaId = document.getElementById('vidhansabha_id')?.value;
                        const gramId = document.getElementById('txtgram')?.value;
                        const fromDate = document.getElementById('from_date')?.value;
                        const toDate = document.getElementById('to_date')?.value;
                        const officeType = document.querySelector('select[name="office_type"]')?.value;

                        let formData = `summary=${summaryValue}`;
                        if (divisionId) formData += `&division_id=${divisionId}`;
                        if (districtId) formData += `&district_id=${districtId}`;
                        if (vidhansabhaId) formData += `&vidhansabha_id=${vidhansabhaId}`;
                        if (gramId) formData += `&gram_id=${gramId}`;
                        if (fromDate) formData += `&from_date=${fromDate}`;
                        if (toDate) formData += `&to_date=${toDate}`;
                        if (officeType) formData += `&office_type=${officeType}`;

                        submitFormAJAX(summaryValue, formData);
                    });
                }

                toggleAreaFilters();

                // Default load if summary=area
                const selectedSummary = document.querySelector('input[name="summary"]:checked')?.value;
                if (selectedSummary === 'area') {
                    submitFormAJAX('area');
                }

                // Dependent dropdowns
                $('#division_id').on('change', function() {
                    let divisionId = $(this).val();
                    if (!divisionId) return;

                    $.get('/admin/get-districts/' + divisionId, function(data) {
                        $('#district_id').html('<option value="">--चुने--</option>' + data);

                        let firstDistrict = $('#district_id option:first').val();
                        if (firstDistrict) {
                            $.get('/admin/get-vidhansabha/' + firstDistrict, function(data) {
                                $('#vidhansabha_id').html(data);
                            });
                        }
                    });
                });

                $('#district_id').on('change', function() {
                    let districtId = $(this).val();
                    if (!districtId) return;

                    $.get("{{ route('get.vidhansabha_filter', ':id') }}".replace(':id', districtId), function(
                        data) {
                        $('#vidhansabha_id').html('<option value="">--चुने--</option>' + data);
                    });
                });

                $('#vidhansabha_id').on('change', function() {
                    let vidhansabhaId = $(this).val();
                    if (!vidhansabhaId) return;

                    $.get('/admin/get-nagars-by-vidhansabha/' + vidhansabhaId, function(data) {
                        $('#txtgram').html('<option value="">--चुने--</option>');
                        $.each(data, function(i, option) {
                            $('#txtgram').append(option);
                        });
                    });
                });



                window.addEventListener('load', function() {
                    if (window.location.search) {
                        const cleanUrl = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, cleanUrl);
                    }
                });
            });

            function printReport() {
                const content = document.getElementById('report-results').innerHTML;
                const printWindow = window.open('', '', 'height=800,width=1200');
                printWindow.document.write(`
                        <html>
                        <head>
                            <title>Report</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .step-header {
                                    background-color: #343a40 !important;
                                    color: white !important;
                                    padding: 6px !important; 
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

                                /* Last row of tbody (total row) */
                                table tbody tr:last-child {
                                    background-color: #aff5af !important;
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
