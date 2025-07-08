@php
$pageTitle = 'जिला जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'जिला जोड़े' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Add City')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">

            <form method="POST" action="{{ route('city.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>संभाग नाम<span class="text-danger">*</span></label>
                        <select name="division_id" class="form-control" required>
                            <option value="">-- Select Division --</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिला नाम <span class="text-danger">*</span></label>
                        <input type="text" name="district_name" class="form-control" required>
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('city.master') }}" class="btn btn-primary ml-2">Cancel</a>
                    </div>
                </div>
            </form>

        </div>
    </div>


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

                    <div class="table-responsive">
                        <table class="display table-bordered" style="min-width: 845px" id="example">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Division Name</th>
                                    <th>District Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($districts as $index => $dist)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dist->division_name }}</td>
                                    <td>{{ $dist->district_name }}</td>
                                    <td>
                                        <a href="{{ route('city.edit', $dist->district_id) }}" data-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit fa-lg"></i>
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