@php
$pageTitle = 'समस्याएँ देखे';
$breadcrumbs = [
'मेंबर' => '#',
'समस्याएँ देखे' => '#'
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

                    @if(session('success'))
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
                                    <th>शिकायत का प्रकार</th>
                                    <th>शिकायतकर्ता का नाम</th>
                                    <th>शिकायतकर्ता का मोबाइल</th>
                                    <th>शिकायत का विषय</th>
                                    <th>आगे देखें</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($complaints as $index => $complaint)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $complaint->complaint_type }}</td>
                                    <td>{{ $complaint->name }}</td>
                                    <td>{{ $complaint->email }}</td>
                                    <td>{{ $complaint->issue_title }}</td>
                                    <td>
                                        <a href="{{ route('complaints.show', $complaint->complaint_id) }}" class="btn btn-sm btn-primary" style="white-space: nowrap;">
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