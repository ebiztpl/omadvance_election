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

                        <div class="col-md-3 d-flex flex-wrap align-items-center mt-2">
                            @php
                                $filterOptions = [
                                    'manager' => 'मैनेजर',
                                    'operator' => 'ऑपरेटर',
                                    'member' => 'कमांडर',
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

                        <div class="col-md-3 teamprintExcel">
                            <button type="button" class="btn btn-sm btn-success" onclick="printReport()"
                                style="font-size: 12px; margin-left: 14px">प्रिंट रिपोर्ट</button>
                            <button type="button" class="btn btn-info" style="font-size: 12px;"
                                onclick="exportExcel()">Excel</button>
                        </div>
                    </div>

                    <div class="row mt-1 mb-1">
                        <div class="col-md-2">
                            <label>मैनेजर</label>
                            <select name="manager_id" id="managerSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}"
                                        {{ request('manager_id') == $manager->admin_id ? 'selected' : '' }}>
                                        {{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>ऑपरेटर</label>
                            <select name="operator_id" id="operatorSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($offices as $operator)
                                    <option value="{{ $operator->admin_id }}"
                                        {{ request('operator_id') == $operator->admin_id ? 'selected' : '' }}>
                                        {{ $operator->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>कमांडर</label>
                            <select name="commander_id" id="commanderSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($fields as $commander)
                                    <option value="{{ $commander->member_id }}"
                                        {{ request('commander_id') == $commander->member_id ? 'selected' : '' }}>
                                        {{ $commander->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1 mt-1">
                            <br>
                            <button type="submit" class="btn btn-primary" id="filterBtn"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
            $fromDate = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d-m-Y') : null;
            $toDate = request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d-m-Y') : null;
            $dateRangeText =
                $fromDate && $toDate
                    ? "$fromDate से $toDate"
                    : ($fromDate
                        ? "$fromDate से"
                        : ($toDate
                            ? "$toDate तक"
                            : now()->format('d-m-Y') . ' (तक)'));
        @endphp

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="report-results" style="color: black">
                        @if ((isset($reportManager) && request('summary') == 'manager') || !request('summary'))
                            <div
                                class="step-header border-header bg-dark text-white p-2 rounded d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 text-white">मैनेजर रिपोर्ट: {{ $dateRangeText }}</h5>
                            </div>
                            <table class="table table-bordered table-sm" style="color: black">
                                <thead style="background-color: blanchedalmond">
                                    <tr>
                                        <th>प्रकार</th>
                                        <th>कुल</th>
                                        <th>कुल फॉरवर्ड</th>
                                        <th>कुल आगे भेजी</th>
                                        <th>कुल समाधान</th>
                                        <th>कुल निरस्त</th>
                                        <th>कुल रीव्यू पर</th>
                                        <th>कुल अपडेट</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($reportManager))
                                        @foreach ($reportManager as $prakar => $data)
                                            <tr>
                                                <td style="font-weight: bold">{{ $prakar }}</td>
                                                <td>{{ $data['total'] }}</td>
                                                <td>{{ $data['total_replies'] ?? 0 }}</td>
                                                <td>{{ $data['reply_from'] ?? 0 }}</td>
                                                <td>{{ $data['total_solved'] ?? 0 }}</td>
                                                <td>{{ $data['total_cancelled'] ?? 0 }}</td>
                                                <td>{{ $data['total_reviewed'] ?? 0 }}</td>
                                                <td>{{ $data['total_updates'] ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @endif

                        {{-- Operator Report --}}
                        @if ((isset($reportOperator) && request('summary') == 'operator') || !request('summary'))
                            <div
                                class="step-header border-header bg-dark text-white p-2 rounded d-flex justify-content-between align-items-center mb-3 mt-4">
                                <h5 class="mb-0 text-white">ऑपरेटर रिपोर्ट: {{ $dateRangeText }}</h5>
                            </div>
                            <table class="table table-bordered table-sm" style="color: black">
                                <thead style="background-color: blanchedalmond">
                                    <tr>
                                        <th>प्रकार</th>
                                        <th>कुल</th>
                                        <th>कुल फ़ॉलोअप</th>
                                        <th>पूर्ण फ़ॉलोअप</th>
                                        <th>कुल प्राप्त कॉल</th>
                                        <th>प्राप्त फ़ॉलोअप प्रतिक्रिया</th>
                                        <th>समस्या स्थिति</th>
                                        <th>समस्या स्थिति अपडेट</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($reportOperator))
                                        @foreach ($reportOperator as $prakar => $data)
                                            <tr>
                                                <td style="font-weight: bold">{{ $prakar }}</td>
                                                <td>{{ $data['total'] }}</td>
                                                <td>{{ $data['followups'] ?? '-' }}</td>
                                                <td>{{ $data['completed_followups'] ?? '-' }}</td>
                                                <td>{{ $data['overall_incoming'] ?? '-' }}</td>
                                                <td>{{ $data['incoming_reason1'] ?? '-' }}</td>
                                                <td>{{ $data['incoming_reason2'] ?? '-' }}</td>
                                                <td>{{ $data['incoming_reason3'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @endif

                        {{-- Member Report --}}
                        @if ((isset($reportMember) && request('summary') == 'member') || !request('summary'))
                            <div
                                class="step-header border-header bg-dark text-white p-2 rounded d-flex justify-content-between align-items-center mb-3 mt-4">
                                <h5 class="mb-0 text-white">कमांडर रिपोर्ट: {{ $dateRangeText }}</h5>
                            </div>
                            <table class="table table-bordered table-sm" style="color: black">
                                <thead style="background-color: blanchedalmond">
                                    <tr>
                                        <th>प्रकार</th>
                                        <th>कुल</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($reportMember))
                                        @foreach ($reportMember as $prakar => $data)
                                            <tr>
                                                <td style="font-weight: bold">{{ $prakar }}</td>
                                                <td>{{ $data['total'] }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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

            window.addEventListener('load', function() {
                if (window.location.search) {
                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            });

            function enableDropdowns() {
                const val = document.querySelector('.summaryRadio:checked')?.value;
                document.getElementById('managerSelect').disabled = val !== 'manager';
                document.getElementById('operatorSelect').disabled = val !== 'operator';
                document.getElementById('commanderSelect').disabled = val !== 'member';
            }

            document.querySelectorAll('.summaryRadio').forEach(radio => {
                radio.addEventListener('change', enableDropdowns);
            });

            enableDropdowns();

            function printReport() {
                const content = document.getElementById('report-results').innerHTML;
                const printWindow = window.open('', '', 'height=800,width=1200');
                printWindow.document.write(`
                        <html>
                        <head>
                            <title>Reference Report</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .step-header {
                                    background-color: #343a40 !important;
                                    color: white !important;
                                    padding-left: 4px !important; 
                                    border-radius: 6px;          
                                    margin-bottom: 10px;        
                                    margin-top: 10px;        
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
