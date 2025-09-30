@php
    $pageTitle = 'विभाग रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'विभाग रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Department Report')

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

                        <div class="col-md-1 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px">फ़िल्टर</button>
                        </div>

                        <div class="col-md-3 printExcelbuttons">
                            <button type="button" class="btn btn btn-success" onclick="printReport()"
                                style="font-size: 12px;">प्रिंट
                                रिपोर्ट</button>
                  
                            <button type="button" class="btn btn-info" style="font-size: 12px;"
                                onclick="exportExcel()">Excel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        @if (isset($hasFilter) && $hasFilter)
            <div class="row">
                <div class="col-12">
                    <div class="card">
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
                                    विभाग रिपोर्ट:
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
                                <div class="text-center text-white py-1 rounded mb-2 complaint-type-title"
                                    style="font-size: 1.2rem; font-weight: 600; background-color: #4a54e9">
                                    कुल शिकायतें: ({{ $totalsAll['total_registered'] }}),
                                    कुल निरस्त: ({{ $totalsAll['total_cancel'] }}),
                                    कुल समाधान: ({{ $totalsAll['total_solved'] }})
                                </div>


                                <table class="table table-bordered table-sm" style="color: black">
                                    <thead style="background-color: blanchedalmond">
                                        <tr>
                                            <th>क्र.</th>
                                            <th>विभाग</th>
                                            <th>कुल शिकायतें</th>
                                            <th>कुल निरस्त</th>
                                            <th>कुल समाधान</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($withComplaints as $row)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $row->department_name }}</td>
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
                                    <div class="mt-4 mb-2 complaint-type-title text-center text-white py-1 rounded"
                                        style="font-size: 1.2rem; font-weight: 600; background-color:#4a54e9">
                                        अप्राप्त शिकायत: कुल विभाग: ({{ $totalsAll['total_department'] }}),
                                        पंजीकृत विभाग: ({{ $totalsRegistered['total_department'] }})
                                    </div>
                                    <table class="table table-bordered table-sm text-center" style="color: black">
                                        <tbody>
                                            @foreach ($noComplaints->chunk(8) as $row)
                                                <tr>
                                                    @foreach ($row as $dept)
                                                        <td>{{ $dept->department_name }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                            <title>Department Report</title>
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
