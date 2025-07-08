@php
$pageTitle = 'जाति जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'जाति जोड़े' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Add Jati')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('jati.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जाति<span class="required">*</span></label>
                        <input type="text" name="jati_name" class="form-control" required>
                        @error('jati_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('jati.index') }}" class="btn btn-secondary ">Cancel</a>
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
                                    <th>Jati Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jatis as $index => $jati)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $jati->jati_name }}</td>
                                    <td>
                                        <a href="{{ route('jati.edit', $jati->jati_id) }}" data-toggle="tooltip" title="Edit">
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