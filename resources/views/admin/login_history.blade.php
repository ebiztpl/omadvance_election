@php
    $pageTitle = 'गतिविधि लॉग';
    $breadcrumbs = [
        'एडमिन' => '#',
        'गतिविधि लॉग' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Login History')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row mb-1">
                        <div class="col-md-2">
                            <label>तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label>तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-2">
                            <label>एडमिन</label>
                            <select name="admin_id" id="admin_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($admins ?? [] as $admin)
                                    <option value="{{ $admin->admin_id }}">{{ $admin->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मैनेजर</label>
                            <select name="manager_id" id="manager_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($managers ?? [] as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>कार्यालय</label>
                            <select name="office_id" id="office_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($offices ?? [] as $office)
                                    <option value="{{ $office->admin_id }}">{{ $office->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>फ़ील्ड</label>
                            <select name="field_id" id="field_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($fields ?? [] as $field)
                                    <option value="{{ $field->member_id }}">{{ $field->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div id="loginHistoryTable" class="table-responsive">
                            @php
                                $fromDate = $fromDate ?? date('Y-m-d');
                                $toDate = $toDate ?? date('Y-m-d');
                            @endphp

                            <span
                                style="margin-bottom: 0px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                गतिविधि - <span id="login-count"></span></span>
                            <table id="example" style="min-width: 845px " class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th id="col-name">यूज़र नाम</th>
                                        <th>भूमिका</th>
                                        <th>लॉगिन समय</th>
                                        <th>लॉगआउट समय</th>
                                        <th>IP</th>
                                        <th>स्थान</th>
                                        <th>डिवाइस/ब्राउज़र</th>
                                    </tr>
                                </thead>
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
                let table = $('#example').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: [{
                            extend: "csv",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                }
                            },
                        },
                        {
                            extend: "excel",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                }
                            },
                        }

                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, 500, 1000],
                        [10, 25, 50, 100, 500, 1000],
                    ],
                    ajax: {
                        url: "{{ route('activity_log.index') }}",
                        data: function(d) {
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.admin_id = $('#admin_id').val();
                            d.manager_id = $('#manager_id').val();
                            d.office_id = $('#office_id').val();
                            d.field_id = $('#field_id').val();
                        }
                    },
                    columns: [{
                            data: null,
                            name: 'serial_no',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: null,
                            name: 'name',
                            render: function(data, type, row) {
                                if (row.admin_name) {
                                    return row.admin_name;
                                }
                                if (row.member_name) {
                                    return row.member_name;
                                }
                                return '-';
                            }
                        },
                        {
                            data: null,
                            name: 'role_display',
                            render: function(data, type, row) {
                                if (row.role == 1) return "एडमिन";
                                if (row.role == 2) return "मैनेजर";
                                if (row.role == 3) return "कार्यालय";
                                if (row.position_id == 8) return "फ़ील्ड(सदस्य)";
                                return "-";
                            }
                        },

                        {
                            data: 'login_date_time',
                            name: 'login_date_time'
                        },
                        {
                            data: 'logout_date_time',
                            name: 'logout_date_time'
                        },
                        {
                            data: 'ip',
                            name: 'ip'
                        },
                        {
                            data: 'location',
                            name: 'location',
                            defaultContent: '-'
                        },
                        {
                            data: null,
                            name: 'device_browser',
                            render: function(data, type, row) {
                                let browser = row.browser ? row.browser : '-';
                                let device = row.device ? row.device : '-';
                                let os = row.os ? row.os : '';

                                let deviceInfo = device + (os ? " (" + os + ")" : "");
                                return browser + " | " + deviceInfo;
                            }
                        }
                    ]
                });

                table.on('preXhr.dt', function() {
                    $('#loader-wrapper').show();
                });

                table.on('xhr.dt', function() {
                    $('#loader-wrapper').hide();
                });

                table.on('draw', function() {
                    let info = table.page.info();
                    $('#login-count').text(info.recordsDisplay);
                });


                function toggleColumns() {
                    let onlyDate = !$('#admin_id').val() && !$('#manager_id').val() && !$('#office_id').val() && !$(
                        '#field_id').val();

                    if (onlyDate) {
                        $('#col-registration').show();
                        $('#col-name').show();
                    } else if ($('#field_id').val()) {
                        $('#col-registration').show();
                        $('#col-name').hide();
                    } else {
                        $('#col-registration').hide();
                        $('#col-name').show();
                    }
                }

                $('#complaintFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    table.ajax.reload();
                });

            });
        </script>
    @endpush
@endsection
