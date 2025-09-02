@php
    $pageTitle = 'दैनिक रिपोर्ट';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'दैनिक रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Daily Report')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" action="{{ route('dailyreport.index') }}" class="mb-4">
                    <div class="row mt-1">
                        <div class="col-md-2">
                            <label>फॉरवर्ड</label>
                            <select name="admin_id" id="admin_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($managers as $managerOption)
                                    <option value="{{ $managerOption->admin_id }}"
                                        {{ $selectedManagerId == $managerOption->admin_id ? 'selected' : '' }}>
                                        {{ $managerOption->admin_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px">फ़िल्टर</button>
                        </div>

                        <div id="report-print">
                            @if ($manager)
                                <div class="mt-2">
                                    <br>
                                    <button onclick="printDiv('printableArea')" class="btn btn-success"
                                        style="font-size: 12px">
                                        PDF / Print डाउनलोड करें
                                    </button>
                                    <a href="{{ route('dailyreport.index') }}" class="btn btn-danger"
                                        style="font-size: 12px">रीसेट</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="report-container">
            @if ($manager)
                <div id="printableArea">
                    <div
                        class="step-header border-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
                        @php
                            $today = \Carbon\Carbon::today()->format('d-m-Y');
                        @endphp
                        <h5 class="mb-0 text-white">दैनिक रिपोर्ट: {{ $today }}</h5>
                        <span class="step-number badge bg-light text-dark fs-4"
                            style="font-size: 100%">{{ $manager->admin_name }}</span>
                    </div>

                    @php
                        $types = [
                            'समस्या' => $samasya,
                            'विकास' => $vikash,
                            'शुभ सुचना' => $subhSuchna,
                            'अशुभ सुचना' => $asubhSuchna,
                        ];
                    @endphp

                    @foreach ($types as $typeName => $complaints)
                        <div class="card mb-3">
                            <div class="text-center text-white py-1 rounded mb-3 complaint-type-title"
                                style="font-size: 1.5rem; font-weight: 600; letter-spacing: 1px; background-color: #4a54e9">
                                {{ $typeName }} ({{ $complaints->count() }})
                            </div>
                            <div class="card-body">
                                @if ($complaints->isEmpty())
                                    <p>कोई रिकॉर्ड नहीं।</p>
                                @else
                                    <table class="table table-striped table-bordered complaint-table"
                                        style="color: black; table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 20%;">शिकायतकर्ता</th>
                                                <th style="width: 10%;">रेफरेंस</th>
                                                <th style="width: 10%;">क्षेत्र</th>
                                                @if (in_array($typeName, ['समस्या', 'विकास']))
                                                    <th style="width: 20%;">शिकायत विवरण</th>
                                                    <th style="width: 10%;">विभाग</th>
                                                @else
                                                    <th style="width: 20%;">सुचना विवरण</th>
                                                    <th style="width: 10%;">कार्यक्रम दिनांक/समय</th>
                                                @endif
                                                <th style="width: 10%;">आवेदक</th>
                                                <th style="width: 20%;">रिप्लाई विवरण</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($complaints as $complaint)
                                                <tr>
                                                    <td style="word-wrap: break-word;">
                                                        <strong>शिकायत क्र.:
                                                        </strong>{{ $complaint->complaint_number ?? 'N/A' }}<br>
                                                        <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }}<br>
                                                        <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }}<br>
                                                        <strong>पुत्र श्री:
                                                        </strong>{{ $complaint->father_name ?? '' }}<br><br>
                                                        @if (in_array($complaint->complaint_type, ['समस्या', 'विकास']))
                                                            <strong>शिकायत तिथि:
                                                            </strong>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}<br>
                                                        @else
                                                            <strong>सुचना तिथि:
                                                            </strong>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}<br>
                                                        @endif
                                                        <strong>स्थिति: </strong>{!! $complaint->statusTextPlain() !!}
                                                    </td>
                                                    <td style="word-wrap: break-word;">{{ $complaint->reference_name }}
                                                    </td>
                                                    <td style="word-wrap: break-word;">
                                                        {{ $complaint->division->division_name ?? 'N/A' }}<br>
                                                        {{ $complaint->district->district_name ?? 'N/A' }}<br>
                                                        {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}<br>
                                                        {{ $complaint->mandal->mandal_name ?? 'N/A' }}<br>
                                                        {{ $complaint->gram->nagar_name ?? 'N/A' }}<br>
                                                        {{ $complaint->polling->polling_name ?? 'N/A' }}
                                                        ({{ $complaint->polling->polling_no ?? 'N/A' }})
                                                        <br>
                                                        {{ $complaint->area->area_name ?? 'N/A' }}
                                                    </td>

                                                    @if (in_array($complaint->complaint_type, ['समस्या', 'विकास']))
                                                        <td style="word-wrap: break-word;">
                                                            {{ $complaint->issue_description }}</td>
                                                        <td style="word-wrap: break-word;">
                                                            {{ $complaint->complaint_department ?? 'N/A' }}</td>
                                                    @else
                                                        <td style="word-wrap: break-word;">
                                                            {{ $complaint->issue_description }}</td>
                                                        <td style="word-wrap: break-word;">{{ $complaint->program_date }} -
                                                            {{ $complaint->news_time }}</td>
                                                    @endif

                                                    <td style="word-wrap: break-word;">
                                                        @if ($complaint->type == 2)
                                                            {{ $complaint->admin->admin_name ?? '-' }}
                                                        @else
                                                            {{ $complaint->registrationDetails->name ?? '-' }}
                                                        @endif
                                                    </td>

                                                    <td style="word-wrap: break-word;">
                                                        @foreach ($complaint->replies as $reply)
                                                            <div class="border p-2 mb-2 rounded bg-light">
                                                                <div><strong>दिनांक:</strong>
                                                                    {{ $reply->reply_date->format('d-m-Y H:i') }}</div>
                                                                <div><strong>रिप्लाई:</strong>
                                                                    {{ $reply->complaint_reply }}</div>
                                                                <div><strong>स्थिति:</strong>
                                                                    {{ $reply->statusTextPlain() }}</div>
                                                                <div><strong>द्वारा भेजा गया:</strong>
                                                                    {{ $reply->replyfrom->admin_name ?? '-' }}</div>
                                                                <div><strong>को भेजा गया:</strong>
                                                                    {{ $reply->forwardedToManager->admin_name ?? '-' }}
                                                                </div>
                                                                @if ($selectedManagerId && $reply->forwarded_to == $selectedManagerId)
                                                                    <div><strong>(Forwarded to this manager)</strong></div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('form').on('submit', function(e) {
                    e.preventDefault();
                    $("#loader-wrapper").show();

                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'GET',
                        data: $(this).serialize(),
                        success: function(response) {
                            const newContent = $(response).find('#report-container').html();
                            $('#report-container').html(newContent);

                            const print = $(response).find('#report-print').html();
                            $('#report-print').html(print);

                            // $('.complaint-table').each(function() {
                            //     $(this).DataTable({
                            //         paging: true,
                            //         searching: true,
                            //         ordering: true,
                            //         info: true,
                            //         lengthChange: true,
                            //         pageLength: 25,
                            //         dom: 'Bfrtip',
                            //         buttons: ['csv', 'excel', 'pdf', 'print']
                            //     });
                            // });
                        },
                        complete: function() {
                            $("#loader-wrapper").hide();
                        },
                        error: function() {
                            alert("कुछ गड़बड़ हो गई है। कृपया बाद में प्रयास करें।");
                        }
                    });
                });
            });


            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;

                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;

                tempDiv.querySelectorAll('.card').forEach(function(card) {
                    var table = card.querySelector('table');
                    if (!table || table.querySelectorAll('tbody tr').length === 0) {
                        card.remove(); 
                    }
                });

                var content = tempDiv.innerHTML;


                var mywindow = window.open('', 'Print', 'height=600,width=800');

                mywindow.document.write('<html><head><title>दैनिक रिपोर्ट</title>');

                mywindow.document.write(`
                        <style>
                                    @media print {
                                .dataTables_filter,
                                .dataTables_length,
                                .dataTables_info,
                                .dataTables_paginate,
                                .dt-buttons,
                                .no-print {
                                    display: none !important;
                                }

                                table {
                                    display: block;
                                    overflow-x: auto;
                                }

                                body {
                                    font-family: Arial, sans-serif;
                                    font-size: 12px;
                                    color: #000;
                                }

                                /* Header */
                                .step-header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    background-color: #343a40 !important; 
                                    color: #fff !important;              
                                    -webkit-print-color-adjust: exact;   
                                    print-color-adjust: exact;           
                                    padding: 15px;
                                    border-radius: 8px;
                                    margin-bottom: 20px;
                                    font-size: 20px;
                                }

                                .step-header .badge {
                                    background-color: #f8f9fa !important; 
                                    color: #212529 !important;         
                                    -webkit-print-color-adjust: exact;
                                    print-color-adjust: exact;
                                    font-size: 14px;
                                    padding: 5px 10px;
                                    border-radius: 8px;
                                }

                                .step-header h5 {
                                    margin: 0;
                                    font-weight: 600;
                                }

                                .step-header .badge {
                                    font-size: 14px;
                                    padding: 5px 10px;
                                }

                                /* Colored borders on header */
                            

                                /* Complaint type title */
                                .complaint-type-title {
                                    text-align: center;
                                    background-color: #4a54e9 !important;
                                    color: #fff !important;
                                    font-size: 1.5rem;
                                    font-weight: 600;
                                    letter-spacing: 1px;
                                    padding: 10px 0;
                                    border-radius: 6px;
                                    margin-bottom: 15px;
                                    margin-top: 15px;
                                    -webkit-print-color-adjust: exact;
                                    print-color-adjust: exact;
                                }

                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    table-layout: fixed;
                                    margin-bottom: 20px;
                                }

                                th, td {
                                    border: 1px solid #444;
                                    padding: 8px;
                                    text-align: left;
                                    word-wrap: break-word;
                                }

                                th {
                                    background-color: #f0f0f0 !important;
                                    font-weight: bold;
                                    -webkit-print-color-adjust: exact;
                                    print-color-adjust: exact;
                                }

                                .reply-box, .border {
                                    border: 1px solid #444 !important;
                                    padding: 6px;
                                    margin-bottom: 5px;
                                    border-radius: 5px;
                                    background: #f9f9f9 !important;
                                    font-size: 12px;
                                    -webkit-print-color-adjust: exact;
                                    print-color-adjust: exact;
                                }
                            }

                        </style>
                    `);

                mywindow.document.write('</head><body>');
                mywindow.document.write(content);
                mywindow.document.write('</body></html>');

                mywindow.document.close();
                mywindow.focus();
                mywindow.print();
                mywindow.close();
            }
        </script>
    @endpush


@endsection
