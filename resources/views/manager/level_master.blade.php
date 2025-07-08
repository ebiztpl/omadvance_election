@php
$pageTitle = 'कार्य क्षेत्र';
$breadcrumbs = [
'मैनेजर' => '#',
'कार्य क्षेत्र' => '#'
];
@endphp

@extends('layouts.app')

@section('title', 'Add Level')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form action="{{ route('level.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>कार्य क्षेत्र का नाम <span class="text-danger">*</span></label>
                        <input name="level_name" class="form-control" required>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>Level Name<span class="text-danger">*</span></label>
                        <select name="ref_level_id" class="form-control" required>
                            <option value="">-- Select Level--</option>
                            @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}">{{ $lv->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('level.index') }}" class="btn btn-primary ml-2">Cancel</a>
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
                                    <th>#</th>
                                    <th>Level Name</th>
                                    <th>Under</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levels as $i => $lv)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $lv->level_name }}</td>
                                    <td>{{ $lv->parent->level_name ?? '-' }}</td>
                                    <td><a href="{{ route('level.edit', $lv->level_id) }}" data-toggle="tooltip" title="Edit"> <i class="fa fa-edit fa-lg"></i></a></td>
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