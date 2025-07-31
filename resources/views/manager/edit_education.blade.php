@php
$pageTitle = 'शिक्षा अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'शिक्षा अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Education')

@section('content')
<div class="container">

    <!-- <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h3>संभाग अपडेट करें</h3>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">मैनेजर</a></li>
                <li class="breadcrumb-item active"><a href="#">संभाग अपडेट करें</a></li>
            </ol>
        </div>
    </div> -->



    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>शिक्षा</h3>
            <form method="POST" action="{{ route('education.update', $education->id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>शिक्षा<span class="required">*</span></label>
                        <input type="text" name="education" class="form-control" value="{{ $education->education_name }}" required>
                        @error('education') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">अपडेट</button>
                        <a href="{{ route('education.index') }}" class="btn btn-secondary">वापस</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection