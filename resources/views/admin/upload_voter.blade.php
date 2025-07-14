@php
    $pageTitle = 'मतदाता डेटा अपलोड';
    $breadcrumbs = [
        'एडमिन' => '#',
        'मतदाता डेटा अपलोड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Upload Voter Data')

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


        {{-- <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <a href="{{ route('voters.download') }}" class="btn btn-info">
                    <i class="fa fa-download"></i> Download Sample
                </a>
            </div>
        </div> --}}


        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form action="{{ route('voter.upload') }}" method="POST" id="voter-upload-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="voter_excel">Upload Filled Excel File</label>
                        <input type="file" name="voter_excel" id="voter_excel" class="form-control"
                            accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Upload</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card" id="error-table-container" style="display: none;">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h5 class="text-danger">Some records could not be processed:</h5>
                            <div id="upload-summary" style="display: none;" class="mb-3">
                                <span class="badge bg-success me-2" id="success-count">Success: 0</span>
                                <span class="badge bg-danger" id="failed-count">Failed: 0</span>
                            </div>
                            <table class="display table table-bordered" style="min-width: 845px" id="error-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Guardian</th>
                                        <th>House</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Voter ID</th>
                                        <th>Area</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="voter-results-body">
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
            document.getElementById('voter-upload-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                $(".loader-wrapper").show();

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async (res) => {
                        let data;
                        try {
                            data = await res.json();
                        } catch (err) {
                            alert('Failed to parse response');
                            return;
                        }

                        const success = data.success_count || 0;
                        const failed = data.failed_count || 0;

                        if (Array.isArray(data.errors) && data.errors.length > 0) {
                            showUploadErrors(data.errors);
                        } else {
                            hideUploadErrors();
                        }

                        document.getElementById('upload-summary').style.display = 'block';
                        document.getElementById('success-count').innerText = `Success: ${success}`;
                        document.getElementById('failed-count').innerText = `Failed: ${failed}`;
                    })
                    .catch((error) => {
                        console.error('Upload error:', error);
                        alert('Upload failed');
                    });
            });

            function showUploadErrors(errors) {
                const container = document.getElementById('error-table-container');
                const body = document.getElementById('voter-results-body');
                body.innerHTML = '';

                errors.forEach((err, index) => {
                    const row = `
                <tr>
                    <td>${index + 1}</td>
                    
                    <td>${escapeHtml(err.name)}</td>
                    <td>${escapeHtml(err.father_name)}</td>
                    <td>${escapeHtml(err.house)}</td>
                    <td>${escapeHtml(err.age)}</td>
                    <td>${escapeHtml(err.gender)}</td>
                    <td>${escapeHtml(err.voter_id)}</td>
                    <td>${escapeHtml(err.area)}</td>
                    <td>${escapeHtml(err.reason)}</td>
                </tr>`;
                    body.insertAdjacentHTML('beforeend', row);
                });

                container.style.display = 'block';

                if ($.fn.DataTable.isDataTable('#error-table')) {
                    $('#error-table').DataTable().clear().destroy();
                }
                $('#error-table').DataTable({
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                });
            }

            function hideUploadErrors() {
                const container = document.getElementById('error-table-container');
                const body = document.getElementById('voter-results-body');
                container.style.display = 'none';
                body.innerHTML = '';
            }

            function escapeHtml(unsafe) {
                return String(unsafe)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        </script>
    @endpush

@endsection
