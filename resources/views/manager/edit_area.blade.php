@php
$pageTitle = 'मतदान क्षेत्र अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'मतदान क्षेत्र अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Area')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>मतदान केंद्र/मतदान क्रमांक</h3>
            <form method="POST" action="{{ route('area.update', $area->area_id) }}">
                @csrf

                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="form-label">मतदान क्षेत्र <span class="text-danger">*</span></label>
                        <input type="text" name="area_name" class="form-control mr-2" value="{{ $area->area_name }}" required>
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('area.index') }}" class="btn btn-primary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection