@php
$pageTitle = 'कार्य क्षेत्र अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'कार्य क्षेत्र अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Level')
@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>कार्य क्षेत्र</h3>
            <form action="{{ route('level.update', $level->level_id) }}" method="POST">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>कार्य क्षेत्र का नाम <span class="required">*</span></label>
                        <input name="level_name" class="form-control" value="{{ old('level_name', $level->level_name) }}" required>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>Level Name<span class="required">*</span></label>
                        <select name="ref_level_id" class="form-control" required>
                            <option value="">--Select Level--</option>
                            @foreach($parents as $pl)
                            <option value="{{ $pl->level_id }}" {{ $level->ref_level_id == $pl->level_id ? 'selected' : '' }}>
                                {{ $pl->level_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('level.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
        @endsection