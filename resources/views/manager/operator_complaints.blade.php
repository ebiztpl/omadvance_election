@php
    $pageTitle = 'ऑपरेटर समस्याएँ';
    $breadcrumbs = [
        'मेंबर' => '#',
        'ऑपरेटर समस्याएँ' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View User Complaints')

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
                                        <th>शिकायतकर्ता का नाम</th>
                                        <th>शिकायतकर्ता का मोबाइल</th>
                                        <th>मतदान केंद्र</th>
                                        <th>ग्राम चौपाल</th>
                                        <th>अपलोड वीडियो</th>
                                        <th>आगे देखें</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($complaints as $index => $complaint)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $complaint->name }}</td>
                                            <td>{{ $complaint->mobile_number }}</td>
                                            <td>{{ $complaint->polling->polling_name }}</td>
                                            <td>{{ $complaint->area->area_name }}</td>
                                            <td>
                                                @if (!empty($complaint->issue_attachment))
                                                    <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                        target="_blank" class="btn btn-sm btn-success">
                                                        {{ $complaint->issue_attachment }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>No Attachment</button>
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
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
