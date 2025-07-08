@php
$pageTitle = 'मंडल जोड़े';
$breadcrumbs = [
'मैनेजर' => '#',
'मंडल जोड़े' => '#'
];
@endphp

@extends('layouts.app')

@section('title', 'Add Mandal')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="{{ route('mandal.store') }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>जिला <span class="required">*</span></label>
                        <select name="district_id" id="district_id" class="form-control" required>
                            <option value="">--Select District--</option>
                            @foreach($districts as $district)
                            <option value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विधानसभा <span class="required">*</span></label>
                        <select name="txtvidhansabha" id="txtvidhansabha" class="form-control" required>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>मंडल नाम <span class="required">*</span></label>
                        <input type="text" name="txtmadal" class="form-control" required>
                    </div>

                    <div class="col-md-3 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('mandal.index') }}" class="btn btn-primary">Cancel</a>
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
                                    <th>#</th>
                                    <th>Vidhansabha</th>
                                    <th>Mandal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mandals as $index => $mandal)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $mandal->vidhansabha->vidhansabha ?? 'N/A' }}</td>
                                    <td>{{ $mandal->mandal_name }}</td>
                                    <td>
                                        <a href="{{ route('mandal.edit', $mandal->mandal_id) }}" data-toggle="tooltip" title="Edit">
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
    $(document).ready(function() {
        $('#district_id').change(function() {
            var id = $(this).val();
            if (id) {
                $.ajax({
                    url: '{{ route("mandal.getVidhansabha") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(data) {
                        $('#txtvidhansabha').html(data.options);
                    }
                });
            } else {
                $('#txtvidhansabha').html('<option value="">--Select Vidhansabha--</option>');
            }
        });
    });
</script>
@endpush
@endsection