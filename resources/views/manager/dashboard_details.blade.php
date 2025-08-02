@php
    $pageTitle = 'डैशबोर्ड विवरण';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'डैशबोर्ड विवरण' => '#',
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <h4>{{ $title }}</h4>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <span
                                style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                शिकायतें - <span id="complaint-count">{{ $complaints->count() }}</span></span>
                            <table id="example" style="min-width: 845px" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th style="min-width: 150px;">शिकायतकर्ता</th>
                                        <th style="min-width: 100px;">क्षेत्र</th>
                                        <th>विभाग</th>
                                        <th>शिकायत की तिथि</th>
                                        <th>से बकाया</th>
                                        <th>स्थिति</th>
                                        <th>आवेदक</th>
                                        <th>फॉरवर्ड अधिकारी</th>
                                        <th>आगे देखें</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($complaints as $index => $complaint)
                                        
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td> <strong>शिकायत क्र.: </strong>{{ $complaint->complaint_number ?? 'N/A' }} <br>
                                                <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }} <br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }} <br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br>
                                                <strong>रेफरेंस: </strong>{{ $complaint->reference_name ?? '' }}
                                            </td>

                                            <td
                                                title="
                                                
विभाग:  {{ $complaint->division->division_name ?? 'N/A' }}
जिला:  {{ $complaint->district->district_name ?? 'N/A' }}
विधानसभा:  {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}
मंडल:  {{ $complaint->mandal->mandal_name ?? 'N/A' }}
नगर/ग्राम:  {{ $complaint->gram->nagar_name ?? 'N/A' }}
मतदान केंद्र:  {{ $complaint->polling->polling_name ?? 'N/A' }} ({{ $complaint->polling->polling_no ?? 'N/A' }})
क्षेत्र:  {{ $complaint->area->area_name ?? 'N/A' }}
">
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

                                            <td>{{ $complaint->complaint_department ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                            </td>

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
                                            <td>
                                                @if ($complaint->type == 2)
                                                    {{ $complaint->admin->admin_name ?? '-' }}
                                                @else
                                                    {{ $complaint->registrationDetails->name ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                               {{ $complaint->forwarded_to_name ?? '-' }} <br>
                                               {{ $complaint->forwarded_to_date }}
                                                {{-- @if (!empty($complaint->issue_attachment))
                                                    <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                        target="_blank" class="btn btn-sm btn-success">
                                                        देखें
                                                    </a>
                                                 
                                                @endif --}}
                                            </td>
                                            <td>
                                                <a href="{{ route('complaints_show.details', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
