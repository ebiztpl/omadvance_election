@php
$pageTitle = 'विधानसभा/लोकसभा अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'विधानसभा/लोकसभा अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Vidhansabha/Loksabha')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>विधानसभा/लोकसभा</h3>
            <form method="POST" action="{{ route('vidhansabha.update', $entry->vidhansabha_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिला नाम<span class="required">*</span></label>
                        <select name="district_id" class="form-control" required>
                            @foreach($districts as $d)
                            <option value="{{ $d->district_id }}" {{ $entry->district_id == $d->district_id ? 'selected' : '' }}>
                                {{ $d->district_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा नाम<span class="required">*</span></label>
                        <input name="vidhansabha" class="form-control" value="{{ $entry->vidhansabha }}" required />
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>लोकसभा नाम<span class="required">*</span></label>
                        <input name="loksabha" class="form-control" value="{{ $entry->loksabha }}" required />
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button class="btn btn-success">Update</button>
                        <a href="{{ route('vidhansabha.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection