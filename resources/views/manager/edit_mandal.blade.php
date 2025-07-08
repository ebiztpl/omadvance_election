@php
$pageTitle = 'मंडल अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'मंडल अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Mandal')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>मंडल</h3>
            <form method="POST" action="{{ route('mandal.update', $mandal->mandal_id) }}">
                @csrf

                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिला<span class="required">*</span></label>
                        <select name="district_id" id="district_id" class="form-control" required>
                            <option value="">--Select--</option>
                            @foreach($districts as $district)
                            <option value="{{ $district->district_id }}"
                                {{ optional($mandal->vidhansabha)->district_id == $district->district_id ? 'selected' : '' }}>
                                {{ $district->district_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा <span class="required">*</span></label>
                        <select name="txtvidhansabha" id="txtvidhansabha" class="form-control" required>
                            @foreach($vidhansabhas as $v)
                            <option value="{{ $v->vidhansabha_id }}"
                                {{ $mandal->vidhansabha_id == $v->vidhansabha_id ? 'selected' : '' }}>
                                {{ $v->vidhansabha }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल नाम <span class="required">*</span></label>
                        <input type="text" name="txtmadal" value="{{ $mandal->mandal_name }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('mandal.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>


        @push('scripts')
        <script>
            $(document).ready(function() {
                $('#district_id').change(function() {
                    var id = $(this).val();
                    $.ajax({
                        url: '{{ route("mandal.getVidhansabha") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(data) {
                            $('#txtvidhansabha').html(data.options);
                        },
                        error: function() {
                            alert('Error fetching vidhansabha list.');
                        }
                    });
                });
            });
        </script>
        @endpush
    </div>

    @endsection