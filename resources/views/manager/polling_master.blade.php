@php
$pageTitle = 'मतदान केंद्र/क्रमांक जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'मतदान केंद्र/क्रमांक जोड़े' => '#'
];
@endphp

@extends('layouts.app')

@section('title', 'Add Polling')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('polling.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिले का नाम <span class="text-danger">*</span></label>
                        <select name="district_id" id="district_id" class="form-control" required>
                            <option value="">-- Select District --</option>
                            @foreach($districts as $district)
                            <option value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा का नाम <span class="text-danger">*</span></label>
                        <select name="vidhansabha_id" id="vidhansabha_id" class="form-control" required></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का नाम <span class="text-danger">*</span></label>
                        <select name="mandal_id" id="mandal_id" class="form-control" required></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>कमाण्ड ऐरिया का नाम <span class="text-danger">*</span></label>
                        <select name="nagar_id" id="nagar_id" class="form-control" required></select>
                    </div>
                </div>

                <div id="polling-rows">
                    <div class="form-row align-items-end mb-2 polling-item">
                        <div class="col-md-4">
                            <label>मतदान केंद्र का नाम <span class="text-danger">*</span></label>
                            <input type="text" name="polling_name[]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>मतदान केंद्र का क्रमांक <span class="text-danger">*</span></label>
                            <input type="text" name="polling_number[]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary add-row mt-4"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                    <a href="{{ route('polling.index') }}" class="btn btn-secondary">Cancel</a>
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
                        <table id="example" class="display table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Mandal Name</th>
                                    <th>Town Centre/ Village Centre Name</th>
                                    <th>Polling Centre Name</th>
                                    <th>Polling Centre Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pollings as $i => $poll)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $poll['mandal_name'] }}</td>
                                    <td>{{ $poll['gram_name'] }}</td>
                                    <td>{{ $poll['polling_name'] }}</td>
                                    <td>{{ $poll['polling_number'] }}</td>

                                    <td>
                                        <a href="{{ route('polling.edit', $poll['gram_polling_id']) }}" data-toggle="tooltip" title="Edit">
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    $('#district_id').change(function() {
        $.post('{{ route("ajax.vidhansabha") }}', {
            id: $(this).val()
        }, function(res) {
            $('#vidhansabha_id').html(res.options);
            $('#mandal_id').html('');
            $('#nagar_id').html('');
        });
    });

    $('#vidhansabha_id').change(function() {
        $.post('{{ route("ajax.mandal") }}', {
            id: $(this).val()
        }, function(res) {
            $('#mandal_id').html(res.options);
            $('#nagar_id').html('');
        });
    });

    $('#mandal_id').change(function() {
        $.post('{{ route("ajax.nagar") }}', {
            id: $(this).val()
        }, function(res) {
            $('#nagar_id').html(res.options);
        });
    });

    let rowTemplate = $('.polling-item').first().clone();
    rowTemplate.find('input').val('');
    rowTemplate.find('.add-row').removeClass('add-row btn-primary').addClass('remove-row btn-danger').html('<i class="fa fa-minus"></i>');

    $('#polling-rows').on('click', '.add-row', function() {
        $('#polling-rows').append(rowTemplate.clone());
    });

    $('#polling-rows').on('click', '.remove-row', function() {
        $(this).closest('.polling-item').remove();
    });
</script>
@endpush

@endsection