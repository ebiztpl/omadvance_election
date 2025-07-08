@php
$pageTitle = 'जिला अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'जिला अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit City')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>जिला</h3>
            <form method="POST" action="{{ route('city.update', $district->district_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>संभाग <span class="required">*</span></label>
                        <select name="division_id" class="form-control" required>
                            @foreach($divisions as $division)
                            <option value="{{ $division->division_id }}" {{ $district->division_id == $division->division_id ? 'selected' : '' }}>
                                {{ $division->division_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('division_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिला<span class="required">*</span></label>
                        <input type="text" name="district_name" class="form-control" value="{{ $district->district_name }}" required>
                        @error('district_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('city.master') }}" class="btn btn-primary ml-2">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection