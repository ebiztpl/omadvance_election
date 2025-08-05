@php
    $pageTitle = 'समस्या पंजीयन करे';
    $breadcrumbs = [
        'फ़ील्ड' => '#',
        'समस्या पंजीयन करे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Register Complaint')

@section('content')
    <div class="container">
        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" id="complaintForm" action="{{ route('complaint.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        {{-- <div class="col-md-4">
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
                        </div> --}}

                        <div class="col-md-4">
                            <label for="area_id">ग्राम चौपाल चुनें:</label>
                            <select name="area_id" id="area_id" class="form-control" required>
                                <option value="">--चुनें--</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->area_id }}">
                                        {{ $area->area_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="video">वीडियो अपलोड करें:</label>
                            <input type="file" name="video" id="video" class="form-control" accept="video/*"
                                required>
                            <span class="text-danger small" id="video-error"></span>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br />
                            <button type="submit" class="btn btn-primary">अपलोड करें</button>
                        </div>

                        <div class="col-md-12 mt-4 d-none" id="progress-container">
                            <div class="progress" style="height: 25px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#complaintForm').on('submit', function(e) {
                    e.preventDefault();
                    // $("#loader-wrapper").show();
                    $('#video-error').text('');
                    $('#progressBar').css('width', '0%').text('0%').removeClass('bg-success');
                    $('#progress-container').removeClass('d-none');

                    var formData = new FormData(this);

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            var xhr = new XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable) {
                                    var percent = Math.round((e.loaded / e.total) * 100);
                                    $('#progressBar')
                                        .css('width', percent + '%')
                                        .text(percent + '%');

                                    if (percent === 100) {
                                        $('#progressBar').addClass('bg-success');
                                    }
                                }
                            });
                            return xhr;
                        },
                        success: function(response) {
                            // $("#loader-wrapper").hide();
                            $('#progressBar').css('width', '0%').text('0%');
                            $('#progressBar').removeClass('bg-success');
                             $('#progress-container').addClass('d-none');


                            if (response.success) {
                                $('#success-message').text(response.message);

                                $('#success-alert').removeClass('d-none');

                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth'
                                });

                                $('#complaintForm')[0].reset();
                            }

                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            // $("#loader-wrapper").hide();
                              $('#progressBar').css('width', '0%').text('0%').removeClass('bg-success');
                    $('#progress-container').addClass('d-none');
                    
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                if (xhr.responseJSON.errors.video) {
                                    $('#video-error').text(xhr.responseJSON.errors.video[0]);
                                }
                                if (xhr.responseJSON.errors.area_id) {
                                    alert(xhr.responseJSON.errors.area_id[
                                        0]);
                                }
                            } else {
                                alert('त्रुटि: शिकायत दर्ज नहीं की जा सकी।');
                            }
                        }
                    });
                });

                $('#video').on('change', function() {
                    const file = this.files[0];
                    const maxSize = 2.5 * 1024 * 1024 * 1024;
                    $('#video-error').text('');

                    if (!file) return;

                    if (file.size > maxSize) {
                        $('#video-error').text('वीडियो फ़ाइल अधिकतम 2.5GB हो सकती है।');
                        $(this).val('');
                    }
                });
            });
        </script>
    @endpush
@endsection
