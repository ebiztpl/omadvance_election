@php
    $pageTitle = 'कार्यालय समस्याएँ';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'कार्यालय समस्याएँ' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Operator Complaints')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div id="rowGroup">
                        <div class="form-row align-items-end mb-2">
                            <div class="col-md-4">
                                <label for="complaint_status" class="mr-2 font-weight-bold">स्थिति:</label>
                                <select name="complaint_status" id="complaint_status" class="form-control mr-2">
                                    <option value="">-- सभी --</option>
                                    <option value="1" {{ request('complaint_status') == '1' ? 'selected' : '' }}>शिकायत
                                        दर्ज</option>
                                    <option value="2" {{ request('complaint_status') == '2' ? 'selected' : '' }}>
                                        प्रक्रिया में</option>
                                    <option value="3" {{ request('complaint_status') == '3' ? 'selected' : '' }}>स्थगित
                                    </option>
                                    <option value="4" {{ request('complaint_status') == '4' ? 'selected' : '' }}>पूर्ण
                                    </option>
                                    <option value="5" {{ request('complaint_status') == '5' ? 'selected' : '' }}>रद्द
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <span style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                            शिकायत - <span id="complaint-count">{{ $complaints->count() }}</span></span>

                        <div class="table-responsive">
                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                   <tr>
                                        <th>क्र.</th>
                                        <th>शिकायतकर्ता-मोबाइल</th>
                                        <th style="min-width: 100px;">क्षेत्र</th>
                                        <th>विभाग</th>
                                        <th>शिकायत की तिथि</th>
                                        <th>से बकाया</th>
                                        <th>स्थिति</th>
                                        <th>आवेदक</th>
                                        <th>फ़ाइल देखें</th>
                                        <th>आगे देखें</th>
                                    </tr>
                                </thead>
                                 <tbody>
                                    @foreach ($complaints as $index => $complaint)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td> {{ $complaint->name ?? 'N/A' }}
                                                {{ $complaint->mobile_number ?? '' }}
                                            </td>
                                            <td
                                                title="
                                                
                                                
विभाग:  {{ $complaint->division->division_name ?? 'N/A' }}
जिला:  {{ $complaint->district->district_name ?? 'N/A' }}
विधानसभा:  {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}
मंडल:  {{ $complaint->mandal->mandal_name ?? 'N/A' }}
नगर/ग्राम:  {{ $complaint->gram->nagar_name ?? 'N/A' }}
मतदान केंद्र:  {{ $complaint->polling->polling_name ?? 'N/A' }} ({{ $complaint->polling->polling_no ?? 'N/A'}})
क्षेत्र:  {{ $complaint->area->area_name ?? 'N/A' }}
">
                                                {{ $complaint->division->division_name ?? 'N/A' }}<br>
                                                {{ $complaint->district->district_name ?? 'N/A' }}<br>
                                                {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}<br>
                                                {{ $complaint->mandal->mandal_name ?? 'N/A' }}<br>
                                                {{ $complaint->gram->nagar_name ?? 'N/A' }}<br>
                                                {{ $complaint->polling->polling_name ?? 'N/A' }}
                                                ({{ $complaint->polling->polling_no ?? 'N/A'}})
                                                <br>
                                                {{ $complaint->area->area_name ?? 'N/A' }}
                                            </td>

                                            <td>{{ $complaint->complaint_department ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y') }}</td>
                                            {{-- <td>
                                                @if (!in_array($complaint->complaint_status, [4, 5]))
                                                    {{ $complaint->pending_days }} दिन
                                                @else
                                                @endif
                                            </td> --}}

                                            <td>
                                                @if ($complaint->complaint_status == 4)
                                                    पूर्ण
                                                @elseif ($complaint->complaint_status == 5)
                                                    रद्द
                                                @else
                                                    {{ $complaint->pending_days }} दिन
                                                @endif
                                            </td>

                                            <td>{!! $complaint->statusTextPlain() !!}</td>
                                            <td>{{ $complaint->admin->admin_name ?? 'N/A' }}</td>
                                            <td>
                                                @if (!empty($complaint->issue_attachment))
                                                    <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                        target="_blank" class="btn btn-sm btn-success">
                                                        {{ $complaint->issue_attachment }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं
                                                        है</button>
                                                @endif
                                            </td>

                                              <td>
                                                <a href="{{ route('operator_complaint.show', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                {{-- <tbody id="complaints-tbody">
                                    @foreach ($complaints as $index => $complaint)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $complaint->admin->admin_name ?? 'N/A' }}</td>
                                            <td>{{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}</td>
                                            <td>{{ $complaint->mandal->mandal_name ?? 'N/A' }}</td>
                                            <td>{{ $complaint->gram->nagar_name ?? 'N/A' }}</td>
                                            <td>{{ $complaint->polling->polling_name ?? 'N/A' }}</td>
                                            <td>{{ $complaint->area->area_name ?? 'N/A' }}</td>
                                            <td>
                                                @if (!empty($complaint->issue_attachment))
                                                    <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                        target="_blank" class="btn btn-sm btn-success">
                                                        {{ $complaint->issue_attachment }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं
                                                        है</button>
                                                @endif
                                            </td>
                                            <td>{!! $complaint->statusTextPlain() !!}</td>
                                            <td>
                                                <a href="{{ route('operator_complaint.show', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody> --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#complaint_status').on('change', function() {
                    let status = $(this).val();

                    $.ajax({
                        url: '{{ route('operator_complaint.view') }}',
                        method: 'GET',
                        data: {
                            complaint_status: status
                        },
                        success: function(response) {
                            $('#complaints-tbody').html(response.tbody);

                            $('#complaint-count').text(response.count);

                            // Optional: scroll to table
                            $('html, body').animate({
                                scrollTop: $("#example").offset().top
                            }, 500);
                        },
                        error: function() {
                            alert('डेटा लोड करने में त्रुटि हुई');
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection
