@php
$pageTitle = 'शिक्षा जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'शिक्षा जोड़े' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Add Education')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('education.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>शिक्षा <span class="text-danger">*</span></label>
                        <input type="text" name="education" class="form-control" required>
                        @error('education')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">जोड़ें</button>
                        <a href="{{ route('education.index') }}" class="btn btn-primary">रद्द</a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @if(session('insert_msg'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('insert_msg') }}
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
                                    <th>क्र.</th>
                                    <th>शिक्षा</th>
                                    <th>विकल्प</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($educations as $index => $education)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $education->education_name }}</td>
                                    <td>
                                        <a href="{{ route('education.edit', $education->id) }}" data-toggle="tooltip" title="Edit">
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