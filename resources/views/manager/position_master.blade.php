@php
$pageTitle = 'दायित्व';
$breadcrumbs = [
'मैनेजर' => '#',
'दायित्व' => '#'
];
@endphp

@extends('layouts.app')

@section('title', 'Add Position')

@section('content')

<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form action="{{ route('positions.store') }}" method="POST">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>दायित्व नाम <span class="text-danger">*</span></label>
                        <input type="text" name="position_name" class="form-control mr-2" required>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>Level Name<span class="text-danger">*</span></label>
                        <select name="level" class="form-control mr-2" required>
                            <option value="">-- Level --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('positions.index') }}" class="btn btn-primary ml-2">Cancel</a>
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
                        <table id="example" style="min-width: 845px" class="display table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Position Name</th>
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($positions as $index => $pos)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $pos->position_name }}</td>
                                    <td>{{ $pos->level }}</td>
                                    <td>
                                        <a href="{{ route('positions.edit', $pos->position_id) }}" data-toggle="tooltip" title="Edit">
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