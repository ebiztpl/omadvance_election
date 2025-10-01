@php
    $pageTitle = 'टीम रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'टीम रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Team Report')

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
                        @php
                            function sumFraction($current, $addition)
                            {
                                if (strpos($current, '/') === false) {
                                    $current = $current . '/0';
                                }
                                if (strpos($addition, '/') === false) {
                                    $addition = $addition . '/0';
                                }

                                [$curA, $curB] = explode('/', $current);
                                [$addA, $addB] = explode('/', $addition);

                                return $curA + $addA . '/' . ($curB + $addB);
                            }
                        @endphp
                        @if (!empty($managerReport))
                            @foreach ($managerReport as $managerId => $reports)
                                @if (!empty($reports))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        मैनेजर {{ $managers->firstWhere('admin_id', $managerId)->admin_name ?? '' }}
                                        रिपोर्ट : {{ $dateRangeText }}
                                    </div>

                                    @php
                                        // Initialize totals before the loop
                                        $totals = [
                                            'total' => 0,
                                            'total_replies' => '0/0',
                                            'replies' => 0,
                                            'reply_from' => 0,
                                            'not_forward' => 0,
                                            'total_solved' => 0,
                                            'total_cancelled' => 0,
                                            'total_reviewed' => '0/0',
                                            'total_updates' => 0,
                                        ];
                                    @endphp


                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल प्राप्त</th>
                                                <th>कुल फॉरवर्ड</th>
                                                <th>कुल जवाब दर्ज</th>
                                                <th>कुल जवाब आगे भेजे</th>
                                                <th>कुल जवाब आगे नहीं भेजे</th>
                                                <th>कुल समाधान</th>
                                                <th>कुल निरस्त</th>
                                                <th>कुल रीव्यू पर</th>
                                                <th>कुल अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reports as $prakar => $data)
                                                @php
                                                    // Sum numeric values
                                                    foreach (
                                                        [
                                                            'total',
                                                            'replies',
                                                            'reply_from',
                                                            'total_solved',
                                                            'not_forward',
                                                            'total_cancelled',
                                                            'total_updates',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($data[$key]) && is_numeric($data[$key])
                                                                ? $data[$key]
                                                                : 0;
                                                    }
                                                    // Sum fraction values
                                                    foreach (['total_replies', 'total_reviewed'] as $key) {
                                                        if (isset($data[$key])) {
                                                            $totals[$key] = sumFraction($totals[$key], $data[$key]);
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $data['total'] ?? 0 }}</td>
                                                    <td>{{ $data['total_replies'] ?? 0 }}</td>
                                                    <td>{{ $data['replies'] ?? 0 }}</td>
                                                    <td>{{ $data['reply_from'] ?? 0 }}</td>
                                                    <td>{{ $data['not_forward'] ?? 0 }}</td>
                                                    <td>{{ $data['total_solved'] ?? 0 }}</td>
                                                    <td>{{ $data['total_cancelled'] ?? 0 }}</td>
                                                    <td>{{ $data['total_reviewed'] ?? 0 }}</td>
                                                    <td>{{ $data['total_updates'] ?? 0 }}</td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['total_replies'] }}</td>
                                                <td>{{ $totals['replies'] }}</td>
                                                <td>{{ $totals['reply_from'] }}</td>
                                                <td>{{ $totals['not_forward'] }}</td>
                                                <td>{{ $totals['total_solved'] }}</td>
                                                <td>{{ $totals['total_cancelled'] }}</td>
                                                <td>{{ $totals['total_reviewed'] }}</td>
                                                <td>{{ $totals['total_updates'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif

                        {{-- Operator Report --}}
                        @if (!empty($reportOperator))
                            @foreach ($reportOperator as $operatorId => $data)
                                @if (!empty($data))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $offices->firstWhere('admin_id', $operatorId)->admin_name ?? 'ऑपरेटर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php
                                        $totals = [
                                            'total' => 0,
                                            'followups' => '0/0',
                                            'completed_followups' => 0,
                                            'overall_incoming' => 0,
                                            'incoming_reason1' => '0/0',
                                            'incoming_reason2' => 0,
                                            'incoming_reason3' => 0,
                                        ];
                                    @endphp


                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                                <th>कुल फ़ॉलोअप</th>
                                                <th>पूर्ण फ़ॉलोअप</th>
                                                <th>कुल प्राप्त कॉल</th>
                                                <th>प्राप्त फ़ॉलोअप प्रतिक्रिया</th>
                                                <th>समस्या स्थिति</th>
                                                <th>समस्या स्थिति अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data as $prakar => $d)
                                                @php
                                                    // Sum numeric values
                                                    foreach (
                                                        [
                                                            'total',
                                                            'completed_followups',
                                                            'overall_incoming',
                                                            'incoming_reason2',
                                                            'incoming_reason3',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($d[$key]) && is_numeric($d[$key]) ? $d[$key] : 0;
                                                    }

                                                    // Sum fraction-like values
                                                    if (isset($d['followups'])) {
                                                        $totals['followups'] = sumFraction(
                                                            $totals['followups'],
                                                            $d['followups'],
                                                        );
                                                    }

                                                    if (isset($d['incoming_reason1'])) {
                                                        $totals['incoming_reason1'] = sumFraction(
                                                            $totals['incoming_reason1'],
                                                            $d['incoming_reason1'],
                                                        );
                                                    }
                                                @endphp

                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $d['total'] ?? 0 }}</td>
                                                    <td>{{ $d['followups'] ?? '-' }}</td>
                                                    <td>{{ $d['completed_followups'] ?? '-' }}</td>
                                                    <td>{{ $d['overall_incoming'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason1'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason2'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason3'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['followups'] }}</td>
                                                <td>{{ $totals['completed_followups'] }}</td>
                                                <td>{{ $totals['overall_incoming'] }}</td>
                                                <td>{{ $totals['incoming_reason1'] }}</td>
                                                <td>{{ $totals['incoming_reason2'] }}</td>
                                                <td>{{ $totals['incoming_reason3'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif

                        {{-- Member Report --}}
                        @if (!empty($reportMember))
                            @php $grandTotalMembers = 0; @endphp

                            @foreach ($reportMember as $memberId => $memberData)
                                @if (!empty($memberData))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $fields->firstWhere('member_id', $memberId)->name ?? 'कमांडर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php $memberTotal = 0; @endphp

                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($memberData as $prakar => $data)
                                                @php
                                                    $value =
                                                        isset($data['total']) && is_numeric($data['total'])
                                                            ? (int) $data['total']
                                                            : 0;
                                                    $memberTotal += $value;
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endforeach

                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $memberTotal }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    @php $grandTotalMembers += $memberTotal; @endphp
                                @endif
                            @endforeach
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
                                     table tbody tr:last-child { background-color: #e2e3e5 !important; font-weight: bold; }
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
