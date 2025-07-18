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
                                        <th>क्रमांक</th>
                                        <th>नाम</th>
                                        <th>मोबाइल</th>
                                        <th>विधानसभा</th>
                                        <th>मंडल</th>
                                        <th>नगर केंद्र</th>
                                        <th>मतदान केंद्र</th>
                                        <th>ग्राम चौपाल</th>
                                        <th>अपलोड फ़ाइल</th>
                                        <th>स्थिति</th>
                                        <th>आगे देखें</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
