@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'फ़ील्ड' => '#',
        'समस्याएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Complaints')

@section('content')
    <div class="container">
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
                                            <td>{{ $complaint->registrationDetails->name ?? '' }}</td>
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
                                                <a href="{{ route('complaint.show', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>


                                {{-- <tbody>
                                    @foreach ($complaints as $index => $complaint)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $complaint->registrationDetails->name ?? 'N/A' }}</td>
                                            <td>{{ $complaint->registrationDetails->mobile1 ?? 'N/A' }}</td>
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
                                                    <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं है</button>
                                                @endif
                                            </td>
                                            <td>{!! $complaint->statusTextPlain() !!}</td>
                                            <td>
                                                <a href="{{ route('complaint.show', $complaint->complaint_id) }}"
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
@endsection
