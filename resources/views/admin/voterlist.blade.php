@php
    $pageTitle = 'मतदाता डेटा';
    $breadcrumbs = [
        'एडमिन' => '#',
        'मतदाता डेटा' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Voter List')

@section('content')
    <div class="container">
        @foreach (['success', 'update_msg', 'delete_msg'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'delete_msg' ? 'danger' : 'success' }} alert-dismissible fade show">
                    {{ session($msg) }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        @endforeach

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('voterdata.index') }}">
                    @csrf
                    <div id="rowGroup">
                        <div class="form-row align-items-end mb-2">

                            <div class="col-md-4">
                                <label>मतदाता आईडी सर्च करें <span class="text-danger">*</span></label>
                                <input type="text" name="voter_id" id="main_voter_id" class="form-control">
                            </div>
                            <div class="col-xl-1 mt-2" style="color:rgb(55, 64, 75)">
                                <br />
                                <button type="button" id="data-filter" class="btn btn-success" disabled>सर्च
                                    करें</button>
                            </div>

                            <div class="col-xl-2">
                                <br />
                                <p class="mt-">कुल रिकॉर्ड: <span id="record-count">{{ $total }}</span></p>
                            </div>
                            <div class="col-md-2 mt-2">
                                <br />

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <button type="button" id="show-voters" class="btn btn-primary btn-lg">मतदाता देखें</button>
                </div>

                <div class="text-right mb-2">
                    <button id="reopen-downloads" class="btn btn-primary"
                        style="position: fixed; bottom: 20px; right: 20px; z-index: 1081;">Show Download
                        Link</button>
                </div>
                <div class="card" id="table_card" style="display: none">
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 25px; display:none;" id="download-progress-wrapper">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%" id="download-progress">0%</div>
                        </div>

                        <button id="download_full_data" class="btn btn-primary mb-3" style="display: none; float: right;">
                            पूरा डेटा डाउनलोड करें
                        </button>


                        <div class="table-responsive" id="filtered_data">
                            <table class="display table table-bordered" style="min-width: 845px" id="example">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th>नाम</th>
                                        <th>पिता/पति</th>
                                        <th>मकान क्र.</th>
                                        <th>उम्र</th>
                                        <th>लिंग</th>
                                        <th>मतदाता आईडी</th>
                                        <th>मतदान क्षेत्र</th>
                                        <th>जाति</th>
                                        <th>मतदान क्र.</th>
                                        <th>कुल सदस्य</th>
                                        <th>मुखिया मोबाइल</th>
                                        <th>मृत्यु/स्थानांतरित</th>
                                        <th>दिनांक</th>
                                        <th>क्रिया</th>
                                    </tr>
                                </thead>
                                {{-- <tbody id="voter-table-body">
                                    @php $i = 1; @endphp
                                    @foreach ($voters as $voter)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $voter->name }}</td>
                                            <td>{{ $voter->father_name }}</td>
                                            <td>{{ $voter->step2->house ?? '' }}</td>
                                            <td>{{ $voter->age }}</td>
                                            <td>{{ $voter->gender }}</td>
                                            <td>{{ $voter->voter_id }}</td>
                                            <td>{{ $voter->area_name }}</td>
                                            <td>{{ $voter->jati }}</td>
                                            <td>{{ $voter->step2->matdan_kendra_no ?? '' }}</td>
                                            <td>{{ $voter->step3->total_member ?? '' }}</td>
                                            <td>{{ $voter->step3->mukhiya_mobile ?? '' }}</td>
                                            <td>{{ $voter->{'death/left'} ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('voter.show', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-success mr-1">View</a>
                                                     <a href="{{ route('voter.update', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-info mr-1">Edit</a>
                                                <form action="{{ route('register.destroy', $voter->registration_id) }}"
                                                    method="POST" style="display: inline-block;"
                                                    onsubmit="return confirm('क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody> --}}
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
                $('#main_voter_id').on('input', function() {
                    const hasValue = $(this).val().trim().length > 0;
                    $('#data-filter').prop('disabled', !hasValue);
                });

                $('#show-voters').on('click', function() {
                    $('#table_card').show();
                    $("#download_full_data").show();
                    $(this).hide();
                    $("#loader-wrapper").show();
                    // Destroy if already initialized
                    if ($.fn.DataTable.isDataTable('#example')) {
                        $('#example').DataTable().clear().destroy();
                    }

                    var table = $('#example').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 10,
                        lengthChange: true,
                        dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                            '<"row"<"col-sm-12"tr>>' +
                            '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                        buttons: [
                            'csv', 'excel'
                        ],
                        lengthMenu: [
                            [10, 25, 50, 100, 500, 1000],
                            [10, 25, 50, 100, 500, 1000],
                        ],
                        ajax: {
                            url: "{{ route('viewvoter.index') }}",
                            type: 'GET',
                            data: function(d) {
                                d.voter_id = $('#main_voter_id').val();
                            },
                            dataSrc: function(json) {
                                $('#record-count').text(json.recordsFiltered);
                                return json.data;
                            },
                            complete: function() {
                                $("#loader-wrapper").hide();
                            },
                            error: function(xhr) {
                                console.error("AJAX error:", xhr.responseText);
                            }
                        },
                        columns: [{
                                data: 'sr_no'
                            },
                            {
                                data: 'name'
                            },
                            {
                                data: 'father_name'
                            },
                            {
                                data: 'house'
                            },
                            {
                                data: 'age'
                            },
                            {
                                data: 'gender'
                            },
                            {
                                data: 'voter_id'
                            },
                            {
                                data: 'area_name'
                            },
                            {
                                data: 'jati'
                            },
                            {
                                data: 'matdan_kendra_no'
                            },
                            {
                                data: 'total_member'
                            },
                            {
                                data: 'mukhiya_mobile'
                            },
                            {
                                data: 'death_left'
                            },
                            {
                                data: 'date_time'
                            },
                            {
                                data: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                    $('#example').on('preXhr.dt', function() {
                        $("#loader-wrapper").show();
                    });

                    $('#example').on('xhr.dt', function() {
                        $("#loader-wrapper").hide();
                    });

                    $('#data-filter').click(function() {
                        $('#example').DataTable().ajax.reload(null, false);
                    });

                    // $('#download_full_data').click(function(e) {
                    //     e.preventDefault();
                    //     $.post("{{ route('voterlist.request') }}", {
                    //         _token: "{{ csrf_token() }}",
                    //         voter_id: $('#main_voter_id').val()
                    //     }, function(res) {
                    //         if (res.status === 'success') showToast(res.message, 5000);
                    //         else showToast('कुछ गलत हुआ।', 5000);
                    //     }, 'json');
                    // });
                });


                // // Filter by Voter ID
                // $('#data-filter').click(function() {
                //     $("#loader-wrapper").show();
                //     $('#example').DataTable().ajax.reload(null, false);
                // });
            });
        </script>
    @endpush

@endsection
