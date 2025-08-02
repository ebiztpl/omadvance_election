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

        {{-- @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif --}}

        <div id="upload-alert" style="display: none;" class="alert alert-dismissible fade show mt-3" role="alert">
            <span id="upload-alert-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form action="{{ route('voter.upload') }}" method="POST" id="voter-upload-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="voter_excel">भरा हुआ एक्सेल फ़ाइल अपलोड करें </label>
                        <input type="file" name="voter_excel" id="voter_excel" class="form-control" accept=".xlsx,.xls"
                            required>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">अपलोड करें</button>
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
                                <span class="badge bg-warning" id="repeat_count">Duplicate: 0</span>
                            </div>
                            <table class="display table table-bordered" style="min-width: 845px" id="example">
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
                                        <th>Jati</th>
                                        <th>Polling No.</th>
                                        <th>Total Member</th>
                                        <th>Mukhiya Mobile</th>
                                        <th>Death/Left</th>
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

                $("#loader-wrapper").show();
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async (res) => {
                        $("#loader-wrapper").hide();
                        let data;
                        try {
                            data = await res.json();
                        } catch (err) {
                            alert('Failed to parse response');
                            return;
                        }

                        const success = data.success_count || 0;
                        const failed = data.failed_count || 0;
                        const duplicate = data.repeat_count || 0;

                        let alertType = 'info';
                        let alertMessage = '';

                        if (success > 0 && failed > 0 && duplicate > 0) {
                            alertType = 'warning';
                            alertMessage =
                                `${success} रिकॉर्ड सफल, ${failed} असफल, ${duplicate} डुप्लीकेट रिकॉर्ड पाए गए और अपडेट किए गए।`;
                        } else if (success > 0 && duplicate > 0) {
                            alertType = 'success';
                            alertMessage =
                                `${success} रिकॉर्ड सफलतापूर्वक अपलोड किए गए, ${duplicate} डुप्लीकेट रिकॉर्ड पाए गए और अपडेट किए गए।`;
                        } else if (success > 0 && failed > 0) {
                            alertType = 'warning';
                            alertMessage = `${success} सफल, ${failed} असफल।`;
                        } else if (success > 0) {
                            alertType = 'success';
                            alertMessage = `${success} रिकॉर्ड सफलतापूर्वक अपलोड किए गए।`;
                        } else if (failed > 0 && duplicate > 0) {
                            alertType = 'danger';
                            alertMessage =
                                `कोई भी नया रिकॉर्ड अपलोड नहीं हुआ। ${failed} त्रुटियाँ, ${duplicate} डुप्लीकेट रिकॉर्ड पाए गए और अपडेट किए गए।`;
                        } else if (failed > 0) {
                            alertType = 'danger';
                            alertMessage = `कोई भी रिकॉर्ड अपलोड नहीं हुआ। कुल ${failed} त्रुटियाँ मिलीं।`;
                        } else if (duplicate > 0) {
                            alertType = 'info';
                            alertMessage =
                                `${duplicate} डुप्लीकेट रिकॉर्ड पाए गए, अपडेट कर दिए गए और कोई नया डेटा अपलोड नहीं हुआ।`;
                        }

                        // Set Bootstrap alert
                        const alertBox = document.getElementById('upload-alert');
                        alertBox.classList.remove('alert-success', 'alert-warning', 'alert-danger',
                            'alert-info');
                        alertBox.classList.add('alert-' + alertType);
                        document.getElementById('upload-alert-message').innerText = alertMessage;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });

                        alertBox.style.display = 'block';

                        setTimeout(() => {
                            alertBox.style.display = 'none';
                        }, 10000);

                        if (Array.isArray(data.errors) && data.errors.length > 0) {
                            showUploadErrors(data.errors);
                        } else {
                            hideUploadErrors();
                        }

                        document.getElementById('upload-summary').style.display = 'block';
                        document.getElementById('success-count').innerText = `Success: ${success}`;
                        document.getElementById('failed-count').innerText = `Failed: ${failed}`;
                        document.getElementById('repeat_count').innerText = `Duplicate: ${duplicate}`;

                        form.reset();
                    })
                    .catch((error) => {
                        console.error('Upload error:', error);
                        alert('Upload failed');
                        $("#loader-wrapper").hide();
                    });
            });

            function showUploadErrors(errors) {
                const container = document.getElementById('error-table-container');
                const body = document.getElementById('voter-results-body');

                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable().clear().destroy();
                }

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
            <td>${escapeHtml(err.jati)}</td>
            <td>${escapeHtml(err.polling_no)}</td>
            <td>${escapeHtml(err.family_count)}</td>
            <td>${escapeHtml(err.mukhiya_mobile)}</td>
            <td>${escapeHtml(err.death_left)}</td>
            <td>${escapeHtml(err.reason)}</td>
        </tr>`;
                    body.insertAdjacentHTML('beforeend', row);
                });

                container.style.display = 'block';

                // Re-initialize DataTable after table is rebuilt
                $('#example').DataTable({
                    destroy: true, // Important to ensure full reset
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: [
                        'copy',
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            title: '',
                            exportOptions: {
                                modifier: {
                                    page: 'all'
                                },
                                columns: ':visible'
                            }
                        },
                        'pdf',
                        'print'
                    ]
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