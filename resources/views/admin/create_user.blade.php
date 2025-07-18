@php
    $pageTitle = 'एडमिन डैशबोर्ड';
    $breadcrumbs = [
        'एडमिन' => '#',
        'एडमिन डैशबोर्ड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Admin Create Manager/User')

@section('content')
    <div class="container">
        <h3>ऑपरेटर/मैनेजर बनाएँ</h3>
        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="row page-titles mx-0">

            <div class="col-md-4 col-sm-12 col-xs-12">

                <form id="createAdminForm" method="POST" action="{{ route('admin.store') }}">
                    @csrf

                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="admin_name">नाम:</label>
                            <input type="text" name="admin_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="admin_pass">पासवर्ड:</label>
                            <input type="password" name="admin_pass" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label>भूमिका (Role):</label>
                            <select name="role" class="form-control" required>
                                <option value="">-- चुने --</option>
                                <option value="2">मैनेजर</option>
                                <option value="3">ऑपरेटर</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">बनाएं</button>
                </form>
            </div>

            <div class="col-md-1 d-flex justify-content-center align-items-center">
                <div style="width:1px; height:100%; background:#ccc;"></div>
            </div>

            <div class="col-md-7">
                <div class="card" id="table_card">
                    <div class="card-body">
                        <div id="filtered_data" class="table-responsive">
                            <table class="display table table-bordered" style="min-width: 845px" id="data">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>नाम</th>
                                        <th>भूमिका</th>
                                        <th>पोस्ट तिथि</th>
                                        <th>क्रिया</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach ($admins as $admin)
                                        <tr>
                                            <td>{{ $admin->admin_id }}</td>
                                            <td>{{ $admin->admin_name }}</td>
                                            <td>
                                                @if ($admin->role == 1)
                                                    एडमिन
                                                @elseif ($admin->role == 2)
                                                    मैनेजर
                                                @elseif ($admin->role == 3)
                                                    ऑपरेटर
                                                @else
                                                    ''
                                                @endif
                                            </td>
                                            {{-- <td>{{ $admin->admin_pass }}</td> --}}
                                            <td>{{ $admin->posted_date }}</td>
                                            <td>

                                                <button type="button" class="btn btn-success btn-sm mr-2 editBtn"
                                                    data-toggle="modal" data-target="#editModal"
                                                    data-id="{{ $admin->admin_id }}" data-name="{{ $admin->admin_name }}"
                                                    data-role="{{ $admin->role }}">
                                                    अपडेट
                                                </button>

                                                <form method="POST" action="{{ route('admin.destroy', $admin->admin_id) }}"
                                                    style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm mr-2"
                                                        onclick="return confirm('हटाना निश्चित है?')">हटाएं</button>
                                                </form>
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



        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{ route('admin.update', $admin->admin_id) }}" id="editForm">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">ऑपरेटर/मैनेजर अपडेट करें</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label>नाम:</label>
                                <input type="text" name="admin_name" class="form-control" id="edit_name" required>
                            </div>

                            <div class="form-group">
                                <label>पासवर्ड (खाली छोड़ें तो पुराना रहेगा):</label>
                                <input type="password" name="admin_pass" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>भूमिका:</label>
                                <select name="role" class="form-control" id="edit_role" required>
                                    <option value="2">मैनेजर</option>
                                    <option value="3">ऑपरेटर</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">अपडेट करें</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">बंद करें</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#createAdminForm').on('submit', function(e) {
                    e.preventDefault();

                    $("#loader-wrapper").show();

                    let form = this;
                    let formData = new FormData(form);

                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#success-message').text("यूज़र सफलतापूर्वक जोड़ा गया।");
                            $('#success-alert').removeClass('d-none');

                            // Reset the form if needed
                            form.reset();

                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            alert('यूज़र जोड़ने में त्रुटि हुई।');
                        }
                    });
                });

                $('.editBtn').on('click', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var role = $(this).data('role');

                    $('#edit_name').val(name);
                    $('#edit_role').val(role);

                    let url = "{{ url('admin/users') }}/" + id;
                    $('#editForm').attr('action', url);
                });

                $('#data').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "searching": true
                });
            });
        </script>
    @endpush
@endsection
