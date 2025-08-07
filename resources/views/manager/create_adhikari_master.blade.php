@php
    $pageTitle = 'अधिकारी जोड़ें';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'अधिकारी जोड़ें' => '#',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Add Adhikari')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('adhikari.store') }}">
                    @csrf
                    <div id="entries">
                        <div class="item form-group row mb-2">
                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <label>विभाग <span class="text-danger">*</span></label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    <option value="">--Select Department--</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <label>पद<span class="text-danger">*</span></label>
                                <select id="designation_id" name="designation_id" class="form-control" required></select>
                            </div>

                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <label>व्यक्ति का नाम <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <label>मोबाइल <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control" required>
                            </div>

                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <label>ईमेल आईडी <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-2 mt-2">
                                <br />
                                <button type="submit" class="btn btn-success">सबमिट</button>
                                <a href="{{ route('adhikari.index') }}" class="btn btn-primary ml-2">रद्द</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('update_msg'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('update_msg') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="display table-bordered" style="min-width: 845px" id="example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>नाम</th>
                                        <th>विभाग</th>
                                        <th>पद</th>
                                        <th>मोबाइल </th>
                                        <th>ईमेल </th>
                                        <th>विकल्प</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $idx => $r)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $r->name }}</td>
                                            <td>{{ $r->department->department_name ?? '-' }}</td>
                                            <td>{{ $r->designation->designation_name ?? '-' }}</td>
                                            <td>{{ $r->mobile }}</td>
                                            <td>{{ $r->email }}</td>
                                            <td><a href="{{ route('adhikari.edit', $r->adhikari_id) }}"
                                                    data-toggle="tooltip" title="Edit"><i
                                                        class="fa fa-edit fa-lg"></i></a></td>
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

            $('#department_id').change(function() {
                let id = this.value;
                $.post("{{ route('ajax.designation') }}", {
                    id
                }, function(res) {
                    $('#designation_id').html(res.options);
                }).fail(function(xhr) {
                    console.error("Error loading Designation:", xhr.responseText);
                });
            });
        </script>
    @endpush

@endsection
