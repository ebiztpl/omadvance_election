@php
$pageTitle = 'विधानसभा/लोकसभा जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'विधानसभा/लोकसभा जोड़े' => '#'
];
@endphp

@extends('layouts.app')

@section('title', 'Add Vidhansabha/Loksabha')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">

            <form method="POST" action="{{ route('vidhansabha.store') }}">
                @csrf
                <div id="entries">
                    <div class="item form-group row mb-2">
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <label>जिला नाम <span class="text-danger">*</span></label>
                            <select name="district_id[]" class="form-control" required>
                                <option value="">--Select District--</option>
                                @foreach($districts as $d)
                                <option value="{{ $d->district_id }}">{{ $d->district_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <label>विधानसभा नाम <span class="text-danger">*</span></label>
                            <input name="vidhansabha[]" class="form-control" required />
                        </div>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <label>लोकसभा नाम <span class="text-danger">*</span></label>
                            <input name="loksabha[]" class="form-control" required />
                        </div>

                        <div class="col-md-3 mt-2">
                            <br />
                            <button type="button" class="btn btn-primary add-row"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>

                <div class="item form-group row">
                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('vidhansabha.index') }}" class="btn btn-primary ml-2">Cancel</a>
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
                        <!-- <h3>View Vidhansabha/Loksabha</h3> -->
                        <table class="display table-bordered" style="min-width: 845px" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>District</th>
                                    <th>Vidhansabha</th>
                                    <th>Loksabha</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $idx => $r)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $r->district->district_name ?? '' }}</td>
                                    <td>{{ $r->vidhansabha }}</td>
                                    <td>{{ $r->loksabha }}</td>
                                    <td><a href="{{ route('vidhansabha.edit', $r->vidhansabha_id) }}" data-toggle="tooltip" title="Edit"><i class="fa fa-edit fa-lg"></i></a></td>
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
    $(document).ready(function() {
        $('.datatable').DataTable();

        let entryRow = $('.row.mb-2').first().clone();
        entryRow.find('input').val('');
        entryRow.find('.add-row').removeClass('btn-primary add-row').addClass('btn-danger remove-row').html('<i class="fa fa-minus"></i>');

        $(document).on('click', '.add-row', function() {
            $('#entries').append(entryRow.clone());
        });
        $(document).on('click', '.remove-row', function() {
            $(this).closest('.row.mb-2').remove();
        });
    });
</script>
@endpush

@endsection