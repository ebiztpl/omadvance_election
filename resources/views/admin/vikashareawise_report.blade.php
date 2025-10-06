@php
    $pageTitle = 'विकास कार्य क्षेत्र रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'विकास कार्य क्षेत्र रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Area Wise Report')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row mt-1 align-items-end">
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
                                <option value="1" {{ request('office_type', '2') == '1' ? 'selected' : '' }}>कमांडर
                                </option>
                                <option value="2" {{ request('office_type', '2') == '2' ? 'selected' : '' }}>कार्यालय
                                </option>
                            </select>
                        </div>

                        <div class="col-md-7 d-flex flex-wrap align-items-center mt-2">
                            @php
                                $filterOptions = [
                                    'sambhag' => 'संभाग',
                                    'jila' => 'जिला',
                                    'vidhansabha' => 'विधानसभा',
                                    'mandal' => 'मंडल',
                                    'nagar' => 'कमांड एरिया',
                                    'polling' => 'पोलिंग',
                                    'area' => 'ग्राम/वार्ड चौपाल',
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

                            <button type="button" class="btn btn-sm btn-success" onclick="printReport()"
                                style="font-size: 12px; margin-left: 14px">प्रिंट रिपोर्ट</button>

                            <button type="button" class="btn btn-sm btn-info" style="margin-left: 14px"
                                onclick="exportExcel()">Excel</button>

                        </div>
                    </div>

                    <div class="row mt-1">
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
                            <label>मंडल</label>
                            <select name="mandal_id" id="mandal_id" class="form-control">
                                <option value="">-- सभी --</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>कमांड एरिया</label>
                            <select name="gram_id" id="gram_id" class="form-control">
                                <option value="">-- सभी --</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मतदान केंद्र/क्रमांक</label>
                            <select name="polling_id" id="polling_id" class="form-control">
                                <option value="">-- सभी --</option>
                            </select>
                        </div>

                        <div class="col-md-2" style="display: none">
                            <label>ग्राम/वार्ड चौपाल</label>
                            <select name="area_id" id="area_id" class="form-control">
                                <option value="">-- सभी --</option>
                            </select>
                        </div>

                        {{-- <div class="col-md-2">
                            <label>मंडल</label>
                            <select name="mandal_id" id="mandal_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($mandals as $mandal)
                                    <option value="{{ $mandal->mandal_id }}">{{ $mandal->mandal_name }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        {{-- <div class="col-md-2">
                            <label>कमांड एरिया/मंडल</label>
                            <select name="nagar_id" id="txtgram" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($nagars as $nagar)
                                    <option value="{{ $nagar->nagar_id }}"
                                        {{ request('nagar_id') == $nagar->nagar_id ? 'selected' : '' }}>
                                        {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}

                        {{-- <div class="col-md-2" style="display: none">
                            <label>पोलिंग/ग्राम/वार्ड चौपाल</label>
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
                        </div> --}}

                        <div class="col-md-1 mt-1">
                            <br>
                            <button type="submit" class="btn btn-primary" id="filterBtn"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>

                    <div class="row mt-2 mb-2">
                        <div class="col-md-12 d-flex justify-content-start align-items-center" style="gap: 6px;">
                            <div class="col-md-1 form-check form-check-inline big-radio-box" style="white-space: nowrap;">
                                <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                    id="complaint_received" value="received"
                                    {{ request('complaint_type', 'received') === 'received' ? 'checked' : '' }} disabled>
                                <label class="form-check-label" for="complaint_received">विकास कार्य प्राप्त</label>
                            </div>
                            <div class="col-md-1 form-check type-radio form-check-inline big-radio-box"
                                style="white-space: nowrap;">
                                <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                    id="complaint_not_received" value="not_received"
                                    {{ request('complaint_type') === 'not_received' ? 'checked' : '' }} disabled>
                                <label class="form-check-label" for="complaint_not_received">विकास कार्य अप्राप्त</label>
                            </div>
                            <div class="col-md-1 form-check type-radio form-check-inline big-radio-box"
                                style="white-space: nowrap;">
                                <input class="form-check-input complaintTypeRadio" type="radio" name="complaint_type"
                                    id="complaint_all" value="all"
                                    {{ request('complaint_type') === 'all' ? 'checked' : '' }} disabled>
                                <label class="form-check-label" for="complaint_all">सभी</label>
                            </div>





                        </div>
                    </div>
                </form>


            </div>
        </div>


        @if (isset($areaData) && $areaData->count())
            <div class="row">
                <div class="col-12">
                    <div class="card" id="reportContainer" style="display: none;">
                        <div class="card-body" id="report-results" style="color: black">

                            
                            @php
                                $buildComplaintUrl = function ($data, $status = null) {
                                    $officeType = request('office_type');
                                    $baseRoute =
                                        $officeType == '1'
                                            ? route('commander.complaint.view')
                                            : route('operator.complaint.view');

                                    $params = [
                                        'complaint_type' => 'विकास',
                                        'from_date' => request('from_date'),
                                        'to_date' => request('to_date'),
                                    ];

                                    $summary = request('summary');

                                    switch ($summary) {
                                        case 'sambhag':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            }
                                            break;

                                        case 'jila':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            }
                                            break;

                                        case 'vidhansabha':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            } elseif (request('district_id')) {
                                                $params['district_id'] = request('district_id');
                                            }
                                            if (isset($data->vidhansabha_id)) {
                                                $params['vidhansabha_id'] = $data->vidhansabha_id;
                                            }
                                            break;

                                        case 'mandal':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            } elseif (request('district_id')) {
                                                $params['district_id'] = request('district_id');
                                            }
                                            if (isset($data->vidhansabha_id)) {
                                                $params['vidhansabha_id'] = $data->vidhansabha_id;
                                            } elseif (request('vidhansabha_id')) {
                                                $params['vidhansabha_id'] = request('vidhansabha_id');
                                            }
                                            if (isset($data->mandal_id)) {
                                                $params['mandal_id'] = $data->mandal_id;
                                            }
                                            break;

                                        case 'nagar':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            } elseif (request('district_id')) {
                                                $params['district_id'] = request('district_id');
                                            }
                                            if (isset($data->vidhansabha_id)) {
                                                $params['vidhansabha_id'] = $data->vidhansabha_id;
                                            } elseif (request('vidhansabha_id')) {
                                                $params['vidhansabha_id'] = request('vidhansabha_id');
                                            }
                                            if (isset($data->mandal_id)) {
                                                $params['mandal_id'] = $data->mandal_id;
                                            } elseif (request('mandal_id')) {
                                                $params['mandal_id'] = request('mandal_id');
                                            }
                                            if (isset($data->gram_id)) {
                                                $params['gram_id'] = $data->gram_id;
                                            }
                                            break;

                                        case 'polling':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            } elseif (request('district_id')) {
                                                $params['district_id'] = request('district_id');
                                            }
                                            if (isset($data->vidhansabha_id)) {
                                                $params['vidhansabha_id'] = $data->vidhansabha_id;
                                            } elseif (request('vidhansabha_id')) {
                                                $params['vidhansabha_id'] = request('vidhansabha_id');
                                            }
                                            if (isset($data->mandal_id)) {
                                                $params['mandal_id'] = $data->mandal_id;
                                            } elseif (request('mandal_id')) {
                                                $params['mandal_id'] = request('mandal_id');
                                            }
                                            if (isset($data->gram_id)) {
                                                $params['gram_id'] = $data->gram_id;
                                            } elseif (request('gram_id')) {
                                                $params['gram_id'] = request('gram_id');
                                            }
                                            if (isset($data->polling_id)) {
                                                $params['polling_id'] = $data->polling_id;
                                            }
                                            break;

                                        case 'area':
                                            if (isset($data->division_id)) {
                                                $params['division_id'] = $data->division_id;
                                            } elseif (request('division_id')) {
                                                $params['division_id'] = request('division_id');
                                            }
                                            if (isset($data->district_id)) {
                                                $params['district_id'] = $data->district_id;
                                            } elseif (request('district_id')) {
                                                $params['district_id'] = request('district_id');
                                            }
                                            if (isset($data->vidhansabha_id)) {
                                                $params['vidhansabha_id'] = $data->vidhansabha_id;
                                            } elseif (request('vidhansabha_id')) {
                                                $params['vidhansabha_id'] = request('vidhansabha_id');
                                            }
                                            if (isset($data->mandal_id)) {
                                                $params['mandal_id'] = $data->mandal_id;
                                            } elseif (request('mandal_id')) {
                                                $params['mandal_id'] = request('mandal_id');
                                            }
                                            if (isset($data->gram_id)) {
                                                $params['gram_id'] = $data->gram_id;
                                            } elseif (request('gram_id')) {
                                                $params['gram_id'] = request('gram_id');
                                            }
                                            if (isset($data->polling_id)) {
                                                $params['polling_id'] = $data->polling_id;
                                            } elseif (request('polling_id')) {
                                                $params['polling_id'] = request('polling_id');
                                            }
                                            if (isset($data->area_id)) {
                                                $params['area_id'] = $data->area_id;
                                            }
                                            break;
                                    }

                                    if ($status) {
                                        $params['complaint_status'] = $status;
                                    }

                                    return $baseRoute . '?' . http_build_query($params);
                                };
                            @endphp


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
                                    विकास कार्य क्षेत्र रिपोर्ट:
                                    @if ($fromDate && $toDate)
                                        {{ $fromDate }} से {{ $toDate }}
                                    @elseif($fromDate)
                                        {{ $fromDate }} से
                                    @elseif($toDate)
                                        {{ $toDate }} तक
                                    @else
                                        {{ now()->format('d-m-Y') }} (तक)
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
                                            कुल विकास कार्य: ({{ $totalsAll['total_registered'] }}),
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

                                                                @case('mandal')
                                                                    मंडल
                                                                @break

                                                                @case('nagar')
                                                                    कमांड एरिया
                                                                @break

                                                                @case('polling')
                                                                    पोलिंग
                                                                @break

                                                                @case('area')
                                                                    ग्राम/वार्ड चौपाल
                                                                @break
                                                            @endswitch
                                                        </th>
                                                        <th>कुल पंजीकृत</th>
                                                        <th>कुल निरस्त</th>
                                                        <th>कुल समाधान</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($withComplaints as $data)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $data->area_name }}</td>
                                                            <td>
                                                                @if (request('office_type') && $data->total_registered > 0)
                                                                    <a href="{{ $buildComplaintUrl($data) }}"
                                                                        class="text-primary" target="_blank">
                                                                        {{ $data->total_registered }}
                                                                    </a>
                                                                @else
                                                                    {{ $data->total_registered }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (request('office_type') && $data->total_cancel > 0)
                                                                    <a href="{{ $buildComplaintUrl($data, 5) }}"
                                                                        class="text-primary" target="_blank">
                                                                        {{ $data->total_cancel }}
                                                                    </a>
                                                                @else
                                                                    {{ $data->total_cancel }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (request('office_type') && $data->total_solved > 0)
                                                                    <a href="{{ $buildComplaintUrl($data, 4) }}"
                                                                        class="text-primary" target="_blank">
                                                                        {{ $data->total_solved }}
                                                                    </a>
                                                                @else
                                                                    {{ $data->total_solved }}
                                                                @endif
                                                            </td>
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
                                        </div>
                                    @else
                                        <p class="text-center">कोई विकास कार्य उपलब्ध नहीं है।</p>
                                    @endif
                                @endif

                                {{-- शिकायत अप्राप्त --}}
                                @if (request('complaint_type') === 'not_received')
                                    @php
                                        $labels = [
                                            'sambhag' => 'संभाग',
                                            'jila' => 'जिला',
                                            'vidhansabha' => 'विधानसभा',
                                            'mandal' => 'मंडल',
                                            'nagar' => 'कमांड एरिया',
                                            'polling' => 'पोलिंग',
                                            'area' => 'ग्राम/वार्ड चौपाल',
                                        ];
                                        $label = $labels[$summary] ?? 'विभाग';
                                    @endphp

                                    <div class="mt-4 mb-2 complaint-type-title text-center text-white py-1 rounded"
                                        style="font-size: 1.2rem; font-weight: 600; background-color:#4a54e9">
                                        अप्राप्त विकास कार्य: कुल {{ $label }}:
                                        ({{ $totalsAll['total_areas'] ?? 0 }}),
                                        पंजीकृत {{ $label }}:
                                        ({{ $totalsRegistered['total_areas'] ?? 0 }})
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
                                            कुल विकास कार्य: ({{ $totalsAll['total_registered'] ?? 0 }}),
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

                                                            @case('mandal')
                                                                मंडल
                                                            @break

                                                            @case('nagar')
                                                                कमांड एरिया
                                                            @break

                                                            @case('polling')
                                                                पोलिंग
                                                            @break

                                                            @case('area')
                                                                ग्राम/वार्ड चौपाल
                                                            @break
                                                        @endswitch
                                                    </th>
                                                    <th>कुल विकास कार्य</th>
                                                    <th>कुल निरस्त</th>
                                                    <th>कुल समाधान</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($withComplaints as $data)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $data->area_name }}</td>
                                                        <td>
                                                            @if (request('office_type') && $data->total_registered > 0)
                                                                <a href="{{ $buildComplaintUrl($data) }}"
                                                                    class="text-primary" target="_blank">
                                                                    {{ $data->total_registered }}
                                                                </a>
                                                            @else
                                                                {{ $data->total_registered }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (request('office_type') && $data->total_cancel > 0)
                                                                <a href="{{ $buildComplaintUrl($data, 5) }}"
                                                                    class="text-primary" target="_blank">
                                                                    {{ $data->total_cancel }}
                                                                </a>
                                                            @else
                                                                {{ $data->total_cancel }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (request('office_type') && $data->total_solved > 0)
                                                                <a href="{{ $buildComplaintUrl($data, 4) }}"
                                                                    class="text-primary" target="_blank">
                                                                    {{ $data->total_solved }}
                                                                </a>
                                                            @else
                                                                {{ $data->total_solved }}
                                                            @endif
                                                        </td>
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
                                                    'mandal' => 'मंडल',
                                                    'nagar' => 'कमांड एरिया',
                                                    'polling' => 'पोलिंग',
                                                    'area' => 'ग्राम/वार्ड चौपाल',
                                                ];
                                                $label = $labels[$summary] ?? 'क्षेत्र';
                                            @endphp

                                            <div class="mt-4 mb-2 complaint-type-title text-center text-white py-1 rounded"
                                                style="font-size: 1.2rem; font-weight: 600; background-color:#4a54e9">
                                                अप्राप्त विकास कार्य: कुल {{ $label }}:
                                                ({{ $totalsAll['total_areas'] ?? 0 }}),
                                                पंजीकृत {{ $label }}:
                                                ({{ $totalsRegistered['total_areas'] ?? 0 }})
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
            function exportExcel() {
                let form = document.getElementById('complaintFilterForm');
                let url = new URL(form.action, window.location.origin);
                let params = new URLSearchParams(new FormData(form));
                params.append('export', 'excel');
                window.location.href = url.pathname + '?' + params.toString();
            }

            document.addEventListener('DOMContentLoaded', function() {
                const summaryRadios = document.querySelectorAll('.summaryRadio');
                const complaintRadios = document.querySelectorAll('.complaintTypeRadio');
                const filterBtn = document.getElementById('filterBtn');

                const divisionSelect = document.getElementById('division_id');
                const districtSelect = document.getElementById('district_id');
                const vidhansabhaSelect = document.getElementById('vidhansabha_id');
                const mandalSelect = document.getElementById('mandal_id');
                const nagarSelect = document.getElementById('gram_id');
                const pollingSelect = document.getElementById('polling_id');
                const areaSelect = document.getElementById('area_id');

                const dropdowns = [divisionSelect, districtSelect, vidhansabhaSelect, mandalSelect, nagarSelect,
                    pollingSelect, areaSelect
                ];

                // Disable all dropdowns and filter button initially
                function disableAllDropdowns() {
                    dropdowns.forEach(el => {
                        if (el) el.disabled = true;
                    });
                    filterBtn.disabled = true;
                }

                // Enable dropdowns based on selected summary
                function enableDropdownsForSummary(value) {
                    disableAllDropdowns();
                    switch (value) {
                        case 'jila':
                            divisionSelect.disabled = false;
                            break;
                        case 'vidhansabha':
                            divisionSelect.disabled = false;
                            districtSelect.disabled = false;
                            break;
                        case 'mandal':
                            divisionSelect.disabled = false;
                            districtSelect.disabled = false;
                            vidhansabhaSelect.disabled = false;
                            break;
                        case 'nagar':
                            divisionSelect.disabled = false;
                            districtSelect.disabled = false;
                            vidhansabhaSelect.disabled = false;
                            mandalSelect.disabled = false;
                            break;
                        case 'polling':
                            divisionSelect.disabled = false;
                            districtSelect.disabled = false;
                            vidhansabhaSelect.disabled = false;
                            mandalSelect.disabled = false;
                            nagarSelect.disabled = false;
                            break;
                        case 'area':
                            divisionSelect.disabled = false;
                            districtSelect.disabled = false;
                            vidhansabhaSelect.disabled = false;
                            mandalSelect.disabled = false;
                            nagarSelect.disabled = false;
                            pollingSelect.disabled = false;
                            break;
                        default:
                            break;
                    }
                    filterBtn.disabled = false;
                }

                // Summary radio change
                summaryRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        enableDropdownsForSummary(this.value);
                    });
                });

                // Complaint type radios: submit form on change
                complaintRadios.forEach(radio => {
                    radio.removeAttribute('disabled');
                    radio.addEventListener('change', function() {
                        document.getElementById('complaintFilterForm').submit();
                    });
                });

                // Initialize dropdowns
                disableAllDropdowns();
                const selectedSummary = document.querySelector('.summaryRadio:checked');
                if (selectedSummary) enableDropdownsForSummary(selectedSummary.value);

                // Populate dependent dropdowns with previous selections
                function populateDropdownsAfterReload() {
                    const divisionId = $('#division_id').val();
                    const districtId = '{{ request('district_id') }}';
                    const vidhansabhaId = '{{ request('vidhansabha_id') }}';
                    const mandalId = '{{ request('mandal_id') }}';
                    const gramId = '{{ request('gram_id') }}';
                    const pollingId = '{{ request('polling_id') }}';
                    const areaId = '{{ request('area_id') }}';

                    if (divisionId) {
                        $.get('/admin/get-districts/' + divisionId, function(data) {
                            $('#district_id').html('<option value="">--चुने--</option>');
                            data.forEach(option => $('#district_id').append(option));
                            if (districtId) $('#district_id').val(districtId).trigger('change');
                        });
                    }
                    if (districtId) {
                        $.get('/admin/get-vidhansabha/' + districtId, function(data) {
                            $('#vidhansabha_id').html('<option value="">--चुने--</option>');
                            data.forEach(option => $('#vidhansabha_id').append(option));
                            if (vidhansabhaId) $('#vidhansabha_id').val(vidhansabhaId).trigger('change');
                        });
                    }
                    if (vidhansabhaId) {
                        $.get('/admin/get-mandal/' + vidhansabhaId, function(data) {
                            $('#mandal_id').html('<option value="">-- सभी --</option>');
                            data.forEach(option => $('#mandal_id').append(option));
                            if (mandalId) $('#mandal_id').val(mandalId).trigger('change');
                        });
                    }
                    if (mandalId) {
                        $.get('/admin/get-nagar/' + mandalId, function(data) {
                            $('#gram_id').html('<option value="">-- सभी --</option>');
                            data.forEach(option => $('#gram_id').append(option));
                            if (gramId) $('#gram_id').val(gramId).trigger('change');
                        });
                    }
                    if (gramId) {
                        $.get('/admin/get-polling/' + gramId, function(data) {
                            $('#polling_id').html('<option value="">-- सभी --</option>');
                            data.forEach(p => $('#polling_id').append('<option value="' + p.gram_polling_id +
                                '">' + p.polling_name + ' (' + p.polling_no + ')</option>'));
                            if (pollingId) $('#polling_id').val(pollingId).trigger('change');
                        });
                    }
                    if (pollingId) {
                        $.get('/admin/get-area/' + pollingId, function(data) {
                            $('#area_id').html('<option value="">-- सभी --</option>');
                            data.forEach(area => $('#area_id').append('<option value="' + area.area_id + '">' +
                                area.area_name + '</option>'));
                            if (areaId) $('#area_id').val(areaId);
                        });
                    }
                }

                populateDropdownsAfterReload();
                // AJAX-dependent dropdowns
                $('#division_id').on('change', function() {
                    const divisionId = $(this).val();
                    $('#district_id, #vidhansabha_id, #gram_id, #polling_id, #area_id').html('');
                    $('#district_id').append('<option value="">--चुने--</option>');
                    $('#vidhansabha_id').append('<option value="">--चुने--</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!divisionId) return;
                    $.get('/admin/get-districts/' + divisionId, function(data) {
                        data.forEach(option => $('#district_id').append(option));
                    });
                });

                $('#district_id').on('change', function() {
                    const districtId = $(this).val();
                    $('#vidhansabha_id, #gram_id, #polling_id, #area_id').html('');
                    $('#vidhansabha_id').append('<option value="">--चुने--</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!districtId) return;
                    $.get('/admin/get-vidhansabha/' + districtId, function(data) {
                        data.forEach(option => $('#vidhansabha_id').append(option));
                    });
                });

                $('#vidhansabha_id').on('change', function() {
                    const vidhansabhaId = $(this).val();
                    $('#mandal_id, #gram_id, #polling_id, #area_id').html('');
                    $('#mandal_id').append('<option value="">-- सभी --</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!vidhansabhaId) return;
                    $.get('/admin/get-mandal/' + vidhansabhaId, function(data) {
                        data.forEach(option => $('#mandal_id').append(option));
                    });
                });

                $('#mandal_id').on('change', function() {
                    const mandalId = $(this).val();
                    $('#gram_id, #polling_id, #area_id').html('');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!mandalId) return;
                    $.get('/admin/get-nagar/' + mandalId, function(data) {
                        data.forEach(option => $('#gram_id').append(option));
                    });
                });

                $('#gram_id').on('change', function() {
                    const gramId = $(this).val();
                    $('#polling_id, #area_id').html('');
                    $('#polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!gramId) return;
                    $.get('/admin/get-polling/' + gramId, function(data) {
                        data.forEach(p => $('#polling_id').append('<option value="' + p
                            .gram_polling_id + '">' + p.polling_name + ' (' + p.polling_no +
                            ')</option>'));
                    });
                });

                $('#polling_id').on('change', function() {
                    const pollingId = $(this).val();
                    $('#area_id').html('<option value="">-- सभी --</option>');
                    if (!pollingId) return;
                    $.get('/admin/get-area/' + pollingId, function(data) {
                        data.forEach(area => $('#area_id').append('<option value="' + area.area_id +
                            '">' + area.area_name + '</option>'));
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
