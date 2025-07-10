@php
$pageTitle = 'मतदान क्षेत्र जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'मतदान क्षेत्र जोड़े' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Add Area')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('area.store') }}">
                @csrf
                <div class="item form-group row">

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा का नाम <span class="text-danger">*</span></label>
                        <select id="vidhansabha" class="form-control">
                            <option value="">--Select--</option>
                            @foreach($vidhansabhas as $v)
                            <option value="{{ $v->vidhansabha_id }}">{{ $v->vidhansabha }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का नाम <span class="text-danger">*</span></label>
                        <select id="mandal" class="form-control"></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>नगर केंद्र/ग्राम केंद्र का नाम <span class="text-danger">*</span></label>
                        <select id="nagar" class="form-control"></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मतदान केंद्र का नाम/क्रमांक <span class="text-danger">*</span></label>
                        <select name="polling_id" id="polling" class="form-control" required></select>
                    </div>
                </div>

                <div id="rowGroup">
                    <div class="form-row align-items-end mb-2">
                        <div class="col-md-4">
                            <label>मतदान क्षेत्र <span class="text-danger">*</span></label>
                            <input type="text" name="area_name[]" class="form-control mr-2" required>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary add-row" id="addRow">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="item form-group row">
                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('area.index') }}" class="btn btn-primary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('update_msg'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('update_msg') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table id="example" style="min-width: 845px" class="display table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Polling Centre Name/Number</th>
                                    <th>Polling Area</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($areas as $i => $area)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $area->polling->polling_name ?? '' }} - {{ $area->polling->polling_no ?? '' }}</td>
                                    <td>{{ $area->area_name }}</td>
                                    <td>
                                        <a href="{{ route('area.edit', $area->area_id) }}" data-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit fa-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
    const token = '{{ csrf_token() }}';

    $('#vidhansabha').change(function() {
        loadOptions('mandal', $(this).val(), '#mandal');
    });
    $('#mandal').change(function() {
        loadOptions('nagar', $(this).val(), '#nagar');
    });
    $('#nagar').change(function() {
        loadOptions('polling', $(this).val(), '#polling');
    });

    function loadOptions(type, id, target) {
        $.post('{{ route("area.ajax") }}', {
            type,
            id,
            _token: token
        }, res => {
            $(target).html(res.html);
        });
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