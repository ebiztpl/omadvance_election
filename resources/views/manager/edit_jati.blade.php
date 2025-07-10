@php
$pageTitle = 'जाति अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'जाति अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Jati')
@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>जाति</h3>
            <form method="POST" action="{{ route('jati.update', $jati->jati_id) }}">
                @csrf
                <div class="item form-group row">

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जाति<span class="required">*</span></label>
                        <input type="text" name="jati_name" value="{{ $jati->jati_name }}" class="form-control" required>
                        @error('jati_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>


                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('jati.index') }}" class="btn btn-primary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection