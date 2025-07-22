@php
$pageTitle = 'विभाग अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'विभाग अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Department')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>विभाग</h3>
            <form method="POST" action="{{ route('department.update', $department->department_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विभाग<span class="required">*</span></label>
                        <input type="text" name="department_name" class="form-control" value="{{ $department->department_name }}" required>
                        @error('department_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('department.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection