@php
$pageTitle = 'कमाण्ड ऐरिया अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'कमाण्ड ऐरिया अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Gram Centre')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>कमाण्ड ऐरिया</h3>
            <form method="POST" action="{{ route('nagar.update', $nagar->nagar_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का नाम <span class="required">*</span></label>
                        <select name="txtmandal" id="mandal_id" class="form-control" required>
                            <option>--Select--</option>
                            @foreach($mandals as $m)
                            <option value="{{ $m->mandal_id }}"
                                {{ $m->mandal_id == $nagar->mandal_id ? 'selected' : '' }}>
                                {{ $m->mandal_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का प्रकार <span class="required">*</span></label>
                        <select name="mandal_type" class="form-control" required>
                            <option value="">--Select--</option>
                            <option value="1" {{ $nagar->mandal_type == 1 ? 'selected' : '' }}>ग्रामीण मंडल</option>
                            <option value="2" {{ $nagar->mandal_type == 2 ? 'selected' : '' }}>नगर मंडल</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>कमाण्ड ऐरिया का नाम <span class="required">*</span></label>
                        <input type="text" name="gram_name" value="{{ $nagar->nagar_name }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('nagar.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    $('#district_id').change(function() {
        let id = $(this).val();
        $.post('{{ route("ajax.vidhansabha") }}', {
            id: id
        }, function(data) {
            $('#vidhansabha_id').html(data.options);
            $('#mandal_id').html('<option>--Select Mandal--</option>');
        });
    });

    $('#vidhansabha_id').change(function() {
        let id = $(this).val();
        $.post('{{ route("ajax.mandal") }}', {
            id: id
        }, function(data) {
            $('#mandal_id').html(data.options);
        });
    });
</script>
@endpush

@endsection