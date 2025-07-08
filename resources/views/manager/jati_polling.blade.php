@php
$pageTitle = 'जातिगत मतदाता प्रविष्टि';
$breadcrumbs = [
'मैनेजर' => '#',
'जातिगत मतदाता प्रविष्टि' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Voter Entry')

@section('content')
<div class="container">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="row page-titles mx-0">

        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('jati_polling.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा का नाम <span class="text-danger">*</span></label>
                        <select name="txtvidhansabha" id="txtvidhansabha" class="form-control" required></select>
                    </div>

                    <div class="col-md-2 col-sm-3 col-xs-12">
                        <label>मंडल का नाम <span class="text-danger">*</span></label>
                        <select name="txtmandal" id="txtmandal" class="form-control" required></select>
                    </div>


                    <div class="col-md-2 col-sm-3 col-xs-12">
                        <label>मंडल का प्रकार <span class="text-danger">*</span></label>
                        <select name="mandal_type" id="mandal_type" class="form-control" required>
                            <option value="">--Select Type--</option>
                            <option value="1">ग्रामीण मंडल</option>
                            <option value="2">नगर मंडल</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>नगर केंद्र/ग्राम केंद्र <span class="text-danger">*</span></label>
                        <select name="txtgram" id="txtgram" class="form-control" required></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मतदान केंद्र का नाम/क्रमांक <span class="text-danger">*</span></label>
                        <select name="txtpolling" id="txtpolling" class="form-control" required></select>
                    </div>
                </div>

                <div id="rowGroup">
                    <div class="form-row align-items-end mb-2">
                        <div class="col-md-3">
                            <label>जाति <span class="text-danger">*</span></label>
                            <select name="jati_id[]" class="form-control" required>
                                <option value="">--Select Jati--</option>
                                @foreach($jatis as $jati)
                                <option value="{{ $jati->jati_id }}">{{ $jati->jati_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <label class="control-label">कुल मतदाता<span class="required">*</span></label>
                            <input type="number" name="total_voter[]" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary add-row mt-4">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="item form-group row">
                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('jati_polling.index') }}" class="btn btn-primary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
<script>
    $('#txtvidhansabha').ready(function() {
        $.post('{{ url("manager/ajax/vidhansabha") }}', {
            id: 11,
            _token: '{{ csrf_token() }}'
        }, function(data) {
            $('#txtvidhansabha').html(data.options);
        });
    });

    $('#txtvidhansabha').on('change', function() {
        $.post('{{ url("manager/ajax/mandal") }}', {
            id: this.value,
            _token: '{{ csrf_token() }}'
        }, function(data) {
            $('#txtmandal').html(data.options);
        });
    });

    $('#mandal_type').on('change', function() {
        const mandalid = $('#txtmandal').val();
        const mandal_type = $(this).val();
        $.post('{{ url("manager/ajax/grams") }}', {
            mandalid,
            mandal_type,
            _token: '{{ csrf_token() }}'
        }, function(data) {
            $('#txtgram').html(renderOptions(data));
        });
    });


    $('#txtgram').on('change', function() {
        $.post('{{ url("manager/ajax/pollings") }}', {
            id: this.value,
            _token: '{{ csrf_token() }}'
        }, function(data) {
            $('#txtpolling').html(renderOptions(data, true));
        });
    });

    function renderOptions(data, includeDefault = true) {
        let options = includeDefault ? "<option value=''>--Select--</option>" : '';
        if (Array.isArray(data)) {
            for (let item of data) {
                let id = item.id;
                let name = item.name;
                options += `<option value="${id}">${name}</option>`;
            }
        } else if (data.options) {
            options = data.options;
        }
        return options;
    }

    let template = $('#rowGroup .form-row.mb-2').first().clone();
    template.find('input').val('');
    template.find('.add-row').replaceWith('<button type="button" class="btn btn-danger remove-row"><i class="fa fa-minus"></i></button>');

    $('#rowGroup').on('click', '.add-row', function() {
        $('#rowGroup').append(template.clone());
    });

    $('#rowGroup').on('click', '.remove-row', function() {
        $(this).closest('.form-row').remove();
    });
</script>
@endpush

@endsection