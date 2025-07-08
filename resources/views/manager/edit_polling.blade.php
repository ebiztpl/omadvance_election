@php
$pageTitle = 'मतदान केंद्र/मतदान क्रमांक अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'मतदान केंद्र/मतदान क्रमांक अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Polling')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>मतदान केंद्र/मतदान क्रमांक</h3>
            <form method="POST" action="{{ route('polling.update', $polling->gram_polling_id) }}">
                @csrf

                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="form-label">मतदान केंद्र का नाम <span class="text-danger">*</span></label>
                        <input type="text" name="polling_name" class="form-control" value="{{ old('polling_name', $polling->polling_name) }}" required>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="form-label">मतदान केंद्र का क्रमांक <span class="text-danger">*</span></label>
                        <input type="text" name="polling_no" class="form-control" value="{{ old('polling_no', $polling->polling_no) }}" required>
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('polling.index') }}" class="btn btn-primary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection