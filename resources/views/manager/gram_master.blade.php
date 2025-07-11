@php
$pageTitle = 'नगर केंद्र/ग्राम केंद्र जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'नगर केंद्र/ग्राम केंद्र जोड़े' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Add Gram Centre')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('nagar.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिले का नाम <span class="text-danger">*</span></label>
                        <select id="district_id" name="district_id" class="form-control" required>
                            <option>--Select District--</option>
                            @foreach($districts as $d)
                            <option value="{{ $d->district_id }}">{{ $d->district_name }}</option>
                            @endforeach
                        </select>

                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा का नाम <span class="text-danger">*</span></label>
                        <select id="vidhansabha_id" name="vidhansabha_id" class="form-control" required></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का नाम <span class="text-danger">*</span></label>
                        <select name="txtmandal" id="mandal_id" class="form-control" required></select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल का प्रकार <span class="text-danger">*</span></label>
                        <select name="mandal_type" class="form-control" required>
                            <option value="">--Select Type--</option>
                            <option value="1">ग्रामीण मंडल</option>
                            <option value="2">नगर मंडल</option>
                        </select>
                    </div>
                </div>

                <div id="rowGroup">
                    <div class="form-row align-items-end mb-2">
                        <div class="col-md-4">
                            <label>नगर केंद्र/ग्राम केंद्र का नाम <span class="text-danger">*</span></label>
                            <input type="text" name="gram_name[]" class="form-control" required>
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
                        <a href="{{ route('nagar.index') }}" class="btn btn-primary">Cancel</a>
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
                                    <th>Mandal Name</th>
                                    <th>Mandal Type</th>
                                    <th>Town Centre/ Village Centre Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nagarList as $i => $n)
                                <tr>
                                    <td>{{ $n['sr'] }}</td>
                                    <td>{{ $n['mandal_name'] }}</td>
                                    <td>{{ $n['mandal_tyname'] }}</td>
                                    <td>{{ $n['gram_name'] }}</td>
                                    <td>
                                        <a href="{{ route('nagar.edit', $n['nagar_id']) }}" data-toggle="tooltip" title="Edit">
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
        let id = this.value;
        $.post("{{ route('ajax.vidhansabha') }}", {
            id
        }, function(res) {
            $('#vidhansabha_id').html(res.options);
        }).fail(function(xhr) {
            console.error("Error loading Vidhan Sabha:", xhr.responseText);
        });
    });

    $('#vidhansabha_id').change(function() {
        let id = this.value;
        $.post("{{ route('ajax.mandal') }}", {
            id
        }, function(res) {
            $('#mandal_id').html(res.options);
        }).fail(function(xhr) {
            console.error("Error loading Mandal:", xhr.responseText);
        });
    });

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