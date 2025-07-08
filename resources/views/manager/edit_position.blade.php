@php
$pageTitle = 'दायित्व अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'दायित्व अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Position')
@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>दायित्व</h3>
            <form action="{{ route('positions.update', $position->position_id) }}" method="POST">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>दायित्व नाम<span class="required">*</span></label>
                        <input type="text" name="position_name" class="form-control" value="{{ old('position_name', $position->position_name) }}" required>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>Level Name<span class="required">*</span></label>
                        <select name="level" class="form-control" required>
                            <option value="">-- Select Level --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $position->level == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('positions.index') }}" class="btn btn-primary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection