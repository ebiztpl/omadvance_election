@php
    $pageTitle = 'अधिकारी अपडेट करें';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'अधिकारी अपडेट करें' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Edit Adhikari')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <h3>अधिकारी</h3>
                <form method="POST" action="{{ route('adhikari.update', $adhikari->adhikari_id) }}">
                    @csrf
                    <div class="item form-group row">
                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <label>विभाग <span class="required text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-control" required>
                                <option value="">--Select Department--</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->department_id }}"
                                        {{ $adhikari->department_id == $d->department_id ? 'selected' : '' }}>
                                        {{ $d->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <label>पद <span class="text-danger">*</span></label>
                            <select name="designation_id" id="designation_id" class="form-control" required>
                                <option value="">--Select Designation--</option>
                                @foreach ($designations as $des)
                                    <option value="{{ $des->designation_id }}"
                                        {{ $adhikari->designation_id == $des->designation_id ? 'selected' : '' }}>
                                        {{ $des->designation_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <label>व्यक्ति का नाम <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $adhikari->name }}"
                                required>
                        </div>

                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <label>मोबाइल <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" value="{{ $adhikari->mobile }}"
                                required>
                        </div>

                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <label>ईमेल आईडी <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ $adhikari->email }}"
                                required>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br />
                            <button type="submit" class="btn btn-success">अपडेट</button>
                            <a href="{{ route('adhikari.index') }}" class="btn btn-secondary">वापस</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            $('#department_id').change(function() {
                let id = this.value;
                $.post("{{ route('ajax.designation') }}", {
                    id
                }, function(res) {
                    $('#designation_id').html(res.options);
                });
            });
        </script>
    @endpush

@endsection
