@php
    $pageTitle = 'मेंबर डैशबोर्ड';
    $breadcrumbs = [
        'मेंबर' => '#',
        'मेंबर डैशबोर्ड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Member Dashboard')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('complaint.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <label for="polling_id">मतदान केंद्र चुनें:</label>
                            <select name="polling_id" id="polling_id" class="form-control" required>
                                <option value="">चुनें</option>
                                @foreach ($pollingCenters as $polling)
                                    <option value="{{ $polling->gram_polling_id }}">
                                        {{ $polling->polling_name }} ({{ $polling->polling_no }})
                                    </option>
                                    <option>
                                        
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="area_id">ग्राम चौपाल चुनें:</label>
                            <select name="area_id" id="area_id" class="form-control" required>
                                <option value="">चुनें</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="video">वीडियो अपलोड करें:</label>
                            <input type="file" name="video" id="video" class="form-control" accept="video/*"
                                required>
                        </div>

                        <div class="col-md-2 mt-2">
                             <br />
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#polling_id').on('change', function() {
                    var pollingId = $(this).val();
                    var areaSelect = $('#area_id');

                    areaSelect.html('<option value="">Loading...</option>');

                    $.ajax({
                        url: '/get-area/' + pollingId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            areaSelect.html('<option value="">चुनें</option>');
                            $.each(response, function(index, optionHtml) {
                                areaSelect.append(optionHtml);
                            });
                        },
                        error: function() {
                            areaSelect.html('<option value="">त्रुटि</option>');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
