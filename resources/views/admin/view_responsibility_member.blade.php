@php
$pageTitle = 'दायित्व कार्यकर्ता देखे';
$breadcrumbs = [
'एडमिन' => '#',
'दायित्व कार्यकर्ता देखे' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'View Responsibility Member')

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

                    @if(session('update_msg'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('update_msg') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('delete_msg'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('delete_msg') }}
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
                                    <th>पता</th>
                                    <th>फ़ोटो</th>
                                    <th>कार्य क्षेत्र</th>
                                    <th>दायित्व</th>
                                    <th>दायित्व क्षेत्र</th>
                                    <th>Action</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $index => $assign)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $assign->member->name ?? '' }}</td>
                                    <td>{{ $assign->addressInfo->permanent_address }}</td>
                                    <td> <img src="{{ asset('assets/upload/' . $assign->member->photo) }}" width="100" height="100" alt="Member Photo"></td>
                                    <td>{{ $assign->level_name ?? '' }}</td>
                                    <td>{{ $assign->position->position_name ?? '' }}</td>
                                    <td>{{$assign->district->district_name ?? '' }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('register.show', $assign->member->registration_id) }}" class="btn btn-sm btn-success mr-2">View</a>
                                            <a href="{{ route('register.card', $assign->member->registration_id) }}" class="btn btn-sm btn-warning mr-2" target="_blank">IDCard</a>

                                            <form action="{{ route('assign.destroy', $assign->assign_position_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8">No responsibilities assigned.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection